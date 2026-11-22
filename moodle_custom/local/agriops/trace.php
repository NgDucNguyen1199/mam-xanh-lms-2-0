<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');

local_mamxanhapp_handle_lang_switch();

$code = optional_param('code', '', PARAM_ALPHANUMEXT);
$tamper = optional_param('tamper', 0, PARAM_INT);

// Smart Cross-Routing: If user entered a Certificate Code (MXCERT-...), redirect to verify.php
if ($code !== '' && stripos($code, 'MXCERT-') === 0) {
    redirect(new moodle_url('/local/mamxanhcert/verify.php', ['code' => $code]));
}

$syscontext = context_system::instance();
$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/agriops/trace.php', $code !== '' ? ['code' => $code] : []));
$PAGE->set_pagelayout('standard');

$title = local_mamxanhapp_t('Hộ chiếu Truy xuất Nguồn gốc Nông sản VietGAP', 'VietGAP Digital Agricultural Traceability Passport');
$PAGE->set_title($title);
$PAGE->set_heading('' . $title);

$batch = null;
$farmer = null;
$profile = null;
$logs = [];

$samplebatches = [];
if ($DB->get_manager()->table_exists('local_agriops_batch')) {
    $samplebatches = $DB->get_records('local_agriops_batch', null, 'id ASC', 'id,batch_code,product_name', 0, 4);
    if ($code !== '') {
        $batch = $DB->get_record('local_agriops_batch', ['batch_code' => $code]);
        if ($batch) {
            $farmer = $DB->get_record('user', ['id' => $batch->userid]);
            if ($DB->get_manager()->table_exists('local_farmerprofile')) {
                $profile = $DB->get_record('local_farmerprofile', ['userid' => $batch->userid]);
            }
            $logs = $DB->get_records('local_agriops_log', ['batchid' => $batch->id], 'log_time DESC');
        }
    }
}

echo $OUTPUT->header();
?>

<div class="mx-trace-container" style="max-width:860px;margin:0 auto;">
    <div style="background:#FFF;border:1px solid #E6ECE9;border-radius:16px;padding:24px;margin-bottom:20px;">
        <h3 style="color:#234E3E;font-weight:700;margin-bottom:12px;">
            <?php echo s(local_mamxanhapp_t('Tra cứu Mã Lô Nông sản VietGAP (TCVN 11892-1:2017)', 'Lookup VietGAP Produce Batch Code (TCVN 11892-1:2017)')); ?>
        </h3>
        <form method="get" action="<?php echo (new moodle_url('/local/agriops/trace.php'))->out(false); ?>" class="d-flex flex-wrap mb-3" style="gap:10px;">
            <input type="text" name="code" class="form-control" style="flex:1;min-width:220px;min-height:44px;border-radius:8px;font-weight:600;" placeholder="VD: VG-LD-202611-0001" value="<?php echo s($code); ?>">
            <button type="submit" class="btn btn-primary" style="min-height:44px;border-radius:8px;font-weight:600;padding:0 22px;background:#234E3E;border-color:#234E3E;">
                <?php echo s(local_mamxanhapp_t('Kiểm tra Nguồn gốc', 'Verify Origin')); ?>
            </button>
        </form>

        <?php if (!empty($samplebatches)): ?>
        <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
            <span class="small text-muted fw-bold"><?php echo s(local_mamxanhapp_t('Mã lô nông sản mẫu (Bấm xem ngay):', '1-Click Sample Produce Batches:')); ?></span>
            <?php foreach ($samplebatches as $sb): ?>
            <a href="<?php echo (new moodle_url('/local/agriops/trace.php', ['code' => $sb->batch_code]))->out(false); ?>" class="btn btn-sm btn-secondary" style="font-family:monospace;font-weight:700;">
                <?php echo s($sb->batch_code); ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($code !== '' && !$batch): ?>
    <div style="background:#FFF5F5;border:2px solid #C62828;border-radius:16px;padding:24px;text-align:center;">
        <h4 style="color:#B71C1C;font-weight:700;"><?php echo s(local_mamxanhapp_t('KHÔNG TÌM THẤY MÃ LÔ NÔNG SẢN', 'BATCH CODE NOT FOUND')); ?></h4>
        <p class="mb-0"><?php echo s(local_mamxanhapp_t('Mã lô "' . $code . '" chưa được đăng ký trên hệ thống truy xuất nguồn gốc VietGAP.', 'Batch code "' . $code . '" is not registered in the VietGAP traceability ledger.')); ?></p>
    </div>
    <?php elseif ($batch):
        $traceurl = (new moodle_url('/local/agriops/trace.php', ['code' => $batch->batch_code]))->out(false);
        $integrity = local_agriops_verify_batch_integrity($batch);
        $storedhmac = $integrity->hmac_signature;
        $verifybatch = clone $batch;
        if ($tamper === 1) {
            $verifybatch->area_m2 = (int)$batch->area_m2 + 5000;
        }
        $recomputedhmac = local_agriops_compute_batch_hmac($verifybatch);
        $hmacvalid = $integrity->valid && hash_equals($storedhmac, $recomputedhmac);
        $qrsvg = \local_mamxanhapp\qr_helper::render_svg($traceurl, 150, $hmacvalid ? '#234E3E' : '#C62828');

        // Evaluate active Pre-Harvest Interval (PHI) quarantine lock across batch logs
        $now = time();
        $activephilock = false;
        $maxsafedate = 0;
        $maxremainingdays = 0;
        $activephilogs = [];
        foreach ($logs as $lg) {
            $pdays = max(0, (int)$lg->phi_days);
            if ($pdays > 0) {
                $sdate = (int)$lg->log_time + ($pdays * 86400);
                if ($sdate > $now) {
                    $activephilock = true;
                    $rem = (int)ceil(($sdate - $now) / 86400);
                    $activephilogs[(int)$lg->id] = $rem;
                    if ($sdate > $maxsafedate) {
                        $maxsafedate = $sdate;
                        $maxremainingdays = $rem;
                    }
                }
            }
        }
    ?>
    <div style="background:#FFF;border:2px solid <?php echo (!$hmacvalid || $activephilock) ? '#C62828' : '#234E3E'; ?>;border-radius:16px;padding:26px;box-shadow:0 8px 24px rgba(15,23,42,0.06);">
        <?php if (!$hmacvalid): ?>
        <div style="background:#FEF2F2;border:2px solid #C62828;border-radius:12px;padding:14px 18px;margin-bottom:18px;color:#991B1B;font-weight:700;">
            <?php echo s(local_mamxanhapp_t(
                'CẢNH BÁO GIẢ MẠO DỮ LIỆU: Chữ ký số HMAC-SHA256 của lô nông sản không khớp với sổ cái gốc!',
                'DATA TAMPERING ALERT: Batch HMAC-SHA256 cryptographic signature does not match the original ledger!'
            )); ?>
        </div>
        <?php endif; ?>

        <?php if ($activephilock): ?>
        <div style="background:#FEF2F2;border:2px solid #C62828;border-left:6px solid #C62828;border-radius:12px;padding:16px 18px;margin-bottom:18px;color:#991B1B;">
            <div style="font-weight:800;font-size:1.0rem;margin-bottom:4px;">
                <?php echo s(local_mamxanhapp_t(
                    'CẢNH BÁO ĐANG TRONG THỜI GIAN CÁCH LY THU HOẠCH VIETGAP (PHI — CÒN ' . $maxremainingdays . ' NGÀY)',
                    'VIETGAP PRE-HARVEST INTERVAL QUARANTINE ACTIVE (' . $maxremainingdays . ' DAY(S) REMAINING)'
                )); ?>
            </div>
            <div style="font-size:0.9rem;color:#7F1D1D;">
                <?php echo s(local_mamxanhapp_t(
                    'Lô nông sản này vừa thực hiện can thiệp dinh dưỡng / BVTV có yêu cầu cách ly. Tuyệt đối KHÔNG thu hoạch hoặc xuất bán trước ngày an toàn: ' . userdate($maxsafedate, '%d/%m/%Y %H:%M') . '.',
                    'This batch is currently under active PHI quarantine. Do NOT harvest or distribute before safe harvest date: ' . userdate($maxsafedate, '%d/%m/%Y %H:%M') . '.'
                )); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:16px;">
            <div>
                <span style="display:inline-block;background:<?php echo (!$hmacvalid || $activephilock) ? '#FEF2F2' : '#EAF2EE'; ?>;color:<?php echo (!$hmacvalid || $activephilock) ? '#C62828' : '#234E3E'; ?>;padding:6px 14px;border-radius:999px;font-weight:700;font-size:0.84rem;margin-bottom:8px;">
                    <?php
                    if (!$hmacvalid) {
                        echo s(local_mamxanhapp_t('CHỮ KÝ SỐ HMAC-SHA256 KHÔNG HỢP LỆ', 'INVALID HMAC-SHA256 SIGNATURE'));
                    } else if ($activephilock) {
                        echo s(local_mamxanhapp_t('ĐANG CÁCH LY PHI — KHÓA THU HOẠCH (' . $maxremainingdays . ' NGÀY)', 'ACTIVE PHI QUARANTINE — HARVEST LOCKED'));
                    } else {
                        echo s(local_mamxanhapp_t('ĐÃ XÁC THỰC NGUỒN GỐC VIETGAP • AN TOÀN THU HOẠCH', 'VERIFIED VIETGAP PASSPORT • SAFE TO HARVEST'));
                    }
                    ?>
                </span>
                <h2 style="color:#234E3E;font-weight:700;margin-bottom:4px;"><?php echo s($batch->product_name); ?></h2>
                <div style="font-size:0.95rem;color:#52615B;">
                    <strong><?php echo s(local_mamxanhapp_t('Mã lô:', 'Batch Code:')); ?></strong> <code><?php echo s($batch->batch_code); ?></code> •
                    <strong><?php echo s(local_mamxanhapp_t('Giống:', 'Variety:')); ?></strong> <?php echo s($batch->crop_variety); ?>
                </div>
            </div>
            <div><?php echo $qrsvg; ?></div>
        </div>

        <div class="row mb-4" style="background:#F9FAF9;border:1px solid #E6ECE9;border-radius:12px;padding:16px;margin:0;">
            <div class="col-md-6 mb-2">
                <strong><?php echo s(local_mamxanhapp_t('Nông hộ sản xuất:', 'Producer Household:')); ?></strong><br>
                <?php echo s(($profile ? $profile->farm_name . ' — ' : '') . ($farmer ? fullname($farmer) : '')); ?>
            </div>
            <div class="col-md-6 mb-2">
                <strong><?php echo s(local_mamxanhapp_t('Vùng trồng & Tọa độ GPS:', 'Region & GPS Coordinates:')); ?></strong><br>
                <?php echo s($batch->region); ?> (<code><?php echo s($profile ? ($profile->gps_lat . ', ' . $profile->gps_lng) : '11.9404, 108.4583'); ?></code>)
            </div>
            <div class="col-md-6 mb-2">
                <strong><?php echo s(local_mamxanhapp_t('Quy mô & Sản lượng:', 'Scale & Expected Yield:')); ?></strong><br>
                <?php echo number_format((int)$verifybatch->area_m2); ?> m² • <?php echo number_format((int)$batch->expected_yield_kg); ?> kg
            </div>
            <div class="col-md-6 mb-2">
                <strong><?php echo s(local_mamxanhapp_t('Mã định danh QR Token:', 'QR Verification Token:')); ?></strong><br>
                <code><?php echo s($batch->qr_token); ?></code>
            </div>
            <div class="col-12 mt-1 pt-2" style="border-top:1px dashed #CBD5E1;font-size:0.86rem;">
                <strong><?php echo s(local_mamxanhapp_t('Chữ ký toàn vẹn HMAC-SHA256:', 'HMAC-SHA256 Integrity Signature:')); ?></strong>
                <code style="word-break:break-all;"><?php echo s($recomputedhmac); ?></code>
                <span class="badge ms-2" style="background:<?php echo $hmacvalid ? '#EAF2EE' : '#FEF2F2'; ?>;color:<?php echo $hmacvalid ? '#234E3E' : '#C62828'; ?>;border:1px solid <?php echo $hmacvalid ? '#234E3E' : '#C62828'; ?>;">
                    <?php echo s($hmacvalid ? local_mamxanhapp_t('Khớp chữ ký sổ cái 100%', 'Verified Ledger Match') : local_mamxanhapp_t('Sai lệch chữ ký HMAC', 'HMAC Mismatch')); ?>
                </span>
            </div>
        </div>

        <h4 style="color:#234E3E;font-weight:700;margin-bottom:14px;">
            <?php echo s(local_mamxanhapp_t('Nhật ký Canh tác Điện tử Minh bạch (' . count($logs) . ' bản ghi)', 'Transparent Cultivation Timeline (' . count($logs) . ' logs)')); ?>
        </h4>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead style="background:#EAF2EE;color:#234E3E;">
                    <tr>
                        <th><?php echo s(local_mamxanhapp_t('Thời gian', 'Timestamp')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('Nội dung canh tác', 'Cultivation Activity')); ?></th>
                        <th>pH / EC</th>
                        <th>PHI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $lg):
                        $islockedlog = isset($activephilogs[(int)$lg->id]);
                        $remdays = $activephilogs[(int)$lg->id] ?? 0;
                    ?>
                    <tr style="<?php echo $islockedlog ? 'background:#FEF2F2;' : ''; ?>">
                        <td><?php echo userdate($lg->log_time, '%d/%m/%Y %H:%M'); ?></td>
                        <td><?php echo s($lg->notes); ?></td>
                        <td><code>pH <?php echo s($lg->ph_val); ?> | EC <?php echo s($lg->ec_val); ?></code></td>
                        <td>
                            <?php if ($islockedlog): ?>
                            <span class="badge" style="background:#C62828;color:#FFFFFF;">
                                <?php echo (int)$lg->phi_days . ' ' . s(local_mamxanhapp_t('ngày (Còn ' . $remdays . ' ngày cách ly)', 'days (' . $remdays . 'd left)')); ?>
                            </span>
                            <?php else: ?>
                            <span class="badge bg-success"><?php echo (int)$lg->phi_days; ?> <?php echo s(local_mamxanhapp_t('ngày (An toàn)', 'days (Safe)')); ?></span>
                            <?php endif; ?>
                        </td>
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
