<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');

$cmids = [136, 137, 142, 143, 148, 149];
foreach ($cmids as $cmid) {
    $cm = $DB->get_record('course_modules', ['id' => $cmid]);
    if ($cm) {
        $cm->showdescription = 1;
        $DB->update_record('course_modules', $cm);
        echo "Enabled showdescription for CM {$cmid}\n";
    }
}

foreach ([2, 3, 8] as $cid) {
    rebuild_course_cache($cid, true);
}
echo "Caches rebuilt.\n";
