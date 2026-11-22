const {
  RLS_HELP_VI,
  RLS_HELP_EN,
  assertCleanUtf8,
  setJsonHeaders,
  hasSupabaseConfig,
  isRlsPolicyError,
  formatSupabaseError,
  supabaseFetch,
  parseJsonBody,
  formatViDateTime,
  FALLBACK_FERTIGATION,
} = require('./_supabase');

module.exports = async function handler(req, res) {
  setJsonHeaders(res);

  const method = String(req.method || 'GET').toUpperCase();
  const supabaseEnabled = hasSupabaseConfig();

  if (method === 'GET') {
    if (supabaseEnabled) {
      try {
        const rows = await supabaseFetch(
          'fertigation_records?select=*&order=recorded_at.desc,id.desc&limit=10'
        );
        const dbRecords = (Array.isArray(rows) ? rows : []).map(r => assertCleanUtf8({
          ...r,
          recorded_at_display: formatViDateTime(r.recorded_at),
        }));
        const rlsRecords = FALLBACK_FERTIGATION.filter(r => r.rls_fallback === true);
        return res.status(200).json({
          ok: true,
          source: 'supabase-live',
          records: [...rlsRecords, ...dbRecords].slice(0, 10),
        });
      } catch (err) {
        return res.status(200).json({
          ok: true,
          source: 'embedded-fallback',
          fallback_reason: formatSupabaseError(err, 'đọc công thức pha phân'),
          records: FALLBACK_FERTIGATION,
        });
      }
    }

    return res.status(200).json({
      ok: true,
      source: 'embedded-fallback',
      records: FALLBACK_FERTIGATION,
    });
  }

  if (method === 'POST') {
    const body = parseJsonBody(req);
    const cropStage = assertCleanUtf8(String(body.crop_stage || '').trim());
    const tankVolumeL = Number(body.tank_volume_l);
    const targetEc = Number(body.target_ec);
    const targetPh = assertCleanUtf8(String(body.target_ph || '5.8 - 6.2').trim());
    const stockAMl = Number(body.stock_a_ml);
    const stockBMl = Number(body.stock_b_ml);
    const acidMl = Number(body.acid_ml);

    if (
      !cropStage ||
      !Number.isFinite(tankVolumeL) || tankVolumeL <= 0 ||
      !Number.isFinite(targetEc) || targetEc <= 0 ||
      !Number.isFinite(stockAMl) || stockAMl < 0 ||
      !Number.isFinite(stockBMl) || stockBMl < 0 ||
      !Number.isFinite(acidMl) || acidMl < 0
    ) {
      return res.status(400).json({
        ok: false,
        message_vi: 'Dữ liệu công thức pha phân không hợp lệ.',
        message_en: 'Invalid fertigation recipe parameters.',
      });
    }

    const row = assertCleanUtf8({
      crop_stage: cropStage,
      tank_volume_l: Number(tankVolumeL.toFixed(2)),
      target_ec: Number(targetEc.toFixed(2)),
      target_ph: targetPh,
      stock_a_ml: Number(stockAMl.toFixed(2)),
      stock_b_ml: Number(stockBMl.toFixed(2)),
      acid_ml: Number(acidMl.toFixed(2)),
      recorded_at: new Date().toISOString(),
    });

    if (supabaseEnabled) {
      try {
        const inserted = await supabaseFetch('fertigation_records', {
          method: 'POST',
          headers: { Prefer: 'return=representation' },
          body: row,
        });
        const saved = Array.isArray(inserted) ? inserted[0] : row;
        return res.status(201).json({
          ok: true,
          source: 'supabase-live',
          persisted: true,
          record: {
            ...saved,
            recorded_at_display: formatViDateTime(saved.recorded_at),
          },
          message_vi: 'Đã lưu công thức pha phân vào bảng fertigation_records trên Supabase PostgreSQL Live.',
        });
      } catch (err) {
        if (isRlsPolicyError(err)) {
          const memRow = {
            id: Date.now(),
            rls_fallback: true,
            ...row,
            recorded_at_display: formatViDateTime(row.recorded_at),
          };
          FALLBACK_FERTIGATION.unshift(memRow);
          return res.status(201).json({
            ok: true,
            source: 'supabase-live',
            persisted: false,
            rls_fallback: true,
            record: memRow,
            rls_hint_vi: RLS_HELP_VI,
            rls_hint_en: RLS_HELP_EN,
            message_vi: `Đã ghi nhận công thức pha phân qua bộ nhớ Cloud Edge. (${RLS_HELP_VI})`,
            message_en: `Saved fertigation recipe via Cloud Edge buffer. (${RLS_HELP_EN})`,
          });
        }
        return res.status(502).json({
          ok: false,
          source: 'supabase-live',
          message_vi: formatSupabaseError(err, 'lưu công thức pha phân'),
          message_en: `Supabase fertigation insert error: ${err.message}`,
        });
      }
    }

    const memRow = {
      id: Date.now(),
      ...row,
      recorded_at_display: formatViDateTime(row.recorded_at),
    };
    FALLBACK_FERTIGATION.unshift(memRow);
    return res.status(201).json({
      ok: true,
      source: 'embedded-fallback',
      persisted: false,
      record: memRow,
      message_vi: 'Đã lưu tạm công thức ở chế độ Fallback (cấu hình SUPABASE_URL & SUPABASE_SERVICE_KEY trên Vercel để lưu vĩnh viễn).',
    });
  }

  return res.status(405).json({ ok: false, message_en: `Method ${method} not allowed` });
};
