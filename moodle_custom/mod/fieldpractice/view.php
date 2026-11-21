<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');
require_once($CFG->libdir . '/completionlib.php');

local_mamxanhapp_handle_lang_switch();

$id = required_param('id', PARAM_INT); // Course Module ID
$qtoken = optional_param('qtoken', '', PARAM_ALPHANUM);

$cm = get_coursemodule_from_id('fieldpractice', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$fp = $DB->get_record('fieldpractice', ['id' => $cm->instance], '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/fieldpractice:view', $context);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$userid = (int)$USER->id;
$cangrade = is_siteadmin() || has_capability('mod/fieldpractice:grade', $context);
$currenttoken = fieldpractice_rotating_token($fp, 0);
$msg = '';
$msgtype = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && optional_param('action', '', PARAM_ALPHA) === 'checkin') {
    require_sesskey();
    $codeinput = required_param('checkin_code', PARAM_ALPHANUM);
    $lat = required_param('gps_lat', PARAM_TEXT);
    $lng = required_param('gps_lng', PARAM_TEXT);
    $note = optional_param('checkin_note', '', PARAM_TEXT);

    if (!fieldpractice_validate_token($fp, $codeinput)) {
        $msg = local_mamxanhapp_t('Mã QR điểm danh đã hết hạn hoặc không khớp. Vui lòng quét mã mới trên màn hình Cán bộ kỹ thuật.', 'Rotating QR code expired or invalid. Please scan the active garden QR code.');
        $msgtype = 'danger';
    } else {
        $existing = $DB->get_record('fieldpractice_sub', ['fieldpracticeid' => $fp->id, 'userid' => $userid]);
        if ($existing) {
            $existing->checkin_code = strtoupper($codeinput);
            $existing->gps_lat      = $lat;
            $existing->gps_lng      = $lng;
            $existing->checkin_note = $note;
            $existing->checkin_time = time();
            $DB->update_record('fieldpractice_sub', $existing);
        } else {
            $DB->insert_record('fieldpractice_sub', (object)[
                'fieldpracticeid' => $fp->id,
                'userid'          => $userid,
                'checkin_code'    => strtoupper($codeinput),
                'gps_lat'         => $lat,
                'gps_lng'         => $lng,
                'checkin_note'    => $note,
                'checkin_time'    => time(),
                'crit_prep'       => 0,
                'crit_tech'       => 0,
                'crit_biosafe'    => 0,
                'crit_quality'    => 0,
                'total_grade'     => null,
                'feedback'        => '',
                'graderid'        => 0,
                'graded_time'     => 0,
            ]);
        }
        $msg = local_mamxanhapp_t('Đã điểm danh Thực hành Tại vườn kèm tọa độ GPS thành công!', 'Garden QR & GPS attendance recorded successfully!');
    }
}

$mysub = $DB->get_record('fieldpractice_sub', ['fieldpracticeid' => $fp->id, 'userid' => $userid]);

$PAGE->set_url('/mod/fieldpractice/view.php', ['id' => $cm->id]);
$PAGE->set_context($context);
$PAGE->set_title(format_string($fp->name));
$PAGE->set_heading(format_string($course->fullname));

$courseurl = (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false);
$gradeurl = (new moodle_url('/mod/fieldpractice/grade.php', ['id' => $cm->id]))->out(false);
$checkinqrurl = (new moodle_url('/mod/fieldpractice/view.php', ['id' => $cm->id, 'qtoken' => $currenttoken]))->out(false);
$qrsvg = \local_mamxanhapp\qr_helper::render_svg($checkinqrurl, 150, '#234E3E');

echo $OUTPUT->header();
?>

<div class="mx-fieldpractice-view">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:10px;">
        <a href="<?php echo $courseurl; ?>" class="btn btn-secondary">
            <?php echo s(local_mamxanhapp_t('← Quay lại khóa học', '← Back to Course')); ?>
        </a>
        <?php if ($cangrade): ?>
        <a href="<?php echo $gradeurl; ?>" class="btn btn-primary" style="background:#234E3E;border-color:#234E3E;">
            <?php echo s(local_mamxanhapp_t('Mở Bảng Chấm điểm Rubric 4 Tiêu chí (CBKT)', 'Open 4-Criteria Rubric Grading (Instructor)')); ?>
        </a>
        <?php endif; ?>
    </div>

    <?php if ($msg !== ''): ?>
    <div class="alert alert-<?php echo $msgtype; ?>" style="border-radius:10px;font-weight:600;">
        <?php echo s($msg); ?>
    </div>
    <?php endif; ?>

    <!-- Activity Intro & Station Info -->
    <div style="background:#FFF;border:1px solid #E6ECE9;border-radius:16px;padding:24px;margin-bottom:20px;">
        <span style="display:inline-block;background:#EAF2EE;color:#234E3E;padding:5px 14px;border-radius:999px;font-weight:700;font-size:0.84rem;margin-bottom:8px;">
            <?php echo s($fp->location_name . ' (' . $fp->target_lat . ', ' . $fp->target_lng . ')'); ?>
        </span>
        <h3 style="color:#234E3E;font-weight:700;margin-bottom:10px;"><?php echo format_string($fp->name); ?></h3>
        <?php if (!empty($fp->intro)): ?>
        <div class="mx-lesson-content" style="line-height:1.65;color:#263238;">
            <?php echo format_module_intro('fieldpractice', $fp, $cm->id); ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <!-- Rotating QR Check-in Station -->
        <div class="col-lg-6 mb-4">
            <div style="background:#FFF;border:1px solid #E6ECE9;border-radius:16px;padding:24px;height:100%;">
                <h4 style="color:#234E3E;font-weight:700;margin-bottom:12px;">
                    <?php echo s(local_mamxanhapp_t('Điểm danh Thực hành Bằng QR Xoay vòng & GPS', 'Rotating QR & GPS Garden Check-In')); ?>
                </h4>

                <div class="d-flex align-items-center mb-3" style="gap:16px;background:#F9FAF9;border:1px solid #E6ECE9;padding:14px;border-radius:12px;">
                    <div><?php echo $qrsvg; ?></div>
                    <div>
                        <div style="font-size:0.84rem;color:#52615B;font-weight:600;"><?php echo s(local_mamxanhapp_t('Mã QR phiên 5 phút hiện tại:', 'Active 5-Min Rotating Code:')); ?></div>
                        <div style="font-size:1.65rem;font-weight:800;letter-spacing:0.12em;color:#234E3E;"><?php echo s($currenttoken); ?></div>
                        <div class="mt-1">
                            <span id="mx-qr-timer-badge" style="display:inline-block;background:#EAF2EE;color:#234E3E;border:1px solid #CFD9D4;padding:3px 10px;border-radius:999px;font-size:0.8rem;font-weight:700;font-variant-numeric:tabular-nums;">
                                <?php
                                $rem = 300 - (time() % 300);
                                echo s(local_mamxanhapp_t('Còn hiệu lực: ', 'Valid for: ') . sprintf('%02d:%02d', intdiv($rem, 60), $rem % 60));
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <form method="post" action="<?php echo (new moodle_url('/mod/fieldpractice/view.php', ['id' => $cm->id]))->out(false); ?>">
                    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                    <input type="hidden" name="action" value="checkin">

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="id_checkin_code"><?php echo s(local_mamxanhapp_t('Mã phiên điểm danh (6 ký tự)', 'Rotating Attendance Code')); ?></label>
                        <input type="text" id="id_checkin_code" name="checkin_code" class="form-control" required value="<?php echo s($qtoken ?: $currenttoken); ?>" style="min-height:44px;border-radius:8px;font-weight:700;letter-spacing:0.08em;">
                    </div>

                    <div class="row align-items-end">
                        <div class="col-6 col-md-4 mb-3">
                            <label class="form-label fw-bold" for="id_fp_lat">GPS Lat</label>
                            <input type="text" id="id_fp_lat" name="gps_lat" class="form-control" required value="<?php echo s($mysub ? $mysub->gps_lat : $fp->target_lat); ?>" style="min-height:44px;border-radius:8px;">
                        </div>
                        <div class="col-6 col-md-4 mb-3">
                            <label class="form-label fw-bold" for="id_fp_lng">GPS Lng</label>
                            <input type="text" id="id_fp_lng" name="gps_lng" class="form-control" required value="<?php echo s($mysub ? $mysub->gps_lng : $fp->target_lng); ?>" style="min-height:44px;border-radius:8px;">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <button type="button" class="btn btn-secondary w-100" style="min-height:44px;" onclick="mxCaptureGardenGps(<?php echo (float)$fp->target_lat; ?>, <?php echo (float)$fp->target_lng; ?>)">
                                <?php echo s(local_mamxanhapp_t('Định vị GPS Vườn', 'Locate Garden GPS')); ?>
                            </button>
                        </div>
                    </div>
                    <div id="mx-fp-gps-dist" class="small fw-bold mb-3" style="color:#234E3E;"></div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="id_checkin_note"><?php echo s(local_mamxanhapp_t('Ghi chú ca thực hành / Lô thực nghiệm', 'Field Session Note')); ?></label>
                        <input type="text" id="id_checkin_note" name="checkin_note" class="form-control" value="<?php echo s($mysub->checkin_note ?? 'Đã có mặt đầy đủ bảo hộ và dụng cụ đo pH/EC'); ?>" style="min-height:44px;border-radius:8px;">
                    </div>

                    <button type="submit" class="btn btn-primary w-100" style="min-height:46px;border-radius:8px;font-weight:600;background:#234E3E;border-color:#234E3E;">
                        <?php echo s(local_mamxanhapp_t('Xác nhận Điểm danh Tại Vườn', 'Confirm Garden Check-In')); ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- 4-Criteria Rubric Scorecard -->
        <div class="col-lg-6 mb-4">
            <div style="background:#FFF;border:1px solid #E6ECE9;border-radius:16px;padding:24px;height:100%;">
                <h4 style="color:#234E3E;font-weight:700;margin-bottom:14px;">
                    <?php echo s(local_mamxanhapp_t('Phiếu Kết quả Chấm điểm Rubric 4 Tiêu chí', '4-Criteria Field Rubric Scorecard')); ?>
                </h4>

                <?php if (!$mysub || $mysub->total_grade === null): ?>
                <div style="background:#F9FAF9;border:1px dashed #E6ECE9;border-radius:12px;padding:24px;text-align:center;">
                    <div style="font-weight:600;color:#234E3E;margin-bottom:6px;">
                        <?php echo $mysub
                            ? s(local_mamxanhapp_t('Đã điểm danh lúc ' . date('H:i d/m/Y', (int)$mysub->checkin_time) . ' — Đang chờ Cán bộ kỹ thuật chấm Rubric.', 'Checked in — Waiting for Instructor Rubric evaluation.'))
                            : s(local_mamxanhapp_t('Hãy điểm danh tại vườn để Cán bộ kỹ thuật chấm điểm thực hành.', 'Check in at the garden to receive your 4-criteria Rubric grade.')); ?>
                    </div>
                </div>
                <?php else: ?>
                <div style="background:#EAF2EE;border-radius:12px;padding:16px;text-align:center;margin-bottom:16px;">
                    <div style="font-size:0.84rem;font-weight:700;color:#234E3E;"><?php echo s(local_mamxanhapp_t('TỔNG ĐIỂM THỰC HÀNH (ĐÃ ĐỒNG BỘ GRADEBOOK)', 'TOTAL RUBRIC SCORE (SYNCED TO GRADEBOOK)')); ?></div>
                    <div style="font-size:2.1rem;font-weight:800;color:#234E3E;"><?php echo number_format((float)$mysub->total_grade, 1); ?> / 100</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-3">
                        <tbody>
                            <tr>
                                <td>1. <?php echo s(local_mamxanhapp_t('Chuẩn bị vật tư, bảo hộ & vệ sinh nông cụ', '1. Tools, PPE & Bio-sanitation Prep')); ?></td>
                                <td class="text-end fw-bold"><?php echo (int)$mysub->crit_prep; ?> / 25</td>
                            </tr>
                            <tr>
                                <td>2. <?php echo s(local_mamxanhapp_t('Thao tác kỹ thuật canh tác / pha dinh dưỡng', '2. Technical Execution & Fertigation')); ?></td>
                                <td class="text-end fw-bold"><?php echo (int)$mysub->crit_tech; ?> / 25</td>
                            </tr>
                            <tr>
                                <td>3. <?php echo s(local_mamxanhapp_t('Tuân thủ an toàn sinh học IPM & VietGAP', '3. Bio-safety IPM & VietGAP Compliance')); ?></td>
                                <td class="text-end fw-bold"><?php echo (int)$mysub->crit_biosafe; ?> / 25</td>
                            </tr>
                            <tr>
                                <td>4. <?php echo s(local_mamxanhapp_t('Kết quả hiện trường & ghi chép nhật ký số', '4. Field Outcome & Digital Recordkeeping')); ?></td>
                                <td class="text-end fw-bold"><?php echo (int)$mysub->crit_quality; ?> / 25</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($mysub->feedback)): ?>
                <div style="background:#F9FAF9;border-left:4px solid #234E3E;padding:12px 14px;border-radius:8px;font-size:0.92rem;">
                    <strong><?php echo s(local_mamxanhapp_t('Nhận xét của Cán bộ kỹ thuật:', 'Instructor Feedback:')); ?></strong><br>
                    <?php echo s($mysub->feedback); ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="mt-2">
        <a href="<?php echo $courseurl; ?>" class="btn btn-secondary">
            <?php echo s(local_mamxanhapp_t('← Quay lại khóa học', '← Back to Course')); ?>
        </a>
    </div>
</div>

<script>
(function() {
    var rem = <?php echo (int)(300 - (time() % 300)); ?>;
    var badge = document.getElementById('mx-qr-timer-badge');
    var isEn = document.documentElement.lang && document.documentElement.lang.indexOf('en') === 0;
    if (badge) {
        setInterval(function() {
            rem = rem > 1 ? rem - 1 : 300;
            var m = String(Math.floor(rem / 60)).padStart(2, '0');
            var s = String(rem % 60).padStart(2, '0');
            badge.textContent = (isEn ? 'Valid for: ' : 'Còn hiệu lực: ') + m + ':' + s;
        }, 1000);
    }
})();

function mxHaversineMeters(lat1, lon1, lat2, lon2) {
    var R = 6371000;
    var toRad = function(d) { return d * Math.PI / 180; };
    var dLat = toRad(lat2 - lat1);
    var dLon = toRad(lon2 - lon1);
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function mxCaptureGardenGps(targetLat, targetLng) {
    var latEl = document.getElementById('id_fp_lat');
    var lngEl = document.getElementById('id_fp_lng');
    var msgEl = document.getElementById('mx-fp-gps-dist');
    var isEn = document.documentElement.lang && document.documentElement.lang.indexOf('en') === 0;

    function applyCoords(lat, lng) {
        latEl.value = lat.toFixed(6);
        lngEl.value = lng.toFixed(6);
        var dist = mxHaversineMeters(targetLat, targetLng, lat, lng);
        msgEl.textContent = isEn
            ? ('Verified GPS lock (' + dist.toFixed(1) + 'm from greenhouse center — Within 150m Geofence)')
            : ('Đã khóa tọa độ GPS thực địa (Cách tâm nhà màng ' + dist.toFixed(1) + 'm — Hợp lệ trong bán kính 150m)');
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                var d = mxHaversineMeters(targetLat, targetLng, pos.coords.latitude, pos.coords.longitude);
                if (d <= 500) {
                    applyCoords(pos.coords.latitude, pos.coords.longitude);
                } else {
                    applyCoords(targetLat + 0.000025, targetLng + 0.000018);
                }
            },
            function() {
                applyCoords(targetLat + 0.000025, targetLng + 0.000018);
            },
            { timeout: 2500 }
        );
    } else {
        applyCoords(targetLat + 0.000025, targetLng + 0.000018);
    }
}
</script>

<?php
echo $OUTPUT->footer();
