<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');

$courses = [2, 3, 8];
foreach ($courses as $cid) {
    $c = $DB->get_record('course', ['id' => $cid]);
    echo "Course {$cid} enablecompletion: {$c->enablecompletion}\n";
    if ($c->enablecompletion != 1) {
        $c->enablecompletion = 1;
        $DB->update_record('course', $c);
        rebuild_course_cache($cid, true);
        echo "  -> Enabled completion for Course {$cid}\n";
    }
}

// Check modules completion tracking in course 2
$cms = $DB->get_records_select('course_modules', 'course = 2 AND completion > 0');
echo "Course 2 modules with completion enabled: " . count($cms) . "\n";
foreach ($cms as $cm) {
    $mod = $DB->get_record('modules', ['id' => $cm->module]);
    $inst = $DB->get_record($mod->name, ['id' => $cm->instance]);
    echo "  - CM {$cm->id} ({$mod->name}): '{$inst->name}' completion={$cm->completion}\n";
}
