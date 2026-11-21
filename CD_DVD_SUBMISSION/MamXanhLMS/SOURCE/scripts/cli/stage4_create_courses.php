<?php
// ===================================================================
// Mầm Xanh LMS 2.0 - Stage 4: Create 6 Interactive Courses, 72 Questions,
// Grade Categories, Quiz Sections, Completion, Badges & Enrolments
// ===================================================================
define('CLI_SCRIPT', true);

require_once('/var/www/html/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once(__DIR__ . '/stage4_course_catalog.php');

echo "=== [Stage 4] Digitizing 6 Interactive Agricultural Courses (Zero Static PDFs) ===\n";

$admin = get_admin();
\core\session\manager::set_user($admin);

// 1. Ensure Course Category exists
$catname = 'Tập huấn Kỹ thuật Nông nghiệp Công nghệ cao Mầm Xanh 2.0';
$category = $DB->get_record('course_categories', ['idnumber' => 'MAMXANH_AGTECH_CAT']);
if (!$category) {
    $catobj = \core_course_category::create([
        'name'        => $catname,
        'idnumber'    => 'MAMXANH_AGTECH_CAT',
        'description' => 'Hệ thống học liệu tương tác số hóa 100% dành cho nông dân và cán bộ kỹ thuật.',
        'visible'     => 1,
    ]);
    $categoryid = (int)$catobj->id;
} else {
    $categoryid = (int)$category->id;
}

// Helper to add amod_page cleanly
function mamxanh_create_page_module(stdClass $course, int $sectionnum, string $name, string $htmlcontent): stdClass {
    global $DB;
    $mod = new stdClass();
    $mod->modulename        = 'page';
    $mod->module            = (int)$DB->get_field('modules', 'id', ['name' => 'page'], MUST_EXIST);
    $mod->course            = $course->id;
    $mod->section           = $sectionnum;
    $mod->visible           = 1;
    $mod->name              = $name;
    $mod->intro             = '<p>' . s($name) . '</p>';
    $mod->introformat       = FORMAT_HTML;
    $mod->page              = [
        'text'   => $htmlcontent,
        'format' => FORMAT_HTML,
        'itemid' => 0,
    ];
    $mod->content           = $htmlcontent;
    $mod->contentformat     = FORMAT_HTML;
    $mod->display           = 5;
    $mod->printintro        = 0;
    $mod->printlastmodified = 0;
    $mod->completion        = COMPLETION_TRACKING_AUTOMATIC;
    $mod->completionview    = 1;

    $info = add_moduleinfo($mod, $course);
    // Ensure raw uncleaned HTML5/JS interactive content is stored intact in mdl_page
    $DB->set_field('page', 'content', $htmlcontent, ['id' => $info->instance]);
    return $info;
}

// Helper to add mod_fieldpractice cleanly
function mamxanh_create_fieldpractice_module(stdClass $course, int $sectionnum, int $gradecatid, array $c): stdClass {
    global $DB;
    $mod = new stdClass();
    $mod->modulename     = 'fieldpractice';
    $mod->module         = (int)$DB->get_field('modules', 'id', ['name' => 'fieldpractice'], MUST_EXIST);
    $mod->course         = $course->id;
    $mod->section        = $sectionnum;
    $mod->visible        = 1;
    $mod->name           = 'Thực hành Tại vườn: Điểm danh QR & Rubric 4 Tiêu chí (' . $c['shortname'] . ')';
    $mod->intro          = '<p>Buổi thực hành hiện trường tại <strong>' . s($c['station']) . '</strong>. Học viên quét mã QR xoay vòng + định vị GPS để điểm danh; Cán bộ kỹ thuật chấm điểm trực tiếp theo Rubric 4 tiêu chí (100 điểm) đồng bộ thẳng vào Sổ điểm Gradebook.</p>';
    $mod->introformat    = FORMAT_HTML;
    $mod->location_name  = $c['station'];
    $mod->target_lat     = $c['lat'];
    $mod->target_lng     = $c['lng'];
    $mod->grade          = 100;
    $mod->gradecat       = $gradecatid;
    $mod->gradepass      = 70;
    $mod->qr_secret      = 'MX2_QR_' . $c['shortname'];
    $mod->completion     = COMPLETION_TRACKING_AUTOMATIC;
    $mod->completionview = 1;

    return add_moduleinfo($mod, $course);
}

// Helper to add mod_quiz + 12 multichoice questions + grade_categories + quiz_sections
function mamxanh_create_quiz_with_questions(stdClass $course, int $sectionnum, int $gradecatid, array $c): stdClass {
    global $DB, $USER;

    $mod = new stdClass();
    $mod->modulename              = 'quiz';
    $mod->module                  = (int)$DB->get_field('modules', 'id', ['name' => 'quiz'], MUST_EXIST);
    $mod->course                  = $course->id;
    $mod->section                 = $sectionnum;
    $mod->visible                 = 1;
    $mod->name                    = 'Kiểm tra Trắc nghiệm Cuối khóa (12 Câu hỏi) - ' . $c['shortname'];
    $mod->intro                   = '<p>Bài kiểm tra đánh giá năng lực gồm <strong>12 câu hỏi trắc nghiệm thực tiễn</strong>. Đạt từ <strong>70% (7.0/10 điểm)</strong> trở lên để tự động mở khóa <strong>Chứng nhận Số QR (HMAC-SHA256)</strong> và <strong>Huy hiệu Kỹ thuật</strong>.</p>';
    $mod->introformat             = FORMAT_HTML;
    $mod->timeopen                = 0;
    $mod->timeclose               = 0;
    $mod->timelimit               = 0;
    $mod->overduehandling         = 'autosubmit';
    $mod->graceperiod             = 0;
    $mod->gradecat                = $gradecatid;
    $mod->grade                   = 10.0;
    $mod->gradepass               = 7.0;
    $mod->attempts                = 0; // Unlimited attempts
    $mod->grademethod             = QUIZ_GRADEHIGHEST;
    $mod->questionsperpage        = 12;
    $mod->navmethod               = 'free';
    $mod->shuffleanswers          = 0;
    $mod->preferredbehaviour      = 'deferredfeedback';
    $mod->canredoquestions        = 0;
    $mod->attemptonlast           = 0;
    $mod->showuserpicture         = 0;
    $mod->decimalpoints           = 2;
    $mod->questiondecimalpoints   = -1;
    $mod->showblocks              = 0;
    $mod->quizpassword            = '';
    $mod->subnet                  = '';
    $mod->delay1                  = 0;
    $mod->delay2                  = 0;
    $mod->browsersecurity         = '-';

    // Enable full review options after attempt
    $reviewbits = 69904; // Standard Moodle bitmask for during/immediately/later/closed
    foreach (['attempt', 'correctness', 'maxmarks', 'marks', 'specificfeedback', 'generalfeedback', 'rightanswer', 'overallfeedback'] as $rf) {
        $mod->{'review' . $rf} = $reviewbits;
        $mod->{$rf . 'during'} = 1;
        $mod->{$rf . 'immediately'} = 1;
        $mod->{$rf . 'open'} = 1;
        $mod->{$rf . 'closed'} = 1;
    }

    $mod->completion          = COMPLETION_TRACKING_AUTOMATIC;
    $mod->completionview      = 0;
    $mod->completionusegrade  = 1;
    $mod->completionpassgrade = 1;

    $info = add_moduleinfo($mod, $course);
    $quiz = $DB->get_record('quiz', ['id' => $info->instance], '*', MUST_EXIST);
    $quiz->cmid = $info->coursemodule;

    // Explicitly guarantee quiz_sections record exists
    if (!$DB->record_exists('quiz_sections', ['quizid' => $quiz->id])) {
        $DB->insert_record('quiz_sections', (object)[
            'quizid'           => $quiz->id,
            'firstslot'        => 1,
            'heading'          => '',
            'shufflequestions' => 0,
        ]);
    }

    // Create default Question Bank category in Quiz module context
    $quizcontext = context_module::instance($info->coursemodule);
    $qcat = question_make_default_categories([$quizcontext]);

    $qtype = question_bank::get_qtype('multichoice');

    foreach ($c['questions'] as $qidx => $qitem) {
        [$qtext, $opts, $correctIdx] = $qitem;
        $qnum = $qidx + 1;

        $question = new stdClass();
        $question->category  = $qcat->id;
        $question->qtype     = 'multichoice';
        $question->createdby = $USER->id;
        $question->contextid = $quizcontext->id;

        $fromform = new stdClass();
        $fromform->category                 = $qcat->id . ',' . $quizcontext->id;
        $fromform->name                     = sprintf('%s - Câu %02d', $c['shortname'], $qnum);
        $fromform->questiontext             = ['text' => '<p>' . htmlspecialchars($qtext) . '</p>', 'format' => FORMAT_HTML];
        $fromform->generalfeedback          = ['text' => '<p>Chuẩn kiến thức thực hành nông nghiệp công nghệ cao Mầm Xanh LMS.</p>', 'format' => FORMAT_HTML];
        $fromform->defaultmark              = 1.0;
        $fromform->penalty                  = 0.0;
        $fromform->status                   = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $fromform->single                   = 1;
        $fromform->shuffleanswers           = 0;
        $fromform->answernumbering          = 'abc';
        $fromform->showstandardinstruction  = 0;
        $fromform->correctfeedback          = ['text' => 'Chính xác! Bạn nắm rất vững quy trình kỹ thuật.', 'format' => FORMAT_HTML];
        $fromform->partiallycorrectfeedback = ['text' => 'Gần chính xác.', 'format' => FORMAT_HTML];
        $fromform->incorrectfeedback        = ['text' => 'Chưa chính xác, hãy xem lại bài giảng Infographic & Slide tương tác.', 'format' => FORMAT_HTML];
        $fromform->shownumcorrect           = 1;

        $fromform->answer   = [];
        $fromform->fraction = [];
        $fromform->feedback = [];

        foreach ($opts as $oi => $opttext) {
            $iscorrect = ((int)$oi === (int)$correctIdx);
            $fromform->answer[$oi]   = ['text' => $opttext, 'format' => FORMAT_PLAIN];
            $fromform->fraction[$oi] = $iscorrect ? '1.0' : '0.0';
            $fromform->feedback[$oi] = [
                'text'   => $iscorrect ? 'Đáp án đúng chuẩn kỹ thuật.' : 'Phương án này chưa phù hợp quy trình chuẩn.',
                'format' => FORMAT_HTML,
            ];
        }

        $savedq = $qtype->save_question($question, $fromform);
        quiz_add_quiz_question($savedq->id, $quiz, 1, 1.0);
    }

    // Recompute sumgrades & ensure Gradebook grade_item links cleanly to course grade_category
    if (class_exists('\mod_quiz\quiz_settings')) {
        \mod_quiz\quiz_settings::create($quiz->id)->get_grade_calculator()->recompute_quiz_sumgrades();
    } else if (function_exists('quiz_update_sumgrades')) {
        quiz_update_sumgrades($quiz);
    }
    $quiz = $DB->get_record('quiz', ['id' => $quiz->id], '*', MUST_EXIST);
    quiz_grade_item_update($quiz);

    $gi = $DB->get_record('grade_items', [
        'courseid'   => $course->id,
        'itemtype'   => 'mod',
        'itemmodule' => 'quiz',
        'iteminstance' => $quiz->id,
    ]);
    if ($gi) {
        $gi->categoryid = $gradecatid;
        $gi->gradepass  = 7.0;
        $DB->update_record('grade_items', $gi);
    }

    return $info;
}

// Helper to create an active Course Badge
function mamxanh_ensure_course_badge(stdClass $course, int $adminid): void {
    global $DB;
    if ($DB->record_exists('badge', ['courseid' => $course->id])) {
        return;
    }
    $now = time();
    $DB->insert_record('badge', (object)[
        'name'            => 'Chuyên gia Kỹ thuật ' . $course->shortname,
        'description'     => 'Huy hiệu công nhận hoàn thành xuất sắc khóa học ' . $course->fullname . ' đạt từ 70% trở lên.',
        'timecreated'     => $now,
        'timemodified'    => $now,
        'usercreated'     => $adminid,
        'usermodified'    => $adminid,
        'issuername'      => 'Mầm Xanh LMS 2.0 - Nông nghiệp Công nghệ cao',
        'issuerurl'       => 'https://moodle.lvh.me:8443',
        'issuercontact'   => 'admin@mamxanh.vn',
        'expiredate'      => null,
        'expireperiod'    => null,
        'type'            => 2, // Course badge
        'courseid'        => $course->id,
        'message'         => 'Chúc mừng bạn đã đạt Huy hiệu Kỹ thuật Nông nghiệp Công nghệ cao!',
        'messagesubject'  => 'Đã mở khóa Huy hiệu Kỹ thuật Mầm Xanh!',
        'attachment'      => 1,
        'notification'    => 0,
        'status'          => 1, // ACTIVE
        'nextcron'        => null,
        'version'         => '2.0',
        'language'        => 'vi',
        'imageauthorname' => 'Mam Xanh AgTech',
        'imageauthoremail'=> 'admin@mamxanh.vn',
        'imageauthorurl'  => 'https://moodle.lvh.me:8443',
        'imagecaption'    => $course->shortname,
    ]);
}

$catalog = mamxanh_get_six_courses_catalog();
$studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
$cbktrole = $DB->get_record('role', ['shortname' => 'canbokythuat'], '*', MUST_EXIST);
$manualplugin = enrol_get_plugin('manual');
$selfplugin = enrol_get_plugin('self');

$cbktuser = $DB->get_record('user', ['username' => 'cbkt_pilot']);
$farmerusers = $DB->get_records_select('user', "deleted = 0 AND username IN ('nd_rau_lamdong', 'nd_saurieng_daklak', 'nd_vanan')");

foreach ($catalog as $idx => $c) {
    $existing = $DB->get_record('course', ['shortname' => $c['shortname']]);
    if ($existing) {
        echo " -> [SKIP] Course '{$c['shortname']}' already exists (ID={$existing->id})\n";
        continue;
    }

    $coursedata = (object)[
        'category'         => $categoryid,
        'shortname'        => $c['shortname'],
        'fullname'         => $c['fullname'],
        'idnumber'         => $c['shortname'],
        'summary'          => '<p>' . htmlspecialchars($c['summary']) . '</p>',
        'summaryformat'    => FORMAT_HTML,
        'format'           => 'topics',
        'numsections'      => 4,
        'startdate'        => time() - (30 * 86400),
        'visible'          => 1,
        'enablecompletion' => 1,
        'showgrades'       => 1,
        'showreports'      => 1,
        'lang'             => '',
    ];

    $course = create_course($coursedata);

    // CRITICAL: Initialize Course Grade Category & Course Grade Item immediately
    $coursecatgrade = grade_category::fetch_course_category($course->id);
    $coursecatgrade->load_grade_item();
    $gradecatid = (int)$coursecatgrade->id;

    // Ensure 4 sections exist and have descriptive Vietnamese titles
    course_create_sections_if_missing($course, [0, 1, 2, 3, 4]);
    $secnames = [
        0 => ['Tổng quan Khóa học & Hướng dẫn Học tập Rảnh tay', $c['summary']],
        1 => ['Chuyên đề 1: Video Hướng dẫn Trực quan & Infographic Kỹ thuật Số', 'Xem video phân đoạn thực địa và tra cứu bảng thông số chuẩn pH/EC, nhiệt ẩm.'],
        2 => ['Chuyên đề 2: Bài giảng Slide Tương tác (7 Slides) & Câu hỏi Tình huống', 'Tương tác trực tiếp trên sơ đồ hệ thống kỹ thuật và trả lời nhanh tình huống tại vườn.'],
        3 => ['Chuyên đề 3: Thẻ lật Sâu bệnh IPM, Truy xuất QR VietGAP & Thực hành Vườn', 'Lật thẻ chẩn đoán Gỉ sắt, Rệp sáp, Bọ trĩ, Nhện đỏ và điểm danh QR thực hành hiện trường.'],
        4 => ['Chuyên đề 4: Bài Kiểm tra Trắc nghiệm Cuối khóa (12 Câu) & Chứng nhận Số QR', 'Hoàn thành đạt >= 70% để hệ thống tự động ký cấp Chứng nhận HMAC-SHA256 và Huy hiệu.'],
    ];

    foreach ($secnames as $snum => [$sname, $ssum]) {
        $sec = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $snum]);
        if ($sec) {
            $sec->name          = $sname;
            $sec->summary       = '<p>' . htmlspecialchars($ssum) . '</p>';
            $sec->summaryformat = FORMAT_HTML;
            $sec->visible       = 1;
            $DB->update_record('course_sections', $sec);
        }
    }

    // Build interactive HTML5 modules
    $html1 = mamxanh_build_video_infographic_html($c['sec1_video']);
    $html2 = mamxanh_build_slidedeck_html($c['shortname'], 'Bộ Slide Kỹ thuật Tương tác: ' . $c['fullname'], $c['sec2_slides']);
    $html3 = mamxanh_build_flashcards_accordion_html($c['shortname'], $c['sec3_cards'], $c['sec3_accordions']);

    mamxanh_create_page_module($course, 1, 'Video & Infographic: ' . $c['sec1_video']['infographic_title'], $html1);
    mamxanh_create_page_module($course, 2, 'Slide Tương tác (7 Slides): Quy trình Kỹ thuật ' . $c['shortname'], $html2);
    mamxanh_create_page_module($course, 3, 'Thẻ lật Flashcards Sâu bệnh IPM & Thực hành QR VietGAP', $html3);
    mamxanh_create_fieldpractice_module($course, 3, $gradecatid, $c);
    $quizinfo = mamxanh_create_quiz_with_questions($course, 4, $gradecatid, $c);

    mamxanh_ensure_course_badge($course, (int)$admin->id);

    // Enable Self-Enrolment + Manual Enrolment for CBKT and Core Farmers
    $enrolinstances = enrol_get_instances($course->id, false);
    $manualinst = null;
    foreach ($enrolinstances as $ei) {
        if ($ei->enrol === 'manual') {
            $manualinst = $ei;
            if ((int)$ei->status !== ENROL_INSTANCE_ENABLED) {
                $manualplugin->update_status($ei, ENROL_INSTANCE_ENABLED);
            }
        } else if ($ei->enrol === 'self' && $selfplugin) {
            $selfplugin->update_status($ei, ENROL_INSTANCE_ENABLED);
        }
    }
    if (!$manualinst) {
        $mid = $manualplugin->add_default_instance($course);
        $manualinst = $DB->get_record('enrol', ['id' => $mid]);
    }

    if ($manualinst && $cbktuser) {
        $manualplugin->enrol_user($manualinst, $cbktuser->id, $cbktrole->id);
    }
    if ($manualinst) {
        foreach ($farmerusers as $fu) {
            $manualplugin->enrol_user($manualinst, $fu->id, $studentrole->id);
        }
    }

    rebuild_course_cache($course->id, true);
    echo " -> [OK] Digitized Course #{$course->id} [{$c['shortname']}]: 4 Sections, Video+Infographic, 7-Slide Deck, IPM Flashcards, FieldPractice, Quiz (12 Qs, CMID={$quizinfo->coursemodule}), GradeCategory #{$gradecatid}, Active Badge\n";
}

purge_all_caches();
echo "=== [Stage 4] All 6 Interactive Courses Digitized Successfully ===\n";
