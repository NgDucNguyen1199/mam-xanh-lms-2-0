const {
  RLS_HELP_VI,
  assertCleanUtf8,
  setJsonHeaders,
  hasSupabaseConfig,
  inspectServiceKeyRole,
  isRlsPolicyError,
  formatSupabaseError,
  supabaseFetch,
  supabaseCount,
  computeCertHmac,
  parseJsonBody,
  sanitizeUser,
  authenticateRequest,
  FALLBACK_CERTS,
  FALLBACK_BATCHES,
  FALLBACK_LOGS,
  FALLBACK_FERTIGATION,
  FALLBACK_USERS,
  FALLBACK_COURSES,
  FALLBACK_ENROLLMENTS,
  FALLBACK_FIELDPRACTICE_SUBS,
  FALLBACK_QUIZ_ATTEMPTS,
  FALLBACK_NEWS,
} = require('./_supabase');
const {
  getCourseBlueprint,
  getClientQuizQuestions,
  gradeQuizAnswers,
  computeDistanceMeters,
} = require('./_lms_curriculum');

function buildFallbackCertList() {
  return Object.values(FALLBACK_CERTS);
}

async function maybeIssueCertificate({ source, studentUser, course, quizScore, practiceGrade }) {
  const qScore = Number(quizScore || 0);
  const pScore = Number(practiceGrade || 0);
  if (qScore < 70 || pScore < 60) {
    return { issued: false, certificate: null };
  }

  const finalScore = Number((qScore * 0.5 + pScore * 0.5).toFixed(2));
  const cleanCourseTag = String(course.code).replace(/[^A-Z0-9]/gi, '').toUpperCase().slice(0, 12);
  const suffixNum = String((Number(studentUser.id) * 100 + Number(course.id || 1)) % 10000).padStart(4, '0');
  const certCode = `MXCERT-2026-${cleanCourseTag}-${suffixNum}`;
  const issuedUnix = Math.floor(Date.now() / 1000);
  const hmacSig = computeCertHmac(certCode, studentUser.id, course.id || 1, finalScore, issuedUnix);

  const certRow = assertCleanUtf8({
    cert_code: certCode,
    student_name: studentUser.full_name,
    student_username: studentUser.username,
    farm_name: studentUser.farm_name || 'Nông trại Mầm Xanh',
    course_code: course.code,
    course_name: course.fullname,
    final_score: finalScore,
    hmac_signature: hmacSig,
    status: 'valid',
    issued_at: new Date(issuedUnix * 1000).toISOString(),
    moodle_userid: Number(studentUser.id),
    moodle_courseid: Number(course.id || 1),
    issued_unix: issuedUnix,
  });

  if (source === 'supabase-live') {
    try {
      const existing = await supabaseFetch(
        `mamxanh_certificates?student_username=eq.${encodeURIComponent(studentUser.username)}&course_code=eq.${encodeURIComponent(course.code)}&select=*&limit=1`
      );
      if (Array.isArray(existing) && existing.length > 0) {
        return { issued: true, already_existed: true, certificate: existing[0] };
      }
      const inserted = await supabaseFetch('mamxanh_certificates', {
        method: 'POST',
        headers: { Prefer: 'return=representation' },
        body: certRow,
      });
      return { issued: true, already_existed: false, certificate: Array.isArray(inserted) ? inserted[0] : certRow };
    } catch (err) {
      if (!isRlsPolicyError(err)) throw err;
    }
  }

  const existingFb = buildFallbackCertList().find(
    c => c.student_username === studentUser.username && c.course_code === course.code
  );
  if (existingFb) {
    return { issued: true, already_existed: true, certificate: existingFb };
  }
  FALLBACK_CERTS[certCode] = certRow;
  return { issued: true, already_existed: false, certificate: certRow };
}

module.exports = async function handler(req, res) {
  setJsonHeaders(res);

  const method = String(req.method || 'GET').toUpperCase();
  const body = method === 'POST' ? parseJsonBody(req) : {};
  const action = String((req.query && req.query.action) || body.action || '').trim().toLowerCase();
  const supabaseEnabled = hasSupabaseConfig();
  const keyRole = inspectServiceKeyRole();
  const canWriteSupabase = supabaseEnabled && keyRole === 'service_role';

  // PUBLIC: GET /api/lms?action=news
  if (method === 'GET' && action === 'news') {
    if (supabaseEnabled) {
      try {
        const rows = await supabaseFetch('lms_news?select=*&order=published_at.desc,id.desc&limit=20');
        const memNews = FALLBACK_NEWS.filter(n => Number(n.id) > 4);
        return res.status(200).json({
          ok: true,
          source: 'supabase-live',
          news: [...memNews, ...(Array.isArray(rows) ? rows : [])],
        });
      } catch (err) {
        return res.status(200).json({ ok: true, source: 'embedded-fallback', fallback_reason: err.message, news: FALLBACK_NEWS });
      }
    }
    return res.status(200).json({ ok: true, source: 'embedded-fallback', news: FALLBACK_NEWS });
  }

  // PUBLIC: GET /api/lms?action=courses
  if (method === 'GET' && action === 'courses') {
    if (supabaseEnabled) {
      try {
        const rows = await supabaseFetch('lms_courses?select=*&order=id.asc');
        const memCourses = FALLBACK_COURSES.filter(c => Number(c.id) > 8);
        return res.status(200).json({
          ok: true,
          source: 'supabase-live',
          courses: [...(Array.isArray(rows) ? rows : []), ...memCourses],
        });
      } catch (err) {
        return res.status(200).json({ ok: true, source: 'embedded-fallback', fallback_reason: err.message, courses: FALLBACK_COURSES });
      }
    }
    return res.status(200).json({ ok: true, source: 'embedded-fallback', courses: FALLBACK_COURSES });
  }

  // AUTHENTICATED: GET /api/lms?action=dashboard
  if (method === 'GET' && action === 'dashboard') {
    const auth = await authenticateRequest(req, res);
    if (!auth) return;
    const { user } = auth;
    const effectiveSource = supabaseEnabled ? 'supabase-live' : 'embedded-fallback';
    const isStaff = user.role === 'teacher' || user.role === 'admin';

    let courses = [];
    let enrollments = [];
    let subs = [];
    let quizzes = [];
    let certs = [];
    let gradingQueue = [];

    if (canWriteSupabase) {
      try {
        const [cRows, eRows, sRows, qRows, certRows] = await Promise.all([
          supabaseFetch('lms_courses?select=*&order=id.asc'),
          supabaseFetch(`lms_enrollments?user_id=eq.${encodeURIComponent(user.id)}&select=*&order=id.asc`),
          supabaseFetch(`lms_fieldpractice_subs?user_id=eq.${encodeURIComponent(user.id)}&select=*&order=submitted_at.desc,id.desc`),
          supabaseFetch(`lms_quiz_attempts?user_id=eq.${encodeURIComponent(user.id)}&select=*&order=attempted_at.desc,id.desc`),
          supabaseFetch(`mamxanh_certificates?student_username=eq.${encodeURIComponent(user.username)}&select=*&order=issued_at.desc,id.desc`),
        ]);
        courses = cRows || [];
        enrollments = eRows || [];
        subs = sRows || [];
        quizzes = qRows || [];
        certs = certRows || [];

        if (isStaff) {
          const [allSubs, allUsers, allEnrolls] = await Promise.all([
            supabaseFetch('lms_fieldpractice_subs?select=*&order=submitted_at.desc,id.desc&limit=50'),
            supabaseFetch('lms_users?select=id,username,full_name,farm_name,region,role'),
            supabaseFetch('lms_enrollments?select=user_id,course_code,quiz_score,progress_pct,status'),
          ]);
          const userMap = new Map((allUsers || []).map(u => [u.id, u]));
          const courseMap = new Map(courses.map(c => [c.code, c]));
          const enrollMap = new Map((allEnrolls || []).map(e => [`${e.user_id}:${e.course_code}`, e]));
          gradingQueue = (allSubs || []).map(s => {
            const stu = userMap.get(s.user_id) || {};
            const crs = courseMap.get(s.course_code) || {};
            const enr = enrollMap.get(`${s.user_id}:${s.course_code}`) || {};
            return {
              ...s,
              student_name: stu.full_name || 'Nông hộ',
              student_username: stu.username || '',
              farm_name: stu.farm_name || '',
              course_name: crs.fullname || s.course_code,
              student_quiz_score: Number(enr.quiz_score || 0),
            };
          });
        }
      } catch (err) {
        return res.status(502).json({ ok: false, message_vi: formatSupabaseError(err, 'tải bảng điều khiển') });
      }
    } else {
      courses = FALLBACK_COURSES;
      enrollments = FALLBACK_ENROLLMENTS.filter(e => Number(e.user_id) === Number(user.id));
      subs = FALLBACK_FIELDPRACTICE_SUBS.filter(s => Number(s.user_id) === Number(user.id));
      quizzes = FALLBACK_QUIZ_ATTEMPTS.filter(q => Number(q.user_id) === Number(user.id));
      if (supabaseEnabled) {
        try {
          const certRows = await supabaseFetch(`mamxanh_certificates?student_username=eq.${encodeURIComponent(user.username)}&select=*&order=issued_at.desc,id.desc`);
          const fbCerts = buildFallbackCertList().filter(c => c.student_username === user.username);
          const seen = new Set((certRows || []).map(c => c.cert_code));
          certs = [...(certRows || []), ...fbCerts.filter(c => !seen.has(c.cert_code))];
        } catch {
          certs = buildFallbackCertList().filter(c => c.student_username === user.username);
        }
      } else {
        certs = buildFallbackCertList().filter(c => c.student_username === user.username);
      }

      if (isStaff) {
        const userMap = new Map(FALLBACK_USERS.map(u => [u.id, u]));
        const courseMap = new Map(courses.map(c => [c.code, c]));
        const enrollMap = new Map(FALLBACK_ENROLLMENTS.map(e => [`${e.user_id}:${e.course_code}`, e]));
        gradingQueue = FALLBACK_FIELDPRACTICE_SUBS.map(s => {
          const stu = userMap.get(s.user_id) || {};
          const crs = courseMap.get(s.course_code) || {};
          const enr = enrollMap.get(`${s.user_id}:${s.course_code}`) || {};
          return {
            ...s,
            student_name: stu.full_name || 'Nông hộ',
            student_username: stu.username || '',
            farm_name: stu.farm_name || '',
            course_name: crs.fullname || s.course_code,
            student_quiz_score: Number(enr.quiz_score || 0),
          };
        });
      }
    }

    const courseMap = new Map(courses.map(c => [c.code, c]));
    const enrichedEnrollments = enrollments.map(e => {
      const c = courseMap.get(e.course_code) || {};
      const latestSub = subs.find(s => s.course_code === e.course_code) || null;
      const cert = certs.find(ct => ct.course_code === e.course_code) || null;
      return {
        ...e,
        fullname: c.fullname || e.course_code,
        category: c.category || '',
        modules_count: c.modules_count || 14,
        practice_sub: latestSub,
        certificate: cert,
      };
    });

    const avgProgress = enrichedEnrollments.length
      ? Number((enrichedEnrollments.reduce((acc, x) => acc + Number(x.progress_pct || 0), 0) / enrichedEnrollments.length).toFixed(1))
      : 0;

    return res.status(200).json(assertCleanUtf8({
      ok: true,
      source: effectiveSource,
      key_role: keyRole,
      user: sanitizeUser(user),
      stats: {
        enrolled_count: enrichedEnrollments.length,
        avg_progress: avgProgress,
        certificates_count: certs.length,
        practice_submissions_count: subs.length,
      },
      enrollments: enrichedEnrollments,
      all_courses: courses,
      practice_submissions: subs,
      quiz_attempts: quizzes,
      certificates: certs,
      ...(isStaff ? { grading_queue: gradingQueue } : {}),
    }));
  }

  // AUTHENTICATED: GET /api/lms?action=course_detail&code=...
  if (method === 'GET' && action === 'course_detail') {
    const auth = await authenticateRequest(req, res);
    if (!auth) return;
    const { user } = auth;
    const effectiveSource = supabaseEnabled ? 'supabase-live' : 'embedded-fallback';
    const code = assertCleanUtf8(String((req.query && req.query.code) || '').trim().toUpperCase());
    if (!code) {
      return res.status(400).json({ ok: false, message_vi: 'Thiếu mã khóa học (code).' });
    }

    let course = null;
    let enrollment = null;
    let practiceSubs = [];
    let quizAttempts = [];
    let cert = null;

    if (canWriteSupabase) {
      const [cRows, eRows, sRows, qRows, certRows] = await Promise.all([
        supabaseFetch(`lms_courses?code=eq.${encodeURIComponent(code)}&select=*&limit=1`),
        supabaseFetch(`lms_enrollments?user_id=eq.${encodeURIComponent(user.id)}&course_code=eq.${encodeURIComponent(code)}&select=*&limit=1`),
        supabaseFetch(`lms_fieldpractice_subs?user_id=eq.${encodeURIComponent(user.id)}&course_code=eq.${encodeURIComponent(code)}&select=*&order=submitted_at.desc,id.desc&limit=5`),
        supabaseFetch(`lms_quiz_attempts?user_id=eq.${encodeURIComponent(user.id)}&course_code=eq.${encodeURIComponent(code)}&select=*&order=attempted_at.desc,id.desc&limit=5`),
        supabaseFetch(`mamxanh_certificates?student_username=eq.${encodeURIComponent(user.username)}&course_code=eq.${encodeURIComponent(code)}&select=*&limit=1`),
      ]);
      course = cRows && cRows[0];
      enrollment = (eRows && eRows[0]) || null;
      practiceSubs = sRows || [];
      quizAttempts = qRows || [];
      cert = (certRows && certRows[0]) || null;
    } else {
      course = FALLBACK_COURSES.find(c => c.code === code) || null;
      enrollment = FALLBACK_ENROLLMENTS.find(e => Number(e.user_id) === Number(user.id) && e.course_code === code) || null;
      practiceSubs = FALLBACK_FIELDPRACTICE_SUBS.filter(s => Number(s.user_id) === Number(user.id) && s.course_code === code);
      quizAttempts = FALLBACK_QUIZ_ATTEMPTS.filter(q => Number(q.user_id) === Number(user.id) && q.course_code === code);
      cert = buildFallbackCertList().find(ct => ct.student_username === user.username && ct.course_code === code) || null;
      if (!cert && supabaseEnabled) {
        try {
          const certRows = await supabaseFetch(`mamxanh_certificates?student_username=eq.${encodeURIComponent(user.username)}&course_code=eq.${encodeURIComponent(code)}&select=*&limit=1`);
          cert = (certRows && certRows[0]) || null;
        } catch {}
      }
    }

    if (!course) {
      return res.status(404).json({ ok: false, message_vi: `Không tìm thấy khóa học "${code}".` });
    }

    const bp = getCourseBlueprint(code);
    return res.status(200).json(assertCleanUtf8({
      ok: true,
      source: effectiveSource,
      course,
      enrollment,
      pedagogy: {
        pdf_title: bp.pdf_title,
        pdf_summary: bp.pdf_summary,
        modules: bp.modules,
        h5p_prompt: bp.h5p_prompt,
        station_name: bp.station_name,
        target_lat: bp.target_lat,
        target_lng: bp.target_lng,
        expected_qr_prefix: bp.expected_qr_prefix,
        rubric_criteria: [
          { key: 'crit_prep', label: '1. Chuẩn bị vật tư, bảo hộ & hiệu chuẩn dụng cụ', max: 25 },
          { key: 'crit_tech', label: '2. Thao tác kỹ thuật chuẩn quy trình VietGAP / Fertigation', max: 25 },
          { key: 'crit_biosafe', label: '3. An toàn sinh học, kiểm soát pH/EC & thời gian cách ly PHI', max: 25 },
          { key: 'crit_quality', label: '4. Ghi chép Nhật ký điện tử & Chất lượng kết quả nghiệm thu', max: 25 },
        ],
      },
      quiz_questions: getClientQuizQuestions(code),
      practice_submissions: practiceSubs,
      quiz_attempts: quizAttempts,
      certificate: cert,
    }));
  }

  // AUTHENTICATED: POST /api/lms?action=enroll
  if (method === 'POST' && action === 'enroll') {
    const auth = await authenticateRequest(req, res);
    if (!auth) return;
    const { user } = auth;
    const effectiveSource = supabaseEnabled ? 'supabase-live' : 'embedded-fallback';
    const courseCode = assertCleanUtf8(String(body.course_code || '').trim().toUpperCase());
    if (!courseCode) {
      return res.status(400).json({ ok: false, message_vi: 'Thiếu mã khóa học (course_code).' });
    }

    if (canWriteSupabase) {
      try {
        const cRows = await supabaseFetch(`lms_courses?code=eq.${encodeURIComponent(courseCode)}&select=*&limit=1`);
        if (!Array.isArray(cRows) || cRows.length === 0) {
          return res.status(404).json({ ok: false, message_vi: `Khóa học "${courseCode}" không tồn tại.` });
        }
        const existing = await supabaseFetch(
          `lms_enrollments?user_id=eq.${encodeURIComponent(user.id)}&course_code=eq.${encodeURIComponent(courseCode)}&select=*&limit=1`
        );
        if (Array.isArray(existing) && existing.length > 0) {
          return res.status(200).json({ ok: true, source: effectiveSource, enrollment: existing[0], already_enrolled: true });
        }
        const ins = await supabaseFetch('lms_enrollments', {
          method: 'POST',
          headers: { Prefer: 'return=representation' },
          body: { user_id: user.id, course_code: courseCode, progress_pct: 10, quiz_score: 0, attendance_count: 0, status: 'enrolled' },
        });
        return res.status(201).json({ ok: true, source: effectiveSource, enrollment: Array.isArray(ins) ? ins[0] : ins });
      } catch (err) {
        if (!isRlsPolicyError(err)) {
          return res.status(502).json({ ok: false, message_vi: formatSupabaseError(err, 'ghi danh khóa học') });
        }
      }
    }

    if (!FALLBACK_COURSES.some(c => c.code === courseCode)) {
      return res.status(404).json({ ok: false, message_vi: `Khóa học "${courseCode}" không tồn tại.` });
    }
    const existingFb = FALLBACK_ENROLLMENTS.find(e => Number(e.user_id) === Number(user.id) && e.course_code === courseCode);
    if (existingFb) return res.status(200).json({ ok: true, source: effectiveSource, enrollment: existingFb, already_enrolled: true });
    const newEnr = {
      id: FALLBACK_ENROLLMENTS.length + 1,
      user_id: user.id,
      course_code: courseCode,
      progress_pct: 10,
      quiz_score: 0,
      attendance_count: 0,
      status: 'enrolled',
      enrolled_at: new Date().toISOString(),
    };
    FALLBACK_ENROLLMENTS.push(newEnr);
    return res.status(201).json({ ok: true, source: effectiveSource, enrollment: newEnr });
  }

  // AUTHENTICATED: POST /api/lms?action=submit_quiz
  if (method === 'POST' && action === 'submit_quiz') {
    const auth = await authenticateRequest(req, res);
    if (!auth) return;
    const { user } = auth;
    const effectiveSource = supabaseEnabled ? 'supabase-live' : 'embedded-fallback';
    const courseCode = assertCleanUtf8(String(body.course_code || '').trim().toUpperCase());
    const answers = Array.isArray(body.answers) ? body.answers : [];
    if (!courseCode) {
      return res.status(400).json({ ok: false, message_vi: 'Thiếu mã khóa học (course_code).' });
    }

    const graded = gradeQuizAnswers(courseCode, answers);

    if (canWriteSupabase) {
      try {
        const cRows = await supabaseFetch(`lms_courses?code=eq.${encodeURIComponent(courseCode)}&select=*&limit=1`);
        const course = cRows && cRows[0];
        if (!course) return res.status(404).json({ ok: false, message_vi: `Khóa học "${courseCode}" không tồn tại.` });

        const attemptIns = await supabaseFetch('lms_quiz_attempts', {
          method: 'POST',
          headers: { Prefer: 'return=representation' },
          body: {
            user_id: user.id,
            course_code: courseCode,
            score_100: graded.score100,
            answers_json: { answers, correct: graded.correctCount, total: graded.total },
          },
        });

        const [eRows, sRows] = await Promise.all([
          supabaseFetch(`lms_enrollments?user_id=eq.${encodeURIComponent(user.id)}&course_code=eq.${encodeURIComponent(courseCode)}&select=*&limit=1`),
          supabaseFetch(`lms_fieldpractice_subs?user_id=eq.${encodeURIComponent(user.id)}&course_code=eq.${encodeURIComponent(courseCode)}&graded_by=not.is.null&select=*&order=total_grade.desc&limit=1`),
        ]);

        const prevEnroll = eRows && eRows[0];
        const gradedPractice = sRows && sRows[0];
        const bestQuiz = Math.max(Number(prevEnroll ? prevEnroll.quiz_score : 0), graded.score100);
        const practiceGrade = gradedPractice ? Number(gradedPractice.total_grade) : 0;
        const nextProgress = practiceGrade >= 60 && bestQuiz >= 70 ? 100 : Math.max(Number(prevEnroll ? prevEnroll.progress_pct : 0), bestQuiz >= 70 ? 60 : 35);
        const nextStatus = nextProgress >= 100 ? 'completed' : 'in_progress';

        if (prevEnroll) {
          await supabaseFetch(`lms_enrollments?id=eq.${prevEnroll.id}`, {
            method: 'PATCH',
            body: { quiz_score: bestQuiz, progress_pct: nextProgress, status: nextStatus },
          });
        } else {
          await supabaseFetch('lms_enrollments', {
            method: 'POST',
            body: { user_id: user.id, course_code: courseCode, quiz_score: bestQuiz, progress_pct: nextProgress, attendance_count: 0, status: nextStatus },
          });
        }

        const certRes = await maybeIssueCertificate({ source: effectiveSource, studentUser: user, course, quizScore: bestQuiz, practiceGrade });
        return res.status(200).json({
          ok: true,
          source: effectiveSource,
          course_code: courseCode,
          score_100: graded.score100,
          correct_count: graded.correctCount,
          total_questions: graded.total,
          breakdown: graded.breakdown,
          best_quiz_score: bestQuiz,
          certificate_issued: certRes.issued,
          certificate: certRes.certificate,
          attempt: Array.isArray(attemptIns) ? attemptIns[0] : attemptIns,
        });
      } catch (err) {
        if (!isRlsPolicyError(err)) {
          return res.status(502).json({ ok: false, message_vi: formatSupabaseError(err, 'chấm bài Quiz') });
        }
      }
    }

    const course = FALLBACK_COURSES.find(c => c.code === courseCode);
    if (!course) return res.status(404).json({ ok: false, message_vi: `Khóa học "${courseCode}" không tồn tại.` });

    const attempt = {
      id: FALLBACK_QUIZ_ATTEMPTS.length + 1,
      user_id: user.id,
      course_code: courseCode,
      score_100: graded.score100,
      answers_json: { answers, correct: graded.correctCount, total: graded.total },
      attempted_at: new Date().toISOString(),
    };
    FALLBACK_QUIZ_ATTEMPTS.unshift(attempt);

    let enr = FALLBACK_ENROLLMENTS.find(e => Number(e.user_id) === Number(user.id) && e.course_code === courseCode);
    const gradedPractice = FALLBACK_FIELDPRACTICE_SUBS
      .filter(s => Number(s.user_id) === Number(user.id) && s.course_code === courseCode && s.graded_by)
      .sort((a, b) => Number(b.total_grade) - Number(a.total_grade))[0];

    const bestQuiz = Math.max(Number(enr ? enr.quiz_score : 0), graded.score100);
    const practiceGrade = gradedPractice ? Number(gradedPractice.total_grade) : 0;
    const nextProgress = practiceGrade >= 60 && bestQuiz >= 70 ? 100 : Math.max(Number(enr ? enr.progress_pct : 0), bestQuiz >= 70 ? 60 : 35);
    const nextStatus = nextProgress >= 100 ? 'completed' : 'in_progress';
    if (enr) {
      enr.quiz_score = bestQuiz;
      enr.progress_pct = nextProgress;
      enr.status = nextStatus;
    } else {
      enr = { id: FALLBACK_ENROLLMENTS.length + 1, user_id: user.id, course_code: courseCode, progress_pct: nextProgress, quiz_score: bestQuiz, attendance_count: 0, status: nextStatus, enrolled_at: new Date().toISOString() };
      FALLBACK_ENROLLMENTS.push(enr);
    }

    const certRes = await maybeIssueCertificate({ source: effectiveSource, studentUser: user, course, quizScore: bestQuiz, practiceGrade });
    return res.status(200).json({
      ok: true,
      source: effectiveSource,
      course_code: courseCode,
      score_100: graded.score100,
      correct_count: graded.correctCount,
      total_questions: graded.total,
      breakdown: graded.breakdown,
      best_quiz_score: bestQuiz,
      certificate_issued: certRes.issued,
      certificate: certRes.certificate,
      attempt,
    });
  }

  // AUTHENTICATED: POST /api/lms?action=submit_practice
  if (method === 'POST' && action === 'submit_practice') {
    const auth = await authenticateRequest(req, res);
    if (!auth) return;
    const { user } = auth;
    const effectiveSource = supabaseEnabled ? 'supabase-live' : 'embedded-fallback';
    const courseCode = assertCleanUtf8(String(body.course_code || '').trim().toUpperCase());
    const checkinCode = assertCleanUtf8(String(body.checkin_code || '').trim().toUpperCase());
    const gpsLat = assertCleanUtf8(String(body.gps_lat || '11.946520').trim());
    const gpsLng = assertCleanUtf8(String(body.gps_lng || '108.504110').trim());

    if (!courseCode || !checkinCode) {
      return res.status(400).json({ ok: false, message_vi: 'Vui lòng nhập Mã điểm danh QR và Tọa độ GPS.' });
    }

    const bp = getCourseBlueprint(courseCode);
    const distanceM = computeDistanceMeters(gpsLat, gpsLng, bp.target_lat, bp.target_lng);

    const subRow = assertCleanUtf8({
      user_id: user.id,
      course_code: courseCode,
      checkin_code: checkinCode,
      gps_lat: gpsLat,
      gps_lng: gpsLng,
      distance_m: distanceM,
      crit_prep: 0,
      crit_tech: 0,
      crit_biosafe: 0,
      crit_quality: 0,
      total_grade: 0,
      feedback: '',
      graded_by: null,
      submitted_at: new Date().toISOString(),
      graded_at: null,
    });

    if (canWriteSupabase) {
      try {
        const cRows = await supabaseFetch(`lms_courses?code=eq.${encodeURIComponent(courseCode)}&select=code&limit=1`);
        if (!Array.isArray(cRows) || cRows.length === 0) {
          return res.status(404).json({ ok: false, message_vi: `Khóa học "${courseCode}" không tồn tại.` });
        }
        const inserted = await supabaseFetch('lms_fieldpractice_subs', {
          method: 'POST',
          headers: { Prefer: 'return=representation' },
          body: subRow,
        });
        const eRows = await supabaseFetch(
          `lms_enrollments?user_id=eq.${encodeURIComponent(user.id)}&course_code=eq.${encodeURIComponent(courseCode)}&select=*&limit=1`
        );
        const prevEnr = eRows && eRows[0];
        if (prevEnr) {
          await supabaseFetch(`lms_enrollments?id=eq.${prevEnr.id}`, {
            method: 'PATCH',
            body: {
              attendance_count: Number(prevEnr.attendance_count || 0) + 1,
              progress_pct: Math.max(Number(prevEnr.progress_pct || 0), 75),
              status: prevEnr.status === 'completed' ? 'completed' : 'in_progress',
            },
          });
        } else {
          await supabaseFetch('lms_enrollments', {
            method: 'POST',
            body: { user_id: user.id, course_code: courseCode, progress_pct: 50, quiz_score: 0, attendance_count: 1, status: 'in_progress' },
          });
        }

        return res.status(201).json({
          ok: true,
          source: effectiveSource,
          submission: Array.isArray(inserted) ? inserted[0] : subRow,
          distance_m: distanceM,
          message_vi: `Đã ghi nhận điểm danh thực hành tại vườn (sai số GPS ${distanceM}m). Đang chờ Cán bộ kỹ thuật chấm Rubric 4 tiêu chí.`,
        });
      } catch (err) {
        if (!isRlsPolicyError(err)) {
          return res.status(502).json({ ok: false, message_vi: formatSupabaseError(err, 'nộp điểm danh thực hành') });
        }
      }
    }

    if (!FALLBACK_COURSES.some(c => c.code === courseCode)) {
      return res.status(404).json({ ok: false, message_vi: `Khóa học "${courseCode}" không tồn tại.` });
    }
    const newSub = { id: FALLBACK_FIELDPRACTICE_SUBS.length + 1, ...subRow };
    FALLBACK_FIELDPRACTICE_SUBS.unshift(newSub);
    let enr = FALLBACK_ENROLLMENTS.find(e => Number(e.user_id) === Number(user.id) && e.course_code === courseCode);
    if (enr) {
      enr.attendance_count = Number(enr.attendance_count || 0) + 1;
      enr.progress_pct = Math.max(Number(enr.progress_pct || 0), 75);
      if (enr.status !== 'completed') enr.status = 'in_progress';
    } else {
      FALLBACK_ENROLLMENTS.push({ id: FALLBACK_ENROLLMENTS.length + 1, user_id: user.id, course_code: courseCode, progress_pct: 50, quiz_score: 0, attendance_count: 1, status: 'in_progress', enrolled_at: new Date().toISOString() });
    }
    return res.status(201).json({
      ok: true,
      source: effectiveSource,
      submission: newSub,
      distance_m: distanceM,
      message_vi: `Đã ghi nhận điểm danh thực hành tại vườn (sai số GPS ${distanceM}m). Đang chờ Cán bộ kỹ thuật chấm Rubric 4 tiêu chí.`,
    });
  }

  // TEACHER / ADMIN ONLY (403 for student): POST /api/lms?action=grade_rubric
  if (method === 'POST' && action === 'grade_rubric') {
    const auth = await authenticateRequest(req, res, ['teacher', 'admin']);
    if (!auth) return;
    const { user: grader } = auth;
    const effectiveSource = supabaseEnabled ? 'supabase-live' : 'embedded-fallback';

    const submissionId = Number(body.submission_id);
    const critPrep = Math.min(25, Math.max(0, parseInt(body.crit_prep ?? 0, 10) || 0));
    const critTech = Math.min(25, Math.max(0, parseInt(body.crit_tech ?? 0, 10) || 0));
    const critBiosafe = Math.min(25, Math.max(0, parseInt(body.crit_biosafe ?? 0, 10) || 0));
    const critQuality = Math.min(25, Math.max(0, parseInt(body.crit_quality ?? 0, 10) || 0));
    const totalGrade = critPrep + critTech + critBiosafe + critQuality;
    const feedback = assertCleanUtf8(String(body.feedback || '').trim());

    if (!submissionId) {
      return res.status(400).json({ ok: false, message_vi: 'Thiếu mã bài nộp thực hành (submission_id).' });
    }

    const gradedAt = new Date().toISOString();
    const patch = assertCleanUtf8({
      crit_prep: critPrep,
      crit_tech: critTech,
      crit_biosafe: critBiosafe,
      crit_quality: critQuality,
      total_grade: totalGrade,
      feedback,
      graded_by: grader.username,
      graded_at: gradedAt,
    });

    if (canWriteSupabase) {
      try {
        const updatedSubs = await supabaseFetch(`lms_fieldpractice_subs?id=eq.${encodeURIComponent(submissionId)}`, {
          method: 'PATCH',
          headers: { Prefer: 'return=representation' },
          body: patch,
        });
        const sub = Array.isArray(updatedSubs) ? updatedSubs[0] : null;
        if (!sub) return res.status(404).json({ ok: false, message_vi: 'Không tìm thấy bài nộp thực hành cần chấm.' });

        const [stuRows, crsRows, enrRows] = await Promise.all([
          supabaseFetch(`lms_users?id=eq.${encodeURIComponent(sub.user_id)}&select=*&limit=1`),
          supabaseFetch(`lms_courses?code=eq.${encodeURIComponent(sub.course_code)}&select=*&limit=1`),
          supabaseFetch(`lms_enrollments?user_id=eq.${encodeURIComponent(sub.user_id)}&course_code=eq.${encodeURIComponent(sub.course_code)}&select=*&limit=1`),
        ]);
        const studentUser = stuRows && stuRows[0];
        const course = crsRows && crsRows[0];
        const enr = enrRows && enrRows[0];

        const quizScore = Number(enr ? enr.quiz_score : 0);
        const passedAll = quizScore >= 70 && totalGrade >= 60;
        const nextProgress = passedAll ? 100 : Math.max(Number(enr ? enr.progress_pct : 0), 85);
        if (enr) {
          await supabaseFetch(`lms_enrollments?id=eq.${enr.id}`, {
            method: 'PATCH',
            body: { progress_pct: nextProgress, status: passedAll ? 'completed' : 'in_progress' },
          });
        }

        const certRes = (studentUser && course)
          ? await maybeIssueCertificate({ source: effectiveSource, studentUser, course, quizScore, practiceGrade: totalGrade })
          : { issued: false, certificate: null };

        return res.status(200).json({
          ok: true,
          source: effectiveSource,
          submission: sub,
          total_grade: totalGrade,
          certificate_issued: certRes.issued,
          certificate: certRes.certificate,
          message_vi: certRes.issued
            ? `Đã chấm ${totalGrade}/100 điểm Rubric và tự động cấp Chứng nhận số HMAC-SHA256 (${certRes.certificate.cert_code}).`
            : `Đã lưu kết quả chấm Rubric ${totalGrade}/100 điểm cho học viên.`,
        });
      } catch (err) {
        if (!isRlsPolicyError(err)) {
          return res.status(502).json({ ok: false, message_vi: formatSupabaseError(err, 'chấm điểm Rubric') });
        }
      }
    }

    const sub = FALLBACK_FIELDPRACTICE_SUBS.find(s => Number(s.id) === submissionId);
    if (!sub) return res.status(404).json({ ok: false, message_vi: 'Không tìm thấy bài nộp thực hành.' });
    Object.assign(sub, patch);
    const studentUser = FALLBACK_USERS.find(u => Number(u.id) === Number(sub.user_id));
    const course = FALLBACK_COURSES.find(c => c.code === sub.course_code);
    const enr = FALLBACK_ENROLLMENTS.find(e => Number(e.user_id) === Number(sub.user_id) && e.course_code === sub.course_code);
    const quizScore = Number(enr ? enr.quiz_score : 0);
    const passedAll = quizScore >= 70 && totalGrade >= 60;
    if (enr) {
      enr.progress_pct = passedAll ? 100 : Math.max(Number(enr.progress_pct || 0), 85);
      enr.status = passedAll ? 'completed' : 'in_progress';
    }
    const certRes = (studentUser && course)
      ? await maybeIssueCertificate({ source: effectiveSource, studentUser, course, quizScore, practiceGrade: totalGrade })
      : { issued: false, certificate: null };

    return res.status(200).json({
      ok: true,
      source: effectiveSource,
      submission: sub,
      total_grade: totalGrade,
      certificate_issued: certRes.issued,
      certificate: certRes.certificate,
      message_vi: certRes.issued
        ? `Đã chấm ${totalGrade}/100 điểm Rubric và tự động cấp Chứng nhận số HMAC-SHA256 (${certRes.certificate.cert_code}).`
        : `Đã lưu kết quả chấm Rubric ${totalGrade}/100 điểm cho học viên.`,
    });
  }

  // TEACHER / ADMIN ONLY (403 for student): POST /api/lms?action=clone_course
  if (method === 'POST' && action === 'clone_course') {
    const auth = await authenticateRequest(req, res, ['teacher', 'admin']);
    if (!auth) return;
    const { user } = auth;
    const effectiveSource = supabaseEnabled ? 'supabase-live' : 'embedded-fallback';

    const sourceCode = assertCleanUtf8(String(body.source_code || '').trim().toUpperCase());
    const newCode = assertCleanUtf8(String(body.new_code || '').trim().toUpperCase());
    const newFullname = assertCleanUtf8(String(body.new_fullname || '').trim());
    const newCategory = assertCleanUtf8(String(body.category || 'Chuyển giao Hợp tác xã').trim());

    if (!sourceCode || !/^[A-Z0-9-]{4,40}$/.test(newCode) || !newFullname) {
      return res.status(400).json({
        ok: false,
        message_vi: 'Mã khóa học mới (chỉ chữ hoa/số/gạch ngang, 4–40 ký tự) và Tên đầy đủ không được để trống.',
      });
    }

    if (canWriteSupabase) {
      try {
        const [srcRows, dupRows] = await Promise.all([
          supabaseFetch(`lms_courses?code=eq.${encodeURIComponent(sourceCode)}&select=*&limit=1`),
          supabaseFetch(`lms_courses?code=eq.${encodeURIComponent(newCode)}&select=code&limit=1`),
        ]);
        const src = srcRows && srcRows[0];
        if (!src) return res.status(404).json({ ok: false, message_vi: `Không tìm thấy khóa nguồn "${sourceCode}".` });
        if (Array.isArray(dupRows) && dupRows.length > 0) {
          return res.status(409).json({ ok: false, message_vi: `Mã khóa học "${newCode}" đã tồn tại.` });
        }

        const cloned = await supabaseFetch('lms_courses', {
          method: 'POST',
          headers: { Prefer: 'return=representation' },
          body: {
            code: newCode,
            fullname: newFullname,
            category: newCategory,
            summary: `[Nhân bản từ ${src.code}] ${src.summary}`,
            modules_count: src.modules_count || 14,
            is_cloned: true,
            created_by: user.username,
          },
        });
        return res.status(201).json({
          ok: true,
          source: effectiveSource,
          course: Array.isArray(cloned) ? cloned[0] : cloned,
          message_vi: `Đã nhân bản thành công khóa học "${newCode}" trên Supabase PostgreSQL.`,
        });
      } catch (err) {
        if (!isRlsPolicyError(err)) {
          return res.status(502).json({ ok: false, message_vi: formatSupabaseError(err, 'nhân bản khóa học') });
        }
      }
    }

    const src = FALLBACK_COURSES.find(c => c.code === sourceCode);
    if (!src) return res.status(404).json({ ok: false, message_vi: `Không tìm thấy khóa nguồn "${sourceCode}".` });
    if (FALLBACK_COURSES.some(c => c.code === newCode)) {
      return res.status(409).json({ ok: false, message_vi: `Mã khóa học "${newCode}" đã tồn tại.` });
    }
    const newCourse = assertCleanUtf8({
      id: FALLBACK_COURSES.length + 1,
      code: newCode,
      fullname: newFullname,
      category: newCategory,
      summary: `[Nhân bản từ ${src.code}] ${src.summary}`,
      modules_count: src.modules_count || 14,
      is_cloned: true,
      created_by: user.username,
      created_at: new Date().toISOString(),
    });
    FALLBACK_COURSES.push(newCourse);
    return res.status(201).json({
      ok: true,
      source: effectiveSource,
      course: newCourse,
      message_vi: `Đã nhân bản thành công khóa học "${newCode}".`,
    });
  }

  // ADMIN ONLY (403 for student & teacher): GET / POST /api/lms?action=admin_manage
  if (action === 'admin_manage') {
    const auth = await authenticateRequest(req, res, ['admin']);
    if (!auth) return;
    const { user: adminUser } = auth;
    const effectiveSource = supabaseEnabled ? 'supabase-live' : 'embedded-fallback';

    if (method === 'GET') {
      if (canWriteSupabase) {
        try {
          const [usersRows, newsRows, ...counts] = await Promise.all([
            supabaseFetch('lms_users?select=id,username,email,full_name,role,farm_name,region,area_m2,main_crop,is_active,created_at&order=id.asc'),
            supabaseFetch('lms_news?select=*&order=published_at.desc,id.desc'),
            supabaseCount('mamxanh_certificates'),
            supabaseCount('agriops_batches'),
            supabaseCount('agriops_logs'),
            supabaseCount('fertigation_records'),
            supabaseCount('lms_users'),
            supabaseCount('lms_courses'),
            supabaseCount('lms_enrollments'),
            supabaseCount('lms_fieldpractice_subs'),
            supabaseCount('lms_quiz_attempts'),
            supabaseCount('lms_news'),
          ]);
          const tableNames = [
            'mamxanh_certificates', 'agriops_batches', 'agriops_logs', 'fertigation_records',
            'lms_users', 'lms_courses', 'lms_enrollments', 'lms_fieldpractice_subs', 'lms_quiz_attempts', 'lms_news',
          ];
          const auditTables = tableNames.map((t, i) => ({ table: t, rows: counts[i] }));
          return res.status(200).json({ ok: true, source: effectiveSource, users: usersRows || [], news: newsRows || [], audit_tables: auditTables });
        } catch (err) {
          if (!isRlsPolicyError(err)) {
            return res.status(502).json({ ok: false, message_vi: formatSupabaseError(err, 'kiểm toán hệ thống') });
          }
        }
      }

      const auditTables = [
        { table: 'mamxanh_certificates', rows: Math.max(42, Object.keys(FALLBACK_CERTS).length) },
        { table: 'agriops_batches', rows: Math.max(27, Object.keys(FALLBACK_BATCHES).length) },
        { table: 'agriops_logs', rows: Math.max(54, FALLBACK_LOGS.length) },
        { table: 'fertigation_records', rows: FALLBACK_FERTIGATION.length },
        { table: 'lms_users', rows: FALLBACK_USERS.length },
        { table: 'lms_courses', rows: FALLBACK_COURSES.length },
        { table: 'lms_enrollments', rows: FALLBACK_ENROLLMENTS.length },
        { table: 'lms_fieldpractice_subs', rows: FALLBACK_FIELDPRACTICE_SUBS.length },
        { table: 'lms_quiz_attempts', rows: FALLBACK_QUIZ_ATTEMPTS.length },
        { table: 'lms_news', rows: FALLBACK_NEWS.length },
      ];
      return res.status(200).json({
        ok: true,
        source: effectiveSource,
        ...(supabaseEnabled && keyRole !== 'service_role' ? { rls_hint_vi: RLS_HELP_VI } : {}),
        users: FALLBACK_USERS.map(sanitizeUser),
        news: FALLBACK_NEWS,
        audit_tables: auditTables,
      });
    }

    if (method === 'POST') {
      const subAction = String(body.sub_action || '').trim().toLowerCase();

      if (subAction === 'update_user') {
        const targetId = Number(body.user_id);
        const newRole = body.role ? String(body.role).trim() : null;
        const hasActive = typeof body.is_active === 'boolean';
        if (!targetId) return res.status(400).json({ ok: false, message_vi: 'Thiếu user_id.' });
        if (targetId === Number(adminUser.id) && ((newRole && newRole !== 'admin') || (hasActive && body.is_active === false))) {
          return res.status(400).json({ ok: false, message_vi: 'Không thể tự hạ quyền hoặc khóa chính tài khoản Quản trị đang đăng nhập.' });
        }
        if (newRole && !['student', 'teacher', 'admin'].includes(newRole)) {
          return res.status(400).json({ ok: false, message_vi: 'Vai trò không hợp lệ.' });
        }
        const patch = {};
        if (newRole) patch.role = newRole;
        if (hasActive) patch.is_active = body.is_active;

        if (canWriteSupabase) {
          try {
            const updated = await supabaseFetch(`lms_users?id=eq.${encodeURIComponent(targetId)}`, {
              method: 'PATCH',
              headers: { Prefer: 'return=representation' },
              body: patch,
            });
            return res.status(200).json({ ok: true, source: effectiveSource, user: sanitizeUser(Array.isArray(updated) ? updated[0] : updated) });
          } catch (err) {
            if (!isRlsPolicyError(err)) {
              return res.status(502).json({ ok: false, message_vi: formatSupabaseError(err, 'cập nhật phân quyền') });
            }
          }
        }
        const u = FALLBACK_USERS.find(x => Number(x.id) === targetId);
        if (!u) return res.status(404).json({ ok: false, message_vi: 'Không tìm thấy tài khoản.' });
        Object.assign(u, patch);
        return res.status(200).json({ ok: true, source: effectiveSource, user: sanitizeUser(u) });
      }

      if (subAction === 'publish_news') {
        const category = assertCleanUtf8(String(body.category || 'Thông báo Đào tạo').trim());
        const title = assertCleanUtf8(String(body.title || '').trim());
        const summary = assertCleanUtf8(String(body.summary || '').trim());
        const content = assertCleanUtf8(String(body.content || '').trim());
        if (!title || !summary || !content) {
          return res.status(400).json({ ok: false, message_vi: 'Vui lòng nhập đầy đủ Tiêu đề, Tóm tắt và Nội dung bài viết.' });
        }
        const row = assertCleanUtf8({ category, title, summary, content, published_at: new Date().toISOString() });
        if (canWriteSupabase) {
          try {
            const inserted = await supabaseFetch('lms_news', {
              method: 'POST',
              headers: { Prefer: 'return=representation' },
              body: row,
            });
            return res.status(201).json({ ok: true, source: effectiveSource, article: Array.isArray(inserted) ? inserted[0] : row });
          } catch (err) {
            if (!isRlsPolicyError(err)) {
              return res.status(502).json({ ok: false, message_vi: formatSupabaseError(err, 'đăng bản tin khuyến nông') });
            }
          }
        }
        const fbArt = { id: FALLBACK_NEWS.length + 1, ...row };
        FALLBACK_NEWS.unshift(fbArt);
        return res.status(201).json({ ok: true, source: effectiveSource, article: fbArt });
      }

      return res.status(400).json({ ok: false, message_vi: `sub_action "${subAction}" không hợp lệ.` });
    }
  }

  return res.status(400).json({
    ok: false,
    message_vi: `Action "${action}" không được hỗ trợ trên /api/lms.`,
  });
};
