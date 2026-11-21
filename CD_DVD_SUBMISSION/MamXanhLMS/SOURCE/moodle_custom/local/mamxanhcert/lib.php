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
 * Issue (or upgrade score of) a digital HMAC-SHA256 certificate for a learner in a course.
 */
function local_mamxanhcert_issue_certificate(int $userid, int $courseid, float $score, int $issuedat = 0): stdClass {
    global $DB;

    $existing = $DB->get_record('local_mamxanhcert', ['userid' => $userid, 'courseid' => $courseid]);
    if ($existing) {
        if ($score > (float)$existing->final_score) {
            $existing->final_score = round($score, 2);
            $existing->hmac_signature = local_mamxanhcert_compute_hmac(
                $existing->cert_code,
                (int)$existing->userid,
                (int)$existing->courseid,
                (float)$existing->final_score,
                (int)$existing->timecreated
            );
            $DB->update_record('local_mamxanhcert', $existing);
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
    return $rec;
}

/**
 * Scan finished Quiz attempts >= 70% for a user and ensure digital certificates exist.
 */
function local_mamxanhcert_sync_user_certificates(int $userid): void {
    global $DB;

    $sql = "SELECT qa.id, qa.sumgrades, q.course, q.sumgrades AS maxgrades
              FROM {quiz_attempts} qa
              JOIN {quiz} q ON q.id = qa.quiz
             WHERE qa.userid = :uid AND qa.state = 'finished' AND q.sumgrades > 0";
    $rows = $DB->get_records_sql($sql, ['uid' => $userid]);
    $bestbycourse = [];
    foreach ($rows as $r) {
        $pct = round(((float)$r->sumgrades / (float)$r->maxgrades) * 100.0, 2);
        $cid = (int)$r->course;
        if ($pct >= 70.0 && (!isset($bestbycourse[$cid]) || $pct > $bestbycourse[$cid])) {
            $bestbycourse[$cid] = $pct;
        }
    }
    foreach ($bestbycourse as $cid => $pct) {
        local_mamxanhcert_issue_certificate($userid, $cid, $pct);
    }
}
