<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/completionlib.php');

local_mamxanhapp_handle_lang_switch();
require_login();

if (isguestuser()) {
    redirect(new moodle_url('/login/index.php'));
}

$syscontext = context_system::instance();
$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/mamxanhapp/index.php'));
$PAGE->set_pagelayout('standard');

$title = local_mamxanhapp_t('Bảng điều khiển Nông dân - Mầm Xanh LMS 2.0', 'Farmer Learning Hub - Mam Xanh LMS 2.0');
$PAGE->set_title($title);
$PAGE->set_heading(local_mamxanhapp_t('🌱 Không gian Tập huấn Nông dân Số', '🌱 Digital Farmer Training Hub'));

$dbman = $DB->get_manager();
$userid = (int)$USER->id;
$en = local_mamxanhapp_is_en();

// 1. Load Farmer Household Profile
$profile = null;
if ($dbman->table_exists('local_farmerprofile')) {
    $profile = $DB->get_record('local_farmerprofile', ['userid' => $userid]);
}
$region = $profile && !empty($profile->region) ? $profile->region : 'Lâm Đồng';
$farmname = $profile && !empty($profile->farm_name)
    ? $profile->farm_name
    : local_mamxanhapp_t('Nông hộ Công nghệ cao Mầm Xanh', 'Mam Xanh Smart Farm');
$aream2 = $profile ? (int)$profile->area_m2 : 2500;
$weather = local_mamxanhapp_weather_data($region);

// 2. Load Courses (Enrolled first, fallback to all visible courses excluding SITEID)
$courses = enrol_get_my_courses(['id', 'fullname', 'shortname', 'summary', 'enablecompletion']);
if (empty($courses)) {
    $all = get_courses();
    $courses = [];
    foreach ($all as $c) {
        if ((int)$c->id !== SITEID && !empty($c->visible)) {
            $courses[$c->id] = $c;
        }
    }
}

// 3. Calculate Course Progress, Quiz Scores & Completion Status
$courseitems = [];
$totalcompletedmods = 0;
$totalmods = 0;
$completedcoursescount = 0;
$quizscores = [];

$courseicons = [
    'MASTER-AGRITECH-TPL' => '🏆',
    'CHAMSOC01'           => '🌿',
    'CHEBIEN01'           => '❄️',
    'IOTNHAMANG01'        => '📡',
    'THUYCANH01'          => '💧',
    'SAURIENG01'          => '🍈',
    'PHANVISINH01'        => '🦠',
];

usort($courses, static function($a, $b) {
    if ($a->shortname === 'MASTER-AGRITECH-TPL') return -1;
    if ($b->shortname === 'MASTER-AGRITECH-TPL') return 1;
    return ($a->id <=> $b->id);
});

foreach ($courses as $course) {
    if ((int)$course->id === SITEID) {
        continue;
    }
    $modinfo = get_fast_modinfo($course, $userid);
    $completion = new completion_info($course);
    $cmods = 0;
    $cdone = 0;
    $firstcmurl = new moodle_url('/course/view.php', ['id' => $course->id]);

    foreach ($modinfo->get_cms() as $cm) {
        if (!$cm->uservisible || $cm->modname === 'label') {
            continue;
        }
        if ($cmods === 0 && $cm->url) {
            $firstcmurl = $cm->url;
        }
        $cmods++;
        if ($completion->is_enabled($cm)) {
            $cdata = $completion->get_data($cm, false, $userid);
            if (!empty($cdata->completionstate) && (int)$cdata->completionstate !== COMPLETION_INCOMPLETE) {
                $cdone++;
            }
        }
    }

    // Check highest finished quiz attempt percentage in this course
    $bestquizpct = null;
    $quizzes = $DB->get_records('quiz', ['course' => $course->id]);
    foreach ($quizzes as $q) {
        if ((float)$q->sumgrades <= 0) {
            continue;
        }
        $attempts = $DB->get_records('quiz_attempts', [
            'quiz'   => $q->id,
            'userid' => $userid,
            'state'  => 'finished',
        ], 'sumgrades DESC', 'id,sumgrades', 0, 1);
        if (!empty($attempts)) {
            $top = reset($attempts);
            $pct = round(((float)$top->sumgrades / (float)$q->sumgrades) * 100.0, 1);
            if ($bestquizpct === null || $pct > $bestquizpct) {
                $bestquizpct = $pct;
            }
        }
    }
    if ($bestquizpct !== null) {
        $quizscores[] = $bestquizpct;
        if ($bestquizpct >= 70.0 && $cdone === 0 && $cmods > 0) {
            $cdone = max(1, $cdone);
        }
    }

    $totalmods += $cmods;
    $totalcompletedmods += $cdone;
    $progress = $cmods > 0 ? (int)round(($cdone / $cmods) * 100) : 0;
    if ($bestquizpct !== null && $bestquizpct >= 70.0 && $progress < 100 && $cdone >= max(1, $cmods - 1)) {
        $progress = 100;
    }

    if ($progress >= 100 || ($bestquizpct !== null && $bestquizpct >= 70.0 && $progress >= 75)) {
        $completedcoursescount++;
        $statuskey = 'done';
        $statuslabel = local_mamxanhapp_t('✅ Đã hoàn thành', '✅ Completed');
        $statusbg = '#D8F3DC';
        $statusfg = '#1B4332';
    } else if ($progress > 0 || $bestquizpct !== null) {
        $statuskey = 'learning';
        $statuslabel = local_mamxanhapp_t('⏳ Đang học (' . $progress . '%)', '⏳ In Progress (' . $progress . '%)');
        $statusbg = '#FFF8E1';
        $statusfg = '#E65100';
    } else {
        $statuskey = 'new';
        $statuslabel = local_mamxanhapp_t('📘 Chưa học', '📘 Not Started');
        $statusbg = '#ECEFF1';
        $statusfg = '#37474F';
    }

    $icon = $courseicons[$course->shortname] ?? '🌾';
    $cleanSummary = trim(strip_tags(format_text($course->summary ?? '', FORMAT_HTML)));
    if (\core_text::strlen($cleanSummary) > 135) {
        $cleanSummary = \core_text::substr($cleanSummary, 0, 132) . '...';
    }

    $courseitems[] = (object)[
        'id'          => $course->id,
        'shortname'   => $course->shortname,
        'fullname'    => format_string($course->fullname),
        'summary'     => $cleanSummary,
        'icon'        => $icon,
        'cmods'       => $cmods,
        'cdone'       => $cdone,
        'progress'    => $progress,
        'quizpct'     => $bestquizpct,
        'statuskey'   => $statuskey,
        'statuslabel' => $statuslabel,
        'statusbg'    => $statusbg,
        'statusfg'    => $statusfg,
        'courseurl'   => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
        'firstcmurl'  => $firstcmurl->out(false),
    ];
}

// 4. Certificates & VietGAP Batches count
$certcount = 0;
if ($dbman->table_exists('local_mamxanhcert')) {
    $certcount = $DB->count_records('local_mamxanhcert', ['userid' => $userid, 'status' => 'valid']);
}
$batchcount = 0;
if ($dbman->table_exists('local_agriops_batch')) {
    $batchcount = $DB->count_records('local_agriops_batch', ['userid' => $userid]);
}
$avgquiz = !empty($quizscores) ? round(array_sum($quizscores) / count($quizscores), 1) . '%' : '--';

$roletype = local_mamxanhapp_user_role_type();
$langviurl = (new moodle_url('/local/mamxanhapp/index.php', ['lang' => 'vi']))->out(false);
$langenurl = (new moodle_url('/local/mamxanhapp/index.php', ['lang' => 'en']))->out(false);

echo $OUTPUT->header();
?>

<div class="mx-hub-container">
    <!-- ============================================================= -->
    <!-- A. IMMERSIVE AGTECH HERO SECTION                              -->
    <!-- ============================================================= -->
    <section class="mx-hero-banner">
        <div class="mx-hero-main">
            <div class="mx-hero-top-row">
                <div class="mx-hero-farmchip">
                    <span>🏡 <?php echo s($farmname); ?></span>
                    <span>•</span>
                    <span>📍 <?php echo s($region); ?> (<?php echo number_format($aream2); ?> m²)</span>
                    <span>•</span>
                    <span>🌿 VietGAP Active</span>
                </div>
                <div class="mx-hero-quick-actions">
                    <div class="mx-lang-pill" id="mx-lang-switcher">
                        <a href="<?php echo $langviurl; ?>" class="<?php echo !$en ? 'active' : ''; ?>" id="mx-switch-vi">🇻🇳 VI</a>
                        <a href="<?php echo $langenurl; ?>" class="<?php echo $en ? 'active' : ''; ?>" id="mx-switch-en">🇬🇧 EN</a>
                    </div>
                    <button type="button" id="mx-outdoor-toggle" class="mx-outdoor-btn" onclick="mxToggleOutdoorMode()">
                        <?php echo $en ? '☀️ Outdoor' : '☀️ Ngoài trời'; ?>
                    </button>
                </div>
            </div>
            <h1 class="mx-hero-title">
                <?php echo s(local_mamxanhapp_t('Xin chào bà con, ', 'Welcome back, ') . fullname($USER) . '!'); ?>
            </h1>
            <p class="mx-hero-lead">
                <?php echo s(local_mamxanhapp_t(
                    'Hệ thống Tập huấn Kỹ thuật Canh tác Nông nghiệp Công nghệ cao — Video/PDF/H5P/SCORM 1.2, ghi nhật ký giọng nói & Chứng nhận số QR.',
                    'High-Tech Agriculture Training Platform — Video/PDF/H5P/SCORM 1.2, voice diary & secure QR certificates.'
                )); ?>
            </p>

            <!-- Overview Progress Metrics Bar -->
            <div class="mx-hero-stats-strip">
                <div class="mx-hero-stat-item">
                    <span>📚 <?php echo s(local_mamxanhapp_t('Khóa học:', 'Courses:')); ?></span>
                    <span class="num"><?php echo count($courseitems); ?></span>
                </div>
                <div class="mx-hero-stat-item">
                    <span>✅ <?php echo s(local_mamxanhapp_t('Đã học:', 'Done:')); ?></span>
                    <span class="num"><?php echo $totalcompletedmods . '/' . max(1, $totalmods); ?></span>
                </div>
                <div class="mx-hero-stat-item">
                    <span>🎯 <?php echo s(local_mamxanhapp_t('Điểm Quiz TB:', 'Avg Quiz:')); ?></span>
                    <span class="num"><?php echo s($avgquiz); ?></span>
                </div>
                <div class="mx-hero-stat-item">
                    <span>🎓 <?php echo s(local_mamxanhapp_t('Chứng nhận / Lô:', 'Certs / Lots:')); ?></span>
                    <span class="num"><?php echo (int)$certcount; ?> / <?php echo (int)$batchcount; ?></span>
                </div>
            </div>

            <!-- Action CTAs -->
            <div class="mx-hero-actions">
                <a href="<?php echo (new moodle_url('/local/agriops/index.php'))->out(false); ?>" class="mx-hero-btn mx-hero-btn-primary">
                    <span class="mx-hero-btn-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="9" y="2" width="6" height="12" rx="3"></rect><path d="M5 11a7 7 0 0 0 14 0"></path><line x1="12" y1="18" x2="12" y2="22"></line><line x1="8" y1="22" x2="16" y2="22"></line></svg>
                    </span>
                    <span><?php echo s(local_mamxanhapp_t('Ghi Nhật ký VietGAP', 'VietGAP Voice Diary')); ?></span>
                </a>
                <a href="<?php echo (new moodle_url('/local/mamxanhapp/calculator.php'))->out(false); ?>" class="mx-hero-btn mx-hero-btn-secondary">
                    <span class="mx-hero-btn-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="8" y1="11" x2="8.01" y2="11"></line><line x1="12" y1="11" x2="12.01" y2="11"></line><line x1="16" y1="11" x2="16.01" y2="11"></line><line x1="8" y1="15" x2="8.01" y2="15"></line><line x1="12" y1="15" x2="12.01" y2="15"></line><line x1="16" y1="15" x2="16.01" y2="15"></line></svg>
                    </span>
                    <span><?php echo s(local_mamxanhapp_t('Máy tính Pha phân & Chẩn đoán', 'Fertigation & Leaf AI')); ?></span>
                </a>
                <?php if ($roletype === 'cbkt' || $roletype === 'admin'): ?>
                <a href="<?php echo (new moodle_url('/local/mamxanhapp/manage.php'))->out(false); ?>" class="mx-hero-btn mx-hero-btn-warn">
                    <span class="mx-hero-btn-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    </span>
                    <span><?php echo s(local_mamxanhapp_t('Chuyển sang Quản lý CBKT', 'Instructor Analytics Hub')); ?></span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3D Glassmorphism Weather & Advisory Station -->
        <aside class="mx-weather-widget" aria-label="<?php echo s(local_mamxanhapp_t('Trạm Khí tượng & Cảnh báo Canh tác', 'Agri Weather & Spray Advisory Station')); ?>">
            <div>
                <div class="mx-weather-head">
                    <span class="mx-weather-badge">
                        <span style="font-size:1.6rem;">⛅</span>
                        <span><?php echo s($weather['badge']); ?></span>
                    </span>
                </div>
                <div class="mx-weather-meta mt-1">
                    <strong>📍 <?php echo s($weather['region']); ?></strong> • 💨 <?php echo s($weather['wind']); ?>
                </div>

                <div class="mx-weather-pills">
                    <span class="mx-weather-pill highlight">🌡️ <?php echo s($weather['temp']); ?></span>
                    <span class="mx-weather-pill">💧 <?php echo s($weather['humidity']); ?></span>
                    <span class="mx-weather-pill">📊 VPD <?php echo s($weather['vpd']); ?></span>
                </div>
            </div>

            <div>
                <p class="mx-weather-advice">
                    <?php echo s($weather['advice']); ?>
                </p>
                <div class="mx-weather-window">
                    ⏰ <?php echo s(local_mamxanhapp_t('Khung giờ vàng phun/tưới: ', 'Golden Spray/Drip Window: ') . $weather['window']); ?>
                </div>
            </div>
        </aside>
    </section>

    <!-- ============================================================= -->
    <!-- B. BENTO GRID - ASYMMETRIC ECOSYSTEM SUITE                    -->
    <!-- ============================================================= -->
    <section aria-labelledby="mx-bento-heading" class="mb-5">
        <div class="mx-section-header">
            <div>
                <h2 id="mx-bento-heading" class="mx-section-title">
                    ✨ <?php echo s(local_mamxanhapp_t('Hệ sinh thái Công cụ & Tiện ích Nông dân Số', 'Smart Digital Farmer Ecosystem & AgTech Suite')); ?>
                </h2>
                <div class="mx-section-subtitle">
                    <?php echo s(local_mamxanhapp_t('Bộ giải pháp canh tác thông minh hỗ trợ trực tiếp ngoài đồng ruộng & vườn ươm', 'Field-ready smart tools for fertigation, disease diagnosis, VietGAP logs, and digital certification')); ?>
                </div>
            </div>
        </div>

        <div class="mx-bento-grid">
            <!-- Bento Card 1 (Span 2x2): Fertigation & Leaf AI Diagnostics -->
            <article class="mx-bento-card mx-bento-span-2x2">
                <div>
                    <span class="mx-bento-tag">🧮 <?php echo s(local_mamxanhapp_t('Dinh dưỡng & Bảo vệ Thực vật', 'Fertigation & Plant Health')); ?></span>
                    <h3 class="mx-bento-title">
                        <?php echo s(local_mamxanhapp_t('Máy tính Pha phân Fertigation & Chẩn đoán Lá bệnh AI', 'Smart Fertigation Calculator & AI Leaf Doctor')); ?>
                    </h3>
                    <p class="mx-bento-desc">
                        <?php echo s(local_mamxanhapp_t(
                            'Tự động cân bằng dinh dưỡng khoáng đa - trung - vi lượng theo giai đoạn sinh trưởng cây trồng; phân tích hình ảnh và chẩn đoán tức thời 12 loại nấm bệnh, thiếu hụt dinh dưỡng qua triệu chứng lá.',
                            'Balance mineral fertilizers dynamically based on crop growth stages; instantly diagnose 12 fungal diseases and nutritional deficiencies via leaf symptom recognition.'
                        )); ?>
                    </p>

                    <!-- Interactive Formula & Diagnostic Preview -->
                    <div class="mx-bento-formula-box">
                        <div style="font-size:0.82rem;font-weight:8 thông;color:#1B4332;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.04em;">
                            📊 <?php echo s(local_mamxanhapp_t('Công thức dung dịch dinh dưỡng khuyến nghị:', 'Recommended Nutrient Solution Parameters:')); ?>
                        </div>
                        <div class="mx-bento-formula-pills">
                            <span class="mx-bento-formula-pill">⚡ EC: 1.4 – 2.0 mS/cm</span>
                            <span class="mx-bento-formula-pill">🧪 pH: 5.8 – 6.5</span>
                            <span class="mx-bento-formula-pill">⚖️ Tỷ lệ N:P:K:Ca:Mg = 3 : 1 : 4 : 2 : 1</span>
                        </div>
                        <div style="font-size:0.8rem;color:#475569;margin-bottom:6px;">
                            🌿 <?php echo s(local_mamxanhapp_t('Nhận diện triệu chứng bệnh phổ biến:', 'Fast symptom diagnosis:')); ?>
                        </div>
                        <div class="mx-bento-diag-pills">
                            <span class="mx-bento-diag-pill">🍂 Thiếu Canxi (Cháy mép)</span>
                            <span class="mx-bento-diag-pill">🍄 Sương mai (Phấn trắng)</span>
                            <span class="mx-bento-diag-pill">🌿 Thán thư (Đốm vòng)</span>
                            <span class="mx-bento-diag-pill">🦠 Thối rễ Pythium</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 pt-2">
                    <a href="<?php echo (new moodle_url('/local/mamxanhapp/calculator.php'))->out(false); ?>" class="btn btn-success" style="min-height:48px;display:inline-flex;align-items:center;gap:8px;border-radius:14px;font-weight:800;background:#1B4332;border-color:#1B4332;padding:0 20px;">
                        🧮 <?php echo s(local_mamxanhapp_t('Mở Máy tính & Chẩn đoán ngay →', 'Launch Calculator & AI Doctor →')); ?>
                    </a>
                </div>
            </article>

            <!-- Bento Card 2 (Span 2x1): VietGAP Voice Diary & Traceability -->
            <article class="mx-bento-card mx-bento-span-2x1">
                <div>
                    <span class="mx-bento-tag">🎙️ <?php echo s(local_mamxanhapp_t('VietGAP & Truy xuất Nguồn gốc', 'VietGAP & Traceability')); ?></span>
                    <h3 class="mx-bento-title">
                        <?php echo s(local_mamxanhapp_t('Nhật ký Canh tác Giọng nói & Mã Lô VietGAP', 'VietGAP Voice Diary & Batch Traceability')); ?>
                    </h3>
                    <p class="mx-bento-desc">
                        <?php echo s(local_mamxanhapp_t(
                            'Ghi chép phân bón, phun thuốc rảnh tay bằng giọng nói tiếng Việt; kết nối hệ sinh thái truy xuất nguồn gốc số hóa cho từng lô nông sản xuất khẩu.',
                            'Hands-free speech-to-text farming diary; instant QR batch traceability passport export compliant with VietGAP standards.'
                        )); ?>
                    </p>

                    <div class="mx-bento-status-box">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                            <span>📦 <strong>Lô gần nhất:</strong> #VG-LD-2026-08 (Rau xà lách thủy canh)</span>
                            <span style="background:#D8F3DC;color:#1B4332;padding:2px 8px;border-radius:999px;font-size:0.78rem;font-weight:800;">
                                🟢 Cách ly an toàn 7 ngày
                            </span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 pt-2">
                    <a href="<?php echo (new moodle_url('/local/agriops/index.php'))->out(false); ?>" class="btn btn-success" style="min-height:46px;display:inline-flex;align-items:center;gap:8px;border-radius:12px;font-weight:800;background:#1B4332;border-color:#1B4332;padding:0 18px;">
                        🎤 <?php echo s(local_mamxanhapp_t('Ghi Nhật ký Giọng nói', 'Voice Diary')); ?>
                    </a>
                    <a href="<?php echo (new moodle_url('/local/farmerprofile/index.php'))->out(false); ?>" class="btn btn-outline-success" style="min-height:46px;display:inline-flex;align-items:center;gap:8px;border-radius:12px;font-weight:800;padding:0 16px;">
                        🏡 <?php echo s(local_mamxanhapp_t('Hồ sơ Nông hộ & GPS', 'Farm Profile & GPS')); ?>
                    </a>
                </div>
            </article>

            <!-- Bento Card 3 (Span 1x1): QR Certs & Badges -->
            <article class="mx-bento-card mx-bento-span-1x1">
                <div>
                    <span class="mx-bento-tag">🎓 <?php echo s(local_mamxanhapp_t('Chứng nhận Số', 'Digital Certs')); ?></span>
                    <h3 class="mx-bento-title" style="font-size:1.15rem;">
                        <?php echo s(local_mamxanhapp_t('Chứng nhận Số QR', 'QR Certificates')); ?>
                    </h3>
                    <div style="font-size:2rem;font-weight:900;color:#1B4332;margin:4px 0 8px;">
                        <?php echo (int)$certcount; ?> <span style="font-size:0.95rem;color:#475569;font-weight:600;">/ <?php echo count($courseitems); ?></span>
                    </div>
                    <p style="font-size:0.86rem;color:#475569;line-height:1.45;margin-bottom:12px;">
                        <?php echo s(local_mamxanhapp_t('Ký mã số học HMAC-SHA256 chống làm giả, quét mã xác thực công khai.', 'Secured via HMAC-SHA256 signature with instant public QR verification.')); ?>
                    </p>
                </div>
                <a href="<?php echo (new moodle_url('/local/mamxanhcert/index.php'))->out(false); ?>" class="btn btn-outline-success w-100" style="min-height:44px;display:flex;align-items:center;justify-content:center;gap:6px;border-radius:12px;font-weight:800;font-size:0.9rem;">
                    📜 <?php echo s(local_mamxanhapp_t('Tra cứu Chứng chỉ →', 'View Certs →')); ?>
                </a>
            </article>

            <!-- Bento Card 4 (Span 1x1): AI Crop Doctor 24/7 -->
            <article class="mx-bento-card mx-bento-span-1x1">
                <div>
                    <span class="mx-bento-tag">🌱 <?php echo s(local_mamxanhapp_t('Trợ lý AI 24/7', 'AI Assistant')); ?></span>
                    <h3 class="mx-bento-title" style="font-size:1.15rem;">
                        <?php echo s(local_mamxanhapp_t('Bác sĩ Cây trồng & GPS', 'Crop Doctor & GPS')); ?>
                    </h3>
                    <p style="font-size:0.86rem;color:#475569;line-height:1.45;margin:6px 0 12px;">
                        <?php echo s(local_mamxanhapp_t('Cố vấn kỹ thuật số trực tuyến 24/7 kết hợp điểm danh QR xoay vòng & GPS tại vườn.', '24/7 on-demand digital agronomist combined with rotating QR & GPS field attendance.')); ?>
                    </p>
                </div>
                <button type="button" onclick="mxToggleCropDoctor()" class="btn btn-success w-100" style="min-height:44px;display:flex;align-items:center;justify-content:center;gap:6px;border-radius:12px;font-weight:800;background:#1B4332;border-color:#1B4332;font-size:0.9rem;">
                    💬 <?php echo s(local_mamxanhapp_t('Hỏi Bác sĩ ngay', 'Chat with Doctor')); ?>
                </button>
            </article>
        </div>
    </section>

    <!-- ============================================================= -->
    <!-- C. MASONRY LAYOUT - COURSES & TECHNICAL POCKET GUIDES         -->
    <!-- ============================================================= -->
    <section aria-labelledby="mx-courses-heading">
        <div class="mx-section-header">
            <div>
                <h2 id="mx-courses-heading" class="mx-section-title">
                    🌾 <?php echo s(local_mamxanhapp_t('Danh mục Khóa học Tập huấn & Cẩm nang Kỹ thuật Nông nghiệp', 'Digital Training Courses & Technical Quick Guides')); ?>
                </h2>
                <div class="mx-section-subtitle">
                    <?php echo s(local_mamxanhapp_t(
                        'Chương trình số hóa đa phương tiện chuẩn H5P, SCORM 1.2, thực hành vườn và bài viết nghiệp vụ bỏ túi',
                        'Modular interactive courses powered by H5P, SCORM 1.2, field practice, and quick pocket guides'
                    )); ?>
                </div>
            </div>
            <span style="font-size:0.88rem;color:#1B4332;font-weight:800;background:#E8F5E9;padding:6px 14px;border-radius:999px;">
                🎯 <?php echo s(local_mamxanhapp_t('Đạt >= 70% Quiz để mở khóa Chứng nhận QR', 'Score >= 70% on Quiz for QR Cert')); ?>
            </span>
        </div>

        <div class="mx-masonry-container">
            <?php
            // We intersperse courses with 4 technical quick pocket guides to achieve authentic, natural Masonry layout
            $courseidx = 0;
            foreach ($courseitems as $item):
                $courseidx++;
            ?>
                <!-- Course Item #<?php echo $courseidx; ?> -->
                <div class="mx-masonry-item">
                    <article class="mx-course-tile" style="background:#FFFFFF;border-radius:24px;border:2px solid #DCEEE0;padding:22px;box-shadow:0 6px 20px rgba(27,67,50,0.06);">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:12px;">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:14px;background:#E8F5E9;font-size:1.6rem;">
                                <?php echo $item->icon; ?>
                            </span>
                            <span class="mx-completion-badge" style="background:<?php echo $item->statusbg; ?>;color:<?php echo $item->statusfg; ?>;padding:6px 12px;border-radius:999px;font-size:0.82rem;font-weight:800;">
                                <?php echo s($item->statuslabel); ?>
                            </span>
                        </div>

                        <?php if ($item->shortname === 'MASTER-AGRITECH-TPL'): ?>
                        <div style="display:inline-block;background:#FEF3C7;color:#92400E;padding:3px 10px;border-radius:6px;font-size:0.75rem;font-weight:900;margin-bottom:6px;">
                            🏆 <?php echo s(local_mamxanhapp_t('KHÓA HỌC MẪU CHUẨN (MASTER TEMPLATE)', 'MASTER COURSE TEMPLATE')); ?>
                        </div>
                        <?php else: ?>
                        <div style="font-size:0.8rem;font-weight:700;color:#2E7D32;letter-spacing:0.04em;margin-bottom:4px;">
                            <?php echo s($item->shortname); ?>
                        </div>
                        <?php endif; ?>

                        <h3 style="font-size:1.15rem;font-weight:800;color:#14281D;margin-bottom:8px;line-height:1.38;">
                            <a href="<?php echo $item->courseurl; ?>" style="color:inherit;text-decoration:none;">
                                <?php echo $item->fullname; ?>
                            </a>
                        </h3>
                        <p style="font-size:0.9rem;color:#4E6358;line-height:1.55;margin-bottom:16px;">
                            <?php echo s($item->summary); ?>
                        </p>

                        <div>
                            <div style="display:flex;justify-content:space-between;font-size:0.84rem;font-weight:800;color:#2E7D32;margin-bottom:6px;">
                                <span><?php echo s(local_mamxanhapp_t('Tiến độ: ', 'Progress: ') . $item->cdone . '/' . $item->cmods); ?></span>
                                <span>
                                    <?php echo $item->quizpct !== null
                                        ? s(local_mamxanhapp_t('Điểm Quiz: ', 'Quiz: ') . $item->quizpct . '%')
                                        : s($item->progress . '%'); ?>
                                </span>
                            </div>
                            <div style="height:10px;background:#E8F5E9;border-radius:999px;overflow:hidden;margin-bottom:16px;">
                                <div style="height:100%;width:<?php echo max(4, $item->progress); ?>%;background:linear-gradient(90deg,#1B4332,#43A047);border-radius:999px;"></div>
                            </div>

                            <a href="<?php echo $item->courseurl; ?>" class="btn btn-success w-100 mx-enter-course-btn" style="min-height:50px;display:flex;align-items:center;justify-content:center;gap:8px;border-radius:14px;font-weight:800;font-size:0.98rem;background:#1B4332;border-color:#1B4332;">
                                <?php echo $item->progress > 0
                                    ? s(local_mamxanhapp_t('🚀 Tiếp tục học ngay', '🚀 Continue Learning'))
                                    : s(local_mamxanhapp_t('🌱 Vào học ngay', '🌱 Start Learning Now')); ?>
                            </a>
                        </div>
                    </article>
                </div>

                <?php if ($courseidx === 1): ?>
                <!-- Technical Pocket Article #1: Drip Irrigation Clog Prevention -->
                <div class="mx-masonry-item">
                    <article class="mx-article-card" style="background:#FFFDF5;border-color:#FDE68A;">
                        <span class="mx-article-tag" style="background:#FEF3C7;color:#92400E;">
                            🔧 <?php echo s(local_mamxanhapp_t('CẨM NANG VẬN HÀNH NHÀ MÀNG', 'GREENHOUSE OPERATIONS')); ?>
                        </span>
                        <h4 class="mx-article-title">
                            💡 <?php echo s(local_mamxanhapp_t('Mẹo xử lý nghẹt béc tưới nhỏ giọt & xơ cặn đường ống', 'Preventing Drip Emitter Clogging in Fertigation')); ?>
                        </h4>
                        <div class="mx-article-body">
                            <p style="margin-bottom:10px;">
                                <?php echo s(local_mamxanhapp_t(
                                    'Trong canh tác công nghệ cao, kết tủa Canxi Phosphate và màng vi sinh (Biofilm) là nguyên nhân gây nghẹt béc số một. Bà con áp dụng quy trình 3 bước định kỳ:',
                                    'Mineral precipitation and bacterial biofilm are the main causes of clogged emitters. Follow this 3-step maintenance rule:'
                                )); ?>
                            </p>
                            <ul style="padding-left:18px;margin-bottom:12px;line-height:1.6;">
                                <li><strong>1. Súc rửa Acid:</strong> Dùng dung dịch Acid Nitric (HNO3) 0.1% điều chỉnh pH nước xuống 4.0, ngâm trong hệ thống 60 phút để hòa tan muối khoáng.</li>
                                <li><strong>2. Xả cặn cuối hàng (Flush Valve):</strong> Định kỳ mỗi thứ 7 mở van cuối nhánh tống toàn bộ cặn lắng ra ngoài.</li>
                                <li><strong>3. Vệ sinh bộ lọc đĩa (Disc Filter 120 mesh):</strong> Rửa ngược ngay khi áp suất chênh lệch trước và sau lọc vượt quá 0.5 bar.</li>
                            </ul>
                            <div style="background:#FFFFFF;border:1px solid #FCD34D;border-radius:12px;padding:10px 12px;font-size:0.82rem;font-weight:700;color:#92400E;">
                                ⚠️ <em>Lưu ý: Không bao giờ hòa chung phân bón gốc Canxi (Canxi Nitrat) với phân gốc Lân hoặc Lưu huỳnh trong cùng một bồn mẹ A.</em>
                            </div>
                        </div>
                    </article>
                </div>
                <?php endif; ?>

                <?php if ($courseidx === 2): ?>
                <!-- Technical Pocket Article #2: pH Quick Reference Table -->
                <div class="mx-masonry-item">
                    <article class="mx-article-card" style="background:#F6FBF8;border-color:#BBF7D0;">
                        <span class="mx-article-tag" style="background:#DCFCE7;color:#166534;">
                            🌱 <?php echo s(local_mamxanhapp_t('BẢNG TRA CỨU NÔNG HỌC', 'AGRONOMY QUICK REFERENCE')); ?>
                        </span>
                        <h4 class="mx-article-title">
                            📊 <?php echo s(local_mamxanhapp_t('Bảng tra cứu độ pH & EC chuẩn cho cây trồng phổ biến', 'Ideal pH & EC Guide for High-Tech Crops')); ?>
                        </h4>
                        <div class="mx-article-body">
                            <table class="mx-article-table">
                                <thead>
                                    <tr>
                                        <th>Cây trồng</th>
                                        <th>Độ pH tối ưu</th>
                                        <th>Độ dẫn điện (EC)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Xà lách thủy canh</strong></td>
                                        <td>5.8 – 6.5</td>
                                        <td>1.4 – 1.8 mS/cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dâu tây nhà màng</strong></td>
                                        <td>5.5 – 6.2</td>
                                        <td>1.2 – 1.5 mS/cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Ớt chuông ngọt</strong></td>
                                        <td>6.0 – 6.5</td>
                                        <td>2.0 – 2.5 mS/cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cà chua beef / cherry</strong></td>
                                        <td>5.8 – 6.5</td>
                                        <td>2.2 – 2.8 mS/cm</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div style="margin-top:10px;font-size:0.84rem;color:#1B4332;">
                                💡 <strong>Giải pháp cân bằng pH:</strong> Nếu pH đất &lt; 5.2 (chua), bón lót 120-150 kg vôi Dolomite/1.000 m² trước khi trồng 14 ngày.
                            </div>
                        </div>
                    </article>
                </div>
                <?php endif; ?>

                <?php if ($courseidx === 3): ?>
                <!-- Technical Pocket Article #3: Trichoderma Biological Defense -->
                <div class="mx-masonry-item">
                    <article class="mx-article-card" style="background:#F0FDF4;border-color:#A7F3D0;">
                        <span class="mx-article-tag" style="background:#D1FAE5;color:#065F46;">
                            🦠 <?php echo s(local_mamxanhapp_t('BẢO VỆ THỰC VẬT SINH HỌC', 'BIOLOGICAL PEST CONTROL')); ?>
                        </span>
                        <h4 class="mx-article-title">
                            🛡️ <?php echo s(local_mamxanhapp_t('Quy tắc "4 Đúng" khi sử dụng nấm đối kháng Trichoderma', 'The 4-Right Rules for Trichoderma Bio-Fungicide')); ?>
                        </h4>
                        <div class="mx-article-body">
                            <p style="margin-bottom:8px;">
                                <?php echo s(local_mamxanhapp_t(
                                    'Trichoderma là "vũ khí sinh học" đối kháng nấm Fusarium, Pythium, Rhizoctonia gây thối rễ. Cần tuân thủ 4 nguyên tắc sống còn:',
                                    'Trichoderma is a key biological control agent against soil-borne pathogens. Adhere to these essential rules:'
                                )); ?>
                            </p>
                            <ul style="padding-left:18px;margin-bottom:10px;line-height:1.6;">
                                <li><strong>Đúng thời điểm:</strong> Phun hoặc tưới gốc lúc trời râm mát (sau 16:30 chiều). Tia UV trong nắng gắt sẽ tiêu diệt bào tử sống trong 2 giờ.</li>
                                <li><strong>Đúng độ ẩm:</strong> Độ ẩm đất phải đạt 65–75%. Nếu đất quá khô, bào tử nấm sẽ rơi vào trạng thái ngủ và chết đói.</li>
                                <li><strong>Đúng cách ly:</strong> KHÔNG hòa chung hoặc phun thuốc trừ nấm hóa học (gốc Đồng, Mancozeb, Ridomil) trước hoặc sau 7 ngày.</li>
                                <li><strong>Đúng thức ăn:</strong> Bón kèm phân hữu cơ hoai mục để cung cấp thức ăn ban đầu cho nấm nhân sinh khối.</li>
                            </ul>
                        </div>
                    </article>
                </div>
                <?php endif; ?>

                <?php if ($courseidx === 4): ?>
                <!-- Technical Pocket Article #4: VietGAP Organic Compost Guide -->
                <div class="mx-masonry-item">
                    <article class="mx-article-card" style="background:#FFFBEB;border-color:#FDE68A;">
                        <span class="mx-article-tag" style="background:#FEF3C7;color:#92400E;">
                            ♻️ <?php echo s(local_mamxanhapp_t('TIÊU CHUẨN VIETGAP COMPOST', 'VIETGAP COMPOSTING GUIDE')); ?>
                        </span>
                        <h4 class="mx-article-title">
                            📦 <?php echo s(local_mamxanhapp_t('Quy trình ủ phân hữu cơ compost đạt chuẩn VietGAP trong 45 ngày', '45-Day High-Quality VietGAP Organic Composting')); ?>
                        </h4>
                        <div class="mx-article-body">
                            <p style="margin-bottom:8px;">
                                Tận dụng phế phẩm nông nghiệp (vỏ cà phê, rơm rạ, xác cây họ đậu) ủ thành phân hữu cơ giàu mùn với các mốc kiểm soát:
                            </p>
                            <ul style="padding-left:18px;margin-bottom:10px;line-height:1.6;">
                                <li><strong>Tỷ lệ C/N lý tưởng:</strong> 30 phần vật liệu giàu Carbon (nâu) phối trộn 1 phần đạm hữu cơ (phân chuồng, rau xanh).</li>
                                <li><strong>Kiểm soát nhiệt:</strong> Duy trì nhiệt độ đống ủ 55–65°C trong ít nhất 72 giờ liên tục để diệt trừ mầm bệnh và hạt cỏ dại.</li>
                                <li><strong>Lịch đảo đống:</strong> Đảo thoáng khí vào các ngày thứ 7, 15, 30. Độ ẩm duy trì 50–55% (nắm chặt tay không rỉ nước).</li>
                            </ul>
                        </div>
                    </article>
                </div>
                <?php endif; ?>

            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php
echo $OUTPUT->footer();
