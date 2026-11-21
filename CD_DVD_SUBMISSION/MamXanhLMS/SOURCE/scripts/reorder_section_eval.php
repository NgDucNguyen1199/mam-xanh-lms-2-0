<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');
require_once($CFG->dirroot . '/course/lib.php');

$courses = [2, 3, 8];
foreach ($courses as $cid) {
    $sec = $DB->get_record_select('course_sections', "course = ? AND name = 'Kiểm tra & Đánh giá'", [$cid]);
    if (!$sec) continue;

    // Find modules
    $att_lt = $DB->get_record('attendance', ['course' => $cid, 'name' => 'Điểm danh Lý thuyết']);
    $att_th = $DB->get_record('attendance', ['course' => $cid, 'name' => 'Điểm danh Thực hành']);
    $as_t1  = $DB->get_record('assign', ['course' => $cid, 'name' => 'Thảo luận tại lớp buổi 1']);
    $as_t2  = $DB->get_record('assign', ['course' => $cid, 'name' => 'Thảo luận tại lớp buổi 2']);
    $as_th  = $DB->get_record('assign', ['course' => $cid, 'name' => 'Bài tập thực hành: Pha chế dung dịch dinh dưỡng A-B & cân bằng pH/EC']);
    $as_hv  = $DB->get_record('assign', ['course' => $cid, 'name' => 'Bài thu hoạch cuối khóa: Đề án Chuyển đổi Canh tác Số & VietGAP']);

    $mod_att = $DB->get_record('modules', ['name' => 'attendance']);
    $mod_as  = $DB->get_record('modules', ['name' => 'assign']);

    $ordered_cmids = [];
    $order = [
        [$mod_att->id, $att_lt->id],
        [$mod_att->id, $att_th->id],
        [$mod_as->id,  $as_t1->id],
        [$mod_as->id,  $as_t2->id],
        [$mod_as->id,  $as_th->id],
        [$mod_as->id,  $as_hv->id]
    ];

    foreach ($order as $pair) {
        $cm = $DB->get_record('course_modules', ['course' => $cid, 'module' => $pair[0], 'instance' => $pair[1]]);
        if ($cm) {
            $ordered_cmids[] = $cm->id;
        }
    }

    $sec->sequence = implode(',', $ordered_cmids);
    $DB->update_record('course_sections', $sec);
    rebuild_course_cache($cid, true);
    echo "Reordered section 'Kiểm tra & Đánh giá' for Course {$cid}: " . implode(',', $ordered_cmids) . "\n";
}
