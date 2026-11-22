const {
  hasSupabaseConfig,
  supabaseFetch,
  computeCertHmac,
  computeBatchHmac,
  timingSafeHexEqual,
  formatViDateTime,
  ACTIVITY_LABELS,
  FALLBACK_CERTS,
  FALLBACK_BATCHES,
  FALLBACK_LOGS,
} = require('./_supabase');

function formatBatchResponse(batch, logs, source) {
  const expectedBatchHmac = batch.qr_token
    ? computeBatchHmac(batch.batch_code, batch.region, batch.area_m2, batch.qr_token)
    : batch.hmac_signature;
  const hmacValid = batch.hmac_signature
    ? timingSafeHexEqual(batch.hmac_signature, expectedBatchHmac)
    : true;

  return {
    ok: true,
    found: true,
    type: 'batch',
    source,
    hmac_valid: hmacValid,
    data: {
      batch_code: batch.batch_code,
      product_name: batch.product_name,
      crop_variety: batch.crop_variety,
      farmer_name: batch.farmer_name || 'Nông hộ Mầm Xanh',
      farm_name: batch.farm_name || 'Nông trại Mầm Xanh',
      region: batch.region,
      gps: batch.gps_coords,
      gps_coords: batch.gps_coords,
      area_m2: Number(batch.area_m2),
      expected_yield_kg: Number(batch.expected_yield_kg),
      vietgap_status: batch.vietgap_status || 'active',
      standard: 'VietGAP TCVN 11892-1:2017',
      hmac_signature: batch.hmac_signature || expectedBatchHmac,
      created_at: formatViDateTime(batch.created_at),
      logs: logs.map(l => ({
        id: l.id,
        time: formatViDateTime(l.logged_at),
        logged_at: l.logged_at,
        activity_type: l.activity_type,
        activity_label: ACTIVITY_LABELS[l.activity_type] || l.activity_type,
        activity: l.notes,
        notes: l.notes,
        ph: String(l.ph_val),
        ec: String(l.ec_val),
        phi: Number(l.phi_days || 0),
      })),
    },
  };
}

function formatCertResponse(stored, tamper, source) {
  const storedScore = Number(stored.final_score);
  const evaluatedScore = tamper ? (storedScore === 100 ? 99.0 : 100.0) : storedScore;
  const issuedUnix = Number(
    stored.issued_unix ||
      Math.floor(new Date(stored.issued_at || Date.now()).getTime() / 1000)
  );
  const recomputedHmac = computeCertHmac(
    stored.cert_code,
    stored.moodle_userid ?? 0,
    stored.moodle_courseid ?? 0,
    evaluatedScore,
    issuedUnix
  );
  const hmacValid = timingSafeHexEqual(stored.hmac_signature, recomputedHmac);

  return {
    ok: true,
    found: true,
    type: 'certificate',
    source,
    tampered_simulation: tamper,
    hmac_valid: hmacValid,
    data: {
      cert_code: stored.cert_code,
      learner_name: stored.student_name,
      student_name: stored.student_name,
      student_username: stored.student_username,
      farm_name: stored.farm_name,
      course_shortname: stored.course_code,
      course_code: stored.course_code,
      course_fullname: stored.course_name,
      course_name: stored.course_name,
      final_score: evaluatedScore,
      stored_score: storedScore,
      status: stored.status || 'valid',
      timecreated: issuedUnix,
      issued_at: formatViDateTime(stored.issued_at || issuedUnix),
      stored_hmac: stored.hmac_signature,
      recomputed_hmac: recomputedHmac,
    },
  };
}

module.exports = async function handler(req, res) {
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.setHeader('Cache-Control', 'no-store');

  const rawCode = String((req.query && req.query.code) || '').trim().toUpperCase();
  const tamper = String((req.query && req.query.tamper) || '0') === '1';

  const supabaseEnabled = hasSupabaseConfig();

  if (!rawCode) {
    return res.status(200).json({
      ok: true,
      mode: 'info',
      source: supabaseEnabled ? 'supabase-live' : 'embedded-fallback',
      sample_certs: Object.keys(FALLBACK_CERTS),
      sample_batches: Object.keys(FALLBACK_BATCHES),
    });
  }

  let fallbackReason = null;

  if (supabaseEnabled) {
    try {
      const encoded = encodeURIComponent(rawCode);
      const batchRows = await supabaseFetch(
        `agriops_batches?batch_code=eq.${encoded}&select=*&limit=1`
      );
      if (Array.isArray(batchRows) && batchRows.length > 0) {
        const logRows = await supabaseFetch(
          `agriops_logs?batch_code=eq.${encoded}&select=*&order=logged_at.desc,id.desc&limit=50`
        );
        return res.status(200).json(
          formatBatchResponse(batchRows[0], Array.isArray(logRows) ? logRows : [], 'supabase-live')
        );
      }

      const certRows = await supabaseFetch(
        `mamxanh_certificates?cert_code=eq.${encoded}&select=*&limit=1`
      );
      if (Array.isArray(certRows) && certRows.length > 0) {
        return res.status(200).json(
          formatCertResponse(certRows[0], tamper, 'supabase-live')
        );
      }

      return res.status(404).json({
        ok: false,
        found: false,
        source: 'supabase-live',
        code: rawCode,
        message_vi: `Không tìm thấy mã "${rawCode}" trong cơ sở dữ liệu Supabase (Chứng nhận số hoặc Lô nông sản VietGAP).`,
        message_en: `Code "${rawCode}" was not found in Supabase PostgreSQL (Certificates or VietGAP Batches).`,
      });
    } catch (err) {
      fallbackReason = err.message;
    }
  }

  // Embedded fallback mode (or if Supabase connection failed)
  if (FALLBACK_BATCHES[rawCode]) {
    const logs = FALLBACK_LOGS
      .filter(l => l.batch_code === rawCode)
      .sort((a, b) => new Date(b.logged_at) - new Date(a.logged_at));
    const payload = formatBatchResponse(FALLBACK_BATCHES[rawCode], logs, 'embedded-fallback');
    if (fallbackReason) payload.fallback_reason = fallbackReason;
    return res.status(200).json(payload);
  }

  if (FALLBACK_CERTS[rawCode]) {
    const payload = formatCertResponse(FALLBACK_CERTS[rawCode], tamper, 'embedded-fallback');
    if (fallbackReason) payload.fallback_reason = fallbackReason;
    return res.status(200).json(payload);
  }

  return res.status(404).json({
    ok: false,
    found: false,
    source: 'embedded-fallback',
    ...(fallbackReason ? { fallback_reason: fallbackReason } : {}),
    code: rawCode,
    message_vi: `Không tìm thấy mã "${rawCode}" trong sổ cái chứng nhận số hoặc lô nông sản VietGAP.`,
    message_en: `Code "${rawCode}" was not found in the digital certificate or VietGAP batch ledger.`,
  });
};
