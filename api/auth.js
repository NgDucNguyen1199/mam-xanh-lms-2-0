const {
  hasSupabaseConfig,
  supabaseFetch,
  parseJsonBody,
  hashPassword,
  verifyPassword,
  signJwt,
  sanitizeUser,
  authenticateRequest,
  FALLBACK_USERS,
  FALLBACK_ENROLLMENTS,
} = require('./_supabase');

const STARTER_COURSES = ['CHAMSOC01', 'IOTNHAMANG01', 'DAUTAY-CNC-01'];

module.exports = async function handler(req, res) {
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.setHeader('Cache-Control', 'no-store');

  const method = String(req.method || 'GET').toUpperCase();
  const body = method === 'POST' ? parseJsonBody(req) : {};
  const action = String((req.query && req.query.action) || body.action || '').trim().toLowerCase();
  const supabaseEnabled = hasSupabaseConfig();

  // GET /api/auth?action=me
  if (method === 'GET' && action === 'me') {
    const auth = await authenticateRequest(req, res);
    if (!auth) return;
    return res.status(200).json({
      ok: true,
      source: auth.source,
      user: sanitizeUser(auth.user),
    });
  }

  // POST /api/auth?action=register
  if (method === 'POST' && action === 'register') {
    const username = String(body.username || '').trim().toLowerCase();
    const email = String(body.email || '').trim().toLowerCase();
    const password = String(body.password || '');
    const fullName = String(body.full_name || '').trim();
    const farmName = String(body.farm_name || 'Nông trại Mầm Xanh').trim();
    const region = String(body.region || 'Lâm Đồng').trim();
    const areaM2 = Math.max(50, parseInt(body.area_m2 || 1000, 10) || 1000);
    const mainCrop = String(body.main_crop || 'Rau & Dâu tây Công nghệ cao').trim();

    if (!/^[a-z0-9_.-]{3,40}$/.test(username)) {
      return res.status(400).json({
        ok: false,
        message_vi: 'Tên đăng nhập phải từ 3–40 ký tự (chữ thường, số, dấu gạch dưới hoặc chấm).',
        message_en: 'Username must be 3-40 lowercase alphanumeric/._- characters.',
      });
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      return res.status(400).json({
        ok: false,
        message_vi: 'Địa chỉ Email không hợp lệ.',
        message_en: 'Invalid email address.',
      });
    }
    if (password.length < 6) {
      return res.status(400).json({
        ok: false,
        message_vi: 'Mật khẩu phải có ít nhất 6 ký tự.',
        message_en: 'Password must be at least 6 characters.',
      });
    }
    if (!fullName) {
      return res.status(400).json({
        ok: false,
        message_vi: 'Vui lòng nhập Họ và tên nông hộ / học viên.',
        message_en: 'Please enter full_name.',
      });
    }

    const { salt, password_hash } = hashPassword(password);

    if (supabaseEnabled) {
      try {
        const dupUser = await supabaseFetch(
          `lms_users?or=(username.eq.${encodeURIComponent(username)},email.eq.${encodeURIComponent(email)})&select=id,username,email&limit=1`
        );
        if (Array.isArray(dupUser) && dupUser.length > 0) {
          return res.status(409).json({
            ok: false,
            message_vi: 'Tên đăng nhập hoặc Email đã tồn tại trong hệ thống.',
            message_en: 'Username or email already exists.',
          });
        }

        const inserted = await supabaseFetch('lms_users', {
          method: 'POST',
          headers: { Prefer: 'return=representation' },
          body: {
            username,
            email,
            password_hash,
            salt,
            full_name: fullName,
            role: 'student',
            farm_name: farmName,
            region,
            area_m2: areaM2,
            main_crop: mainCrop,
            is_active: true,
          },
        });
        const newUser = Array.isArray(inserted) ? inserted[0] : inserted;

        // Auto-enroll starter courses
        const enrollRows = STARTER_COURSES.map(code => ({
          user_id: newUser.id,
          course_code: code,
          progress_pct: 10,
          quiz_score: 0,
          attendance_count: 0,
          status: 'enrolled',
        }));
        await supabaseFetch('lms_enrollments', {
          method: 'POST',
          headers: { Prefer: 'return=representation,resolution=ignore-duplicates' },
          body: enrollRows,
        });

        const token = signJwt({ sub: newUser.id, username: newUser.username, role: newUser.role });
        return res.status(201).json({
          ok: true,
          source: 'supabase-live',
          token,
          user: sanitizeUser(newUser),
          enrolled_starter_courses: STARTER_COURSES,
        });
      } catch (err) {
        return res.status(502).json({
          ok: false,
          source: 'supabase-live',
          message_vi: `Lỗi đăng ký tài khoản trên Supabase: ${err.message}`,
          message_en: `Supabase registration error: ${err.message}`,
        });
      }
    }

    // Fallback registration
    if (FALLBACK_USERS.some(u => u.username === username || u.email === email)) {
      return res.status(409).json({
        ok: false,
        source: 'embedded-fallback',
        message_vi: 'Tên đăng nhập hoặc Email đã tồn tại trong hệ thống.',
        message_en: 'Username or email already exists.',
      });
    }
    const nextId = FALLBACK_USERS.reduce((m, u) => Math.max(m, u.id), 0) + 1;
    const newUser = {
      id: nextId,
      username,
      email,
      password_hash,
      salt,
      full_name: fullName,
      role: 'student',
      farm_name: farmName,
      region,
      area_m2: areaM2,
      main_crop: mainCrop,
      is_active: true,
      created_at: new Date().toISOString(),
    };
    FALLBACK_USERS.push(newUser);
    let nextEnrollId = FALLBACK_ENROLLMENTS.reduce((m, e) => Math.max(m, e.id), 0) + 1;
    for (const code of STARTER_COURSES) {
      FALLBACK_ENROLLMENTS.push({
        id: nextEnrollId++,
        user_id: newUser.id,
        course_code: code,
        progress_pct: 10,
        quiz_score: 0,
        attendance_count: 0,
        status: 'enrolled',
        enrolled_at: new Date().toISOString(),
      });
    }
    const token = signJwt({ sub: newUser.id, username: newUser.username, role: newUser.role });
    return res.status(201).json({
      ok: true,
      source: 'embedded-fallback',
      token,
      user: sanitizeUser(newUser),
      enrolled_starter_courses: STARTER_COURSES,
    });
  }

  // POST /api/auth?action=login
  if (method === 'POST' && action === 'login') {
    const identifier = String(body.identifier || body.username || body.email || '').trim().toLowerCase();
    const password = String(body.password || '');

    if (!identifier || !password) {
      return res.status(400).json({
        ok: false,
        message_vi: 'Vui lòng nhập Tên đăng nhập/Email và Mật khẩu.',
        message_en: 'Please provide username/email and password.',
      });
    }

    let foundUser = null;
    let source = 'embedded-fallback';
    if (supabaseEnabled) {
      try {
        const rows = await supabaseFetch(
          `lms_users?or=(username.eq.${encodeURIComponent(identifier)},email.eq.${encodeURIComponent(identifier)})&select=*&limit=1`
        );
        if (Array.isArray(rows) && rows.length > 0) {
          foundUser = rows[0];
        }
        source = 'supabase-live';
      } catch (err) {
        return res.status(502).json({
          ok: false,
          source: 'supabase-live',
          message_vi: `Lỗi kết nối Supabase Auth: ${err.message}`,
          message_en: err.message,
        });
      }
    } else {
      foundUser = FALLBACK_USERS.find(
        u => u.username.toLowerCase() === identifier || u.email.toLowerCase() === identifier
      ) || null;
    }

    if (!foundUser || !verifyPassword(password, foundUser.salt, foundUser.password_hash)) {
      return res.status(401).json({
        ok: false,
        source,
        message_vi: 'Tên đăng nhập/Email hoặc Mật khẩu không chính xác.',
        message_en: 'Invalid username/email or password.',
      });
    }

    if (!foundUser.is_active) {
      return res.status(403).json({
        ok: false,
        source,
        message_vi: 'Tài khoản này đang bị tạm khóa. Vui lòng liên hệ Quản trị viên.',
        message_en: 'Account is disabled.',
      });
    }

    const token = signJwt({ sub: foundUser.id, username: foundUser.username, role: foundUser.role });
    return res.status(200).json({
      ok: true,
      source,
      token,
      user: sanitizeUser(foundUser),
    });
  }

  // POST /api/auth?action=update_profile
  if (method === 'POST' && action === 'update_profile') {
    const auth = await authenticateRequest(req, res);
    if (!auth) return;
    const currentUser = auth.user;

    const patch = {
      full_name: String(body.full_name || currentUser.full_name).trim(),
      farm_name: String(body.farm_name ?? currentUser.farm_name).trim(),
      region: String(body.region || currentUser.region).trim(),
      area_m2: Math.max(10, parseInt(body.area_m2 || currentUser.area_m2, 10) || 1000),
      main_crop: String(body.main_crop ?? currentUser.main_crop).trim(),
    };

    if (body.new_password) {
      const currentPwd = String(body.current_password || '');
      const newPwd = String(body.new_password || '');
      if (!verifyPassword(currentPwd, currentUser.salt, currentUser.password_hash)) {
        return res.status(400).json({
          ok: false,
          message_vi: 'Mật khẩu hiện tại không chính xác.',
          message_en: 'Current password is incorrect.',
        });
      }
      if (newPwd.length < 6) {
        return res.status(400).json({
          ok: false,
          message_vi: 'Mật khẩu mới phải từ 6 ký tự trở lên.',
          message_en: 'New password must be at least 6 characters.',
        });
      }
      const hashed = hashPassword(newPwd);
      patch.salt = hashed.salt;
      patch.password_hash = hashed.password_hash;
    }

    if (auth.source === 'supabase-live') {
      try {
        const updated = await supabaseFetch(`lms_users?id=eq.${encodeURIComponent(currentUser.id)}`, {
          method: 'PATCH',
          headers: { Prefer: 'return=representation' },
          body: patch,
        });
        const row = Array.isArray(updated) ? updated[0] : { ...currentUser, ...patch };
        return res.status(200).json({
          ok: true,
          source: 'supabase-live',
          user: sanitizeUser(row),
          message_vi: 'Đã cập nhật hồ sơ & mật khẩu lên Supabase PostgreSQL.',
        });
      } catch (err) {
        return res.status(502).json({
          ok: false,
          message_vi: `Lỗi cập nhật hồ sơ: ${err.message}`,
        });
      }
    }

    Object.assign(currentUser, patch);
    return res.status(200).json({
      ok: true,
      source: 'embedded-fallback',
      user: sanitizeUser(currentUser),
      message_vi: 'Đã cập nhật hồ sơ (Fallback Mode).',
    });
  }

  return res.status(400).json({
    ok: false,
    message_vi: `Action "${action}" không hợp lệ trên /api/auth.`,
    message_en: `Unsupported auth action "${action}".`,
  });
};
