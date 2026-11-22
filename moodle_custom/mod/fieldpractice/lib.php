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
 * Compute great-circle Haversine distance in meters between two GPS coordinates.
 */
function fieldpractice_haversine_meters(float $lat1, float $lng1, float $lat2, float $lng2): float {
    $earthradius = 6371000.0;
    $dlat = deg2rad($lat2 - $lat1);
    $dlng = deg2rad($lng2 - $lng1);
    $a = sin($dlat / 2) * sin($dlat / 2)
       + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
       * sin($dlng / 2) * sin($dlng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(max(0.0, 1.0 - $a)));
    return round($earthradius * $c, 2);
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

    // Re-evaluate 3-gate certificate eligibility for this learner
    $certlib = $CFG->dirroot . '/local/mamxanhcert/lib.php';
    if (file_exists($certlib)) {
        require_once($certlib);
        if (function_exists('local_mamxanhcert_sync_user_certificates')) {
            local_mamxanhcert_sync_user_certificates($userid);
        }
    }

    return $sub;
}

/**
 * Aggregate 4-criteria Rubric average scores (Prep, Technique, BioSafety, Quality) across graded submissions.
 */
function fieldpractice_get_rubric_criteria_summary(int $fieldpracticeid = 0): stdClass {
    global $DB;

    $summary = (object)[
        'count'       => 0,
        'avg_prep'    => 0.0,
        'avg_tech'    => 0.0,
        'avg_biosafe' => 0.0,
        'avg_quality' => 0.0,
        'avg_total'   => 0.0,
    ];
    if (!$DB->get_manager()->table_exists('fieldpractice_sub')) {
        return $summary;
    }

    $where = 'total_grade IS NOT NULL AND graded_time > 0';
    $params = [];
    if ($fieldpracticeid > 0) {
        $where .= ' AND fieldpracticeid = :fpid';
        $params['fpid'] = $fieldpracticeid;
    }

    $sql = "SELECT COUNT(1) AS cnt,
                   COALESCE(AVG(crit_prep), 0) AS avg_prep,
                   COALESCE(AVG(crit_tech), 0) AS avg_tech,
                   COALESCE(AVG(crit_biosafe), 0) AS avg_biosafe,
                   COALESCE(AVG(crit_quality), 0) AS avg_quality,
                   COALESCE(AVG(total_grade), 0) AS avg_total
              FROM {fieldpractice_sub}
             WHERE {$where}";
    $row = $DB->get_record_sql($sql, $params);
    if ($row && (int)$row->cnt > 0) {
        $summary->count       = (int)$row->cnt;
        $summary->avg_prep    = round((float)$row->avg_prep, 2);
        $summary->avg_tech    = round((float)$row->avg_tech, 2);
        $summary->avg_biosafe = round((float)$row->avg_biosafe, 2);
        $summary->avg_quality = round((float)$row->avg_quality, 2);
        $summary->avg_total   = round((float)$row->avg_total, 2);
    }
    return $summary;
}

/**
 * Render reusable 4-criteria Rubric average breakdown table for Analytics & Audit views.
 */
function fieldpractice_render_criteria_summary_html(int $fieldpracticeid = 0, bool $en = false): string {
    $st = fieldpractice_get_rubric_criteria_summary($fieldpracticeid);
    $criteria = [
        [
            'code' => 'TC1',
            'name' => $en ? '1. Materials, PPE & Instrument Calibration' : '1. Chuẩn bị Vật tư, Bảo hộ & Hiệu chuẩn Dụng cụ',
            'avg'  => $st->avg_prep,
        ],
        [
            'code' => 'TC2',
            'name' => $en ? '2. Standardized VietGAP & Fertigation Execution' : '2. Kỹ thuật Thao tác chuẩn Quy trình VietGAP / Fertigation',
            'avg'  => $st->avg_tech,
        ],
        [
            'code' => 'TC3',
            'name' => $en ? '3. BioSafety, pH/EC Control & PHI Compliance' : '3. An toàn Sinh học, Kiểm soát pH/EC & Cách ly PHI',
            'avg'  => $st->avg_biosafe,
        ],
        [
            'code' => 'TC4',
            'name' => $en ? '4. Output Quality & Digital Field Logging' : '4. Chất lượng Thành phẩm & Ghi chép Nhật ký Điện tử',
            'avg'  => $st->avg_quality,
        ],
    ];

    $title = $en
        ? '4-Criteria Field Practice Rubric Skill Averages (' . $st->count . ' Graded Evaluations)'
        : 'Thống kê Điểm Trung bình 4 Tiêu chí Rubric Thực hành VietGAP (' . $st->count . ' Phiếu đã chấm)';

    $rows = '';
    foreach ($criteria as $cr) {
        $pct = $st->count > 0 ? round(($cr['avg'] / 25.0) * 100.0, 1) : 0.0;
        $rows .= '<tr>'
            . '<td><code>' . s($cr['code']) . '</code></td>'
            . '<td style="font-weight:600;color:#1C2522;">' . s($cr['name']) . '</td>'
            . '<td class="text-center"><strong>' . ($st->count > 0 ? number_format($cr['avg'], 2) : '--') . ' / 25</strong></td>'
            . '<td>'
            . '<div class="d-flex align-items-center" style="gap:8px;">'
            . '<div style="flex:1;height:8px;background:#EAF2EE;border-radius:4px;overflow:hidden;"><div style="width:' . max(4, min(100, (int)round($pct))) . '%;height:100%;background:#234E3E;border-radius:4px;"></div></div>'
            . '<span class="small fw-bold" style="color:#234E3E;min-width:48px;">' . ($st->count > 0 ? number_format($pct, 1) . '%' : '--') . '</span>'
            . '</div>'
            . '</td>'
            . '</tr>';
    }

    return '<section class="mx-criteria-summary-card mt-4 mb-4" style="background:#FFFFFF;border:1px solid #E6ECE9;border-radius:14px;padding:20px;">'
        . '<div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:10px;">'
        . '<h4 class="h4 mb-0" style="color:#234E3E;font-weight:700;">' . s($title) . '</h4>'
        . '<span class="badge" style="background:#EAF2EE;color:#234E3E;border:1px solid #CFD9D4;font-weight:700;padding:6px 12px;border-radius:999px;">'
        . s(($en ? 'Overall Mean: ' : 'Điểm tổng bình quân: ') . ($st->count > 0 ? number_format($st->avg_total, 2) . '/100' : '--'))
        . '</span>'
        . '</div>'
        . '<div class="table-responsive">'
        . '<table class="table table-hover align-middle mb-0">'
        . '<thead><tr>'
        . '<th>' . s($en ? 'Code' : 'Mã') . '</th>'
        . '<th>' . s($en ? 'Rubric Criterion' : 'Tiêu chí Đánh giá Tay nghề') . '</th>'
        . '<th class="text-center">' . s($en ? 'Mean Score' : 'Điểm Trung bình') . '</th>'
        . '<th>' . s($en ? 'Proficiency Rate' : 'Mức độ Đạt chuẩn') . '</th>'
        . '</tr></thead>'
        . '<tbody>' . $rows . '</tbody>'
        . '</table>'
        . '</div>'
        . '</section>';
}
