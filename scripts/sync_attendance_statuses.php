<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');
require_once($CFG->dirroot . '/mod/attendance/lib.php');
require_once($CFG->dirroot . '/mod/attendance/locallib.php');

$atts = $DB->get_records('attendance');
foreach ($atts as $a) {
    echo "Processing Attendance ID {$a->id}: {$a->name}\n";
    $statuses = $DB->get_records('attendance_statuses', ['attendanceid' => $a->id]);
    if (empty($statuses)) {
        att_add_default_statuses($a->id);
        echo "  -> Added default statuses for attendance {$a->id}\n";
    }

    // Update descriptions and grades for each status set
    $cur_statuses = $DB->get_records('attendance_statuses', ['attendanceid' => $a->id]);
    foreach ($cur_statuses as $st) {
        if ($st->acronym === 'P') {
            $st->description = 'Có mặt';
            $st->grade = 2.0;
        } else if ($st->acronym === 'L') {
            $st->description = 'Đi trễ';
            $st->grade = 1.0;
        } else if ($st->acronym === 'E') {
            $st->description = 'Vắng có phép';
            $st->grade = 1.0;
        } else if ($st->acronym === 'A') {
            $st->description = 'Vắng không phép';
            $st->grade = 0.0;
        }
        $DB->update_record('attendance_statuses', $st);
    }

    $st_p = $DB->get_record('attendance_statuses', ['attendanceid' => $a->id, 'acronym' => 'P']);
    $st_l = $DB->get_record('attendance_statuses', ['attendanceid' => $a->id, 'acronym' => 'L']);

    // Update existing logs for this attendance
    $sessions = $DB->get_records('attendance_sessions', ['attendanceid' => $a->id]);
    foreach ($sessions as $sess) {
        $logs = $DB->get_records('attendance_log', ['sessionid' => $sess->id]);
        foreach ($logs as $log) {
            $user = $DB->get_record('user', ['id' => $log->studentid]);
            if ($user && $user->username === 'nd_saurieng_daklak') {
                $log->statusid = $st_l ? $st_l->id : $st_p->id;
            } else {
                $log->statusid = $st_p->id;
            }
            $DB->update_record('attendance_log', $log);
        }
    }
}
echo "All attendance statuses and logs synchronized successfully.\n";
