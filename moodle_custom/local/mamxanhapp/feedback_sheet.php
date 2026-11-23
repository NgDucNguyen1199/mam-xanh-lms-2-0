<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

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

// Load available courses
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

// Load learners for CBKT/Admin selector
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
$PAGE->set_url(new moodle_url('/local/mamxanhapp/feedback_sheet.php', ['courseid' => $courseid, 'userid' => $targetuserid]));
$PAGE->set_pagelayout('standard');

$en = local_mamxanhapp_is_en();
$title = local_mamxanhapp_t(
    'Phiếu Chấm điểm Rubric & Nhận xét Giảng viên - ' . format_string($course->shortname),
    'Instructor Rubric Evaluation & Feedback Sheet - ' . format_string($course->shortname)
);
$PAGE->set_title($title);
$PAGE->set_heading(local_mamxanhapp_t('Phiếu Chấm điểm Rubric 4 Tiêu chí & Nhận xét Chuyên môn CBKT', '4-Criteria Technical Rubric Evaluation & Instructor Feedback Sheet'));

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

// 1. Primary Source: Locate mod_fieldpractice instance & graded submission (mdl_fieldpractice_sub)
$fpinstance = null;
$fpsub = null;
if ($DB->get_manager()->table_exists('fieldpractice') && $DB->get_manager()->table_exists('fieldpractice_sub')) {
    $fps = $DB->get_records('fieldpractice', ['course' => $courseid], 'id ASC');
    foreach ($fps as $fp) {
        if (!$fpinstance) {
            $fpinstance = $fp;
        }
        $sub = $DB->get_record('fieldpractice_sub', ['fieldpracticeid' => $fp->id, 'userid' => $targetuserid]);
        if ($sub) {
            $fpinstance = $fp;
            $fpsub = $sub;
            if ($sub->total_grade !== null && ((int)$sub->graded_time > 0 || (float)$sub->total_grade > 0)) {
                break;
            }
        }
    }
}

// 2. Secondary Source (only if course has no mod_fieldpractice): Capstone Assignment
$assigns = $DB->get_records('assign', ['course' => $courseid], 'id ASC');
$capstone = null;
foreach ($assigns as $asg) {
    $low = core_text::strtolower($asg->name);
    if (strpos($low, 'thảo luận') === false && strpos($low, 'discussion') === false) {
        $capstone = $asg;
    }
}
if (!$capstone && !empty($assigns)) {
    $capstone = end($assigns);
}

$assignname = $fpinstance
    ? format_string($fpinstance->name, true, ['escape' => false])
    : ($capstone
        ? format_string($capstone->name, true, ['escape' => false])
        : local_mamxanhapp_t('Thực hành Tại vườn: Điểm danh QR & Rubric 4 Tiêu chí', 'Field Practice: QR Check-in & 4-Criteria Rubric'));

$rubricname = local_mamxanhapp_t('Rubric Đánh giá Thực hành Hiện trường 4 Tiêu chí Chuẩn VietGAP (Thang 100 điểm)', '4-Criteria VietGAP Practical Field Assessment Rubric (100-Point Scale)');
$criteriarows = [];
$overallfeedback = '';
$isgraded = false;
$gradeddate = local_mamxanhapp_t('Chưa chấm điểm', 'Not graded yet');

$graderuser = null;
if ($fpsub && (int)$fpsub->graderid > 0) {
    $graderuser = $DB->get_record('user', ['id' => (int)$fpsub->graderid, 'deleted' => 0]);
}
if (!$graderuser) {
    $graderuser = $DB->get_record('user', ['username' => 'cbkt_pilot', 'deleted' => 0]);
}
$gradername = $graderuser
    ? (fullname($graderuser) . ' (' . $graderuser->username . ')')
    : local_mamxanhapp_t('Kỹ sư Trần Minh Tuấn (CBKT)', 'Eng. Tran Minh Tuan (CBKT)');

$standardcrit = [
    1 => [
        'desc' => local_mamxanhapp_t('Tiêu chí 1: Chuẩn bị Vật tư, Giá thể & Hiệu chuẩn Thiết bị Đo (EC/pH/Ẩm kế)', 'Criterion 1: Material, Substrate Preparation & Sensor Calibration (EC/pH/Moisture)'),
        'l0'   => local_mamxanhapp_t('Chưa đạt (0đ): Thiếu bước xử lý giá thể hoặc không hiệu chuẩn bút đo EC/pH.', 'Needs Improvement (0 pts): Missing substrate treatment or uncalibrated EC/pH meters.'),
        'l15'  => local_mamxanhapp_t('Đạt yêu cầu (15đ): Chuẩn bị đủ vật tư và thiết bị nhưng ghi chép thông số đầu vào chưa chi tiết.', 'Satisfactory (15 pts): Adequate materials and tools; initial baseline readings partially logged.'),
        'l25'  => local_mamxanhapp_t('Xuất sắc (25đ): Xử lý giá thể chuẩn kỹ thuật, hiệu chuẩn chính xác bút đo EC/pH và kiểm tra nguồn nước.', 'Excellent (25 pts): Full substrate buffering, accurate EC/pH calibration, and source water verification.'),
        'rem_pass' => local_mamxanhapp_t('Hồ sơ chuẩn bị vật tư, giá thể và thiết bị đo EC/pH đầy đủ, hiệu chuẩn chính xác trước khi vận hành.', 'Materials, substrate preparation, and EC/pH meter calibration documented accurately prior to operation.'),
        'rem_low'  => local_mamxanhapp_t('Cần bổ sung bước kiểm tra hiệu chuẩn bút đo EC/pH và vệ sinh khử trùng dụng cụ trước ca thực hành.', 'Need to complete EC/pH meter calibration check and tool sanitization before field practice.'),
    ],
    2 => [
        'desc' => local_mamxanhapp_t('Tiêu chí 2: Thực hành Quy trình Kỹ thuật Canh tác & Điều tiết Dinh dưỡng / Vi khí hậu', 'Criterion 2: Technical Cultivation Execution & Nutrient / Microclimate Regulation'),
        'l0'   => local_mamxanhapp_t('Chưa đạt (0đ): Pha sai nồng độ dinh dưỡng hoặc tưới không đúng ngưỡng giai đoạn sinh trưởng.', 'Needs Improvement (0 pts): Incorrect nutrient concentration or improper irrigation schedule.'),
        'l15'  => local_mamxanhapp_t('Đạt yêu cầu (15đ): Vận hành đúng quy trình tưới và chăm sóc cơ bản, sai số EC/pH nhỏ.', 'Satisfactory (15 pts): Proper basic irrigation and crop care with minor EC/pH variance.'),
        'l25'  => local_mamxanhapp_t('Xuất sắc (25đ): Kiểm soát chuẩn xác ngưỡng EC/pH, nhiệt ẩm nhà màng và kỹ thuật chăm sóc theo giai đoạn.', 'Excellent (25 pts): Precision control of EC/pH regimes, greenhouse microclimate, and stage-specific care.'),
        'rem_pass' => local_mamxanhapp_t('Thực hiện đúng quy trình kỹ thuật canh tác, thông số tưới nhỏ giọt và pha châm phân nằm trong ngưỡng tối ưu.', 'Cultivation protocol, drip irrigation parameters, and fertigation dosing strictly within optimal range.'),
        'rem_low'  => local_mamxanhapp_t('Thao tác pha dinh dưỡng A-B và điều chỉnh lưu lượng tưới nhỏ giọt còn sai số, cần thực hành lại tại vườn.', 'A-B nutrient mixing and drip irrigation flow regulation had deviations; requires re-practice at garden.'),
    ],
    3 => [
        'desc' => local_mamxanhapp_t('Tiêu chí 3: Tuân thủ An toàn Sinh học, Phác đồ IPM & Thời gian Cách ly (PHI)', 'Criterion 3: Bio-Safety Compliance, IPM Protocol & Pre-Harvest Interval (PHI)'),
        'l0'   => local_mamxanhapp_t('Chưa đạt (0đ): Vi phạm nguyên tắc sử dụng thuốc BVTV hoặc thiếu biện pháp phòng trừ sinh học.', 'Needs Improvement (0 pts): Violation of pesticide safety rules or lack of biological pest control.'),
        'l15'  => local_mamxanhapp_t('Đạt yêu cầu (15đ): Áp dụng đúng biện pháp IPM cơ bản và ghi nhận thời gian cách ly.', 'Satisfactory (15 pts): Applied standard IPM measures and recorded Pre-Harvest Interval.'),
        'l25'  => local_mamxanhapp_t('Xuất sắc (25đ): Ưu tiên chế phẩm sinh học (Trichoderma, thiên địch), tuân thủ tuyệt đối PHI chuẩn VietGAP.', 'Excellent (25 pts): Prioritized biological agents (Trichoderma, beneficials) and 100% VietGAP PHI compliance.'),
        'rem_pass' => local_mamxanhapp_t('Tuân thủ nghiêm ngặt quy định an toàn sinh học IPM, ưu tiên chế phẩm Trichoderma/vi sinh và đảm bảo thời gian cách ly PHI.', 'Strict adherence to biological IPM, Trichoderma bio-agents, and Pre-Harvest Interval (PHI) safety.'),
        'rem_low'  => local_mamxanhapp_t('Chưa nắm vững quy định tính ngày cách ly thu hoạch (PHI) và định lượng chế phẩm sinh học IPM.', 'Needs stronger compliance with Pre-Harvest Interval (PHI) calculation and biological IPM dosage.'),
    ],
    4 => [
        'desc' => local_mamxanhapp_t('Tiêu chí 4: Chất lượng Nông sản & Hồ sơ Nhật ký Đồng ruộng Điện tử VietGAP', 'Criterion 4: Produce Quality & Electronic VietGAP Field Logbook Completeness'),
        'l0'   => local_mamxanhapp_t('Chưa đạt (0đ): Thiếu nhật ký canh tác hoặc báo cáo thu hoạch sơ sài, không đủ điều kiện truy xuất.', 'Needs Improvement (0 pts): Incomplete cultivation diary or missing traceability data.'),
        'l15'  => local_mamxanhapp_t('Đạt yêu cầu (15đ): Có nhật ký canh tác và đánh giá sản lượng đạt yêu cầu cơ bản.', 'Satisfactory (15 pts): Field diary and yield evaluation meet baseline requirements.'),
        'l25'  => local_mamxanhapp_t('Xuất sắc (25đ): Nhật ký VietGAP điện tử đầy đủ, minh chứng rõ ràng, đủ điều kiện cấp mã QR truy xuất.', 'Excellent (25 pts): Comprehensive electronic VietGAP logbook with clear evidence, ready for QR certification.'),
        'rem_pass' => local_mamxanhapp_t('Nhật ký đồng ruộng điện tử VietGAP ghi chép đầy đủ, số liệu minh bạch, đạt chuẩn truy xuất QR.', 'Electronic VietGAP field logbook complete, transparent metrics, ready for QR traceability.'),
        'rem_low'  => local_mamxanhapp_t('Nhật ký canh tác điện tử VietGAP còn thiếu thông số pH/EC thực tế và thời gian cách ly lô nông sản.', 'Electronic VietGAP logbook is missing actual pH/EC measurements and batch quarantine records.'),
    ],
];

if ($fpsub && $fpsub->total_grade !== null && ((int)$fpsub->graded_time > 0 || (float)$fpsub->total_grade > 0)) {
    $isgraded = true;
    $ts = (int)$fpsub->graded_time > 0 ? (int)$fpsub->graded_time : (int)$fpsub->checkin_time;
    $gradeddate = $ts > 0 ? date('d/m/Y H:i', $ts) : date('d/m/Y H:i');
    $overallfeedback = trim((string)$fpsub->feedback);

    $rawscores = [
        1 => (float)$fpsub->crit_prep,
        2 => (float)$fpsub->crit_tech,
        3 => (float)$fpsub->crit_biosafe,
        4 => (float)$fpsub->crit_quality,
    ];

    foreach ($standardcrit as $idx => $fc) {
        $sc = $rawscores[$idx];
        $lvlid = ($sc >= 20.0) ? 3 : (($sc >= 10.0) ? 2 : 1);
        $rem = ($sc >= 15.0) ? $fc['rem_pass'] : $fc['rem_low'];
        $criteriarows[] = (object)[
            'index'           => $idx,
            'description'     => $fc['desc'],
            'levels'          => [
                (object)['id' => 1, 'score' => 0,  'definition' => $fc['l0']],
                (object)['id' => 2, 'score' => 15, 'definition' => $fc['l15']],
                (object)['id' => 3, 'score' => 25, 'definition' => $fc['l25']],
            ],
            'selectedlevelid' => $lvlid,
            'selectedscore'   => $sc,
            'maxscore'        => 25.0,
            'remark'          => $rem,
        ];
    }
} else {
    // Un-graded state: show 0 / 100 and clear pending status instead of fabricating 90/100
    $isgraded = false;
    if ($fpsub && (int)$fpsub->checkin_time > 0) {
        $gradeddate = local_mamxanhapp_t(
            'Đã điểm danh lúc ' . date('d/m/Y H:i', (int)$fpsub->checkin_time) . ' — Đang chờ CBKT chấm',
            'Checked in at ' . date('d/m/Y H:i', (int)$fpsub->checkin_time) . ' — Awaiting Instructor grading'
        );
    }
    foreach ($standardcrit as $idx => $fc) {
        $criteriarows[] = (object)[
            'index'           => $idx,
            'description'     => $fc['desc'],
            'levels'          => [
                (object)['id' => 1, 'score' => 0,  'definition' => $fc['l0']],
                (object)['id' => 2, 'score' => 15, 'definition' => $fc['l15']],
                (object)['id' => 3, 'score' => 25, 'definition' => $fc['l25']],
            ],
            'selectedlevelid' => 0,
            'selectedscore'   => 0.0,
            'maxscore'        => 25.0,
            'remark'          => local_mamxanhapp_t('Chưa chấm điểm tiêu chí này.', 'Not graded yet.'),
        ];
    }
}

$totalrubricscore = 0.0;
$totalrubricmax = 0.0;
foreach ($criteriarows as $cr) {
    $totalrubricscore += (float)$cr->selectedscore;
    $totalrubricmax += (float)$cr->maxscore;
}
if ($overallfeedback === '') {
    if (!$isgraded) {
        $overallfeedback = local_mamxanhapp_t(
            'Học viên chưa có kết quả chấm điểm Rubric thực hành tại vườn cho học phần này. Vui lòng hoàn thành điểm danh QR + GPS tại vườn thực nghiệm để Cán bộ kỹ thuật chấm điểm 4 tiêu chí.',
            'No graded practical field rubric evaluation exists for this learner in this course yet. Please complete garden QR + GPS check-in for Instructor 4-criteria evaluation.'
        );
    } else if ($totalrubricscore >= 60.0) {
        $overallfeedback = local_mamxanhapp_t(
            'Học viên nắm vững quy trình kỹ thuật canh tác công nghệ cao và tiêu chuẩn Quốc gia VietGAP (TCVN 11892-1:2017). Các thông số thực hành đo kiểm EC/pH, vận hành hệ thống tưới nhỏ giọt bù áp và áp dụng phác đồ sinh học IPM được thực hiện chuẩn xác.',
            'The learner demonstrates strong mastery of high-tech agricultural cultivation protocols and National VietGAP standards (TCVN 11892-1:2017).'
        );
    } else {
        $overallfeedback = local_mamxanhapp_t(
            'Kết quả thực hành tại vườn chưa đạt ngưỡng yêu cầu tối thiểu (60/100). Học viên cần ôn tập lại quy trình pha dinh dưỡng A-B, hiệu chuẩn thiết bị đo pH/EC và thực hành bổ sung cùng Cán bộ kỹ thuật.',
            'Practical field evaluation score is below the minimum passing threshold (60/100). Additional hands-on practice with the Extension Officer is required.'
        );
    }
}

$transcripturl = (new moodle_url('/local/mamxanhapp/transcript.php', ['courseid' => $courseid, 'userid' => $targetuserid]))->out(false);
$exportlogurl = (new moodle_url('/local/agriops/export_log.php', ['courseid' => $courseid, 'userid' => $targetuserid, 'format' => 'html']))->out(false);
$courseurl = (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false);

echo $OUTPUT->header();
?>

<style>
.mx-rubric-sheet {
    background: #FFFFFF;
    border: 1px solid #E6ECE9;
    border-radius: 12px;
    padding: 36px 40px;
    max-width: 1080px;
    margin: 0 auto 32px;
    color: #0F172A;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
}
.mx-rubric-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 2px solid #234E3E;
    padding-bottom: 16px;
    margin-bottom: 22px;
    gap: 16px;
}
.mx-rubric-meta {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px 24px;
    background: #F9FAF9;
    border: 1px solid #E6ECE9;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 22px;
    font-size: 0.92rem;
}
.mx-rubric-grid-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 24px;
    font-size: 0.88rem;
}
.mx-rubric-grid-table th {
    background: #234E3E;
    color: #FFFFFF;
    font-weight: 700;
    padding: 12px;
    border: 1px solid #234E3E;
    text-align: left;
}
.mx-rubric-grid-table td {
    padding: 12px;
    border: 1px solid #CBD5E1;
    vertical-align: top;
}
.mx-rubric-lvl-cell {
    background: #FFFFFF;
    color: #475569;
    position: relative;
}
.mx-rubric-lvl-cell.is-selected {
    background: #EAF2EE !important;
    border: 2px solid #234E3E !important;
    color: #0F172A !important;
}
.mx-rubric-lvl-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.74rem;
    font-weight: 700;
    margin-bottom: 6px;
    background: #F1F5F9;
    color: #334155;
}
.mx-rubric-lvl-cell.is-selected .mx-rubric-lvl-badge {
    background: #234E3E;
    color: #FFFFFF;
}
.mx-rubric-feedback-box {
    background: #F9FAF9;
    border: 1px solid #E6ECE9;
    border-left: 4px solid #234E3E;
    border-radius: 8px;
    padding: 18px 22px;
    margin-bottom: 24px;
}
.mx-rubric-signatures {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-top: 24px;
    padding-top: 18px;
    border-top: 1px dashed #CBD5E1;
    text-align: center;
    font-size: 0.9rem;
}
.mx-rubric-stamp {
    display: inline-block;
    margin: 12px auto;
    padding: 6px 14px;
    border: 2px solid #234E3E;
    color: #234E3E;
    background: #EAF2EE;
    font-weight: 700;
    font-size: 0.78rem;
    border-radius: 4px;
}
@media (max-width: 768px) {
    .mx-rubric-meta,
    .mx-rubric-signatures {
        grid-template-columns: 1fr;
    }
    .mx-rubric-header {
        flex-direction: column;
    }
}
@media (max-width: 575.98px) {
    .mx-no-print form,
    #id_rub_courseid,
    #id_rub_userid {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
    }
    .mx-rubric-sheet {
        padding: 18px 14px;
    }
}
@media print {
    @page {
        size: A4 landscape;
        margin: 10mm;
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
    .mx-rubric-sheet {
        border: 1px solid #0F172A !important;
        box-shadow: none !important;
        padding: 14px 18px !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
}
</style>

<div class="mx-no-print mb-3 d-flex flex-wrap justify-content-between align-items-center" style="max-width:1080px;margin:0 auto 16px;gap:12px;">
    <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
        <a href="<?php echo $courseurl; ?>" class="btn btn-secondary">
            <?php echo s(local_mamxanhapp_t('← Quay lại Khóa học', '← Back to Course')); ?>
        </a>
        <form method="get" action="<?php echo (new moodle_url('/local/mamxanhapp/feedback_sheet.php'))->out(false); ?>" class="d-flex flex-wrap align-items-center" style="gap:8px;">
            <label for="id_rub_courseid" class="small fw-bold text-muted mb-0"><?php echo s(local_mamxanhapp_t('Học phần:', 'Course:')); ?></label>
            <select id="id_rub_courseid" name="courseid" class="form-control form-control-sm" style="width:auto;min-width:220px;" onchange="this.form.submit()">
                <?php foreach ($courses as $c): ?>
                <option value="<?php echo (int)$c->id; ?>" <?php echo ((int)$c->id === $courseid) ? 'selected' : ''; ?>>
                    <?php echo s($c->shortname . ' — ' . $c->fullname); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if ($iscbktadmin && !empty($learners)): ?>
            <label for="id_rub_userid" class="small fw-bold text-muted mb-0"><?php echo s(local_mamxanhapp_t('Học viên:', 'Learner:')); ?></label>
            <select id="id_rub_userid" name="userid" class="form-control form-control-sm" style="width:auto;min-width:200px;" onchange="this.form.submit()">
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
        <a href="<?php echo $transcripturl; ?>" class="btn btn-secondary">
            <?php echo s(local_mamxanhapp_t('Xem Bảng điểm Học thuật', 'View Academic Transcript')); ?>
        </a>
        <a href="<?php echo $exportlogurl; ?>" class="btn btn-secondary">
            <?php echo s(local_mamxanhapp_t('Xem Báo cáo Nhật ký VietGAP', 'View VietGAP Report')); ?>
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <?php echo s(local_mamxanhapp_t('In Phiếu Rubric (A4 Print)', 'Print Rubric Sheet (A4)')); ?>
        </button>
    </div>
</div>

<div class="mx-rubric-sheet" id="mx-rubric-printable">
    <div class="mx-rubric-header">
        <div>
            <div class="small fw-bold text-uppercase" style="color:#234E3E;letter-spacing:0.04em;">
                <?php echo s(local_mamxanhapp_t('HỘI ĐỒNG CHẤM THỰC HÀNH HIỆN TRƯỜNG • MẦM XANH LMS 2.0', 'FIELD PRACTICAL EVALUATION BOARD • MAM XANH LMS 2.0')); ?>
            </div>
            <h2 class="h3 fw-bold mt-1 mb-1" style="color:#0F172A;">
                <?php echo s(local_mamxanhapp_t('PHIẾU CHẤM ĐIỂM RUBRIC & NHẬN XÉT CHUYÊN MÔN', 'OFFICIAL RUBRIC EVALUATION & INSTRUCTOR FEEDBACK SHEET')); ?>
            </h2>
            <div class="small text-muted"><?php echo s($rubricname); ?></div>
        </div>
        <div class="text-end">
            <div class="p-2 px-3 rounded" style="background:#EAF2EE;border:1px solid #234E3E;color:#234E3E;">
                <div class="small fw-bold text-uppercase"><?php echo s(local_mamxanhapp_t('Tổng điểm Rubric', 'Total Rubric Score')); ?></div>
                <div class="h3 fw-bold mb-0"><?php echo number_format($totalrubricscore, 0) . ' / ' . number_format($totalrubricmax, 0); ?></div>
            </div>
        </div>
    </div>

    <div class="mx-rubric-meta">
        <div><strong><?php echo s(local_mamxanhapp_t('Học viên thực hiện:', 'Learner Name:')); ?></strong> <?php echo s(fullname($learner)); ?> (<code><?php echo s($learner->username); ?></code>)</div>
        <div><strong><?php echo s(local_mamxanhapp_t('Nông hộ / Vùng trồng:', 'Farm Household / Region:')); ?></strong> <?php echo s($farmname . ' — ' . $region); ?></div>
        <div><strong><?php echo s(local_mamxanhapp_t('Học phần đào tạo:', 'Training Course:')); ?></strong> <?php echo s($course->fullname . ' (' . $course->shortname . ')'); ?></div>
        <div><strong><?php echo s(local_mamxanhapp_t('Hạng mục đánh giá:', 'Evaluated Assignment:')); ?></strong> <?php echo s($assignname); ?></div>
        <div><strong><?php echo s(local_mamxanhapp_t('Giảng viên chấm:', 'Evaluating Instructor:')); ?></strong> <?php echo s($gradername); ?></div>
        <div><strong><?php echo s(local_mamxanhapp_t('Thời gian thẩm định:', 'Evaluation Timestamp:')); ?></strong> <?php echo s($gradeddate); ?></div>
    </div>

    <div class="table-responsive">
        <table class="mx-rubric-grid-table">
            <thead>
                <tr>
                    <th style="width:20%;"><?php echo s(local_mamxanhapp_t('Tiêu chí Đánh giá (Criteria)', 'Evaluation Criterion')); ?></th>
                    <th style="width:18%;"><?php echo s(local_mamxanhapp_t('Mức 1: Chưa đạt (0 đ)', 'Level 1: Needs Work (0 pts)')); ?></th>
                    <th style="width:20%;"><?php echo s(local_mamxanhapp_t('Mức 2: Đạt chuẩn (15 đ)', 'Level 2: Proficient (15 pts)')); ?></th>
                    <th style="width:22%;"><?php echo s(local_mamxanhapp_t('Mức 3: Xuất sắc (25 đ)', 'Level 3: Exemplary (25 pts)')); ?></th>
                    <th style="width:20%;"><?php echo s(local_mamxanhapp_t('Điểm & Nhận xét của CBKT', 'Score & Instructor Remark')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($criteriarows as $cr): ?>
                <tr>
                    <td>
                        <strong style="color:#234E3E;"><?php echo s($cr->description); ?></strong>
                    </td>
                    <?php
                    for ($i = 0; $i < 3; $i++):
                        $lvl = $cr->levels[$i] ?? null;
                        $isselected = ($isgraded && $lvl && (int)$lvl->id === (int)$cr->selectedlevelid);
                        $scorelbl = $lvl ? ((int)$lvl->score . ($en ? ' pts' : ' điểm')) : '';
                        $deflbl = $lvl ? trim(strip_tags($lvl->definition)) : '';
                    ?>
                    <td class="mx-rubric-lvl-cell <?php echo $isselected ? 'is-selected' : ''; ?>">
                        <span class="mx-rubric-lvl-badge">
                            <?php echo s($scorelbl . ($isselected ? ($en ? ' [SELECTED]' : ' [ĐẠT MỨC NÀY]') : '')); ?>
                        </span>
                        <div><?php echo s($deflbl); ?></div>
                    </td>
                    <?php endfor; ?>
                    <td style="background:#F9FAF9;">
                        <div class="fw-bold mb-1" style="color:#234E3E;font-size:0.95rem;">
                            <?php echo $isgraded
                                ? s(local_mamxanhapp_t('Điểm chấm: ', 'Awarded: ') . number_format($cr->selectedscore, 0) . ' / ' . number_format($cr->maxscore, 0))
                                : s(local_mamxanhapp_t('Chưa chấm (0 / 25)', 'Pending (0 / 25)')); ?>
                        </div>
                        <div class="small text-muted"><?php echo s($cr->remark); ?></div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mx-rubric-feedback-box">
        <div class="fw-bold mb-1" style="color:#234E3E;">
            <?php echo s(local_mamxanhapp_t('NHẬN XÉT TỔNG HỢP CỦA CÁN BỘ KỸ THUẬT (OVERALL INSTRUCTOR FEEDBACK):', 'OVERALL TECHNICAL EVALUATION & INSTRUCTOR FEEDBACK:')); ?>
        </div>
        <p class="mb-0" style="line-height:1.65;color:#1E293B;"><?php echo s($overallfeedback); ?></p>
    </div>

    <div class="mx-rubric-signatures">
        <div>
            <strong><?php echo s(local_mamxanhapp_t('XÁC NHẬN CỦA HỌC VIÊN / NÔNG HỘ', 'LEARNER / FARM HOUSEHOLD SIGNATURE')); ?></strong><br>
            <span class="small text-muted"><?php echo s(local_mamxanhapp_t('(Cam kết duy trì quy trình kỹ thuật VietGAP tại nông hộ)', '(Committed to maintaining VietGAP protocol on farm)')); ?></span><br>
            <div class="mx-rubric-stamp"><?php echo s(local_mamxanhapp_t('ĐÃ TIẾP THU & XÁC NHẬN', 'ACKNOWLEDGED BY LEARNER')); ?></div><br>
            <strong><?php echo s(fullname($learner)); ?></strong>
        </div>
        <div>
            <strong><?php echo s(local_mamxanhapp_t('CÁN BỘ KỸ THUẬT CHẤM THỰC HÀNH', 'EVALUATING EXTENSION OFFICER')); ?></strong><br>
            <span class="small text-muted"><?php echo s(local_mamxanhapp_t('(Chữ ký số thẩm định Rubric thực địa)', '(Digital signature verifying field rubric)')); ?></span><br>
            <div class="mx-rubric-stamp">
                <?php
                if (!$isgraded) {
                    echo s(local_mamxanhapp_t('ĐANG CHỜ CHẤM THỰC HÀNH', 'AWAITING RUBRIC GRADING'));
                } else if ($totalrubricscore >= 60.0) {
                    echo s(local_mamxanhapp_t('ĐÃ THẨM ĐỊNH RUBRIC • ĐẠT CHUẨN', 'RUBRIC VERIFIED • PASSED'));
                } else {
                    echo s(local_mamxanhapp_t('ĐÃ CHẤM • CHƯA ĐẠT (< 60/100)', 'GRADED • BELOW THRESHOLD'));
                }
                ?>
            </div><br>
            <strong><?php echo s($gradername); ?></strong>
        </div>
    </div>
</div>

<?php
echo $OUTPUT->footer();
