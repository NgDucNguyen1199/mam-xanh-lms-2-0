<?php
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->dirroot . '/course/lib.php');

echo "=== [1/4] Fixing UTF-8 in mdl_fieldpractice & mdl_fieldpractice_sub ===\n";

$standardIntro = '<p>Trạm thực hành hiện trường chuẩn VietGAP TCVN 11892-1:2017: Học viên quét mã QR xoay vòng 5 phút tại nhà màng, xác nhận tọa độ định vị GPS và nhận kết quả chấm điểm theo ma trận Rubric 4 tiêu chí (100 điểm) đồng bộ trực tiếp vào Sổ điểm Gradebook.</p>';

$locationByCourse = [
    19 => 'Nhà màng Dâu tây Công nghệ cao Phường 7, Đà Lạt',
    21 => 'Nhà màng Dâu tây Hợp tác xã Đơn Dương, Lâm Đồng',
];

$fps = $DB->get_records('fieldpractice');
foreach ($fps as $fp) {
    $course = $DB->get_record('course', ['id' => $fp->course], 'id,shortname');
    $short = $course ? $course->shortname : ('COURSE-' . $fp->course);
    $changed = false;

    if (strpos((string)$fp->name, '??') !== false || (int)$fp->course === 19 || (int)$fp->course === 21) {
        $fp->name = "Thực hành Tại vườn: Điểm danh QR & Rubric 4 Tiêu chí ({$short})";
        $changed = true;
    }
    if (isset($locationByCourse[(int)$fp->course]) || strpos((string)$fp->location_name, '??') !== false) {
        $fp->location_name = $locationByCourse[(int)$fp->course] ?? 'Vườn Thực nghiệm Công nghệ cao Lâm Đồng';
        $changed = true;
    }
    if (strpos((string)$fp->intro, '??') !== false || (int)$fp->course === 19 || (int)$fp->course === 21) {
        $fp->intro = $standardIntro;
        $changed = true;
    }
    if ($changed) {
        $DB->update_record('fieldpractice', $fp);
        echo "  + Fixed fieldpractice #{$fp->id} (course={$fp->course}, shortname={$short})\n";
    }
}

$subs = $DB->get_records('fieldpractice_sub');
foreach ($subs as $sub) {
    $changed = false;
    if (strpos((string)$sub->feedback, '??') !== false) {
        $sub->feedback = 'Thao tác hiệu chuẩn EC/pH, vận hành tưới nhỏ giọt và thực hành an toàn sinh học IPM đạt chuẩn VietGAP.';
        $changed = true;
    }
    if (strpos((string)$sub->checkin_note, '??') !== false) {
        $sub->checkin_note = 'Đã kiểm tra hệ thống tưới nhỏ giọt bù áp, đo EC/pH và treo bẫy dính sinh học nhà màng.';
        $changed = true;
    }
    if ($changed) {
        $DB->update_record('fieldpractice_sub', $sub);
        echo "  + Fixed fieldpractice_sub #{$sub->id}\n";
    }
}

echo "=== [2/4] Restoring UTF-8 on Course 2/3/21 records touched earlier ===\n";
if ($p3 = $DB->get_record('page', ['id' => 3])) {
    if (strpos((string)$p3->name, '??') !== false) {
        $p3->name = 'Cẩm nang Chẩn đoán Triệu chứng Sâu bệnh Sinh học IPM & Thực hành QR VietGAP';
        $DB->update_record('page', $p3);
        echo "  + Fixed page #3\n";
    }
}
if ($p6 = $DB->get_record('page', ['id' => 6])) {
    if (strpos((string)$p6->name, '??') !== false) {
        $p6->name = 'Quy chuẩn Đóng gói Chuỗi Lạnh, Tem Truy xuất QR & Kiểm soát Mối nguy HACCP';
        $DB->update_record('page', $p6);
        echo "  + Fixed page #6\n";
    }
}
if ($a9 = $DB->get_record('assign', ['id' => 9])) {
    if (strpos((string)$a9->name, '??') !== false) {
        $a9->name = 'Bài tập thực hành: Thiết kế quy trình làm mát sơ bộ Pre-cooling & đóng gói MAP';
        $DB->update_record('assign', $a9);
        echo "  + Fixed assign #9\n";
    }
}
if ($q18 = $DB->get_record('quiz', ['id' => 18])) {
    if (strpos((string)$q18->name, '??') !== false) {
        $q18->name = 'Kiểm tra Trắc nghiệm Cuối khóa (10 Câu hỏi) - DAUTAY-DONDUONG-01';
        $DB->update_record('quiz', $q18);
        echo "  + Fixed quiz #18\n";
    }
}

echo "=== [3/4] Scanning all key Vietnamese text tables for any remaining '??' ===\n";
$checks = [
    ['course', ['fullname', 'shortname', 'summary']],
    ['course_sections', ['name', 'summary']],
    ['page', ['name', 'intro', 'content']],
    ['quiz', ['name', 'intro']],
    ['assign', ['name', 'intro']],
    ['fieldpractice', ['name', 'intro', 'location_name']],
    ['fieldpractice_sub', ['feedback', 'checkin_note']],
];

$totalBroken = 0;
foreach ($checks as [$table, $cols]) {
    $rows = $DB->get_records($table);
    foreach ($rows as $row) {
        foreach ($cols as $col) {
            if (isset($row->$col) && strpos((string)$row->$col, '??') !== false) {
                $totalBroken++;
                echo "  [FOUND ??] {$table}#{$row->id}.{$col}: " . mb_substr((string)$row->$col, 0, 80) . "\n";
            }
        }
    }
}
echo "  Total remaining '??' records: {$totalBroken}\n";

echo "=== [4/4] Rebuilding all course caches ===\n";
$allcourses = $DB->get_records('course', null, 'id ASC', 'id');
foreach ($allcourses as $c) {
    rebuild_course_cache($c->id, true);
}
echo "Done!\n";
