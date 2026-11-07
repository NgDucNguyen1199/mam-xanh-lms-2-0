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

echo $OUTPUT->header();
?>

<div class="mx-hub-container">
    <!-- Hero Farmer Greeting & Micro-climate Weather Station -->
    <section class="mx-hero-banner" style="background:linear-gradient(135deg,#1B4332 0%,#2E7D32 100%);color:#FFFFFF;border-radius:24px;padding:24px;margin-bottom:20px;box-shadow:0 14px 32px rgba(27,67,50,0.18);">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-3 mb-lg-0">
                <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.16);padding:6px 14px;border-radius:999px;font-size:0.88rem;font-weight:700;margin-bottom:10px;">
                    <span>🏡 <?php echo s($farmname); ?></span>
                    <span>•</span>
                    <span>📍 <?php echo s($region); ?> (<?php echo number_format($aream2); ?> m²)</span>
                </div>
                <h2 style="color:#FFFFFF;font-weight:800;font-size:1.65rem;margin-bottom:8px;">
                    <?php echo s(local_mamxanhapp_t('Xin chào bà con, ', 'Welcome back, ') . fullname($USER) . '!'); ?>
                </h2>
                <p style="color:#D8F3DC;margin-bottom:14px;font-size:1rem;line-height:1.55;">
                    <?php echo s(local_mamxanhapp_t(
                        'Học kỹ thuật canh tác thực tiễn bằng hình ảnh, giọng đọc rảnh tay, ghi nhật ký giọng nói VietGAP và nhận Chứng nhận số QR ngay trên điện thoại.',
                        'Master high-tech farming with interactive visuals, hands-free voice reader, VietGAP voice diary, and digital QR certificates.'
                    )); ?>
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    <a href="<?php echo (new moodle_url('/local/agriops/index.php'))->out(false); ?>" class="btn btn-light" style="min-height:48px;display:inline-flex;align-items:center;gap:8px;border-radius:14px;font-weight:700;color:#1B4332;padding:0 18px;">
                        🎤 <?php echo s(local_mamxanhapp_t('Ghi Nhật ký VietGAP', 'VietGAP Voice Diary')); ?>
                    </a>
                    <a href="<?php echo (new moodle_url('/local/mamxanhapp/calculator.php'))->out(false); ?>" class="btn btn-outline-light" style="min-height:48px;display:inline-flex;align-items:center;gap:8px;border-radius:14px;font-weight:700;padding:0 18px;border-width:2px;">
                        🧮 <?php echo s(local_mamxanhapp_t('Máy tính Pha phân & Chẩn đoán', 'Fertigation & Leaf Doctor')); ?>
                    </a>
                    <?php if ($roletype === 'cbkt' || $roletype === 'admin'): ?>
                    <a href="<?php echo (new moodle_url('/local/mamxanhapp/manage.php'))->out(false); ?>" class="btn btn-warning" style="min-height:48px;display:inline-flex;align-items:center;gap:8px;border-radius:14px;font-weight:800;color:#1B4332;padding:0 18px;">
                        📊 <?php echo s(local_mamxanhapp_t('Chuyển sang Quản lý CBKT', 'Instructor Analytics Hub')); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="mx-weather-widget" style="background:rgba(255,255,255,0.13);backdrop-filter:blur(8px);border:1.5px solid rgba(255,255,255,0.28);border-radius:20px;padding:18px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;flex-wrap:wrap;gap:6px;">
                        <span style="font-weight:800;font-size:0.95rem;color:#FFF;"><?php echo s($weather['badge']); ?></span>
                        <span style="background:#FFD54F;color:#1B4332;font-weight:800;padding:3px 10px;border-radius:999px;font-size:0.82rem;">
                            🌡️ <?php echo s($weather['temp']); ?> | 💧 <?php echo s($weather['humidity']); ?>
                        </span>
                    </div>
                    <div style="font-size:0.86rem;color:#E8F5E9;margin-bottom:8px;">
                        <strong><?php echo s($weather['region']); ?></strong> • 💨 <?php echo s($weather['wind']); ?> • VPD: <?php echo s($weather['vpd']); ?>
                    </div>
                    <p style="font-size:0.88rem;color:#FFFFFF;margin:0 0 8px;line-height:1.45;">
                        <?php echo s($weather['advice']); ?>
                    </p>
                    <div style="font-size:0.82rem;color:#B7E4C7;font-weight:700;">
                        ⏰ <?php echo s(local_mamxanhapp_t('Khung giờ phun/tưới tối ưu: ', 'Optimal Spray/Drip Window: ') . $weather['window']); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4 KPI Summary Cards -->
    <section class="row mb-4" aria-label="KPI">
        <div class="col-6 col-md-3 mb-3">
            <div class="mx-kpi-card" style="background:#FFFFFF;border-radius:20px;padding:18px;border:2px solid #E0F2F1;height:100%;box-shadow:0 4px 14px rgba(0,0,0,0.04);">
                <div style="font-size:1.6rem;margin-bottom:4px;">📚</div>
                <div style="font-size:1.6rem;font-weight:800;color:#1B4332;"><?php echo count($courseitems); ?></div>
                <div style="font-size:0.88rem;color:#455A64;font-weight:600;"><?php echo s(local_mamxanhapp_t('Khóa tập huấn số', 'Digital Courses')); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="mx-kpi-card" style="background:#FFFFFF;border-radius:20px;padding:18px;border:2px solid #E0F2F1;height:100%;box-shadow:0 4px 14px rgba(0,0,0,0.04);">
                <div style="font-size:1.6rem;margin-bottom:4px;">✅</div>
                <div style="font-size:1.6rem;font-weight:800;color:#2E7D32;"><?php echo $totalcompletedmods . '/' . max(1, $totalmods); ?></div>
                <div style="font-size:0.88rem;color:#455A64;font-weight:600;"><?php echo s(local_mamxanhapp_t('Chuyên đề đã học', 'Activities Done')); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="mx-kpi-card" style="background:#FFFFFF;border-radius:20px;padding:18px;border:2px solid #E0F2F1;height:100%;box-shadow:0 4px 14px rgba(0,0,0,0.04);">
                <div style="font-size:1.6rem;margin-bottom:4px;">🎯</div>
                <div style="font-size:1.6rem;font-weight:800;color:#1B4332;"><?php echo s($avgquiz); ?></div>
                <div style="font-size:0.88rem;color:#455A64;font-weight:600;"><?php echo s(local_mamxanhapp_t('Điểm Quiz TB', 'Avg Quiz Score')); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="mx-kpi-card" style="background:#FFFFFF;border-radius:20px;padding:18px;border:2px solid #E0F2F1;height:100%;box-shadow:0 4px 14px rgba(0,0,0,0.04);">
                <div style="font-size:1.6rem;margin-bottom:4px;">🎓</div>
                <div style="font-size:1.6rem;font-weight:800;color:#2E7D32;"><?php echo (int)$certcount; ?> / <?php echo (int)$batchcount; ?></div>
                <div style="font-size:0.88rem;color:#455A64;font-weight:600;"><?php echo s(local_mamxanhapp_t('Chứng nhận / Lô VietGAP', 'Certs / VietGAP Lots')); ?></div>
            </div>
        </div>
    </section>

    <!-- Quick AgTech Ecosystem Actions -->
    <section class="row mb-4">
        <div class="col-6 col-lg-3 mb-3">
            <a href="<?php echo (new moodle_url('/local/farmerprofile/index.php'))->out(false); ?>" class="mx-quick-tile" style="display:flex;align-items:center;gap:12px;background:#FFFFFF;border:2px solid #C8E6C9;border-radius:18px;padding:14px 16px;min-height:68px;text-decoration:none;color:#1B4332;font-weight:700;">
                <span style="font-size:1.7rem;">🏡</span>
                <span><?php echo s(local_mamxanhapp_t('Hồ sơ Nông hộ & GPS', 'Farm Profile & GPS')); ?></span>
            </a>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <a href="<?php echo (new moodle_url('/local/agriops/index.php'))->out(false); ?>" class="mx-quick-tile" style="display:flex;align-items:center;gap:12px;background:#FFFFFF;border:2px solid #C8E6C9;border-radius:18px;padding:14px 16px;min-height:68px;text-decoration:none;color:#1B4332;font-weight:700;">
                <span style="font-size:1.7rem;">📦</span>
                <span><?php echo s(local_mamxanhapp_t('Lô VietGAP & Truy xuất QR', 'VietGAP Lots & QR')); ?></span>
            </a>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <a href="<?php echo (new moodle_url('/local/mamxanhapp/calculator.php'))->out(false); ?>" class="mx-quick-tile" style="display:flex;align-items:center;gap:12px;background:#FFFFFF;border:2px solid #C8E6C9;border-radius:18px;padding:14px 16px;min-height:68px;text-decoration:none;color:#1B4332;font-weight:700;">
                <span style="font-size:1.7rem;">🧮</span>
                <span><?php echo s(local_mamxanhapp_t('Pha Phân & Chẩn đoán Lá', 'Fertigation & Leaf AI')); ?></span>
            </a>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <a href="<?php echo (new moodle_url('/local/mamxanhcert/index.php'))->out(false); ?>" class="mx-quick-tile" style="display:flex;align-items:center;gap:12px;background:#FFFFFF;border:2px solid #C8E6C9;border-radius:18px;padding:14px 16px;min-height:68px;text-decoration:none;color:#1B4332;font-weight:700;">
                <span style="font-size:1.7rem;">📜</span>
                <span><?php echo s(local_mamxanhapp_t('Chứng nhận Số QR (' . $certcount . ')', 'Digital QR Certs (' . $certcount . ')')); ?></span>
            </a>
        </div>
    </section>

    <!-- Course Tiles Grid -->
    <section aria-labelledby="mx-courses-heading">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
            <h3 id="mx-courses-heading" style="color:#1B4332;font-weight:800;margin:0;font-size:1.35rem;">
                🌾 <?php echo s(local_mamxanhapp_t('Chương trình Tập huấn Kỹ thuật Số hóa (6 Khóa học)', 'Interactive Digital Training Courses (6 Modules)')); ?>
            </h3>
            <span style="font-size:0.9rem;color:#2E7D32;font-weight:700;">
                <?php echo s(local_mamxanhapp_t('Đạt >= 70% bài kiểm tra để mở khóa Chứng nhận QR', 'Score >= 70% on Quiz to unlock QR Certificate')); ?>
            </span>
        </div>

        <div class="row">
            <?php foreach ($courseitems as $item): ?>
            <div class="col-12 col-md-6 col-xl-4 mb-4">
                <article class="mx-course-tile" style="background:#FFFFFF;border-radius:22px;border:2px solid #DCEEE0;padding:20px;height:100%;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 8px 22px rgba(27,67,50,0.06);">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:12px;">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:14px;background:#E8F5E9;font-size:1.6rem;">
                                <?php echo $item->icon; ?>
                            </span>
                            <span class="mx-completion-badge" style="background:<?php echo $item->statusbg; ?>;color:<?php echo $item->statusfg; ?>;padding:6px 12px;border-radius:999px;font-size:0.83rem;font-weight:800;">
                                <?php echo s($item->statuslabel); ?>
                            </span>
                        </div>

                        <div style="font-size:0.8rem;font-weight:700;color:#2E7D32;letter-spacing:0.04em;margin-bottom:4px;">
                            <?php echo s($item->shortname); ?>
                        </div>
                        <h4 style="font-size:1.12rem;font-weight:800;color:#14281D;margin-bottom:8px;line-height:1.38;">
                            <a href="<?php echo $item->courseurl; ?>" style="color:inherit;text-decoration:none;">
                                <?php echo $item->fullname; ?>
                            </a>
                        </h4>
                        <p style="font-size:0.9rem;color:#4E6358;line-height:1.5;margin-bottom:14px;">
                            <?php echo s($item->summary); ?>
                        </p>
                    </div>

                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:0.84rem;font-weight:700;color:#2E7D32;margin-bottom:6px;">
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

                        <a href="<?php echo $item->courseurl; ?>" class="btn btn-success w-100 mx-enter-course-btn" style="min-height:50px;display:flex;align-items:center;justify-content:center;gap:8px;border-radius:14px;font-weight:800;font-size:1rem;background:#1B4332;border-color:#1B4332;">
                            <?php echo $item->progress > 0
                                ? s(local_mamxanhapp_t('🚀 Tiếp tục học ngay', '🚀 Continue Learning'))
                                : s(local_mamxanhapp_t('🌱 Vào học ngay', '🌱 Start Learning Now')); ?>
                        </a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php
echo $OUTPUT->footer();
