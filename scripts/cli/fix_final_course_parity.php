<?php
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/gradelib.php');

echo "=== [1/4] Fixing Course 3 (CHEBIEN01) Quiz sequence & domain titles ===\n";
$quizcm = $DB->get_record('course_modules', ['id' => 12, 'course' => 3]);
if ($quizcm) {
    $sec = $DB->get_record('course_sections', ['id' => $quizcm->section]);
    if ($sec) {
        $seq = array_filter(array_map('trim', explode(',', (string)$sec->sequence)));
        if (!in_array('12', $seq, true)) {
            $seq[] = '12';
            $sec->sequence = implode(',', $seq);
            $DB->update_record('course_sections', $sec);
            echo "  + Added cmid=12 (CHEBIEN01 Quiz) back into section sequence ({$sec->sequence})\n";
        }
    }
    $quizcm->visible = 1;
    $quizcm->visibleoncoursepage = 1;
    $DB->update_record('course_modules', $quizcm);
}

// Fix off-topic "Flashcards Sâu bệnh IPM" page titles on Course 2 & Course 3
if ($p3 = $DB->get_record('page', ['id' => 3])) {
    $p3->name = 'Cẩm nang Chẩn đoán Triệu chứng Sâu bệnh Sinh học IPM & Thực hành QR VietGAP';
    $DB->update_record('page', $p3);
    echo "  + Updated Course 2 Page #3 title\n";
}
if ($p6 = $DB->get_record('page', ['id' => 6])) {
    $p6->name = 'Quy chuẩn Đóng gói Chuỗi Lạnh, Tem Truy xuất QR & Kiểm soát Mối nguy HACCP';
    $DB->update_record('page', $p6);
    echo "  + Updated Course 3 Page #6 title to Post-Harvest HACCP & QR Packaging\n";
}
if ($a9 = $DB->get_record('assign', ['id' => 9])) {
    $a9->name = 'Bài tập thực hành: Thiết kế quy trình làm mát sơ bộ Pre-cooling & đóng gói MAP';
    $DB->update_record('assign', $a9);
    echo "  + Updated Course 3 Assign #9 title\n";
}
if ($q18 = $DB->get_record('quiz', ['id' => 18])) {
    $q18->name = 'Kiểm tra Trắc nghiệm Cuối khóa (10 Câu hỏi) - DAUTAY-DONDUONG-01';
    $DB->update_record('quiz', $q18);
    echo "  + Updated Course 21 Quiz #18 title\n";
}

echo "=== [2/4] Adding mod_fieldpractice stations to Courses 19 & 21 ===\n";
$fpmod = $DB->get_record('modules', ['name' => 'fieldpractice'], '*', MUST_EXIST);
$ndrau = $DB->get_record('user', ['username' => 'nd_rau_lamdong']);
$cbkt  = $DB->get_record('user', ['username' => 'cbkt_pilot']);
$now   = time();

$fpconfigs = [
    19 => [
        'name' => 'Thực hành Tại vườn: Điểm danh QR & Rubric 4 Tiêu chí (DAUTAY-CNC-01)',
        'loc'  => 'Nhà màng Dâu tây Công nghệ cao Phường 7, Đà Lạt',
        'lat'  => '11.9642',
        'lng'  => '108.4385',
        'sec'  => 'MXFP_DAUTAY_DALAT_2026',
    ],
    21 => [
        'name' => 'Thực hành Tại vườn: Điểm danh QR & Rubric 4 Tiêu chí (DAUTAY-DONDUONG-01)',
        'loc'  => 'Nhà màng HTX Rau Hoa Đơn Dương, Lâm Đồng',
        'lat'  => '11.8325',
        'lng'  => '108.5542',
        'sec'  => 'MXFP_DAUTAY_DONDUONG_2026',
    ],
];

foreach ($fpconfigs as $cid => $cfg) {
    $fp = $DB->get_record('fieldpractice', ['course' => $cid]);
    if (!$fp) {
        $fpid = $DB->insert_record('fieldpractice', (object)[
            'course'        => $cid,
            'name'          => $cfg['name'],
            'intro'         => '<p>Trạm thực hành hiện trường chuẩn VietGAP TCVN 11892-1:2017: Học viên quét mã QR xoay vòng 5 phút tại nhà màng, xác nhận tọa độ định vị GPS và nhận kết quả chấm điểm theo ma trận Rubric 4 tiêu chí (100 điểm) đồng bộ trực tiếp vào sổ điểm Gradebook.</p>',
            'introformat'   => FORMAT_HTML,
            'location_name' => $cfg['loc'],
            'target_lat'    => $cfg['lat'],
            'target_lng'    => $cfg['lng'],
            'qr_secret'     => $cfg['sec'],
            'grade'         => 100,
            'timecreated'   => $now,
            'timemodified'  => $now,
        ]);
        $fp = $DB->get_record('fieldpractice', ['id' => $fpid], '*', MUST_EXIST);
        echo "  + Created fieldpractice instance #{$fpid} for Course {$cid}\n";
    }

    $sec3 = $DB->get_record('course_sections', ['course' => $cid, 'section' => 3], '*', MUST_EXIST);
    $cm = $DB->get_record('course_modules', ['course' => $cid, 'module' => $fpmod->id, 'instance' => $fp->id]);
    if (!$cm) {
        $cmid = $DB->insert_record('course_modules', (object)[
            'course'              => $cid,
            'module'              => $fpmod->id,
            'instance'            => $fp->id,
            'section'             => $sec3->id,
            'added'               => $now,
            'score'               => 0,
            'indent'              => 0,
            'visible'             => 1,
            'visibleoncoursepage' => 1,
            'visibleold'          => 1,
            'groupmode'           => 0,
            'groupingid'          => 0,
            'completion'          => 1,
            'completionview'      => 1,
            'completionexpected'  => 0,
            'showdescription'     => 0,
            'deletioninprogress'  => 0,
        ]);
        $seq = array_filter(array_map('trim', explode(',', (string)$sec3->sequence)));
        if (!in_array((string)$cmid, $seq, true)) {
            $seq[] = (string)$cmid;
            $sec3->sequence = implode(',', $seq);
            $DB->update_record('course_sections', $sec3);
        }
        context_module::instance($cmid);
        echo "  + Attached fieldpractice cmid={$cmid} to Course {$cid} Section 3\n";
    }

    // Seed graded submission for nd_rau_lamdong
    if ($ndrau && !$DB->record_exists('fieldpractice_sub', ['fieldpracticeid' => $fp->id, 'userid' => $ndrau->id])) {
        $DB->insert_record('fieldpractice_sub', (object)[
            'fieldpracticeid' => $fp->id,
            'userid'          => $ndrau->id,
            'checkin_code'    => 'DAUTAY',
            'gps_lat'         => $cfg['lat'],
            'gps_lng'         => $cfg['lng'],
            'checkin_note'    => 'Đã kiểm tra hệ thống tưới nhỏ giọt bù áp, đo EC 1.6 mS/cm, pH 5.8 và treo bẫy dính sinh học nhà màng.',
            'checkin_time'    => $now - 7200,
            'crit_prep'       => 23,
            'crit_tech'       => 24,
            'crit_biosafe'    => 23,
            'crit_quality'    => 22,
            'total_grade'     => 92.0,
            'feedback'        => 'Thao tác hiệu chuẩn EC/pH và thực hành IPM trên dâu tây nhà màng đạt chuẩn xuất sắc.',
            'graderid'        => $cbkt ? $cbkt->id : 2,
            'graded_time'     => $now - 3600,
        ]);
    }
    if (file_exists($CFG->dirroot . '/mod/fieldpractice/lib.php')) {
        require_once($CFG->dirroot . '/mod/fieldpractice/lib.php');
        if ($ndrau) {
            fieldpractice_save_rubric_grade($fp, (int)$ndrau->id, 23, 24, 23, 22, 'Thao tác hiệu chuẩn EC/pH và thực hành IPM trên dâu tây nhà màng đạt chuẩn xuất sắc.', $cbkt ? (int)$cbkt->id : 2);
        }
    }
}

echo "=== [3/4] Rebuilding course caches for all courses ===\n";
$allcourses = $DB->get_records('course', null, 'id ASC', 'id');
foreach ($allcourses as $c) {
    rebuild_course_cache($c->id, true);
}
echo "=== [4/4] Done! ===\n";
