<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');

$langdir = $CFG->dataroot . '/lang/vi';
if (!is_dir($langdir)) {
    mkdir($langdir, 0777, true);
}

// 1. Update or create course.php
$course_file = $langdir . '/course.php';
$course_strings = file_exists($course_file) ? file_get_contents($course_file) : "<?php\n";
if (strpos($course_strings, 'activitydate:opened') === false) {
    $additions = "
\$string['activitydate:closed'] = 'Đã đóng:';
\$string['activitydate:closes'] = 'Đóng:';
\$string['activitydate:opened'] = 'Đã mở:';
\$string['activitydate:opens'] = 'Mở:';
";
    file_put_contents($course_file, $course_strings . $additions);
    echo "Added course.php activitydate strings in Vietnamese.\n";
}

// 2. Update or create assign.php
$assign_file = $langdir . '/assign.php';
$assign_strings = file_exists($assign_file) ? file_get_contents($assign_file) : "<?php\n";
if (strpos($assign_strings, 'activitydate:submissionsdue') === false) {
    $additions = "
\$string['activitydate:submissionsdue'] = 'Hạn nộp:';
\$string['activitydate:submissionsopen'] = 'Mở:';
\$string['activitydate:submissionsopened'] = 'Đã mở:';
";
    file_put_contents($assign_file, $assign_strings . $additions);
    echo "Added assign.php activitydate strings in Vietnamese.\n";
}

echo "Language strings updated.\n";
