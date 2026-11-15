<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Load Boost parent main SCSS content.
 */
function theme_agritrain_child_get_main_scss_content($theme): string {
    global $CFG;
    require_once($CFG->dirroot . '/theme/boost/lib.php');
    $boost = theme_config::load('boost');
    return theme_boost_get_main_scss_content($boost);
}

/**
 * Pre-SCSS variables injected before Bootstrap compilation.
 */
function theme_agritrain_child_get_pre_scss($theme): string {
    global $CFG;
    $file = $CFG->dirroot . '/theme/agritrain_child/scss/pre.scss';
    return file_exists($file) ? file_get_contents($file) : '';
}

/**
 * Post-SCSS rules injected after Bootstrap & Boost compilation.
 */
function theme_agritrain_child_get_extra_scss($theme): string {
    global $CFG;
    $file = $CFG->dirroot . '/theme/agritrain_child/scss/post.scss';
    return file_exists($file) ? file_get_contents($file) : '';
}

/**
 * Clean availability info HTML/string into concise plain text for locked cards.
 */
function theme_agritrain_child_clean_avail_info($availinfo, bool $en): string {
    if (empty($availinfo)) {
        return $en
            ? 'Complete the previous required learning step to unlock this module.'
            : 'Hoàn thành đầy đủ học liệu ở chuyên đề trước để mở khóa bước này.';
    }
    if (is_string($availinfo)) {
        $txt = trim(preg_replace('/\s+/', ' ', strip_tags($availinfo)));
        if ($txt !== '') {
            return $txt;
        }
    }
    return $en
        ? 'Complete the previous required learning step to unlock this module.'
        : 'Hoàn thành đầy đủ học liệu ở chuyên đề trước để mở khóa bước này.';
}

/**
 * Build the flat, academic course outline (general section, topics, assessments) for /course/view.php.
 * Layout intentionally mirrors a classic university LMS: section headings with simple activity rows,
 * plus a thin overall progress bar. No emoji and no decorative widgets.
 */
function theme_agritrain_child_build_course_tiles_html(stdClass $course): string {
    global $CFG, $DB, $USER;

    if ((int)$course->id === SITEID) {
        return '';
    }
    require_once($CFG->libdir . '/completionlib.php');
    $en = strpos(current_language(), 'en') === 0;

    $modinfo = get_fast_modinfo($course, $USER->id);
    $completion = new completion_info($course);
    $sections = $modinfo->get_section_info_all();

    $sectionblocks = [];
    $totalall = 0;
    $doneall = 0;
    $dfmt = $en ? 'D, d M Y, H:i' : 'd/m/Y H:i';

    foreach ($sections as $secnum => $secinfo) {
        if (!$secinfo->visible && !has_capability('moodle/course:viewhiddensections', context_course::instance($course->id))) {
            continue;
        }
        $cmids = $modinfo->sections[$secnum] ?? [];
        $secsummary = !empty($secinfo->summary) ? format_text($secinfo->summary, $secinfo->summaryformat) : '';
        if (empty($cmids) && $secsummary === '') {
            continue;
        }

        $seclocked = (!$secinfo->uservisible && !$secinfo->available);
        $sectotal = 0;
        $secdone = 0;
        $rows = '';

        foreach ($cmids as $cmid) {
            if (!isset($modinfo->cms[$cmid])) {
                continue;
            }
            $cm = $modinfo->cms[$cmid];
            if (!$cm->visible && !has_capability('moodle/course:viewhiddenactivities', $cm->context)) {
                continue;
            }
            if ($cm->modname === 'label') {
                $labelhtml = $cm->get_formatted_content(['overflowdiv' => true, 'noclean' => true]);
                if (trim(strip_tags($labelhtml)) !== '') {
                    $rows .= '<li class="mx-act-row mx-act-label">' . $labelhtml . '</li>';
                }
                continue;
            }

            $cmlocked = $seclocked || (!$cm->uservisible && !$cm->available);
            $isdone = false;
            if ($completion->is_enabled($cm)) {
                $sectotal++;
                $totalall++;
                $cdata = $completion->get_data($cm, false, $USER->id);
                $isdone = !empty($cdata->completionstate) && ((int)$cdata->completionstate !== COMPLETION_INCOMPLETE);
                if ($isdone) {
                    $secdone++;
                    $doneall++;
                }
            }

            $name = format_string($cm->name);
            $cmurl = (!$cmlocked && $cm->url) ? $cm->url->out(false) : '';
            $typename = $cm->get_module_type_name();
            $iconurl = $cm->get_icon_url()->out(false);

            $meta = [$typename];
            if ($cm->modname === 'assign') {
                $arec = $DB->get_record('assign', ['id' => $cm->instance], 'allowsubmissionsfromdate,duedate');
                if ($arec && (int)$arec->allowsubmissionsfromdate > 0) {
                    $meta[] = ($en ? 'Opened: ' : 'Đã mở: ') . userdate((int)$arec->allowsubmissionsfromdate, $dfmt);
                }
                if ($arec && (int)$arec->duedate > 0) {
                    $meta[] = ($en ? 'Due: ' : 'Đến hạn: ') . userdate((int)$arec->duedate, $dfmt);
                }
            } else if ($cm->modname === 'quiz') {
                $q = $DB->get_record('quiz', ['id' => $cm->instance], 'timelimit,timeclose');
                if ($q && (int)$q->timelimit > 0) {
                    $meta[] = ($en ? 'Time limit: ' : 'Thời gian: ') . format_time((int)$q->timelimit);
                }
                if ($q && (int)$q->timeclose > 0) {
                    $meta[] = ($en ? 'Closes: ' : 'Đóng: ') . userdate((int)$q->timeclose, $dfmt);
                }
            }

            if ($cmlocked) {
                $status = '<span class="mx-status mx-status-locked">' . s($en ? 'Locked' : 'Chưa mở') . '</span>';
            } else if ($isdone) {
                $status = '<span class="mx-status mx-status-done">' . s($en ? 'Completed' : 'Hoàn thành') . '</span>';
            } else if ($completion->is_enabled($cm)) {
                $status = '<span class="mx-status mx-status-todo">' . s($en ? 'Not completed' : 'Chưa hoàn thành') . '</span>';
            } else {
                $status = '';
            }

            $titlehtml = ($cmurl !== '')
                ? '<a href="' . $cmurl . '" class="mx-act-link">' . $name . '</a>'
                : '<span class="mx-act-link mx-act-disabled">' . $name . '</span>';
            $availnote = '';
            if ($cmlocked) {
                $availnote = '<div class="mx-act-note">'
                    . s(theme_agritrain_child_clean_avail_info($cm->availableinfo ?: ($secinfo->availableinfo ?? ''), $en))
                    . '</div>';
            }
            $inlinedesc = '';
            if (!empty($cm->showdescription)) {
                $rawdesc = trim($cm->get_formatted_content(['overflowdiv' => true, 'noclean' => true]));
                if (trim(strip_tags($rawdesc)) !== '') {
                    $inlinedesc = '<div class="activity-description mt-2">' . $rawdesc . '</div>';
                }
            }

            $rows .= '<li class="mx-act-row' . ($cmlocked ? ' mx-act-locked' : '') . '" data-mx-modname="' . s($cm->modname) . '">'
                . '<img class="mx-act-icon" src="' . s($iconurl) . '" alt="" width="24" height="24">'
                . '<div class="mx-act-main">' . $titlehtml
                . '<div class="mx-act-meta">' . s(implode('  |  ', $meta)) . '</div>' . $inlinedesc . $availnote . '</div>'
                . '<div class="mx-act-status">' . $status . '</div>'
                . '</li>';
        }

        if ($rows === '' && $secsummary === '') {
            continue;
        }

        $secname = ((int)$secnum === 0)
            ? ($en ? 'General information' : 'Thông tin chung')
            : get_section_name($course, $secinfo);
        $secpct = $sectotal > 0 ? (int)round($secdone / $sectotal * 100) : 0;
        $secprog = '';
        if ($sectotal > 0) {
            $secprog = '<div class="mx-sec-progress"><span class="mx-sec-progress-text">'
                . s($secdone . ' / ' . $sectotal . ' ' . ($en ? 'completed' : 'hoàn thành')) . '</span>'
                . '<div class="mx-bar"><div class="mx-bar-fill" style="width:' . $secpct . '%;"></div></div></div>';
        }

        $sectionblocks[] = '<section id="mx-sec-' . (int)$secnum . '" class="mx-course-section-block' . ($seclocked ? ' mx-sec-is-locked' : '') . '">'
            . '<header class="mx-sec-header"><h3 class="mx-sec-title">' . $secname . '</h3>' . $secprog . '</header>'
            . ($secsummary !== '' ? '<div class="mx-sec-summary">' . $secsummary . '</div>' : '')
            . ($rows !== '' ? '<ul class="mx-act-list">' . $rows . '</ul>' : '')
            . '</section>';
    }

    if (empty($sectionblocks)) {
        return '';
    }

    $overallpct = $totalall > 0 ? (int)round($doneall / $totalall * 100) : 0;
    $overall = '<section id="mx-learning-path-stepper" class="mx-course-overall" aria-label="'
        . s($en ? 'Course progress' : 'Tiến độ khóa học') . '">'
        . '<div class="mx-overall-row"><span class="mx-overall-label">' . s($en ? 'Course progress' : 'Tiến độ khóa học') . '</span>'
        . '<span class="mx-overall-pct">' . $overallpct . '%</span></div>'
        . '<div class="mx-bar"><div class="mx-bar-fill" style="width:' . $overallpct . '%;"></div></div>'
        . '<div class="mx-overall-sub">' . s($doneall . ' / ' . $totalall . ' ' . ($en ? 'activities completed' : 'hoạt động đã hoàn thành')) . '</div>'
        . '</section>';

    return '<div class="mx-course-tiles-wrapper">' . $overall . implode('', $sectionblocks) . '</div>';
}
