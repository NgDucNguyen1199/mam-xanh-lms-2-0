<?php
namespace local_mamxanhapp;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observers for smart role-based login routing and automatic certificate/badge issuance.
 */
class observer {
    /**
     * Route users to their tailored dashboard right after login.
     */
    public static function user_loggedin(\core\event\user_loggedin $event): void {
        global $CFG, $SESSION;

        $userid = (int)$event->userid;
        if ($userid <= 0 || isguestuser($userid)) {
            return;
        }

        $syscontext = \context_system::instance();
        $wants = $SESSION->wantsurl ?? '';
        $isgeneric = empty($wants);

        if (!$isgeneric) {
            $parsed = parse_url($wants, PHP_URL_PATH) ?: '/';
            $genericpaths = ['/', '/index.php', '/my/', '/my', '/my/index.php', '/my/courses.php', '/login/index.php'];
            if (in_array($parsed, $genericpaths, true)) {
                $isgeneric = true;
            }
        }

        if (!$isgeneric) {
            return;
        }

        if (is_siteadmin($userid)) {
            $SESSION->wantsurl = (new \moodle_url('/local/mamxanhapp/manage.php'))->out(false);
        } else if (has_capability('local/mamxanhapp:manage', $syscontext, $userid)) {
            // Cán bộ kỹ thuật -> Instructor Management & Analytics Hub
            $SESSION->wantsurl = (new \moodle_url('/local/mamxanhapp/manage.php'))->out(false);
        } else {
            // Nông dân -> Mobile-First Farmer Dashboard
            $SESSION->wantsurl = (new \moodle_url('/local/mamxanhapp/index.php'))->out(false);
        }
    }

    /**
     * Automatically issue HMAC-SHA256 Digital Certificate and Course Badge when Quiz score >= 70%.
     */
    public static function quiz_attempt_submitted(\mod_quiz\event\attempt_submitted $event): void {
        global $CFG, $DB;

        try {
            $attempt = $DB->get_record('quiz_attempts', ['id' => $event->objectid]);
            if (!$attempt || $attempt->state !== 'finished') {
                return;
            }
            $quiz = $DB->get_record('quiz', ['id' => $attempt->quiz]);
            if (!$quiz || (float)$quiz->sumgrades <= 0) {
                return;
            }

            $pct = round(((float)$attempt->sumgrades / (float)$quiz->sumgrades) * 100.0, 2);
            $userid = (int)$attempt->userid;
            $courseid = (int)$event->courseid;

            // Refresh activity completion state if enabled
            require_once($CFG->libdir . '/completionlib.php');
            $course = get_course($courseid);
            $completion = new \completion_info($course);
            if ($completion->is_enabled()) {
                $cm = get_coursemodule_from_instance('quiz', $quiz->id, $courseid, false, IGNORE_MISSING);
                if ($cm && $completion->is_enabled($cm)) {
                    $completion->update_state($cm, COMPLETION_COMPLETE, $userid);
                }
            }

            if ($pct >= 70.0) {
                $certlib = $CFG->dirroot . '/local/mamxanhcert/lib.php';
                if (file_exists($certlib)) {
                    require_once($certlib);
                    if (function_exists('local_mamxanhcert_issue_certificate')) {
                        local_mamxanhcert_issue_certificate($userid, $courseid, $pct);
                    }
                }

                // Award course badge if present and not yet issued
                $badges = $DB->get_records('badge', ['courseid' => $courseid, 'status' => 1]);
                foreach ($badges as $b) {
                    if (!$DB->record_exists('badge_issued', ['badgeid' => $b->id, 'userid' => $userid])) {
                        $DB->insert_record('badge_issued', (object)[
                            'badgeid'    => $b->id,
                            'userid'     => $userid,
                            'uniquehash' => sha1($b->id . '|' . $userid . '|' . time()),
                            'dateissued' => time(),
                            'dateexpire' => null,
                            'visible'    => 1,
                            'issuernotified' => time(),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            debugging('MamXanh quiz_attempt_submitted observer warning: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
}
