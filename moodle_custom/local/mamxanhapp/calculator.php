<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

local_mamxanhapp_handle_lang_switch();
require_login();

$syscontext = context_system::instance();
$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/mamxanhapp/calculator.php'));
$PAGE->set_pagelayout('standard');

$en = local_mamxanhapp_is_en();
$title = local_mamxanhapp_t('Trung tâm Công cụ Nông nghiệp Thông minh - Mầm Xanh LMS', 'Smart AgTech Toolkit & Weather Advisory - Mam Xanh LMS');
$PAGE->set_title($title);
$PAGE->set_heading(local_mamxanhapp_t('🧮 Trung tâm Công cụ Nông nghiệp & Cảnh báo Thời tiết', '🧮 Smart AgTech Calculator, Leaf Doctor & Micro-Climate Advisory'));

$roletype = local_mamxanhapp_user_role_type();
$backurl = ($roletype === 'cbkt' || $roletype === 'admin')
    ? new moodle_url('/local/mamxanhapp/manage.php')
    : new moodle_url('/local/mamxanhapp/index.php');

$weatherld = local_mamxanhapp_weather_data('Lâm Đồng');
$weatherdl = local_mamxanhapp_weather_data('Đắk Lắk');
$weatherdn = local_mamxanhapp_weather_data('Đồng Nai');

echo $OUTPUT->header();
?>

<div class="mx-toolkit-wrap">
    <div class="mb-3">
        <a href="<?php echo $backurl->out(false); ?>" class="btn btn-outline-success" style="min-height:48px;display:inline-flex;align-items:center;gap:8px;border-radius:14px;font-weight:700;border-width:2px;">
            <?php echo s(local_mamxanhapp_t('← Quay lại Dashboard', '← Back to Dashboard')); ?>
        </a>
    </div>

    <!-- Interactive Navigation Pills -->
    <div class="d-flex flex-wrap mb-4" style="gap:10px;" role="tablist">
        <button type="button" class="btn btn-success mx-tool-tab active" data-target="mx-tab-fert" onclick="mxSwitchToolTab('mx-tab-fert', this)" style="min-height:48px;border-radius:14px;font-weight:800;padding:0 18px;background:#1B4332;border-color:#1B4332;">
            💧 <?php echo s(local_mamxanhapp_t('1. Máy tính Pha Phân & Tưới Nhỏ giọt', '1. Fertigation & Drip Calculator')); ?>
        </button>
        <button type="button" class="btn btn-outline-success mx-tool-tab" data-target="mx-tab-leaf" onclick="mxSwitchToolTab('mx-tab-leaf', this)" style="min-height:48px;border-radius:14px;font-weight:800;padding:0 18px;border-width:2px;">
            🍃 <?php echo s(local_mamxanhapp_t('2. Chẩn đoán Triệu chứng Lá bệnh IPM', '2. Leaf Symptom & IPM Diagnostic')); ?>
        </button>
        <button type="button" class="btn btn-outline-success mx-tool-tab" data-target="mx-tab-weather" onclick="mxSwitchToolTab('mx-tab-weather', this)" style="min-height:48px;border-radius:14px;font-weight:800;padding:0 18px;border-width:2px;">
            🌦️ <?php echo s(local_mamxanhapp_t('3. Trạm Cảnh báo Thời tiết Tiểu vùng', '3. Micro-Climate Spray Advisory')); ?>
        </button>
    </div>

    <!-- ========================================================== -->
    <!-- TOOL 1: FERTIGATION & DRIP IRRIGATION CALCULATOR           -->
    <!-- ========================================================== -->
    <section id="mx-tab-fert" class="mx-tool-pane" style="background:#FFF;border:2px solid #DCEEE0;border-radius:24px;padding:24px;margin-bottom:24px;">
        <h3 style="color:#1B4332;font-weight:800;font-size:1.35rem;margin-bottom:8px;">
            💧 <?php echo s(local_mamxanhapp_t('Máy tính Pha Dinh dưỡng A-B & Lịch Tưới Nhỏ giọt Tự động', 'Automated A-B Fertigation & Drip Schedule Calculator')); ?>
        </h3>
        <p style="color:#4E6358;margin-bottom:20px;">
            <?php echo s(local_mamxanhapp_t(
                'Tự động tính lượng dung dịch mẹ Stock A, Stock B (tỷ lệ 1:100), axit hạ pH (H3PO4) và thời gian mở van tưới nhỏ giọt theo cây trồng & giai đoạn sinh trưởng.',
                'Calculate Stock A & B concentrate volume (1:100), phosphoric acid pH buffer, and exact drip valve minutes by crop and growth stage.'
            )); ?>
        </p>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="mb-3">
                    <label for="mx-crop" class="form-label fw-bold"><?php echo s(local_mamxanhapp_t('Loại cây trồng chủ lực', 'Target Crop')); ?></label>
                    <select id="mx-crop" class="form-control" style="min-height:48px;border-radius:12px;font-weight:600;" onchange="mxRunFertCalc()">
                        <option value="leafy" data-ec="1.6" data-ph="5.8 - 6.2" data-water="3.5"><?php echo s(local_mamxanhapp_t('Rau ăn lá Thủy canh / Giá thể (Xà lách, Cải)', 'Hydroponic Leafy Greens (Lettuce, Pakchoi)')); ?></option>
                        <option value="melon" data-ec="2.4" data-ph="6.0 - 6.5" data-water="5.0"><?php echo s(local_mamxanhapp_t('Dưa lưới Nhà màng Công nghệ cao', 'Greenhouse Netted Melon')); ?></option>
                        <option value="tomato" data-ec="2.6" data-ph="5.8 - 6.3" data-water="5.5"><?php echo s(local_mamxanhapp_t('Cà chua Beef / Ớt chuông Đà Lạt', 'Greenhouse Beef Tomato / Bell Pepper')); ?></option>
                        <option value="durian" data-ec="1.8" data-ph="5.8 - 6.5" data-water="8.0"><?php echo s(local_mamxanhapp_t('Sầu riêng VietGAP (Tưới gốc Fertigation)', 'VietGAP Durian Orchard Fertigation')); ?></option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="mx-stage" class="form-label fw-bold"><?php echo s(local_mamxanhapp_t('Giai đoạn sinh trưởng', 'Growth Stage')); ?></label>
                    <select id="mx-stage" class="form-control" style="min-height:48px;border-radius:12px;font-weight:600;" onchange="mxRunFertCalc()">
                        <option value="0.7"><?php echo s(local_mamxanhapp_t('1. Cây con / Hồi xanh (70% liều EC)', '1. Seedling / Recovery (70% EC)')); ?></option>
                        <option value="1.0" selected><?php echo s(local_mamxanhapp_t('2. Phát triển thân lá mạnh (100% liều EC)', '2. Vegetative Growth (100% EC)')); ?></option>
                        <option value="1.15"><?php echo s(local_mamxanhapp_t('3. Ra hoa - Nuôi trái (115% liều EC + giàu Kali)', '3. Flowering & Fruit Filling (115% EC + High K)')); ?></option>
                        <option value="0.85"><?php echo s(local_mamxanhapp_t('4. Trước thu hoạch 7 ngày (Giảm đạm, tăng độ ngọt Brix)', '4. Pre-harvest Ripening (85% EC, High Brix)')); ?></option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label for="mx-area" class="form-label fw-bold"><?php echo s(local_mamxanhapp_t('Diện tích vườn (m²)', 'Farm Area (m²)')); ?></label>
                        <input type="number" id="mx-area" class="form-control" value="1000" min="50" max="100000" style="min-height:48px;border-radius:12px;font-weight:700;" oninput="mxRunFertCalc()">
                    </div>
                    <div class="col-6 mb-3">
                        <label for="mx-tank" class="form-label fw-bold"><?php echo s(local_mamxanhapp_t('Thể tích bồn pha (Lít)', 'Mixing Tank (Liters)')); ?></label>
                        <input type="number" id="mx-tank" class="form-control" value="500" min="20" max="20000" style="min-height:48px;border-radius:12px;font-weight:700;" oninput="mxRunFertCalc()">
                    </div>
                </div>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label for="mx-raw-ec" class="form-label fw-bold"><?php echo s(local_mamxanhapp_t('EC nước đầu vào (mS/cm)', 'Source Water EC')); ?></label>
                        <input type="number" id="mx-raw-ec" class="form-control" value="0.2" step="0.1" min="0" max="2.0" style="min-height:48px;border-radius:12px;font-weight:700;" oninput="mxRunFertCalc()">
                    </div>
                    <div class="col-6 mb-3">
                        <label for="mx-emitter" class="form-label fw-bold"><?php echo s(local_mamxanhapp_t('Lưu lượng đầu nhỏ giọt (L/h)', 'Drip Emitter (L/hr)')); ?></label>
                        <input type="number" id="mx-emitter" class="form-control" value="2.0" step="0.5" min="0.5" max="20" style="min-height:48px;border-radius:12px;font-weight:700;" oninput="mxRunFertCalc()">
                    </div>
                </div>

                <button type="button" class="btn btn-success w-100" onclick="mxRunFertCalc()" style="min-height:50px;border-radius:14px;font-weight:800;background:#1B4332;border-color:#1B4332;">
                    ⚡ <?php echo s(local_mamxanhapp_t('Tính toán Công thức Pha & Giờ Tưới', 'Calculate Exact Fertigation Formula')); ?>
                </button>
            </div>

            <div class="col-lg-6">
                <div id="mx-fert-result" style="background:linear-gradient(145deg,#F1F8E9,#E8F5E9);border:2px solid #81C784;border-radius:20px;padding:22px;height:100%;">
                    <h4 style="color:#1B4332;font-weight:800;margin-bottom:14px;">
                        📋 <?php echo s(local_mamxanhapp_t('Kết quả Khuyến nghị Kỹ thuật', 'Recommended Field Recipe')); ?>
                    </h4>
                    <div class="row text-center mb-3">
                        <div class="col-6 mb-2">
                            <div style="background:#FFF;border-radius:14px;padding:12px;border:1px solid #C8E6C9;">
                                <div style="font-size:0.8rem;color:#4E6358;font-weight:700;">EC MỤC TIÊU / TARGET EC</div>
                                <div id="res-target-ec" style="font-size:1.55rem;font-weight:800;color:#1B4332;">1.60 mS/cm</div>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div style="background:#FFF;border-radius:14px;padding:12px;border:1px solid #C8E6C9;">
                                <div style="font-size:0.8rem;color:#4E6358;font-weight:700;">NGƯỠNG pH CHUẨN</div>
                                <div id="res-target-ph" style="font-size:1.55rem;font-weight:800;color:#2E7D32;">5.8 - 6.2</div>
                            </div>
                        </div>
                    </div>

                    <ul style="list-style:none;padding:0;margin:0;line-height:1.9;font-size:0.98rem;">
                        <li>🧪 <strong><?php echo s(local_mamxanhapp_t('Dung dịch mẹ Bồn A (Ca(NO3)2 + Fe-EDDHA):', 'Stock Solution A (Calcium + Iron):')); ?></strong> <span id="res-stock-a" style="font-weight:800;color:#1B4332;">2.10 Lít</span></li>
                        <li>🧪 <strong><?php echo s(local_mamxanhapp_t('Dung dịch mẹ Bồn B (MKP + KNO3 + MgSO4 + Vi lượng):', 'Stock Solution B (NPK + Mg + Micro):')); ?></strong> <span id="res-stock-b" style="font-weight:800;color:#1B4332;">2.10 Lít</span></li>
                        <li>⚖️ <strong><?php echo s(local_mamxanhapp_t('Axit H3PO4 85% điều chỉnh pH dự kiến:', 'Estimated H3PO4 pH Down:')); ?></strong> <span id="res-acid" style="font-weight:800;color:#C62828;">35 ml</span></li>
                        <li>🚿 <strong><?php echo s(local_mamxanhapp_t('Tổng lượng nước tưới cả vườn/ngày:', 'Total Daily Irrigation Volume:')); ?></strong> <span id="res-daily-water" style="font-weight:800;color:#1B4332;">3,500 Lít/ngày</span></li>
                        <li>⏱️ <strong><?php echo s(local_mamxanhapp_t('Thời gian mở van nhỏ giọt (chia 4 lần/ngày):', 'Drip Valve Duration (4 pulses/day):')); ?></strong> <span id="res-drip-mins" style="font-weight:800;color:#2E7D32;">13 phút / lần</span></li>
                    </ul>

                    <div style="margin-top:14px;background:#FFFDE7;border-left:4px solid #F9A825;padding:10px 14px;border-radius:10px;font-size:0.86rem;color:#4E342E;">
                        ⚠️ <strong><?php echo s(local_mamxanhapp_t('Nguyên tắc vàng:', 'Safety Rule:')); ?></strong>
                        <?php echo s(local_mamxanhapp_t(
                            'Tuyệt đối KHÔNG đổ trực tiếp Stock A vào Stock B đậm đặc (gây kết tủa thạch cao CaSO4 làm tắc béc nhỏ giọt). Pha loãng A vào bồn nước khuấy đều 3 phút rồi mới châm B.',
                            'NEVER mix concentrated Stock A and Stock B directly (causes CaSO4 precipitation & emitter clogging). Dissolve A into water tank first, stir 3 mins, then add B.'
                        )); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================== -->
    <!-- TOOL 2: INTERACTIVE LEAF SYMPTOM DIAGNOSTIC                -->
    <!-- ========================================================== -->
    <section id="mx-tab-leaf" class="mx-tool-pane" style="display:none;background:#FFF;border:2px solid #DCEEE0;border-radius:24px;padding:24px;margin-bottom:24px;">
        <h3 style="color:#1B4332;font-weight:800;font-size:1.35rem;margin-bottom:8px;">
            🍃 <?php echo s(local_mamxanhapp_t('Chẩn đoán Nhanh Triệu chứng Lá bệnh & Phác đồ Sinh học IPM', 'Interactive Leaf Symptom Doctor & Biological IPM Protocol')); ?>
        </h3>
        <p style="color:#4E6358;margin-bottom:18px;">
            <?php echo s(local_mamxanhapp_t('Bấm chọn biểu hiện thực tế trên lá/cây tại vườn để nhận phác đồ xử lý sinh học an toàn VietGAP:', 'Tap the observed symptom on your crop to view the VietGAP-compliant biological IPM treatment:')); ?>
        </p>

        <div class="row mb-3">
            <div class="col-md-6 col-lg-4 mb-3">
                <button type="button" class="btn btn-outline-success w-100 text-start p-3" style="min-height:68px;border-radius:16px;font-weight:700;border-width:2px;" onclick="mxDiagnoseLeaf('rust')">
                    🟠 <?php echo s(local_mamxanhapp_t('Ổ bột màu nâu cam / gỉ sắt mặt dưới lá già', 'Orange-brown powdery pustules under older leaves')); ?>
                </button>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <button type="button" class="btn btn-outline-success w-100 text-start p-3" style="min-height:68px;border-radius:16px;font-weight:700;border-width:2px;" onclick="mxDiagnoseLeaf('thrips')">
                    🌀 <?php echo s(local_mamxanhapp_t('Lá non ngọn xoăn tít, sần sùi, ánh bạc', 'Curled young shoots with silvery bronzed streaks')); ?>
                </button>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <button type="button" class="btn btn-outline-success w-100 text-start p-3" style="min-height:68px;border-radius:16px;font-weight:700;border-width:2px;" onclick="mxDiagnoseLeaf('mealybug')">
                    ⚪ <?php echo s(local_mamxanhapp_t('Lớp phấn sáp trắng bám kẽ lá, cuống trái & kiến đen', 'White waxy cottony clusters on stems/fruits + ants')); ?>
                </button>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <button type="button" class="btn btn-outline-success w-100 text-start p-3" style="min-height:68px;border-radius:16px;font-weight:700;border-width:2px;" onclick="mxDiagnoseLeaf('spidermite')">
                    🕸️ <?php echo s(local_mamxanhapp_t('Đốm vàng li ti mặt trên, tơ mảnh đỏ mặt dưới khi nắng nóng', 'Tiny yellow stippling & fine webbing in hot dry weather')); ?>
                </button>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <button type="button" class="btn btn-outline-success w-100 text-start p-3" style="min-height:68px;border-radius:16px;font-weight:700;border-width:2px;" onclick="mxDiagnoseLeaf('calcium')">
                    🍂 <?php echo s(local_mamxanhapp_t('Cháy khô mép lá non (Tipburn) / Thối đen đít trái', 'Young leaf edge tipburn / Blossom-end rot on fruit')); ?>
                </button>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <button type="button" class="btn btn-outline-success w-100 text-start p-3" style="min-height:68px;border-radius:16px;font-weight:700;border-width:2px;" onclick="mxDiagnoseLeaf('rootrot')">
                    🥀 <?php echo s(local_mamxanhapp_t('Vàng lá thối rễ, xì mủ thân khi đất bí ẩm pH thấp', 'Yellowing wilt, root rot & trunk gummosis in wet acidic soil')); ?>
                </button>
            </div>
        </div>

        <div id="mx-leaf-result" style="background:#F1F8E9;border:2px solid #66BB6A;border-radius:20px;padding:22px;">
            <h4 id="mx-diag-title" style="color:#1B4332;font-weight:800;margin-bottom:8px;">
                🔍 <?php echo s(local_mamxanhapp_t('Chẩn đoán: Bệnh Gỉ sắt (Puccinia / Uromyces spp.)', 'Diagnosis: Leaf Rust (Puccinia / Uromyces spp.)')); ?>
            </h4>
            <p id="mx-diag-cause" style="color:#33691E;font-weight:600;margin-bottom:10px;">
                <?php echo s(local_mamxanhapp_t('Nguyên nhân: Nấm phát sinh mạnh khi sương mù ẩm độ >85%, tán cây rậm rạp thiếu thông gió.', 'Cause: Fungal spores germinate rapidly under >85% humidity, morning mist, and dense canopy.')); ?>
            </p>
            <div id="mx-diag-action" style="background:#FFF;border-radius:14px;padding:14px 16px;line-height:1.65;color:#14281D;">
                <strong><?php echo s(local_mamxanhapp_t('Phác đồ IPM Sinh học VietGAP:', 'VietGAP Biological IPM Protocol:')); ?></strong><br>
                1. <?php echo s(local_mamxanhapp_t('Cắt tỉa lá bệnh già phía gốc đem tiêu hủy ngoài vườn, bật quạt đối lưu giảm ẩm.', 'Prune infected basal leaves and remove from greenhouse; increase ventilation.')); ?><br>
                2. <?php echo s(local_mamxanhapp_t('Phun vi khuẩn đối kháng Bacillus subtilis hoặc nấm Chaetomium 20g/bình 16L vào sáng sớm.', 'Spray antagonistic Bacillus subtilis or Chaetomium bio-fungicide (20g/16L) early morning.')); ?><br>
                3. <?php echo s(local_mamxanhapp_t('Thời gian cách ly (PHI): 0 ngày (An toàn sinh học 100%).', 'Pre-Harvest Interval (PHI): 0 days (100% bio-safe).')); ?>
            </div>
        </div>
    </section>

    <!-- ========================================================== -->
    <!-- TOOL 3: MICRO-CLIMATE WEATHER SPRAYING ADVISORY            -->
    <!-- ========================================================== -->
    <section id="mx-tab-weather" class="mx-tool-pane" style="display:none;background:#FFF;border:2px solid #DCEEE0;border-radius:24px;padding:24px;margin-bottom:24px;">
        <h3 style="color:#1B4332;font-weight:800;font-size:1.35rem;margin-bottom:16px;">
            🌦️ <?php echo s(local_mamxanhapp_t('Trạm Cảnh báo Thời tiết Phun tưới theo 3 Tiểu vùng Khí hậu', 'Micro-Climate Spraying & Fertigation Stations (3 Growing Regions)')); ?>
        </h3>
        <div class="row">
            <?php foreach ([$weatherld, $weatherdl, $weatherdn] as $w): ?>
            <div class="col-lg-4 mb-3">
                <div style="background:#F8FCF9;border:2px solid #C8E6C9;border-radius:20px;padding:20px;height:100%;display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <span style="display:inline-block;background:#D8F3DC;color:#1B4332;padding:5px 12px;border-radius:999px;font-weight:800;font-size:0.84rem;margin-bottom:10px;">
                            <?php echo s($w['badge']); ?>
                        </span>
                        <h4 style="color:#1B4332;font-weight:800;font-size:1.12rem;margin-bottom:12px;">
                            📍 <?php echo s($w['region']); ?>
                        </h4>
                        <div class="row mb-3" style="font-size:0.9rem;">
                            <div class="col-6 mb-2">🌡️ <strong><?php echo s($w['temp']); ?></strong></div>
                            <div class="col-6 mb-2">💧 <strong><?php echo s($w['humidity']); ?></strong></div>
                            <div class="col-6 mb-2">💨 <strong><?php echo s($w['wind']); ?></strong></div>
                            <div class="col-6 mb-2">🌿 VPD: <strong><?php echo s($w['vpd']); ?></strong></div>
                            <div class="col-6">☀️ UV: <strong><?php echo s($w['uv']); ?></strong></div>
                            <div class="col-6">🌧️ Rain: <strong><?php echo s($w['rain_prob']); ?></strong></div>
                        </div>
                        <p style="font-size:0.92rem;color:#263238;line-height:1.55;">
                            <?php echo s($w['advice']); ?>
                        </p>
                    </div>
                    <div style="background:#1B4332;color:#FFF;padding:10px 14px;border-radius:12px;font-weight:700;font-size:0.88rem;">
                        ⏰ <?php echo s(local_mamxanhapp_t('Khung giờ vàng: ', 'Golden Window: ') . $w['window']); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script>
function mxSwitchToolTab(paneId, btnEl) {
    document.querySelectorAll('.mx-tool-pane').forEach(function(el) {
        el.style.display = 'none';
    });
    var target = document.getElementById(paneId);
    if (target) target.style.display = 'block';

    document.querySelectorAll('.mx-tool-tab').forEach(function(b) {
        b.classList.remove('btn-success', 'active');
        b.classList.add('btn-outline-success');
        b.style.background = '';
    });
    if (btnEl) {
        btnEl.classList.remove('btn-outline-success');
        btnEl.classList.add('btn-success', 'active');
        btnEl.style.background = '#1B4332';
    }
}

function mxRunFertCalc() {
    var cropSel = document.getElementById('mx-crop');
    var opt = cropSel.options[cropSel.selectedIndex];
    var baseEc = parseFloat(opt.getAttribute('data-ec')) || 1.6;
    var phRange = opt.getAttribute('data-ph') || '5.8 - 6.2';
    var waterPerM2 = parseFloat(opt.getAttribute('data-water')) || 4.0;

    var stageMult = parseFloat(document.getElementById('mx-stage').value) || 1.0;
    var area = Math.max(10, parseFloat(document.getElementById('mx-area').value) || 1000);
    var tank = Math.max(10, parseFloat(document.getElementById('mx-tank').value) || 500);
    var rawEc = Math.max(0, parseFloat(document.getElementById('mx-raw-ec').value) || 0.2);
    var emitter = Math.max(0.5, parseFloat(document.getElementById('mx-emitter').value) || 2.0);

    var targetEc = Math.max(rawEc + 0.4, baseEc * stageMult);
    var deltaEc = Math.max(0.2, targetEc - rawEc);

    // Agronomic rule: ~3.0 ml of 100x Stock A & B per Liter raises EC by 1.0 mS/cm
    var stockLiters = ((deltaEc * 3.0 * tank) / 1000.0).toFixed(2);
    var acidMl = Math.round(tank * 0.07);
    var dailyWater = Math.round(area * waterPerM2 * stageMult);
    // Assuming 4 emitters/m2 divided across 4 pulses/day
    var flowPerHour = area * 4 * emitter;
    var pulseMins = Math.max(5, Math.round(((dailyWater / 4) / flowPerHour) * 60));

    var isEn = document.documentElement.lang && document.documentElement.lang.indexOf('en') === 0;
    document.getElementById('res-target-ec').textContent = targetEc.toFixed(2) + ' mS/cm';
    document.getElementById('res-target-ph').textContent = phRange;
    document.getElementById('res-stock-a').textContent = stockLiters + (isEn ? ' Liters' : ' Lít');
    document.getElementById('res-stock-b').textContent = stockLiters + (isEn ? ' Liters' : ' Lít');
    document.getElementById('res-acid').textContent = acidMl + ' ml';
    document.getElementById('res-daily-water').textContent = dailyWater.toLocaleString() + (isEn ? ' L/day' : ' Lít/ngày');
    document.getElementById('res-drip-mins').textContent = pulseMins + (isEn ? ' mins / pulse' : ' phút / lần');
}

function mxDiagnoseLeaf(code) {
    var isEn = document.documentElement.lang && document.documentElement.lang.indexOf('en') === 0;
    var db = {
        rust: {
            vi: ['Chẩn đoán: Bệnh Gỉ sắt (Puccinia / Uromyces spp.)', 'Nguyên nhân: Bào tử nấm phát tán mạnh trong sương mù ẩm độ >85%, vườn thiếu thông thoáng.', '1. Tỉa bỏ lá già nhiễm bệnh đem tiêu hủy ngoài khu canh tác.<br>2. Phun vi sinh đối kháng <strong>Bacillus subtilis</strong> hoặc nấm <strong>Chaetomium</strong> (25g/16L).<br>3. Tưới nhỏ giọt gốc sáng sớm, không tưới phun mưa chiều muộn. PHI: 0 ngày.'],
            en: ['Diagnosis: Leaf Rust (Puccinia / Uromyces spp.)', 'Cause: Fungal spores thrive in >85% humidity, morning fog, and poor airflow.', '1. Prune infected lower leaves and dispose outside greenhouse.<br>2. Spray antagonistic <strong>Bacillus subtilis</strong> or <strong>Chaetomium</strong> (25g/16L).<br>3. Use drip irrigation only; avoid evening overhead misting. PHI: 0 days.']
        },
        thrips: {
            vi: ['Chẩn đoán: Bọ trĩ chích hút đọt non (Thrips palmi)', 'Nguyên nhân: Thời tiết nắng ấm khô, bọ trĩ ẩn trong đọt non hút nhựa và truyền virus xoăn lá.', '1. Treo <strong>bẫy dính màu xanh dương / vàng</strong> (40 bẫy/1000m²) cách ngọn 15cm.<br>2. Phun nấm ký sinh côn trùng <strong>Beauveria bassiana + Metarhizium</strong> hoặc dầu Neem sinh học lúc chiều mát.<br>3. Bảo tồn thiên địch bọ rùa và nhện bắt mồi. PHI: 0 ngày.'],
            en: ['Diagnosis: Melon/Chilli Thrips (Thrips palmi)', 'Cause: Warm dry microclimate; thrips feed inside growing tips and vector leaf-curl viruses.', '1. Hang <strong>blue/yellow sticky traps</strong> (40 traps/1000m²) 15cm above canopy.<br>2. Apply entomopathogenic fungi <strong>Beauveria + Metarhizium</strong> + cold-pressed Neem oil at dusk.<br>3. Conserve predatory mites (Amblyseius swirskii). PHI: 0 days.']
        },
        mealybug: {
            vi: ['Chẩn đoán: Rệp sáp phấn trắng (Planococcus / Pseudococcus)', 'Nguyên nhân: Cộng sinh với kiến đen phát tán quanh cuống trái, kẽ cành và vùng rễ.', '1. Vệ sinh vườn, chặn đường kiến cộng sinh quanh gốc cây.<br>2. Phun <strong>Dầu khoáng sinh học SK Enspray 99EC (0.3%)</strong> làm tan lớp sáp bảo vệ kết hợp nấm trắng <strong>Beauveria</strong>.<br>3. Phun kỹ kẽ lá và cuống trái sầu riêng/cây ăn quả. PHI: 0–3 ngày.'],
            en: ['Diagnosis: Mealybugs (Planococcus spp.)', 'Cause: Symbiotic ants farm mealybugs in fruit clusters, stem nodes, and root collars.', '1. Control ant trails on tree trunks and prune overcrowded shoots.<br>2. Spray <strong>Horticultural Mineral Oil (0.3%)</strong> to penetrate waxy coating + <strong>Beauveria bassiana</strong>.<br>3. Target fruit stalks thoroughly. PHI: 0–3 days.']
        },
        spidermite: {
            vi: ['Chẩn đoán: Nhện đỏ hại cây (Tetranychus urticae)', 'Nguyên nhân: Nắng nóng khô hạn kéo dài, ẩm độ <60%, lạm dụng thuốc hóa học diệt mất thiên địch.', '1. Tăng ẩm cục bộ và thả nhện thiên địch <strong>Phytoseiulus persimilis</strong>.<br>2. Phun sinh học hoạt chất <strong>Abamectin sinh học / Matrine thảo mộc</strong> luân phiên dầu khoáng.<br>3. Phun ướt đều mặt dưới lá.'],
            en: ['Diagnosis: Two-Spotted Spider Mite (Tetranychus urticae)', 'Cause: Hot dry conditions (<60% RH) and loss of natural predators.', '1. Release predatory mites <strong>Phytoseiulus persimilis</strong>.<br>2. Apply bio-acaricide <strong>Abamectin / Botanical Matrine</strong> targeting leaf undersides.<br>3. Maintain balanced VPD via mist/drip cooling.']
        },
        calcium: {
            vi: ['Chẩn đoán: Thiếu hụt Canxi sinh lý (Tipburn / Thối đít trái)', 'Nguyên nhân: Nhiệt độ cao bốc hơi nhanh hoặc EC quá cao cản trở rễ hút Canxi lên đọt non/trái.', '1. Kiểm tra giảm EC dung dịch tưới về ngưỡng chuẩn (2.0–2.4 mS/cm), giữ pH 5.8–6.2.<br>2. Chia nhỏ chu kỳ tưới nhỏ giọt vào buổi trưa nắng, bật quạt thông gió tăng thoát hơi nước điều hòa.<br>3. Bổ sung <strong>Canxi-Bo hữu cơ</strong> qua lá sáng sớm.'],
            en: ['Diagnosis: Physiological Calcium Deficiency (Tipburn / Blossom-End Rot)', 'Cause: High VPD heat stress or excessive EC blocking xylem Calcium transport.', '1. Lower root-zone EC to 2.0–2.4 mS/cm and stabilize pH at 5.8–6.2.<br>2. Split midday drip irrigation into shorter frequent pulses + improve airflow.<br>3. Foliar spray chelated <strong>Calcium-Boron</strong> early morning.']
        },
        rootrot: {
            vi: ['Chẩn đoán: Nấm Phytophthora / Fusarium gây vàng lá thối rễ & xì mủ', 'Nguyên nhân: Đất nén chặt ngập úng, pH đất chua (<5.0), thiếu hữu cơ và vi sinh đối kháng.', '1. Khơi rãnh thoát nước triệt để, nâng pH đất lên 5.8–6.2 bằng vôi Dolomite.<br>2. Tưới gốc nấm đối kháng <strong>Trichoderma spp. (50g/gốc) + Humic kích rễ</strong> kết hợp phân hữu cơ vi sinh hoai mục.<br>3. Cạo sạch vết xì mủ thân và quét chế phẩm đồng sinh học / Phosphonate.'],
            en: ['Diagnosis: Phytophthora / Fusarium Root Rot & Trunk Gummosis', 'Cause: Waterlogged compacted soil, acidic pH (<5.0), low beneficial rhizosphere microbes.', '1. Improve drainage immediately and raise soil pH to 5.8–6.2 with Dolomite.<br>2. Drench root zone with <strong>Trichoderma spp. (50g/tree) + Humic acid</strong> + microbial compost.<br>3. Scrape trunk lesions cleanly and apply Potassium Phosphonate bio-paste.']
        }
    };
    var item = db[code] || db.rust;
    var pack = isEn ? item.en : item.vi;
    document.getElementById('mx-diag-title').innerHTML = '🔍 ' + pack[0];
    document.getElementById('mx-diag-cause').textContent = pack[1];
    document.getElementById('mx-diag-action').innerHTML = pack[2];
}
document.addEventListener('DOMContentLoaded', mxRunFertCalc);
</script>

<?php
echo $OUTPUT->footer();
