<?php
defined('MOODLE_INTERNAL') || die();

global $CFG, $OUTPUT, $PAGE, $SESSION, $SITE;

$req = optional_param('lang', '', PARAM_ALPHA);
if (($req === 'vi' || $req === 'en') && isset($SESSION) && is_object($SESSION)) {
    $SESSION->lang = $req;
}

$pwatags = '<link rel="manifest" href="/manifest.json"><meta name="theme-color" content="#1B4332"><link rel="apple-touch-icon" href="/pwa-icons/icon-192.png">';
if (strpos($CFG->additionalhtmlhead ?? '', '/manifest.json') === false) {
    $CFG->additionalhtmlhead = ($CFG->additionalhtmlhead ?? '') . $pwatags;
}

$isen = strpos(current_language(), 'en') === 0;
$bodyattributes = $OUTPUT->body_attributes();

$templatecontext = [
    'sitename'       => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), 'escape' => false]),
    'output'         => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'isen'           => $isen,
    'isvi'           => !$isen,
    'langviurl'      => (new moodle_url('/login/index.php', ['lang' => 'vi']))->out(false),
    'langenurl'      => (new moodle_url('/login/index.php', ['lang' => 'en']))->out(false),
];

echo $OUTPUT->render_from_template('theme_agritrain_child/login', $templatecontext);
