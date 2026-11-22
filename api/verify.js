const crypto = require('crypto');

const SECRET = process.env.MAMXANH_HMAC_SECRET || 'MX2_HMAC_2026_VIETGAP_AGRITECH_DIGITAL_SIGNATURE_KEY';

function computeHmac(certCode, userId, courseId, score, timeCreated) {
  const canonical = [
    String(certCode).trim(),
    String(userId),
    String(courseId),
    Number(score).toFixed(2),
    String(timeCreated),
  ].join('|');
  return crypto.createHmac('sha256', SECRET).update(canonical).digest('hex');
}

const RAW_CERTS = [
  {
    cert_code: 'MXCERT-2026-CHAMSOC01-0042',
    userid: 4,
    courseid: 2,
    learner_name: 'Nguyễn Văn An (Nông hộ Rau Lâm Đồng)',
    farm_name: 'Nông trại Mầm Xanh Đà Lạt',
    course_shortname: 'CHAMSOC01',
    course_fullname: 'Kỹ thuật Canh tác Nông nghiệp Công nghệ cao & Dinh dưỡng Tự động',
    final_score: 91.67,
    status: 'valid',
    timecreated: 1794030797,
  },
  {
    cert_code: 'MXCERT-2026-IOTNHAMANG01-0044',
    userid: 4,
    courseid: 4,
    learner_name: 'Nguyễn Văn An (Nông hộ Rau Lâm Đồng)',
    farm_name: 'Nông trại Mầm Xanh Đà Lạt',
    course_shortname: 'IOTNHAMANG01',
    course_fullname: 'Vận hành Nhà màng Thông minh & Trạm Cảm biến Vi khí hậu IoT',
    final_score: 88.50,
    status: 'valid',
    timecreated: 1794115200,
  },
  {
    cert_code: 'MXCERT-2026-THUYCANH01-0045',
    userid: 4,
    courseid: 5,
    learner_name: 'Nguyễn Văn An (Nông hộ Rau Lâm Đồng)',
    farm_name: 'Nông trại Mầm Xanh Đà Lạt',
    course_shortname: 'THUYCANH01',
    course_fullname: 'Kỹ thuật Thủy canh Hồi lưu NFT & Giá thể Xơ dừa Chuẩn VietGAP',
    final_score: 90.00,
    status: 'valid',
    timecreated: 1794201600,
  },
  {
    cert_code: 'MXCERT-2026-DAUTAYCNC01-0048',
    userid: 4,
    courseid: 19,
    learner_name: 'Nguyễn Văn An (Nông hộ Rau Lâm Đồng)',
    farm_name: 'Nông trại Mầm Xanh Đà Lạt',
    course_shortname: 'DAUTAY-CNC-01',
    course_fullname: 'Kỹ thuật Canh tác Dâu tây & Hoa Cúc Công nghệ cao trong Nhà màng Đà Lạt',
    final_score: 92.00,
    status: 'valid',
    timecreated: 1794288000,
  },
];

const CERTS_DB = {};
for (const c of RAW_CERTS) {
  CERTS_DB[c.cert_code] = {
    ...c,
    hmac_signature: computeHmac(c.cert_code, c.userid, c.courseid, c.final_score, c.timecreated),
  };
}

const BATCHES_DB = {
  'VG-LD-202611-0001': {
    batch_code: 'VG-LD-202611-0001',
    product_name: 'Xà lách Thủy canh Giàn Đà Lạt Lô A1',
    crop_variety: 'Xà lách Romaine F1 Hà Lan',
    region: 'Lâm Đồng',
    farm_name: 'Nông trại Mầm Xanh Đà Lạt — Nguyễn Văn An',
    gps: '11.940419, 108.458313',
    area_m2: 3500,
    expected_yield_kg: 1450,
    standard: 'VietGAP TCVN 11892-1:2017',
    qr_token: '8c3f4db32f5781029261744affc12f18',
    logs: [
      { time: '14/11/2026 06:30', activity: 'Thu hoạch & làm mát sơ bộ Pre-cooling 4°C đạt chuẩn VietGAP', ph: '6.0', ec: '1.2', phi: 0 },
      { time: '10/11/2026 07:15', activity: 'Phun chế phẩm nấm đối kháng Trichoderma + Bacillus subtilis phòng nấm rễ', ph: '5.9', ec: '1.5', phi: 0 },
      { time: '05/11/2026 08:00', activity: 'Tưới nhỏ giọt dinh dưỡng A-B định kỳ, cân bằng EC/pH tự động', ph: '6.0', ec: '1.6', phi: 0 },
    ],
  },
  'VG-LD-202611-0027': {
    batch_code: 'VG-LD-202611-0027',
    product_name: 'Dâu tây New Zealand Giá thể Nhà màng P7',
    crop_variety: 'Dâu tây Cấy mô Sạch bệnh F1',
    region: 'Lâm Đồng',
    farm_name: 'HTX Dâu tây Công nghệ cao Phường 7 — Đà Lạt',
    gps: '11.964200, 108.438500',
    area_m2: 2200,
    expected_yield_kg: 1850,
    standard: 'VietGAP TCVN 11892-1:2017',
    qr_token: '5a91e8c4b07234d119c8e2a17f9d3c04',
    logs: [
      { time: '18/11/2026 06:45', activity: 'Thu hoạch trái chín 85%, đóng hộp truy xuất mã QR từng khay', ph: '5.8', ec: '1.5', phi: 0 },
      { time: '12/11/2026 07:30', activity: 'Treo bẫy dính xanh dương & phun sinh học ngừa bọ trĩ, nhện đỏ', ph: '5.9', ec: '1.6', phi: 0 },
      { time: '06/11/2026 08:15', activity: 'Châm phân nhỏ giọt giai đoạn nuôi trái giàu Kali & Canxi-Bo', ph: '5.8', ec: '1.8', phi: 0 },
    ],
  },
};

module.exports = function handler(req, res) {
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.setHeader('Cache-Control', 'no-store');

  const rawCode = String((req.query && req.query.code) || '').trim().toUpperCase();
  const tamper = String((req.query && req.query.tamper) || '0') === '1';

  if (!rawCode) {
    return res.status(200).json({
      ok: true,
      mode: 'info',
      sample_certs: Object.keys(CERTS_DB),
      sample_batches: Object.keys(BATCHES_DB),
    });
  }

  // Check VietGAP Produce Batch first
  if (BATCHES_DB[rawCode]) {
    const batch = BATCHES_DB[rawCode];
    const batchHmac = crypto
      .createHmac('sha256', SECRET)
      .update(`${batch.batch_code}|${batch.region}|${batch.area_m2}|${batch.qr_token}`)
      .digest('hex');

    return res.status(200).json({
      ok: true,
      found: true,
      type: 'batch',
      data: {
        ...batch,
        hmac_signature: batchHmac,
      },
    });
  }

  // Check Digital Certificate (HMAC-SHA256)
  if (CERTS_DB[rawCode]) {
    const stored = CERTS_DB[rawCode];
    const evaluatedScore = tamper ? 100.0 : stored.final_score;
    const recomputedHmac = computeHmac(
      stored.cert_code,
      stored.userid,
      stored.courseid,
      evaluatedScore,
      stored.timecreated
    );

    const bufStored = Buffer.from(stored.hmac_signature, 'utf8');
    const bufRecomputed = Buffer.from(recomputedHmac, 'utf8');
    const hmacValid =
      bufStored.length === bufRecomputed.length &&
      crypto.timingSafeEqual(bufStored, bufRecomputed);

    return res.status(200).json({
      ok: true,
      found: true,
      type: 'certificate',
      tampered_simulation: tamper,
      hmac_valid: hmacValid,
      data: {
        cert_code: stored.cert_code,
        learner_name: stored.learner_name,
        farm_name: stored.farm_name,
        course_shortname: stored.course_shortname,
        course_fullname: stored.course_fullname,
        final_score: evaluatedScore,
        stored_score: stored.final_score,
        timecreated: stored.timecreated,
        issued_at: new Date(stored.timecreated * 1000).toLocaleString('vi-VN', { timeZone: 'Asia/Ho_Chi_Minh' }),
        stored_hmac: stored.hmac_signature,
        recomputed_hmac: recomputedHmac,
      },
    });
  }

  return res.status(404).json({
    ok: false,
    found: false,
    code: rawCode,
    message_vi: `Không tìm thấy mã "${rawCode}" trong sổ cái chứng nhận số hoặc lô nông sản VietGAP.`,
    message_en: `Code "${rawCode}" was not found in the digital certificate or VietGAP batch ledger.`,
  });
};
