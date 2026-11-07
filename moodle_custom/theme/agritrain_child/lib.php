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
 * Build visual Course Lesson Tiles/Cards HTML for /course/view.php.
 */
function theme_agritrain_child_build_course_tiles_html(stdClass $course): string {
    global $CFG, $USER;

    if ((int)$course->id === SITEID) {
        return '';
    }
    require_once($CFG->libdir . '/completionlib.php');
    $en = strpos(current_language(), 'en') === 0;

    $modinfo = get_fast_modinfo($course, $USER->id);
    $completion = new completion_info($course);
    $sections = $modinfo->get_section_info_all();

    $html = '<div class="mx-course-tiles-wrapper">';
    $hasany = false;

    foreach ($sections as $secnum => $secinfo) {
        if (!$secinfo->uservisible) {
            continue;
        }
        $cmids = $modinfo->sections[$secnum] ?? [];
        if (empty($cmids)) {
            continue;
        }

        $cardshtml = '';
        foreach ($cmids as $cmid) {
            if (!isset($modinfo->cms[$cmid])) {
                continue;
            }
            $cm = $modinfo->cms[$cmid];
            if (!$cm->uservisible || $cm->modname === 'label' || !$cm->url) {
                continue;
            }
            if ((int)$secnum === 0 && $cm->modname === 'forum') {
                continue;
            }

            $isdone = false;
            if ($completion->is_enabled($cm)) {
                $cdata = $completion->get_data($cm, false, $USER->id);
                $isdone = !empty($cdata->completionstate) && ((int)$cdata->completionstate !== COMPLETION_INCOMPLETE);
            }

            $badgeText = $isdone
                ? ($en ? '✅ Completed' : '✅ Đã hoàn thành')
                : ($en ? '📘 Not Started' : '📘 Chưa học');
            $badgeClass = $isdone ? 'mx-badge-done' : 'mx-badge-todo';

            $name = format_string($cm->name);
            $namelower = \core_text::strtolower($name);
            if ($cm->modname === 'quiz') {
                $icon = '📝';
                $typetag = $en ? 'Final Quiz (Pass >=70%)' : 'Bài Kiểm tra Trắc nghiệm';
                $btnlabel = $en ? '📝 Take Quiz Now' : '📝 Làm bài kiểm tra ngay';
            } else if ($cm->modname === 'fieldpractice') {
                $icon = '🌿';
                $typetag = $en ? 'Garden Practice (QR + Rubric)' : 'Thực hành Vườn (QR + Rubric)';
                $btnlabel = $en ? '📍 Open Garden Practice' : '📍 Điểm danh & Thực hành';
            } else if (strpos($namelower, 'slide') !== false) {
                $icon = '📊';
                $typetag = $en ? 'Interactive Slide Deck' : 'Slide Tương tác Trực quan';
                $btnlabel = $en ? '🚀 Start Lesson Now' : '🚀 Vào học ngay';
            } else if (strpos($namelower, 'flashcard') !== false || strpos($namelower, 'thẻ lật') !== false || strpos($namelower, 'ipm') !== false) {
                $icon = '🃏';
                $typetag = $en ? 'IPM Flashcards & Accordion' : 'Thẻ lật Sâu bệnh IPM';
                $btnlabel = $en ? '🚀 Start Lesson Now' : '🚀 Vào học ngay';
            } else {
                $icon = '🎬';
                $typetag = $en ? 'Video & HTML5 Infographic' : 'Video & Infographic Số';
                $btnlabel = $en ? '🚀 Start Lesson Now' : '🚀 Vào học ngay';
            }

            $cardshtml .= '<div class="col-12 col-md-6 col-xl-4 mb-3">'
                . '<article class="mx-lesson-tile-card">'
                . '<div>'
                . '<div class="d-flex justify-content-between align-items-center mb-2" style="gap:8px;">'
                . '<span class="mx-tile-type-pill">' . $icon . ' ' . s($typetag) . '</span>'
                . '<span class="mx-tile-status-badge ' . $badgeClass . '">' . s($badgeText) . '</span>'
                . '</div>'
                . '<h4 class="mx-tile-title"><a href="' . $cm->url->out(false) . '">' . $name . '</a></h4>'
                . '</div>'
                . '<div class="mt-3">'
                . '<a href="' . $cm->url->out(false) . '" class="btn btn-success w-100 mx-tile-action-btn">' . s($btnlabel) . '</a>'
                . '</div>'
                . '</article>'
                . '</div>';
        }

        if ($cardshtml === '') {
            continue;
        }
        $hasany = true;
        $secname = get_section_name($course, $secinfo);
        $secsummary = !empty($secinfo->summary) ? '<div class="mx-sec-summary">' . format_text($secinfo->summary, $secinfo->summaryformat) . '</div>' : '';

        $html .= '<section class="mx-course-section-block mb-4">'
            . '<div class="mx-sec-header">'
            . '<h3 class="mx-sec-title">🌱 ' . $secname . '</h3>'
            . $secsummary
            . '</div>'
            . '<div class="row">' . $cardshtml . '</div>'
            . '</section>';
    }

    $html .= '</div>';
    return $hasany ? $html : '';
}
