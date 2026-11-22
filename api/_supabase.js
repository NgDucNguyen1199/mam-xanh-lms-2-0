const crypto = require('crypto');

const SECRET = process.env.MAMXANH_HMAC_SECRET || 'MX2_HMAC_2026_VIETGAP_AGRITECH_DIGITAL_SIGNATURE_KEY';

function getSupabaseCreds() {
  const url = String(process.env.SUPABASE_URL || '').trim().replace(/\/+$/, '');
  const key = String(process.env.SUPABASE_SERVICE_KEY || '').trim();
  return { url, key, enabled: Boolean(url && key) };
}

function hasSupabaseConfig() {
  return getSupabaseCreds().enabled;
}

async function supabaseFetch(restPath, options = {}) {
  const { url, key, enabled } = getSupabaseCreds();
  if (!enabled) {
    throw new Error('SUPABASE_URL or SUPABASE_SERVICE_KEY is not configured');
  }

  const headers = {
    apikey: key,
    Authorization: `Bearer ${key}`,
    'Content-Type': 'application/json',
    ...(options.headers || {}),
  };

  const res = await fetch(`${url}/rest/v1/${restPath}`, {
    method: options.method || 'GET',
    headers,
    body: options.body ? JSON.stringify(options.body) : undefined,
  });

  const text = await res.text();
  let json = null;
  try {
    json = text ? JSON.parse(text) : null;
  } catch {
    json = { raw: text };
  }

  if (!res.ok) {
    const msg = (json && (json.message || json.hint || json.details)) || `Supabase HTTP ${res.status}`;
    const err = new Error(msg);
    err.status = res.status;
    err.payload = json;
    throw err;
  }

  return json;
}

function computeCertHmac(certCode, userId, courseId, score, timeCreated) {
  const canonical = [
    String(certCode).trim(),
    String(userId),
    String(courseId),
    Number(score).toFixed(2),
    String(timeCreated),
  ].join('|');
  return crypto.createHmac('sha256', SECRET).update(canonical).digest('hex');
}

function computeBatchHmac(batchCode, region, areaM2, qrToken) {
  const canonical = `${String(batchCode).trim()}|${String(region).trim()}|${Number(areaM2)}|${String(qrToken || '').trim()}`;
  return crypto.createHmac('sha256', SECRET).update(canonical).digest('hex');
}

function timingSafeHexEqual(hexA, hexB) {
  const a = Buffer.from(String(hexA || ''), 'utf8');
  const b = Buffer.from(String(hexB || ''), 'utf8');
  return a.length > 0 && a.length === b.length && crypto.timingSafeEqual(a, b);
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
  if (typeof req.body === 'object') return req.body;
  try {
    return JSON.parse(String(req.body));
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

module.exports = {
  SECRET,
  hasSupabaseConfig,
  supabaseFetch,
  computeCertHmac,
  computeBatchHmac,
  timingSafeHexEqual,
  formatViDateTime,
  parseJsonBody,
  ACTIVITY_LABELS,
  FALLBACK_CERTS,
  FALLBACK_BATCHES,
  FALLBACK_LOGS,
  FALLBACK_FERTIGATION,
};
