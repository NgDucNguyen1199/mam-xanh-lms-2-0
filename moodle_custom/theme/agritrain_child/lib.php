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
 * Build visual Sequential Learning Path Roadmap + Course Lesson Tiles/Cards HTML for /course/view.php.
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

    // Check if learner already earned QR Certificate for this course
    $hascert = false;
    if ($DB->get_manager()->table_exists('local_mamxanhcert')) {
        $hascert = $DB->record_exists('local_mamxanhcert', [
            'userid'   => $USER->id,
            'courseid' => $course->id,
            'status'   => 'valid',
        ]);
    }

    $sectionblocks = [];
    $roadmapsteps = [];
    $foundcurrent = false;
    $totalcmsall = 0;
    $donecmsall = 0;

    foreach ($sections as $secnum => $secinfo) {
        if (!$secinfo->visible) {
            continue;
        }
        $cmids = $modinfo->sections[$secnum] ?? [];
        if (empty($cmids)) {
            continue;
        }

        $seclocked = (!$secinfo->uservisible && !$secinfo->available);
        $sechasrule = !empty($secinfo->availability);
        $secavailtext = theme_agritrain_child_clean_avail_info($secinfo->availableinfo ?? '', $en);

        $cardshtml = '';
        $sectotal = 0;
        $secdone = 0;

        foreach ($cmids as $cmid) {
            if (!isset($modinfo->cms[$cmid])) {
                continue;
            }
            $cm = $modinfo->cms[$cmid];
            if (!$cm->visible || $cm->modname === 'label') {
                continue;
            }
            if ((int)$secnum === 0 && $cm->modname === 'forum') {
                continue;
            }

            $cmlocked = $seclocked || (!$cm->uservisible && !$cm->available);
            $cmhasrule = $sechasrule || !empty($cm->availability);
            $cmavailtext = theme_agritrain_child_clean_avail_info($cm->availableinfo ?: ($secinfo->availableinfo ?? ''), $en);

            $isdone = false;
            if ($completion->is_enabled($cm)) {
                $sectotal++;
                $totalcmsall++;
                $cdata = $completion->get_data($cm, false, $USER->id);
                $isdone = !empty($cdata->completionstate) && ((int)$cdata->completionstate !== COMPLETION_INCOMPLETE);
                if ($isdone) {
                    $secdone++;
                    $donecmsall++;
                }
            }

            if ($cmlocked) {
                $badgeText = $en ? '🔒 Locked Step' : '🔒 Đang khóa';
                $badgeClass = 'mx-badge-locked';
            } else if ($isdone) {
                $badgeText = $en ? '✅ Completed' : '✅ Đã hoàn thành';
                $badgeClass = 'mx-badge-done';
            } else {
                $badgeText = $en ? '📘 Ready to Learn' : '📘 Chưa học';
                $badgeClass = 'mx-badge-todo';
            }

            $name = format_string($cm->name);
            $namelower = \core_text::strtolower($name);
            $stdpill = '';

            if ($cm->modname === 'resource') {
                $icon = '📄';
                $typetag = $en ? 'Inline PDF Handbook' : 'Tài liệu PDF Xem Trực tiếp';
                $btnlabel = $en ? '📖 Read Inline PDF Now' : '📖 Xem trực tiếp PDF';
                $stdpill = '<span class="mx-std-chip mx-chip-pdf">PDF EMBED</span>';
            } else if ($cm->modname === 'h5pactivity') {
                $icon = '🧩';
                $typetag = $en ? 'Interactive H5P (Graded)' : 'Học liệu Tương tác H5P';
                $btnlabel = $en ? '🧩 Launch H5P Activity' : '🧩 Tương tác H5P ngay';
                $stdpill = '<span class="mx-std-chip mx-chip-h5p">H5P</span>';
            } else if ($cm->modname === 'scorm') {
                $icon = '📦';
                $typetag = $en ? 'SCORM 1.2 Standard Package' : 'Bài giảng Chuẩn SCORM 1.2';
                $btnlabel = $en ? '▶️ Launch SCORM 1.2' : '▶️ Học gói SCORM 1.2';
                $stdpill = '<span class="mx-std-chip mx-chip-scorm">SCORM 1.2</span>';
            } else if ($cm->modname === 'quiz') {
                $icon = '📝';
                $typetag = $en ? 'Final Quiz (Pass >=70%)' : 'Bài Kiểm tra Trắc nghiệm';
                $btnlabel = $en ? '📝 Take Quiz Now' : '📝 Làm bài kiểm tra ngay';
            } else if ($cm->modname === 'fieldpractice') {
                $icon = '🌿';
                $typetag = $en ? 'Garden Practice (QR + Rubric)' : 'Thực hành Vườn (QR + Rubric)';
                $btnlabel = $en ? '📍 Open Garden Practice' : '📍 Điểm danh & Thực hành';
            } else if (strpos($namelower, 'chứng nhận') !== false || strpos($namelower, 'certificate') !== false) {
                $icon = '🎓';
                $typetag = $en ? 'Digital QR Certificate (>=70%)' : 'Chứng nhận Số QR (>=70%)';
                $btnlabel = $en ? '🎓 Open QR Certificate' : '🎓 Nhận Chứng nhận Số QR';
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
                $typetag = $en ? 'HD HTML5 Video & Infographic' : 'Video Thực tế HD & Infographic';
                $btnlabel = $en ? '🎬 Watch Video Lesson' : '🎬 Xem Video & Bài giảng';
                $stdpill = '<span class="mx-std-chip mx-chip-video">HTML5 MP4</span>';
            }

            $cmurl = (!$cmlocked && $cm->url) ? $cm->url->out(false) : '';
            $titlehtml = ($cmurl !== '')
                ? '<a href="' . $cmurl . '">' . $name . '</a>'
                : '<span class="text-muted">🔒 ' . $name . '</span>';

            $availbox = '';
            if ($cmlocked) {
                $availbox = '<div class="mx-avail-lock-note mt-2">🔐 ' . s($cmavailtext) . '</div>';
            } else if ($cmhasrule) {
                $availbox = '<div class="mx-avail-open-note mt-2">🔓 '
                    . s($en ? 'Sequential Prerequisite Met' : 'Đã mở khóa theo lộ trình tuần tự')
                    . '</div>';
            }

            $actionhtml = ($cmurl !== '')
                ? '<a href="' . $cmurl . '" class="btn btn-success w-100 mx-tile-action-btn">' . s($btnlabel) . '</a>'
                : '<button type="button" class="btn btn-secondary w-100 mx-tile-locked-btn" disabled>'
                    . s($en ? '🔒 Complete Previous Step First' : '🔒 Hoàn thành bước trước để mở')
                    . '</button>';

            $cardshtml .= '<div class="col-12 col-md-6 col-xl-4 mb-3">'
                . '<article class="mx-lesson-tile-card' . ($cmlocked ? ' mx-tile-locked' : '') . '">'
                . '<div>'
                . '<div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap:6px;">'
                . '<span class="mx-tile-type-pill">' . $icon . ' ' . s($typetag) . ' ' . $stdpill . '</span>'
                . '<span class="mx-tile-status-badge ' . $badgeClass . '">' . s($badgeText) . '</span>'
                . '</div>'
                . '<h4 class="mx-tile-title">' . $titlehtml . '</h4>'
                . $availbox
                . '</div>'
                . '<div class="mt-3">'
                . $actionhtml
                . '</div>'
                . '</article>'
                . '</div>';
        }

        if ($cardshtml === '') {
            continue;
        }

        $secname = get_section_name($course, $secinfo);
        if ((int)$secnum >= 1) {
            if ($seclocked) {
                $stepstate = 'locked';
                $steplabel = $en ? '🔒 Locked' : '🔒 Chưa mở khóa';
            } else if ($sectotal > 0 && $secdone >= $sectotal) {
                $stepstate = 'done';
                $steplabel = $en ? '✅ Completed' : '✅ Hoàn thành (' . $secdone . '/' . $sectotal . ')';
            } else if (!$foundcurrent) {
                $stepstate = 'current';
                $foundcurrent = true;
                $steplabel = $en ? '🎯 Current Step' : '🎯 Đang học (' . $secdone . '/' . max(1, $sectotal) . ')';
            } else {
                $stepstate = 'open';
                $steplabel = $en ? '🟢 Unlocked' : '🟢 Đã mở (' . $secdone . '/' . max(1, $sectotal) . ')';
            }

            $roadmapsteps[] = [
                'num'   => (int)$secnum,
                'title' => $secname,
                'state' => $stepstate,
                'label' => $steplabel,
                'anchor'=> '#mx-sec-' . (int)$secnum,
            ];
        }

        $secsummary = !empty($secinfo->summary) ? '<div class="mx-sec-summary">' . format_text($secinfo->summary, $secinfo->summaryformat) . '</div>' : '';
        $secbanner = '';
        if ($seclocked) {
            $secbanner = '<div class="mx-sec-lock-banner mb-3">🔒 <strong>'
                . s($en ? 'Restricted Access (Sequential Path): ' : 'Lộ trình Tuần tự (Chưa mở khóa): ')
                . '</strong>' . s($secavailtext) . '</div>';
        } else if ($sechasrule) {
            $secbanner = '<div class="mx-sec-unlock-banner mb-3">✅ <strong>'
                . s($en ? 'Sequential Step Unlocked: ' : 'Bước lộ trình tuần tự đang mở: ')
                . '</strong>' . s($en ? 'Prerequisites verified automatically by Moodle.' : 'Hệ thống xác nhận đủ điều kiện học tập.') . '</div>';
        }

        $sectionblocks[] = '<section id="mx-sec-' . (int)$secnum . '" class="mx-course-section-block' . ($seclocked ? ' mx-sec-is-locked' : '') . ' mb-4">'
            . '<div class="mx-sec-header">'
            . '<div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:8px;">'
            . '<h3 class="mx-sec-title">' . ($seclocked ? '🔒 ' : '🌱 ') . $secname . '</h3>'
            . ($sectotal > 0 ? '<span class="mx-sec-prog-pill">' . $secdone . ' / ' . $sectotal . ' ' . s($en ? 'done' : 'hoàn thành') . '</span>' : '')
            . '</div>'
            . $secsummary
            . $secbanner
            . '</div>'
            . '<div class="row">' . $cardshtml . '</div>'
            . '</section>';
    }

    if (empty($sectionblocks)) {
        return '';
    }

    // Build Visual Stepper / Progress Roadmap Header (Bước 1 -> Bước 2 -> Bước 3 -> Bước 4 -> Chứng nhận)
    $overallpct = $totalcmsall > 0 ? (int)round(($donecmsall / $totalcmsall) * 100) : 0;
    $roadmaphtml = '';
    if (!empty($roadmapsteps)) {
        $certurl = (new moodle_url('/local/mamxanhcert/index.php'))->out(false);
        $roadmapsteps[] = [
            'num'    => count($roadmapsteps) + 1,
            'title'  => $en ? 'QR Digital Certificate (Pass >=70%)' : 'Chứng nhận Số QR (Đạt Quiz >=70%)',
            'state'  => $hascert ? 'done' : 'cert',
            'label'  => $hascert ? ($en ? '🏅 Issued' : '🏅 Đã cấp QR') : ($en ? '🎓 Final Goal' : '🎓 Đích đến'),
            'anchor' => $hascert ? $certurl : '#mx-sec-4',
        ];

        $stepsitems = '';
        $totalsteps = count($roadmapsteps);
        foreach ($roadmapsteps as $idx => $st) {
            $stepnum = $idx + 1;
            $shorttitle = \core_text::strlen($st['title']) > 46
                ? \core_text::substr($st['title'], 0, 44) . '…'
                : $st['title'];
            $arrow = ($idx < $totalsteps - 1) ? '<div class="mx-stepper-arrow" aria-hidden="true">➜</div>' : '';

            $stepsitems .= '<a href="' . s($st['anchor']) . '" class="mx-stepper-node mx-step-' . $st['state'] . '">'
                . '<div class="d-flex justify-content-between align-items-center mb-1" style="gap:6px;">'
                . '<span class="mx-step-num">' . s(($en ? 'Step ' : 'Bước ') . $stepnum) . '</span>'
                . '<span class="mx-step-badge">' . s($st['label']) . '</span>'
                . '</div>'
                . '<div class="mx-step-title">' . s($shorttitle) . '</div>'
                . '</a>'
                . $arrow;
        }

        $roadmaphtml = '<section id="mx-learning-path-stepper" class="mx-roadmap-card mb-4" aria-label="Learning Path Roadmap">'
            . '<div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap:10px;">'
            . '<div>'
            . '<h3 class="mx-roadmap-heading">🗺️ '
            . s($en ? 'Sequential Learning Path Roadmap (Step-by-Step)' : 'Sơ đồ Lộ trình Học tập Tuần tự (Bước 1 → Bước 2 → Bước 3 → Bước 4)')
            . '</h3>'
            . '<p class="mx-roadmap-sub mb-0">'
            . s($en
                ? 'Complete each stage in order: Video & Inline PDF → Interactive H5P & SCORM 1.2 → Garden Practice → Final Quiz (>=70%) → QR Certificate.'
                : 'Hoàn thành lần lượt từng bước để mở khóa chuyên đề kế tiếp và nhận Chứng nhận Số QR (HMAC-SHA256).')
            . '</p>'
            . '</div>'
            . '<div class="mx-roadmap-pct-box">'
            . '<span style="font-size:0.8rem;font-weight:700;">' . s($en ? 'Progress' : 'Tiến độ khóa học') . '</span>'
            . '<strong style="font-size:1.3rem;">' . $overallpct . '%</strong>'
            . '</div>'
            . '</div>'
            . '<div class="mx-roadmap-bar mb-3"><div class="mx-roadmap-bar-fill" style="width:' . max(4, $overallpct) . '%;"></div></div>'
            . '<div class="mx-stepper-track">' . $stepsitems . '</div>'
            . '</section>';
    }

    return '<div class="mx-course-tiles-wrapper">' . $roadmaphtml . implode('', $sectionblocks) . '</div>';
}
