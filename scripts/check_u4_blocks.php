<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');

$all = $DB->get_records('attendance_statuses');
echo "ATTENDANCE STATUSES:\n";
foreach ($all as $st) {
    echo "ID: {$st->id} | AID: {$st->attendanceid} | Acronym: {$st->acronym} | Desc: {$st->description} | Grade: {$st->grade}\n";
}

$atts = $DB->get_records('attendance');
echo "\nATTENDANCE INSTANCES:\n";
foreach ($atts as $a) {
    echo "ID: {$a->id} | Course: {$a->course} | Name: {$a->name}\n";
}
