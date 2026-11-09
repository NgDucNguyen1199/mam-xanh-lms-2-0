<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');
require_once($CFG->libdir . '/blocklib.php');

$sysctx = context_system::instance();

// Delete all current block instances on my-index
$existing_blocks = $DB->get_records_select('block_instances', "pagetypepattern = 'my-index'");
foreach ($existing_blocks as $eb) {
    blocks_delete_instance($eb);
}

// System default private page
$sys_page = $DB->get_record('my_pages', ['userid' => null, 'name' => '__default', 'private' => 1]);
$sys_subpage = $sys_page ? $sys_page->id : 2;

// 1. Add Default Blocks to System Context (id: $sysctx->id, subpagepattern: $sys_subpage)
$blocks_def = [
    ['name' => 'recentlyaccessedcourses', 'region' => 'content', 'weight' => 0],
    ['name' => 'myoverview', 'region' => 'content', 'weight' => 1],
    ['name' => 'calendar_month', 'region' => 'side-pre', 'weight' => 0],
    ['name' => 'calendar_upcoming', 'region' => 'side-pre', 'weight' => 1]
];

foreach ($blocks_def as $binfo) {
    $bi = new stdClass();
    $bi->blockname = $binfo['name'];
    $bi->parentcontextid = $sysctx->id;
    $bi->showinsubcontexts = 0;
    $bi->pagetypepattern = 'my-index';
    $bi->subpagepattern = $sys_subpage;
    $bi->defaultregion = $binfo['region'];
    $bi->defaultweight = $binfo['weight'];
    $bi->configdata = '';
    $bi->timecreated = time();
    $bi->timemodified = time();
    $DB->insert_record('block_instances', $bi);
}
echo "Configured 4 clean system default blocks for my-index (subpage {$sys_subpage})\n";

// 2. For each existing user with a private page in my_pages, configure their blocks with their specific subpagepattern
$user_pages = $DB->get_records_select('my_pages', "userid IS NOT NULL AND private = 1");
foreach ($user_pages as $up) {
    $uctx = context_user::instance($up->userid);
    foreach ($blocks_def as $binfo) {
        $bi = new stdClass();
        $bi->blockname = $binfo['name'];
        $bi->parentcontextid = $uctx->id;
        $bi->showinsubcontexts = 0;
        $bi->pagetypepattern = 'my-index';
        $bi->subpagepattern = $up->id;
        $bi->defaultregion = $binfo['region'];
        $bi->defaultweight = $binfo['weight'];
        $bi->configdata = '';
        $bi->timecreated = time();
        $bi->timemodified = time();
        $DB->insert_record('block_instances', $bi);
    }
}
echo "Configured individual blocks for " . count($user_pages) . " user private dashboards.\n";
