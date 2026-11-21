<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');

local_mamxanhapp_handle_lang_switch();
require_login();

if (isguestuser()) {
    redirect(new moodle_url('/login/index.php'));
}

$syscontext = context_system::instance();
$roletype = local_mamxanhapp_user_role_type();
$iscbktadmin = ($roletype === 'cbkt' || $roletype === 'admin' || is_siteadmin());

$targetuserid = optional_param('userid', (int)$USER->id, PARAM_INT);
if (!$iscbktadmin) {
    $targetuserid = (int)$USER->id;
}
$batchid = optional_param('batchid', 0, PARAM_INT);
$courseid = optional_param('courseid', 2, PARAM_INT);
$format = optional_param('format', 'html', PARAM_ALPHA);
$download = optional_param('download', '', PARAM_ALPHA);

// Ensure user has seeded batches & logs
local_agriops_ensure_seed_data($targetuserid);

$learner = $DB->get_record('user', ['id' => $targetuserid, 'deleted' => 0]);
if (!$learner) {
    $learner = $USER;
    $targetuserid = (int)$USER->id;
}

$batches = local_agriops_get_user_batches($targetuserid);
if (empty($batches)) {
    $batches = $DB->get_records('local_agriops_batch', null, 'id ASC', '*', 0, 3);
}
$selectedbatch = null;
if ($batchid > 0 && isset($batches[$batchid])) {
    $selectedbatch = $batches[$batchid];
} else if (!empty($batches)) {
    $selectedbatch = reset($batches);
    $batchid = (int)$selectedbatch->id;
}

$logs = $selectedbatch ? local_agriops_get_batch_logs((int)$selectedbatch->id) : [];
$secret = $CFG->mamxanh_hmac_secret ?? 'mamxanh_lms_2026_hmac_key';
$verifypayload = ($selectedbatch ? $selectedbatch->batch_code : 'BATCH') . '|' . $learner->username . '|' . count($logs);
$hmachash = hash_hmac('sha256', $verifypayload, $secret);

$en = local_mamxanhapp_is_en();

// 1. Handle CSV Download (?format=csv or ?download=csv)
if ($format === 'csv' || $download === 'csv') {
    $filename = 'VietGAP_Log_' . clean_filename($learner->username) . '_' . date('Ymd') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    // UTF-8 BOM for Excel compatibility
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['MAM XANH LMS 2.0 - VIETGAP ELECTRONIC FIELD LOGBOOK REPORT (TCVN 11892-1:2017)']);
    fputcsv($out, ['Learner', fullname($learner), 'Username', $learner->username]);
    if ($selectedbatch) {
        fputcsv($out, [
            'Batch Code', $selectedbatch->batch_code,
            'Product', $selectedbatch->product_name,
            'Variety', $selectedbatch->crop_variety,
            'Region', $selectedbatch->region,
            'Area (m2)', $selectedbatch->area_m2,
        ]);
    }
    fputcsv($out, ['HMAC-SHA256 Verification Hash', $hmachash]);
    fputcsv($out, []);
    fputcsv($out, ['No.', 'Timestamp', 'Operation Type', 'pH', 'EC (mS/cm)', 'PHI (Days)', 'Detailed Technical Notes']);
    $idx = 0;
    foreach ($logs as $l) {
        $idx++;
        fputcsv($out, [
            $idx,
            date('Y-m-d H:i', (int)$l->log_time),
            $l->activity_type,
            number_format((float)$l->ph_val, 2),
            number_format((float)$l->ec_val, 2),
            (int)$l->phi_days,
            $l->notes,
        ]);
    }
    fclose($out);
    exit;
}

// 2. Handle Standalone HTML Report File Download (?download=html) for mod_assign upload
if ($download === 'html') {
    $filename = 'BaoCao_NhatKy_VietGAP_' . clean_filename($learner->username) . '.html';
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "<!DOCTYPE html>\n<html lang=\"vi\">\n<head>\n<meta charset=\"utf-8\">\n";
    echo "<title>Báo cáo Thực hành Nhật ký Canh tác VietGAP - " . s(fullname($learner)) . "</title>\n";
    echo "<style>body{font-family:Arial,sans-serif;color:#0F172A;margin:24px;line-height:1.5;}table{width:100%;border-collapse:collapse;margin-top:16px;}th,td{border:1px solid #CBD5E1;padding:10px;text-align:left;font-size:13px;}th{background:#EAF2EE;color:#234E3E;}h1,h2{color:#234E3E;margin-bottom:6px;}.meta{background:#F9FAF9;border:1px solid #E6ECE9;padding:14px;border-radius:8px;margin-bottom:16px;}</style>\n";
    echo "</head>\n<body>\n";
    echo "<h1>BÁO CÁO THỰC HÀNH NHẬT KÝ ĐỒNG RUỘNG ĐIỆN TỬ VIETGAP (TCVN 11892-1:2017)</h1>\n";
    echo "<div class=\"meta\">";
    echo "<p><strong>Học viên thực hiện:</strong> " . s(fullname($learner)) . " (" . s($learner->username) . ")</p>";
    if ($selectedbatch) {
        echo "<p><strong>Mã lô canh tác:</strong> " . s($selectedbatch->batch_code) . " — <strong>Sản phẩm:</strong> " . s($selectedbatch->product_name) . " (" . s($selectedbatch->crop_variety) . ")</p>";
        echo "<p><strong>Vùng trồng:</strong> " . s($selectedbatch->region) . " — <strong>Diện tích:</strong> " . number_format((int)$selectedbatch->area_m2) . " m² — <strong>Sản lượng dự kiến:</strong> " . number_format((int)$selectedbatch->expected_yield_kg) . " kg</p>";
    }
    echo "<p><strong>Chữ ký số xác thực HMAC-SHA256:</strong> <code>" . s($hmachash) . "</code></p>";
    echo "</div>\n";
    echo "<table><thead><tr><th>STT</th><th>Thời gian</th><th>Hạng mục tác nghiệp</th><th>pH</th><th>EC (mS/cm)</th><th>Cách ly PHI</th><th>Chi tiết kỹ thuật thực hiện</th></tr></thead><tbody>";
    $i = 0;
    foreach ($logs as $l) {
        $i++;
        echo "<tr><td>{$i}</td><td>" . date('d/m/Y H:i', (int)$l->log_time) . "</td><td><strong>" . s($l->activity_type) . "</strong></td><td>" . number_format((float)$l->ph_val, 1) . "</td><td>" . number_format((float)$l->ec_val, 1) . "</td><td>" . (int)$l->phi_days . " ngày</td><td>" . s($l->notes) . "</td></tr>";
    }
    echo "</tbody></table>\n</body>\n</html>";
    exit;
}

// 3. Render Interactive & Printable Moodle View
$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/agriops/export_log.php', ['batchid' => $batchid, 'courseid' => $courseid, 'userid' => $targetuserid]));
$PAGE->set_pagelayout('standard');

$title = local_mamxanhapp_t('Trích xuất Báo cáo Thực hành Nhật ký VietGAP', 'Export VietGAP Practical Cultivation Report');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$courseurl = (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false);
$agriopsurl = (new moodle_url('/local/agriops/index.php', ['batchid' => $batchid]))->out(false);
$dlhtmlurl = (new moodle_url('/local/agriops/export_log.php', ['batchid' => $batchid, 'courseid' => $courseid, 'userid' => $targetuserid, 'download' => 'html']))->out(false);
$dlcsvurl = (new moodle_url('/local/agriops/export_log.php', ['batchid' => $batchid, 'courseid' => $courseid, 'userid' => $targetuserid, 'format' => 'csv']))->out(false);

echo $OUTPUT->header();
?>

<style>
.mx-vietgap-export-sheet {
    background: #FFFFFF;
    border: 1px solid #E6ECE9;
    border-radius: 12px;
    padding: 32px 36px;
    max-width: 1040px;
    margin: 0 auto 32px;
    color: #0F172A;
}
.mx-vg-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    margin-top: 16px;
}
.mx-vg-table th {
    background: #EAF2EE;
    color: #234E3E;
    font-weight: 700;
    padding: 11px 12px;
    border: 1px solid #CBD5E1;
}
.mx-vg-table td {
    padding: 11px 12px;
    border: 1px solid #E2E8F0;
    vertical-align: top;
}
@media print {
    nav.navbar, #page-header, .secondary-navigation, .drawer-toggles, .drawer, footer#page-footer, .mx-back-nav-bar, .mx-no-print {
        display: none !important;
    }
    .mx-vietgap-export-sheet {
        border: 1px solid #0F172A !important;
        padding: 16px !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
}
</style>

<div class="mx-no-print mb-3 d-flex flex-wrap justify-content-between align-items-center" style="max-width:1040px;margin:0 auto 16px;gap:12px;">
    <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
        <a href="<?php echo $courseurl; ?>" class="btn btn-secondary">
            <?php echo s(local_mamxanhapp_t('← Quay lại Khóa học', '← Back to Course')); ?>
        </a>
        <a href="<?php echo $agriopsurl; ?>" class="btn btn-secondary">
            <?php echo s(local_mamxanhapp_t('Mở Sổ Nhật ký VietGAP', 'Open VietGAP Logbook')); ?>
        </a>
        <?php if (count($batches) > 1): ?>
        <form method="get" action="<?php echo (new moodle_url('/local/agriops/export_log.php'))->out(false); ?>" class="d-inline-flex align-items-center" style="gap:6px;">
            <input type="hidden" name="courseid" value="<?php echo (int)$courseid; ?>">
            <input type="hidden" name="userid" value="<?php echo (int)$targetuserid; ?>">
            <select name="batchid" class="form-control form-control-sm" onchange="this.form.submit()">
                <?php foreach ($batches as $b): ?>
                <option value="<?php echo (int)$b->id; ?>" <?php echo ((int)$b->id === $batchid) ? 'selected' : ''; ?>>
                    <?php echo s($b->batch_code . ' — ' . $b->product_name); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php endif; ?>
    </div>

    <div class="d-flex flex-wrap" style="gap:8px;">
        <a href="<?php echo $dlhtmlurl; ?>" class="btn btn-primary">
            <?php echo s(local_mamxanhapp_t('Tải File Báo cáo (.HTML) để Nộp bài', 'Download Report (.HTML) for Submission')); ?>
        </a>
        <a href="<?php echo $dlcsvurl; ?>" class="btn btn-secondary">
            <?php echo s(local_mamxanhapp_t('Tải Dữ liệu (.CSV)', 'Download CSV')); ?>
        </a>
        <button type="button" class="btn btn-secondary" onclick="window.print()">
            <?php echo s(local_mamxanhapp_t('In Báo cáo (A4)', 'Print Report (A4)')); ?>
        </button>
    </div>
</div>

<div class="mx-vietgap-export-sheet">
    <div class="d-flex flex-wrap justify-content-between align-items-start pb-3 mb-3" style="border-bottom:2px solid #234E3E;gap:16px;">
        <div>
            <div class="small fw-bold text-uppercase" style="color:#234E3E;">
                <?php echo s(local_mamxanhapp_t('HỒ SƠ BÁO CÁO THỰC HÀNH NÔNG NGHIỆP CÔNG NGHỆ CAO • TCVN 11892-1:2017', 'HIGH-TECH AGRICULTURAL PRACTICAL REPORT • VIETGAP TCVN 11892-1:2017')); ?>
            </div>
            <h2 class="h3 fw-bold mt-1 mb-1"><?php echo s(local_mamxanhapp_t('TRÍCH XUẤT NHẬT KÝ ĐỒNG RUỘNG ĐIỆN TỬ VIETGAP', 'VERIFIED ELECTRONIC VIETGAP FIELD LOGBOOK EXTRACT')); ?></h2>
            <div class="small text-muted">
                <?php echo s(local_mamxanhapp_t('Dùng làm minh chứng nộp Bài tập Thực hành Cuối khóa trên hệ thống Mầm Xanh LMS 2.0', 'Official evidence attachment for Capstone Practical Assignments on Mam Xanh LMS 2.0')); ?>
            </div>
        </div>
        <div class="p-2 px-3 rounded text-end" style="background:#EAF2EE;border:1px solid #234E3E;color:#234E3E;">
            <div class="small fw-bold"><?php echo s(local_mamxanhapp_t('Trạng thái Thẩm định', 'Verification Status')); ?></div>
            <div class="fw-bold"><?php echo s($selectedbatch ? $selectedbatch->vietgap_status : 'VietGAP Verified'); ?></div>
        </div>
    </div>

    <?php if ($selectedbatch): ?>
    <div class="p-3 rounded mb-3" style="background:#F9FAF9;border:1px solid #E6ECE9;">
        <div class="row small" style="line-height:1.8;">
            <div class="col-md-6"><strong><?php echo s(local_mamxanhapp_t('Học viên / Chủ nông hộ:', 'Learner / Farmer:')); ?></strong> <?php echo s(fullname($learner) . ' (' . $learner->username . ')'); ?></div>
            <div class="col-md-6"><strong><?php echo s(local_mamxanhapp_t('Mã lô truy xuất:', 'Batch Code:')); ?></strong> <code><?php echo s($selectedbatch->batch_code); ?></code></div>
            <div class="col-md-6"><strong><?php echo s(local_mamxanhapp_t('Sản phẩm & Giống:', 'Product & Variety:')); ?></strong> <?php echo s($selectedbatch->product_name . ' (' . $selectedbatch->crop_variety . ')'); ?></div>
            <div class="col-md-6"><strong><?php echo s(local_mamxanhapp_t('Vùng canh tác & Diện tích:', 'Region & Area:')); ?></strong> <?php echo s($selectedbatch->region . ' • ' . number_format((int)$selectedbatch->area_m2) . ' m²'); ?></div>
            <div class="col-md-12"><strong><?php echo s(local_mamxanhapp_t('Mã băm xác thực toàn vẹn dữ liệu (HMAC-SHA256):', 'Data Integrity HMAC-SHA256 Hash:')); ?></strong> <code><?php echo s($hmachash); ?></code></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="mx-vg-table">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:16%;"><?php echo s(local_mamxanhapp_t('Ngày giờ ghi', 'Timestamp')); ?></th>
                    <th style="width:20%;"><?php echo s(local_mamxanhapp_t('Hạng mục Tác nghiệp', 'Operation Type')); ?></th>
                    <th style="width:9%;text-align:center;">pH</th>
                    <th style="width:11%;text-align:center;">EC (mS/cm)</th>
                    <th style="width:11%;text-align:center;"><?php echo s(local_mamxanhapp_t('Cách ly PHI', 'PHI Days')); ?></th>
                    <th style="width:28%;"><?php echo s(local_mamxanhapp_t('Thông số Kỹ thuật & Ghi chú Hiện trường', 'Technical Parameters & Field Notes')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $idx = 0;
                foreach ($logs as $l):
                    $idx++;
                ?>
                <tr>
                    <td><?php echo $idx; ?></td>
                    <td><?php echo date('d/m/Y H:i', (int)$l->log_time); ?></td>
                    <td><strong style="color:#234E3E;"><?php echo s(local_agriops_translate_activity_type($l->activity_type)); ?></strong></td>
                    <td style="text-align:center;"><?php echo number_format((float)$l->ph_val, 1); ?></td>
                    <td style="text-align:center;"><?php echo number_format((float)$l->ec_val, 1); ?></td>
                    <td style="text-align:center;"><strong><?php echo (int)$l->phi_days; ?> <?php echo s(local_mamxanhapp_t('ngày', 'days')); ?></strong></td>
                    <td><?php echo s(local_agriops_translate_note($l->notes)); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
echo $OUTPUT->footer();
