<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');

$courses = $DB->get_records_list('course', 'id', [2, 3, 8]);
foreach ($courses as $c) {
    echo "Course {$c->id} showactivitydates: {$c->showactivitydates}\n";
    if ($c->showactivitydates != 1) {
        $c->showactivitydates = 1;
        $DB->update_record('course', $c);
        rebuild_course_cache($c->id, true);
        echo "  -> Updated showactivitydates to 1 for Course {$c->id}\n";
    }
}
