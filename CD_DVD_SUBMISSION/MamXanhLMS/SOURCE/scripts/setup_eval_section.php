<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/attendance/lib.php');
require_once($CFG->dirroot . '/mod/attendance/locallib.php');
require_once($CFG->dirroot . '/mod/assign/lib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

echo "=== 1. STANDARDIZING ATTENDANCE STATUSES ===\n";
// Update default statuses (setnumber = 0)
$statuses = $DB->get_records('attendance_statuses', ['setnumber' => 0]);
foreach ($statuses as $st) {
    if ($st->acronym === 'P') {
        $st->description = 'Có mặt';
        $st->grade = 2.0;
        $DB->update_record('attendance_statuses', $st);
        echo "Updated P: Có mặt (2.0đ)\n";
    } else if ($st->acronym === 'L') {
        $st->description = 'Đi trễ';
        $st->grade = 1.0;
        $DB->update_record('attendance_statuses', $st);
        echo "Updated L: Đi trễ (1.0đ)\n";
    } else if ($st->acronym === 'E') {
        $st->description = 'Vắng có phép';
        $st->grade = 1.0;
        $DB->update_record('attendance_statuses', $st);
        echo "Updated E: Vắng có phép (1.0đ)\n";
    } else if ($st->acronym === 'A') {
        $st->description = 'Vắng không phép';
        $st->grade = 0.0;
        $DB->update_record('attendance_statuses', $st);
        echo "Updated A: Vắng không phép (0.0đ)\n";
    }
}

// Get enrolled students to take sample attendance
$students = $DB->get_records_sql("
    SELECT DISTINCT u.id, u.username, u.firstname, u.lastname
    FROM {user} u
    JOIN {role_assignments} ra ON ra.userid = u.id
    JOIN {context} ctx ON ctx.id = ra.contextid
    WHERE u.deleted = 0 AND u.username LIKE 'nd_%'
");
echo "Found " . count($students) . " farmer student(s) for sample attendance logs.\n";

$target_courses = [2, 3, 8];

foreach ($target_courses as $cid) {
    $course = $DB->get_record('course', ['id' => $cid]);
    if (!$course) continue;
    echo "\n------------------------------------------------------------\n";
    echo "PROCESSING COURSE {$cid}: {$course->fullname}\n";
    echo "------------------------------------------------------------\n";

    // 1. Ensure Section "Kiểm tra & Đánh giá" exists
    $eval_sec = $DB->get_record_select('course_sections', "course = ? AND (name LIKE '%Kiểm tra%' OR name LIKE '%Đánh giá%')", [$cid]);
    if (!$eval_sec) {
        $max_sec = $DB->get_field_sql("SELECT MAX(section) FROM {course_sections} WHERE course = ?", [$cid]);
        $new_sec_num = (int)$max_sec + 1;
        $eval_sec = course_create_section($cid, $new_sec_num);
        $DB->set_field('course_sections', 'name', 'Kiểm tra & Đánh giá', ['id' => $eval_sec->id]);
        echo "Created new section 'Kiểm tra & Đánh giá' at section index {$new_sec_num}\n";
    } else {
        $DB->set_field('course_sections', 'name', 'Kiểm tra & Đánh giá', ['id' => $eval_sec->id]);
        echo "Found and updated section ID {$eval_sec->id} (Sec {$eval_sec->section}) to 'Kiểm tra & Đánh giá'\n";
    }

    $mod_attendance = $DB->get_record('modules', ['name' => 'attendance']);
    $mod_assign = $DB->get_record('modules', ['name' => 'assign']);

    // 2. Setup "Điểm danh Lý thuyết" (mod_attendance)
    $att_lt = $DB->get_record('attendance', ['course' => $cid, 'name' => 'Điểm danh Lý thuyết']);
    if (!$att_lt) {
        $att_lt = new stdClass();
        $att_lt->course = $cid;
        $att_lt->name = 'Điểm danh Lý thuyết';
        $att_lt->intro = '<p>Điểm danh các buổi học lý thuyết trực tuyến và trên lớp.</p>';
        $att_lt->introformat = FORMAT_HTML;
        $att_lt->grade = 100;
        $att_lt->timemodified = time();
        $att_lt->id = $DB->insert_record('attendance', $att_lt);

        $cm = new stdClass();
        $cm->course = $cid;
        $cm->module = $mod_attendance->id;
        $cm->instance = $att_lt->id;
        $cm->section = $eval_sec->id;
        $cm->added = time();
        $cm->visible = 1;
        $cm->visibleold = 1;
        $cm->showdescription = 0;
        $cmid = $DB->insert_record('course_modules', $cm);
        
        course_add_cm_to_section($cid, $cmid, $eval_sec->section);
        echo "Created module: Điểm danh Lý thuyết (CMID {$cmid})\n";
    } else {
        echo "Existing attendance: Điểm danh Lý thuyết (ID {$att_lt->id})\n";
    }

    // Add 5 Sessions for "Điểm danh Lý thuyết" in November 2026
    $lt_sessions_config = [
        ['date' => '2026-11-02 07:30:00', 'dur' => 7200, 'desc' => 'Buổi 1: Tổng quan Vi khí hậu Nhà màng & Tiêu chuẩn Cây giống'],
        ['date' => '2026-11-09 07:30:00', 'dur' => 7200, 'desc' => 'Buổi 2: Kỹ thuật Tưới nhỏ giọt & Tiết kiệm nước theo chuẩn VietGAP'],
        ['date' => '2026-11-16 07:30:00', 'dur' => 7200, 'desc' => 'Buổi 3: Cân bằng Dinh dưỡng pH/EC & Châm phân tự động Fertigation'],
        ['date' => '2026-11-23 07:30:00', 'dur' => 7200, 'desc' => 'Buổi 4: Quản lý Dịch hại Tổng hợp (IPM) & Kiểm soát Sinh học'],
        ['date' => '2026-11-30 07:30:00', 'dur' => 7200, 'desc' => 'Buổi 5: Báo cáo Tổng kết & Đề án Chuyển đổi Canh tác Số']
    ];

    $status_p = $DB->get_record('attendance_statuses', ['setnumber' => 0, 'acronym' => 'P']);
    $status_l = $DB->get_record('attendance_statuses', ['setnumber' => 0, 'acronym' => 'L']);

    foreach ($lt_sessions_config as $idx => $sconf) {
        $s_time = strtotime($sconf['date']);
        $sess = $DB->get_record('attendance_sessions', ['attendanceid' => $att_lt->id, 'sessdate' => $s_time]);
        if (!$sess) {
            $sess = new stdClass();
            $sess->attendanceid = $att_lt->id;
            $sess->sessdate = $s_time;
            $sess->duration = $sconf['dur'];
            $sess->lasttaken = ($idx <= 1) ? $s_time + 1800 : 0;
            $sess->lasttakenby = ($idx <= 1) ? 2 : 0;
            $sess->timemodified = time();
            $sess->description = $sconf['desc'];
            $sess->descriptionformat = FORMAT_HTML;
            $sess->studentscanmark = 1;
            $sess->autoassignstatus = 0;
            $sess->preventsharedip = 0;
            $sess->calendarevent = 1;
            $sess->id = $DB->insert_record('attendance_sessions', $sess);
            echo "  + Added LT Session " . ($idx + 1) . ": {$sconf['date']} ({$sconf['desc']})\n";

            // Create calendar event for session
            $event = new stdClass();
            $event->name = 'Điểm danh Lý thuyết: ' . $sconf['desc'];
            $event->description = $sconf['desc'];
            $event->format = FORMAT_HTML;
            $event->courseid = $cid;
            $event->groupid = 0;
            $event->userid = 0;
            $event->modulename = 'attendance';
            $event->instance = $att_lt->id;
            $event->eventtype = 'attendance';
            $event->timestart = $s_time;
            $event->timeduration = $sconf['dur'];
            $event->visible = 1;
            $event->sequence = 1;
            $event->timemodified = time();
            $event_id = $DB->insert_record('event', $event);
            $DB->set_field('attendance_sessions', 'calendarevent', 1, ['id' => $sess->id]);

            // Add sample logs for completed sessions (Sessions 1 and 2)
            if ($idx <= 1 && $status_p) {
                foreach ($students as $stu) {
                    $log = new stdClass();
                    $log->sessionid = $sess->id;
                    $log->studentid = $stu->id;
                    $log->statusid = ($idx === 1 && $stu->username === 'nd_saurieng_daklak') ? $status_l->id : $status_p->id;
                    $log->statusset = 0;
                    $log->timetaken = $s_time + 600;
                    $log->takenby = 2; // admin
                    $log->remarks = 'Điểm danh hệ thống Mầm Xanh LMS';
                    $DB->insert_record('attendance_log', $log);
                }
            }
        }
    }

    // 3. Setup "Điểm danh Thực hành" (mod_attendance)
    $att_th = $DB->get_record('attendance', ['course' => $cid, 'name' => 'Điểm danh Thực hành']);
    if (!$att_th) {
        $att_th = new stdClass();
        $att_th->course = $cid;
        $att_th->name = 'Điểm danh Thực hành';
        $att_th->intro = '<p>Điểm danh các buổi thực hành tại nhà màng & vườn ươm công nghệ cao.</p>';
        $att_th->introformat = FORMAT_HTML;
        $att_th->grade = 100;
        $att_th->timemodified = time();
        $att_th->id = $DB->insert_record('attendance', $att_th);

        $cm = new stdClass();
        $cm->course = $cid;
        $cm->module = $mod_attendance->id;
        $cm->instance = $att_th->id;
        $cm->section = $eval_sec->id;
        $cm->added = time();
        $cm->visible = 1;
        $cm->visibleold = 1;
        $cm->showdescription = 0;
        $cmid = $DB->insert_record('course_modules', $cm);
        
        course_add_cm_to_section($cid, $cmid, $eval_sec->section);
        echo "Created module: Điểm danh Thực hành (CMID {$cmid})\n";
    }

    // Add 3 Sessions for "Điểm danh Thực hành" in November 2026
    $th_sessions_config = [
        ['date' => '2026-11-04 08:00:00', 'dur' => 12600, 'desc' => 'Buổi 1: Khảo sát cảm biến IoT, hệ thống châm phân & kiểm tra mẫu đất tại vườn'],
        ['date' => '2026-11-11 08:00:00', 'dur' => 12600, 'desc' => 'Buổi 2: Pha chế dung dịch dinh dưỡng A-B & cân bằng pH/EC tưới nhỏ giọt'],
        ['date' => '2026-11-18 08:00:00', 'dur' => 12600, 'desc' => 'Buổi 3: Đánh giá sinh trưởng, bắt bọ trĩ bẫy dính & phun vi sinh Beauveria']
    ];

    foreach ($th_sessions_config as $idx => $sconf) {
        $s_time = strtotime($sconf['date']);
        $sess = $DB->get_record('attendance_sessions', ['attendanceid' => $att_th->id, 'sessdate' => $s_time]);
        if (!$sess) {
            $sess = new stdClass();
            $sess->attendanceid = $att_th->id;
            $sess->sessdate = $s_time;
            $sess->duration = $sconf['dur'];
            $sess->lasttaken = ($idx === 0) ? $s_time + 1800 : 0;
            $sess->lasttakenby = ($idx === 0) ? 2 : 0;
            $sess->timemodified = time();
            $sess->description = $sconf['desc'];
            $sess->descriptionformat = FORMAT_HTML;
            $sess->studentscanmark = 1;
            $sess->autoassignstatus = 0;
            $sess->preventsharedip = 0;
            $sess->calendarevent = 1;
            $sess->id = $DB->insert_record('attendance_sessions', $sess);
            echo "  + Added TH Session " . ($idx + 1) . ": {$sconf['date']} ({$sconf['desc']})\n";

            // Calendar event
            $event = new stdClass();
            $event->name = 'Điểm danh Thực hành: ' . $sconf['desc'];
            $event->description = $sconf['desc'];
            $event->format = FORMAT_HTML;
            $event->courseid = $cid;
            $event->groupid = 0;
            $event->userid = 0;
            $event->modulename = 'attendance';
            $event->instance = $att_th->id;
            $event->eventtype = 'attendance';
            $event->timestart = $s_time;
            $event->timeduration = $sconf['dur'];
            $event->visible = 1;
            $event->sequence = 1;
            $event->timemodified = time();
            $DB->insert_record('event', $event);

            // Log for Session 1
            if ($idx === 0 && $status_p) {
                foreach ($students as $stu) {
                    $log = new stdClass();
                    $log->sessionid = $sess->id;
                    $log->studentid = $stu->id;
                    $log->statusid = $status_p->id;
                    $log->statusset = 0;
                    $log->timetaken = $s_time + 600;
                    $log->takenby = 2;
                    $log->remarks = 'Điểm danh thực hành nhà màng';
                    $DB->insert_record('attendance_log', $log);
                }
            }
        }
    }

    // 4. Setup "Thảo luận tại lớp buổi 1" (mod_assign)
    $t1_name = 'Thảo luận tại lớp buổi 1';
    $t1_open = strtotime('2026-11-09 07:30:00');
    $t1_due  = strtotime('2026-11-13 23:30:00');
    $t1_intro = '
<p>Tài liệu tham khảo chuyên đề vi khí hậu &amp; dinh dưỡng tưới:</p>
<ul style="list-style: none; padding-left: 0; margin-bottom: 12px; font-size: 0.9rem;">
    <li style="margin-bottom: 4px;"><a href="https://cuctoan.gov.vn/quy-trinh-ky-thuat-vietgap-nha-mang-dalat" target="_blank" rel="noopener noreferrer">https://cuctoan.gov.vn/quy-trinh-ky-thuat-vietgap-nha-mang-dalat</a></li>
    <li style="margin-bottom: 4px;"><a href="https://faovietnam.org/sustainable-agritech-greenhouse-climate-control" target="_blank" rel="noopener noreferrer">https://faovietnam.org/sustainable-agritech-greenhouse-climate-control</a></li>
    <li style="margin-bottom: 4px;"><a href="https://dalat-smartfarm.vn/giao-trinh-dinh-duong-tuoi-nho-giot-ec-ph" target="_blank" rel="noopener noreferrer">https://dalat-smartfarm.vn/giao-trinh-dinh-duong-tuoi-nho-giot-ec-ph</a></li>
</ul>
<p style="margin-bottom: 0;"><strong>Câu hỏi:</strong> Theo anh/chị yếu tố kỹ thuật nào là quan trọng nhất khi kiểm soát vi khí hậu và dinh dưỡng tưới nhỏ giọt trong nhà màng?</p>';

    $assign1 = $DB->get_record('assign', ['course' => $cid, 'name' => $t1_name]);
    if (!$assign1) {
        $assign1 = new stdClass();
        $assign1->course = $cid;
        $assign1->name = $t1_name;
        $assign1->intro = $t1_intro;
        $assign1->introformat = FORMAT_HTML;
        $assign1->alwaysshowdescription = 1;
        $assign1->submissiondrafts = 0;
        $assign1->requiresubmissionstatement = 0;
        $assign1->sendnotifications = 0;
        $assign1->sendlatenotifications = 0;
        $assign1->duedate = $t1_due;
        $assign1->allowsubmissionsfromdate = $t1_open;
        $assign1->grade = 100;
        $assign1->timemodified = time();
        $assign1->completionsubmit = 1;
        $assign1->cutoffdate = $t1_due + 86400 * 2;
        $assign1->gradingduedate = $t1_due + 86400 * 5;
        $assign1->teamsubmission = 0;
        $assign1->requireallteammemberssubmit = 0;
        $assign1->blindmarking = 0;
        $assign1->hidegrader = 0;
        $assign1->revealidentities = 0;
        $assign1->attemptreopenmethod = 'none';
        $assign1->maxattempts = -1;
        $assign1->markingworkflow = 0;
        $assign1->markingallocation = 0;
        $assign1->sendstudentnotifications = 1;
        $assign1->preventsubmissionnotingroup = 0;
        $assign1->activity = '';
        $assign1->activityformat = 0;
        $assign1->timelimit = 0;
        $assign1->id = $DB->insert_record('assign', $assign1);

        $cm = new stdClass();
        $cm->course = $cid;
        $cm->module = $mod_assign->id;
        $cm->instance = $assign1->id;
        $cm->section = $eval_sec->id;
        $cm->added = time();
        $cm->visible = 1;
        $cm->visibleold = 1;
        $cm->showdescription = 1; // Show description on course page!
        $cmid = $DB->insert_record('course_modules', $cm);
        
        course_add_cm_to_section($cid, $cmid, $eval_sec->section);
        echo "Created assign: {$t1_name} (CMID {$cmid})\n";

        // Assign calendar event
        $event = new stdClass();
        $event->name = $t1_name . ' có hạn nộp';
        $event->description = 'Hạn chót nộp bài thảo luận tại lớp buổi 1';
        $event->format = FORMAT_HTML;
        $event->courseid = $cid;
        $event->groupid = 0;
        $event->userid = 0;
        $event->modulename = 'assign';
        $event->instance = $assign1->id;
        $event->eventtype = 'due';
        $event->timestart = $t1_due;
        $event->timeduration = 0;
        $event->visible = 1;
        $event->sequence = 1;
        $event->timemodified = time();
        $DB->insert_record('event', $event);
    } else {
        $assign1->intro = $t1_intro;
        $assign1->allowsubmissionsfromdate = $t1_open;
        $assign1->duedate = $t1_due;
        $assign1->alwaysshowdescription = 1;
        $DB->update_record('assign', $assign1);
        $cm = $DB->get_record('course_modules', ['course' => $cid, 'module' => $mod_assign->id, 'instance' => $assign1->id]);
        if ($cm) {
            $DB->set_field('course_modules', 'showdescription', 1, ['id' => $cm->id]);
        }
        echo "Updated assign: {$t1_name}\n";
    }

    // 5. Setup "Thảo luận tại lớp buổi 2" (mod_assign)
    $t2_name = 'Thảo luận tại lớp buổi 2';
    $t2_open = strtotime('2026-11-16 07:30:00');
    $t2_due  = strtotime('2026-11-20 23:30:00');
    $t2_intro = '
<p>Tài liệu tham khảo chuyên đề quản lý dịch hại &amp; mã số vùng trồng:</p>
<ul style="list-style: none; padding-left: 0; margin-bottom: 12px; font-size: 0.9rem;">
    <li style="margin-bottom: 4px;"><a href="https://dalat-smartfarm.vn/quan-ly-sau-benh-ipm-sinh-hoc-thrips-palmi" target="_blank" rel="noopener noreferrer">https://dalat-smartfarm.vn/quan-ly-sau-benh-ipm-sinh-hoc-thrips-palmi</a></li>
    <li style="margin-bottom: 4px;"><a href="https://vietgap.gov.vn/so-tay-thu-hoach-dong-goi-truy-xuat-ma-vung-trong" target="_blank" rel="noopener noreferrer">https://vietgap.gov.vn/so-tay-thu-hoach-dong-goi-truy-xuat-ma-vung-trong</a></li>
</ul>
<p style="margin-bottom: 0;"><strong>Câu hỏi:</strong> Phân tích biện pháp phòng ngừa bọ trĩ và nấm rỉ sắt sinh học kết hợp ghi nhật ký số truy xuất nguồn gốc?</p>';

    $assign2 = $DB->get_record('assign', ['course' => $cid, 'name' => $t2_name]);
    if (!$assign2) {
        $assign2 = new stdClass();
        $assign2->course = $cid;
        $assign2->name = $t2_name;
        $assign2->intro = $t2_intro;
        $assign2->introformat = FORMAT_HTML;
        $assign2->alwaysshowdescription = 1;
        $assign2->submissiondrafts = 0;
        $assign2->requiresubmissionstatement = 0;
        $assign2->sendnotifications = 0;
        $assign2->sendlatenotifications = 0;
        $assign2->duedate = $t2_due;
        $assign2->allowsubmissionsfromdate = $t2_open;
        $assign2->grade = 100;
        $assign2->timemodified = time();
        $assign2->completionsubmit = 1;
        $assign2->cutoffdate = $t2_due + 86400 * 2;
        $assign2->gradingduedate = $t2_due + 86400 * 5;
        $assign2->teamsubmission = 0;
        $assign2->requireallteammemberssubmit = 0;
        $assign2->blindmarking = 0;
        $assign2->hidegrader = 0;
        $assign2->revealidentities = 0;
        $assign2->attemptreopenmethod = 'none';
        $assign2->maxattempts = -1;
        $assign2->markingworkflow = 0;
        $assign2->markingallocation = 0;
        $assign2->sendstudentnotifications = 1;
        $assign2->preventsubmissionnotingroup = 0;
        $assign2->activity = '';
        $assign2->activityformat = 0;
        $assign2->timelimit = 0;
        $assign2->id = $DB->insert_record('assign', $assign2);

        $cm = new stdClass();
        $cm->course = $cid;
        $cm->module = $mod_assign->id;
        $cm->instance = $assign2->id;
        $cm->section = $eval_sec->id;
        $cm->added = time();
        $cm->visible = 1;
        $cm->visibleold = 1;
        $cm->showdescription = 1;
        $cmid = $DB->insert_record('course_modules', $cm);
        
        course_add_cm_to_section($cid, $cmid, $eval_sec->section);
        echo "Created assign: {$t2_name} (CMID {$cmid})\n";

        // Assign calendar event
        $event = new stdClass();
        $event->name = $t2_name . ' có hạn nộp';
        $event->description = 'Hạn chót nộp bài thảo luận tại lớp buổi 2';
        $event->format = FORMAT_HTML;
        $event->courseid = $cid;
        $event->groupid = 0;
        $event->userid = 0;
        $event->modulename = 'assign';
        $event->instance = $assign2->id;
        $event->eventtype = 'due';
        $event->timestart = $t2_due;
        $event->timeduration = 0;
        $event->visible = 1;
        $event->sequence = 1;
        $event->timemodified = time();
        $DB->insert_record('event', $event);
    } else {
        $assign2->intro = $t2_intro;
        $assign2->allowsubmissionsfromdate = $t2_open;
        $assign2->duedate = $t2_due;
        $assign2->alwaysshowdescription = 1;
        $DB->update_record('assign', $assign2);
        $cm = $DB->get_record('course_modules', ['course' => $cid, 'module' => $mod_assign->id, 'instance' => $assign2->id]);
        if ($cm) {
            $DB->set_field('course_modules', 'showdescription', 1, ['id' => $cm->id]);
        }
        echo "Updated assign: {$t2_name}\n";
    }

    // 6. Setup "Bài tập thực hành" & "Bài thu hoạch cuối khóa"
    $th_assign_name = 'Bài tập thực hành: Pha chế dung dịch dinh dưỡng A-B & cân bằng pH/EC';
    $th_assign_open = strtotime('2026-11-09 08:00:00');
    $th_assign_due  = strtotime('2026-11-22 23:30:00');

    $as_th = $DB->get_record('assign', ['course' => $cid, 'name' => $th_assign_name]);
    if (!$as_th) {
        $as_th = new stdClass();
        $as_th->course = $cid;
        $as_th->name = $th_assign_name;
        $as_th->intro = '<p>Học viên tiến hành pha chế dung dịch dinh dưỡng mẹ tank A &amp; tank B theo công thức tiêu chuẩn, ghi chép giá trị pH ban đầu, EC và hiệu chỉnh về ngưỡng tối ưu 5.8 - 6.2 và EC 1.8 - 2.2 mS/cm. Nộp báo cáo đính kèm ảnh chụp chỉ số bút đo.</p>';
        $as_th->introformat = FORMAT_HTML;
        $as_th->alwaysshowdescription = 1;
        $as_th->submissiondrafts = 0;
        $as_th->requiresubmissionstatement = 0;
        $as_th->sendnotifications = 0;
        $as_th->sendlatenotifications = 0;
        $as_th->duedate = $th_assign_due;
        $as_th->allowsubmissionsfromdate = $th_assign_open;
        $as_th->grade = 100;
        $as_th->timemodified = time();
        $as_th->completionsubmit = 1;
        $as_th->cutoffdate = $th_assign_due + 86400 * 2;
        $as_th->gradingduedate = $th_assign_due + 86400 * 5;
        $as_th->teamsubmission = 0;
        $as_th->requireallteammemberssubmit = 0;
        $as_th->blindmarking = 0;
        $as_th->hidegrader = 0;
        $as_th->revealidentities = 0;
        $as_th->attemptreopenmethod = 'none';
        $as_th->maxattempts = -1;
        $as_th->markingworkflow = 0;
        $as_th->markingallocation = 0;
        $as_th->sendstudentnotifications = 1;
        $as_th->preventsubmissionnotingroup = 0;
        $as_th->activity = '';
        $as_th->activityformat = 0;
        $as_th->timelimit = 0;
        $as_th->id = $DB->insert_record('assign', $as_th);

        $cm = new stdClass();
        $cm->course = $cid;
        $cm->module = $mod_assign->id;
        $cm->instance = $as_th->id;
        $cm->section = $eval_sec->id;
        $cm->added = time();
        $cm->visible = 1;
        $cm->visibleold = 1;
        $cm->showdescription = 0;
        $cmid = $DB->insert_record('course_modules', $cm);
        
        course_add_cm_to_section($cid, $cmid, $eval_sec->section);
        echo "Created assign: {$th_assign_name} (CMID {$cmid})\n";

        // Calendar event
        $event = new stdClass();
        $event->name = $th_assign_name . ' có hạn nộp';
        $event->description = 'Hạn chót nộp bài tập thực hành pha chế dinh dưỡng A-B';
        $event->format = FORMAT_HTML;
        $event->courseid = $cid;
        $event->groupid = 0;
        $event->userid = 0;
        $event->modulename = 'assign';
        $event->instance = $as_th->id;
        $event->eventtype = 'due';
        $event->timestart = $th_assign_due;
        $event->timeduration = 0;
        $event->visible = 1;
        $event->sequence = 1;
        $event->timemodified = time();
        $DB->insert_record('event', $event);
    }

    $harvest_assign_name = 'Bài thu hoạch cuối khóa: Đề án Chuyển đổi Canh tác Số & VietGAP';
    $harvest_assign_open = strtotime('2026-11-16 08:00:00');
    $harvest_assign_due  = strtotime('2026-11-30 23:30:00');

    $as_hv = $DB->get_record('assign', ['course' => $cid, 'name' => $harvest_assign_name]);
    if (!$as_hv) {
        $as_hv = new stdClass();
        $as_hv->course = $cid;
        $as_hv->name = $harvest_assign_name;
        $as_hv->intro = '<p>Báo cáo thu hoạch tổng kết toàn diện: Đánh giá thực trạng nông hộ, xây dựng lộ trình áp dụng công nghệ vi khí hậu IoT, tưới nhỏ giọt fertigation và phác đồ sinh học IPM nhằm đạt chứng chỉ VietGAP.</p>';
        $as_hv->introformat = FORMAT_HTML;
        $as_hv->alwaysshowdescription = 1;
        $as_hv->submissiondrafts = 0;
        $as_hv->requiresubmissionstatement = 0;
        $as_hv->sendnotifications = 0;
        $as_hv->sendlatenotifications = 0;
        $as_hv->duedate = $harvest_assign_due;
        $as_hv->allowsubmissionsfromdate = $harvest_assign_open;
        $as_hv->grade = 100;
        $as_hv->timemodified = time();
        $as_hv->completionsubmit = 1;
        $as_hv->cutoffdate = $harvest_assign_due + 86400 * 2;
        $as_hv->gradingduedate = $harvest_assign_due + 86400 * 5;
        $as_hv->teamsubmission = 0;
        $as_hv->requireallteammemberssubmit = 0;
        $as_hv->blindmarking = 0;
        $as_hv->hidegrader = 0;
        $as_hv->revealidentities = 0;
        $as_hv->attemptreopenmethod = 'none';
        $as_hv->maxattempts = -1;
        $as_hv->markingworkflow = 0;
        $as_hv->markingallocation = 0;
        $as_hv->sendstudentnotifications = 1;
        $as_hv->preventsubmissionnotingroup = 0;
        $as_hv->activity = '';
        $as_hv->activityformat = 0;
        $as_hv->timelimit = 0;
        $as_hv->id = $DB->insert_record('assign', $as_hv);

        $cm = new stdClass();
        $cm->course = $cid;
        $cm->module = $mod_assign->id;
        $cm->instance = $as_hv->id;
        $cm->section = $eval_sec->id;
        $cm->added = time();
        $cm->visible = 1;
        $cm->visibleold = 1;
        $cm->showdescription = 0;
        $cmid = $DB->insert_record('course_modules', $cm);
        
        course_add_cm_to_section($cid, $cmid, $eval_sec->section);
        echo "Created assign: {$harvest_assign_name} (CMID {$cmid})\n";

        // Calendar event
        $event = new stdClass();
        $event->name = $harvest_assign_name . ' có hạn nộp';
        $event->description = 'Hạn chót nộp bài thu hoạch cuối khóa';
        $event->format = FORMAT_HTML;
        $event->courseid = $cid;
        $event->groupid = 0;
        $event->userid = 0;
        $event->modulename = 'assign';
        $event->instance = $as_hv->id;
        $event->eventtype = 'due';
        $event->timestart = $harvest_assign_due;
        $event->timeduration = 0;
        $event->visible = 1;
        $event->sequence = 1;
        $event->timemodified = time();
        $DB->insert_record('event', $event);
    }

    // Rebuild course cache for this course
    rebuild_course_cache($cid, true);
    echo "Rebuilt course cache for Course {$cid}\n";
}

echo "\n=== ALL EVALUATION SECTIONS CONFIGURED SUCCESSFULLY ===\n";
