<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');

local_mamxanhapp_handle_lang_switch();
require_login();

$syscontext = context_system::instance();
$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/agriops/index.php'));
$PAGE->set_pagelayout('standard');

$title = local_mamxanhapp_t('Nhật ký Canh tác VietGAP & Truy xuất QR - Mầm Xanh LMS', 'VietGAP Voice Diary & QR Traceability - Mam Xanh LMS');
$PAGE->set_title($title);
$PAGE->set_heading(local_mamxanhapp_t('Quản lý Lô Nông sản VietGAP & Nhật ký Giọng nói', 'VietGAP Production Lots, Voice Diary & QR Traceability'));

$userid = (int)$USER->id;
$canaudit = is_siteadmin() || has_capability('local/agriops:audit', $syscontext);
$flashmsg = '';
$prefillph = optional_param('prefill_ph', '6.0', PARAM_TEXT);
$prefillec = optional_param('prefill_ec', '1.6', PARAM_TEXT);
$prefillnotes = optional_param('prefill_notes', '', PARAM_TEXT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_sesskey();
    $action = required_param('action', PARAM_ALPHA);

    if ($action === 'createbatch') {
        $batch = local_agriops_create_batch($userid, [
            'product_name'      => required_param('product_name', PARAM_TEXT),
            'crop_variety'      => required_param('crop_variety', PARAM_TEXT),
            'region'            => required_param('region', PARAM_TEXT),
            'area_m2'           => required_param('area_m2', PARAM_INT),
            'expected_yield_kg' => required_param('expected_yield_kg', PARAM_INT),
        ]);
        // Seed initial germination/batch registration log automatically
        local_agriops_add_log($userid, (int)$batch->id, [
            'activity_type' => 'env_check',
            'notes'         => 'Khởi tạo lô canh tác chuẩn VietGAP, kiểm tra nguồn nước và giá thể đạt chuẩn.',
            'ph_val'        => '6.0',
            'ec_val'        => '1.5',
            'phi_days'      => 0,
            'voice_input'   => 0,
        ]);
        $flashmsg = local_mamxanhapp_t(
            'Đã tạo lô nông sản VietGAP mới thành công! Mã truy xuất QR: ' . $batch->batch_code,
            'Created new VietGAP batch successfully! QR Trace Code: ' . $batch->batch_code
        );
    } else if ($action === 'addlog') {
        $batchid = required_param('batchid', PARAM_INT);
        $batch = $DB->get_record('local_agriops_batch', ['id' => $batchid], '*', MUST_EXIST);
        if ((int)$batch->userid !== $userid && !$canaudit) {
            local_mamxanhapp_send_403_and_exit();
        }
        local_agriops_add_log($userid, $batchid, [
            'activity_type' => required_param('activity_type', PARAM_ALPHANUMEXT),
            'notes'         => required_param('notes', PARAM_TEXT),
            'ph_val'        => required_param('ph_val', PARAM_TEXT),
            'ec_val'        => required_param('ec_val', PARAM_TEXT),
            'phi_days'      => required_param('phi_days', PARAM_INT),
            'voice_input'   => optional_param('voice_input', 0, PARAM_INT),
        ]);
        $flashmsg = local_mamxanhapp_t(
            'Đã ghi Nhật ký canh tác vào Lô ' . $batch->batch_code . ' và cập nhật hộ chiếu QR!',
            'Recorded cultivation log onto Batch ' . $batch->batch_code . ' and updated QR passport!'
        );
    }
}

$batches = $canaudit
    ? $DB->get_records('local_agriops_batch', null, 'id DESC')
    : $DB->get_records('local_agriops_batch', ['userid' => $userid], 'id DESC');

$roletype = local_mamxanhapp_user_role_type();
$backurl = ($roletype === 'cbkt' || $roletype === 'admin')
    ? new moodle_url('/local/mamxanhapp/manage.php')
    : new moodle_url('/local/mamxanhapp/index.php');

echo $OUTPUT->header();
?>

<div class="mx-agriops-wrap mx-ops-wrapper">
    <div class="mb-3">
        <a href="<?php echo $backurl->out(false); ?>" class="btn btn-secondary">
            <?php echo s(local_mamxanhapp_t('← Quay lại Dashboard', '← Back to Dashboard')); ?>
        </a>
    </div>

    <?php if ($flashmsg !== ''): ?>
    <div class="alert alert-success mb-4" id="mx-agriops-alert" style="border-radius:8px;">
        <?php echo s($flashmsg); ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Create VietGAP Batch Form -->
        <div class="col-lg-5 mb-4">
            <div class="mx-card p-4 h-100">
                <h3 class="h3 mb-3">
                    <?php echo s(local_mamxanhapp_t('Tạo Lô Nông sản VietGAP Mới', 'Register New VietGAP Crop Batch')); ?>
                </h3>
                <form method="post" action="<?php echo (new moodle_url('/local/agriops/index.php'))->out(false); ?>" id="mx-create-batch-form">
                    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                    <input type="hidden" name="action" value="createbatch">

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="id_product_name"><?php echo s(local_mamxanhapp_t('Tên nông sản thương phẩm', 'Product Name')); ?></label>
                        <input type="text" id="id_product_name" name="product_name" class="form-control" required placeholder="VD: Xà lách Thủy canh Đà Lạt / Sầu riêng Ri6">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="id_crop_variety"><?php echo s(local_mamxanhapp_t('Giống cây trồng / Mã lô giống', 'Seed / Cultivar Variety')); ?></label>
                        <input type="text" id="id_crop_variety" name="crop_variety" class="form-control" required value="Giống F1 Chuẩn Sạch Bệnh">
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold" for="id_batch_region"><?php echo s(local_mamxanhapp_t('Vùng trồng', 'Region')); ?></label>
                            <select id="id_batch_region" name="region" class="form-control">
                                <option value="Lâm Đồng">Lâm Đồng</option>
                                <option value="Đắk Lắk">Đắk Lắk</option>
                                <option value="Đồng Nai">Đồng Nai</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold" for="id_batch_area"><?php echo s(local_mamxanhapp_t('Diện tích lô (m²)', 'Batch Area (m²)')); ?></label>
                            <input type="number" id="id_batch_area" name="area_m2" class="form-control fw-bold" required value="1200" min="50">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="id_expected_yield"><?php echo s(local_mamxanhapp_t('Sản lượng dự kiến (kg)', 'Expected Yield (kg)')); ?></label>
                        <input type="number" id="id_expected_yield" name="expected_yield_kg" class="form-control fw-bold" required value="2500" min="10">
                    </div>

                    <button type="submit" id="mx-submit-batch-btn" class="btn btn-primary w-100">
                        <?php echo s(local_mamxanhapp_t('Khởi tạo Lô & Sinh Mã QR Truy xuất', 'Create Batch & Generate Traceability QR')); ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Voice-Enabled Field Cultivation Log Form -->
        <div class="col-lg-7 mb-4">
            <div class="mx-card p-4 h-100">
                <h3 class="h3 mb-3">
                    <?php echo s(local_mamxanhapp_t('Ghi Nhật ký Canh tác Rảnh tay (Web Speech API)', 'Hands-Free Voice Field Diary (Web Speech API)')); ?>
                </h3>

                <?php if (empty($batches)): ?>
                <div class="alert alert-info" style="border-radius:8px;">
                    <?php echo s(local_mamxanhapp_t('Hãy khởi tạo ít nhất 1 Lô nông sản ở khung bên trái trước khi ghi nhật ký canh tác.', 'Please create a VietGAP batch on the left first to start logging diary entries.')); ?>
                </div>
                <?php else: ?>
                <form method="post" action="<?php echo (new moodle_url('/local/agriops/index.php'))->out(false); ?>" id="mx-add-log-form">
                    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                    <input type="hidden" name="action" value="addlog">
                    <input type="hidden" name="voice_input" id="id_voice_input" value="0">

                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-bold" for="id_batchid"><?php echo s(local_mamxanhapp_t('Chọn Lô Nông sản VietGAP', 'Select VietGAP Batch')); ?></label>
                            <select id="id_batchid" name="batchid" class="form-control">
                                <?php foreach ($batches as $b): ?>
                                <option value="<?php echo (int)$b->id; ?>"><?php echo s($b->batch_code . ' — ' . $b->product_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-bold" for="id_activity_type"><?php echo s(local_mamxanhapp_t('Hạng mục công việc', 'Activity Type')); ?></label>
                            <select id="id_activity_type" name="activity_type" class="form-control">
                                <option value="watering_fert"><?php echo s(local_mamxanhapp_t('Tưới nhỏ giọt & Bón phân A-B', 'Drip Irrigation & Fertigation')); ?></option>
                                <option value="ipm_bio"><?php echo s(local_mamxanhapp_t('Phun sinh học IPM / Trichoderma', 'Biological IPM Spray')); ?></option>
                                <option value="env_check"><?php echo s(local_mamxanhapp_t('Đo pH / EC & Kiểm tra nhà màng', 'pH / EC Sensor Check')); ?></option>
                                <option value="pruning"><?php echo s(local_mamxanhapp_t('Tỉa cành, treo dây, bao trái', 'Pruning & Fruit Bagging')); ?></option>
                                <option value="harvest"><?php echo s(local_mamxanhapp_t('Thu hoạch & Làm mát Pre-cooling', 'Harvest & Pre-cooling')); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-4 mb-3">
                            <label class="form-label fw-bold" for="id_ph_val">pH</label>
                            <input type="text" id="id_ph_val" name="ph_val" class="form-control" value="<?php echo s($prefillph); ?>" style="min-height:48px;border-radius:12px;">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label fw-bold" for="id_ec_val">EC (mS/cm)</label>
                            <input type="text" id="id_ec_val" name="ec_val" class="form-control" value="<?php echo s($prefillec); ?>" style="min-height:48px;border-radius:12px;">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label fw-bold" for="id_phi_days"><?php echo s(local_mamxanhapp_t('Cách ly PHI (ngày)', 'PHI (days)')); ?></label>
                            <input type="number" id="id_phi_days" name="phi_days" class="form-control" value="0" min="0" style="min-height:48px;border-radius:12px;">
                        </div>
                    </div>

                    <div class="mb-2 d-flex flex-wrap justify-content-between align-items-center" style="gap:8px;">
                        <label class="form-label fw-bold mb-0" for="id_log_notes"><?php echo s(local_mamxanhapp_t('Nội dung nhật ký thực địa', 'Field Diary Notes')); ?></label>
                        <button type="button" id="mx-voice-btn" onclick="mxStartVoiceDiary()" class="btn btn-outline-danger btn-sm" style="min-height:44px;border-radius:999px;font-weight:800;padding:0 16px;border-width:2px;">
                            <?php echo s(local_mamxanhapp_t('Bấm để Đọc bằng Giọng nói', 'Tap to Dictate by Voice')); ?>
                        </button>
                    </div>

                    <textarea id="id_log_notes" name="notes" rows="3" class="form-control mb-2" required placeholder="<?php echo s(local_mamxanhapp_t('Bấm nút Micro để nói hoặc chạm mẫu câu nhanh bên dưới...', 'Tap Mic to speak or choose a quick field template below...')); ?>" style="border-radius:14px;"><?php echo s($prefillnotes); ?></textarea>
                    <div id="mx-voice-status" style="font-size:0.84rem;color:#C62828;font-weight:700;margin-bottom:8px;"></div>

                    <!-- 1-Tap Quick Field Diary Chips -->
                    <div class="d-flex flex-wrap mb-3" style="gap:6px;">
                        <button type="button" class="btn btn-sm btn-light" style="border:1px solid #E6ECE9;border-radius:999px;font-weight:600;" onclick="document.getElementById('id_log_notes').value='Đã tưới nhỏ giọt dinh dưỡng A-B sáng sớm, pH 6.0, EC 1.6 mS/cm, cây phát triển khỏe.'">+ <?php echo s(local_mamxanhapp_t('Mẫu Tưới A-B', 'Fertigation Template')); ?></button>
                        <button type="button" class="btn btn-sm btn-light" style="border:1px solid #E6ECE9;border-radius:999px;font-weight:600;" onclick="document.getElementById('id_log_notes').value='Phun chế phẩm nấm đối kháng Trichoderma + Bacillus subtilis phòng nấm rễ, cách ly 0 ngày.'">+ <?php echo s(local_mamxanhapp_t('Mẫu Phun Sinh học', 'Bio-IPM Template')); ?></button>
                        <button type="button" class="btn btn-sm btn-light" style="border:1px solid #E6ECE9;border-radius:999px;font-weight:600;" onclick="document.getElementById('id_log_notes').value='Thu hoạch sáng mát 06:30, đưa vào phòng làm mát sơ bộ Pre-cooling 4°C đạt chuẩn VietGAP.'">+ <?php echo s(local_mamxanhapp_t('Mẫu Thu hoạch', 'Harvest Template')); ?></button>
                    </div>

                    <button type="submit" id="mx-submit-log-btn" class="btn btn-primary w-100">
                        <?php echo s(local_mamxanhapp_t('Lưu Nhật ký vào Sổ Điện tử VietGAP', 'Save Entry to Digital VietGAP Logbook')); ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- VietGAP Batches & QR Passport Cards -->
    <section class="mt-2">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:10px;">
            <h3 class="h3 mb-0">
                <?php echo s(local_mamxanhapp_t('Danh sách Lô Nông sản & Mã QR Truy xuất Nguồn gốc (' . count($batches) . ')', 'Active VietGAP Batches & Traceability QR Codes (' . count($batches) . ')')); ?>
            </h3>
            <div class="d-flex flex-wrap" style="gap:8px;">
                <a href="<?php echo (new moodle_url('/local/agriops/export_log.php', ['format' => 'html']))->out(false); ?>" class="btn btn-primary btn-sm">
                    <?php echo s(local_mamxanhapp_t('Xuất Hồ sơ Nhật ký Nộp bài Thực hành (HTML)', 'Export VietGAP Log Report for Assignment (HTML)')); ?>
                </a>
                <a href="<?php echo (new moodle_url('/local/agriops/export_log.php', ['format' => 'csv']))->out(false); ?>" class="btn btn-secondary btn-sm">
                    <?php echo s(local_mamxanhapp_t('Tải Nhật ký CSV', 'Download CSV Log')); ?>
                </a>
            </div>
        </div>
        <div class="row">
            <?php foreach ($batches as $b):
                $traceurl = (new moodle_url('/local/agriops/trace.php', ['code' => $b->batch_code]))->out(false);
                $qrsvg = \local_mamxanhapp\qr_helper::render_svg($traceurl, 138, '#234E3E');
                $logs = $DB->get_records('local_agriops_log', ['batchid' => $b->id], 'log_time DESC', '*', 0, 3);
            ?>
            <div class="col-12 col-xl-6 mb-4">
                <div class="mx-card p-4 h-100">
                    <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
                        <div style="flex:1;min-width:200px;">
                            <span class="badge mb-2" style="background:#EAF2EE;color:#234E3E;border:1px solid #E6ECE9;font-size:0.8rem;font-weight:700;">
                                VietGAP: <?php echo s($b->batch_code); ?>
                            </span>
                            <h4 class="h4 mb-2"><?php echo s($b->product_name); ?></h4>
                            <div class="small text-muted mb-3">
                                <?php echo s($b->region); ?> • <?php echo number_format((int)$b->area_m2); ?> m² • <?php echo number_format((int)$b->expected_yield_kg); ?> kg
                            </div>
                            <div class="d-flex flex-wrap" style="gap:8px;">
                                <a href="<?php echo $traceurl; ?>" class="btn btn-secondary btn-sm">
                                    <?php echo s(local_mamxanhapp_t('Mở Trang Truy xuất QR Công khai', 'Open Public QR Traceability Page')); ?>
                                </a>
                                <a href="<?php echo (new moodle_url('/local/agriops/export_log.php', ['batchid' => $b->id, 'format' => 'html']))->out(false); ?>" class="btn btn-primary btn-sm">
                                    <?php echo s(local_mamxanhapp_t('Xuất Hồ sơ Lô này', 'Export Batch Log')); ?>
                                </a>
                            </div>
                        </div>
                        <div class="text-center">
                            <?php echo $qrsvg; ?>
                            <div class="small text-muted fw-bold mt-1">SCAN VIETGAP</div>
                        </div>
                    </div>

                    <?php if (!empty($logs)): ?>
                    <hr style="border-color:#E6ECE9;">
                    <div style="font-size:0.88rem;">
                        <strong style="color:#234E3E;"><?php echo s(local_mamxanhapp_t('Nhật ký gần nhất:', 'Recent Field Logs:')); ?></strong>
                        <ul class="mb-0 mt-1" style="padding-left:18px;line-height:1.6;">
                            <?php foreach ($logs as $lg): ?>
                            <li>
                                <span class="text-muted">[<?php echo userdate($lg->log_time, '%d/%m %H:%M'); ?>]</span>
                                <?php echo s($lg->notes); ?>
                                <small class="fw-bold" style="color:#234E3E;">(pH <?php echo s($lg->ph_val); ?>, EC <?php echo s($lg->ec_val); ?>, PHI <?php echo (int)$lg->phi_days; ?>d)</small>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script>
function mxStartVoiceDiary() {
    var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    var statusEl = document.getElementById('mx-voice-status');
    var notesEl = document.getElementById('id_log_notes');
    var isEn = document.documentElement.lang && document.documentElement.lang.indexOf('en') === 0;

    if (!SpeechRecognition) {
        statusEl.textContent = isEn
            ? 'Browser voice engine unavailable in headless mode; please use quick template chips below.'
            : 'Trình duyệt hiện tại chưa bật mic giọng nói; bạn có thể chạm các nút Mẫu câu nhanh bên dưới.';
        return;
    }

    var rec = new SpeechRecognition();
    rec.lang = isEn ? 'en-US' : 'vi-VN';
    rec.interimResults = false;
    rec.maxAlternatives = 1;

    statusEl.textContent = isEn ? 'Listening... Speak your field log now...' : 'Đang lắng nghe... Bà con hãy đọc nội dung nhật ký...';
    rec.onresult = function(event) {
        var text = event.results[0][0].transcript;
        notesEl.value = (notesEl.value ? notesEl.value + ' ' : '') + text;
        document.getElementById('id_voice_input').value = '1';
        statusEl.style.color = '#234E3E';
        statusEl.textContent = isEn ? 'Voice captured successfully!' : 'Đã nhận diện giọng nói thành công!';
    };
    rec.onerror = function(err) {
        statusEl.textContent = (isEn ? 'Mic notice: ' : 'Thông báo Micro: ') + err.error;
    };
    rec.start();
}
</script>

<?php
echo $OUTPUT->footer();
