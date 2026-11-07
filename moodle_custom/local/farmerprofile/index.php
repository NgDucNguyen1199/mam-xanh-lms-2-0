<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');

local_mamxanhapp_handle_lang_switch();
require_login();

$syscontext = context_system::instance();
$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/farmerprofile/index.php'));
$PAGE->set_pagelayout('standard');

$title = local_mamxanhapp_t('Hồ sơ Nông hộ & Đồng bộ Cohort - Mầm Xanh LMS', 'Farmer Household Profile & Cohort Sync - Mam Xanh LMS');
$PAGE->set_title($title);
$PAGE->set_heading(local_mamxanhapp_t('🏡 Hồ sơ Nông hộ, Định vị GPS & Tổ hợp tác (Cohort)', '🏡 Farm Household Profile, GPS Coordinates & Regional Cohort'));

$userid = (int)$USER->id;
$savednotice = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && optional_param('action', '', PARAM_ALPHA) === 'saveprofile') {
    require_sesskey();
    local_farmerprofile_save_profile($userid, [
        'farm_name'     => required_param('farm_name', PARAM_TEXT),
        'area_m2'       => required_param('area_m2', PARAM_INT),
        'main_crop'     => required_param('main_crop', PARAM_TEXT),
        'region'        => required_param('region', PARAM_TEXT),
        'gps_lat'       => required_param('gps_lat', PARAM_TEXT),
        'gps_lng'       => required_param('gps_lng', PARAM_TEXT),
        'cert_standard' => required_param('cert_standard', PARAM_TEXT),
        'phone'         => optional_param('phone', '', PARAM_TEXT),
    ]);
    $savednotice = true;
}

$profile = $DB->get_record('local_farmerprofile', ['userid' => $userid]);
if (!$profile) {
    $profile = (object)[
        'farm_name'     => 'Nông hộ ' . fullname($USER),
        'area_m2'       => 2000,
        'main_crop'     => 'Rau ăn lá thủy canh NFT',
        'region'        => 'Lâm Đồng',
        'gps_lat'       => '11.940419',
        'gps_lng'       => '108.458313',
        'cert_standard' => 'VietGAP',
        'phone'         => '0912345678',
        'cohortid'      => 0,
    ];
}

$cohortname = '';
if (!empty($profile->cohortid)) {
    $cohort = $DB->get_record('cohort', ['id' => $profile->cohortid]);
    if ($cohort) {
        $cohortname = $cohort->name . ' (' . $cohort->idnumber . ')';
    }
}

$canviewall = is_siteadmin() || has_capability('local/farmerprofile:viewall', $syscontext);
$roletype = local_mamxanhapp_user_role_type();
$backurl = ($roletype === 'cbkt' || $roletype === 'admin')
    ? new moodle_url('/local/mamxanhapp/manage.php')
    : new moodle_url('/local/mamxanhapp/index.php');

echo $OUTPUT->header();
?>

<div class="mx-profile-wrap">
    <div class="mb-3">
        <a href="<?php echo $backurl->out(false); ?>" class="btn btn-outline-success" style="min-height:48px;display:inline-flex;align-items:center;border-radius:14px;font-weight:700;border-width:2px;">
            <?php echo s(local_mamxanhapp_t('← Quay lại Dashboard', '← Back to Dashboard')); ?>
        </a>
    </div>

    <?php if ($savednotice): ?>
    <div class="alert alert-success" style="border-radius:16px;font-weight:700;border:2px solid #2E7D32;">
        ✅ <?php echo s(local_mamxanhapp_t('Đã lưu Hồ sơ Nông hộ và tự động đồng bộ vào Tổ hợp tác (Cohort) vùng trồng thành công!', 'Farm household profile saved and automatically synced to your regional Cohort!')); ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div style="background:#FFF;border:2px solid #DCEEE0;border-radius:24px;padding:24px;">
                <h3 style="color:#1B4332;font-weight:800;font-size:1.3rem;margin-bottom:14px;">
                    📝 <?php echo s(local_mamxanhapp_t('Cập nhật Thông tin Nông hộ & Định vị Vườn', 'Update Farm Household & Garden Coordinates')); ?>
                </h3>

                <?php if ($cohortname !== ''): ?>
                <div style="background:#E8F5E9;color:#1B4332;padding:10px 14px;border-radius:14px;font-weight:700;margin-bottom:16px;font-size:0.92rem;">
                    🤝 <?php echo s(local_mamxanhapp_t('Cohort đã đồng bộ: ', 'Synced Cohort: ') . $cohortname); ?>
                </div>
                <?php endif; ?>

                <form method="post" action="<?php echo (new moodle_url('/local/farmerprofile/index.php'))->out(false); ?>">
                    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                    <input type="hidden" name="action" value="saveprofile">

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="id_farm_name"><?php echo s(local_mamxanhapp_t('Tên Nông hộ / Trang trại', 'Farm Household Name')); ?></label>
                        <input type="text" id="id_farm_name" name="farm_name" class="form-control" required value="<?php echo s($profile->farm_name); ?>" style="min-height:48px;border-radius:12px;">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" for="id_region"><?php echo s(local_mamxanhapp_t('Vùng trồng (Tự động đồng bộ Cohort)', 'Growing Region (Auto Cohort Sync)')); ?></label>
                            <select id="id_region" name="region" class="form-control" style="min-height:48px;border-radius:12px;font-weight:700;" onchange="mxUpdateRegionGps(this.value)">
                                <?php foreach (['Lâm Đồng', 'Đắk Lắk', 'Đồng Nai'] as $r): ?>
                                <option value="<?php echo s($r); ?>" <?php echo ($profile->region === $r) ? 'selected' : ''; ?>><?php echo s($r); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" for="id_area_m2"><?php echo s(local_mamxanhapp_t('Diện tích canh tác (m²)', 'Cultivation Area (m²)')); ?></label>
                            <input type="number" id="id_area_m2" name="area_m2" class="form-control" required min="50" max="1000000" value="<?php echo (int)$profile->area_m2; ?>" style="min-height:48px;border-radius:12px;font-weight:700;">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-bold" for="id_main_crop"><?php echo s(local_mamxanhapp_t('Loại cây trồng chủ lực', 'Primary Crop')); ?></label>
                            <input type="text" id="id_main_crop" name="main_crop" class="form-control" required value="<?php echo s($profile->main_crop); ?>" style="min-height:48px;border-radius:12px;">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-bold" for="id_cert_standard"><?php echo s(local_mamxanhapp_t('Tiêu chuẩn canh tác', 'Standard')); ?></label>
                            <select id="id_cert_standard" name="cert_standard" class="form-control" style="min-height:48px;border-radius:12px;font-weight:700;">
                                <?php foreach (['VietGAP', 'GlobalGAP', 'Organic Hữu cơ', 'Đang chuyển đổi VietGAP'] as $std): ?>
                                <option value="<?php echo s($std); ?>" <?php echo ($profile->cert_standard === $std) ? 'selected' : ''; ?>><?php echo s($std); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row align-items-end">
                        <div class="col-6 col-md-4 mb-3">
                            <label class="form-label fw-bold" for="id_gps_lat"><?php echo s(local_mamxanhapp_t('Vĩ độ GPS (Lat)', 'GPS Latitude')); ?></label>
                            <input type="text" id="id_gps_lat" name="gps_lat" class="form-control" required value="<?php echo s($profile->gps_lat); ?>" style="min-height:48px;border-radius:12px;">
                        </div>
                        <div class="col-6 col-md-4 mb-3">
                            <label class="form-label fw-bold" for="id_gps_lng"><?php echo s(local_mamxanhapp_t('Kinh độ GPS (Lng)', 'GPS Longitude')); ?></label>
                            <input type="text" id="id_gps_lng" name="gps_lng" class="form-control" required value="<?php echo s($profile->gps_lng); ?>" style="min-height:48px;border-radius:12px;">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <button type="button" class="btn btn-outline-success w-100" onclick="mxCaptureFarmGps()" style="min-height:48px;border-radius:12px;font-weight:700;border-width:2px;">
                                📍 <?php echo s(local_mamxanhapp_t('Lấy GPS hiện tại', 'Get Live GPS')); ?>
                            </button>
                        </div>
                    </div>
                    <div id="mx-gps-status" style="font-size:0.85rem;color:#2E7D32;font-weight:700;margin-bottom:12px;"></div>

                    <button type="submit" class="btn btn-success w-100" style="min-height:52px;border-radius:14px;font-weight:800;font-size:1.05rem;background:#1B4332;border-color:#1B4332;">
                        💾 <?php echo s(local_mamxanhapp_t('Lưu Hồ sơ & Đồng bộ Cohort Vùng trồng', 'Save Profile & Sync Regional Cohort')); ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div style="background:linear-gradient(145deg,#1B4332,#2E7D32);color:#FFF;border-radius:24px;padding:24px;height:100%;">
                <h4 style="color:#FFF;font-weight:800;margin-bottom:12px;">
                    🗺️ <?php echo s(local_mamxanhapp_t('Lợi ích Đồng bộ Hồ sơ Nông hộ', 'Benefits of Household Cohort Sync')); ?>
                </h4>
                <ul style="line-height:1.8;color:#E8F5E9;padding-left:20px;">
                    <li><?php echo s(local_mamxanhapp_t('Tự động xếp vào Tổ hợp tác (Cohort) Lâm Đồng, Đắk Lắk hoặc Đồng Nai.', 'Automatic assignment to Lam Dong, Dak Lak, or Dong Nai regional Cohorts.')); ?></li>
                    <li><?php echo s(local_mamxanhapp_t('Cập nhật bản tin cảnh báo thời tiết tiểu vùng chính xác cho vườn của bạn.', 'Personalizes micro-climate spraying & irrigation alerts on your dashboard.')); ?></li>
                    <li><?php echo s(local_mamxanhapp_t('Gắn tọa độ GPS xác thực lên tem QR truy xuất nguồn gốc nông sản VietGAP.', 'Embeds verified GPS coordinates onto your VietGAP QR traceability labels.')); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <?php if ($canviewall):
        $allprofiles = $DB->get_records_sql("
            SELECT fp.*, u.username, u.firstname, u.lastname, c.idnumber AS cohortcode
              FROM {local_farmerprofile} fp
              JOIN {user} u ON u.id = fp.userid
         LEFT JOIN {cohort} c ON c.id = fp.cohortid
          ORDER BY fp.region ASC, fp.area_m2 DESC
        ");
    ?>
    <section style="background:#FFF;border:2px solid #DCEEE0;border-radius:24px;padding:22px;margin-top:8px;">
        <h4 style="color:#1B4332;font-weight:800;margin-bottom:14px;">
            📋 <?php echo s(local_mamxanhapp_t('Danh bạ Nông hộ Đã Đồng bộ Cohort (' . count($allprofiles) . ' hộ)', 'Synced Farmer Household Directory (' . count($allprofiles) . ' households)')); ?>
        </h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#E8F5E9;color:#1B4332;">
                    <tr>
                        <th><?php echo s(local_mamxanhapp_t('Nông dân', 'Farmer')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('Tên Nông hộ', 'Household')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('Vùng & Cohort', 'Region & Cohort')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('Cây trồng', 'Main Crop')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('Diện tích', 'Area')); ?></th>
                        <th>GPS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allprofiles as $ap): ?>
                    <tr>
                        <td><strong><?php echo s($ap->firstname . ' ' . $ap->lastname); ?></strong><br><small><code><?php echo s($ap->username); ?></code></small></td>
                        <td><?php echo s($ap->farm_name); ?><br><small class="text-success fw-bold"><?php echo s($ap->cert_standard); ?></small></td>
                        <td><span class="badge bg-success"><?php echo s($ap->region); ?></span><br><small><code><?php echo s($ap->cohortcode ?? ''); ?></code></small></td>
                        <td><?php echo s($ap->main_crop); ?></td>
                        <td><strong><?php echo number_format((int)$ap->area_m2); ?> m²</strong></td>
                        <td><code><?php echo s($ap->gps_lat . ', ' . $ap->gps_lng); ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
function mxUpdateRegionGps(region) {
    var coords = {
        'Lâm Đồng': ['11.940419', '108.458313'],
        'Đắk Lắk': ['12.666667', '108.050000'],
        'Đồng Nai': ['10.933333', '107.233333']
    };
    if (coords[region]) {
        document.getElementById('id_gps_lat').value = coords[region][0];
        document.getElementById('id_gps_lng').value = coords[region][1];
    }
}
function mxCaptureFarmGps() {
    var status = document.getElementById('mx-gps-status');
    if (!navigator.geolocation) {
        status.textContent = '📍 Đã dùng tọa độ trung tâm tiểu vùng / Using regional GPS center.';
        return;
    }
    status.textContent = '⏳ Đang định vị vệ tinh GPS...';
    navigator.geolocation.getCurrentPosition(function(pos) {
        document.getElementById('id_gps_lat').value = pos.coords.latitude.toFixed(6);
        document.getElementById('id_gps_lng').value = pos.coords.longitude.toFixed(6);
        status.textContent = '✅ Đã lấy tọa độ GPS thực tế chính xác!';
    }, function() {
        mxUpdateRegionGps(document.getElementById('id_region').value);
        status.textContent = '📍 Đã gán tọa độ tiêu chuẩn của vùng trồng hiện tại.';
    }, { timeout: 5000 });
}
</script>

<?php
echo $OUTPUT->footer();
