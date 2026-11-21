<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');

$cm132 = $DB->get_record('course_modules', ['id' => 132]);
echo "CM 132:\n";
print_r($cm132);

// Check assign table for CM 132
$mod = $DB->get_record('modules', ['id' => $cm132->module]);
$assign132 = $DB->get_record('assign', ['id' => $cm132->instance]);
echo "Assign 132:\n";
print_r($assign132);
