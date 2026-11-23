<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/completionlib.php');
if (file_exists($CFG->dirroot . '/local/mamxanhcert/lib.php')) {
    require_once($CFG->dirroot . '/local/mamxanhcert/lib.php');
}

local_mamxanhapp_handle_lang_switch();
require_login();

if (isguestuser()) {
    redirect(new moodle_url('/login/index.php'));
}

$syscontext = context_system::instance();
$roletype = local_mamxanhapp_user_role_type();
$iscbktadmin = ($roletype === 'cbkt' || $roletype === 'admin' || is_siteadmin());

$courseid = optional_param('courseid', 2, PARAM_INT);
$targetuserid = optional_param('userid', (int)$USER->id, PARAM_INT);
if (!$iscbktadmin) {
    $targetuserid = (int)$USER->id;
}

// Load available courses (exclude hidden templates and junk)
$courses = $DB->get_records_select(
    'course',
    'id <> :siteid AND visible = 1',
    ['siteid' => SITEID],
    'id ASC',
    'id,shortname,fullname,summary'
);
if (empty($courses)) {
    $courses = $DB->get_records_select('course', 'id <> :siteid', ['siteid' => SITEID], 'id ASC', 'id,shortname,fullname,summary');
}
if (!isset($courses[$courseid])) {
    $firstc = reset($courses);
    $courseid = $firstc ? (int)$firstc->id : 2;
}
$course = $courses[$courseid] ?? get_course($courseid);

// Load learner user record
$learner = $DB->get_record('user', ['id' => $targetuserid, 'deleted' => 0]);
if (!$learner) {
    $learner = $USER;
    $targetuserid = (int)$USER->id;
}

// Load all learners for CBKT/Admin selector
$learners = [];
if ($iscbktadmin) {
    $learners = $DB->get_records_sql("
        SELECT id, username, firstname, lastname, email
          FROM {user}
         WHERE deleted = 0 AND suspended = 0
           AND (username LIKE 'nd_%' OR id = :uid)
      ORDER BY username ASC
    ", ['uid' => $targetuserid]);
}

$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/mamxanhapp/transcript.php', ['courseid' => $courseid, 'userid' => $targetuserid]));
$PAGE->set_pagelayout('standard');

$en = local_mamxanhapp_is_en();
$title = local_mamxanhapp_t(
    'Bảng điểm Học thuật Tổng hợp - ' . format_string($course->shortname),
    'Comprehensive Academic Transcript - ' . format_string($course->shortname)
);
$PAGE->set_title($title);
$PAGE->set_heading(local_mamxanhapp_t('Bảng điểm Học thuật Tổng hợp & Xác nhận Đào tạo', 'Comprehensive Academic Transcript & Training Verification'));

// Load farmer household profile
$profile = $DB->get_record('local_farmerprofile', ['userid' => $targetuserid]);
$farmname = ($profile && !empty($profile->farm_name))
    ? $profile->farm_name
    : local_mamxanhapp_t('Chưa khai báo hồ sơ nông hộ', 'Farm profile not configured');
$region = ($profile && !empty($profile->region)) ? $profile->region : ($learner->city ?: 'Lâm Đồng');
if ($en) {
    $rmap = ['Lâm Đồng' => 'Lam Dong', 'Đắk Lắk' => 'Dak Lak', 'Đồng Nai' => 'Dong Nai'];
    $region = $rmap[$region] ?? $region;
}
$aream2 = $profile ? (int)$profile->area_m2 : 0;
$certstd = ($profile && !empty($profile->cert_standard)) ? $profile->cert_standard : 'VietGAP TCVN 11892-1:2017';

if (function_exists('local_mamxanhcert_sync_user_certificates')) {
    local_mamxanhcert_sync_user_certificates($targetuserid);
}
$eligibility = function_exists('local_mamxanhcert_evaluate_eligibility')
    ? local_mamxanhcert_evaluate_eligibility($targetuserid, $courseid)
    : null;

// =========================================================================
// COMPONENT 1: ATTENDANCE (Điểm danh Lý thuyết & Thực hành) — Weight: 10%
// =========================================================================
$attmodules = $DB->get_records('attendance', ['course' => $courseid], 'id ASC');
$attsessionscount = 0;
$attpresentcount = 0;
$attdetails = [];

foreach ($attmodules as $att) {
    $sessions = $DB->get_records('attendance_sessions', ['attendanceid' => $att->id], 'sessdate ASC');
    $modtaken = 0;
    $modpresent = 0;
    foreach ($sessions as $sess) {
        $istaken = ((int)($sess->lasttaken ?? 0) > 0) || $DB->record_exists('attendance_log', ['sessionid' => $sess->id]);
        if (!$istaken) {
            continue;
        }
        $modtaken++;
        $attsessionscount++;
        $log = $DB->get_record('attendance_log', ['sessionid' => $sess->id, 'studentid' => $targetuserid]);
        if ($log) {
            $st = $DB->get_record('attendance_statuses', ['id' => $log->statusid]);
            if ($st && (float)$st->grade > 0) {
                $modpresent++;
                $attpresentcount++;
            }
        }
    }
    if ($modtaken > 0) {
        $attdetails[] = format_string($att->name, true, ['escape' => false]) . ': ' . $modpresent . '/' . $modtaken . ($en ? ' taken sessions' : ' buổi đã điểm danh');
    }
}
$attpct = $attsessionscount > 0 ? round(($attpresentcount / $attsessionscount) * 100.0, 1) : 0.0;
$attscore10 = round($attpct / 10.0, 2);
$attdetailstr = !empty($attdetails)
    ? implode(' • ', $attdetails)
    : local_mamxanhapp_t('Chưa có buổi điểm danh nào được thực hiện', 'No attendance sessions taken yet');

// =========================================================================
// COMPONENT 2: DISCUSSION ASSIGNMENTS (2 Bài Thảo luận) — Weight: 20%
// =========================================================================
$assigns = $DB->get_records('assign', ['course' => $courseid], 'id ASC');
$discscores = [];
$discdetails = [];
$capstoneassign = null;

foreach ($assigns as $asg) {
    $lowname = core_text::strtolower($asg->name);
    $isdisc = (strpos($lowname, 'thảo luận') !== false || strpos($lowname, 'discussion') !== false);
    if ($isdisc) {
        $ag = $DB->get_record('assign_grades', ['assignment' => $asg->id, 'userid' => $targetuserid]);
        $maxg = (float)$asg->grade > 0 ? (float)$asg->grade : 100.0;
        if ($ag && $ag->grade !== null && (float)$ag->grade >= 0) {
            $pct = min(100.0, round(((float)$ag->grade / $maxg) * 100.0, 1));
            $discscores[] = $pct;
            $discdetails[] = format_string($asg->name, true, ['escape' => false]) . ' (' . round($pct / 10.0, 1) . '/10)';
        } else {
            $discdetails[] = format_string($asg->name, true, ['escape' => false]) . ' (' . local_mamxanhapp_t('Chưa chấm', 'Not graded') . ')';
        }
    } else {
        $capstoneassign = $asg;
    }
}
$discpct = !empty($discscores) ? round(array_sum($discscores) / count($discscores), 1) : 0.0;
$discscore10 = round($discpct / 10.0, 2);
$discdetailstr = !empty($discdetails)
    ? implode(' • ', $discdetails)
    : local_mamxanhapp_t('Chưa có điểm bài tập thảo luận', 'No graded discussion assignments yet');

// =========================================================================
// COMPONENT 3: INTERACTIVE H5P & SCORM 1.2 SIMULATION — Weight: 20%
// =========================================================================
$h5precs = $DB->get_records('h5pactivity', ['course' => $courseid], 'id ASC');
$scormrecs = $DB->get_records('scorm', ['course' => $courseid], 'id ASC');
$interactscores = [];
$interactdetails = [];

foreach ($h5precs as $h5p) {
    $gi = $DB->get_record('grade_items', ['courseid' => $courseid, 'itemmodule' => 'h5pactivity', 'iteminstance' => $h5p->id]);
    $pct = null;
    if ($gi) {
        $gg = $DB->get_record('grade_grades', ['itemid' => $gi->id, 'userid' => $targetuserid]);
        if ($gg && $gg->finalgrade !== null && (float)$gi->grademax > 0) {
            $pct = min(100.0, round(((float)$gg->finalgrade / (float)$gi->grademax) * 100.0, 1));
        }
    }
    if ($pct !== null) {
        $interactscores[] = $pct;
        $interactdetails[] = 'H5P: ' . format_string($h5p->name, true, ['escape' => false]) . ' (' . round($pct / 10.0, 1) . '/10)';
    } else {
        $interactdetails[] = 'H5P: ' . format_string($h5p->name, true, ['escape' => false]) . ' (' . local_mamxanhapp_t('Chưa hoàn thành', 'Not completed') . ')';
    }
}
foreach ($scormrecs as $sc) {
    $gi = $DB->get_record('grade_items', ['courseid' => $courseid, 'itemmodule' => 'scorm', 'iteminstance' => $sc->id]);
    $pct = null;
    if ($gi) {
        $gg = $DB->get_record('grade_grades', ['itemid' => $gi->id, 'userid' => $targetuserid]);
        if ($gg && $gg->finalgrade !== null && (float)$gi->grademax > 0) {
            $pct = min(100.0, round(((float)$gg->finalgrade / (float)$gi->grademax) * 100.0, 1));
        }
    }
    if ($pct !== null) {
        $interactscores[] = $pct;
        $interactdetails[] = 'SCORM 1.2: ' . format_string($sc->name, true, ['escape' => false]) . ' (' . round($pct / 10.0, 1) . '/10)';
    } else {
        $interactdetails[] = 'SCORM 1.2: ' . format_string($sc->name, true, ['escape' => false]) . ' (' . local_mamxanhapp_t('Chưa hoàn thành', 'Not completed') . ')';
    }
}
$interactpct = !empty($interactscores) ? round(array_sum($interactscores) / count($interactscores), 1) : 0.0;
$interactscore10 = round($interactpct / 10.0, 2);
$interactdetailstr = !empty($interactdetails)
    ? implode(' • ', $interactdetails)
    : local_mamxanhapp_t('Chưa hoàn thành học liệu tương tác H5P / SCORM', 'No completed H5P / SCORM activities yet');

// =========================================================================
// COMPONENT 4: FINAL COMPREHENSIVE QUIZ (mod_quiz) — Weight: 25%
// =========================================================================
$quizzes = $DB->get_records('quiz', ['course' => $courseid], 'id ASC');
$quizpct = null;
$quizname = local_mamxanhapp_t('Bài Kiểm tra Trắc nghiệm Cuối khóa', 'Final Comprehensive Quiz');
$qcount = 10;

foreach ($quizzes as $q) {
    $quizname = format_string($q->name, true, ['escape' => false]);
    $slots = $DB->count_records('quiz_slots', ['quizid' => $q->id]);
    if ($slots > 0) {
        $qcount = $slots;
    }
    if ((float)$q->sumgrades > 0) {
        $attempts = $DB->get_records('quiz_attempts', [
            'quiz'   => $q->id,
            'userid' => $targetuserid,
            'state'  => 'finished',
        ], 'sumgrades DESC', 'id,sumgrades,timemodified', 0, 1);
        if (!empty($attempts)) {
            $top = reset($attempts);
            $pct = min(100.0, round(((float)$top->sumgrades / (float)$q->sumgrades) * 100.0, 1));
            if ($quizpct === null || $pct > $quizpct) {
                $quizpct = $pct;
            }
        }
    }
}
$hasquizattempt = ($quizpct !== null);
if (!$hasquizattempt) {
    $quizpct = 0.0;
}
$quizscore10 = round($quizpct / 10.0, 2);
$quizdetailstr = $hasquizattempt
    ? ($quizname . ' (' . $qcount . ($en ? ' questions • Score: ' : ' câu hỏi • Điểm đạt: ') . $quizscore10 . '/10)')
    : ($quizname . ' (' . $qcount . ($en ? ' questions • Status: Not taken' : ' câu hỏi • Trạng thái: Chưa làm bài') . ')');

// =========================================================================
// COMPONENT 5: CAPSTONE PRACTICAL RUBRIC (mod_fieldpractice / mod_assign) — Weight: 25%
// =========================================================================
$rubricpct = null;
$rubricsub = null;
$rubrictitle = local_mamxanhapp_t('Thực hành Tại vườn: Điểm danh QR & Rubric 4 Tiêu chí', 'Field Practice: QR Check-in & 4-Criteria Rubric');
$hasfpmodule = false;

if ($DB->get_manager()->table_exists('fieldpractice') && $DB->get_manager()->table_exists('fieldpractice_sub')) {
    $fps = $DB->get_records('fieldpractice', ['course' => $courseid], 'id ASC');
    if (!empty($fps)) {
        $hasfpmodule = true;
        foreach ($fps as $fp) {
            $rubrictitle = format_string($fp->name, true, ['escape' => false]);
            $sub = $DB->get_record('fieldpractice_sub', ['fieldpracticeid' => $fp->id, 'userid' => $targetuserid]);
            if ($sub && $sub->total_grade !== null && ((int)$sub->graded_time > 0 || (float)$sub->total_grade > 0)) {
                $rubricpct = min(100.0, (float)$sub->total_grade);
                $rubricsub = $sub;
                break;
            }
        }
    }
}
if (!$hasfpmodule && $rubricpct === null && $capstoneassign) {
    $rubrictitle = format_string($capstoneassign->name, true, ['escape' => false]);
    $ag = $DB->get_record('assign_grades', ['assignment' => $capstoneassign->id, 'userid' => $targetuserid]);
    $maxg = (float)$capstoneassign->grade > 0 ? (float)$capstoneassign->grade : 100.0;
    if ($ag && $ag->grade !== null && (float)$ag->grade > 0) {
        $rubricpct = min(100.0, round(((float)$ag->grade / $maxg) * 100.0, 1));
    }
}
$hasrubricgrade = ($rubricpct !== null);
if (!$hasrubricgrade) {
    $rubricpct = 0.0;
}
$rubricscore10 = round($rubricpct / 10.0, 2);
if ($rubricsub) {
    $rubricdetailstr = $rubrictitle . ' (TC1: ' . (int)$rubricsub->crit_prep . '/25 • TC2: ' . (int)$rubricsub->crit_tech . '/25 • TC3: ' . (int)$rubricsub->crit_biosafe . '/25 • TC4: ' . (int)$rubricsub->crit_quality . '/25)';
} else if ($hasrubricgrade) {
    $rubricdetailstr = $rubrictitle . ' — ' . local_mamxanhapp_t('Đã chấm điểm Rubric thực hành', 'Graded via 4-Criteria VietGAP Rubric');
} else {
    $rubricdetailstr = $rubrictitle . ' — ' . local_mamxanhapp_t('Chưa chấm điểm Rubric thực hành', 'Practical Rubric not graded yet');
}

// =========================================================================
// WEIGHTED TOTAL CALCULATION & CLASSIFICATION
// =========================================================================
$w1 = round($attscore10 * 0.10, 2);
$w2 = round($discscore10 * 0.20, 2);
$w3 = round($interactscore10 * 0.20, 2);
$w4 = round($quizscore10 * 0.25, 2);
$w5 = round($rubricscore10 * 0.25, 2);

$final10 = round($w1 + $w2 + $w3 + $w4 + $w5, 2);
$final100 = round($final10 * 10.0, 1);

// Certificate record check & dynamic instructor/date metadata
$cert = $DB->get_record('local_mamxanhcert', ['userid' => $targetuserid, 'courseid' => $courseid, 'status' => 'valid']);
$iseligiblecert = $eligibility ? (bool)$eligibility->eligible : ($cert !== false);

if ($final10 >= 9.0 && $iseligiblecert) {
    $ranklabel = local_mamxanhapp_t('Xuất sắc (Distinction - A+)', 'Distinction (Grade A+)');
} else if ($final10 >= 8.0 && $iseligiblecert) {
    $ranklabel = local_mamxanhapp_t('Giỏi (Very Good - A)', 'Very Good (Grade A)');
} else if ($final10 >= 7.0 && $iseligiblecert) {
    $ranklabel = local_mamxanhapp_t('Khá (Good - B)', 'Good (Grade B)');
} else if ($final10 >= 5.0) {
    $ranklabel = $iseligiblecert
        ? local_mamxanhapp_t('Đạt (Pass - C)', 'Pass (Grade C)')
        : local_mamxanhapp_t('Đạt GPA — Chờ đủ điều kiện QR', 'GPA Pass — Pending QR Gates');
} else {
    $ranklabel = local_mamxanhapp_t('Chưa đạt (Incomplete)', 'Incomplete');
}
$certcode = $cert ? $cert->cert_code : '';
$certhmac = $cert ? (substr($cert->hmac_signature, 0, 24) . '...') : '';
$verifyurl = $cert ? (new moodle_url('/local/mamxanhcert/verify.php', ['code' => $certcode]))->out(false) : '';
$feedbackurl = (new moodle_url('/local/mamxanhapp/feedback_sheet.php', ['courseid' => $courseid, 'userid' => $targetuserid]))->out(false);
$courseurl = (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false);

$reportts = $cert
    ? (int)$cert->timecreated
    : (($rubricsub && (int)$rubricsub->graded_time > 0) ? (int)$rubricsub->graded_time : time());
$reportdatelabel = local_mamxanhapp_t(
    'Lâm Đồng, ngày ' . date('d', $reportts) . ' tháng ' . date('m', $reportts) . ' năm ' . date('Y', $reportts),
    'Lam Dong, ' . date('F d, Y', $reportts)
);

$graderuser = null;
if ($rubricsub && (int)$rubricsub->graderid > 0) {
    $graderuser = $DB->get_record('user', ['id' => (int)$rubricsub->graderid, 'deleted' => 0]);
}
if (!$graderuser) {
    $graderuser = $DB->get_record('user', ['username' => 'cbkt_pilot', 'deleted' => 0]);
}
$instructorname = $graderuser
    ? fullname($graderuser)
    : local_mamxanhapp_t('Kỹ sư Trần Minh Tuấn (CBKT)', 'Eng. Tran Minh Tuan (CBKT)');

echo $OUTPUT->header();
?>

<style>
.mx-transcript-sheet {
    background: #FFFFFF;
    border: 1px solid #E6ECE9;
    border-radius: 12px;
    padding: 36px 40px;
    max-width: 1040px;
    margin: 0 auto 32px;
    color: #0F172A;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
}
.mx-transcript-gov-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 2px solid #234E3E;
    padding-bottom: 18px;
    margin-bottom: 24px;
    gap: 20px;
}
.mx-transcript-org {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #334155;
    line-height: 1.55;
}
.mx-transcript-org strong {
    color: #234E3E;
    font-size: 0.92rem;
}
.mx-transcript-motto {
    text-align: right;
    font-size: 0.85rem;
    line-height: 1.55;
    color: #0F172A;
}
.mx-transcript-title-block {
    text-align: center;
    margin-bottom: 26px;
}
.mx-transcript-title-block h2 {
    color: #234E3E;
    font-weight: 800;
    font-size: 1.5rem;
    letter-spacing: 0.02em;
    margin-bottom: 6px;
    text-transform: uppercase;
}
.mx-transcript-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px 28px;
    background: #F9FAF9;
    border: 1px solid #E6ECE9;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 24px;
    font-size: 0.92rem;
}
.mx-transcript-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 24px;
    font-size: 0.92rem;
}
.mx-transcript-table th {
    background: #EAF2EE;
    color: #234E3E;
    font-weight: 700;
    padding: 12px 14px;
    border: 1px solid #CBD5E1;
    text-align: left;
}
.mx-transcript-table td {
    padding: 12px 14px;
    border: 1px solid #E2E8F0;
    vertical-align: middle;
}
.mx-transcript-table tfoot td {
    background: #F9FAF9;
    font-weight: 700;
    border-top: 2px solid #234E3E;
}
.mx-transcript-summary-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}
.mx-ts-card {
    background: #F9FAF9;
    border: 1px solid #E6ECE9;
    border-radius: 8px;
    padding: 14px 16px;
    text-align: center;
}
.mx-ts-card-lbl {
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748B;
    font-weight: 700;
    margin-bottom: 4px;
}
.mx-ts-card-val {
    font-size: 1.35rem;
    font-weight: 800;
    color: #234E3E;
}
.mx-transcript-signatures {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 28px;
    padding-top: 18px;
    border-top: 1px dashed #CBD5E1;
    text-align: center;
    font-size: 0.88rem;
}
.mx-sig-box {
    min-height: 125px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.mx-sig-stamp {
    display: inline-block;
    margin: 10px auto;
    padding: 6px 14px;
    border: 2px solid #234E3E;
    color: #234E3E;
    font-weight: 700;
    font-size: 0.78rem;
    border-radius: 4px;
    background: #EAF2EE;
}
@media (max-width: 768px) {
    .mx-transcript-meta-grid,
    .mx-transcript-summary-cards,
    .mx-transcript-signatures {
        grid-template-columns: 1fr;
    }
    .mx-transcript-gov-header {
        flex-direction: column;
    }
    .mx-transcript-motto {
        text-align: left;
    }
}
@media (max-width: 575.98px) {
    .mx-no-print form,
    #id_courseid,
    #id_userid {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
    }
    .mx-transcript-sheet {
        padding: 18px 14px;
    }
}
@media print {
    @page {
        size: A4 portrait;
        margin: 12mm;
    }
    nav.navbar,
    #page-header,
    .secondary-navigation,
    .drawer-toggles,
    .drawer,
    footer#page-footer,
    .mx-back-nav-bar,
    .mx-no-print {
        display: none !important;
    }
    #page, #page-wrapper, #topofscroll, #page-content, #region-main-box, #region-main {
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        background: #FFFFFF !important;
        box-shadow: none !important;
    }
    .mx-transcript-sheet {
        border: 1px solid #0F172A !important;
        box-shadow: none !important;
        padding: 16px 20px !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
    .mx-transcript-summary-cards {
        grid-template-columns: repeat(4, 1fr) !important;
    }
    .mx-transcript-signatures {
        grid-template-columns: repeat(3, 1fr) !important;
        page-break-inside: avoid;
    }
}
</style>

<div class="mx-no-print mb-3 d-flex flex-wrap justify-content-between align-items-center" style="max-width:1040px;margin:0 auto 16px;gap:12px;">
    <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
        <a href="<?php echo $courseurl; ?>" class="btn btn-secondary">
            <?php echo s(local_mamxanhapp_t('← Quay lại Khóa học', '← Back to Course')); ?>
        </a>
        <form method="get" action="<?php echo (new moodle_url('/local/mamxanhapp/transcript.php'))->out(false); ?>" class="d-flex flex-wrap align-items-center" style="gap:8px;">
            <label for="id_courseid" class="small fw-bold text-muted mb-0"><?php echo s(local_mamxanhapp_t('Học phần:', 'Course:')); ?></label>
            <select id="id_courseid" name="courseid" class="form-control form-control-sm" style="width:auto;min-width:220px;" onchange="this.form.submit()">
                <?php foreach ($courses as $c): ?>
                <option value="<?php echo (int)$c->id; ?>" <?php echo ((int)$c->id === $courseid) ? 'selected' : ''; ?>>
                    <?php echo s($c->shortname . ' — ' . $c->fullname); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if ($iscbktadmin && !empty($learners)): ?>
            <label for="id_userid" class="small fw-bold text-muted mb-0"><?php echo s(local_mamxanhapp_t('Học viên:', 'Learner:')); ?></label>
            <select id="id_userid" name="userid" class="form-control form-control-sm" style="width:auto;min-width:200px;" onchange="this.form.submit()">
                <?php foreach ($learners as $u): ?>
                <option value="<?php echo (int)$u->id; ?>" <?php echo ((int)$u->id === $targetuserid) ? 'selected' : ''; ?>>
                    <?php echo s(fullname($u) . ' (' . $u->username . ')'); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
        </form>
    </div>

    <div class="d-flex flex-wrap" style="gap:8px;">
        <a href="<?php echo $feedbackurl; ?>" class="btn btn-secondary">
            <?php echo s(local_mamxanhapp_t('Xem Phiếu Chấm Rubric', 'View Rubric Sheet')); ?>
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <?php echo s(local_mamxanhapp_t('In Bảng điểm (A4 Print)', 'Print Transcript (A4)')); ?>
        </button>
    </div>
</div>

<div class="mx-transcript-sheet" id="mx-transcript-printable">
    <div class="mx-transcript-gov-header">
        <div class="mx-transcript-org">
            <strong><?php echo s(local_mamxanhapp_t('HỆ THỐNG ĐÀO TẠO & KHUYẾN NÔNG SỐ MẦM XANH LMS 2.0', 'MAM XANH LMS 2.0 DIGITAL EXTENSION & ACADEMIC SYSTEM')); ?></strong><br>
            <?php echo s(local_mamxanhapp_t('Hội đồng Thẩm định Đào tạo Nông nghiệp Công nghệ cao', 'High-Tech Agricultural Training Assessment Board')); ?><br>
            <span><?php echo s(local_mamxanhapp_t('Số hồ sơ: ', 'Record ID: ') . 'TS-2026-' . $courseid . '-' . $targetuserid); ?></span>
        </div>
        <div class="mx-transcript-motto">
            <strong><?php echo s(local_mamxanhapp_t('CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM', 'SOCIALIST REPUBLIC OF VIETNAM')); ?></strong><br>
            <span><?php echo s(local_mamxanhapp_t('Độc lập - Tự do - Hạnh phúc', 'Independence - Freedom - Happiness')); ?></span><br>
            <em class="small text-muted"><?php echo s($reportdatelabel); ?></em>
        </div>
    </div>

    <div class="mx-transcript-title-block">
        <h2><?php echo s(local_mamxanhapp_t('BẢNG ĐIỂM HỌC THUẬT TỔNG HỢP', 'OFFICIAL ACADEMIC TRANSCRIPT')); ?></h2>
        <div class="text-muted fw-bold">
            <?php echo s(local_mamxanhapp_t('Học phần: ', 'Course: ') . $course->fullname . ' (' . $course->shortname . ')'); ?>
        </div>
    </div>

    <div class="mx-transcript-meta-grid">
        <div><strong><?php echo s(local_mamxanhapp_t('Họ và tên học viên:', 'Learner Full Name:')); ?></strong> <?php echo s(fullname($learner)); ?></div>
        <div><strong><?php echo s(local_mamxanhapp_t('Mã định danh học viên:', 'Learner Account / ID:')); ?></strong> <code><?php echo s($learner->username); ?></code></div>
        <div><strong><?php echo s(local_mamxanhapp_t('Đơn vị / Nông hộ:', 'Farm Household:')); ?></strong> <?php echo s($farmname); ?><?php echo $aream2 > 0 ? ' (' . number_format($aream2) . ' m²)' : ''; ?></div>
        <div><strong><?php echo s(local_mamxanhapp_t('Vùng canh tác & Tiêu chuẩn:', 'Region & Standard:')); ?></strong> <?php echo s($region . ' • ' . $certstd); ?></div>
    </div>

    <div class="mx-transcript-summary-cards">
        <div class="mx-ts-card">
            <div class="mx-ts-card-lbl"><?php echo s(local_mamxanhapp_t('Điểm Tổng kết (Hệ 10)', 'Final GPA (Scale 10)')); ?></div>
            <div class="mx-ts-card-val"><?php echo number_format($final10, 2); ?> / 10</div>
        </div>
        <div class="mx-ts-card">
            <div class="mx-ts-card-lbl"><?php echo s(local_mamxanhapp_t('Điểm Quy đổi (Hệ 100)', 'Percentage Score')); ?></div>
            <div class="mx-ts-card-val"><?php echo number_format($final100, 1); ?>%</div>
        </div>
        <div class="mx-ts-card">
            <div class="mx-ts-card-lbl"><?php echo s(local_mamxanhapp_t('Xếp loại Học lực', 'Classification')); ?></div>
            <div class="mx-ts-card-val" style="font-size:1.05rem;"><?php echo s($ranklabel); ?></div>
        </div>
        <div class="mx-ts-card">
            <div class="mx-ts-card-lbl"><?php echo s(local_mamxanhapp_t('Trạng thái Chứng nhận QR', 'QR Certificate Status')); ?></div>
            <div class="mx-ts-card-val" style="font-size:1.0rem;color:<?php echo $iseligiblecert ? '#234E3E' : '#B45309'; ?>;">
                <?php echo s($iseligiblecert ? local_mamxanhapp_t('Đủ điều kiện cấp', 'Eligible & Verified') : local_mamxanhapp_t('Chưa đủ điều kiện', 'Not Yet Eligible')); ?>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="mx-transcript-table">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:28%;"><?php echo s(local_mamxanhapp_t('Thành phần Đánh giá', 'Assessment Component')); ?></th>
                    <th style="width:35%;"><?php echo s(local_mamxanhapp_t('Chi tiết Minh chứng Học tập', 'Academic Evidence & Details')); ?></th>
                    <th style="width:10%;text-align:center;"><?php echo s(local_mamxanhapp_t('Trọng số', 'Weight')); ?></th>
                    <th style="width:11%;text-align:center;"><?php echo s(local_mamxanhapp_t('Điểm (Hệ 10)', 'Score (10)')); ?></th>
                    <th style="width:11%;text-align:center;"><?php echo s(local_mamxanhapp_t('Điểm Quy đổi', 'Weighted')); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>01</td>
                    <td><strong><?php echo s(local_mamxanhapp_t('Chuyên cần (Điểm danh Lý thuyết & Thực hành)', 'Attendance (Theory & Practical Sessions)')); ?></strong></td>
                    <td class="small text-muted"><?php echo s($attdetailstr); ?></td>
                    <td style="text-align:center;"><strong>10%</strong></td>
                    <td style="text-align:center;"><?php echo number_format($attscore10, 2); ?></td>
                    <td style="text-align:center;"><strong><?php echo number_format($w1, 2); ?></strong></td>
                </tr>
                <tr>
                    <td>02</td>
                    <td><strong><?php echo s(local_mamxanhapp_t('Bài tập Thảo luận Chuyên đề (02 bài)', 'In-Class Discussion Assignments (02 tasks)')); ?></strong></td>
                    <td class="small text-muted"><?php echo s($discdetailstr); ?></td>
                    <td style="text-align:center;"><strong>20%</strong></td>
                    <td style="text-align:center;"><?php echo number_format($discscore10, 2); ?></td>
                    <td style="text-align:center;"><strong><?php echo number_format($w2, 2); ?></strong></td>
                </tr>
                <tr>
                    <td>03</td>
                    <td><strong><?php echo s(local_mamxanhapp_t('Tương tác Học liệu Số (H5P & Mô phỏng SCORM)', 'Interactive H5P & SCORM 1.2 Simulation')); ?></strong></td>
                    <td class="small text-muted"><?php echo s($interactdetailstr); ?></td>
                    <td style="text-align:center;"><strong>20%</strong></td>
                    <td style="text-align:center;"><?php echo number_format($interactscore10, 2); ?></td>
                    <td style="text-align:center;"><strong><?php echo number_format($w3, 2); ?></strong></td>
                </tr>
                <tr>
                    <td>04</td>
                    <td><strong><?php echo s(local_mamxanhapp_t('Kiểm tra Trắc nghiệm Cuối khóa', 'Final Comprehensive Examination')); ?></strong></td>
                    <td class="small text-muted"><?php echo s($quizdetailstr); ?></td>
                    <td style="text-align:center;"><strong>25%</strong></td>
                    <td style="text-align:center;"><?php echo number_format($quizscore10, 2); ?></td>
                    <td style="text-align:center;"><strong><?php echo number_format($w4, 2); ?></strong></td>
                </tr>
                <tr>
                    <td>05</td>
                    <td><strong><?php echo s(local_mamxanhapp_t('Bài Thu hoạch Thực hành (Chấm Rubric)', 'Capstone Practical Field Report (Rubric)')); ?></strong></td>
                    <td class="small text-muted"><?php echo s($rubricdetailstr); ?></td>
                    <td style="text-align:center;"><strong>25%</strong></td>
                    <td style="text-align:center;"><?php echo number_format($rubricscore10, 2); ?></td>
                    <td style="text-align:center;"><strong><?php echo number_format($w5, 2); ?></strong></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right;"><?php echo s(local_mamxanhapp_t('TỔNG ĐIỂM HỌC PHẦN TÍCH LŨY:', 'TOTAL WEIGHTED COURSE SCORE:')); ?></td>
                    <td style="text-align:center;">100%</td>
                    <td style="text-align:center;color:#234E3E;"><?php echo number_format($final10, 2); ?> / 10</td>
                    <td style="text-align:center;color:#234E3E;font-size:1.05rem;"><?php echo number_format($final100, 1); ?>%</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="p-3 rounded mb-3" style="background:<?php echo $cert ? '#EAF2EE' : '#FFF8E1'; ?>;border:1px solid #E6ECE9;font-size:0.88rem;">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:12px;">
            <?php if ($cert): ?>
            <div>
                <strong style="color:#234E3E;"><?php echo s(local_mamxanhapp_t('Xác thực Chữ ký Số HMAC-SHA256:', 'HMAC-SHA256 Cryptographic Verification:')); ?></strong>
                <span class="ms-1"><?php echo s(local_mamxanhapp_t('Mã chứng nhận:', 'Certificate Code:')); ?> <code><?php echo s($certcode); ?></code></span>
                <span class="ms-2">• Hash: <code><?php echo s($certhmac); ?></code></span>
            </div>
            <a href="<?php echo $verifyurl; ?>" class="btn btn-sm btn-secondary mx-no-print">
                <?php echo s(local_mamxanhapp_t('Tra cứu Xác thực QR', 'Verify QR Online')); ?>
            </a>
            <?php else: ?>
            <div>
                <strong style="color:#B45309;"><?php echo s(local_mamxanhapp_t('Trạng thái Sổ cái Chứng nhận Số HMAC-SHA256:', 'HMAC-SHA256 Certificate Ledger Status:')); ?></strong>
                <span class="ms-1"><?php echo s(local_mamxanhapp_t('Chưa cấp chứng nhận (Cần hoàn thành đủ 03 cổng: Điểm danh ≥ 80%, Trắc nghiệm ≥ 70%, Rubric Thực hành ≥ 60/100)', 'Not yet issued (Requires all 3 gates: Attendance >= 80%, Final Quiz >= 70%, Practical Rubric >= 60/100)')); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mx-transcript-signatures">
        <div class="mx-sig-box">
            <div>
                <strong><?php echo s(local_mamxanhapp_t('XÁC NHẬN CỦA HỌC VIÊN', 'LEARNER ACKNOWLEDGMENT')); ?></strong><br>
                <span class="text-muted small"><?php echo s(local_mamxanhapp_t('(Chữ ký điện tử xác nhận kết quả)', '(Electronic signature confirming transcript)')); ?></span>
            </div>
            <div class="mx-sig-stamp"><?php echo s(local_mamxanhapp_t('ĐÃ XÁC NHẬN HỒ SƠ SỐ', 'DIGITALLY ACKNOWLEDGED')); ?></div>
            <div><strong><?php echo s(fullname($learner)); ?></strong></div>
        </div>
        <div class="mx-sig-box">
            <div>
                <strong><?php echo s(local_mamxanhapp_t('GIẢNG VIÊN / CÁN BỘ KỸ THUẬT', 'INSTRUCTOR / EXTENSION OFFICER')); ?></strong><br>
                <span class="text-muted small"><?php echo s(local_mamxanhapp_t('(Thẩm định Chuyên môn & Rubric)', '(Technical & Rubric Evaluator)')); ?></span>
            </div>
            <div class="mx-sig-stamp"><?php echo s($hasrubricgrade ? local_mamxanhapp_t('ĐÃ CHẤM & PHÊ DUYỆT', 'GRADED & APPROVED') : local_mamxanhapp_t('ĐANG THEO DÕI HỌC TẬP', 'IN EVALUATION')); ?></div>
            <div><strong><?php echo s($instructorname); ?></strong></div>
        </div>
        <div class="mx-sig-box">
            <div>
                <strong><?php echo s(local_mamxanhapp_t('HỘI ĐỒNG ĐÀO TẠO MẦM XANH LMS', 'MAM XANH ACADEMIC BOARD')); ?></strong><br>
                <span class="text-muted small"><?php echo s(local_mamxanhapp_t('(Ký số HMAC-SHA256 & Cấp Chứng nhận)', '(HMAC-SHA256 Digital Seal)')); ?></span>
            </div>
            <div class="mx-sig-stamp"><?php echo s(local_mamxanhapp_t('CHỨNG THỰC HMAC-SHA256', 'HMAC-SHA256 SEALED')); ?></div>
            <div><strong><?php echo s(local_mamxanhapp_t('Trung tâm Khuyến nông Số Mầm Xanh', 'Mam Xanh Digital Extension Center')); ?></strong></div>
        </div>
    </div>
</div>

<?php
echo $OUTPUT->footer();
