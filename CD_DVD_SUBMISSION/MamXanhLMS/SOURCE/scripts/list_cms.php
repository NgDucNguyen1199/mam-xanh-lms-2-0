<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');

$courses = [2, 3, 8];
foreach ($courses as $cid) {
    echo "=== Course $cid ===\n";
    $cms = $DB->get_records('course_modules', ['course' => $cid], 'id ASC');
    foreach ($cms as $cm) {
        $mod = $DB->get_record('modules', ['id' => $cm->module]);
        $inst = $DB->get_record($mod->name, ['id' => $cm->instance]);
        echo "CM {$cm->id} | Mod: {$mod->name} | Section: {$cm->section} | Comp: {$cm->completion} | Name: {$inst->name}\n";
    }
}
