<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');

local_mamxanhapp_handle_lang_switch();

$id = required_param('id', PARAM_INT); // Course Module ID
$cm = get_coursemodule_from_id('fieldpractice', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$fp = $DB->get_record('fieldpractice', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, false, $cm);
$context = context_module::instance($cm->id);

// Enforce strict 403 RBAC check for grading
local_mamxanhapp_require_capability_or_403(
    'mod/fieldpractice:grade',
    $context,
    'Chức năng Chấm điểm Rubric Thực hành Vườn chỉ dành cho Cán bộ kỹ thuật và Quản trị viên.',
    'Garden Field Practice Rubric Grading is restricted to Technical Officers (CBKT) and Administrators.'
);

$savedmsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && optional_param('action', '', PARAM_ALPHA) === 'saverubric') {
    require_sesskey();
    $learnerid = required_param('learnerid', PARAM_INT);
    $c1 = required_param('crit_prep', PARAM_INT);
    $c2 = required_param('crit_tech', PARAM_INT);
    $c3 = required_param('crit_biosafe', PARAM_INT);
    $c4 = required_param('crit_quality', PARAM_INT);
    $feedback = optional_param('feedback', '', PARAM_TEXT);

    $saved = fieldpractice_save_rubric_grade($fp, $learnerid, $c1, $c2, $c3, $c4, $feedback, (int)$USER->id);
    $lu = $DB->get_record('user', ['id' => $learnerid]);
    $savedmsg = local_mamxanhapp_t(
        'Đã chấm Rubric cho ' . ($lu ? fullname($lu) : ('User #' . $learnerid)) . ' đạt ' . number_format((float)$saved->total_grade, 1) . '/100 điểm và đồng bộ thẳng vào Gradebook!',
        'Saved Rubric score (' . number_format((float)$saved->total_grade, 1) . '/100) and synced to Moodle Gradebook!'
    );
}

// Fetch enrolled learners in course (exclude admin/cbkt)
$enrolled = get_enrolled_users($context, '', 0, 'u.id,u.username,u.firstname,u.lastname,u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename,u.email');
$syscontext = context_system::instance();
$learners = [];
foreach ($enrolled as $u) {
    if (is_siteadmin($u->id) || has_capability('local/mamxanhapp:manage', $syscontext, $u->id)) {
        continue;
    }
    $learners[$u->id] = $u;
}
if (empty($learners)) {
    $fallback = $DB->get_records_select('user', "deleted = 0 AND username NOT IN ('guest','admin','cbkt_pilot')", null, 'id ASC');
    foreach ($fallback as $u) {
        $learners[$u->id] = $u;
    }
}

$subs = $DB->get_records('fieldpractice_sub', ['fieldpracticeid' => $fp->id]);
$subsbyuser = [];
foreach ($subs as $s) {
    $subsbyuser[(int)$s->userid] = $s;
}

$PAGE->set_url('/mod/fieldpractice/grade.php', ['id' => $cm->id]);
$PAGE->set_context($context);
$PAGE->set_title('Rubric: ' . format_string($fp->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
?>

<div class="mx-rubric-grading">
    <div class="d-flex flex-wrap justify-content-between mb-3" style="gap:10px;">
        <a href="<?php echo (new moodle_url('/mod/fieldpractice/view.php', ['id' => $cm->id]))->out(false); ?>" class="btn btn-outline-success" style="min-height:48px;display:inline-flex;align-items:center;border-radius:14px;font-weight:700;border-width:2px;">
            ← <?php echo s(local_mamxanhapp_t('Quay lại trang Thực hành', 'Back to Activity')); ?>
        </a>
        <a href="<?php echo (new moodle_url('/local/mamxanhapp/manage.php'))->out(false); ?>" class="btn btn-success" style="min-height:48px;display:inline-flex;align-items:center;border-radius:14px;font-weight:700;background:#1B4332;">
            <?php echo s(local_mamxanhapp_t('Về Trung tâm Báo cáo CBKT', 'Instructor Analytics Hub')); ?>
        </a>
    </div>

    <?php if ($savedmsg !== ''): ?>
    <div class="alert alert-success" id="mx-rubric-saved-alert" style="border-radius:16px;font-weight:800;border:2px solid #2E7D32;">
        <?php echo s($savedmsg); ?>
    </div>
    <?php endif; ?>

    <div style="background:#FFF;border:2px solid #DCEEE0;border-radius:24px;padding:24px;">
        <h3 style="color:#1B4332;font-weight:800;margin-bottom:6px;">
            <?php echo s(local_mamxanhapp_t('Chấm điểm Thực hành Tại vườn theo Rubric 4 Tiêu chí', '4-Criteria Field Practice Rubric Evaluation')); ?>
        </h3>
        <p style="color:#4E6358;margin-bottom:18px;">
            <strong><?php echo format_string($fp->name); ?></strong> — <?php echo s(local_mamxanhapp_t('Mỗi tiêu chí tối đa 25 điểm (Tổng 100 điểm, tự động ghi thẳng vào Sổ điểm Gradebook).', 'Each criterion is worth 0–25 pts (Total 100 pts, pushed directly to Moodle Gradebook).')); ?>
        </p>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead style="background:#E8F5E9;color:#1B4332;">
                    <tr>
                        <th><?php echo s(local_mamxanhapp_t('Học viên Nông dân & GPS', 'Farmer & Check-in GPS')); ?></th>
                        <th style="min-width:105px;">TC1: Chuẩn bị (0-25)</th>
                        <th style="min-width:105px;">TC2: Kỹ thuật (0-25)</th>
                        <th style="min-width:105px;">TC3: Sinh học (0-25)</th>
                        <th style="min-width:105px;">TC4: Kết quả (0-25)</th>
                        <th style="min-width:190px;"><?php echo s(local_mamxanhapp_t('Nhận xét CBKT', 'Feedback')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('Thao tác', 'Action')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($learners as $uid => $lu):
                        $sub = $subsbyuser[$uid] ?? null;
                        $formid = 'mx-rubric-form-' . $uid;
                    ?>
                    <tr>
                        <td>
                            <form id="<?php echo $formid; ?>" method="post" action="<?php echo (new moodle_url('/mod/fieldpractice/grade.php', ['id' => $cm->id]))->out(false); ?>">
                                <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                                <input type="hidden" name="action" value="saverubric">
                                <input type="hidden" name="learnerid" value="<?php echo (int)$uid; ?>">
                            </form>
                            <strong><?php echo s(fullname($lu)); ?></strong> (<code><?php echo s($lu->username); ?></code>)<br>
                            <?php if ($sub && !empty($sub->checkin_time)): ?>
                                <small class="text-success fw-bold">GPS: <?php echo s($sub->gps_lat . ',' . $sub->gps_lng); ?></small>
                            <?php else: ?>
                                <small class="text-muted">Chấm trực tiếp hiện trường</small>
                            <?php endif; ?>
                            <?php if ($sub && $sub->total_grade !== null): ?>
                                <div><span class="badge bg-success"><?php echo number_format((float)$sub->total_grade, 0); ?>/100</span></div>
                            <?php endif; ?>
                        </td>
                        <td><input form="<?php echo $formid; ?>" type="number" name="crit_prep" class="form-control" min="0" max="25" value="<?php echo $sub ? (int)$sub->crit_prep : 22; ?>" style="min-height:44px;font-weight:700;"></td>
                        <td><input form="<?php echo $formid; ?>" type="number" name="crit_tech" class="form-control" min="0" max="25" value="<?php echo $sub ? (int)$sub->crit_tech : 23; ?>" style="min-height:44px;font-weight:700;"></td>
                        <td><input form="<?php echo $formid; ?>" type="number" name="crit_biosafe" class="form-control" min="0" max="25" value="<?php echo $sub ? (int)$sub->crit_biosafe : 24; ?>" style="min-height:44px;font-weight:700;"></td>
                        <td><input form="<?php echo $formid; ?>" type="number" name="crit_quality" class="form-control" min="0" max="25" value="<?php echo $sub ? (int)$sub->crit_quality : 22; ?>" style="min-height:44px;font-weight:700;"></td>
                        <td><input form="<?php echo $formid; ?>" type="text" name="feedback" class="form-control" value="<?php echo s($sub->feedback ?? 'Thao tác pha dinh dưỡng và an toàn sinh học đúng chuẩn VietGAP.'); ?>" style="min-height:44px;"></td>
                        <td>
                            <button form="<?php echo $formid; ?>" type="submit" class="btn btn-success mx-save-rubric-btn" data-username="<?php echo s($lu->username); ?>" style="min-height:46px;border-radius:12px;font-weight:800;white-space:nowrap;background:#1B4332;">
                                <?php echo s(local_mamxanhapp_t('Lưu điểm', 'Save')); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
echo $OUTPUT->footer();
