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
        <a href="<?php echo (new moodle_url('/mod/fieldpractice/view.php', ['id' => $cm->id]))->out(false); ?>" class="btn btn-secondary">
            ← <?php echo s(local_mamxanhapp_t('Quay lại trang Thực hành', 'Back to Activity')); ?>
        </a>
        <a href="<?php echo (new moodle_url('/local/mamxanhapp/manage.php'))->out(false); ?>" class="btn btn-primary" style="background:#234E3E;border-color:#234E3E;">
            <?php echo s(local_mamxanhapp_t('Về Trung tâm Báo cáo CBKT', 'Instructor Analytics Hub')); ?>
        </a>
    </div>

    <?php if ($savedmsg !== ''): ?>
    <div class="alert alert-success" id="mx-rubric-saved-alert" style="border-radius:10px;font-weight:700;border:1px solid #234E3E;">
        <?php echo s($savedmsg); ?>
    </div>
    <?php endif; ?>

    <div style="background:#FFF;border:1px solid #E6ECE9;border-radius:16px;padding:24px;">
        <h3 style="color:#234E3E;font-weight:700;margin-bottom:6px;">
            <?php echo s(local_mamxanhapp_t('Chấm điểm Thực hành Tại vườn theo Rubric 4 Tiêu chí', '4-Criteria Field Practice Rubric Evaluation')); ?>
        </h3>
        <p style="color:#52615B;margin-bottom:16px;">
            <strong><?php echo format_string($fp->name); ?></strong> — <?php echo s(local_mamxanhapp_t('Mỗi tiêu chí tối đa 25 điểm (Tổng 100 điểm, tự động ghi thẳng vào Sổ điểm Gradebook).', 'Each criterion is worth 0–25 pts (Total 100 pts, pushed directly to Moodle Gradebook).')); ?>
        </p>

        <?php
        $en = local_mamxanhapp_is_en();
        $firstlearner = reset($learners);
        $firstuid = $firstlearner ? (int)$firstlearner->id : 0;
        $quickchips = [
            [
                'label' => local_mamxanhapp_t('Đạt chuẩn VietGAP & pH/EC', 'VietGAP & pH/EC Compliant'),
                'text'  => local_mamxanhapp_t('Đạt chuẩn VietGAP: Kiểm soát đúng ngưỡng pH/EC, thao tác gọn và tuân thủ an toàn sinh học.', 'Meets VietGAP standard: accurate pH/EC calibration, clean execution, and proper biosafety.'),
            ],
            [
                'label' => local_mamxanhapp_t('Tuân thủ cách ly PHI', 'PHI Quarantine Compliant'),
                'text'  => local_mamxanhapp_t('Tuân thủ nghiêm ngặt thời gian cách ly PHI và ghi chép nhật ký đồng ruộng điện tử đầy đủ.', 'Strictly complies with PHI pre-harvest intervals and digital field diary logging.'),
            ],
            [
                'label' => local_mamxanhapp_t('Nhắc nhở hiệu chuẩn EC/Bảo hộ', 'Recalibrate EC / PPE Reminder'),
                'text'  => local_mamxanhapp_t('Cần hiệu chuẩn lại đầu đo EC/pH trước khi pha dung dịch gốc và mang đầy đủ trang bị bảo hộ.', 'Needs EC/pH probe recalibration before mixing stock tanks and full PPE compliance.'),
            ],
            [
                'label' => local_mamxanhapp_t('Theo dõi ẩm độ giá thể trưa', 'Monitor Midday Substrate Moisture'),
                'text'  => local_mamxanhapp_t('Thao tác tưới nhỏ giọt đều; lưu ý tăng nhịp kiểm tra ẩm độ giá thể vào khung giờ nắng gắt.', 'Even drip irrigation setup; monitor substrate moisture closely during midday heat.'),
            ],
            [
                'label' => local_mamxanhapp_t('Hộ điểm mẫu xuất sắc HTX', 'Exemplary Co-op Benchmark'),
                'text'  => local_mamxanhapp_t('Thao tác kỹ thuật xuất sắc, đủ điều kiện làm mô hình điểm hướng dẫn cho hội viên HTX.', 'Exemplary practical execution; qualified to serve as a demonstration benchmark for co-op peers.'),
            ],
        ];
        ?>

        <!-- 1-Tap VietGAP Field Feedback Chips Bar (Mobile Friendly >= 44px) -->
        <div class="mx-one-tap-feedback-bar mb-3 p-3" style="background:#F9FAF9;border:1px solid #E6ECE9;border-radius:12px;">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap:10px;">
                <strong style="color:#234E3E;font-size:0.9rem;">
                    <?php echo s(local_mamxanhapp_t('Nhận xét mẫu chuẩn VietGAP 1-chạm (chèn nhanh trên điện thoại ngoài vườn):', '1-Tap Standardized VietGAP Feedback Templates (Mobile Field Grading):')); ?>
                </strong>
                <?php if (!empty($learners)): ?>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <label for="mx-active-learner-sel" class="mb-0 small fw-bold" style="color:#4A5552;">
                        <?php echo s(local_mamxanhapp_t('Học viên đang chọn:', 'Target learner:')); ?>
                    </label>
                    <select id="mx-active-learner-sel" class="form-select form-select-sm" style="min-height:44px;min-width:190px;border:1px solid #CFD9D4;border-radius:8px;font-weight:600;" onchange="mxSelectRubricLearner(Number(this.value))">
                        <?php foreach ($learners as $uid => $lu): ?>
                        <option value="<?php echo (int)$uid; ?>"><?php echo s(fullname($lu) . ' (' . $lu->username . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <div class="d-flex flex-wrap" style="gap:8px;">
                <?php foreach ($quickchips as $idx => $chip): ?>
                <button type="button"
                        class="btn btn-sm mx-rubric-chip"
                        data-feedback="<?php echo s($chip['text']); ?>"
                        onclick="mxInsertFeedbackChip(this)"
                        style="min-height:44px;padding:8px 14px;border-radius:999px;background:#EAF2EE;color:#234E3E;border:1px solid #CFD9D4;font-weight:600;">
                    + <?php echo s($chip['label']); ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div id="mx-chip-status" class="small fw-bold mt-2" style="color:#234E3E;min-height:18px;"></div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead style="background:#EAF2EE;color:#234E3E;">
                    <tr>
                        <th><?php echo s(local_mamxanhapp_t('Học viên Nông dân & GPS', 'Farmer & Check-in GPS')); ?></th>
                        <th style="min-width:105px;">TC1: <?php echo s(local_mamxanhapp_t('Chuẩn bị (0-25)', 'Prep (0-25)')); ?></th>
                        <th style="min-width:105px;">TC2: <?php echo s(local_mamxanhapp_t('Kỹ thuật (0-25)', 'Tech (0-25)')); ?></th>
                        <th style="min-width:105px;">TC3: <?php echo s(local_mamxanhapp_t('Sinh học (0-25)', 'BioSafe (0-25)')); ?></th>
                        <th style="min-width:105px;">TC4: <?php echo s(local_mamxanhapp_t('Thành phẩm (0-25)', 'Quality (0-25)')); ?></th>
                        <th style="min-width:210px;"><?php echo s(local_mamxanhapp_t('Nhận xét CBKT', 'Feedback')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('Thao tác', 'Action')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($learners as $uid => $lu):
                        $sub = $subsbyuser[$uid] ?? null;
                        $formid = 'mx-rubric-form-' . $uid;
                    ?>
                    <tr id="mx-rubric-row-<?php echo (int)$uid; ?>">
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
                                <small class="text-muted"><?php echo s(local_mamxanhapp_t('Chấm trực tiếp hiện trường', 'On-site field grading')); ?></small>
                            <?php endif; ?>
                            <?php if ($sub && $sub->total_grade !== null): ?>
                                <div><span class="badge bg-success"><?php echo number_format((float)$sub->total_grade, 0); ?>/100</span></div>
                            <?php endif; ?>
                        </td>
                        <td><input form="<?php echo $formid; ?>" type="number" name="crit_prep" aria-label="TC1 <?php echo s($lu->username); ?>" onfocus="mxSelectRubricLearner(<?php echo (int)$uid; ?>)" class="form-control" min="0" max="25" value="<?php echo $sub ? (int)$sub->crit_prep : 22; ?>" style="min-height:44px;font-weight:700;"></td>
                        <td><input form="<?php echo $formid; ?>" type="number" name="crit_tech" aria-label="TC2 <?php echo s($lu->username); ?>" onfocus="mxSelectRubricLearner(<?php echo (int)$uid; ?>)" class="form-control" min="0" max="25" value="<?php echo $sub ? (int)$sub->crit_tech : 23; ?>" style="min-height:44px;font-weight:700;"></td>
                        <td><input form="<?php echo $formid; ?>" type="number" name="crit_biosafe" aria-label="TC3 <?php echo s($lu->username); ?>" onfocus="mxSelectRubricLearner(<?php echo (int)$uid; ?>)" class="form-control" min="0" max="25" value="<?php echo $sub ? (int)$sub->crit_biosafe : 24; ?>" style="min-height:44px;font-weight:700;"></td>
                        <td><input form="<?php echo $formid; ?>" type="number" name="crit_quality" aria-label="TC4 <?php echo s($lu->username); ?>" onfocus="mxSelectRubricLearner(<?php echo (int)$uid; ?>)" class="form-control" min="0" max="25" value="<?php echo $sub ? (int)$sub->crit_quality : 22; ?>" style="min-height:44px;font-weight:700;"></td>
                        <td><input id="mx-feedback-<?php echo (int)$uid; ?>" form="<?php echo $formid; ?>" type="text" name="feedback" aria-label="Feedback <?php echo s($lu->username); ?>" onfocus="mxSelectRubricLearner(<?php echo (int)$uid; ?>)" class="form-control" value="<?php echo s($sub->feedback ?? 'Thao tác pha dinh dưỡng và an toàn sinh học đúng chuẩn VietGAP.'); ?>" style="min-height:44px;"></td>
                        <td>
                            <button form="<?php echo $formid; ?>" type="submit" class="btn btn-primary mx-save-rubric-btn" data-username="<?php echo s($lu->username); ?>" style="min-height:44px;min-width:44px;border-radius:8px;font-weight:600;white-space:nowrap;background:#234E3E;border-color:#234E3E;">
                                <?php echo s(local_mamxanhapp_t('Lưu điểm', 'Save')); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php echo fieldpractice_render_criteria_summary_html((int)$fp->id, $en); ?>
</div>

<script>
var mxActiveRubricUid = <?php echo (int)$firstuid; ?>;
function mxSelectRubricLearner(uid) {
    if (!uid) return;
    mxActiveRubricUid = uid;
    var sel = document.getElementById('mx-active-learner-sel');
    if (sel && Number(sel.value) !== Number(uid)) {
        sel.value = String(uid);
    }
    document.querySelectorAll('tr[id^="mx-rubric-row-"]').forEach(function(tr) {
        tr.style.outline = '';
    });
    var activeRow = document.getElementById('mx-rubric-row-' + uid);
    if (activeRow) {
        activeRow.style.outline = '2px solid #234E3E';
    }
}
function mxInsertFeedbackChip(btn) {
    var txt = btn ? (btn.getAttribute('data-feedback') || '') : '';
    var input = document.getElementById('mx-feedback-' + mxActiveRubricUid);
    if (!input || !txt) return;
    input.value = txt;
    input.focus();
    var status = document.getElementById('mx-chip-status');
    var sel = document.getElementById('mx-active-learner-sel');
    var targetLabel = sel && sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex].text : ('#' + mxActiveRubricUid);
    if (status) {
        status.textContent = (document.documentElement.lang && document.documentElement.lang.indexOf('en') === 0
            ? 'Inserted feedback template for '
            : 'Đã chèn nhận xét chuẩn VietGAP cho ') + targetLabel;
    }
}
</script>

<?php
echo $OUTPUT->footer();
