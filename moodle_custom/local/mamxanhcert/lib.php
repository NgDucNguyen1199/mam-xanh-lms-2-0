<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Compute deterministic HMAC-SHA256 cryptographic signature for a certificate record.
 */
function local_mamxanhcert_compute_hmac(string $certcode, int $userid, int $courseid, float $score, int $timecreated): string {
    global $CFG;
    $secret = $CFG->mamxanh_hmac_secret ?? 'MX2_HMAC_2026_VIETGAP_AGRITECH_DIGITAL_SIGNATURE_KEY';
    $canonical = implode('|', [
        trim($certcode),
        $userid,
        $courseid,
        number_format($score, 2, '.', ''),
        $timecreated,
    ]);
    return hash_hmac('sha256', $canonical, $secret);
}

/**
 * Verify an existing certificate record's HMAC-SHA256 signature.
 */
function local_mamxanhcert_verify_record(stdClass $cert): bool {
    if (($cert->status ?? '') !== 'valid') {
        return false;
    }
    $expected = local_mamxanhcert_compute_hmac(
        (string)$cert->cert_code,
        (int)$cert->userid,
        (int)$cert->courseid,
        (float)$cert->final_score,
        (int)$cert->timecreated
    );
    return hash_equals($expected, (string)$cert->hmac_signature);
}

/**
 * Push a newly issued or upgraded certificate record to Supabase asynchronously (non-blocking 2s timeout).
 */
function local_mamxanhcert_push_cloud_sync(stdClass $cert): void {
    global $CFG, $DB;

    if (!function_exists('local_mamxanh_sync_to_cloud') && file_exists($CFG->dirroot . '/local/mamxanhapp/lib.php')) {
        require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');
    }
    if (!function_exists('local_mamxanh_sync_to_cloud')) {
        return;
    }

    $student = $DB->get_record('user', ['id' => (int)$cert->userid], 'id,firstname,lastname,firstnamephonetic,lastnamephonetic,middlename,alternatename,username');
    $course = $DB->get_record('course', ['id' => (int)$cert->courseid], 'id,shortname,fullname');
    $farmname = '';
    if ($DB->get_manager()->table_exists('local_farmerprofile')) {
        $pf = $DB->get_record('local_farmerprofile', ['userid' => (int)$cert->userid], 'id,farm_name');
        if ($pf && !empty($pf->farm_name)) {
            $farmname = (string)$pf->farm_name;
        }
    }

    local_mamxanh_sync_to_cloud('mamxanh_certificates', [
        'cert_code'        => (string)$cert->cert_code,
        'student_name'     => $student ? fullname($student) : ('Học viên #' . (int)$cert->userid),
        'student_username' => $student ? (string)$student->username : '',
        'farm_name'        => $farmname,
        'course_code'      => $course ? (string)$course->shortname : ('C' . (int)$cert->courseid),
        'course_name'      => $course ? format_string($course->fullname) : '',
        'final_score'      => round((float)$cert->final_score, 2),
        'hmac_signature'   => (string)$cert->hmac_signature,
        'status'           => (string)($cert->status ?? 'valid'),
        'issued_at'        => gmdate('c', (int)$cert->timecreated),
        'moodle_userid'    => (int)$cert->userid,
        'moodle_courseid'  => (int)$cert->courseid,
        'issued_unix'      => (int)$cert->timecreated,
    ], 'cert_code');
}

/**
 * Issue (or upgrade score of) a digital HMAC-SHA256 certificate for a learner in a course.
 */
function local_mamxanhcert_issue_certificate(int $userid, int $courseid, float $score, int $issuedat = 0): stdClass {
    global $DB;

    $existing = $DB->get_record('local_mamxanhcert', ['userid' => $userid, 'courseid' => $courseid]);
    if ($existing) {
        $newscore = round($score, 2);
        if (abs($newscore - (float)$existing->final_score) >= 0.01 || ($existing->status ?? '') !== 'valid') {
            $existing->final_score = $newscore;
            $existing->status = 'valid';
            $existing->hmac_signature = local_mamxanhcert_compute_hmac(
                $existing->cert_code,
                (int)$existing->userid,
                (int)$existing->courseid,
                (float)$existing->final_score,
                (int)$existing->timecreated
            );
            $DB->update_record('local_mamxanhcert', $existing);
            local_mamxanhcert_push_cloud_sync($existing);
        }
        return $existing;
    }

    $course = $DB->get_record('course', ['id' => $courseid], 'id,shortname');
    $short = $course ? preg_replace('/[^A-Z0-9]/', '', strtoupper($course->shortname)) : ('C' . $courseid);
    if ($short === '') {
        $short = 'AGRI';
    }
    $ts = $issuedat > 0 ? $issuedat : time();
    $certcode = sprintf('MXCERT-%s-%s-%04d', date('Y', $ts), substr($short, 0, 12), $userid * 10 + ($courseid % 10));

    $attemptseq = 1;
    while ($DB->record_exists('local_mamxanhcert', ['cert_code' => $certcode])) {
        $certcode = sprintf('MXCERT-%s-%s-%04d-%d', date('Y', $ts), substr($short, 0, 10), $userid, $attemptseq++);
    }

    $cleanScore = round($score, 2);
    $hmac = local_mamxanhcert_compute_hmac($certcode, $userid, $courseid, $cleanScore, $ts);

    $rec = (object)[
        'userid'         => $userid,
        'courseid'       => $courseid,
        'cert_code'      => $certcode,
        'final_score'    => $cleanScore,
        'hmac_signature' => $hmac,
        'status'         => 'valid',
        'timecreated'    => $ts,
    ];
    $rec->id = $DB->insert_record('local_mamxanhcert', $rec);
    local_mamxanhcert_push_cloud_sync($rec);
    return $rec;
}

/**
 * Single Source of Truth: Evaluate 3 mandatory academic gates for certificate eligibility:
 * 1) Real Attendance on actually taken sessions >= 80%
 * 2) Final Quiz attempt >= 70%
 * 3) Practical Field Rubric (mdl_fieldpractice_sub, or non-discussion assign_grades if no fieldpractice exists) >= 60/100
 */
function local_mamxanhcert_evaluate_eligibility(int $userid, int $courseid): stdClass {
    global $DB;

    $dbman = $DB->get_manager();

    // 1. Attendance check (>= 80% on sessions that have actually been taken)
    $atts = $DB->get_records('attendance', ['course' => $courseid], 'id ASC');
    $totatt = 0;
    $presatt = 0;
    foreach ($atts as $att) {
        $sesses = $DB->get_records('attendance_sessions', ['attendanceid' => $att->id], 'sessdate ASC');
        foreach ($sesses as $s) {
            $istaken = ((int)($s->lasttaken ?? 0) > 0) || $DB->record_exists('attendance_log', ['sessionid' => $s->id]);
            if (!$istaken) {
                continue;
            }
            $totatt++;
            $log = $DB->get_record('attendance_log', ['sessionid' => $s->id, 'studentid' => $userid]);
            if ($log) {
                $st = $DB->get_record('attendance_statuses', ['id' => $log->statusid]);
                if ($st && (float)$st->grade > 0) {
                    $presatt++;
                }
            }
        }
    }
    $attpct = $totatt > 0 ? round(($presatt / $totatt) * 100.0, 1) : null;
    $attok = ($attpct !== null && $attpct >= 80.0);

    // 2. Final Quiz check (>= 70% on finished quiz_attempts)
    $quizzes = $DB->get_records('quiz', ['course' => $courseid], 'id ASC');
    $bestq = null;
    foreach ($quizzes as $q) {
        if ((float)$q->sumgrades <= 0) {
            continue;
        }
        $attsq = $DB->get_records('quiz_attempts', [
            'quiz'   => $q->id,
            'userid' => $userid,
            'state'  => 'finished',
        ], 'sumgrades DESC', 'id,sumgrades', 0, 1);
        if (!empty($attsq)) {
            $top = reset($attsq);
            $pct = min(100.0, round(((float)$top->sumgrades / (float)$q->sumgrades) * 100.0, 1));
            if ($bestq === null || $pct > $bestq) {
                $bestq = $pct;
            }
        }
    }
    $quizok = ($bestq !== null && $bestq >= 70.0);

    // 3. Practical Field Rubric check (>= 60/100 from mdl_fieldpractice_sub)
    $rubricscore = null;
    $rubricsub = null;
    $hascheckin = false;
    $hasfp = false;
    if ($dbman->table_exists('fieldpractice') && $dbman->table_exists('fieldpractice_sub')) {
        $fps = $DB->get_records('fieldpractice', ['course' => $courseid], 'id ASC');
        if (!empty($fps)) {
            $hasfp = true;
            foreach ($fps as $fp) {
                $sub = $DB->get_record('fieldpractice_sub', ['fieldpracticeid' => $fp->id, 'userid' => $userid]);
                if ($sub) {
                    $hascheckin = true;
                    if ($sub->total_grade !== null && ((int)$sub->graded_time > 0 || (float)$sub->total_grade > 0)) {
                        $sc = min(100.0, round((float)$sub->total_grade, 1));
                        if ($rubricscore === null || $sc > $rubricscore) {
                            $rubricscore = $sc;
                            $rubricsub = $sub;
                        }
                    }
                }
            }
        }
    }
    if (!$hasfp && $rubricscore === null) {
        $assigns = $DB->get_records('assign', ['course' => $courseid], 'id ASC');
        foreach ($assigns as $asg) {
            $low = \core_text::strtolower($asg->name);
            if (strpos($low, 'thảo luận') === false && strpos($low, 'discussion') === false) {
                $ag = $DB->get_record('assign_grades', ['assignment' => $asg->id, 'userid' => $userid]);
                if ($ag && $ag->grade !== null && (float)$ag->grade > 0) {
                    $maxg = (float)$asg->grade > 0 ? (float)$asg->grade : 100.0;
                    $sc = min(100.0, round(((float)$ag->grade / $maxg) * 100.0, 1));
                    if ($rubricscore === null || $sc > $rubricscore) {
                        $rubricscore = $sc;
                    }
                }
            }
        }
    }
    $rubricok = ($rubricscore !== null && $rubricscore >= 60.0);

    $eligible = ($attok && $quizok && $rubricok);
    $finalscore = 0.0;
    if ($eligible) {
        $finalscore = round(($attpct * 0.20) + ($bestq * 0.40) + ($rubricscore * 0.40), 2);
    }

    return (object)[
        'userid'      => $userid,
        'courseid'    => $courseid,
        'atttaken'    => $totatt,
        'attpresent'  => $presatt,
        'attpct'      => $attpct,
        'attok'       => $attok,
        'quizpct'     => $bestq,
        'quizok'      => $quizok,
        'rubricscore' => $rubricscore,
        'rubricsub'   => $rubricsub,
        'hascheckin'  => $hascheckin,
        'rubricok'    => $rubricok,
        'eligible'    => $eligible,
        'final_score' => $finalscore,
    ];
}

/**
 * Evaluate all courses for a user against the 3-gate eligibility rules and synchronize digital certificates.
 */
function local_mamxanhcert_sync_user_certificates(int $userid): void {
    global $DB;

    if ($userid <= 0) {
        return;
    }

    $courses = $DB->get_records_select(
        'course',
        'id <> :siteid AND shortname <> :tpl',
        ['siteid' => SITEID, 'tpl' => 'MASTER-AGRITECH-TPL'],
        'id ASC',
        'id,shortname,visible'
    );

    foreach ($courses as $c) {
        $cid = (int)$c->id;
        $eval = local_mamxanhcert_evaluate_eligibility($userid, $cid);
        if ($eval->eligible) {
            local_mamxanhcert_issue_certificate($userid, $cid, $eval->final_score);
        } else {
            if ($DB->record_exists('local_mamxanhcert', ['userid' => $userid, 'courseid' => $cid])) {
                $DB->delete_records('local_mamxanhcert', ['userid' => $userid, 'courseid' => $cid]);
            }
        }
    }
}
