const {
  hasSupabaseConfig,
  supabaseFetch,
  parseJsonBody,
  formatViDateTime,
  FALLBACK_FERTIGATION,
} = require('./_supabase');

module.exports = async function handler(req, res) {
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.setHeader('Cache-Control', 'no-store');

  const method = String(req.method || 'GET').toUpperCase();
  const supabaseEnabled = hasSupabaseConfig();

  if (method === 'GET') {
    if (supabaseEnabled) {
      try {
        const rows = await supabaseFetch(
          'fertigation_records?select=*&order=recorded_at.desc,id.desc&limit=10'
        );
        return res.status(200).json({
          ok: true,
          source: 'supabase-live',
          records: (Array.isArray(rows) ? rows : []).map(r => ({
            ...r,
            recorded_at_display: formatViDateTime(r.recorded_at),
          })),
        });
      } catch (err) {
        return res.status(200).json({
          ok: true,
          source: 'embedded-fallback',
          fallback_reason: err.message,
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
    const cropStage = String(body.crop_stage || '').trim();
    const tankVolumeL = Number(body.tank_volume_l);
    const targetEc = Number(body.target_ec);
    const targetPh = String(body.target_ph || '5.8 - 6.2').trim();
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

    const row = {
      crop_stage: cropStage,
      tank_volume_l: Number(tankVolumeL.toFixed(2)),
      target_ec: Number(targetEc.toFixed(2)),
      target_ph: targetPh,
      stock_a_ml: Number(stockAMl.toFixed(2)),
      stock_b_ml: Number(stockBMl.toFixed(2)),
      acid_ml: Number(acidMl.toFixed(2)),
      recorded_at: new Date().toISOString(),
    };

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
        });
      } catch (err) {
        return res.status(502).json({
          ok: false,
          source: 'supabase-live',
          message_vi: `Lỗi lưu công thức pha phân lên Supabase: ${err.message}`,
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
