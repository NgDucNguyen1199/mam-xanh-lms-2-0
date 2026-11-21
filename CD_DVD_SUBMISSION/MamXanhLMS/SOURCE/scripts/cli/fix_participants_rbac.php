<?php
/**
 * CLI Script: Revoke moodle/course:viewparticipants & moodle/site:viewparticipants
 * from the student (Nông hộ) role and ensure Technical Officers (cbkt_pilot)
 * retain full participant management permissions across all courses.
 */
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/enrollib.php');

global $DB;

$syscontext = context_system::instance();

// 1. Revoke moodle/course:viewparticipants and moodle/site:viewparticipants from student & guest roles
$studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
assign_capability('moodle/course:viewparticipants', CAP_PREVENT, $studentrole->id, $syscontext->id, true);
assign_capability('moodle/site:viewparticipants', CAP_PREVENT, $studentrole->id, $syscontext->id, true);
echo "[OK] Revoked moodle/course:viewparticipants & moodle/site:viewparticipants (CAP_PREVENT) for role 'student' (id={$studentrole->id}).\n";

$guestrole = $DB->get_record('role', ['shortname' => 'guest']);
if ($guestrole) {
    assign_capability('moodle/course:viewparticipants', CAP_PREVENT, $guestrole->id, $syscontext->id, true);
    assign_capability('moodle/site:viewparticipants', CAP_PREVENT, $guestrole->id, $syscontext->id, true);
}

$userrole = $DB->get_record('role', ['shortname' => 'user']);
if ($userrole) {
    assign_capability('moodle/course:viewparticipants', CAP_PREVENT, $userrole->id, $syscontext->id, true);
    assign_capability('moodle/site:viewparticipants', CAP_PREVENT, $userrole->id, $syscontext->id, true);
}

// 2. Ensure editingteacher, teacher, manager, and canbokythuat have CAP_ALLOW on viewparticipants
foreach (['editingteacher', 'teacher', 'manager', 'canbokythuat'] as $rshort) {
    $r = $DB->get_record('role', ['shortname' => $rshort]);
    if ($r) {
        assign_capability('moodle/course:viewparticipants', CAP_ALLOW, $r->id, $syscontext->id, true);
        assign_capability('moodle/site:viewparticipants', CAP_ALLOW, $r->id, $syscontext->id, true);
        echo "[OK] Granted moodle/course:viewparticipants & moodle/site:viewparticipants (CAP_ALLOW) for role '{$rshort}' (id={$r->id}).\n";
    }
}

// 3. Ensure cbkt_pilot (Technical Officer) is enrolled as editingteacher (and not student) in all courses
$cbktuser = $DB->get_record('user', ['username' => 'cbkt_pilot', 'deleted' => 0]);
$edteacherrole = $DB->get_record('role', ['shortname' => 'editingteacher'], '*', MUST_EXIST);
if ($cbktuser && $edteacherrole) {
    // Remove any accidental 'student' role assignments for cbkt_pilot so CAP_PREVENT on student doesn't conflict
    $DB->delete_records('role_assignments', ['userid' => $cbktuser->id, 'roleid' => $studentrole->id]);

    $manualplugin = enrol_get_plugin('manual');
    $courses = $DB->get_records_sql("SELECT * FROM {course} WHERE id > 1 ORDER BY id ASC");
    foreach ($courses as $course) {
        $cctx = context_course::instance($course->id);
        $enrolinstances = enrol_get_instances($course->id, false);
        $manualinst = null;
        foreach ($enrolinstances as $ei) {
            if ($ei->enrol === 'manual') {
                $manualinst = $ei;
                break;
            }
        }
        if ($manualinst && $manualplugin) {
            if (!$DB->record_exists('user_enrolments', ['enrolid' => $manualinst->id, 'userid' => $cbktuser->id])) {
                $manualplugin->enrol_user($manualinst, $cbktuser->id, $edteacherrole->id, time());
            } else {
                role_assign($edteacherrole->id, $cbktuser->id, $cctx->id);
            }
        } else {
            role_assign($edteacherrole->id, $cbktuser->id, $cctx->id);
        }
    }
    echo "[OK] Ensured cbkt_pilot (id={$cbktuser->id}) holds 'editingteacher' role in all courses.\n";
}

// Clear accesslib & role caches
accesslib_clear_all_caches(false);
echo "[OK] Cleared Moodle accesslib capability caches.\n";
