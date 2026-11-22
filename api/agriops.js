const crypto = require('crypto');
const {
  RLS_HELP_VI,
  RLS_HELP_EN,
  assertCleanUtf8,
  setJsonHeaders,
  hasSupabaseConfig,
  isRlsPolicyError,
  formatSupabaseError,
  supabaseFetch,
  computeBatchHmac,
  parseJsonBody,
  formatViDateTime,
  ACTIVITY_LABELS,
  FALLBACK_BATCHES,
  FALLBACK_LOGS,
} = require('./_supabase');

function regionPrefix(region) {
  const norm = String(region || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase();
  if (norm.includes('dak lak') || norm.includes('daklak')) return 'DL';
  if (norm.includes('dong nai')) return 'DN';
  return 'LD';
}

function enrichLog(l) {
  return assertCleanUtf8({
    ...l,
    activity_label: ACTIVITY_LABELS[l.activity_type] || l.activity_type,
    time_display: formatViDateTime(l.logged_at),
  });
}

module.exports = async function handler(req, res) {
  setJsonHeaders(res);

  const method = String(req.method || 'GET').toUpperCase();
  const supabaseEnabled = hasSupabaseConfig();

  if (method === 'GET') {
    const filterBatch = assertCleanUtf8(String((req.query && req.query.batch_code) || '').trim().toUpperCase());

    if (supabaseEnabled) {
      try {
        const batches = await supabaseFetch(
          'agriops_batches?select=*&order=created_at.desc,id.desc&limit=30'
        );
        const logQuery = filterBatch
          ? `agriops_logs?batch_code=eq.${encodeURIComponent(filterBatch)}&select=*&order=logged_at.desc,id.desc&limit=50`
          : 'agriops_logs?select=*&order=logged_at.desc,id.desc&limit=30';
        const logs = await supabaseFetch(logQuery);
        const memLogs = FALLBACK_LOGS
          .filter(l => l.rls_fallback === true && (!filterBatch || l.batch_code === filterBatch));

        return res.status(200).json({
          ok: true,
          source: 'supabase-live',
          batches: Array.isArray(batches) ? batches : [],
          logs: [...memLogs, ...(Array.isArray(logs) ? logs : [])].map(enrichLog),
        });
      } catch (err) {
        const fallbackLogs = FALLBACK_LOGS
          .filter(l => !filterBatch || l.batch_code === filterBatch)
          .slice()
          .sort((a, b) => new Date(b.logged_at) - new Date(a.logged_at))
          .slice(0, 30)
          .map(enrichLog);
        return res.status(200).json({
          ok: true,
          source: 'embedded-fallback',
          fallback_reason: formatSupabaseError(err, 'đọc sổ nhật ký VietGAP'),
          batches: Object.values(FALLBACK_BATCHES),
          logs: fallbackLogs,
        });
      }
    }

    const fallbackLogs = FALLBACK_LOGS
      .filter(l => !filterBatch || l.batch_code === filterBatch)
      .slice()
      .sort((a, b) => new Date(b.logged_at) - new Date(a.logged_at))
      .slice(0, 30)
      .map(enrichLog);

    return res.status(200).json({
      ok: true,
      source: 'embedded-fallback',
      batches: Object.values(FALLBACK_BATCHES),
      logs: fallbackLogs,
    });
  }

  if (method === 'POST') {
    const body = parseJsonBody(req);
    const action = String(body.action || '').trim().toLowerCase();
    const isCreateBatch =
      action === 'create_batch' || (!body.batch_code && Boolean(body.product_name));

    if (isCreateBatch) {
      const productName = assertCleanUtf8(String(body.product_name || '').trim());
      const cropVariety = assertCleanUtf8(String(body.crop_variety || 'Giống F1 Chuẩn VietGAP').trim());
      const farmerName = assertCleanUtf8(String(body.farmer_name || 'Nông hộ Mầm Xanh').trim());
      const farmName = assertCleanUtf8(String(body.farm_name || 'Nông trại Công nghệ cao Đà Lạt').trim());
      const region = assertCleanUtf8(String(body.region || 'Lâm Đồng').trim());
      const gpsCoords = assertCleanUtf8(String(body.gps_coords || '11.946520, 108.504110').trim());
      const areaM2 = Math.max(10, parseInt(body.area_m2 || 1000, 10) || 1000);
      const expectedYieldKg = Math.max(10, parseInt(body.expected_yield_kg || 500, 10) || 500);

      if (!productName) {
        return res.status(400).json({
          ok: false,
          message_vi: 'Vui lòng nhập tên sản phẩm / lô hàng VietGAP.',
          message_en: 'Please provide product_name.',
        });
      }

      const suffix = String(crypto.randomInt(1000, 9999));
      const batchCode = `VG-${regionPrefix(region)}-202611-${suffix}`;
      const qrToken = crypto.randomBytes(16).toString('hex');
      const hmacSignature = computeBatchHmac(batchCode, region, areaM2, qrToken);
      const nowIso = new Date().toISOString();

      const row = assertCleanUtf8({
        batch_code: batchCode,
        product_name: productName,
        crop_variety: cropVariety,
        farmer_name: farmerName,
        farm_name: farmName,
        region,
        gps_coords: gpsCoords,
        area_m2: areaM2,
        expected_yield_kg: expectedYieldKg,
        vietgap_status: 'active',
        qr_token: qrToken,
        hmac_signature: hmacSignature,
        created_at: nowIso,
      });

      const initLog = assertCleanUtf8({
        batch_code: batchCode,
        activity_type: 'env_check',
        notes: `Khởi tạo lô nông sản VietGAP "${productName}" và ký số HMAC-SHA256 trên Cloud.`,
        ph_val: '6.0',
        ec_val: '1.5',
        phi_days: 0,
        logged_at: nowIso,
      });

      if (supabaseEnabled) {
        try {
          const inserted = await supabaseFetch('agriops_batches', {
            method: 'POST',
            headers: { Prefer: 'return=representation' },
            body: row,
          });
          await supabaseFetch('agriops_logs', {
            method: 'POST',
            headers: { Prefer: 'return=representation' },
            body: initLog,
          });
          return res.status(201).json({
            ok: true,
            source: 'supabase-live',
            persisted: true,
            type: 'batch',
            batch: Array.isArray(inserted) ? inserted[0] : row,
            message_vi: `Đã khởi tạo và ký số lô ${batchCode} trực tiếp lên Supabase PostgreSQL Live.`,
          });
        } catch (err) {
          if (isRlsPolicyError(err)) {
            FALLBACK_BATCHES[batchCode] = row;
            FALLBACK_LOGS.unshift({ id: Date.now(), rls_fallback: true, ...initLog });
            return res.status(201).json({
              ok: true,
              source: 'supabase-live',
              persisted: false,
              rls_fallback: true,
              type: 'batch',
              batch: row,
              rls_hint_vi: RLS_HELP_VI,
              rls_hint_en: RLS_HELP_EN,
              message_vi: `Đã khởi tạo lô ${batchCode} trên Cloud Edge. (${RLS_HELP_VI})`,
              message_en: `Created batch ${batchCode} via Cloud Edge buffer. (${RLS_HELP_EN})`,
            });
          }
          return res.status(502).json({
            ok: false,
            source: 'supabase-live',
            message_vi: formatSupabaseError(err, 'khởi tạo lô nông sản VietGAP'),
            message_en: `Supabase batch insert error: ${err.message}`,
          });
        }
      }

      FALLBACK_BATCHES[batchCode] = row;
      FALLBACK_LOGS.unshift({ id: Date.now(), ...initLog });
      return res.status(201).json({
        ok: true,
        source: 'embedded-fallback',
        persisted: false,
        type: 'batch',
        batch: row,
        message_vi: 'Đã tạo lô mẫu ở chế độ Fallback (chưa cấu hình SUPABASE_URL/SERVICE_KEY trên Vercel).',
      });
    }

    // Add field diary log
    const batchCode = assertCleanUtf8(String(body.batch_code || '').trim().toUpperCase());
    const activityType = assertCleanUtf8(String(body.activity_type || 'watering_fert').trim());
    const notes = assertCleanUtf8(String(body.notes || '').trim());
    const phVal = assertCleanUtf8(String(body.ph_val || '6.0').trim());
    const ecVal = assertCleanUtf8(String(body.ec_val || '1.6').trim());
    const phiDays = Math.max(0, parseInt(body.phi_days ?? 0, 10) || 0);

    if (!batchCode || !notes) {
      return res.status(400).json({
        ok: false,
        message_vi: 'Vui lòng chọn mã lô VietGAP và nhập nội dung ghi chú canh tác.',
        message_en: 'Please provide batch_code and notes.',
      });
    }

    const logRow = assertCleanUtf8({
      batch_code: batchCode,
      activity_type: ACTIVITY_LABELS[activityType] ? activityType : 'watering_fert',
      notes,
      ph_val: phVal,
      ec_val: ecVal,
      phi_days: phiDays,
      logged_at: new Date().toISOString(),
    });

    if (supabaseEnabled) {
      try {
        const existing = await supabaseFetch(
          `agriops_batches?batch_code=eq.${encodeURIComponent(batchCode)}&select=batch_code&limit=1`
        );
        if ((!Array.isArray(existing) || existing.length === 0) && !FALLBACK_BATCHES[batchCode]) {
          return res.status(404).json({
            ok: false,
            source: 'supabase-live',
            message_vi: `Mã lô "${batchCode}" chưa tồn tại trong bảng agriops_batches.`,
            message_en: `Batch "${batchCode}" does not exist in agriops_batches.`,
          });
        }

        const inserted = await supabaseFetch('agriops_logs', {
          method: 'POST',
          headers: { Prefer: 'return=representation' },
          body: logRow,
        });
        const saved = Array.isArray(inserted) ? inserted[0] : logRow;
        return res.status(201).json({
          ok: true,
          source: 'supabase-live',
          persisted: true,
          type: 'log',
          log: enrichLog(saved),
          message_vi: `Đã lưu dòng nhật ký vào Supabase PostgreSQL Live cho lô ${batchCode}.`,
        });
      } catch (err) {
        if (isRlsPolicyError(err)) {
          const memRow = { id: Date.now(), rls_fallback: true, ...logRow };
          FALLBACK_LOGS.unshift(memRow);
          return res.status(201).json({
            ok: true,
            source: 'supabase-live',
            persisted: false,
            rls_fallback: true,
            type: 'log',
            log: enrichLog(memRow),
            rls_hint_vi: RLS_HELP_VI,
            rls_hint_en: RLS_HELP_EN,
            message_vi: `Đã ghi nhận nhật ký cho lô ${batchCode} qua bộ nhớ Cloud Edge. (${RLS_HELP_VI})`,
            message_en: `Saved diary log for ${batchCode} via Cloud Edge buffer. (${RLS_HELP_EN})`,
          });
        }
        return res.status(502).json({
          ok: false,
          source: 'supabase-live',
          message_vi: formatSupabaseError(err, 'lưu nhật ký canh tác'),
          message_en: `Supabase log insert error: ${err.message}`,
        });
      }
    }

    if (!FALLBACK_BATCHES[batchCode]) {
      return res.status(404).json({
        ok: false,
        source: 'embedded-fallback',
        message_vi: `Không tìm thấy lô "${batchCode}" trong chế độ Fallback.`,
      });
    }

    const memRow = { id: Date.now(), ...logRow };
    FALLBACK_LOGS.unshift(memRow);
    return res.status(201).json({
      ok: true,
      source: 'embedded-fallback',
      persisted: false,
      type: 'log',
      log: enrichLog(memRow),
      message_vi: 'Đã ghi tạm nhật ký ở chế độ Fallback (cấu hình SUPABASE_URL & SUPABASE_SERVICE_KEY để lưu vĩnh viễn trên PostgreSQL).',
    });
  }

  return res.status(405).json({ ok: false, message_en: `Method ${method} not allowed` });
};
