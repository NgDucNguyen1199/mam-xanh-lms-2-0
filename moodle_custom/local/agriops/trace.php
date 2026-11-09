<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');

local_mamxanhapp_handle_lang_switch();

$code = optional_param('code', '', PARAM_ALPHANUMEXT);
$syscontext = context_system::instance();
$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/agriops/trace.php', ['code' => $code]));
$PAGE->set_pagelayout('standard');

$title = local_mamxanhapp_t('Hộ chiếu Truy xuất Nguồn gốc Nông sản VietGAP', 'VietGAP Digital Agricultural Traceability Passport');
$PAGE->set_title($title);
$PAGE->set_heading('' . $title);

$batch = null;
$farmer = null;
$profile = null;
$logs = [];

if ($code !== '' && $DB->get_manager()->table_exists('local_agriops_batch')) {
    $batch = $DB->get_record('local_agriops_batch', ['batch_code' => $code]);
    if ($batch) {
        $farmer = $DB->get_record('user', ['id' => $batch->userid]);
        if ($DB->get_manager()->table_exists('local_farmerprofile')) {
            $profile = $DB->get_record('local_farmerprofile', ['userid' => $batch->userid]);
        }
        $logs = $DB->get_records('local_agriops_log', ['batchid' => $batch->id], 'log_time DESC');
    }
}

echo $OUTPUT->header();
?>

<div class="mx-trace-container" style="max-width:860px;margin:0 auto;">
    <?php if (!$batch): ?>
    <div style="background:#FFF;border:2px solid #DCEEE0;border-radius:24px;padding:28px;text-align:center;">
        <h3 style="color:#1B4332;font-weight:800;margin-bottom:12px;">
            <?php echo s(local_mamxanhapp_t('Tra cứu Mã Lô Nông sản VietGAP', 'Lookup VietGAP Batch Code')); ?>
        </h3>
        <form method="get" action="<?php echo (new moodle_url('/local/agriops/trace.php'))->out(false); ?>" class="d-flex flex-wrap justify-content-center" style="gap:10px;">
            <input type="text" name="code" class="form-control" style="max-width:340px;min-height:48px;border-radius:12px;" placeholder="VD: VG-LD-202611-0001" value="<?php echo s($code); ?>">
            <button type="submit" class="btn btn-success" style="min-height:48px;border-radius:12px;font-weight:800;padding:0 22px;background:#1B4332;">
                <?php echo s(local_mamxanhapp_t('Kiểm tra Nguồn gốc', 'Verify Origin')); ?>
            </button>
        </form>
    </div>
    <?php else:
        $traceurl = (new moodle_url('/local/agriops/trace.php', ['code' => $batch->batch_code]))->out(false);
        $qrsvg = \local_mamxanhapp\qr_helper::render_svg($traceurl, 150, '#1B4332');
    ?>
    <div style="background:#FFF;border:3px solid #2E7D32;border-radius:24px;padding:26px;box-shadow:0 12px 32px rgba(27,67,50,0.12);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:16px;">
            <div>
                <span style="display:inline-block;background:#D8F3DC;color:#1B4332;padding:6px 14px;border-radius:999px;font-weight:800;font-size:0.88rem;margin-bottom:8px;">
                    <?php echo s(local_mamxanhapp_t('ĐÃ XÁC THỰC NGUỒN GỐC VIETGAP', 'VERIFIED VIETGAP TRACEABILITY PASSPORT')); ?>
                </span>
                <h2 style="color:#1B4332;font-weight:800;margin-bottom:4px;"><?php echo s($batch->product_name); ?></h2>
                <div style="font-size:0.95rem;color:#4E6358;">
                    <strong><?php echo s(local_mamxanhapp_t('Mã lô:', 'Batch Code:')); ?></strong> <code><?php echo s($batch->batch_code); ?></code> •
                    <strong><?php echo s(local_mamxanhapp_t('Giống:', 'Variety:')); ?></strong> <?php echo s($batch->crop_variety); ?>
                </div>
            </div>
            <div><?php echo $qrsvg; ?></div>
        </div>

        <div class="row mb-4" style="background:#F4FBF6;border-radius:18px;padding:16px;margin:0;">
            <div class="col-md-6 mb-2">
                <strong><?php echo s(local_mamxanhapp_t('Nông hộ sản xuất:', 'Producer Household:')); ?></strong><br>
                <?php echo s(($profile ? $profile->farm_name . ' — ' : '') . ($farmer ? fullname($farmer) : '')); ?>
            </div>
            <div class="col-md-6 mb-2">
                <strong><?php echo s(local_mamxanhapp_t('Vùng trồng & Tọa độ GPS:', 'Region & GPS Coordinates:')); ?></strong><br>
                <?php echo s($batch->region); ?> (<code><?php echo s($profile ? ($profile->gps_lat . ', ' . $profile->gps_lng) : '11.9404, 108.4583'); ?></code>)
            </div>
            <div class="col-md-6">
                <strong><?php echo s(local_mamxanhapp_t('Quy mô & Sản lượng:', 'Scale & Expected Yield:')); ?></strong><br>
                <?php echo number_format((int)$batch->area_m2); ?> m² • <?php echo number_format((int)$batch->expected_yield_kg); ?> kg
            </div>
            <div class="col-md-6">
                <strong><?php echo s(local_mamxanhapp_t('Mã băm bảo mật:', 'Verification Token:')); ?></strong><br>
                <code><?php echo s($batch->qr_token); ?></code>
            </div>
        </div>

        <h4 style="color:#1B4332;font-weight:800;margin-bottom:14px;">
            <?php echo s(local_mamxanhapp_t('Nhật ký Canh tác Điện tử Minh bạch (' . count($logs) . ' bản ghi)', 'Transparent Cultivation Timeline (' . count($logs) . ' logs)')); ?>
        </h4>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead style="background:#E8F5E9;color:#1B4332;">
                    <tr>
                        <th><?php echo s(local_mamxanhapp_t('Thời gian', 'Timestamp')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('Nội dung canh tác', 'Cultivation Activity')); ?></th>
                        <th>pH / EC</th>
                        <th>PHI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $lg): ?>
                    <tr>
                        <td><?php echo userdate($lg->log_time, '%d/%m/%Y %H:%M'); ?></td>
                        <td><?php echo s($lg->notes); ?> <?php echo !empty($lg->voice_input) ? '' : ''; ?></td>
                        <td><code>pH <?php echo s($lg->ph_val); ?> | EC <?php echo s($lg->ec_val); ?></code></td>
                        <td><span class="badge bg-success"><?php echo (int)$lg->phi_days; ?> <?php echo s(local_mamxanhapp_t('ngày', 'days')); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
echo $OUTPUT->footer();
