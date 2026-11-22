const crypto = require('crypto');
const { sendWelcomeEmail } = require('./_mailer');
const {
  RLS_HELP_VI,
  RLS_HELP_EN,
  assertCleanUtf8,
  setJsonHeaders,
  getSupabaseCreds,
  getSupabaseOAuthConfig,
  hasSupabaseConfig,
  inspectServiceKeyRole,
  isRlsPolicyError,
  formatSupabaseError,
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

function cleanUsernameFromEmail(email) {
  const rawLocal = String(email || '').split('@')[0] || 'nongho';
  const ascii = rawLocal
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[đĐ]/g, 'd')
    .toLowerCase()
    .replace(/[^a-z0-9_.-]+/g, '_')
    .replace(/^[._-]+|[._-]+$/g, '');
  let base = ascii || 'nongho';
  if (base.length < 3) base = `nongho_${base}`;
  if (base.length > 34) base = base.slice(0, 34).replace(/[._-]+$/g, '') || 'nongho';
  return base;
}

async function generateUniqueUsername(email, supabaseEnabled) {
  const base = cleanUsernameFromEmail(email);
  for (let attempt = 0; attempt < 8; attempt++) {
    const cand = attempt === 0 ? base : `${base}_${Math.floor(1000 + Math.random() * 9000)}`;
    const takenInFallback = FALLBACK_USERS.some(u => u.username.toLowerCase() === cand);
    if (takenInFallback) continue;
    if (supabaseEnabled) {
      try {
        const rows = await supabaseFetch(`lms_users?username=eq.${encodeURIComponent(cand)}&select=id&limit=1`);
        if (Array.isArray(rows) && rows.length > 0) continue;
      } catch {
        // Ignore transient or RLS errors during username check
      }
    }
    return cand;
  }
  return `${base}_${Date.now().toString().slice(-5)}`;
}

function enrollInFallback(userId) {
  let nextEnrollId = FALLBACK_ENROLLMENTS.reduce((m, e) => Math.max(m, Number(e.id)), 0) + 1;
  for (const code of STARTER_COURSES) {
    if (!FALLBACK_ENROLLMENTS.some(e => Number(e.user_id) === Number(userId) && e.course_code === code)) {
      FALLBACK_ENROLLMENTS.push({
        id: nextEnrollId++,
        user_id: userId,
        course_code: code,
        progress_pct: 10,
        quiz_score: 0,
        attendance_count: 0,
        status: 'enrolled',
        enrolled_at: new Date().toISOString(),
      });
    }
  }
}

function registerInFallback({ username, email, password_hash, salt, fullName, farmName, region, areaM2, mainCrop }) {
  const nextId = FALLBACK_USERS.reduce((m, u) => Math.max(m, Number(u.id)), 0) + 1;
  const newUser = assertCleanUtf8({
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
  });
  FALLBACK_USERS.push(newUser);
  enrollInFallback(newUser.id);
  return newUser;
}

async function persistNewUser(userData, supabaseEnabled) {
  const { username, email, password_hash, salt, fullName, farmName, region, areaM2, mainCrop } = userData;

  if (supabaseEnabled) {
    try {
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
      return { user: newUser, source: 'supabase-live', rlsFallback: false };
    } catch (err) {
      if (isRlsPolicyError(err)) {
        const fallbackUser = registerInFallback(userData);
        return { user: fallbackUser, source: 'supabase-live', rlsFallback: true };
      }
      throw err;
    }
  }

  const fallbackUser = registerInFallback(userData);
  return { user: fallbackUser, source: 'embedded-fallback', rlsFallback: false };
}

async function findUserByEmailOrUsername({ email, username }, supabaseEnabled) {
  if (supabaseEnabled) {
    try {
      const filter = username
        ? `or=(username.eq.${encodeURIComponent(username)},email.eq.${encodeURIComponent(email)})`
        : `email=eq.${encodeURIComponent(email)}`;
      const rows = await supabaseFetch(`lms_users?${filter}&select=*&limit=1`);
      if (Array.isArray(rows) && rows.length > 0) {
        return { user: rows[0], source: 'supabase-live', rlsFallback: false };
      }
    } catch (err) {
      if (!isRlsPolicyError(err)) throw err;
    }
  }
  const fbUser = FALLBACK_USERS.find(
    u => u.email.toLowerCase() === email || (username && u.username.toLowerCase() === username)
  ) || null;
  return {
    user: fbUser,
    source: supabaseEnabled ? 'supabase-live' : 'embedded-fallback',
    rlsFallback: Boolean(fbUser && supabaseEnabled),
  };
}

module.exports = async function handler(req, res) {
  setJsonHeaders(res);

  const method = String(req.method || 'GET').toUpperCase();
  const body = method === 'POST' ? parseJsonBody(req) : {};
  const action = String((req.query && req.query.action) || body.action || '').trim().toLowerCase();
  const supabaseEnabled = hasSupabaseConfig();
  const keyRole = inspectServiceKeyRole();

  // GET /api/auth?action=oauth_config
  if (method === 'GET' && action === 'oauth_config') {
    const oauthCfg = getSupabaseOAuthConfig();
    if (!oauthCfg.supabaseUrl || !oauthCfg.supabaseAnonKey) {
      return res.status(400).json({
        ok: false,
        code: 'MISSING_SUPABASE_ANON_KEY',
        supabaseUrl: oauthCfg.supabaseUrl || '',
        supabaseAnonKey: '',
        message_vi:
          'Chưa cấu hình biến môi trường SUPABASE_ANON_KEY (hoặc SUPABASE_URL) trên Vercel. Quản trị viên vui lòng vào Vercel Dashboard -> Settings -> Environment Variables để thêm SUPABASE_ANON_KEY từ Supabase (Project Settings -> API -> anon public) và Redeploy.',
        message_en:
          'Missing SUPABASE_ANON_KEY or SUPABASE_URL environment variable on Vercel. Please configure SUPABASE_ANON_KEY in Vercel Project Settings and redeploy.',
      });
    }
    return res.status(200).json({
      ok: true,
      supabaseUrl: oauthCfg.supabaseUrl,
      supabaseAnonKey: oauthCfg.supabaseAnonKey,
    });
  }

  // GET /api/auth?action=me
  if (method === 'GET' && action === 'me') {
    const auth = await authenticateRequest(req, res);
    if (!auth) return;
    return res.status(200).json({
      ok: true,
      source: auth.source,
      key_role: keyRole,
      user: sanitizeUser(auth.user),
    });
  }

  // POST /api/auth?action=register (Simplified 3-field + optional overrides)
  if (method === 'POST' && action === 'register') {
    const rawUsername = assertCleanUtf8(String(body.username || '').trim().toLowerCase());
    const email = assertCleanUtf8(String(body.email || '').trim().toLowerCase());
    const password = String(body.password || '');
    const fullName = assertCleanUtf8(String(body.full_name || '').trim());
    const farmName = assertCleanUtf8(String(body.farm_name || '').trim());
    const region = assertCleanUtf8(String(body.region || 'Lâm Đồng').trim());
    const areaM2 = Math.max(50, parseInt(body.area_m2 || 1000, 10) || 1000);
    const mainCrop = assertCleanUtf8(String(body.main_crop || '').trim());

    if (!fullName) {
      return res.status(400).json({
        ok: false,
        message_vi: 'Vui lòng nhập Họ và tên của bạn.',
        message_en: 'Please enter your full name.',
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
    if (rawUsername && !/^[a-z0-9_.-]{3,40}$/.test(rawUsername)) {
      return res.status(400).json({
        ok: false,
        message_vi: 'Tên đăng nhập phải từ 3–40 ký tự (chữ thường, số, dấu gạch dưới hoặc chấm).',
        message_en: 'Username must be 3-40 lowercase alphanumeric/._- characters.',
      });
    }

    try {
      const existing = await findUserByEmailOrUsername({ email, username: rawUsername }, supabaseEnabled);
      if (existing.user) {
        return res.status(409).json({
          ok: false,
          source: existing.source,
          message_vi: 'Địa chỉ Email (hoặc tên đăng nhập) này đã tồn tại. Vui lòng Đăng nhập hoặc dùng Google/GitHub.',
          message_en: 'Email or username already exists.',
        });
      }

      const username = rawUsername || (await generateUniqueUsername(email, supabaseEnabled));
      const { salt, password_hash } = hashPassword(password);
      const created = await persistNewUser(
        { username, email, password_hash, salt, fullName, farmName, region, areaM2, mainCrop },
        supabaseEnabled
      );
      const token = signJwt({ sub: created.user.id, username: created.user.username, role: created.user.role });
      const welcomeEmail = await sendWelcomeEmail({
        full_name: created.user.full_name,
        username: created.user.username,
        email: created.user.email,
        auth_method: 'Form Đăng ký 3 trường',
      });
      return res.status(201).json({
        ok: true,
        source: created.source,
        ...(created.rlsFallback ? { rls_fallback: true, rls_hint_vi: RLS_HELP_VI, rls_hint_en: RLS_HELP_EN } : {}),
        token,
        user: sanitizeUser(created.user),
        enrolled_starter_courses: STARTER_COURSES,
        welcomeEmail,
        message_vi: welcomeEmail.sent
          ? 'Đăng ký thành công! Email chào mừng đã được gửi tới hộp thư của bạn.'
          : 'Đăng ký tài khoản thành công! Chào mừng bạn đến với Mầm Xanh LMS 2.0.',
      });
    } catch (err) {
      return res.status(502).json({
        ok: false,
        source: supabaseEnabled ? 'supabase-live' : 'embedded-fallback',
        message_vi: formatSupabaseError(err, 'đăng ký tài khoản'),
        message_en: `Registration error: ${err.message}`,
      });
    }
  }

  // POST /api/auth?action=oauth_callback (Verify Supabase OAuth token & sign LMS JWT)
  if (method === 'POST' && action === 'oauth_callback') {
    const accessToken = String(body.access_token || '').trim();
    if (!accessToken) {
      return res.status(400).json({
        ok: false,
        message_vi: 'Thiếu access_token từ cổng xác thực OAuth.',
        message_en: 'Missing OAuth access_token.',
      });
    }

    const oauthCfg = getSupabaseOAuthConfig();
    const creds = getSupabaseCreds();
    const supabaseUrl = oauthCfg.supabaseUrl || creds.url;
    const verifyApiKey = oauthCfg.supabaseAnonKey || creds.key;

    if (!supabaseUrl || !verifyApiKey) {
      return res.status(400).json({
        ok: false,
        code: 'MISSING_SUPABASE_ANON_KEY',
        message_vi:
          'Chưa cấu hình SUPABASE_URL hoặc SUPABASE_ANON_KEY trên Vercel để xác thực OAuth. Vui lòng thiết lập biến môi trường trên Vercel.',
        message_en: 'Missing SUPABASE_URL or SUPABASE_ANON_KEY for OAuth verification.',
      });
    }

    let supaUser = null;
    try {
      const authRes = await fetch(`${supabaseUrl}/auth/v1/user`, {
        method: 'GET',
        headers: {
          Authorization: `Bearer ${accessToken}`,
          apikey: verifyApiKey,
          Accept: 'application/json; charset=utf-8',
        },
      });
      const authText = await authRes.text();
      supaUser = authText ? JSON.parse(authText) : null;
      if (!authRes.ok || !supaUser || !supaUser.id) {
        return res.status(401).json({
          ok: false,
          message_vi: 'Mã phiên đăng nhập OAuth không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.',
          message_en: 'Invalid or expired Supabase OAuth access_token.',
        });
      }
    } catch (err) {
      return res.status(502).json({
        ok: false,
        message_vi: `Không thể xác minh phiên OAuth với Supabase: ${assertCleanUtf8(err.message)}`,
        message_en: `OAuth verification error: ${err.message}`,
      });
    }

    const meta = supaUser.user_metadata || {};
    const identityData =
      Array.isArray(supaUser.identities) && supaUser.identities[0] && supaUser.identities[0].identity_data
        ? supaUser.identities[0].identity_data
        : {};
    const provider = String(
      (supaUser.app_metadata && supaUser.app_metadata.provider) ||
        (Array.isArray(supaUser.identities) && supaUser.identities[0] && supaUser.identities[0].provider) ||
        'oauth'
    ).toLowerCase();

    const rawEmail = String(
      supaUser.email || meta.email || identityData.email || ''
    ).trim().toLowerCase();
    const githubHandle = String(meta.user_name || meta.preferred_username || identityData.user_name || supaUser.id)
      .toLowerCase()
      .replace(/[^a-z0-9._-]/g, '');
    const email = assertCleanUtf8(rawEmail || `${githubHandle || 'oauthuser'}@oauth.${provider}.local`);
    const fullName = assertCleanUtf8(
      String(meta.full_name || meta.name || identityData.full_name || identityData.name || email.split('@')[0]).trim()
    );

    try {
      const existing = await findUserByEmailOrUsername({ email, username: '' }, supabaseEnabled);
      if (existing.user) {
        if (!existing.user.is_active) {
          return res.status(403).json({
            ok: false,
            source: existing.source,
            message_vi: 'Tài khoản này đang bị tạm khóa. Vui lòng liên hệ Quản trị viên.',
            message_en: 'Account is disabled.',
          });
        }
        const token = signJwt({ sub: existing.user.id, username: existing.user.username, role: existing.user.role });
        return res.status(200).json({
          ok: true,
          source: existing.source,
          is_new_user: false,
          provider,
          token,
          user: sanitizeUser(existing.user),
          message_vi: `Đăng nhập thành công qua ${provider.toUpperCase()} (${existing.user.full_name}).`,
        });
      }

      const username = await generateUniqueUsername(email, supabaseEnabled);
      const { salt, password_hash } = hashPassword(crypto.randomBytes(24).toString('hex'));
      const created = await persistNewUser(
        {
          username,
          email,
          password_hash,
          salt,
          fullName,
          farmName: '',
          region: 'Lâm Đồng',
          areaM2: 1000,
          mainCrop: '',
        },
        supabaseEnabled
      );

      const token = signJwt({ sub: created.user.id, username: created.user.username, role: created.user.role });
      const oauthMethodLabel =
        provider === 'google'
          ? 'Google OAuth 2.0'
          : provider === 'github'
            ? 'GitHub OAuth 2.0'
            : `Supabase OAuth (${provider})`;
      const welcomeEmail = await sendWelcomeEmail({
        full_name: created.user.full_name,
        username: created.user.username,
        email: created.user.email,
        auth_method: oauthMethodLabel,
      });
      return res.status(200).json({
        ok: true,
        source: created.source,
        ...(created.rlsFallback ? { rls_fallback: true, rls_hint_vi: RLS_HELP_VI } : {}),
        is_new_user: true,
        provider,
        token,
        user: sanitizeUser(created.user),
        enrolled_starter_courses: STARTER_COURSES,
        welcomeEmail,
        message_vi: welcomeEmail.sent
          ? `Đăng ký thành công qua ${oauthMethodLabel}! Email chào mừng đã được gửi tới hộp thư của bạn.`
          : `Chào mừng ${created.user.full_name}! Đã tạo tài khoản qua ${provider.toUpperCase()} và ghi danh 3 khóa học cơ bản.`,
      });
    } catch (err) {
      return res.status(502).json({
        ok: false,
        source: supabaseEnabled ? 'supabase-live' : 'embedded-fallback',
        message_vi: formatSupabaseError(err, 'đồng bộ tài khoản OAuth'),
        message_en: `OAuth user sync error: ${err.message}`,
      });
    }
  }

  // POST /api/auth?action=login
  if (method === 'POST' && action === 'login') {
    const identifier = assertCleanUtf8(String(body.identifier || body.username || body.email || '').trim().toLowerCase());
    const password = String(body.password || '');

    if (!identifier || !password) {
      return res.status(400).json({
        ok: false,
        message_vi: 'Vui lòng nhập Tên đăng nhập/Email và Mật khẩu.',
        message_en: 'Please provide username/email and password.',
      });
    }

    let foundUser = null;
    let source = supabaseEnabled ? 'supabase-live' : 'embedded-fallback';
    let rlsFallbackUsed = false;

    if (supabaseEnabled) {
      try {
        const rows = await supabaseFetch(
          `lms_users?or=(username.eq.${encodeURIComponent(identifier)},email.eq.${encodeURIComponent(identifier)})&select=*&limit=1`
        );
        if (Array.isArray(rows) && rows.length > 0) {
          foundUser = rows[0];
        }
      } catch (err) {
        if (!isRlsPolicyError(err)) {
          return res.status(502).json({
            ok: false,
            source: 'supabase-live',
            message_vi: formatSupabaseError(err, 'xác thực đăng nhập'),
            message_en: err.message,
          });
        }
      }
    }

    if (!foundUser) {
      foundUser = FALLBACK_USERS.find(
        u => u.username.toLowerCase() === identifier || u.email.toLowerCase() === identifier
      ) || null;
      if (foundUser && supabaseEnabled) {
        rlsFallbackUsed = true;
      }
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
      ...(rlsFallbackUsed ? { rls_fallback: true, rls_hint_vi: RLS_HELP_VI } : {}),
      token,
      user: sanitizeUser(foundUser),
      message_vi: `Đăng nhập thành công (${foundUser.full_name}).`,
    });
  }

  // POST /api/auth?action=update_profile
  if (method === 'POST' && action === 'update_profile') {
    const auth = await authenticateRequest(req, res);
    if (!auth) return;
    const currentUser = auth.user;

    const patch = assertCleanUtf8({
      full_name: String(body.full_name || currentUser.full_name).trim(),
      farm_name: String(body.farm_name ?? currentUser.farm_name).trim(),
      region: String(body.region || currentUser.region).trim(),
      area_m2: Math.max(10, parseInt(body.area_m2 || currentUser.area_m2, 10) || 1000),
      main_crop: String(body.main_crop ?? currentUser.main_crop).trim(),
    });

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

    if (auth.source === 'supabase-live' && keyRole === 'service_role') {
      try {
        const updated = await supabaseFetch(`lms_users?id=eq.${encodeURIComponent(currentUser.id)}`, {
          method: 'PATCH',
          headers: { Prefer: 'return=representation' },
          body: patch,
        });
        const row = Array.isArray(updated) && updated[0] ? updated[0] : { ...currentUser, ...patch };
        return res.status(200).json({
          ok: true,
          source: 'supabase-live',
          user: sanitizeUser(row),
          message_vi: 'Đã cập nhật hồ sơ & mật khẩu lên Supabase PostgreSQL.',
        });
      } catch (err) {
        if (!isRlsPolicyError(err)) {
          return res.status(502).json({
            ok: false,
            message_vi: formatSupabaseError(err, 'cập nhật hồ sơ'),
          });
        }
      }
    }

    Object.assign(currentUser, patch);
    return res.status(200).json({
      ok: true,
      source: auth.source,
      user: sanitizeUser(currentUser),
      message_vi: 'Đã lưu cập nhật hồ sơ thành công.',
    });
  }

  return res.status(400).json({
    ok: false,
    message_vi: `Action "${action}" không hợp lệ trên /api/auth.`,
    message_en: `Unsupported auth action "${action}".`,
  });
};
