<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

require_course_login($course);

$PAGE->set_url('/mod/fieldpractice/index.php', ['id' => $id]);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(format_string($course->shortname) . ': ' . get_string('modulenameplural', 'fieldpractice'));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'fieldpractice'));

$instances = get_all_instances_in_course('fieldpractice', $course);
if (empty($instances)) {
    notice(get_string('thereareno', 'moodle', get_string('modulenameplural', 'fieldpractice')), new moodle_url('/course/view.php', ['id' => $course->id]));
}

echo '<div class="list-group">';
foreach ($instances as $inst) {
    $url = new moodle_url('/mod/fieldpractice/view.php', ['id' => $inst->coursemodule]);
    echo '<a class="list-group-item list-group-item-action fw-bold" style="min-height:52px;display:flex;align-items:center;" href="' . $url->out(false) . '">' . format_string($inst->name) . '</a>';
}
echo '</div>';

echo $OUTPUT->footer();
