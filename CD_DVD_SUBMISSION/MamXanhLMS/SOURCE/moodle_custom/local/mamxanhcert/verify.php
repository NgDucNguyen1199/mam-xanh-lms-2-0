<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');

local_mamxanhapp_handle_lang_switch();

$code = optional_param('code', '', PARAM_RAW_TRIMMED);
$tamper = optional_param('tamper', 0, PARAM_INT);

// Smart Cross-Routing: If user entered a VietGAP Batch Code (VG-...), redirect to trace.php
if ($code !== '' && (stripos($code, 'VG-') === 0 || ($DB->get_manager()->table_exists('local_agriops_batch') && $DB->record_exists('local_agriops_batch', ['batch_code' => $code])))) {
    redirect(new moodle_url('/local/agriops/trace.php', ['code' => $code]));
}

$syscontext = context_system::instance();
$PAGE->set_context($syscontext);
$urlparams = [];
if ($code !== '') {
    $urlparams['code'] = $code;
}
if ($tamper === 1) {
    $urlparams['tamper'] = 1;
}
$PAGE->set_url(new moodle_url('/local/mamxanhcert/verify.php', $urlparams));
$PAGE->set_pagelayout('standard');

$title = local_mamxanhapp_t('Xác thực Chứng nhận Số QR (HMAC-SHA256)', 'Digital QR Certificate Verification (HMAC-SHA256)');
$PAGE->set_title($title);
$PAGE->set_heading('' . $title);

$cert = null;
$learner = null;
$course = null;
$hmacvalid = false;
$recomputedhmac = '';

if ($code !== '' && $DB->get_manager()->table_exists('local_mamxanhcert')) {
    $cert = $DB->get_record('local_mamxanhcert', ['cert_code' => $code]);
    if ($cert) {
        if ($tamper === 1) {
            // In-memory tamper simulation (modifying score to 100.00 without changing DB)
            $cert = clone $cert;
            $cert->final_score = 100.00;
        }
        $hmacvalid = local_mamxanhcert_verify_record($cert);
        $recomputedhmac = local_mamxanhcert_compute_hmac(
            (string)$cert->cert_code,
            (int)$cert->userid,
            (int)$cert->courseid,
            (float)$cert->final_score,
            (int)$cert->timecreated
        );
        $learner = $DB->get_record('user', ['id' => $cert->userid]);
        $course = $DB->get_record('course', ['id' => $cert->courseid]);
    }
}

$samplecerts = [];
if ($DB->get_manager()->table_exists('local_mamxanhcert')) {
    $samplecerts = $DB->get_records('local_mamxanhcert', ['status' => 'valid'], 'id ASC', 'id,cert_code', 0, 3);
}

echo $OUTPUT->header();
?>

<div style="max-width:860px;margin:0 auto;">
    <!-- Public Lookup Form & 1-Click Sample Verification Pills -->
    <div style="background:#FFF;border:1px solid #E6ECE9;border-radius:16px;padding:22px;margin-bottom:20px;">
        <form method="get" action="<?php echo (new moodle_url('/local/mamxanhcert/verify.php'))->out(false); ?>" class="d-flex flex-wrap mb-3" style="gap:10px;">
            <input type="text" name="code" class="form-control" style="flex:1;min-width:220px;min-height:44px;border-radius:8px;font-weight:600;" placeholder="<?php echo s(local_mamxanhapp_t('Nhập mã chứng nhận (MXCERT-...) hoặc mã lô VietGAP (VG-...)', 'Enter certificate code (MXCERT-...) or VietGAP batch (VG-...)')); ?>" value="<?php echo s($code); ?>">
            <button type="submit" class="btn btn-primary" style="min-height:44px;border-radius:8px;font-weight:600;padding:0 24px;background:#234E3E;border-color:#234E3E;">
                <?php echo s(local_mamxanhapp_t('Kiểm tra Chữ ký Số', 'Verify HMAC Signature')); ?>
            </button>
        </form>

        <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
            <span class="small text-muted fw-bold"><?php echo s(local_mamxanhapp_t('Mã mẫu kiểm tra 1 chạm:', '1-Click Sample Codes:')); ?></span>
            <?php foreach ($samplecerts as $sc): ?>
            <a href="<?php echo (new moodle_url('/local/mamxanhcert/verify.php', ['code' => $sc->cert_code]))->out(false); ?>" class="btn btn-sm btn-secondary" style="font-family:monospace;font-weight:700;">
                <?php echo s($sc->cert_code); ?>
            </a>
            <?php endforeach; ?>
            <?php if (!empty($samplecerts)):
                $firstsample = reset($samplecerts);
            ?>
            <a href="<?php echo (new moodle_url('/local/mamxanhcert/verify.php', ['code' => $firstsample->cert_code, 'tamper' => 1]))->out(false); ?>" class="btn btn-sm btn-outline-danger" style="font-weight:700;min-height:44px;display:inline-flex;align-items:center;">
                <?php echo s(local_mamxanhapp_t('Thử Kịch bản Giả mạo Điểm (Tamper Test)', 'Simulate Score Tampering')); ?>
            </a>
            <?php endif; ?>
            <a href="<?php echo (new moodle_url('/local/agriops/trace.php'))->out(false); ?>" class="btn btn-sm btn-secondary">
                <?php echo s(local_mamxanhapp_t('Tra cứu Lô VietGAP →', 'VietGAP Batch Lookup →')); ?>
            </a>
        </div>
    </div>

    <?php if ($code === ''): ?>
    <div style="background:#F9FAF9;border:1px solid #E6ECE9;border-radius:16px;padding:24px;">
        <h4 style="color:#234E3E;font-weight:700;margin-bottom:8px;">
            <?php echo s(local_mamxanhapp_t('Cơ chế Xác thực Mật mã học HMAC-SHA256 (RFC 2104)', 'Cryptographic HMAC-SHA256 Verification Protocol (RFC 2104)')); ?>
        </h4>
        <p class="mb-2 text-muted">
            <?php echo s(local_mamxanhapp_t(
                'Mỗi chứng nhận hoàn thành tập huấn được ký số định danh từ tổ hợp: Mã chứng nhận | ID Học viên | ID Khóa học | Điểm tổng kết | Thời gian cấp. Bất kỳ hành vi sửa đổi điểm số trái phép nào trong CSDL đều làm thay đổi mã băm SHA-256 và bị phát hiện tức thời.',
                'Every issued training certificate is cryptographically signed across: Certificate Code | Learner ID | Course ID | Final Score | Timestamp. Any unauthorized database modification alters the SHA-256 digest and triggers an immediate tamper alert.'
            )); ?>
        </p>
        <div class="small fw-bold" style="color:#234E3E;">
            <?php echo s(local_mamxanhapp_t('Hãy bấm chọn một Mã mẫu ở trên để kiểm tra trực tiếp chữ ký hợp lệ hoặc kịch bản chống giả mạo.', 'Select any sample pill above to test live signature verification or tamper detection.')); ?>
        </div>
    </div>
    <?php elseif ($code !== '' && !$cert): ?>
    <div style="background:#FFF5F5;border:2px solid #C62828;border-radius:16px;padding:28px;text-align:center;">
        <h3 style="color:#B71C1C;font-weight:700;"><?php echo s(local_mamxanhapp_t('KHÔNG TÌM THẤY CHỨNG NHẬN', 'CERTIFICATE NOT FOUND')); ?></h3>
        <p class="mb-0"><?php echo s(local_mamxanhapp_t('Mã "' . $code . '" không tồn tại trong sổ cái chứng nhận số Mầm Xanh LMS.', 'Code "' . $code . '" does not match any issued digital certificate.')); ?></p>
    </div>
    <?php elseif ($cert):
        $verifyurl = (new moodle_url('/local/mamxanhcert/verify.php', ['code' => $cert->cert_code]))->out(false);
        $qrsvg = \local_mamxanhapp\qr_helper::render_svg($verifyurl, 140, $hmacvalid ? '#234E3E' : '#C62828');
    ?>
    <div id="mx-verify-result-card" style="background:<?php echo $hmacvalid ? '#EAF2EE' : '#FFF5F5'; ?>;border:2px solid <?php echo $hmacvalid ? '#234E3E' : '#C62828'; ?>;border-radius:16px;padding:28px;">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:16px;">
            <div style="flex:1;min-width:240px;">
                <span style="display:inline-block;background:<?php echo $hmacvalid ? '#234E3E' : '#C62828'; ?>;color:#FFF;padding:6px 16px;border-radius:999px;font-weight:700;margin-bottom:10px;">
                    <?php echo $hmacvalid
                        ? s(local_mamxanhapp_t('XÁC THỰC HỢP LỆ • HMAC-SHA256 VERIFIED', 'AUTHENTIC • HMAC-SHA256 VERIFIED'))
                        : s(local_mamxanhapp_t('CHỮ KÝ SỐ KHÔNG HỢP LỆ / ĐÃ BỊ CHỈNH SỬA TRÁI PHÉP', 'SIGNATURE TAMPERED OR REVOKED (HMAC MISMATCH)')); ?>
                </span>
                <h3 style="color:#111A16;font-weight:700;margin-bottom:6px;"><?php echo s($learner ? fullname($learner) : ''); ?></h3>
                <div style="font-size:1.05rem;font-weight:700;color:<?php echo $hmacvalid ? '#234E3E' : '#B71C1C'; ?>;margin-bottom:12px;">
                    <?php echo $course ? format_string($course->fullname) : ''; ?>
                </div>
                <ul style="list-style:none;padding:0;margin:0;line-height:1.85;font-size:0.94rem;">
                    <li><strong><?php echo s(local_mamxanhapp_t('Mã chứng nhận:', 'Certificate Code:')); ?></strong> <code><?php echo s($cert->cert_code); ?></code></li>
                    <li><strong><?php echo s(local_mamxanhapp_t('Kết quả cuối khóa:', 'Final Score:')); ?></strong> <strong><?php echo number_format((float)$cert->final_score, 1); ?>%</strong> <?php if ($tamper === 1): ?><span class="badge bg-danger"><?php echo s(local_mamxanhapp_t('Đã giả lập sửa điểm lên 100%', 'Tampered to 100%')); ?></span><?php endif; ?></li>
                    <li><strong><?php echo s(local_mamxanhapp_t('Thời điểm ký cấp:', 'Issued Timestamp:')); ?></strong> <?php echo date('d/m/Y H:i:s', (int)$cert->timecreated); ?></li>
                    <li style="word-break:break-all;"><strong><?php echo s(local_mamxanhapp_t('Chữ ký gốc lưu trữ (HMAC-SHA256):', 'Stored Original HMAC-SHA256:')); ?></strong> <code><?php echo s($cert->hmac_signature); ?></code></li>
                    <?php if (!$hmacvalid): ?>
                    <li style="word-break:break-all;color:#B71C1C;"><strong><?php echo s(local_mamxanhapp_t('Mã băm tính lại (Không khớp):', 'Recomputed Digest (Mismatch):')); ?></strong> <code><?php echo s($recomputedhmac); ?></code></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="text-center">
                <?php echo $qrsvg; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
echo $OUTPUT->footer();
