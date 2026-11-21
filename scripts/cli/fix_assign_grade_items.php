<?php
/**
 * CLI: Tạo Grade Item chuẩn Moodle cho mọi hoạt động mod_assign được seed trực tiếp vào CSDL
 * (nếu thiếu thì giảng viên không mở được bảng chấm điểm: "Cannot load the grade item"),
 * sau đó đồng bộ điểm đã chấm sang Gradebook.
 */
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/assign/lib.php');

global $DB;
$fixed = 0;
$assigns = $DB->get_records('assign', null, 'id ASC');
foreach ($assigns as $a) {
    $cm = get_coursemodule_from_instance('assign', $a->id, $a->course, false, IGNORE_MISSING);
    if (!$cm) {
        continue;
    }
    $a->cmidnumber = $cm->idnumber;
    $has = $DB->record_exists('grade_items', [
        'courseid' => $a->course, 'itemtype' => 'mod', 'itemmodule' => 'assign',
        'iteminstance' => $a->id, 'itemnumber' => 0,
    ]);
    if (!$has) {
        assign_grade_item_update($a);
        $fixed++;
        echo "[OK] Created grade item for assign id={$a->id} (course {$a->course}): {$a->name}\n";
    }
    assign_update_grades($a);
}
echo "Created {$fixed} missing assign grade items; grades re-synced to Gradebook.\n";
foreach ($DB->get_fieldset_select('course', 'id', 'id > 1') as $cid) {
    try {
        grade_regrade_final_grades((int)$cid);
    } catch (\Throwable $e) {
        echo "[WARN] Regrade skipped for course {$cid}: " . $e->getMessage() . "\n";
    }
}
echo "Done.\n";
