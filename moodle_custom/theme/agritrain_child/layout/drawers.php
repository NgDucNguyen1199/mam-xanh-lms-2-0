<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/theme/agritrain_child/lib.php');
if (file_exists($CFG->dirroot . '/local/mamxanhapp/lib.php')) {
    require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');
}

// 1. Sticky Bilingual Switch (?lang=vi | ?lang=en)
global $CFG, $DB, $OUTPUT, $PAGE, $SESSION, $SITE, $USER;
$reqlang = optional_param('lang', '', PARAM_ALPHA);
if (($reqlang === 'vi' || $reqlang === 'en') && isset($SESSION) && is_object($SESSION)) {
    $SESSION->lang = $reqlang;
}
$isen = strpos(current_language(), 'en') === 0;

// 2. Strict 403 RBAC check on core admin endpoints for non-admins
$reqpath = $PAGE->has_set_url() ? ($PAGE->url->get_path() ?: '') : ($_SERVER['SCRIPT_NAME'] ?? '');
$adminrestricted = ['/admin/search.php', '/admin/user.php', '/admin/settings.php', '/admin/index.php', '/admin/roles/manage.php'];
if (isloggedin() && !isguestuser() && !is_siteadmin() && in_array($reqpath, $adminrestricted, true)) {
    if (function_exists('local_mamxanhapp_send_403_and_exit')) {
        local_mamxanhapp_send_403_and_exit();
    }
}

// 3. Smart Post-Login & Home Routing for Farmers and CBKT
// Note: /my/index.php is preserved for direct user access to standard customizable Dashboard
if (isloggedin() && !isguestuser() && !$PAGE->user_is_editing()) {
    $noredir = optional_param('noredir', 0, PARAM_INT);
    if (!$noredir && in_array($PAGE->pagetype, ['site-index'], true)) {
        $syscontext = context_system::instance();
        if (!is_siteadmin()) {
            if (has_capability('local/mamxanhapp:manage', $syscontext)) {
                redirect(new moodle_url('/local/mamxanhapp/manage.php'));
            } else {
                redirect(new moodle_url('/local/mamxanhapp/index.php'));
            }
        }
    }
}

// 4. Inject PWA Manifest & Theme Meta via $CFG->additionalhtmlhead
$pwatags = '<link rel="manifest" href="/manifest.json"><meta name="theme-color" content="#1E3A2F"><link rel="apple-touch-icon" href="/pwa-icons/icon-192.png">';
if (strpos($CFG->additionalhtmlhead ?? '', '/manifest.json') === false) {
    $CFG->additionalhtmlhead = ($CFG->additionalhtmlhead ?? '') . $pwatags;
}

$addblockbutton = $OUTPUT->addblockbutton();
$roletype = function_exists('local_mamxanhapp_user_role_type') ? local_mamxanhapp_user_role_type() : 'farmer';

if ($PAGE->pagetype === 'my-index') {
    $blockdraweropen = true;
    $courseindexopen = false;
} else if (isloggedin() && ($roletype === 'admin' || $roletype === 'cbkt')) {
    $courseindexopen = (get_user_preferences('drawer-open-index', false) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block', false) == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

// 5. Build Course Lesson Tiles/Cards HTML when viewing a course
$mxcoursetileshtml = '';
if (strpos($PAGE->pagetype, 'course-view-') === 0 && isset($COURSE->id) && (int)$COURSE->id !== SITEID && !$PAGE->user_is_editing()) {
    $mxcoursetileshtml = theme_agritrain_child_build_course_tiles_html($COURSE);
}

$extraclasses = ['uses-drawers', 'mx-agritrain-body'];
if ($courseindexopen) {
    $extraclasses[] = 'drawer-open-index';
}
if ($mxcoursetileshtml !== '') {
    $extraclasses[] = 'mx-has-course-tiles';
}

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
if (!$hasblocks) {
    $blockdraweropen = false;
}
$courseindex = core_course_drawer();
if (!$courseindex) {
    $courseindexopen = false;
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation()) {
    $tablistnav = $PAGE->has_tablist_secondary_navigation();
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
    if (!is_null($overflowdata)) {
        $overflow = $overflowdata->export_for_template($OUTPUT);
    }
}

$primary = new core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() && !$PAGE->has_secondary_navigation();
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

$header = $PAGE->activityheader;
$headercontent = $header->export_for_template($renderer);

// 6. Contextual Back Navigation & Voice Reader (#mx-tts-player)
$dashboardurl = ($roletype === 'cbkt' || $roletype === 'admin')
    ? (new moodle_url('/local/mamxanhapp/manage.php'))->out(false)
    : (new moodle_url('/local/mamxanhapp/index.php'))->out(false);

$mxshowtts = false;
$mxbackurl = '';
$mxbacklabel = '';

if (!empty($PAGE->cm->id) && isset($COURSE->id) && (int)$COURSE->id !== SITEID) {
    $mxshowtts = true;
    $mxbackurl = (new moodle_url('/course/view.php', ['id' => $COURSE->id]))->out(false);
    $mxbacklabel = $isen ? '← Back to Course / Dashboard' : '← Quay lại khóa học / Dashboard';
} else if (strpos($PAGE->pagetype, 'course-view-') === 0 && isset($COURSE->id) && (int)$COURSE->id !== SITEID) {
    $mxbackurl = $dashboardurl;
    $mxbacklabel = $isen ? '← Back to Dashboard' : '← Quay lại Dashboard';
}

// 7. Language Switcher URLs preserving query parameters
if ($PAGE->has_set_url()) {
    $viurl = new moodle_url($PAGE->url, ['lang' => 'vi']);
    $enurl = new moodle_url($PAGE->url, ['lang' => 'en']);
} else {
    $viurl = new moodle_url('/local/mamxanhapp/index.php', ['lang' => 'vi']);
    $enurl = new moodle_url('/local/mamxanhapp/index.php', ['lang' => 'en']);
}

$curpath = $PAGE->has_set_url() ? ($PAGE->url->get_path() ?: '') : '';

$templatecontext = [
    'sitename'                  => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), 'escape' => false]),
    'output'                    => $OUTPUT,
    'sidepreblocks'             => $blockshtml,
    'hasblocks'                 => $hasblocks,
    'bodyattributes'            => $bodyattributes,
    'courseindexopen'           => $courseindexopen,
    'blockdraweropen'           => $blockdraweropen,
    'courseindex'               => $courseindex,
    'primarymoremenu'           => $primarymenu['moremenu'],
    'secondarymoremenu'         => $secondarynavigation ?: false,
    'mobileprimarynav'          => $primarymenu['mobileprimarynav'],
    'usermenu'                  => $primarymenu['user'],
    'langmenu'                  => $primarymenu['lang'],
    'forceblockdraweropen'      => $forceblockdraweropen,
    'regionmainsettingsmenu'    => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'overflow'                  => $overflow,
    'headercontent'             => $headercontent,
    'addblockbutton'            => $addblockbutton,

    // Customizable Dashboard Controls
    'ismyindex'                 => ($PAGE->pagetype === 'my-index'),
    'is_editing'                => $PAGE->user_is_editing(),
    'resetpageurl'              => (new moodle_url('/my/index.php', ['edit' => 1, 'reset' => 1, 'sesskey' => sesskey()]))->out(false),
    'stopediturl'               => (new moodle_url('/my/index.php', ['edit' => 0]))->out(false),
    'startediturl'              => (new moodle_url('/my/index.php', ['edit' => 1]))->out(false),
    'str_resetpage'             => $isen ? 'Reset page to default' : 'Đặt lại trang về mặc định',
    'str_stopedit'              => $isen ? 'Stop customising this page' : 'Ngừng chỉnh sửa trang này',
    'str_startedit'             => $isen ? 'Customise this page' : 'Chỉnh sửa trang này',

    // AgTech Smart UI Context
    'isdashboard'               => (strpos($curpath, '/local/mamxanhapp/index.php') !== false),
    'showsubnav'                => (isloggedin() && !isguestuser()),
    'lbl_subnav'                => $isen ? 'Learning tools' : 'Công cụ học tập',
    'isen'                      => $isen,
    'isvi'                      => !$isen,
    'langviurl'                 => $viurl->out(false),
    'langenurl'                 => $enurl->out(false),
    'mxcoursetileshtml'         => $mxcoursetileshtml,
    'mxbackurl'                 => $mxbackurl,
    'mxbacklabel'               => $mxbacklabel,
    'mxhasback'                 => ($mxbackurl !== ''),
    'mxdashboardurl'            => $dashboardurl,
    'mxagriopsurl'              => (new moodle_url('/local/agriops/index.php'))->out(false),
    'mxcalcurl'                 => (new moodle_url('/local/mamxanhapp/calculator.php'))->out(false),
    'mxcerturl'                 => (new moodle_url('/local/mamxanhcert/index.php'))->out(false),
    'mxprofileurl'              => (new moodle_url('/local/farmerprofile/index.php'))->out(false),
    'navhomeactive'             => (strpos($curpath, '/local/mamxanhapp/index.php') !== false || strpos($curpath, '/local/mamxanhapp/manage.php') !== false || strpos($curpath, '/course/') !== false),
    'navagriactive'             => (strpos($curpath, '/local/agriops/') !== false),
    'navcalcactive'             => (strpos($curpath, '/local/mamxanhapp/calculator.php') !== false),
    'navcertactive'             => (strpos($curpath, '/local/mamxanhcert/') !== false),
    'navprofactive'             => (strpos($curpath, '/local/farmerprofile/') !== false),
    'lbl_nav_home'              => ($roletype === 'cbkt' || $roletype === 'admin') ? ($isen ? 'Manage' : 'Quản lý') : ($isen ? 'Courses' : 'Khóa học'),
    'lbl_nav_agri'              => $isen ? 'VietGAP' : 'Nhật ký',
    'lbl_nav_calc'              => $isen ? 'Tools' : 'Công cụ',
    'lbl_nav_cert'              => $isen ? 'Certs' : 'Chứng nhận',
    'lbl_nav_prof'              => $isen ? 'Farm' : 'Nông hộ',
];

echo $OUTPUT->render_from_template('theme_agritrain_child/drawers', $templatecontext);
