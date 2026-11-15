<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');

local_mamxanhapp_handle_lang_switch();

$code = optional_param('code', '', PARAM_RAW_TRIMMED);
$syscontext = context_system::instance();
$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/mamxanhcert/verify.php', $code !== '' ? ['code' => $code] : []));
$PAGE->set_pagelayout('standard');

$title = local_mamxanhapp_t('Xác thực Chứng nhận Số QR (HMAC-SHA256)', 'Digital QR Certificate Verification (HMAC-SHA256)');
$PAGE->set_title($title);
$PAGE->set_heading('' . $title);

$cert = null;
$learner = null;
$course = null;
$hmacvalid = false;

if ($code !== '' && $DB->get_manager()->table_exists('local_mamxanhcert')) {
    $cert = $DB->get_record('local_mamxanhcert', ['cert_code' => $code]);
    if ($cert) {
        $hmacvalid = local_mamxanhcert_verify_record($cert);
        $learner = $DB->get_record('user', ['id' => $cert->userid]);
        $course = $DB->get_record('course', ['id' => $cert->courseid]);
    }
}

echo $OUTPUT->header();
?>

<div style="max-width:820px;margin:0 auto;">
    <!-- Public Lookup Form -->
    <div style="background:#FFF;border:1px solid #E6ECE9;border-radius:16px;padding:22px;margin-bottom:20px;">
        <form method="get" action="<?php echo (new moodle_url('/local/mamxanhcert/verify.php'))->out(false); ?>" class="d-flex flex-wrap" style="gap:10px;">
            <input type="text" name="code" class="form-control" style="flex:1;min-width:220px;min-height:44px;border-radius:8px;font-weight:600;" placeholder="<?php echo s(local_mamxanhapp_t('Nhập mã chứng nhận (VD: MXCERT-2026-...)', 'Enter certificate code (e.g. MXCERT-2026-...)')); ?>" value="<?php echo s($code); ?>">
            <button type="submit" class="btn btn-primary" style="min-height:44px;border-radius:8px;font-weight:600;padding:0 24px;background:#234E3E;border-color:#234E3E;">
                <?php echo s(local_mamxanhapp_t('Kiểm tra Chữ ký Số', 'Verify HMAC Signature')); ?>
            </button>
        </form>
    </div>

    <?php if ($code !== '' && !$cert): ?>
    <div style="background:#FFF5F5;border:2px solid #C62828;border-radius:16px;padding:28px;text-align:center;">
        <h3 style="color:#B71C1C;font-weight:700;"><?php echo s(local_mamxanhapp_t('KHÔNG TÌM THẤY CHỨNG NHẬN', 'CERTIFICATE NOT FOUND')); ?></h3>
        <p class="mb-0"><?php echo s(local_mamxanhapp_t('Mã "' . $code . '" không tồn tại trong sổ cái chứng nhận số Mầm Xanh LMS.', 'Code "' . $code . '" does not match any issued digital certificate.')); ?></p>
    </div>
    <?php elseif ($cert):
        $verifyurl = (new moodle_url('/local/mamxanhcert/verify.php', ['code' => $cert->cert_code]))->out(false);
        $qrsvg = \local_mamxanhapp\qr_helper::render_svg($verifyurl, 140, '#234E3E');
    ?>
    <div id="mx-verify-result-card" style="background:<?php echo $hmacvalid ? '#EAF2EE' : '#FFF5F5'; ?>;border:2px solid <?php echo $hmacvalid ? '#234E3E' : '#C62828'; ?>;border-radius:16px;padding:28px;">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:16px;">
            <div style="flex:1;min-width:240px;">
                <span style="display:inline-block;background:<?php echo $hmacvalid ? '#234E3E' : '#C62828'; ?>;color:#FFF;padding:6px 16px;border-radius:999px;font-weight:700;margin-bottom:10px;">
                    <?php echo $hmacvalid
                        ? '' . s(local_mamxanhapp_t('XÁC THỰC HỢP LỆ • HMAC-SHA256 VERIFIED', 'AUTHENTIC • HMAC-SHA256 VERIFIED'))
                        : '' . s(local_mamxanhapp_t('CHỮ KÝ SỐ KHÔNG HỢP LỆ / THU HỒI', 'SIGNATURE TAMPERED OR REVOKED')); ?>
                </span>
                <h3 style="color:#111A16;font-weight:700;margin-bottom:6px;"><?php echo s($learner ? fullname($learner) : ''); ?></h3>
                <div style="font-size:1.05rem;font-weight:700;color:#234E3E;margin-bottom:12px;">
                    <?php echo $course ? format_string($course->fullname) : ''; ?>
                </div>
                <ul style="list-style:none;padding:0;margin:0;line-height:1.85;font-size:0.94rem;">
                    <li><strong><?php echo s(local_mamxanhapp_t('Mã chứng nhận:', 'Certificate Code:')); ?></strong> <code><?php echo s($cert->cert_code); ?></code></li>
                    <li><strong><?php echo s(local_mamxanhapp_t('Kết quả cuối khóa:', 'Final Score:')); ?></strong> <strong><?php echo number_format((float)$cert->final_score, 1); ?>%</strong></li>
                    <li><strong><?php echo s(local_mamxanhapp_t('Thời điểm ký cấp:', 'Issued Timestamp:')); ?></strong> <?php echo date('d/m/Y H:i:s', (int)$cert->timecreated); ?></li>
                    <li style="word-break:break-all;"><strong>HMAC-SHA256:</strong> <code><?php echo s($cert->hmac_signature); ?></code></li>
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
