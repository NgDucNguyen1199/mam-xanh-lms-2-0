<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');

local_mamxanhapp_handle_lang_switch();
require_login();

$syscontext = context_system::instance();
$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/mamxanhcert/index.php'));
$PAGE->set_pagelayout('standard');

$en = local_mamxanhapp_is_en();
$title = local_mamxanhapp_t('Chứng nhận Số QR Ký số HMAC-SHA256 - Mầm Xanh LMS', 'Digital HMAC-SHA256 QR Certificates - Mam Xanh LMS');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$userid = (int)$USER->id;
local_mamxanhcert_sync_user_certificates($userid);

$canmanage = is_siteadmin() || has_capability('local/mamxanhcert:manage', $syscontext);
if ($canmanage && optional_param('all', 0, PARAM_INT) === 1) {
    $certs = $DB->get_records_sql("
        SELECT mc.*, c.fullname AS coursename, c.shortname, u.firstname, u.lastname, u.username
          FROM {local_mamxanhcert} mc
          JOIN {course} c ON c.id = mc.courseid
          JOIN {user} u ON u.id = mc.userid
      ORDER BY mc.timecreated DESC
    ");
} else {
    $certs = $DB->get_records_sql("
        SELECT mc.*, c.fullname AS coursename, c.shortname, u.firstname, u.lastname, u.username
          FROM {local_mamxanhcert} mc
          JOIN {course} c ON c.id = mc.courseid
          JOIN {user} u ON u.id = mc.userid
         WHERE mc.userid = :uid
      ORDER BY mc.timecreated DESC
    ", ['uid' => $userid]);
}

// Build 3-Step Certification Eligibility Checklist across enrolled courses
$mycourses = enrol_get_my_courses(['id', 'fullname', 'shortname', 'visible']);
if (empty($mycourses) && $canmanage) {
    $mycourses = $DB->get_records_select('course', 'id <> :siteid AND visible = 1', ['siteid' => SITEID], 'id ASC', 'id,shortname,fullname,visible');
}

$certsbycourse = [];
foreach ($certs as $certitem) {
    if ((int)$certitem->userid === $userid) {
        $certsbycourse[(int)$certitem->courseid] = $certitem;
    }
}

$checklistrows = [];
foreach ($mycourses as $mc) {
    if ((int)$mc->id === SITEID || $mc->shortname === 'MASTER-AGRITECH-TPL') {
        continue;
    }
    $cid = (int)$mc->id;
    $eval = local_mamxanhcert_evaluate_eligibility($userid, $cid);
    $certrec = $certsbycourse[$cid] ?? null;

    $checklistrows[] = (object)[
        'courseid'      => $cid,
        'shortname'     => $mc->shortname,
        'fullname'      => $mc->fullname,
        'atttaken'      => $eval->atttaken,
        'attpresent'    => $eval->attpresent,
        'attpct'        => $eval->attpct,
        'attok'         => $eval->attok,
        'quizpct'       => $eval->quizpct,
        'quizok'        => $eval->quizok,
        'rubricscore'   => $eval->rubricscore,
        'hascheckin'    => $eval->hascheckin,
        'rubricok'      => $eval->rubricok,
        'allok'         => $eval->eligible,
        'cert'          => $certrec,
        'courseurl'     => (new moodle_url('/course/view.php', ['id' => $cid]))->out(false),
        'transcripturl' => (new moodle_url('/local/mamxanhapp/transcript.php', ['courseid' => $cid]))->out(false),
        'feedbackurl'   => (new moodle_url('/local/mamxanhapp/feedback_sheet.php', ['courseid' => $cid]))->out(false),
    ];
}

$roletype = local_mamxanhapp_user_role_type();
$backurl = ($roletype === 'cbkt' || $roletype === 'admin')
    ? new moodle_url('/local/mamxanhapp/manage.php')
    : new moodle_url('/local/mamxanhapp/index.php');

echo $OUTPUT->header();
?>

<div class="mx-cert-list">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:10px;">
        <a href="<?php echo $backurl->out(false); ?>" class="btn btn-secondary">
            <?php echo s(local_mamxanhapp_t('← Quay lại Dashboard', '← Back to Dashboard')); ?>
        </a>
        <div class="d-flex flex-wrap" style="gap:8px;">
            <a href="<?php echo (new moodle_url('/local/mamxanhapp/transcript.php'))->out(false); ?>" class="btn btn-secondary">
                <?php echo s(local_mamxanhapp_t('Bảng điểm Học thuật (In A4)', 'Academic Transcript (A4)')); ?>
            </a>
            <a href="<?php echo (new moodle_url('/local/mamxanhapp/feedback_sheet.php'))->out(false); ?>" class="btn btn-secondary">
                <?php echo s(local_mamxanhapp_t('Phiếu Chấm Rubric', 'Rubric Feedback Sheet')); ?>
            </a>
            <a href="<?php echo (new moodle_url('/local/mamxanhcert/verify.php'))->out(false); ?>" class="btn btn-primary">
                <?php echo s(local_mamxanhapp_t('Cổng Xác thực QR Công khai', 'Public QR Verification Portal')); ?>
            </a>
        </div>
    </div>

    <!-- A+ FEATURE 4: 3-STEP CERTIFICATION ELIGIBILITY CHECKLIST -->
    <section class="mx-card p-4 mb-4" aria-label="<?php echo s(local_mamxanhapp_t('Bảng Kiểm tra Điều kiện Cấp Chứng nhận', 'Certification Eligibility Checklist')); ?>">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:10px;">
            <div>
                <h3 class="h4 fw-bold mb-1" style="color:#234E3E;">
                    <?php echo s(local_mamxanhapp_t('Bảng Kiểm tra Điều kiện Hoàn thành Khóa học & Mở khóa Chứng nhận QR', 'Course Completion Criteria & QR Certificate Eligibility Checklist')); ?>
                </h3>
                <p class="small text-muted mb-0">
                    <?php echo s(local_mamxanhapp_t(
                        'Chứng nhận số HMAC-SHA256 được tự động mở khóa khi học viên hoàn thành đủ 03 điều kiện học thuật: (1) Điểm danh Lý thuyết & Thực hành ≥ 80%, (2) Bài kiểm tra Cuối khóa ≥ 70%, và (3) Bài thu hoạch thực hành đã chấm Rubric.',
                        'HMAC-SHA256 Digital QR Certificates unlock automatically upon meeting 3 academic criteria: (1) Theory & Practical Attendance >= 80%, (2) Final Quiz >= 70%, and (3) Graded Practical Rubric.'
                    )); ?>
                </p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="generaltable table-hover align-middle mb-0" style="font-size:0.9rem;">
                <thead>
                    <tr style="background:#EAF2EE;color:#234E3E;">
                        <th><?php echo s(local_mamxanhapp_t('Học phần Đào tạo', 'Training Course')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('1. Điểm danh (≥80%)', '1. Attendance (>=80%)')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('2. Trắc nghiệm (≥70%)', '2. Final Quiz (>=70%)')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('3. Rubric Thực hành', '3. Practical Rubric')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('Trạng thái & Hồ sơ Học thuật', 'Status & Academic Records')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($checklistrows as $row): ?>
                    <tr>
                        <td>
                            <a href="<?php echo $row->courseurl; ?>" class="fw-bold" style="color:#234E3E;text-decoration:none;">
                                <?php echo s($row->shortname); ?>
                            </a>
                            <div class="small text-muted"><?php echo s($row->fullname); ?></div>
                        </td>
                        <td>
                            <?php if ($row->attpct !== null): ?>
                            <span class="badge" style="background:<?php echo $row->attok ? '#EAF2EE' : '#FEF3C7'; ?>;color:<?php echo $row->attok ? '#234E3E' : '#92400E'; ?>;border:1px solid #CBD5E1;">
                                <?php echo s(($row->attok ? '[ĐẠT] ' : '[CHƯA ĐẠT] ') . number_format((float)$row->attpct, 0) . '% (' . $row->attpresent . '/' . $row->atttaken . ')'); ?>
                            </span>
                            <?php else: ?>
                            <span class="badge" style="background:#F1F5F9;color:#64748B;border:1px solid #CBD5E1;">
                                <?php echo s(local_mamxanhapp_t('Chưa điểm danh', 'No sessions taken')); ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row->quizpct !== null): ?>
                            <span class="badge" style="background:<?php echo $row->quizok ? '#EAF2EE' : '#FEF3C7'; ?>;color:<?php echo $row->quizok ? '#234E3E' : '#92400E'; ?>;border:1px solid #CBD5E1;">
                                <?php echo s(($row->quizok ? '[ĐẠT] ' : '[CHƯA ĐẠT] ') . number_format((float)$row->quizpct, 1) . '%'); ?>
                            </span>
                            <?php else: ?>
                            <span class="badge" style="background:#F1F5F9;color:#64748B;border:1px solid #CBD5E1;">
                                <?php echo s(local_mamxanhapp_t('Chưa thi', 'Not taken')); ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row->rubricscore !== null): ?>
                            <span class="badge" style="background:<?php echo $row->rubricok ? '#EAF2EE' : '#FEF3C7'; ?>;color:<?php echo $row->rubricok ? '#234E3E' : '#92400E'; ?>;border:1px solid #CBD5E1;">
                                <?php echo s(($row->rubricok ? '[ĐẠT] ' : '[CHƯA ĐẠT] ') . number_format((float)$row->rubricscore, 0) . '/100'); ?>
                            </span>
                            <?php elseif (!empty($row->hascheckin)): ?>
                            <span class="badge" style="background:#FFF8E1;color:#92400E;border:1px solid #CBD5E1;">
                                <?php echo s(local_mamxanhapp_t('Chờ CBKT chấm', 'Awaiting grading')); ?>
                            </span>
                            <?php else: ?>
                            <span class="badge" style="background:#F1F5F9;color:#64748B;border:1px solid #CBD5E1;">
                                <?php echo s(local_mamxanhapp_t('Đang chờ nộp', 'Pending')); ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap align-items-center" style="gap:6px;">
                                <?php if ($row->cert): ?>
                                <a href="<?php echo (new moodle_url('/local/mamxanhcert/view.php', ['code' => $row->cert->cert_code]))->out(false); ?>" class="btn btn-sm btn-primary">
                                    <?php echo s(local_mamxanhapp_t('Đã mở khóa QR', 'QR Unlocked')); ?>
                                </a>
                                <?php else: ?>
                                <a href="<?php echo $row->courseurl; ?>" class="btn btn-sm btn-secondary">
                                    <?php echo s(local_mamxanhapp_t('Vào hoàn thành', 'Complete Course')); ?>
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo $row->transcripturl; ?>" class="btn btn-sm btn-secondary">
                                    <?php echo s(local_mamxanhapp_t('Bảng điểm', 'Transcript')); ?>
                                </a>
                                <a href="<?php echo $row->feedbackurl; ?>" class="btn btn-sm btn-secondary">
                                    <?php echo s(local_mamxanhapp_t('Rubric', 'Rubric')); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php if (empty($certs)): ?>
    <div class="mx-card p-5 text-center">
        <h4 class="h4 mb-2"><?php echo s(local_mamxanhapp_t('Chưa có chứng nhận nào được mở khóa', 'No Digital Certificates Unlocked Yet')); ?></h4>
        <p class="text-muted" style="max-width:520px;margin:0 auto 18px;">
            <?php echo s(local_mamxanhapp_t(
                'Hãy hoàn thành bài kiểm tra trắc nghiệm cuối khóa đạt từ 70% trở lên để hệ thống tự động cấp Chứng nhận số ký HMAC-SHA256 kèm mã QR.',
                'Score 70% or higher on any course Quiz to automatically unlock your HMAC-SHA256 signed QR Certificate.'
            )); ?>
        </p>
        <a href="<?php echo (new moodle_url('/local/mamxanhapp/index.php'))->out(false); ?>" class="btn btn-primary">
            <?php echo s(local_mamxanhapp_t('Vào học & Làm bài Kiểm tra ngay', 'Go to Courses & Take Quiz')); ?>
        </a>
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($certs as $c):
            $verifyurl = (new moodle_url('/local/mamxanhcert/verify.php', ['code' => $c->cert_code]))->out(false);
            $viewurl = (new moodle_url('/local/mamxanhcert/view.php', ['code' => $c->cert_code]))->out(false);
            $pdfurl = (new moodle_url('/local/mamxanhcert/view.php', ['code' => $c->cert_code, 'download' => 'pdf']))->out(false);
            $transcripturl = (new moodle_url('/local/mamxanhapp/transcript.php', ['courseid' => $c->courseid, 'userid' => $c->userid]))->out(false);
            $feedbackurl = (new moodle_url('/local/mamxanhapp/feedback_sheet.php', ['courseid' => $c->courseid, 'userid' => $c->userid]))->out(false);
            $qrsvg = \local_mamxanhapp\qr_helper::render_svg($verifyurl, 130, '#234E3E');
        ?>
        <div class="col-12 col-xl-6 mb-4">
            <div class="mx-card p-4 h-100">
                <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap:16px;">
                    <div style="flex:1;min-width:210px;">
                        <span class="badge mb-2" style="background:#EAF2EE;color:#234E3E;border:1px solid #E6ECE9;font-size:0.8rem;font-weight:700;">
                            <?php echo s($c->cert_code); ?>
                        </span>
                        <h4 class="h4 mb-2">
                            <?php echo format_string($c->coursename); ?>
                        </h4>
                        <div class="small mb-1">
                            <strong><?php echo s($c->firstname . ' ' . $c->lastname); ?></strong> •
                            <?php echo s(local_mamxanhapp_t('Điểm đạt: ', 'Score: ')); ?><strong><?php echo number_format((float)$c->final_score, 1); ?>%</strong>
                        </div>
                        <div class="small text-muted mb-3">
                            HMAC-SHA256: <code><?php echo s(substr($c->hmac_signature, 0, 20) . '...'); ?></code>
                        </div>

                        <div class="d-flex flex-wrap" style="gap:8px;">
                            <a href="<?php echo $viewurl; ?>" class="btn btn-primary btn-sm">
                                <?php echo s(local_mamxanhapp_t('Xem Chứng nhận', 'View Certificate')); ?>
                            </a>
                            <a href="<?php echo $pdfurl; ?>" class="btn btn-secondary btn-sm">
                                PDF
                            </a>
                            <a href="<?php echo $verifyurl; ?>" class="btn btn-secondary btn-sm">
                                <?php echo s(local_mamxanhapp_t('Xác thực QR', 'Verify QR')); ?>
                            </a>
                            <a href="<?php echo $transcripturl; ?>" class="btn btn-secondary btn-sm">
                                <?php echo s(local_mamxanhapp_t('Bảng điểm', 'Transcript')); ?>
                            </a>
                            <a href="<?php echo $feedbackurl; ?>" class="btn btn-secondary btn-sm">
                                <?php echo s(local_mamxanhapp_t('Rubric', 'Rubric')); ?>
                            </a>
                        </div>
                    </div>
                    <div class="text-center">
                        <?php echo $qrsvg; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php
echo $OUTPUT->footer();
