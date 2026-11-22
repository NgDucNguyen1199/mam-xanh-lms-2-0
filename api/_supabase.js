const crypto = require('crypto');

const CANONICAL_MOODLE_SECRET = 'MX2_HMAC_2026_VIETGAP_AGRITECH_DIGITAL_SIGNATURE_KEY';
const SECRET = String(process.env.MAMXANH_HMAC_SECRET || CANONICAL_MOODLE_SECRET).trim() || CANONICAL_MOODLE_SECRET;
const HMAC_VERIFICATION_SECRETS = SECRET === CANONICAL_MOODLE_SECRET
  ? [SECRET]
  : [SECRET, CANONICAL_MOODLE_SECRET];

const RLS_HELP_VI =
  'Cấu hình SUPABASE_SERVICE_KEY trên Vercel đang dùng khóa anon/publishable (chỉ có quyền đọc RLS 42501). Vui lòng đổi sang khóa service_role (secret) trong Supabase -> Settings -> API để ghi vĩnh viễn vào PostgreSQL. Hệ thống đã tự động xử lý qua bộ nhớ Cloud Edge.';
const RLS_HELP_EN =
  'SUPABASE_SERVICE_KEY on Vercel is currently set to an anon/publishable key (read-only under RLS 42501). Please switch to the service_role (secret) key in Supabase -> Settings -> API for persistent PostgreSQL writes.';

function assertCleanUtf8(input) {
  if (typeof input === 'string') {
    const normalized = input.normalize('NFC').replace(/\uFFFD/g, '');
    return normalized;
  }
  if (Array.isArray(input)) {
    return input.map(assertCleanUtf8);
  }
  if (input && typeof input === 'object') {
    const out = {};
    for (const [k, v] of Object.entries(input)) {
      out[k] = assertCleanUtf8(v);
    }
    return out;
  }
  return input;
}

function setJsonHeaders(res) {
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.setHeader('Cache-Control', 'no-store');
}

function getSupabaseCreds() {
  const url = String(process.env.SUPABASE_URL || '').trim().replace(/\/+$/, '');
  const key = String(process.env.SUPABASE_SERVICE_KEY || '').trim();
  const anonKey = String(process.env.SUPABASE_ANON_KEY || '').trim();
  return { url, key, anonKey, enabled: Boolean(url && key) };
}

function getSupabaseOAuthConfig() {
  const supabaseUrl = String(process.env.SUPABASE_URL || '').trim().replace(/\/+$/, '');
  const supabaseAnonKey = String(process.env.SUPABASE_ANON_KEY || '').trim();
  return {
    supabaseUrl,
    supabaseAnonKey,
    ok: Boolean(supabaseUrl && supabaseAnonKey),
  };
}

function hasSupabaseConfig() {
  return getSupabaseCreds().enabled;
}

function inspectServiceKeyRole() {
  const { key, enabled } = getSupabaseCreds();
  if (!enabled) return 'none';
  if (key.startsWith('sb_publishable_')) return 'anon';
  if (key.startsWith('sb_secret_')) return 'service_role';
  const parts = key.split('.');
  if (parts.length === 3) {
    try {
      let b64 = parts[1].replace(/-/g, '+').replace(/_/g, '/');
      while (b64.length % 4) b64 += '=';
      const payload = JSON.parse(Buffer.from(b64, 'base64').toString('utf8'));
      if (payload && payload.role) return String(payload.role);
    } catch {
      // Ignore malformed JWT
    }
  }
  return 'service_role';
}

function isRlsPolicyError(err) {
  const msg = String((err && err.message) || '');
  const code = String((err && err.payload && err.payload.code) || '');
  return (
    code === '42501' ||
    /row-level security policy|42501|permission denied|insufficient_privilege/i.test(msg)
  );
}

function formatSupabaseError(err, actionLabelVi = 'thao tác dữ liệu') {
  if (isRlsPolicyError(err)) {
    return `Không thể ${actionLabelVi} trực tiếp lên Supabase do khóa SUPABASE_SERVICE_KEY đang là khóa công khai (anon). Vui lòng cấu hình khóa service_role (secret) trong Supabase -> Settings -> API trên Vercel.`;
  }
  return `Lỗi Supabase khi ${actionLabelVi}: ${assertCleanUtf8(String((err && err.message) || 'Unknown error'))}`;
}

async function supabaseFetch(restPath, options = {}) {
  const { url, key, enabled } = getSupabaseCreds();
  if (!enabled) {
    throw new Error('SUPABASE_URL or SUPABASE_SERVICE_KEY is not configured');
  }

  const headers = {
    apikey: key,
    Authorization: `Bearer ${key}`,
    'Content-Type': 'application/json; charset=utf-8',
    Accept: 'application/json; charset=utf-8',
    ...(options.headers || {}),
  };

  const cleanBody = options.body !== undefined ? assertCleanUtf8(options.body) : undefined;

  const res = await fetch(`${url}/rest/v1/${restPath}`, {
    method: options.method || 'GET',
    headers,
    body: cleanBody !== undefined ? JSON.stringify(cleanBody) : undefined,
  });

  const text = await res.text();
  let json = null;
  try {
    json = text ? JSON.parse(text) : null;
  } catch {
    json = { raw: text };
  }

  if (!res.ok) {
    const rawMsg = (json && (json.message || json.hint || json.details)) || `Supabase HTTP ${res.status}`;
    const err = new Error(assertCleanUtf8(String(rawMsg)));
    err.status = res.status;
    err.payload = json;
    err.isRlsError = isRlsPolicyError(err);
    err.friendlyMessageVi = formatSupabaseError(err);
    throw err;
  }

  return assertCleanUtf8(json);
}

function computeCertHmac(certCode, userId, courseId, score, timeCreated, secretOverride = SECRET) {
  const canonical = [
    String(certCode).trim(),
    String(userId),
    String(courseId),
    Number(score).toFixed(2),
    String(timeCreated),
  ].join('|');
  return crypto.createHmac('sha256', secretOverride || SECRET).update(canonical).digest('hex');
}

function computeBatchHmac(batchCode, region, areaM2, qrToken, secretOverride = SECRET) {
  const canonical = `${String(batchCode).trim()}|${String(region).trim()}|${Number(areaM2)}|${String(qrToken || '').trim()}`;
  return crypto.createHmac('sha256', secretOverride || SECRET).update(canonical).digest('hex');
}

function timingSafeHexEqual(hexA, hexB) {
  const a = Buffer.from(String(hexA || ''), 'utf8');
  const b = Buffer.from(String(hexB || ''), 'utf8');
  return a.length > 0 && a.length === b.length && crypto.timingSafeEqual(a, b);
}

function verifyHmacWithSecrets(storedHmac, computeForSecret) {
  const target = String(storedHmac || '').trim();
  let primaryComputed = '';
  for (const sec of HMAC_VERIFICATION_SECRETS) {
    const computed = computeForSecret(sec);
    if (!primaryComputed) primaryComputed = computed;
    if (target && timingSafeHexEqual(target, computed)) {
      return { valid: true, recomputed: computed, matchedSecret: sec };
    }
  }
  return { valid: false, recomputed: primaryComputed, matchedSecret: null };
}

function formatViDateTime(isoOrUnix) {
  if (!isoOrUnix) return '';
  const date = typeof isoOrUnix === 'number'
    ? new Date(isoOrUnix * 1000)
    : new Date(isoOrUnix);
  if (Number.isNaN(date.getTime())) return String(isoOrUnix);
  return date.toLocaleString('vi-VN', { timeZone: 'Asia/Ho_Chi_Minh' });
}

function parseJsonBody(req) {
  if (!req || req.body === undefined || req.body === null) return {};
  if (typeof req.body === 'object') return assertCleanUtf8(req.body);
  try {
    return assertCleanUtf8(JSON.parse(String(req.body)));
  } catch {
    return {};
  }
}

const ACTIVITY_LABELS = {
  watering_fert: 'Tưới & Châm phân tự động',
  bio_ipm: 'Phòng trừ sinh học (IPM)',
  env_check: 'Kiểm tra môi trường & Giá thể',
  harvest: 'Thu hoạch & Đóng gói QR',
};

const RAW_FALLBACK_CERTS = [
  {
    cert_code: 'MXCERT-2026-CHAMSOC01-0042',
    moodle_userid: 4,
    moodle_courseid: 2,
    student_name: 'Nguyễn Văn Hùng',
    student_username: 'nd_rau_lamdong',
    farm_name: 'Nông trại Rau Thủy Canh & Nhà Màng Xuân Thọ',
    course_code: 'CHAMSOC01',
    course_name: 'Kỹ thuật Canh tác Nông nghiệp Công nghệ cao & Dinh dưỡng Tự động',
    final_score: 91.67,
    status: 'valid',
    issued_unix: 1794030797,
  },
  {
    cert_code: 'MXCERT-2026-IOTNHAMANG01-0044',
    moodle_userid: 4,
    moodle_courseid: 4,
    student_name: 'Nguyễn Văn Hùng',
    student_username: 'nd_rau_lamdong',
    farm_name: 'Nông trại Rau Thủy Canh & Nhà Màng Xuân Thọ',
    course_code: 'IOTNHAMANG01',
    course_name: 'Vận hành Nhà màng Thông minh & Trạm Cảm biến Vi khí hậu IoT',
    final_score: 91.67,
    status: 'valid',
    issued_unix: 1794030797,
  },
  {
    cert_code: 'MXCERT-2026-THUYCANH01-0045',
    moodle_userid: 4,
    moodle_courseid: 5,
    student_name: 'Nguyễn Văn Hùng',
    student_username: 'nd_rau_lamdong',
    farm_name: 'Nông trại Rau Thủy Canh & Nhà Màng Xuân Thọ',
    course_code: 'THUYCANH01',
    course_name: 'Kỹ thuật Thủy canh Hồi lưu NFT & Giá thể Xơ dừa Chuẩn VietGAP',
    final_score: 100.00,
    status: 'valid',
    issued_unix: 1794030798,
  },
  {
    cert_code: 'MXCERT-2026-DAUTAYCNC01-0049',
    moodle_userid: 4,
    moodle_courseid: 19,
    student_name: 'Nguyễn Văn Hùng',
    student_username: 'nd_rau_lamdong',
    farm_name: 'Nông trại Rau Thủy Canh & Nhà Màng Xuân Thọ',
    course_code: 'DAUTAY-CNC-01',
    course_name: 'Kỹ thuật Canh tác Dâu tây & Hoa Cúc Công nghệ cao trong Nhà màng Đà Lạt',
    final_score: 90.00,
    status: 'valid',
    issued_unix: 1794752371,
  },
];

const FALLBACK_CERTS = {};
for (const c of RAW_FALLBACK_CERTS) {
  FALLBACK_CERTS[c.cert_code] = {
    ...c,
    issued_at: new Date(c.issued_unix * 1000).toISOString(),
    hmac_signature: computeCertHmac(c.cert_code, c.moodle_userid, c.moodle_courseid, c.final_score, c.issued_unix),
  };
}

const FALLBACK_BATCHES = {
  'VG-LD-202611-0001': {
    batch_code: 'VG-LD-202611-0001',
    product_name: 'Xà lách Thủy canh Giòn Đà Lạt Lô A1',
    crop_variety: 'Xà lách Romaine F1 Hà Lan',
    farmer_name: 'Nguyễn Văn Hùng',
    farm_name: 'Nông trại Rau Thủy Canh & Nhà Màng Xuân Thọ',
    region: 'Lâm Đồng',
    gps_coords: '11.946520, 108.504110',
    area_m2: 3500,
    expected_yield_kg: 1450,
    vietgap_status: 'certified',
    qr_token: '8c3f4db32f5781029261744affc12f18',
    created_at: new Date(1794030623 * 1000).toISOString(),
  },
  'VG-LD-202611-0027': {
    batch_code: 'VG-LD-202611-0027',
    product_name: 'Ớt Chuông Đà Lạt Sấy Lạnh Xuất Khẩu Lô E2E',
    crop_variety: 'Giống F1 Chuẩn Sạch Bệnh',
    farmer_name: 'Nguyễn Văn Hùng',
    farm_name: 'Nông trại Rau Thủy Canh & Nhà Màng Xuân Thọ',
    region: 'Lâm Đồng',
    gps_coords: '11.946520, 108.504110',
    area_m2: 1200,
    expected_yield_kg: 2500,
    vietgap_status: 'active',
    qr_token: 'a59906f67a7e04af27a999f23918c7ad',
    created_at: new Date(1794146156 * 1000).toISOString(),
  },
};

for (const code of Object.keys(FALLBACK_BATCHES)) {
  const b = FALLBACK_BATCHES[code];
  b.hmac_signature = computeBatchHmac(b.batch_code, b.region, b.area_m2, b.qr_token);
}

const FALLBACK_LOGS = [
  {
    id: 2,
    batch_code: 'VG-LD-202611-0001',
    activity_type: 'bio_ipm',
    notes: 'Treo bẫy dính màu vàng/xanh kiểm soát bọ trĩ và tưới nấm đối kháng Trichoderma phòng thối rễ sinh học.',
    ph_val: '6.2',
    ec_val: '1.7',
    phi_days: 0,
    logged_at: new Date(1793857823 * 1000).toISOString(),
  },
  {
    id: 1,
    batch_code: 'VG-LD-202611-0001',
    activity_type: 'watering_fert',
    notes: 'Châm phân nhỏ giọt sáng sớm theo công thức Venturi A+B, kiểm tra đầu nhỏ giọt đều 100%, lá xanh dày khỏe.',
    ph_val: '6.1',
    ec_val: '1.8',
    phi_days: 0,
    logged_at: new Date(1793598623 * 1000).toISOString(),
  },
  {
    id: 54,
    batch_code: 'VG-LD-202611-0027',
    activity_type: 'watering_fert',
    notes: 'Tưới nhỏ giọt sáng sớm pH 6.0, EC 1.7 mS/cm và kiểm tra kho mát Pre-cooling 4°C.',
    ph_val: '6.0',
    ec_val: '1.6',
    phi_days: 0,
    logged_at: new Date(1794146157 * 1000).toISOString(),
  },
  {
    id: 53,
    batch_code: 'VG-LD-202611-0027',
    activity_type: 'env_check',
    notes: 'Khởi tạo lô canh tác chuẩn VietGAP, kiểm tra nguồn nước và giá thể đạt chuẩn.',
    ph_val: '6.0',
    ec_val: '1.5',
    phi_days: 0,
    logged_at: new Date(1794146156 * 1000).toISOString(),
  },
];

const FALLBACK_FERTIGATION = [];

function hashPassword(password, salt = crypto.randomBytes(16).toString('hex')) {
  const password_hash = crypto.pbkdf2Sync(String(password), String(salt), 10000, 64, 'sha512').toString('hex');
  return { salt, password_hash };
}

function verifyPassword(password, salt, storedHash) {
  if (!password || !salt || !storedHash) return false;
  const computed = crypto.pbkdf2Sync(String(password), String(salt), 10000, 64, 'sha512').toString('hex');
  return timingSafeHexEqual(computed, storedHash);
}

function base64UrlEncode(input) {
  return Buffer.from(input).toString('base64').replace(/=/g, '').replace(/\+/g, '-').replace(/\//g, '_');
}

function base64UrlDecode(str) {
  let b64 = String(str).replace(/-/g, '+').replace(/_/g, '/');
  while (b64.length % 4) b64 += '=';
  return Buffer.from(b64, 'base64').toString('utf8');
}

function signJwt(payload, expiresInSec = 7 * 86400) {
  const now = Math.floor(Date.now() / 1000);
  const fullPayload = { ...payload, iat: now, exp: now + expiresInSec };
  const h = base64UrlEncode(JSON.stringify({ alg: 'HS256', typ: 'JWT' }));
  const p = base64UrlEncode(JSON.stringify(fullPayload));
  const sig = base64UrlEncode(crypto.createHmac('sha256', SECRET).update(`${h}.${p}`).digest());
  return `${h}.${p}.${sig}`;
}

function verifyJwt(token) {
  if (!token || typeof token !== 'string') return null;
  const parts = token.split('.');
  if (parts.length !== 3) return null;
  const [h, p, sig] = parts;
  const expectedSig = base64UrlEncode(crypto.createHmac('sha256', SECRET).update(`${h}.${p}`).digest());
  if (!timingSafeHexEqual(sig, expectedSig)) return null;
  try {
    const payload = JSON.parse(base64UrlDecode(p));
    if (!payload.exp || payload.exp < Math.floor(Date.now() / 1000)) return null;
    return payload;
  } catch {
    return null;
  }
}

function sanitizeUser(u) {
  if (!u) return null;
  const { password_hash, salt, ...safe } = u;
  return assertCleanUtf8(safe);
}

function extractBearerToken(req) {
  const headers = (req && req.headers) || {};
  const authHeader = headers.authorization || headers.Authorization || '';
  const match = String(authHeader).match(/^Bearer\s+(.+)$/i);
  return match ? match[1].trim() : '';
}

async function supabaseCount(table) {
  const { url, key, enabled } = getSupabaseCreds();
  if (!enabled) return 0;
  const res = await fetch(`${url}/rest/v1/${table}?select=id`, {
    method: 'HEAD',
    headers: {
      apikey: key,
      Authorization: `Bearer ${key}`,
      Prefer: 'count=exact',
    },
  });
  const range = res.headers.get('content-range') || '';
  const m = range.match(/\/(\d+)$/);
  return m ? Number(m[1]) : 0;
}

const FALLBACK_USERS = [
  {
    id: 1,
    username: 'admin',
    email: 'admin@mamxanh.vn',
    salt: 'mx2026salt_admin_0001a9b8c7d6e5f4',
    password_hash: crypto.pbkdf2Sync('Admin!2026MX', 'mx2026salt_admin_0001a9b8c7d6e5f4', 10000, 64, 'sha512').toString('hex'),
    full_name: 'Quản trị viên Hệ thống Mầm Xanh',
    role: 'admin',
    farm_name: 'Trung tâm Điều hành Mầm Xanh DLU',
    region: 'Lâm Đồng',
    area_m2: 5000,
    main_crop: 'Quản trị Nền tảng Nông nghiệp Số',
    is_active: true,
    created_at: new Date('2026-11-01T00:00:00Z').toISOString(),
  },
  {
    id: 2,
    username: 'cbkt_pilot',
    email: 'cbkt_pilot@mamxanh.vn',
    salt: 'mx2026salt_cbkt_0002b8c7d6e5f4a3',
    password_hash: crypto.pbkdf2Sync('MX!Canbo2026', 'mx2026salt_cbkt_0002b8c7d6e5f4a3', 10000, 64, 'sha512').toString('hex'),
    full_name: 'Kỹ sư Trần Minh Tuấn (CBKT)',
    role: 'teacher',
    farm_name: 'Trạm Khuyến nông & Kiểm định VietGAP Đà Lạt',
    region: 'Lâm Đồng',
    area_m2: 4000,
    main_crop: 'Giám sát Kỹ thuật Nhà màng & Thủy canh',
    is_active: true,
    created_at: new Date('2026-11-01T00:00:00Z').toISOString(),
  },
  {
    id: 3,
    username: 'nd_rau_lamdong',
    email: 'nd_rau_lamdong@mamxanh.vn',
    salt: 'mx2026salt_ndrau_0003c7d6e5f4a2b1',
    password_hash: crypto.pbkdf2Sync('MX!Rau2026', 'mx2026salt_ndrau_0003c7d6e5f4a2b1', 10000, 64, 'sha512').toString('hex'),
    full_name: 'Nguyễn Văn Hùng',
    role: 'student',
    farm_name: 'Nông trại Rau Thủy Canh & Nhà Màng Xuân Thọ',
    region: 'Lâm Đồng',
    area_m2: 3500,
    main_crop: 'Xà lách thủy canh, Ớt chuông & Dâu tây nhà màng',
    is_active: true,
    created_at: new Date('2026-11-02T00:00:00Z').toISOString(),
  },
  {
    id: 4,
    username: 'nd_dautay_lacduong',
    email: 'nd_dautay_lacduong@mamxanh.vn',
    salt: 'mx2026salt_dautay_0004d6e5f4a1b2c3',
    password_hash: crypto.pbkdf2Sync('MX!Dautay2026', 'mx2026salt_dautay_0004d6e5f4a1b2c3', 10000, 64, 'sha512').toString('hex'),
    full_name: 'Trần Thị Lan Anh',
    role: 'student',
    farm_name: 'HTX Dâu tây Công nghệ cao Lạc Dương',
    region: 'Lâm Đồng',
    area_m2: 2200,
    main_crop: 'Dâu tây Albion New Zealand & Hoa Cúc Cắt cành',
    is_active: true,
    created_at: new Date('2026-11-03T00:00:00Z').toISOString(),
  },
];

const FALLBACK_COURSES = [
  { id: 1, code: 'CHAMSOC01', fullname: 'Kỹ thuật Canh tác Nông nghiệp Công nghệ cao & Dinh dưỡng Tự động', category: 'Kỹ thuật Nhà màng & Dinh dưỡng', summary: 'Học phần trang bị kiến thức chuyên sâu về vi khí hậu nhà màng, hệ thống tưới nhỏ giọt bù áp, quản lý dinh dưỡng khoáng pH/EC tự động và phác đồ kiểm soát dịch hại sinh học (IPM) theo chuẩn VietGAP TCVN 11892-1:2017.', modules_count: 23, is_cloned: false, created_by: 'admin', created_at: '2026-11-01T00:00:00Z' },
  { id: 2, code: 'CHEBIEN01', fullname: 'Công nghệ Sơ chế, Chế biến Sâu & Bảo quản Lạnh Nông sản Xuất khẩu', category: 'Sau thu hoạch & Bảo quản', summary: 'Làm chủ chuỗi giá trị sau thu hoạch: Hạ nhiệt đồng ruộng (Pre-cooling) trong 2 giờ vàng, công nghệ sấy lạnh bơm nhiệt, đóng gói khí quyển biến đổi MAP/CA, kho lạnh thông minh và cấp mã QR VietGAP.', modules_count: 15, is_cloned: false, created_by: 'admin', created_at: '2026-11-01T00:00:00Z' },
  { id: 3, code: 'IOTNHAMANG01', fullname: 'Vận hành Nhà màng Thông minh & Trạm Cảm biến Vi khí hậu IoT', category: 'Nông nghiệp Thông minh & IoT', summary: 'Làm chủ hệ thống cảm biến nhiệt-ẩm-ánh sáng PAR-CO2, chỉ số thiếu hụt áp suất hơi nước VPD, điều khiển tự động lưới cắt nắng, quạt đối lưu và màn mưa Cooling Pad.', modules_count: 14, is_cloned: false, created_by: 'admin', created_at: '2026-11-01T00:00:00Z' },
  { id: 4, code: 'THUYCANH01', fullname: 'Kỹ thuật Thủy canh Hồi lưu NFT & Giá thể Xơ dừa Chuẩn VietGAP', category: 'Thủy canh & Giá thể', summary: 'Thiết kế độ dốc máng thủy canh màng dinh dưỡng NFT 1:40, kiểm soát oxy hòa tan DO >= 6 mg/L, xử lý xơ dừa xả chát Tanin/EC và kiểm soát dư lượng nitrat trước thu hoạch.', modules_count: 14, is_cloned: false, created_by: 'admin', created_at: '2026-11-01T00:00:00Z' },
  { id: 5, code: 'SAURIENG01', fullname: 'Quy trình Canh tác Sầu riêng Xuất khẩu, Mã số Vùng trồng & Xử lý Ra hoa', category: 'Cây ăn trái Xuất khẩu', summary: 'Chuẩn hóa quy trình canh tác Sầu riêng Dona/Ri6 phục vụ xuất khẩu chính ngạch: Quản lý cơi đọt, xiết nước ra hoa đồng loạt, phòng chống Phytophthora & Rệp sáp, quản lý Mã số vùng trồng (PUC).', modules_count: 14, is_cloned: false, created_by: 'admin', created_at: '2026-11-01T00:00:00Z' },
  { id: 6, code: 'PHANVISINH01', fullname: 'Sản xuất Phân Hữu cơ Vi sinh, Nhân sinh khối Trichoderma & Cải tạo Đất', category: 'Vi sinh & Tuần hoàn Đất', summary: 'Tận dụng phụ phẩm nông nghiệp ủ hiếu khí 55-65 độ C theo tỷ lệ C/N = 25-30:1, nhân sinh khối nấm đối kháng Trichoderma spp. và Bacillus subtilis cải tạo hệ vi sinh đất.', modules_count: 14, is_cloned: false, created_by: 'admin', created_at: '2026-11-01T00:00:00Z' },
  { id: 7, code: 'DAUTAY-CNC-01', fullname: 'Kỹ thuật Canh tác Dâu tây & Hoa Cúc Công nghệ cao trong Nhà màng Đà Lạt', category: 'Cây trồng Chủ lực Đà Lạt', summary: 'Quy trình chuẩn hóa canh tác Dâu tây Albion và Hoa cúc cắt cành trên giá thể treo tại Đà Lạt: kiểm soát vi khí hậu, châm phân nhỏ giọt, IPM sinh học và truy xuất nguồn gốc VietGAP.', modules_count: 14, is_cloned: false, created_by: 'admin', created_at: '2026-11-01T00:00:00Z' },
  { id: 8, code: 'DAUTAY-DONDUONG-01', fullname: 'Tập huấn Kỹ thuật Dâu tây & Hoa Cúc CNC - HTX Đơn Dương', category: 'Chuyển giao Hợp tác xã', summary: 'Phiên bản khóa học nhân bản dành riêng cho hội viên Hợp tác xã Đơn Dương (Lâm Đồng) với trạm thực hành GPS thực địa ngay tại vùng nguyên liệu Đơn Dương.', modules_count: 14, is_cloned: true, created_by: 'cbkt_pilot', created_at: '2026-11-02T00:00:00Z' },
];

const FALLBACK_ENROLLMENTS = [
  { id: 1, user_id: 3, course_code: 'CHAMSOC01', progress_pct: 100, quiz_score: 91.67, attendance_count: 4, status: 'completed', enrolled_at: '2026-11-02T08:00:00Z' },
  { id: 2, user_id: 3, course_code: 'CHEBIEN01', progress_pct: 100, quiz_score: 100, attendance_count: 3, status: 'completed', enrolled_at: '2026-11-02T08:00:00Z' },
  { id: 3, user_id: 3, course_code: 'IOTNHAMANG01', progress_pct: 100, quiz_score: 91.67, attendance_count: 3, status: 'completed', enrolled_at: '2026-11-02T08:00:00Z' },
  { id: 4, user_id: 3, course_code: 'THUYCANH01', progress_pct: 100, quiz_score: 100, attendance_count: 4, status: 'completed', enrolled_at: '2026-11-02T08:00:00Z' },
  { id: 5, user_id: 3, course_code: 'SAURIENG01', progress_pct: 100, quiz_score: 90, attendance_count: 3, status: 'completed', enrolled_at: '2026-11-02T08:00:00Z' },
  { id: 6, user_id: 3, course_code: 'PHANVISINH01', progress_pct: 100, quiz_score: 90, attendance_count: 3, status: 'completed', enrolled_at: '2026-11-02T08:00:00Z' },
  { id: 7, user_id: 3, course_code: 'DAUTAY-CNC-01', progress_pct: 100, quiz_score: 90, attendance_count: 3, status: 'completed', enrolled_at: '2026-11-02T08:00:00Z' },
  { id: 8, user_id: 3, course_code: 'DAUTAY-DONDUONG-01', progress_pct: 65, quiz_score: 80, attendance_count: 2, status: 'in_progress', enrolled_at: '2026-11-05T08:00:00Z' },
  { id: 9, user_id: 4, course_code: 'CHAMSOC01', progress_pct: 50, quiz_score: 80, attendance_count: 1, status: 'in_progress', enrolled_at: '2026-11-03T08:00:00Z' },
  { id: 10, user_id: 4, course_code: 'IOTNHAMANG01', progress_pct: 25, quiz_score: 0, attendance_count: 1, status: 'enrolled', enrolled_at: '2026-11-03T08:00:00Z' },
  { id: 11, user_id: 4, course_code: 'DAUTAY-CNC-01', progress_pct: 75, quiz_score: 80, attendance_count: 2, status: 'in_progress', enrolled_at: '2026-11-03T08:00:00Z' },
  { id: 12, user_id: 4, course_code: 'DAUTAY-DONDUONG-01', progress_pct: 20, quiz_score: 0, attendance_count: 0, status: 'enrolled', enrolled_at: '2026-11-03T08:00:00Z' },
];

const FALLBACK_FIELDPRACTICE_SUBS = [
  {
    id: 1,
    user_id: 3,
    course_code: 'CHAMSOC01',
    checkin_code: 'QR-CHAMSOC01-XUATHO',
    gps_lat: '11.946520',
    gps_lng: '108.504110',
    distance_m: 18,
    crit_prep: 24,
    crit_tech: 23,
    crit_biosafe: 24,
    crit_quality: 22,
    total_grade: 93,
    feedback: 'Nông hộ thao tác cân chỉnh EC/pH chuẩn 1.6 mS/cm, đường ống nhỏ giọt không rò rỉ, tuân thủ đầy đủ bảo hộ VietGAP.',
    graded_by: 'cbkt_pilot',
    submitted_at: '2026-11-17T08:00:00Z',
    graded_at: '2026-11-18T10:00:00Z',
  },
  {
    id: 2,
    user_id: 4,
    course_code: 'DAUTAY-CNC-01',
    checkin_code: 'QR-DAUTAY-LACDUONG',
    gps_lat: '12.012400',
    gps_lng: '108.431500',
    distance_m: 26,
    crit_prep: 0,
    crit_tech: 0,
    crit_biosafe: 0,
    crit_quality: 0,
    total_grade: 0,
    feedback: '',
    graded_by: null,
    submitted_at: '2026-11-21T07:30:00Z',
    graded_at: null,
  },
];

const FALLBACK_QUIZ_ATTEMPTS = [
  { id: 1, user_id: 3, course_code: 'CHAMSOC01', score_100: 91.67, answers_json: { answers: [1, 2, 0, 1, 2], correct: 5, total: 5 }, attempted_at: '2026-11-16T09:00:00Z' },
  { id: 2, user_id: 4, course_code: 'DAUTAY-CNC-01', score_100: 80, answers_json: { answers: [0, 1, 2, 1, 0], correct: 4, total: 5 }, attempted_at: '2026-11-20T09:00:00Z' },
];

const FALLBACK_NEWS = [
  { id: 1, category: 'Kỹ thuật Canh tác', title: 'Ứng dụng tưới nhỏ giọt bù áp & châm phân tự động (Fertigation) tiết kiệm 40% phân bón', summary: 'Kiểm soát EC 1.2–2.2 mS/cm và pH 5.6–6.2 theo từng giai đoạn sinh trưởng trong nhà màng Lâm Đồng.', content: '<p><strong>1. Đặt vấn đề:</strong> Tưới tràn truyền thống gây thất thoát đạm nitrat và tích tụ muối cục bộ trên giá thể xơ dừa. Hệ thống tưới nhỏ giọt bù áp (1.6–2.0 L/h) kết hợp bộ châm phân tự động giúp đồng đều dinh dưỡng tới 95%.</p><p><strong>2. Kết quả thực tiễn:</strong> Giảm 40% lượng phân bón hòa tan, tiết kiệm 32% nước tưới và tăng 24% tỷ lệ nông sản loại A đạt chuẩn xuất khẩu.</p>', published_at: '2026-11-12T08:00:00Z' },
  { id: 2, category: 'Bảo vệ Thực vật', title: 'Phác đồ Quản lý dịch hại tổng hợp (IPM) & chế phẩm sinh học Trichoderma phòng bệnh héo rũ', summary: 'Nhận diện sớm nấm Fusarium, Phytophthora và kết hợp bẫy dính sinh học đảm bảo thời gian cách ly PHI = 0 ngày.', content: '<p><strong>1. Giám sát dịch tễ:</strong> Điều tra 5 điểm chéo góc định kỳ 7 ngày/lần khi ẩm độ nhà màng vượt 85%.</p><p><strong>2. Biện pháp sinh học:</strong> Ủ hoạt hóa nấm đối kháng <em>Trichoderma spp.</em> cùng vi khuẩn <em>Bacillus subtilis</em> vào giá thể, kết hợp 40 bẫy dính vàng/xanh trên 1.000 m2 giúp triệt tiêu dư lượng thuốc BVTV hóa học.</p>', published_at: '2026-11-10T08:00:00Z' },
  { id: 3, category: 'Tiêu chuẩn & Xuất khẩu', title: 'Quy trình ghi chép nhật ký đồng ruộng điện tử & cấp mã số vùng trồng truy xuất QR', summary: 'Số hóa biểu mẫu theo TCVN 11892-1:2017 và ký xác thực lô nông sản bằng thuật toán mật mã HMAC-SHA256.', content: '<p><strong>1. Nhật ký điện tử 24h:</strong> Toàn bộ tác nghiệp pha tưới, phòng trừ sinh học và thu hoạch được ghi trực tiếp qua Web hoặc Nhật ký Cloud.</p><p><strong>2. Hộ chiếu nông sản QR:</strong> Mỗi lô nông sản thu hoạch được gắn mã truy xuất công khai cho phép đơn vị thu mua kiểm tra lịch sử canh tác tức thì.</p>', published_at: '2026-11-08T08:00:00Z' },
  { id: 4, category: 'Thông báo Đào tạo', title: 'Kế hoạch mở lớp tập huấn Dâu tây & Hoa Cúc Công nghệ cao đợt tháng 11/2026', summary: 'Học phần DAUTAY-CNC-01 gồm 04 chuyên đề trực tuyến, H5P, SCORM 1.2 và thực hành QR+GPS tại Đà Lạt.', content: '<p><strong>1. Cấu trúc chương trình:</strong> 04 chuyên đề từ xử lý giá thể, điều tiết vi khí hậu, tưới nhỏ giọt Fertigation đến phòng trừ IPM.</p><p><strong>2. Điều kiện cấp chứng nhận:</strong> Quiz cuối khóa >= 70% và hoàn thành chấm Rubric 4 tiêu chí tại vườn >= 60 điểm.</p>', published_at: '2026-11-05T08:00:00Z' },
];

const DEFAULT_STARTER_COURSES = ['CHAMSOC01', 'IOTNHAMANG01', 'DAUTAY-CNC-01'];

function buildJwtClaims(user) {
  return assertCleanUtf8({
    sub: Number(user.id),
    username: String(user.username || ''),
    email: String(user.email || ''),
    full_name: String(user.full_name || user.username || 'Nông hộ Mầm Xanh'),
    role: String(user.role || 'student'),
    farm_name: String(user.farm_name || ''),
    region: String(user.region || 'Lâm Đồng'),
    area_m2: Number(user.area_m2 || 1000),
    main_crop: String(user.main_crop || ''),
  });
}

function ensureStarterEnrollmentsInFallback(userId, courseCodes = DEFAULT_STARTER_COURSES) {
  if (!FALLBACK_ENROLLMENTS.some(e => Number(e.user_id) === Number(userId))) {
    let nextEnrollId = FALLBACK_ENROLLMENTS.reduce((m, e) => Math.max(m, Number(e.id)), 0) + 1;
    for (const code of courseCodes) {
      FALLBACK_ENROLLMENTS.push({
        id: nextEnrollId++,
        user_id: Number(userId),
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

function computePhiAlerts(batches = [], logs = []) {
  const batchMap = new Map((Array.isArray(batches) ? batches : []).map(b => [b.batch_code, b]));
  const latestByBatch = new Map();
  for (const l of (Array.isArray(logs) ? logs : [])) {
    if (!l || !l.batch_code) continue;
    const prev = latestByBatch.get(l.batch_code);
    const phiDays = Number(l.phi_days || 0);
    const prevPhi = prev ? Number(prev.phi_days || 0) : -1;
    // Prefer latest log with active PHI > 0, otherwise most recent log for that batch
    if (!prev || (phiDays > 0 && prevPhi <= 0) || (Boolean(phiDays > 0) === Boolean(prevPhi > 0) && new Date(l.logged_at) > new Date(prev.logged_at))) {
      latestByBatch.set(l.batch_code, l);
    }
  }

  const nowMs = Date.now();
  const alerts = [];
  for (const [batchCode, log] of latestByBatch.entries()) {
    const batch = batchMap.get(batchCode) || {};
    const phiDays = Number(log.phi_days || 0);
    const loggedMs = new Date(log.logged_at || nowMs).getTime();
    const safeMs = loggedMs + phiDays * 86400000;
    const remainingDays = phiDays > 0 ? Math.max(0, Math.ceil((safeMs - nowMs) / 86400000)) : 0;
    alerts.push(assertCleanUtf8({
      batch_code: batchCode,
      product_name: batch.product_name || batchCode,
      region: batch.region || 'Lâm Đồng',
      activity_label: ACTIVITY_LABELS[log.activity_type] || log.activity_type,
      phi_days: phiDays,
      remaining_days: remainingDays,
      logged_at_display: formatViDateTime(log.logged_at),
      safe_harvest_date: formatViDateTime(new Date(safeMs).toISOString()),
      status: remainingDays > 0 ? 'waiting_phi' : 'safe_harvest',
    }));
  }
  return alerts
    .sort((a, b) => Number(b.remaining_days) - Number(a.remaining_days) || Number(b.phi_days) - Number(a.phi_days))
    .slice(0, 6);
}

async function authenticateRequest(req, res, allowedRoles = null) {
  setJsonHeaders(res);
  const token = extractBearerToken(req);
  const claims = verifyJwt(token);
  if (!claims || !claims.sub) {
    res.status(401).json({
      ok: false,
      message_vi: 'Vui lòng đăng nhập để truy cập chức năng này (Thiếu hoặc hết hạn JWT Token).',
      message_en: 'Authentication required (missing or invalid JWT token).',
    });
    return null;
  }

  let user = null;
  let source = 'embedded-fallback';
  const keyRole = inspectServiceKeyRole();
  if (hasSupabaseConfig() && keyRole === 'service_role') {
    try {
      const rows = await supabaseFetch(`lms_users?id=eq.${encodeURIComponent(claims.sub)}&select=*&limit=1`);
      if (Array.isArray(rows) && rows.length > 0) {
        user = rows[0];
        source = 'supabase-live';
      }
    } catch {
      // Fallback to embedded/stateless claims if RLS blocks lms_users read
    }
  }
  if (!user) {
    user = FALLBACK_USERS.find(u => Number(u.id) === Number(claims.sub) || u.username === claims.username) || null;
  }

  // Cross-Lambda resilience: reconstruct authenticated user safely from cryptographically verified HS256 JWT claims
  if (!user && claims.username && claims.role) {
    const recoveredUser = assertCleanUtf8({
      id: Number(claims.sub),
      username: String(claims.username),
      email: String(claims.email || `${claims.username}@mamxanh.vn`),
      salt: '',
      password_hash: '',
      full_name: String(claims.full_name || claims.username),
      role: String(claims.role),
      farm_name: String(claims.farm_name || ''),
      region: String(claims.region || 'Lâm Đồng'),
      area_m2: Number(claims.area_m2 || 1000),
      main_crop: String(claims.main_crop || ''),
      is_active: true,
      reconstructed_from_jwt: true,
      created_at: new Date().toISOString(),
    });
    FALLBACK_USERS.push(recoveredUser);
    if (recoveredUser.role === 'student') {
      ensureStarterEnrollmentsInFallback(recoveredUser.id);
    }
    user = recoveredUser;
  }

  if (!user) {
    res.status(401).json({
      ok: false,
      message_vi: 'Tài khoản không tồn tại trong hệ thống.',
      message_en: 'Account not found.',
    });
    return null;
  }

  if (!user.is_active) {
    res.status(403).json({
      ok: false,
      message_vi: 'Tài khoản của bạn đang bị tạm khóa bởi Quản trị viên.',
      message_en: 'Your account is currently disabled.',
    });
    return null;
  }

  if (Array.isArray(allowedRoles) && !allowedRoles.includes(user.role)) {
    res.status(403).json({
      ok: false,
      code: 'FORBIDDEN_ROLE',
      message_vi: `Truy cập bị từ chối (403): Vai trò "${user.role}" không được phép thực hiện thao tác này.`,
      message_en: `Access denied (403): Role "${user.role}" is not permitted for this action.`,
    });
    return null;
  }

  return { user, source };
}

module.exports = {
  SECRET,
  CANONICAL_MOODLE_SECRET,
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
  supabaseCount,
  computeCertHmac,
  computeBatchHmac,
  verifyHmacWithSecrets,
  timingSafeHexEqual,
  formatViDateTime,
  parseJsonBody,
  hashPassword,
  verifyPassword,
  buildJwtClaims,
  ensureStarterEnrollmentsInFallback,
  computePhiAlerts,
  signJwt,
  verifyJwt,
  sanitizeUser,
  extractBearerToken,
  authenticateRequest,
  ACTIVITY_LABELS,
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
};
