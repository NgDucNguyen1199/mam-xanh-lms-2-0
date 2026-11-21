<?php
// ===================================================================
// Mầm Xanh LMS 2.0 - Stage 7 Defense Committee Complete Upgrade Script
// 1. Direct Inline PDF Handbooks (mod_resource RESOURCELIB_DISPLAY_EMBED = 1)
// 2. Authentic H5P (mod_h5pactivity) & SCORM 1.2 (mod_scorm) with Gradebook Sync
// 3. Master Course Template [MASTER-AGRITECH-TPL] with Sequential Restrict Access
// 4. Real Local HTML5 <video> (MP4 + WebM + Vietnamese .vtt) across ALL Courses
// ===================================================================
define('CLI_SCRIPT', true);

require_once('/var/www/html/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->libdir . '/resourcelib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/scorm/locallib.php');
require_once(__DIR__ . '/stage4_course_catalog.php');
require_once(__DIR__ . '/stage7_package_builders.php');

echo "=== [Stage 7] Applying Defense Committee Upgrades (PDF Embed, H5P, SCORM 1.2, Master Template & Real HTML5 Video) ===\n";

$admin = get_admin();
\core\session\manager::set_user($admin);

$assetsroot = '/var/www/html/local/mamxanhapp/assets';
$pdfdir     = $assetsroot . '/pdfs';
$baseh5pdir = $assetsroot . '/h5p_base';
$pkgdir     = $assetsroot . '/packages';

// 1. Generate PDFs, H5P packages & SCORM 1.2 package
echo " -> [Step 1/4] Generating Vietnamese PDF handbooks, H5P packages & SCORM 1.2 zip...\n";
$pdfs  = mamxanh_stage7_generate_technical_pdfs($pdfdir);
$h5ps  = mamxanh_stage7_build_h5p_packages($baseh5pdir, $pkgdir);
$scorm = mamxanh_stage7_build_scorm12_package($pkgdir);
echo "    [OK] Generated PDFs (" . basename($pdfs['chamsoc_pdf']) . ", " . basename($pdfs['chebien_pdf']) . ")\n";
echo "    [OK] Generated H5Ps (" . basename($h5ps['qset_h5p']) . ", " . basename($h5ps['cp_h5p']) . ", " . basename($h5ps['acc_h5p']) . ")\n";
echo "    [OK] Generated SCORM 1.2 (" . basename($scorm) . ")\n";

/**
 * Stage a local file into Moodle user draft area and return draftitemid.
 */
function mamxanh_stage_draft_file(string $filepath, ?string $customname = null): int {
    global $USER;
    $fs = get_file_storage();
    $userctx = context_user::instance($USER->id);
    $draftitemid = file_get_unused_draft_itemid();
    $finfo = [
        'contextid' => $userctx->id,
        'component' => 'user',
        'filearea'  => 'draft',
        'itemid'    => $draftitemid,
        'filepath'  => '/',
        'filename'  => $customname ?: basename($filepath),
    ];
    $fs->create_file_from_pathname($finfo, $filepath);
    return $draftitemid;
}

/**
 * Add an inline embedded PDF (mod_resource with display = RESOURCELIB_DISPLAY_EMBED = 1).
 */
function mamxanh_ensure_inline_pdf_resource(stdClass $course, int $sectionnum, string $name, string $introhtml, string $pdfpath, string $idnumber): stdClass {
    global $DB;
    $existingcm = $DB->get_record('course_modules', ['course' => $course->id, 'idnumber' => $idnumber, 'deletioninprogress' => 0]);
    if ($existingcm) {
        $DB->set_field('resource', 'display', RESOURCELIB_DISPLAY_EMBED, ['id' => $existingcm->instance]);
        $existingcm->coursemodule = $existingcm->id;
        return $existingcm;
    }

    $draftid = mamxanh_stage_draft_file($pdfpath);

    $mod = new stdClass();
    $mod->modulename     = 'resource';
    $mod->module         = (int)$DB->get_field('modules', 'id', ['name' => 'resource'], MUST_EXIST);
    $mod->course         = $course->id;
    $mod->section        = $sectionnum;
    $mod->visible        = 1;
    $mod->cmidnumber     = $idnumber;
    $mod->name           = $name;
    $mod->intro          = $introhtml;
    $mod->introformat    = FORMAT_HTML;
    $mod->files          = $draftid;
    $mod->display        = RESOURCELIB_DISPLAY_EMBED; // 1 = Embed directly in Moodle lesson page without downloading!
    $mod->printintro     = 1;
    $mod->showsize       = 1;
    $mod->showtype       = 1;
    $mod->showdate       = 0;
    $mod->filterfiles    = 0;
    $mod->revision       = 1;
    $mod->completion     = COMPLETION_TRACKING_AUTOMATIC;
    $mod->completionview = 1;

    $info = add_moduleinfo($mod, $course);
    // Explicitly enforce RESOURCELIB_DISPLAY_EMBED (1) in mdl_resource
    $DB->set_field('resource', 'display', RESOURCELIB_DISPLAY_EMBED, ['id' => $info->instance]);
    return $info;
}

/**
 * Add an authentic H5P activity (mod_h5pactivity) and pre-deploy its H5P content record.
 */
function mamxanh_ensure_h5p_activity(stdClass $course, int $sectionnum, int $gradecatid, string $name, string $introhtml, string $h5ppath, string $idnumber): stdClass {
    global $DB;
    $existingcm = $DB->get_record('course_modules', ['course' => $course->id, 'idnumber' => $idnumber, 'deletioninprogress' => 0]);
    if ($existingcm) {
        $existingcm->coursemodule = $existingcm->id;
        return $existingcm;
    }

    $draftid = mamxanh_stage_draft_file($h5ppath);

    $mod = new stdClass();
    $mod->modulename         = 'h5pactivity';
    $mod->module             = (int)$DB->get_field('modules', 'id', ['name' => 'h5pactivity'], MUST_EXIST);
    $mod->course             = $course->id;
    $mod->section            = $sectionnum;
    $mod->visible            = 1;
    $mod->cmidnumber         = $idnumber;
    $mod->name               = $name;
    $mod->intro              = $introhtml;
    $mod->introformat        = FORMAT_HTML;
    $mod->packagefile        = $draftid;
    $mod->grade              = 100;
    $mod->gradecat           = $gradecatid;
    $mod->gradepass          = 70;
    $mod->displayoptions     = 15;
    $mod->enabletracking     = 1;
    $mod->grademethod        = 1; // Highest grade
    $mod->reviewmode         = 1;
    $mod->completion         = COMPLETION_TRACKING_AUTOMATIC;
    $mod->completionview     = 1;

    $info = add_moduleinfo($mod, $course);

    // Pre-deploy H5P package into mdl_h5p so first learner opens instantaneously
    $ctx = context_module::instance($info->coursemodule);
    $fs = get_file_storage();
    $files = $fs->get_area_files($ctx->id, 'mod_h5pactivity', 'package', 0, 'id', false);
    if (!empty($files)) {
        $pkgfile = reset($files);
        $factory = new \core_h5p\factory();
        $cfg = (object)['frame' => 0, 'export' => 0, 'embed' => 0, 'copyright' => 0];
        \core_h5p\helper::save_h5p($factory, $pkgfile, $cfg, false, false);
    }

    return $info;
}

/**
 * Add an authentic ADL SCORM 1.2 activity (mod_scorm) syncing score & completion to Moodle SCORM API.
 */
function mamxanh_ensure_scorm12_activity(stdClass $course, int $sectionnum, int $gradecatid, string $name, string $introhtml, string $scormzippath, string $idnumber): stdClass {
    global $DB;
    $existingcm = $DB->get_record('course_modules', ['course' => $course->id, 'idnumber' => $idnumber, 'deletioninprogress' => 0]);
    if ($existingcm) {
        $existingcm->coursemodule = $existingcm->id;
        return $existingcm;
    }

    $draftid = mamxanh_stage_draft_file($scormzippath);

    $mod = new stdClass();
    $mod->modulename               = 'scorm';
    $mod->module                   = (int)$DB->get_field('modules', 'id', ['name' => 'scorm'], MUST_EXIST);
    $mod->course                   = $course->id;
    $mod->section                  = $sectionnum;
    $mod->visible                  = 1;
    $mod->cmidnumber               = $idnumber;
    $mod->name                     = $name;
    $mod->intro                    = $introhtml;
    $mod->introformat              = FORMAT_HTML;
    $mod->scormtype                = SCORM_TYPE_LOCAL;
    $mod->packagefile              = $draftid;
    $mod->maxgrade                 = 100;
    $mod->gradecat                 = $gradecatid;
    $mod->gradepass                = 70;
    $mod->grademethod              = GRADEHIGHEST;
    $mod->whatgrade                = 0;
    $mod->maxattempt               = 0;
    $mod->forcecompleted           = 0;
    $mod->forcenewattempt          = 0;
    $mod->lastattemptlock          = 0;
    $mod->masteryoverride          = 1;
    $mod->displayattemptstatus     = 1;
    $mod->displaycoursestructure   = 0;
    $mod->updatefreq               = 0;
    $mod->skipview                 = 0;
    $mod->hidebrowse               = 1;
    $mod->hidetoc                  = 1; // Clean full-width player
    $mod->nav                      = 0;
    $mod->auto                     = 0;
    $mod->popup                    = 0; // Inline iframe inside Moodle page
    $mod->width                    = 100;
    $mod->height                   = 680;
    $mod->autocommit               = 1;
    $mod->completion               = COMPLETION_TRACKING_AUTOMATIC;
    $mod->completionview           = 0;
    $mod->completionstatusrequired = 4; // 4 = 'completed'

    return add_moduleinfo($mod, $course);
}

// ===================================================================
// STEP 2/4: Upgrade ALL 6 Existing Courses' Video Lessons to Real HTML5 <video>
// ===================================================================
echo " -> [Step 2/4] Upgrading Video lessons across all 6 courses to real local HTML5 <video> MP4/WebM + .vtt...\n";
$catalog = mamxanh_get_six_courses_catalog();
$catalogByCode = [];
foreach ($catalog as $citem) {
    $catalogByCode[$citem['shortname']] = $citem;
    $course = $DB->get_record('course', ['shortname' => $citem['shortname']]);
    if (!$course) {
        continue;
    }
    $newVideoHtml = mamxanh_build_video_infographic_html($citem['sec1_video']);
    $pages = $DB->get_records('page', ['course' => $course->id], 'id ASC');
    foreach ($pages as $pg) {
        if (stripos($pg->name, 'Video') !== false || strpos($pg->content, 'mx-digital-lesson') !== false) {
            $DB->set_field('page', 'content', $newVideoHtml, ['id' => $pg->id]);
            echo "    [OK] Updated real HTML5 <video> in [{$course->shortname}] Page #{$pg->id}\n";
            break;
        }
    }
    rebuild_course_cache($course->id, true);
}

// ===================================================================
// STEP 3/4: Embed Inline PDFs, H5P & SCORM 1.2 into CHAMSOC01 & CHEBIEN01
// ===================================================================
echo " -> [Step 3/4] Adding Inline PDF (RESOURCELIB_DISPLAY_EMBED), H5P & SCORM 1.2 to CHAMSOC01 & CHEBIEN01...\n";

$chamsoc = $DB->get_record('course', ['shortname' => 'CHAMSOC01'], '*', MUST_EXIST);
$chamsocCat = grade_category::fetch_course_category($chamsoc->id);
$chamsocCat->load_grade_item();

mamxanh_ensure_inline_pdf_resource(
    $chamsoc,
    1,
    'Sổ tay Kỹ thuật Canh tác Nông nghiệp CNC & VietGAP (Xem Trực tiếp PDF)',
    '<p>Tài liệu kỹ thuật chuẩn hóa định dạng PDF được <strong>nhúng xem trực tiếp trên trang bài học Moodle</strong> (khung cao 650px trên máy tính / 480px trên điện thoại kèm thanh công cụ phóng to, thu nhỏ) — <strong>bà con đọc trực tiếp không cần tải về máy</strong>.</p>',
    $pdfs['chamsoc_pdf'],
    'CHAMSOC01_PDF_EMBED'
);

mamxanh_ensure_h5p_activity(
    $chamsoc,
    2,
    (int)$chamsocCat->id,
    'Bài tập H5P Tương tác: Chẩn đoán Dinh dưỡng pH/EC & Sâu bệnh Sinh học IPM',
    '<p>Hoạt động <strong>H5P Question Set</strong> chuẩn quốc tế (<code>mod_h5pactivity</code>): trả lời trực tiếp 3 tình huống kỹ thuật và tự động đồng bộ điểm số vào Sổ điểm Moodle Gradebook.</p>',
    $h5ps['qset_h5p'],
    'CHAMSOC01_H5P_QSET'
);

mamxanh_ensure_scorm12_activity(
    $chamsoc,
    2,
    (int)$chamsocCat->id,
    'Bài giảng Chuẩn SCORM 1.2: Quy trình Thực hành Nông nghiệp Tốt VietGAP & Kiểm soát Mối nguy',
    '<p>Gói bài giảng tương tác đóng gói chuẩn <strong>ADL SCORM 1.2</strong> (<code>imsmanifest.xml</code> + giao tiếp <code>LMSInitialize / LMSSetValue / LMSFinish</code>) tự động đồng bộ điểm số 100/100 và trạng thái hoàn thành về Moodle.</p>',
    $scorm,
    'CHAMSOC01_SCORM12_VIETGAP'
);

mamxanh_ensure_h5p_activity(
    $chamsoc,
    3,
    (int)$chamsocCat->id,
    'Cẩm nang H5P Accordion: An toàn Sinh học Nước tưới & Nấm đối kháng Trichoderma',
    '<p>Học liệu tương tác <strong>H5P Accordion</strong> giúp tra cứu nhanh quy chuẩn lọc đĩa 120 Mesh, ủ <em>Trichoderma</em> và ghi nhật ký truy xuất QR.</p>',
    $h5ps['acc_h5p'],
    'CHAMSOC01_H5P_ACC'
);
rebuild_course_cache($chamsoc->id, true);
echo "    [OK] Upgraded CHAMSOC01 with Inline PDF + 2 H5P Activities + SCORM 1.2\n";

$chebien = $DB->get_record('course', ['shortname' => 'CHEBIEN01'], '*', MUST_EXIST);
$chebienCat = grade_category::fetch_course_category($chebien->id);
$chebienCat->load_grade_item();

mamxanh_ensure_inline_pdf_resource(
    $chebien,
    1,
    'Quy trình Bảo quản Chuỗi Lạnh & Chế biến Sâu Sau Thu hoạch (Xem Trực tiếp PDF)',
    '<p>Tài liệu chuyên khảo kỹ thuật Pre-cooling, Sấy lạnh bơm nhiệt 25–40°C và Kho lạnh MAP/CA <strong>nhúng xem trực tiếp ngay trong trình duyệt</strong> không cần tải file về thiết bị.</p>',
    $pdfs['chebien_pdf'],
    'CHEBIEN01_PDF_EMBED'
);

mamxanh_ensure_h5p_activity(
    $chebien,
    2,
    (int)$chebienCat->id,
    'Bài giảng H5P Course Presentation: Công nghệ Pre-cooling, Sấy lạnh & Bảo quản MAP/CA',
    '<p>Bài trình chiếu tương tác <strong>H5P Course Presentation</strong> tích hợp câu hỏi kiểm tra ngay trên slide và lưu điểm trực tiếp vào Moodle Gradebook.</p>',
    $h5ps['cp_h5p'],
    'CHEBIEN01_H5P_CP'
);

mamxanh_ensure_scorm12_activity(
    $chebien,
    2,
    (int)$chebienCat->id,
    'Bài giảng Chuẩn SCORM 1.2: Quy trình Thực hành Nông nghiệp Tốt VietGAP & Kiểm soát Mối nguy',
    '<p>Gói bài giảng chuẩn <strong>SCORM 1.2</strong> kiểm soát mối nguy Sinh học – Hóa học – Vật lý trong nhà sơ chế đóng gói nông sản sau thu hoạch.</p>',
    $scorm,
    'CHEBIEN01_SCORM12_VIETGAP'
);
rebuild_course_cache($chebien->id, true);
echo "    [OK] Upgraded CHEBIEN01 with Inline PDF + H5P Course Presentation + SCORM 1.2\n";

// ===================================================================
// STEP 4/4: Create Master Course Template [MASTER-AGRITECH-TPL]
// with Sequential Learning Path & Restrict Access Conditions
// ===================================================================
echo " -> [Step 4/4] Creating/Updating Master Course Template [MASTER-AGRITECH-TPL] with Sequential Restrict Access...\n";

$tplshort = 'MASTER-AGRITECH-TPL';
$tplfull  = '[MẪU CHUẨN] Khung Chương trình Tập huấn Nông nghiệp Công nghệ cao (Master Template)';

$existingTpl = $DB->get_record('course', ['shortname' => $tplshort]);
if ($existingTpl) {
    delete_course($existingTpl, false);
    fix_course_sortorder();
}

$catrec = $DB->get_record('course_categories', ['idnumber' => 'MAMXANH_AGTECH_CAT']);
$categoryid = $catrec ? (int)$catrec->id : 1;

$tplcourse = create_course((object)[
    'category'         => $categoryid,
    'shortname'        => $tplshort,
    'fullname'         => $tplfull,
    'idnumber'         => $tplshort,
    'summary'          => '<p>Khóa học Mẫu chuẩn quốc gia thiết kế theo <strong>Lộ trình Học tập Tuần tự 4 Bước khép kín</strong>: (1) Video thực tế HD + Xem trực tiếp PDF kỹ thuật → (2) Học liệu tương tác H5P &amp; SCORM 1.2 → (3) Thực hành hiện trường tại vườn (QR + GPS + Rubric) → (4) Kiểm tra cuối khóa đạt &ge; 70% mở khóa Chứng nhận Số QR. Sẵn sàng nhân bản 1-Chạm cho mọi địa phương và Hợp tác xã!</p>',
    'summaryformat'    => FORMAT_HTML,
    'format'           => 'topics',
    'numsections'      => 4,
    'startdate'        => time() - (7 * 86400),
    'visible'          => 1,
    'enablecompletion' => 1,
    'showgrades'       => 1,
    'showreports'      => 1,
]);

$tplGradeCat = grade_category::fetch_course_category($tplcourse->id);
$tplGradeCat->load_grade_item();
$tplGradecatId = (int)$tplGradeCat->id;

course_create_sections_if_missing($tplcourse, [0, 1, 2, 3, 4]);
$tplSections = [
    0 => ['Hướng dẫn Lộ trình Học tập Tuần tự 4 Bước', 'Hoàn thành lần lượt từng bước để hệ thống tự động mở khóa chuyên đề tiếp theo.'],
    1 => ['Chuyên đề 1 (Bước 1): Video Kỹ thuật Thực tế HD & Xem Trực tiếp Sổ tay PDF', 'Xem video thực địa có phụ đề tiếng Việt và đọc trực tiếp Sổ tay PDF ngay trên web để mở khóa Chuyên đề 2.'],
    2 => ['Chuyên đề 2 (Bước 2): Học liệu Tương tác Chuẩn Quốc tế H5P & Gói Bài giảng SCORM 1.2', 'Yêu cầu mở khóa: Hoàn thành Chuyên đề 1. Thực hành trả lời H5P và gói bài giảng SCORM 1.2 đồng bộ điểm tự động.'],
    3 => ['Chuyên đề 3 (Bước 3): Thực hành Hiện trường tại Vườn (Điểm danh QR/GPS & Chấm Rubric)', 'Yêu cầu mở khóa: Hoàn thành Chuyên đề 2. Điểm danh tại vườn mẫu và nhận điểm Rubric 4 tiêu chí.'],
    4 => ['Chuyên đề 4 (Bước 4): Bài Kiểm tra Cuối khóa (Quiz >= 70%) & Mở khóa Chứng nhận Số QR', 'Yêu cầu mở khóa: Hoàn thành Chuyên đề 3. Đạt từ 70% điểm Quiz trở lên để nhận Chứng nhận Số QR HMAC-SHA256.'],
];
foreach ($tplSections as $snum => [$sname, $ssum]) {
    $sec = $DB->get_record('course_sections', ['course' => $tplcourse->id, 'section' => $snum]);
    if ($sec) {
        $sec->name          = $sname;
        $sec->summary       = '<p>' . htmlspecialchars($ssum) . '</p>';
        $sec->summaryformat = FORMAT_HTML;
        $sec->visible       = 1;
        $DB->update_record('course_sections', $sec);
    }
}

$baseSpec = $catalogByCode['CHAMSOC01'];
$baseSpec['shortname'] = $tplshort;
$baseSpec['sec1_video']['code'] = 'MASTERTPL';

// --- SECTION 1: Real HTML5 Video + Inline PDF (Open immediately) ---
$tplVideoHtml = mamxanh_build_video_infographic_html($baseSpec['sec1_video']);
$vidMod = new stdClass();
$vidMod->modulename        = 'page';
$vidMod->module            = (int)$DB->get_field('modules', 'id', ['name' => 'page'], MUST_EXIST);
$vidMod->course            = $tplcourse->id;
$vidMod->section           = 1;
$vidMod->visible           = 1;
$vidMod->cmidnumber        = 'TPL_SEC1_VIDEO';
$vidMod->name              = 'Bước 1.1 — Video Thực tế HD: Kỹ thuật Tuyển chọn Cây giống & Dinh dưỡng Nhỏ giọt';
$vidMod->intro             = '<p>Video thực tế phát trực tiếp mượt mà bằng trình phát HTML5 <code>&lt;video&gt;</code> kèm phụ đề Tiếng Việt và Infographic thông số chuẩn.</p>';
$vidMod->introformat       = FORMAT_HTML;
$vidMod->page              = ['text' => $tplVideoHtml, 'format' => FORMAT_HTML, 'itemid' => 0];
$vidMod->content           = $tplVideoHtml;
$vidMod->contentformat     = FORMAT_HTML;
$vidMod->display           = 5;
$vidMod->printintro        = 0;
$vidMod->printlastmodified = 0;
$vidMod->completion        = COMPLETION_TRACKING_AUTOMATIC;
$vidMod->completionview    = 1;
$cmVid = add_moduleinfo($vidMod, $tplcourse);
$DB->set_field('page', 'content', $tplVideoHtml, ['id' => $cmVid->instance]);

$cmPdf = mamxanh_ensure_inline_pdf_resource(
    $tplcourse,
    1,
    'Bước 1.2 — Xem Trực tiếp PDF: Sổ tay Kỹ thuật Canh tác Nông nghiệp CNC VietGAP',
    '<p>Đọc trực tiếp Sổ tay PDF ngay trong khung xem 650px (không cần tải về máy) để hoàn tất Bước 1 và mở khóa Bước 2.</p>',
    $pdfs['chamsoc_pdf'],
    'TPL_SEC1_PDF'
);

// --- SECTION 2: H5P + SCORM 1.2 (Restricted until cmVid & cmPdf completed) ---
$cmH5p = mamxanh_ensure_h5p_activity(
    $tplcourse,
    2,
    $tplGradecatId,
    'Bước 2.1 — Tương tác H5P (Question Set): Chẩn đoán pH/EC & Phác đồ Sinh học IPM',
    '<p>Hoạt động H5P chấm điểm tự động vào Gradebook.</p>',
    $h5ps['qset_h5p'],
    'TPL_SEC2_H5P'
);

$cmScorm = mamxanh_ensure_scorm12_activity(
    $tplcourse,
    2,
    $tplGradecatId,
    'Bước 2.2 — Gói Bài giảng Chuẩn SCORM 1.2: Quy trình VietGAP & Kiểm soát Mối nguy',
    '<p>Hoàn thành bài tập SCORM 1.2 để đồng bộ điểm và mở khóa Thực hành tại vườn (Bước 3).</p>',
    $scorm,
    'TPL_SEC2_SCORM'
);

$availSec2 = json_encode([
    'op'    => '&',
    'c'     => [
        ['type' => 'completion', 'cm' => (int)$cmVid->coursemodule, 'e' => 1],
        ['type' => 'completion', 'cm' => (int)$cmPdf->coursemodule, 'e' => 1],
    ],
    'showc' => [true, true],
]);
$DB->set_field('course_sections', 'availability', $availSec2, ['course' => $tplcourse->id, 'section' => 2]);

// --- SECTION 3: Field Practice Garden QR/GPS + Rubric (Restricted until cmH5p & cmScorm completed) ---
$fpMod = new stdClass();
$fpMod->modulename     = 'fieldpractice';
$fpMod->module         = (int)$DB->get_field('modules', 'id', ['name' => 'fieldpractice'], MUST_EXIST);
$fpMod->course         = $tplcourse->id;
$fpMod->section        = 3;
$fpMod->visible        = 1;
$fpMod->cmidnumber     = 'TPL_SEC3_FP';
$fpMod->name           = 'Bước 3.1 — Thực hành Tại vườn: Điểm danh QR Xoay vòng & Chấm Rubric 4 Tiêu chí';
$fpMod->intro          = '<p>Điểm danh thực địa bằng mã QR xoay vòng 5 phút + GPS; Cán bộ kỹ thuật chấm điểm theo Rubric 4 tiêu chí đẩy thẳng vào Gradebook.</p>';
$fpMod->introformat    = FORMAT_HTML;
$fpMod->location_name  = 'Vườn Mẫu Thực nghiệm Nông nghiệp Công nghệ cao Địa phương';
$fpMod->target_lat     = '11.940419';
$fpMod->target_lng     = '108.458313';
$fpMod->grade          = 100;
$fpMod->gradecat       = $tplGradecatId;
$fpMod->gradepass      = 70;
$fpMod->qr_secret      = 'MX2_QR_MASTERTPL';
$fpMod->completion     = COMPLETION_TRACKING_AUTOMATIC;
$fpMod->completionview = 1;
$cmFp = add_moduleinfo($fpMod, $tplcourse);

$availSec3 = json_encode([
    'op'    => '&',
    'c'     => [
        ['type' => 'completion', 'cm' => (int)$cmH5p->coursemodule, 'e' => 1],
        ['type' => 'completion', 'cm' => (int)$cmScorm->coursemodule, 'e' => 1],
    ],
    'showc' => [true, true],
]);
$DB->set_field('course_sections', 'availability', $availSec3, ['course' => $tplcourse->id, 'section' => 3]);

// --- SECTION 4: Final Quiz (12 Qs) + Restricted Certificate Claim Module ---
$quizMod = new stdClass();
$quizMod->modulename              = 'quiz';
$quizMod->module                  = (int)$DB->get_field('modules', 'id', ['name' => 'quiz'], MUST_EXIST);
$quizMod->course                  = $tplcourse->id;
$quizMod->section                 = 4;
$quizMod->visible                 = 1;
$quizMod->cmidnumber              = 'TPL_SEC4_QUIZ';
$quizMod->name                    = 'Bước 4.1 — Bài Kiểm tra Cuối khóa (12 Câu hỏi Trắc nghiệm - Yêu cầu >= 70%)';
$quizMod->intro                   = '<p>Hoàn thành tối thiểu <strong>70% (7.0/10 điểm)</strong> để mở khóa <strong>Chứng nhận Hoàn thành Khóa học (QR HMAC-SHA256)</strong>.</p>';
$quizMod->introformat             = FORMAT_HTML;
$quizMod->timeopen                = 0;
$quizMod->timeclose               = 0;
$quizMod->timelimit               = 0;
$quizMod->overduehandling         = 'autosubmit';
$quizMod->graceperiod             = 0;
$quizMod->gradecat                = $tplGradecatId;
$quizMod->grade                   = 10.0;
$quizMod->gradepass               = 7.0;
$quizMod->attempts                = 0;
$quizMod->grademethod             = QUIZ_GRADEHIGHEST;
$quizMod->questionsperpage        = 12;
$quizMod->navmethod               = 'free';
$quizMod->shuffleanswers          = 0;
$quizMod->preferredbehaviour      = 'deferredfeedback';
$quizMod->canredoquestions        = 0;
$quizMod->attemptonlast           = 0;
$quizMod->showuserpicture         = 0;
$quizMod->decimalpoints           = 2;
$quizMod->questiondecimalpoints   = -1;
$quizMod->showblocks              = 0;
$quizMod->quizpassword            = '';
$quizMod->subnet                  = '';
$quizMod->delay1                  = 0;
$quizMod->delay2                  = 0;
$quizMod->browsersecurity         = '-';
$reviewbits = 69904;
foreach (['attempt', 'correctness', 'maxmarks', 'marks', 'specificfeedback', 'generalfeedback', 'rightanswer', 'overallfeedback'] as $rf) {
    $quizMod->{'review' . $rf} = $reviewbits;
    $quizMod->{$rf . 'during'} = 1;
    $quizMod->{$rf . 'immediately'} = 1;
    $quizMod->{$rf . 'open'} = 1;
    $quizMod->{$rf . 'closed'} = 1;
}
$quizMod->completion          = COMPLETION_TRACKING_AUTOMATIC;
$quizMod->completionview      = 0;
$quizMod->completionusegrade  = 1;
$quizMod->completionpassgrade = 1;

$cmQuiz = add_moduleinfo($quizMod, $tplcourse);
$quizObj = $DB->get_record('quiz', ['id' => $cmQuiz->instance], '*', MUST_EXIST);
$quizObj->cmid = $cmQuiz->coursemodule;

if (!$DB->record_exists('quiz_sections', ['quizid' => $quizObj->id])) {
    $DB->insert_record('quiz_sections', (object)[
        'quizid' => $quizObj->id, 'firstslot' => 1, 'heading' => '', 'shufflequestions' => 0,
    ]);
}
$quizCtx = context_module::instance($cmQuiz->coursemodule);
$qCat = question_make_default_categories([$quizCtx]);
$qType = question_bank::get_qtype('multichoice');

foreach ($baseSpec['questions'] as $qidx => $qitem) {
    [$qtext, $opts, $correctIdx] = $qitem;
    $question = (object)[
        'category' => $qCat->id, 'qtype' => 'multichoice', 'createdby' => $USER->id, 'contextid' => $quizCtx->id,
    ];
    $fromform = (object)[
        'category'                 => $qCat->id . ',' . $quizCtx->id,
        'name'                     => sprintf('MASTER-TPL - Câu %02d', $qidx + 1),
        'questiontext'             => ['text' => '<p>' . htmlspecialchars($qtext) . '</p>', 'format' => FORMAT_HTML],
        'generalfeedback'          => ['text' => '<p>Kiến thức chuẩn Khung Chương trình Tập huấn Nông nghiệp CNC.</p>', 'format' => FORMAT_HTML],
        'defaultmark'              => 1.0,
        'penalty'                  => 0.0,
        'status'                   => \core_question\local\bank\question_version_status::QUESTION_STATUS_READY,
        'single'                   => 1,
        'shuffleanswers'           => 0,
        'answernumbering'          => 'abc',
        'showstandardinstruction'  => 0,
        'correctfeedback'          => ['text' => 'Chính xác!', 'format' => FORMAT_HTML],
        'partiallycorrectfeedback' => ['text' => 'Gần đúng.', 'format' => FORMAT_HTML],
        'incorrectfeedback'        => ['text' => 'Chưa chính xác.', 'format' => FORMAT_HTML],
        'shownumcorrect'           => 1,
        'answer'                   => [],
        'fraction'                 => [],
        'feedback'                 => [],
    ];
    foreach ($opts as $oi => $opttext) {
        $isRight = ((int)$oi === (int)$correctIdx);
        $fromform->answer[$oi]   = ['text' => $opttext, 'format' => FORMAT_PLAIN];
        $fromform->fraction[$oi] = $isRight ? '1.0' : '0.0';
        $fromform->feedback[$oi] = ['text' => $isRight ? 'Đáp án đúng.' : 'Chưa phù hợp.', 'format' => FORMAT_HTML];
    }
    $savedQ = $qType->save_question($question, $fromform);
    quiz_add_quiz_question($savedQ->id, $quizObj, 1, 1.0);
}
\mod_quiz\quiz_settings::create($quizObj->id)->get_grade_calculator()->recompute_quiz_sumgrades();
$quizObj = $DB->get_record('quiz', ['id' => $quizObj->id], '*', MUST_EXIST);
quiz_grade_item_update($quizObj);

$quizGradeItem = $DB->get_record('grade_items', [
    'courseid' => $tplcourse->id, 'itemtype' => 'mod', 'itemmodule' => 'quiz', 'iteminstance' => $quizObj->id,
], '*', MUST_EXIST);
$quizGradeItem->categoryid = $tplGradecatId;
$quizGradeItem->gradepass  = 7.0;
$DB->update_record('grade_items', $quizGradeItem);

// Restrict Section 4 until FieldPractice in Section 3 is completed
$availSec4 = json_encode([
    'op'    => '&',
    'c'     => [
        ['type' => 'completion', 'cm' => (int)$cmFp->coursemodule, 'e' => 1],
    ],
    'showc' => [true],
]);
$DB->set_field('course_sections', 'availability', $availSec4, ['course' => $tplcourse->id, 'section' => 4]);

// Add Certificate Unlock Module inside Section 4 requiring Quiz grade >= 70%
$certPageHtml = '<div style="background:linear-gradient(135deg,#1B4332,#2E7D32);color:#FFF;border-radius:22px;padding:26px;text-align:center;">'
    . '<div style="font-size:3rem;margin-bottom:8px;"></div>'
    . '<h3 style="color:#FFF;font-weight:900;">Chúc mừng bạn đã hoàn thành Lộ trình 4 Bước & Đạt >= 70% Bài Kiểm tra!</h3>'
    . '<p style="color:#D8F3DC;max-width:640px;margin:10px auto 18px;">Chứng nhận Số ký mật mã <strong>HMAC-SHA256</strong> và Huy hiệu Kỹ thuật của bạn đã được kích hoạt tự động. Nhấn nút bên dưới để xem bản in Web, tải file PDF chuẩn Tiếng Việt và quét mã QR xác thực.</p>'
    . '<a href="/local/mamxanhcert/index.php" class="btn btn-warning" style="min-height:52px;display:inline-flex;align-items:center;border-radius:14px;font-weight:900;color:#1B4332;padding:0 26px;">Mở & Tải Chứng nhận Số QR Ngay →</a>'
    . '</div>';

$availCertMod = json_encode([
    'op'    => '&',
    'c'     => [
        ['type' => 'completion', 'cm' => (int)$cmQuiz->coursemodule, 'e' => 1],
        ['type' => 'grade', 'id' => (int)$quizGradeItem->id, 'min' => 70],
    ],
    'showc' => [true, true],
]);

$certMod = new stdClass();
$certMod->modulename        = 'page';
$certMod->module            = (int)$DB->get_field('modules', 'id', ['name' => 'page'], MUST_EXIST);
$certMod->course            = $tplcourse->id;
$certMod->section           = 4;
$certMod->visible           = 1;
$certMod->cmidnumber        = 'TPL_SEC4_CERT_UNLOCK';
$certMod->availability      = $availCertMod;
$certMod->name              = 'Bước 4.2 — Nhận Chứng nhận Số QR Hoàn thành Khóa học (Mở khóa khi Quiz >= 70%)';
$certMod->intro             = '<p>Tự động mở khóa ngay khi bài kiểm tra cuối khóa đạt từ 70% trở lên.</p>';
$certMod->introformat       = FORMAT_HTML;
$certMod->page              = ['text' => $certPageHtml, 'format' => FORMAT_HTML, 'itemid' => 0];
$certMod->content           = $certPageHtml;
$certMod->contentformat     = FORMAT_HTML;
$certMod->display           = 5;
$certMod->printintro        = 0;
$certMod->printlastmodified = 0;
$certMod->completion        = COMPLETION_TRACKING_AUTOMATIC;
$certMod->completionview    = 1;
$cmCert = add_moduleinfo($certMod, $tplcourse);
$DB->set_field('page', 'content', $certPageHtml, ['id' => $cmCert->instance]);
$DB->set_field('course_modules', 'availability', $availCertMod, ['id' => $cmCert->coursemodule]);

// Ensure Course Badge for Master Template
$now = time();
$DB->insert_record('badge', (object)[
    'name'            => 'Chuyên gia Tập huấn Chuẩn MASTER-AGRITECH-TPL',
    'description'     => 'Hoàn thành xuất sắc Khung Chương trình Tập huấn Nông nghiệp Công nghệ cao Mẫu Chuẩn.',
    'timecreated'     => $now,
    'timemodified'    => $now,
    'usercreated'     => $admin->id,
    'usermodified'    => $admin->id,
    'issuername'      => 'Mầm Xanh LMS 2.0',
    'issuerurl'       => 'https://moodle.lvh.me:8443',
    'issuercontact'   => 'admin@mamxanh.vn',
    'type'            => 2,
    'courseid'        => $tplcourse->id,
    'message'         => 'Chúc mừng bạn đạt Huy hiệu Khóa học Mẫu Chuẩn!',
    'messagesubject'  => 'Đã mở khóa Huy hiệu Khóa học Mẫu!',
    'attachment'      => 1,
    'notification'    => 0,
    'status'          => 1,
    'version'         => '2.0',
    'language'        => 'vi',
    'imageauthorname' => 'Mam Xanh AgTech',
    'imageauthoremail'=> 'admin@mamxanh.vn',
    'imageauthorurl'  => 'https://moodle.lvh.me:8443',
    'imagecaption'    => $tplshort,
]);

// Enrol CBKT & Core Farmers into MASTER-AGRITECH-TPL
$manualplugin = enrol_get_plugin('manual');
$selfplugin   = enrol_get_plugin('self');
$manualinst   = null;
foreach (enrol_get_instances($tplcourse->id, false) as $ei) {
    if ($ei->enrol === 'manual') {
        $manualinst = $ei;
        $manualplugin->update_status($ei, ENROL_INSTANCE_ENABLED);
    } else if ($ei->enrol === 'self' && $selfplugin) {
        $selfplugin->update_status($ei, ENROL_INSTANCE_ENABLED);
    }
}
if (!$manualinst) {
    $mid = $manualplugin->add_default_instance($tplcourse);
    $manualinst = $DB->get_record('enrol', ['id' => $mid]);
}
$cbktrole    = $DB->get_record('role', ['shortname' => 'canbokythuat'], '*', MUST_EXIST);
$studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
$cbktuser    = $DB->get_record('user', ['username' => 'cbkt_pilot']);
if ($manualinst && $cbktuser) {
    $manualplugin->enrol_user($manualinst, $cbktuser->id, $cbktrole->id);
}
$corefarmers = $DB->get_records_select('user', "deleted = 0 AND username IN ('nd_rau_lamdong', 'nd_saurieng_daklak', 'nd_vanan')");
foreach ($corefarmers as $fu) {
    $manualplugin->enrol_user($manualinst, $fu->id, $studentrole->id);
}

// Place MASTER-AGRITECH-TPL at top sortorder so it appears prominently on Dashboards
$DB->set_field('course', 'sortorder', 10001, ['id' => $tplcourse->id]);
fix_course_sortorder();
rebuild_course_cache($tplcourse->id, true);
purge_all_caches();
@exec('chown -R www-data:www-data /var/moodledata /tmp/moodle_localcache /tmp/moodle_temp >/dev/null 2>&1');

echo "    [OK] Created Master Course Template #{$tplcourse->id} [{$tplshort}] with strict 4-Step Sequential Restrict Access!\n";
echo "=== [Stage 7] All Defense Committee Upgrades Deployed Successfully! ===\n";
