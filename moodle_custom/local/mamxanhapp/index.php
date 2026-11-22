<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/completionlib.php');
if (file_exists($CFG->dirroot . '/local/agriops/lib.php')) {
    require_once($CFG->dirroot . '/local/agriops/lib.php');
}

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
$PAGE->set_heading(local_mamxanhapp_t('Không gian Tập huấn Nông dân Số', 'Digital Farmer Training Hub'));

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
        $statuslabel = local_mamxanhapp_t('Đã hoàn thành', 'Completed');
        $statusbg = '#EAF2EE';
        $statusfg = '#234E3E';
    } else if ($progress > 0 || $bestquizpct !== null) {
        $statuskey = 'learning';
        $statuslabel = local_mamxanhapp_t('Đang học (' . $progress . '%)', 'In Progress (' . $progress . '%)');
        $statusbg = '#FFF8E1';
        $statusfg = '#E65100';
    } else {
        $statuskey = 'new';
        $statuslabel = local_mamxanhapp_t('Chưa học', 'Not Started');
        $statusbg = '#ECEFF1';
        $statusfg = '#37474F';
    }

    $icon = '';
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
        'transcripturl' => (new moodle_url('/local/mamxanhapp/transcript.php', ['courseid' => $course->id]))->out(false),
        'feedbackurl'   => (new moodle_url('/local/mamxanhapp/feedback_sheet.php', ['courseid' => $course->id]))->out(false),
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
    <header class="mx-page-head">
        <div>
            <h1 class="mx-page-title"><?php echo s(local_mamxanhapp_t('Khóa học của tôi', 'My courses')); ?></h1>
            <p class="mx-page-sub">
                <?php echo s(local_mamxanhapp_t('Xin chào, ', 'Welcome, ') . fullname($USER)); ?>
                <?php if ($farmname !== ''):
                    $regiondisp = $region;
                    if ($en) {
                        $rmap = ['Lâm Đồng' => 'Lam Dong', 'Đắk Lắk' => 'Dak Lak', 'Đồng Nai' => 'Dong Nai'];
                        $regiondisp = $rmap[$region] ?? $region;
                    }
                ?>
                    <span class="mx-sep">/</span> <?php echo s($farmname); ?>, <?php echo s($regiondisp); ?> (<?php echo number_format($aream2); ?> m²)
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo (new moodle_url('/local/mamxanhapp/transcript.php'))->out(false); ?>" class="mx-btn mx-btn-outline">
                <?php echo s(local_mamxanhapp_t('Bảng điểm Học thuật', 'Academic Transcript')); ?>
            </a>
            <a href="<?php echo (new moodle_url('/local/mamxanhapp/feedback_sheet.php'))->out(false); ?>" class="mx-btn mx-btn-outline">
                <?php echo s(local_mamxanhapp_t('Phiếu Rubric & Nhận xét', 'Rubric Feedback Sheet')); ?>
            </a>
            <?php if ($roletype === 'cbkt' || $roletype === 'admin'): ?>
            <a href="<?php echo (new moodle_url('/local/mamxanhapp/manage.php'))->out(false); ?>" class="mx-btn mx-btn-outline">
                <?php echo s(local_mamxanhapp_t('Trang quản lý giảng viên', 'Instructor hub')); ?>
            </a>
            <?php endif; ?>
        </div>
    </header>

    <section class="mx-summary" aria-label="<?php echo s(local_mamxanhapp_t('Tổng quan học tập', 'Learning summary')); ?>">
        <div class="mx-summary-item">
            <span class="mx-summary-num"><?php echo count($courseitems); ?></span>
            <span class="mx-summary-label"><?php echo s(local_mamxanhapp_t('Khóa học', 'Courses')); ?></span>
        </div>
        <div class="mx-summary-item">
            <span class="mx-summary-num"><?php echo $totalcompletedmods . '/' . max(1, $totalmods); ?></span>
            <span class="mx-summary-label"><?php echo s(local_mamxanhapp_t('Hoạt động đã hoàn thành', 'Activities completed')); ?></span>
        </div>
        <div class="mx-summary-item">
            <span class="mx-summary-num"><?php echo s($avgquiz); ?></span>
            <span class="mx-summary-label"><?php echo s(local_mamxanhapp_t('Điểm kiểm tra trung bình', 'Average quiz score')); ?></span>
        </div>
        <div class="mx-summary-item">
            <span class="mx-summary-num"><?php echo (int)$certcount; ?></span>
            <span class="mx-summary-label"><?php echo s(local_mamxanhapp_t('Chứng nhận', 'Certificates')); ?></span>
        </div>
    </section>

    <?php
    if (function_exists('local_agriops_render_phi_widget_html')) {
        echo local_agriops_render_phi_widget_html($userid, $en);
    }
    ?>

    <section aria-labelledby="mx-courses-heading">
        <h2 id="mx-courses-heading" class="mx-section-title"><?php echo s(local_mamxanhapp_t('Danh sách khóa học', 'Course list')); ?></h2>
        <div class="mx-course-grid">
            <?php foreach ($courseitems as $item): ?>
            <article class="mx-course-tile">
                <div class="mx-course-cover" aria-hidden="true">
                    <span><?php echo s(core_text::strtoupper(core_text::substr($item->shortname, 0, 2))); ?></span>
                </div>
                <div class="mx-course-body">
                    <div class="mx-course-code">
                        <?php echo $item->shortname === 'MASTER-AGRITECH-TPL'
                            ? s(local_mamxanhapp_t('Khóa học mẫu', 'Master template'))
                            : s($item->shortname); ?>
                    </div>
                    <h3 class="mx-course-name">
                        <a href="<?php echo $item->courseurl; ?>"><?php echo $item->fullname; ?></a>
                    </h3>
                    <p class="mx-course-summary"><?php echo s($item->summary); ?></p>
                    <div class="mx-course-progress">
                        <div class="mx-bar"><div class="mx-bar-fill" style="width:<?php echo (int)$item->progress; ?>%;"></div></div>
                        <div class="mx-course-progress-meta">
                            <span><?php echo (int)$item->progress; ?>% <?php echo s(local_mamxanhapp_t('hoàn thành', 'complete')); ?></span>
                            <span><?php echo (int)$item->cdone . '/' . (int)$item->cmods; ?></span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <a href="<?php echo $item->courseurl; ?>" class="mx-btn mx-btn-primary mx-enter-course-btn">
                            <?php echo $item->progress > 0
                                ? s(local_mamxanhapp_t('Tiếp tục học', 'Continue'))
                                : s(local_mamxanhapp_t('Bắt đầu học', 'Start')); ?>
                        </a>
                        <a href="<?php echo $item->transcripturl; ?>" class="btn btn-sm btn-secondary">
                            <?php echo s(local_mamxanhapp_t('Bảng điểm', 'Transcript')); ?>
                        </a>
                        <a href="<?php echo $item->feedbackurl; ?>" class="btn btn-sm btn-secondary">
                            <?php echo s(local_mamxanhapp_t('Phiếu Rubric', 'Rubric')); ?>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php
echo $OUTPUT->footer();
