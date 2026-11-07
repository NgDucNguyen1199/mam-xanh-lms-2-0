<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Supported Moodle activity features for mod_fieldpractice.
 */
function fieldpractice_supports(string $feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_PURPOSE:
            return defined('MOD_PURPOSE_ASSESSMENT') ? MOD_PURPOSE_ASSESSMENT : 'assessment';
        default:
            return null;
    }
}

/**
 * Add a new Field Practice instance and create its Gradebook item.
 */
function fieldpractice_add_instance(stdClass $data, $mform = null): int {
    global $DB;

    $now = time();
    $data->timecreated = $now;
    $data->timemodified = $now;
    if (empty($data->grade)) {
        $data->grade = 100;
    }
    if (empty($data->qr_secret)) {
        $data->qr_secret = substr(sha1(uniqid('mxfp_', true)), 0, 24);
    }
    if (empty($data->location_name)) {
        $data->location_name = 'Vườn Thực nghiệm Nông nghiệp Công nghệ cao Mầm Xanh';
    }
    if (empty($data->target_lat)) {
        $data->target_lat = '11.940419';
    }
    if (empty($data->target_lng)) {
        $data->target_lng = '108.458313';
    }

    $data->id = $DB->insert_record('fieldpractice', $data);
    fieldpractice_grade_item_update($data);
    return (int)$data->id;
}

/**
 * Update an existing Field Practice instance.
 */
function fieldpractice_update_instance(stdClass $data, $mform = null): bool {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;
    $res = $DB->update_record('fieldpractice', $data);
    fieldpractice_grade_item_update($data);
    return $res;
}

/**
 * Delete a Field Practice instance and clean up Gradebook item.
 */
function fieldpractice_delete_instance(int $id): bool {
    global $DB;

    $fp = $DB->get_record('fieldpractice', ['id' => $id]);
    if (!$fp) {
        return false;
    }
    $DB->delete_records('fieldpractice_sub', ['fieldpracticeid' => $fp->id]);
    $DB->delete_records('fieldpractice', ['id' => $fp->id]);
    fieldpractice_grade_item_delete($fp);
    return true;
}

/**
 * Create or update the Gradebook item and/or learner grades for Field Practice.
 */
function fieldpractice_grade_item_update(stdClass $fieldpractice, $grades = null): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = [
        'itemname' => $fieldpractice->name,
        'idnumber' => $fieldpractice->cmidnumber ?? '',
    ];

    if ((int)($fieldpractice->grade ?? 100) > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = (float)$fieldpractice->grade;
        $params['grademin']  = 0;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update(
        'mod/fieldpractice',
        $fieldpractice->course,
        'mod',
        'fieldpractice',
        $fieldpractice->id,
        0,
        $grades,
        $params
    );
}

/**
 * Delete Gradebook item.
 */
function fieldpractice_grade_item_delete(stdClass $fieldpractice): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update(
        'mod/fieldpractice',
        $fieldpractice->course,
        'mod',
        'fieldpractice',
        $fieldpractice->id,
        0,
        null,
        ['deleted' => 1]
    );
}

/**
 * Update user grades in Moodle Gradebook.
 */
function fieldpractice_update_grades(stdClass $fieldpractice, int $userid = 0, bool $nullifnone = true): void {
    global $DB;

    $params = ['fieldpracticeid' => $fieldpractice->id];
    if ($userid > 0) {
        $params['userid'] = $userid;
    }
    $subs = $DB->get_records('fieldpractice_sub', $params);
    $grades = [];
    foreach ($subs as $sub) {
        if ($sub->total_grade !== null) {
            $grades[$sub->userid] = (object)[
                'userid'        => $sub->userid,
                'rawgrade'      => (float)$sub->total_grade,
                'dategraded'    => (int)$sub->graded_time,
                'datesubmitted' => (int)($sub->checkin_time ?: $sub->graded_time),
            ];
        }
    }

    if (!empty($grades)) {
        fieldpractice_grade_item_update($fieldpractice, $grades);
    } else if ($userid > 0 && $nullifnone) {
        fieldpractice_grade_item_update($fieldpractice, [
            $userid => (object)['userid' => $userid, 'rawgrade' => null],
        ]);
    } else {
        fieldpractice_grade_item_update($fieldpractice);
    }
}

/**
 * Compute rotating 5-minute attendance code for QR check-in at the garden.
 */
function fieldpractice_rotating_token(stdClass $fp, int $slotoffset = 0): string {
    $slot = intdiv(time(), 300) + $slotoffset;
    $raw = hash_hmac('sha256', $fp->id . '|' . $slot, $fp->qr_secret ?: 'MX2_FIELD_QR');
    return strtoupper(substr($raw, 0, 6));
}

/**
 * Validate a rotating QR check-in code (accepts current or previous 5-minute window).
 */
function fieldpractice_validate_token(stdClass $fp, string $code): bool {
    $clean = strtoupper(trim($code));
    if ($clean === '') {
        return false;
    }
    return hash_equals(fieldpractice_rotating_token($fp, 0), $clean)
        || hash_equals(fieldpractice_rotating_token($fp, -1), $clean);
}

/**
 * Save 4-Criteria Rubric Grade and push directly into Moodle Gradebook + Activity Completion.
 */
function fieldpractice_save_rubric_grade(
    stdClass $fp,
    int $userid,
    int $c1,
    int $c2,
    int $c3,
    int $c4,
    string $feedback,
    int $graderid
): stdClass {
    global $CFG, $DB;

    $c1 = max(0, min(25, $c1));
    $c2 = max(0, min(25, $c2));
    $c3 = max(0, min(25, $c3));
    $c4 = max(0, min(25, $c4));
    $total = (float)($c1 + $c2 + $c3 + $c4);
    $now = time();

    $sub = $DB->get_record('fieldpractice_sub', ['fieldpracticeid' => $fp->id, 'userid' => $userid]);
    if ($sub) {
        $sub->crit_prep    = $c1;
        $sub->crit_tech    = $c2;
        $sub->crit_biosafe = $c3;
        $sub->crit_quality = $c4;
        $sub->total_grade  = $total;
        $sub->feedback     = $feedback;
        $sub->graderid     = $graderid;
        $sub->graded_time  = $now;
        $DB->update_record('fieldpractice_sub', $sub);
    } else {
        $sub = (object)[
            'fieldpracticeid' => $fp->id,
            'userid'          => $userid,
            'checkin_code'    => fieldpractice_rotating_token($fp, 0),
            'gps_lat'         => $fp->target_lat,
            'gps_lng'         => $fp->target_lng,
            'checkin_note'    => 'Điểm danh trực tiếp tại vườn cùng Cán bộ kỹ thuật.',
            'checkin_time'    => $now,
            'crit_prep'       => $c1,
            'crit_tech'       => $c2,
            'crit_biosafe'    => $c3,
            'crit_quality'    => $c4,
            'total_grade'     => $total,
            'feedback'        => $feedback,
            'graderid'        => $graderid,
            'graded_time'     => $now,
        ];
        $sub->id = $DB->insert_record('fieldpractice_sub', $sub);
    }

    // Push straight into Moodle Gradebook
    fieldpractice_grade_item_update($fp, [
        $userid => (object)[
            'userid'        => $userid,
            'rawgrade'      => $total,
            'dategraded'    => $now,
            'datesubmitted' => (int)($sub->checkin_time ?: $now),
        ],
    ]);

    // Mark Activity Completion if enabled
    require_once($CFG->libdir . '/completionlib.php');
    $course = get_course($fp->course);
    $completion = new completion_info($course);
    if ($completion->is_enabled()) {
        $cm = get_coursemodule_from_instance('fieldpractice', $fp->id, $fp->course, false, IGNORE_MISSING);
        if ($cm && $completion->is_enabled($cm)) {
            $completion->update_state($cm, COMPLETION_COMPLETE, $userid);
        }
    }

    return $sub;
}
