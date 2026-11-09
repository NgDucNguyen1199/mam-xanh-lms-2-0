<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');

$courses = [2, 3, 8];
foreach ($courses as $cid) {
    echo "Updating completion for Course $cid...\n";
    $cms = $DB->get_records('course_modules', ['course' => $cid]);
    foreach ($cms as $cm) {
        $mod = $DB->get_record('modules', ['id' => $cm->module]);
        if (in_array($mod->name, ['attendance', 'assign', 'quiz', 'h5pactivity', 'scorm', 'resource', 'page', 'fieldpractice'])) {
            if ($cm->completion == 0) {
                // If it's assign, let's give it completion = 2 with completionsubmit = 1, or completion = 1
                if ($mod->name === 'assign') {
                    $cm->completion = 1; // Manual or submit
                } else if ($mod->name === 'attendance') {
                    $cm->completion = 1; // Manual mark done or attendance taken
                }
                $DB->update_record('course_modules', $cm);
                echo "  CM {$cm->id} ({$mod->name}) set completion = {$cm->completion}\n";
            }
        }
    }
    rebuild_course_cache($cid, true);
}
echo "Done rebuilding course caches.\n";
