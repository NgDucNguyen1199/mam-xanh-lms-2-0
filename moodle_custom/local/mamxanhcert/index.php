<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');

local_mamxanhapp_handle_lang_switch();
require_login();

$syscontext = context_system::instance();
$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/mamxanhcert/index.php'));
$PAGE->set_pagelayout('standard');

$title = local_mamxanhapp_t('Chứng nhận Số QR Ký số HMAC-SHA256 - Mầm Xanh LMS', 'Digital HMAC-SHA256 QR Certificates - Mam Xanh LMS');
$PAGE->set_title($title);
$PAGE->set_heading('🎓 ' . $title);

$userid = (int)$USER->id;
local_mamxanhcert_sync_user_certificates($userid);

$canmanage = is_siteadmin() || has_capability('local/mamxanhcert:manage', $syscontext);
if ($canmanage && optional_param('all', 0, PARAM_INT) === 1) {
    $certs = $DB->get_records_sql("
        SELECT mc.*, c.fullname AS coursename, c.shortname, u.firstname, u.lastname, u.username
          FROM {local_mamxanhcert} mc
          JOIN {course} c ON c.id = mc.courseid
          JOIN {user} u ON u.id = mc.userid
      ORDER BY mc.timecreated DESC
    ");
} else {
    $certs = $DB->get_records_sql("
        SELECT mc.*, c.fullname AS coursename, c.shortname, u.firstname, u.lastname, u.username
          FROM {local_mamxanhcert} mc
          JOIN {course} c ON c.id = mc.courseid
          JOIN {user} u ON u.id = mc.userid
         WHERE mc.userid = :uid
      ORDER BY mc.timecreated DESC
    ", ['uid' => $userid]);
}

$roletype = local_mamxanhapp_user_role_type();
$backurl = ($roletype === 'cbkt' || $roletype === 'admin')
    ? new moodle_url('/local/mamxanhapp/manage.php')
    : new moodle_url('/local/mamxanhapp/index.php');

echo $OUTPUT->header();
?>

<div class="mx-cert-list">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:10px;">
        <a href="<?php echo $backurl->out(false); ?>" class="btn btn-outline-success" style="min-height:48px;display:inline-flex;align-items:center;border-radius:14px;font-weight:700;border-width:2px;">
            <?php echo s(local_mamxanhapp_t('← Quay lại Dashboard', '← Back to Dashboard')); ?>
        </a>
        <a href="<?php echo (new moodle_url('/local/mamxanhcert/verify.php'))->out(false); ?>" class="btn btn-success" style="min-height:48px;display:inline-flex;align-items:center;border-radius:14px;font-weight:700;background:#1B4332;">
            🔍 <?php echo s(local_mamxanhapp_t('Cổng Xác thực QR Công khai', 'Public QR Verification Portal')); ?>
        </a>
    </div>

    <?php if (empty($certs)): ?>
    <div style="background:#FFF;border:2px solid #DCEEE0;border-radius:24px;padding:32px;text-align:center;">
        <div style="font-size:3rem;margin-bottom:8px;">🎓</div>
        <h4 style="color:#1B4332;font-weight:800;"><?php echo s(local_mamxanhapp_t('Chưa có chứng nhận nào được mở khóa', 'No Digital Certificates Unlocked Yet')); ?></h4>
        <p style="color:#4E6358;max-width:520px;margin:0 auto 18px;">
            <?php echo s(local_mamxanhapp_t(
                'Hãy hoàn thành bài kiểm tra trắc nghiệm cuối khóa đạt từ 70% trở lên để hệ thống tự động cấp Chứng nhận số ký HMAC-SHA256 kèm mã QR.',
                'Score 70% or higher on any course Quiz to automatically unlock your HMAC-SHA256 signed QR Certificate.'
            )); ?>
        </p>
        <a href="<?php echo (new moodle_url('/local/mamxanhapp/index.php'))->out(false); ?>" class="btn btn-success" style="min-height:48px;display:inline-flex;align-items:center;border-radius:14px;font-weight:800;padding:0 24px;background:#1B4332;">
            🌱 <?php echo s(local_mamxanhapp_t('Vào học & Làm bài Kiểm tra ngay', 'Go to Courses & Take Quiz')); ?>
        </a>
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($certs as $c):
            $verifyurl = (new moodle_url('/local/mamxanhcert/verify.php', ['code' => $c->cert_code]))->out(false);
            $viewurl = (new moodle_url('/local/mamxanhcert/view.php', ['code' => $c->cert_code]))->out(false);
            $pdfurl = (new moodle_url('/local/mamxanhcert/view.php', ['code' => $c->cert_code, 'download' => 'pdf']))->out(false);
            $qrsvg = \local_mamxanhapp\qr_helper::render_svg($verifyurl, 130, '#1B4332');
        ?>
        <div class="col-12 col-xl-6 mb-4">
            <div style="background:#FFF;border:2px solid #81C784;border-radius:24px;padding:22px;height:100%;box-shadow:0 8px 24px rgba(27,67,50,0.08);">
                <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
                    <div style="flex:1;min-width:210px;">
                        <span style="display:inline-block;background:#D8F3DC;color:#1B4332;padding:4px 12px;border-radius:999px;font-weight:800;font-size:0.82rem;margin-bottom:8px;">
                            🏅 <?php echo s($c->cert_code); ?>
                        </span>
                        <h4 style="color:#1B4332;font-weight:800;font-size:1.15rem;margin-bottom:6px;">
                            <?php echo format_string($c->coursename); ?>
                        </h4>
                        <div style="font-size:0.9rem;color:#37474F;margin-bottom:6px;">
                            👨‍🌾 <strong><?php echo s($c->firstname . ' ' . $c->lastname); ?></strong> •
                            🎯 <?php echo s(local_mamxanhapp_t('Điểm đạt: ', 'Score: ')); ?><strong><?php echo number_format((float)$c->final_score, 1); ?>%</strong>
                        </div>
                        <div style="font-size:0.8rem;color:#546E7A;margin-bottom:14px;">
                            🔐 HMAC-SHA256: <code><?php echo s(substr($c->hmac_signature, 0, 20) . '...'); ?></code>
                        </div>

                        <div class="d-flex flex-wrap" style="gap:8px;">
                            <a href="<?php echo $viewurl; ?>" class="btn btn-success btn-sm" style="min-height:44px;display:inline-flex;align-items:center;border-radius:12px;font-weight:700;padding:0 14px;background:#1B4332;">
                                📜 <?php echo s(local_mamxanhapp_t('Xem Chứng nhận', 'View Certificate')); ?>
                            </a>
                            <a href="<?php echo $pdfurl; ?>" class="btn btn-outline-success btn-sm" style="min-height:44px;display:inline-flex;align-items:center;border-radius:12px;font-weight:700;padding:0 14px;border-width:2px;">
                                ⬇️ PDF
                            </a>
                            <a href="<?php echo $verifyurl; ?>" class="btn btn-outline-secondary btn-sm" style="min-height:44px;display:inline-flex;align-items:center;border-radius:12px;font-weight:700;padding:0 14px;">
                                ✅ <?php echo s(local_mamxanhapp_t('Xác thực QR', 'Verify QR')); ?>
                            </a>
                        </div>
                    </div>
                    <div class="text-center">
                        <?php echo $qrsvg; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php
echo $OUTPUT->footer();
