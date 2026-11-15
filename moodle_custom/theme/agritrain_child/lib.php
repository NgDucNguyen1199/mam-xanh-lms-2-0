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
    $reqlang = optional_param('lang', '', PARAM_ALPHA);
    $en = ($reqlang === 'en') ? true : (($reqlang === 'vi') ? false : (strpos(current_language(), 'en') === 0));

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
    $transcripturl = (new moodle_url('/local/mamxanhapp/transcript.php', ['courseid' => $course->id]))->out(false);
    $feedbackurl = (new moodle_url('/local/mamxanhapp/feedback_sheet.php', ['courseid' => $course->id]))->out(false);
    $certurl = (new moodle_url('/local/mamxanhcert/index.php', ['courseid' => $course->id]))->out(false);
    $exportlogurl = (new moodle_url('/local/agriops/export_log.php', ['courseid' => $course->id, 'format' => 'html']))->out(false);

    $overall = '<section id="mx-learning-path-stepper" class="mx-course-overall" aria-label="'
        . s($en ? 'Course progress' : 'Tiến độ khóa học') . '">'
        . '<div class="mx-overall-row"><span class="mx-overall-label">' . s($en ? 'Course progress' : 'Tiến độ khóa học') . '</span>'
        . '<span class="mx-overall-pct">' . $overallpct . '%</span></div>'
        . '<div class="mx-bar"><div class="mx-bar-fill" style="width:' . $overallpct . '%;"></div></div>'
        . '<div class="d-flex flex-wrap justify-content-between align-items-center mt-2" style="gap:10px;">'
        . '<div class="mx-overall-sub mb-0">' . s($doneall . ' / ' . $totalall . ' ' . ($en ? 'activities completed' : 'hoạt động đã hoàn thành')) . '</div>'
        . '<div class="d-flex flex-wrap" style="gap:8px;">'
        . '<a href="' . $transcripturl . '" class="btn btn-sm btn-secondary">' . s($en ? 'Academic Transcript (Print)' : 'Bảng điểm Học thuật (In A4)') . '</a>'
        . '<a href="' . $feedbackurl . '" class="btn btn-sm btn-secondary">' . s($en ? 'Rubric & Feedback Sheet' : 'Phiếu Chấm Rubric & Nhận xét') . '</a>'
        . '<a href="' . $exportlogurl . '" class="btn btn-sm btn-secondary">' . s($en ? 'Export VietGAP Report' : 'Xuất Báo cáo Nhật ký VietGAP') . '</a>'
        . '<a href="' . $certurl . '" class="btn btn-sm btn-primary">' . s($en ? 'Certificate Checklist & QR' : 'Điều kiện & Chứng nhận QR') . '</a>'
        . '</div></div>'
        . '</section>';

    return '<div class="mx-course-tiles-wrapper">' . $overall . implode('', $sectionblocks) . '</div>';
}

/**
 * Build the Editorial / Minimalist Academic Public Portal & Agricultural Extension News Frontpage
 * for Site Home ($PAGE->pagetype === 'site-index').
 * Strictly 0% emoji, Swiss Minimalist Off-White (#F8FAFC / #FFFFFF / #E2E8F0 / #1E3A2F).
 */
function theme_agritrain_child_build_frontpage_portal_html(): string {
    global $DB;

    $reqlang = optional_param('lang', '', PARAM_ALPHA);
    $en = ($reqlang === 'en') ? true : (($reqlang === 'vi') ? false : (strpos(current_language(), 'en') === 0));
    $isloggedinuser = (isloggedin() && !isguestuser());
    $loginurl = (new moodle_url('/login/index.php'))->out(false);
    $myurl = (new moodle_url('/my/index.php'))->out(false);
    $verifyurl = (new moodle_url('/local/mamxanhcert/verify.php'))->out(false);

    // 1. Hero Action Buttons (Logged-in vs Guest)
    if ($isloggedinuser) {
        $heroactions = '<a href="' . $myurl . '" class="btn btn-primary mx-portal-btn-primary" id="mx-hero-dashboard-btn">'
            . s($en ? 'Go to Learning Dashboard' : 'Vào Bảng điều khiển Học tập') . '</a>'
            . '<a href="#mx-public-courses" class="btn btn-secondary mx-portal-btn-secondary" id="mx-hero-courses-btn">'
            . s($en ? 'View Training Programs' : 'Xem Chương trình Đào tạo') . '</a>';
    } else {
        $heroactions = '<a href="' . $loginurl . '" class="btn btn-primary mx-portal-btn-primary" id="mx-hero-login-btn">'
            . s($en ? 'System Log in' : 'Đăng nhập Hệ thống') . '</a>'
            . '<a href="#mx-public-courses" class="btn btn-secondary mx-portal-btn-secondary" id="mx-hero-courses-btn">'
            . s($en ? 'View Training Programs' : 'Xem Chương trình Đào tạo') . '</a>';
    }

    // 2. Editorial News & Press Articles Data
    $articles = [
        [
            'id' => 'art1',
            'cat' => $en ? 'Cultivation Engineering' : 'Kỹ thuật Canh tác',
            'date' => '12/11/2026',
            'source' => $en ? 'Source: National Agricultural Extension Center' : 'Trích nguồn: Trung tâm Khuyến nông Quốc gia',
            'img' => '/local/mamxanhapp/media/videos/chamsoc_fertigation.jpg',
            'title' => $en
                ? 'Applying pressure-compensating drip irrigation and automated Fertigation saves 40% of fertilizer in greenhouses'
                : 'Ứng dụng công nghệ tưới nhỏ giọt bù áp và châm phân tự động (Fertigation) giúp tiết kiệm 40% lượng phân bón trong nhà màng',
            'excerpt' => $en
                ? 'Technical analysis of pressure-compensating drip emitters combined with automated EC/pH Venturi dosing across crop phenological stages, maintaining 65–75% substrate moisture and preventing salt accumulation.'
                : 'Phân tích quy trình thiết kế hệ thống tưới nhỏ giọt bù áp kết hợp bộ châm phân tự động theo chỉ số EC/pH từng giai đoạn sinh trưởng, duy trì độ ẩm giá thể 65–75% và ngăn ngừa kết tủa muối dinh dưỡng.',
            'body' => $en
                ? '<p><strong>1. Technical Context:</strong> Conventional surface irrigation in greenhouse cultivation often leads to uneven moisture distribution, leaching of nitrate nitrogen, and localized salinization of coco-peat substrates. Pressure-compensating drip emitters (1.6–2.0 L/h) combined with automated Venturi Fertigation resolve these limitations.</p><p><strong>2. Standard EC &amp; pH Regimes:</strong> During the vegetative stage, nutrient solution electrical conductivity (EC) is maintained at 1.2–1.5 mS/cm with pH 5.6–6.0. During flowering and fruit enlargement, EC is adjusted to 1.8–2.2 mS/cm with increased Potassium (K2O) and Calcium-Boron supplementation, split into 6–8 micro-irrigation cycles per day based on solar radiation sensors.</p><p><strong>3. Field Results:</strong> Trials conducted across 18 agricultural cooperatives in Lam Dong recorded a 40% reduction in soluble fertilizer consumption, 32% water savings, and a 24% increase in Class-A exportable yield.</p>'
                : '<p><strong>1. Đặt vấn đề kỹ thuật:</strong> Phương pháp tưới tràn hoặc tưới phun mưa truyền thống trong nhà màng thường gây thất thoát dinh dưỡng qua tầng thấm sâu, làm biến động độ ẩm giá thể xơ dừa và tăng nguy cơ tích tụ muối cục bộ. Hệ thống tưới nhỏ giọt bù áp (lưu lượng 1,6 – 2,0 lít/giờ) kết hợp bộ châm phân tự động (Fertigation) kiểm soát đồng thời hai thông số cốt lõi là độ dẫn điện (EC) và độ pH dung dịch.</p><p><strong>2. Thông số vận hành chuẩn:</strong> Ở giai đoạn cây con và phát triển thân lá, duy trì ngưỡng EC từ 1,2 – 1,5 mS/cm và pH từ 5,6 – 6,0. Bước sang giai đoạn phân hóa mầm hoa và nuôi quả, nâng EC lên 1,8 – 2,2 mS/cm, tăng tỷ lệ Kali (K2O) và Canxi-Bo, chia nhỏ thành 6 – 8 chu kỳ tưới/ngày dựa trên cảm biến bức xạ mặt trời và độ ẩm giá thể (duy trì 65 – 75%).</p><p><strong>3. Hiệu quả thực tiễn:</strong> Kết quả đối chứng tại 18 mô hình điểm thuộc Trung tâm Khuyến nông Quốc gia ghi nhận mức tiết kiệm 40% lượng phân bón hòa tan, giảm 32% lượng nước tưới và tăng 24% tỷ lệ nông sản thương phẩm loại A.</p>',
        ],
        [
            'id' => 'art2',
            'cat' => $en ? 'Plant Protection' : 'Bảo vệ Thực vật',
            'date' => '10/11/2026',
            'source' => $en ? 'Source: Plant Protection Department - MARD' : 'Trích nguồn: Cục Bảo vệ Thực vật - Bộ NN&PTNT',
            'img' => '/local/mamxanhapp/media/videos/visinh_compost.jpg',
            'title' => $en
                ? 'Integrated Pest Management (IPM) protocol and Trichoderma biological agents against wilt disease in fruiting vegetables and strawberries'
                : 'Phác đồ Quản lý dịch hại tổng hợp (IPM) và sử dụng chế phẩm sinh học Trichoderma phòng bệnh héo rũ trên rau ăn quả và dâu tây',
            'excerpt' => $en
                ? 'Guidelines for early root-zone pathogen diagnosis (Fusarium, Phytophthora, Pythium) and mass multiplication of antagonistic Trichoderma spp. combined with biological traps to ensure strict pre-harvest intervals.'
                : 'Hướng dẫn nhận diện sớm nấm bệnh vùng rễ (Fusarium, Phytophthora, Pythium), quy trình nhân sinh khối nấm đối kháng Trichoderma spp. kết hợp bẫy dính sinh học nhằm bảo đảm thời gian cách ly an toàn tuyệt đối.',
            'body' => $en
                ? '<p><strong>1. Epidemiological Surveillance:</strong> Root rot and vascular wilt caused by <em>Fusarium oxysporum</em> and <em>Phytophthora fragariae</em> thrive when greenhouse relative humidity exceeds 85% and substrate drainage is poor. Weekly scouting across 5 diagonal points per greenhouse is mandatory under IPM protocols.</p><p><strong>2. Biological Control Protocol:</strong> Incorporate <em>Trichoderma harzianum</em> and <em>Bacillus subtilis</em> (minimum density 10^8 CFU/g) into organic compost and coco-peat 15 days prior to transplanting, followed by periodic root drenching every 20 days. Deploy yellow and blue chromotropic sticky traps (40 traps/1,000 m2) and predatory mites (<em>Amblyseius swirskii</em>) to control thrips and two-spotted spider mites.</p><p><strong>3. VietGAP Safety Compliance:</strong> Prioritizing biological agents eliminates chemical pesticide residue violations and strictly upholds the Pre-Harvest Interval (PHI) required for domestic supermarket chains and export markets.</p>'
                : '<p><strong>1. Giám sát dịch tễ học vùng rễ:</strong> Bệnh héo rũ mạch dẫn và thối gốc rễ do nấm <em>Fusarium oxysporum</em>, <em>Phytophthora</em> và <em>Pythium</em> phát sinh mạnh khi độ ẩm giá thể vượt ngưỡng 80% và thông gió nhà màng kém. Quy trình IPM yêu cầu điều tra định kỳ 7 ngày/lần theo 5 điểm chéo góc trong khu vực canh tác.</p><p><strong>2. Phác đồ sử dụng chế phẩm sinh học Trichoderma:</strong> Ủ hoạt hóa nấm đối kháng <em>Trichoderma spp.</em> cùng vi khuẩn <em>Bacillus subtilis</em> (mật độ tối thiểu 10^8 CFU/g) với phân hữu cơ vi sinh và giá thể xơ dừa đã xử lý tanin trước khi trồng 15 ngày; định kỳ tưới nhắc lại vùng cổ rễ 20 ngày/lần. Kết hợp treo bẫy dính sinh học màu vàng/xanh (40 bẫy/1.000 m2) và thả thiên địch bắt mồi để kiểm soát bọ trĩ, nhện đỏ.</p><p><strong>3. Tuân thủ thời gian cách ly (PHI):</strong> Việc thay thế thuốc hóa học bằng chế phẩm sinh học giúp triệt tiêu nguy cơ tồn dư hoạt chất bảo vệ thực vật, đáp ứng nghiêm ngặt tiêu chuẩn VietGAP TCVN 11892-1:2017.</p>',
        ],
        [
            'id' => 'art3',
            'cat' => $en ? 'Standards & Export' : 'Tiêu chuẩn & Xuất khẩu',
            'date' => '08/11/2026',
            'source' => $en ? 'Source: Lam Dong Sub-Department of Crop Production & Plant Protection' : 'Trích nguồn: Chi cục Trồng trọt & BVTV Lâm Đồng',
            'img' => '/local/mamxanhapp/media/videos/chebien_postharvest.jpg',
            'title' => $en
                ? 'Standardized electronic field diary recording and Planting Area Code issuance for agricultural traceability'
                : 'Hướng dẫn quy trình ghi chép nhật ký đồng ruộng điện tử và cấp mã số vùng trồng phục vụ truy xuất nguồn gốc nông sản',
            'excerpt' => $en
                ? 'Standardizing digital farm logs under TCVN 11892-1:2017, monitoring Maximum Residue Limits (MRL), cold-chain post-harvest handling, and QR batch assignment for phytosanitary export inspection.'
                : 'Chuẩn hóa biểu mẫu nhật ký canh tác số hóa theo TCVN 11892-1:2017, kiểm soát dư lượng tối đa cho phép (MRL), quy trình sơ chế bảo quản lạnh và gán mã định danh lô nông sản phục vụ xuất khẩu.',
            'body' => $en
                ? '<p><strong>1. Mandatory Requirements for Planting Area Codes (PUC):</strong> Exporting fresh produce requires a registered Planting Area Code with minimum acreage compliance, uniform cultivation protocols, and 100% traceable farm input records across all participating households.</p><p><strong>2. Electronic Field Diary Workflow:</strong> Every agronomic operation—seed/seedling batch reception, fertilizer application, IPM treatment, pre-harvest water/product sampling, and harvest batch packaging—must be logged within 24 hours on the Mầm Xanh LMS VietGAP Digital Logbook with GPS coordinates and timestamp verification.</p><p><strong>3. Cryptographic QR Traceability:</strong> Upon passing final assessment and MRL inspection, each harvest batch and trainee certificate is signed with an HMAC-SHA256 digital signature, allowing instant public verification via QR code.</p>'
                : '<p><strong>1. Yêu cầu bắt buộc về Mã số vùng trồng:</strong> Để nông sản tham gia chuỗi cung ứng hiện đại và xuất khẩu chính ngạch, vùng sản xuất phải áp dụng thống nhất quy trình canh tác, kiểm soát sinh vật gây hại kiểm dịch và lưu trữ đầy đủ hồ sơ truy xuất nguồn gốc theo thời gian thực.</p><p><strong>2. Quy trình ghi chép Nhật ký đồng ruộng điện tử:</strong> Toàn bộ tác nghiệp hàng ngày gồm: xuất xứ lô giống, nhật ký pha tưới phân bón (tên thương phẩm, liều lượng, ngày tưới), nhật ký phun phòng trừ sinh học, kết quả lấy mẫu nước/đất và sản lượng thu hoạch phải được cập nhật trực tiếp lên phân hệ Nhật ký VietGAP của hệ thống Mầm Xanh LMS 2.0.</p><p><strong>3. Định danh lô hàng &amp; Chữ ký số QR:</strong> Mỗi lô nông sản sau thu hoạch và sơ chế lạnh được cấp mã truy xuất QR bảo mật bằng thuật toán HMAC-SHA256, cho phép đơn vị thu mua và cơ quan kiểm định tra cứu công khai ngay trên cổng thông tin.</p>',
        ],
        [
            'id' => 'art4',
            'cat' => $en ? 'Academic Notice' : 'Thông báo Đào tạo',
            'date' => '05/11/2026',
            'source' => $en ? 'Source: Academic Training Board - Mam Xanh LMS 2.0' : 'Trích nguồn: Hội đồng Đào tạo Khuyến nông Số - Mầm Xanh LMS',
            'img' => '/local/mamxanhapp/media/videos/nhamang_hydroponics.jpg',
            'title' => $en
                ? 'Enrollment plan for November 2026 High-Tech Strawberry & Chrysanthemum Greenhouse Cultivation course and online registration guide'
                : 'Kế hoạch mở lớp tập huấn Kỹ thuật Canh tác Dâu tây & Hoa Cúc công nghệ cao đợt tháng 11/2026 và hướng dẫn đăng ký học trực tuyến',
            'excerpt' => $en
                ? 'Official enrollment notice for course code DAUTAY-CNC-01 comprising 04 online academic modules, interactive H5P diagnostics, SCORM simulation packages, and on-site greenhouse field assessment in Da Lat.'
                : 'Thông báo chiêu sinh khóa học thực hành chuyên sâu mã học phần DAUTAY-CNC-01 gồm 04 chuyên đề lý thuyết trực tuyến, bài tập chẩn đoán H5P, gói mô phỏng SCORM và đánh giá thực địa tại nhà màng Đà Lạt.',
            'body' => $en
                ? '<p><strong>1. Course Identification:</strong> Course Title: <em>High-Tech Strawberry &amp; Chrysanthemum Cultivation in Da Lat Greenhouses</em> (Course Code: <code>DAUTAY-CNC-01</code>). Designed for farm households, cooperative technicians, and agricultural extension officers.</p><p><strong>2. Curriculum Structure (04 Modules + Final Evaluation):</strong></p><ul><li><strong>Module 1:</strong> Disease-free tissue-cultured seedling standards &amp; greenhouse microclimate regulation (18–24°C, RH 65–75%).</li><li><strong>Module 2:</strong> Pressure-compensating drip irrigation &amp; substrate moisture management.</li><li><strong>Module 3:</strong> Automated EC/pH Fertigation regimes &amp; supplemental photoperiod lighting for Chrysanthemum.</li><li><strong>Module 4:</strong> Integrated Pest Management (IPM), Botrytis/Powdery Mildew control &amp; cold-chain harvesting.</li></ul><p><strong>3. Completion &amp; Certification:</strong> Learners who complete all 4 modules, pass the 10-question final exam (minimum 8.0/10), and submit the field harvest assignment receive a verifiable HMAC-SHA256 Digital QR Certificate.</p>'
                : '<p><strong>1. Thông tin học phần:</strong> Khóa tập huấn chuyên đề <em>"Kỹ thuật Canh tác Dâu tây &amp; Hoa Cúc Công nghệ cao trong Nhà màng Đà Lạt"</em> (Mã học phần: <code>DAUTAY-CNC-01</code>) được tổ chức dành cho nông hộ, tổ hợp tác và cán bộ kỹ thuật hợp tác xã nông nghiệp.</p><p><strong>2. Cấu trúc chương trình chuẩn hóa (04 Chuyên đề &amp; Kiểm tra cuối khóa):</strong></p><ul><li><strong>Chuyên đề 1:</strong> Tiêu chuẩn cây giống cấy mô sạch bệnh, xử lý giá thể xơ dừa và điều tiết vi khí hậu nhà màng (18 – 24°C, RH 65 – 75%).</li><li><strong>Chuyên đề 2:</strong> Thiết kế và vận hành hệ thống tưới nhỏ giọt bù áp &amp; kiểm soát độ ẩm giá thể.</li><li><strong>Chuyên đề 3:</strong> Châm phân tự động theo chỉ số EC/pH và điều khiển chiếu sáng bổ sung (Photoperiod) cho hoa cúc.</li><li><strong>Chuyên đề 4:</strong> Phòng trừ tổng hợp IPM (nấm mốc xám Botrytis, phấn trắng, bọ trĩ, nhện đỏ) và thu hoạch bảo quản lạnh.</li></ul><p><strong>3. Điều kiện cấp chứng nhận:</strong> Học viên hoàn thành tuần tự 04 chuyên đề, đạt điểm bài kiểm tra trắc nghiệm cuối khóa từ 8,0/10 trở lên và nộp bài thu hoạch thực hành sẽ được cấp Chứng nhận số QR xác thực công khai.</p>',
        ],
    ];

    $featuredbody = $en
        ? '<p><strong>1. Program Overview:</strong> Under the 2026 Digital Agricultural Extension Initiative in Lam Dong – Central Highlands, standardized polyhouse/greenhouse models integrated with IoT microclimate stations and VietGAP TCVN 11892-1:2017 protocols have been deployed across key production zones in Da Lat, Don Duong, Lac Duong, and Duc Trong.</p><p><strong>2. Core Technological Pillars:</strong></p><ul><li><strong>Automated Microclimate Control:</strong> Real-time monitoring of ambient temperature, relative humidity (RH), photosynthetically active radiation (PAR), and substrate moisture to trigger roof vents, shading screens, and misting systems automatically.</li><li><strong>Precision Fertigation:</strong> Closed-loop and semi-closed drip fertigation calibrated to crop stage EC (1.2–2.2 mS/cm) and pH (5.5–6.2), minimizing nutrient runoff.</li><li><strong>Digital VietGAP Compliance:</strong> 100% electronic logging of cultivation tasks, biological IPM interventions, and HMAC-SHA256 QR batch certification.</li></ul><p><strong>3. Socio-Economic Impact:</strong> Participating cooperatives achieved a 28% yield increase, 35–40% reduction in chemical fertilizer and water usage, and full compliance with domestic high-end retail and export traceability standards.</p>'
        : '<p><strong>1. Tổng quan chương trình:</strong> Thực hiện Đề án Chuyển đổi số trong Khuyến nông và Chuẩn hóa vùng nguyên liệu Nông nghiệp Công nghệ cao giai đoạn 2026 tại Lâm Đồng – Tây Nguyên, hệ thống Mầm Xanh LMS 2.0 đã phối hợp cùng các Hợp tác xã triển khai đồng bộ mô hình nhà màng thông minh gắn với quy chuẩn Quốc gia VietGAP (TCVN 11892-1:2017).</p><p><strong>2. Ba trụ cột kỹ thuật cốt lõi:</strong></p><ul><li><strong>Điều tiết vi khí hậu tự động:</strong> Ứng dụng trạm cảm biến IoT giám sát liên tục nhiệt độ, độ ẩm tương đối (RH), bức xạ quang hợp (PAR) để vận hành cửa thông gió mái, lưới cắt nắng và hệ thống phun sương làm mát chính xác.</li><li><strong>Châm phân &amp; Tưới nhỏ giọt chính xác (Fertigation):</strong> Định lượng dinh dưỡng theo ngưỡng EC (1,2 – 2,2 mS/cm) và pH (5,5 – 6,2) phù hợp từng thời kỳ sinh trưởng của rau ăn quả, dâu tây và hoa thương phẩm.</li><li><strong>Số hóa hồ sơ VietGAP &amp; Chứng nhận QR:</strong> Liên thông dữ liệu từ đào tạo trực tuyến, thực hành hiện trường, ghi nhật ký đồng ruộng điện tử đến cấp chứng nhận số HMAC-SHA256.</li></ul><p><strong>3. Kết quả ghi nhận:</strong> Năng suất thương phẩm bình quân tăng 28%, tiết kiệm 35 – 40% lượng phân bón và nước tưới, 100% lô nông sản của học viên đạt chuẩn truy xuất nguồn gốc chuỗi siêu thị và xuất khẩu.</p>';

    // Build 3-column press grid (Articles 1-3) + Dispatch card (Article 4)
    $newsgridcards = '';
    for ($i = 0; $i < 3; $i++) {
        $a = $articles[$i];
        $newsgridcards .= '<article class="mx-news-card" data-article-id="' . s($a['id']) . '">'
            . '<div class="mx-news-card-media">'
            . '<img src="' . s($a['img']) . '" alt="' . s($a['title']) . '" loading="lazy">'
            . '<span class="mx-news-cat-badge">' . s($a['cat']) . '</span>'
            . '</div>'
            . '<div class="mx-news-card-body">'
            . '<div class="mx-news-meta"><span>' . s($a['date']) . '</span><span class="mx-meta-dot">•</span><span>' . s($a['source']) . '</span></div>'
            . '<h3 class="mx-news-title"><button type="button" class="mx-news-title-btn js-open-mx-article" data-article-id="' . s($a['id']) . '">' . s($a['title']) . '</button></h3>'
            . '<p class="mx-news-excerpt">' . s($a['excerpt']) . '</p>'
            . '<div class="mx-news-footer">'
            . '<button type="button" class="mx-news-read-link js-open-mx-article" data-article-id="' . s($a['id']) . '">'
            . s($en ? 'Read article →' : 'Đọc bài viết →') . '</button>'
            . '</div>'
            . '</div>'
            . '</article>';
    }

    // Article 4: Official Training Notice Dispatch Card
    $a4 = $articles[3];
    $dispatchcard = '<article class="mx-news-dispatch" data-article-id="' . s($a4['id']) . '">'
        . '<div class="mx-news-dispatch-left">'
        . '<div class="mx-news-meta"><span class="mx-news-cat-inline">' . s($a4['cat']) . '</span><span class="mx-meta-dot">•</span><span>' . s($a4['date']) . '</span><span class="mx-meta-dot">•</span><span>' . s($a4['source']) . '</span></div>'
        . '<h3 class="mx-news-dispatch-title"><button type="button" class="mx-news-title-btn js-open-mx-article" data-article-id="' . s($a4['id']) . '">' . s($a4['title']) . '</button></h3>'
        . '<p class="mx-news-dispatch-excerpt">' . s($a4['excerpt']) . '</p>'
        . '</div>'
        . '<div class="mx-news-dispatch-right">'
        . '<button type="button" class="btn btn-secondary js-open-mx-article" data-article-id="' . s($a4['id']) . '">'
        . s($en ? 'Read article →' : 'Đọc bài viết →') . '</button>'
        . '</div>'
        . '</article>';

    // 3. Featured Courses Catalog (#mx-public-courses)
    $preferredcodes = ['DAUTAY-CNC-01', 'CHAMSOC01', 'IOTNHAMANG01', 'THUYCANH01', 'SAURIENG01', 'PHANVISINH01'];
    $dbcourses = [];
    if ($DB->get_manager()->table_exists('course')) {
        list($insql, $inparams) = $DB->get_in_or_equal($preferredcodes, SQL_PARAMS_NAMED);
        $records = $DB->get_records_select('course', "shortname $insql AND visible = 1", $inparams, 'id ASC', 'id,shortname,fullname');
        $bycode = [];
        foreach ($records as $r) {
            $bycode[$r->shortname] = $r;
        }
        foreach ($preferredcodes as $code) {
            if (isset($bycode[$code])) {
                $dbcourses[] = $bycode[$code];
            }
        }
    }

    $coursemeta = [
        'DAUTAY-CNC-01' => [
            'modules' => $en ? '04 Modules • 12 Learning Activities' : '04 Chuyên đề • 12 Hoạt động học tập',
            'standard' => $en ? 'MARD & VietGAP TCVN 11892-1:2017' : 'Tài liệu chuẩn Bộ NN&PTNT & TCVN 11892-1:2017',
            'desc' => $en
                ? 'Greenhouse microclimate control, drip irrigation, automated EC/pH fertigation, and biological IPM for strawberry and chrysanthemum.'
                : 'Điều tiết vi khí hậu nhà màng, tưới nhỏ giọt bù áp, châm phân EC/pH tự động và phác đồ IPM sinh học trên dâu tây & hoa cúc Đà Lạt.',
        ],
        'CHAMSOC01' => [
            'modules' => $en ? '04 Modules • 16 Learning Activities' : '04 Chuyên đề • 16 Hoạt động học tập',
            'standard' => $en ? 'MARD & VietGAP TCVN 11892-1:2017' : 'Tài liệu chuẩn Bộ NN&PTNT & TCVN 11892-1:2017',
            'desc' => $en
                ? 'Standardized nutrient solution formulation, Venturi dosing calibration, and physiological disorder diagnosis in high-tech crops.'
                : 'Kỹ thuật pha chế dung dịch dinh dưỡng chuẩn, hiệu chuẩn bộ châm phân tự động và chẩn đoán thiếu hụt vi lượng trong nhà màng.',
        ],
        'IOTNHAMANG01' => [
            'modules' => $en ? '04 Modules • 14 Learning Activities' : '04 Chuyên đề • 14 Hoạt động học tập',
            'standard' => $en ? 'MARD & Smart Greenhouse Standard' : 'Tài liệu chuẩn Bộ NN&PTNT & Khuyến nông Số',
            'desc' => $en
                ? 'Operation of temperature, humidity (RH), PAR solar sensors, automated roof vents, shading screens, and climate control alerts.'
                : 'Vận hành trạm cảm biến nhiệt độ, độ ẩm RH, bức xạ PAR, điều khiển tự động cửa thông gió mái, lưới cắt nắng và cảnh báo sớm.',
        ],
        'THUYCANH01' => [
            'modules' => $en ? '04 Modules • 14 Learning Activities' : '04 Chuyên đề • 14 Hoạt động học tập',
            'standard' => $en ? 'MARD & VietGAP TCVN 11892-1:2017' : 'Tài liệu chuẩn Bộ NN&PTNT & TCVN 11892-1:2017',
            'desc' => $en
                ? 'Recirculating Nutrient Film Technique (NFT) hydroponics, dissolved oxygen management, and buffered coco-peat substrate cultivation.'
                : 'Quy trình vận hành hệ thống thủy canh hồi lưu NFT, kiểm soát oxy hòa tan (DO) và xử lý giá thể xơ dừa trồng dưa lưới, cà chua.',
        ],
        'SAURIENG01' => [
            'modules' => $en ? '04 Modules • 14 Learning Activities' : '04 Chuyên đề • 14 Hoạt động học tập',
            'standard' => $en ? 'MARD & Export Phytosanitary Protocol' : 'Tài liệu chuẩn Bộ NN&PTNT & Nghị định thư Xuất khẩu',
            'desc' => $en
                ? 'Flowering induction, fruit retention, cadmium/residue control, and Planting Area Code compliance for export-grade durian.'
                : 'Quy trình chăm sóc cơi đọt, xử lý ra hoa đồng loạt, kiểm soát dư lượng và quản lý mã số vùng trồng sầu riêng xuất khẩu chính ngạch.',
        ],
        'PHANVISINH01' => [
            'modules' => $en ? '04 Modules • 14 Learning Activities' : '04 Chuyên đề • 14 Hoạt động học tập',
            'standard' => $en ? 'MARD & Circular Agriculture Standard' : 'Tài liệu chuẩn Bộ NN&PTNT & Nông nghiệp Tuần hoàn',
            'desc' => $en
                ? 'Composting agricultural by-products, multiplying antagonistic Trichoderma spp., and rehabilitating degraded greenhouse soils.'
                : 'Kỹ thuật ủ phân hữu cơ vi sinh từ phụ phẩm nông nghiệp, nhân sinh khối nấm đối kháng Trichoderma và phục hồi hệ vi sinh vật đất.',
        ],
    ];

    $coursecardshtml = '';
    foreach ($dbcourses as $c) {
        $code = $c->shortname;
        $meta = $coursemeta[$code] ?? [
            'modules' => $en ? '04 Modules • Comprehensive Assessment' : '04 Chuyên đề • Kiểm tra & Đánh giá',
            'standard' => $en ? 'MARD & VietGAP TCVN 11892-1:2017' : 'Tài liệu chuẩn Bộ NN&PTNT & TCVN 11892-1:2017',
            'desc' => $en ? 'Standardized technical training program for agricultural cooperatives and farm households.' : 'Chương trình tập huấn kỹ thuật canh tác chuẩn hóa dành cho nông hộ và hợp tác xã.',
        ];
        $courseurl = $isloggedinuser
            ? (new moodle_url('/course/view.php', ['id' => $c->id]))->out(false)
            : $loginurl;
        $btnlabel = $isloggedinuser
            ? ($en ? 'View details / Enter course →' : 'Xem chi tiết / Vào lớp học →')
            : ($en ? 'View details / Log in to study' : 'Xem chi tiết / Đăng nhập để học');

        $coursecardshtml .= '<article class="mx-pub-course-card">'
            . '<div class="mx-pub-course-top">'
            . '<span class="mx-pub-course-code">' . s($code) . '</span>'
            . '<span class="mx-pub-course-status">' . s($en ? 'Open for Training' : 'Đang mở lớp') . '</span>'
            . '</div>'
            . '<h3 class="mx-pub-course-title"><a href="' . $courseurl . '">' . format_string($c->fullname) . '</a></h3>'
            . '<p class="mx-pub-course-desc">' . s($meta['desc']) . '</p>'
            . '<ul class="mx-pub-course-specs">'
            . '<li><strong>' . s($en ? 'Structure:' : 'Thời lượng:') . '</strong> ' . s($meta['modules']) . '</li>'
            . '<li><strong>' . s($en ? 'Standard:' : 'Chuẩn biên soạn:') . '</strong> ' . s($meta['standard']) . '</li>'
            . '</ul>'
            . '<div class="mx-pub-course-action">'
            . '<a href="' . $courseurl . '" class="btn btn-secondary w-100 mx-pub-course-btn">' . s($btnlabel) . '</a>'
            . '</div>'
            . '</article>';
    }

    // 4. Hidden Article Payload JSON for Interactive Modal Reader
    $modalpayload = [
        'featured' => [
            'cat' => $en ? 'FEATURED REPORT' : 'CHUYÊN ĐỀ TIÊU ĐIỂM',
            'date' => '15/11/2026',
            'source' => $en ? 'Source: National Agricultural Extension & Lam Dong Department of Agriculture' : 'Trích nguồn: Trung tâm Khuyến nông & Sở NN&PTNT Lâm Đồng',
            'title' => $en
                ? 'Deploying smart greenhouse models and standardizing VietGAP TCVN 11892-1:2017 protocols in Lam Dong - Central Highlands'
                : 'Triển khai mô hình nhà màng thông minh và chuẩn hóa quy trình VietGAP TCVN 11892-1:2017 tại vùng chuyên canh Lâm Đồng - Tây Nguyên',
            'body' => $featuredbody,
        ],
    ];
    foreach ($articles as $a) {
        $modalpayload[$a['id']] = [
            'cat' => $a['cat'],
            'date' => $a['date'],
            'source' => $a['source'],
            'title' => $a['title'],
            'body' => $a['body'],
        ];
    }
    $jsonarticles = json_encode($modalpayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    // 5. Assemble Full Portal HTML
    $html = '<div class="mx-frontpage-portal" id="mx-frontpage-portal">'

        // SECTION 1: EDITORIAL HERO HEADER (2 COLUMNS)
        . '<section class="mx-portal-hero" aria-label="' . s($en ? 'Editorial Hero Header' : 'Tiêu điểm Cổng thông tin Đào tạo') . '">'
        . '<div class="mx-portal-hero-left">'
        . '<div class="mx-portal-kicker">' . s($en ? 'DIGITAL EXTENSION & ACADEMIC TRAINING PORTAL' : 'CỔNG THÔNG TIN ĐÀO TẠO & KHUYẾN NÔNG SỐ') . '</div>'
        . '<h1 class="mx-portal-hero-title">' . s($en ? 'High-Tech Agricultural Cultivation Training System' : 'Hệ thống Tập huấn Kỹ thuật Canh tác Nông nghiệp Công nghệ cao') . '</h1>'
        . '<p class="mx-portal-hero-lead">'
        . s($en
            ? 'Standardizing VietGAP (TCVN 11892-1:2017) cultivation workflows, automated pressure-compensating drip Fertigation, smart greenhouse microclimate management, and Integrated Pest Management (IPM) for farm households and agricultural cooperatives.'
            : 'Chương trình chuẩn hóa quy trình canh tác theo tiêu chuẩn Quốc gia VietGAP (TCVN 11892-1:2017), tưới nhỏ giọt bù áp & châm phân tự động Fertigation, quản lý vi khí hậu nhà màng và phòng trừ dịch hại tổng hợp (IPM) dành cho nông hộ & hợp tác xã.')
        . '</p>'
        . '<div class="mx-portal-hero-actions">' . $heroactions . '</div>'
        . '<div class="mx-portal-hero-stats">'
        . '<div class="mx-hero-stat-item"><span class="mx-stat-val">TCVN 11892-1:2017</span><span class="mx-stat-lbl">' . s($en ? 'National VietGAP Standard' : 'Quy chuẩn Kỹ thuật VietGAP Quốc gia') . '</span></div>'
        . '<div class="mx-hero-stat-item"><span class="mx-stat-val">06+ ' . s($en ? 'Programs' : 'Chuyên ngành') . '</span><span class="mx-stat-lbl">' . s($en ? 'Standardized MARD Curricula' : 'Giáo trình Chuẩn Bộ NN&PTNT') . '</span></div>'
        . '<div class="mx-hero-stat-item"><span class="mx-stat-val">HMAC-SHA256</span><span class="mx-stat-lbl">' . s($en ? 'Digital QR Certification' : 'Chứng nhận Số & Truy xuất QR') . '</span></div>'
        . '</div>'
        . '</div>'

        . '<article class="mx-portal-hero-right">'
        . '<div class="mx-featured-media">'
        . '<img src="/local/mamxanhapp/media/videos/nhamang_clean.jpg" alt="' . s($en ? 'Smart Greenhouse Lam Dong' : 'Mô hình nhà màng thông minh Lâm Đồng') . '">'
        . '<span class="mx-featured-badge">' . s($en ? 'FEATURED REPORT • NOV 2026' : 'TIN TIÊU ĐIỂM • THÁNG 11/2026') . '</span>'
        . '</div>'
        . '<div class="mx-featured-content">'
        . '<div class="mx-featured-meta">' . s($en ? 'Published: November 2026 • Lam Dong - Central Highlands' : 'Đăng tải: Tháng 11/2026 • Chuyên san Khuyến nông Lâm Đồng - Tây Nguyên') . '</div>'
        . '<h2 class="mx-featured-title"><button type="button" class="mx-news-title-btn js-open-mx-article" data-article-id="featured">'
        . s($en
            ? 'Deploying smart greenhouse models and standardizing VietGAP TCVN 11892-1:2017 protocols in Lam Dong - Central Highlands'
            : 'Triển khai mô hình nhà màng thông minh và chuẩn hóa quy trình VietGAP TCVN 11892-1:2017 tại vùng chuyên canh Lâm Đồng - Tây Nguyên')
        . '</button></h2>'
        . '<p class="mx-featured-summary">'
        . s($en
            ? 'Comprehensive assessment of IoT microclimate sensor integration, automated EC/pH drip fertigation, and electronic field logging across Da Lat and Don Duong cooperatives, reducing input costs by 35% and achieving 100% export traceability.'
            : 'Báo cáo đánh giá hiệu quả ứng dụng cảm biến vi khí hậu IoT kết hợp tưới nhỏ giọt châm phân tự động và nhật ký đồng ruộng điện tử tại vùng chuyên canh Đà Lạt – Đơn Dương, giúp giảm 35% chi phí vật tư và đáp ứng 100% hồ sơ truy xuất nguồn gốc.')
        . '</p>'
        . '<div class="mx-featured-footer">'
        . '<button type="button" class="mx-news-read-link js-open-mx-article" data-article-id="featured">'
        . s($en ? 'Read featured report →' : 'Đọc bài viết tiêu điểm →') . '</button>'
        . '</div>'
        . '</div>'
        . '</article>'
        . '</section>'

        // SECTION 2: NEWS & PRESS GRID (3 COLUMNS + DISPATCH)
        . '<section id="mx-public-news" class="mx-portal-section">'
        . '<div class="mx-section-header">'
        . '<div>'
        . '<div class="mx-section-eyebrow">' . s($en ? 'AGRICULTURAL SCIENCE & EXTENSION PRESS' : 'CHUYÊN SAN KHOA HỌC & KHUYẾN NÔNG') . '</div>'
        . '<h2 class="mx-section-title">' . s($en ? 'Agricultural Extension Bulletin & Technical Press' : 'Bản tin Khuyến nông & Phổ biến Kỹ thuật') . '</h2>'
        . '</div>'
        . '<div class="mx-section-sub">' . s($en ? 'Official technical guidelines from MARD, NAEC & Provincial Sub-Departments' : 'Tổng hợp hướng dẫn kỹ thuật từ Bộ NN&PTNT, Trung tâm Khuyến nông Quốc gia & Chi cục Trồng trọt') . '</div>'
        . '</div>'
        . '<div class="mx-news-grid-3col">' . $newsgridcards . '</div>'
        . $dispatchcard
        . '</section>'

        // SECTION 3: OPEN TRAINING PROGRAMS & COURSES (#mx-public-courses)
        . '<section id="mx-public-courses" class="mx-portal-section">'
        . '<div class="mx-section-header">'
        . '<div>'
        . '<div class="mx-section-eyebrow">' . s($en ? 'ACADEMIC CURRICULUM CATALOG' : 'DANH MỤC CHƯƠNG TRÌNH ĐÀO TẠO CHUẨN HÓA') . '</div>'
        . '<h2 class="mx-section-title">' . s($en ? 'Open Training Programs & Technical Courses' : 'Chương trình & Khóa học Tập huấn Đang mở') . '</h2>'
        . '</div>'
        . '<div class="mx-section-sub">'
        . ($isloggedinuser
            ? '<a href="' . $myurl . '" class="mx-section-link">' . s($en ? 'Open My Learning Dashboard →' : 'Mở Bảng điều khiển Học tập của tôi →') . '</a>'
            : '<a href="' . $loginurl . '" class="mx-section-link">' . s($en ? 'Log in with Learner / Technician account →' : 'Đăng nhập tài khoản Học viên / Cán bộ kỹ thuật →') . '</a>')
        . '</div>'
        . '</div>'
        . '<div class="mx-courses-catalog-grid">' . $coursecardshtml . '</div>'
        . '</section>'

        // SECTION 4: ACADEMIC SCHEDULE & PUBLIC QR CERTIFICATE LOOKUP (#mx-public-cert-lookup)
        . '<section id="mx-public-cert-lookup" class="mx-portal-section mx-portal-bottom-grid">'
        . '<div class="mx-portal-panel">'
        . '<div class="mx-section-eyebrow">' . s($en ? 'ACADEMIC AFFAIRS OFFICE • NOV–DEC 2026' : 'THÔNG BÁO HỌC VỤ • THÁNG 11–12/2026') . '</div>'
        . '<h2 class="mx-panel-title">' . s($en ? 'Upcoming Training Cohort Schedule' : 'Lịch Khai giảng Các Đợt Tập huấn Tháng 11–12/2026') . '</h2>'
        . '<div class="table-responsive mt-3">'
        . '<table class="table mx-schedule-table mb-0">'
        . '<thead><tr>'
        . '<th>' . s($en ? 'Cohort / Date' : 'Đợt / Thời gian') . '</th>'
        . '<th>' . s($en ? 'Program & Target Unit' : 'Học phần & Đơn vị Phối hợp') . '</th>'
        . '<th>' . s($en ? 'Status' : 'Trạng thái') . '</th>'
        . '</tr></thead>'
        . '<tbody>'
        . '<tr>'
        . '<td class="fw-semibold">15/11 – 30/11/2026</td>'
        . '<td><strong>DAUTAY-CNC-01</strong> — ' . s($en ? 'High-Tech Strawberry & Chrysanthemum (Da Lat & Don Duong Co-ops)' : 'Canh tác Dâu tây & Hoa Cúc CNC (HTX Đà Lạt & Đơn Dương)') . '</td>'
        . '<td><span class="mx-badge-open">' . s($en ? 'In Progress' : 'Đang học') . '</span></td>'
        . '</tr>'
        . '<tr>'
        . '<td class="fw-semibold">22/11 – 10/12/2026</td>'
        . '<td><strong>CHAMSOC01 &amp; IOTNHAMANG01</strong> — ' . s($en ? 'Automated Fertigation & IoT Greenhouse Operations' : 'Tưới nhỏ giọt Fertigation & Trạm Cảm biến Vi khí hậu IoT') . '</td>'
        . '<td><span class="mx-badge-open">' . s($en ? 'Open' : 'Nhận hồ sơ') . '</span></td>'
        . '</tr>'
        . '<tr>'
        . '<td class="fw-semibold">05/12 – 25/12/2026</td>'
        . '<td><strong>SAURIENG01 &amp; PHANVISINH01</strong> — ' . s($en ? 'Export Durian PUC & Trichoderma Bio-Compost' : 'Sầu riêng Xuất khẩu, Mã số Vùng trồng & Phân Vi sinh') . '</td>'
        . '<td><span class="mx-badge-upcoming">' . s($en ? 'Upcoming' : 'Sắp khai giảng') . '</span></td>'
        . '</tr>'
        . '</tbody>'
        . '</table>'
        . '</div>'
        . '</div>'

        . '<div class="mx-portal-panel">'
        . '<div class="mx-section-eyebrow">' . s($en ? 'PUBLIC CRYPTOGRAPHIC VERIFICATION (HMAC-SHA256)' : 'TRA CỨU CÔNG KHAI CHỨNG NHẬN SỐ & LÔ VIETGAP') . '</div>'
        . '<h2 class="mx-panel-title">' . s($en ? 'Quick QR Certificate & VietGAP Batch Lookup' : 'Tra cứu Nhanh Chứng nhận Số QR / Lô Nông sản VietGAP') . '</h2>'
        . '<p class="mx-panel-desc">'
        . s($en
            ? 'Public verification portal for agricultural purchasing partners, inspectors, and cooperatives. Enter a certificate or batch code to verify its cryptographic HMAC-SHA256 signature.'
            : 'Cổng xác thực công khai dành cho đối tác thu mua, cơ quan kiểm định và hợp tác xã. Nhập mã chứng nhận tập huấn hoặc mã lô nông sản VietGAP để kiểm tra chữ ký số HMAC-SHA256.')
        . '</p>'
        . '<form method="get" action="' . $verifyurl . '" class="mx-lookup-form">'
        . '<input type="text" name="code" class="form-control mx-lookup-input" placeholder="' . s($en ? 'Enter QR Certificate Code (e.g. MXCERT-2026-CHAMSOC01-0042)' : 'Nhập mã chứng nhận QR (VD: MXCERT-2026-CHAMSOC01-0042)') . '" required>'
        . '<button type="submit" class="btn btn-primary mx-lookup-submit">' . s($en ? 'Verify Certificate' : 'Tra cứu Chứng nhận') . '</button>'
        . '</form>'
        . '<div class="mx-lookup-samples">'
        . '<span class="mx-sample-label">' . s($en ? 'Sample issued codes for instant verification:' : 'Mã chứng nhận mẫu đã cấp trên sổ cái để kiểm tra nhanh:') . '</span>'
        . '<div class="mx-sample-pills">'
        . '<a href="' . $verifyurl . '?code=MXCERT-2026-CHAMSOC01-0042" class="mx-sample-code">MXCERT-2026-CHAMSOC01-0042</a>'
        . '<a href="' . $verifyurl . '?code=MXCERT-2026-IOTNHAMANG01-0044" class="mx-sample-code">MXCERT-2026-IOTNHAMANG01-0044</a>'
        . '<a href="' . $verifyurl . '?code=MXCERT-2026-THUYCANH01-0045" class="mx-sample-code">MXCERT-2026-THUYCANH01-0045</a>'
        . '</div>'
        . '</div>'
        . '</div>'
        . '</section>'

        // EDITORIAL ARTICLE READER MODAL
        . '<div id="mx-article-reader-modal" class="mx-article-modal" role="dialog" aria-modal="true" aria-hidden="true" style="display:none;">'
        . '<div class="mx-article-modal-backdrop js-close-mx-article"></div>'
        . '<div class="mx-article-modal-dialog">'
        . '<div class="mx-article-modal-header">'
        . '<div>'
        . '<span id="mx-modal-cat" class="mx-news-cat-inline"></span>'
        . '<span id="mx-modal-meta" class="mx-modal-meta-text"></span>'
        . '</div>'
        . '<button type="button" class="mx-modal-close-btn js-close-mx-article" aria-label="Close">×</button>'
        . '</div>'
        . '<div class="mx-article-modal-body">'
        . '<h2 id="mx-modal-title" class="mx-modal-article-title"></h2>'
        . '<div id="mx-modal-content" class="mx-modal-article-body"></div>'
        . '</div>'
        . '<div class="mx-article-modal-footer">'
        . '<button type="button" class="btn btn-secondary js-close-mx-article">' . s($en ? 'Close reader' : 'Đóng bản tin') . '</button>'
        . '<a href="' . ($isloggedinuser ? $myurl : $loginurl) . '" class="btn btn-primary">'
        . s($isloggedinuser ? ($en ? 'Go to Training Courses →' : 'Vào Khóa học Tập huấn →') : ($en ? 'Log in to access full training materials →' : 'Đăng nhập để xem học liệu chuyên sâu →'))
        . '</a>'
        . '</div>'
        . '</div>'
        . '</div>'

        . '<script type="application/json" id="mx-portal-articles-data">' . $jsonarticles . '</script>'
        . '<script>'
        . 'document.addEventListener("DOMContentLoaded", function() {'
        . '  var dataEl = document.getElementById("mx-portal-articles-data");'
        . '  var modal = document.getElementById("mx-article-reader-modal");'
        . '  if (!dataEl || !modal) return;'
        . '  var articles = {};'
        . '  try { articles = JSON.parse(dataEl.textContent); } catch(e) {}'
        . '  function openArticle(id) {'
        . '    var item = articles[id];'
        . '    if (!item) return;'
        . '    document.getElementById("mx-modal-cat").textContent = item.cat || "";'
        . '    document.getElementById("mx-modal-meta").textContent = " • " + (item.date || "") + " • " + (item.source || "");'
        . '    document.getElementById("mx-modal-title").textContent = item.title || "";'
        . '    document.getElementById("mx-modal-content").innerHTML = item.body || "";'
        . '    modal.style.display = "flex";'
        . '    modal.setAttribute("aria-hidden", "false");'
        . '  }'
        . '  function closeArticle() {'
        . '    modal.style.display = "none";'
        . '    modal.setAttribute("aria-hidden", "true");'
        . '  }'
        . '  document.querySelectorAll(".js-open-mx-article").forEach(function(btn) {'
        . '    btn.addEventListener("click", function() { openArticle(btn.getAttribute("data-article-id")); });'
        . '  });'
        . '  document.querySelectorAll(".js-close-mx-article").forEach(function(btn) {'
        . '    btn.addEventListener("click", closeArticle);'
        . '  });'
        . '  document.addEventListener("keydown", function(e) {'
        . '    if (e.key === "Escape" && modal.style.display === "flex") closeArticle();'
        . '  });'
        . '});'
        . '</script>'
        . '</div>';

    return $html;
}

/**
 * Build a clean, instant server-rendered academic course catalog for /my/courses.php.
 */
function theme_agritrain_child_build_my_courses_html(): string {
    global $CFG, $USER;
    if (!isloggedin() || isguestuser()) {
        return '';
    }
    require_once($CFG->libdir . '/completionlib.php');
    $reqlang = optional_param('lang', '', PARAM_ALPHA);
    $en = ($reqlang === 'en') ? true : (($reqlang === 'vi') ? false : (strpos(current_language(), 'en') === 0));

    $courses = enrol_get_my_courses(['id', 'fullname', 'shortname', 'summary', 'visible', 'enablecompletion']);
    if (empty($courses)) {
        return '';
    }

    $cards = '';
    foreach ($courses as $c) {
        if ((int)$c->id === SITEID || $c->shortname === 'MASTER-AGRITECH-TPL') {
            continue;
        }
        $modinfo = get_fast_modinfo($c, $USER->id);
        $completion = new completion_info($c);
        $cmods = 0;
        $cdone = 0;
        foreach ($modinfo->get_cms() as $cm) {
            if (!$cm->uservisible || $cm->modname === 'label') {
                continue;
            }
            $cmods++;
            if ($completion->is_enabled($cm)) {
                $cdata = $completion->get_data($cm, false, $USER->id);
                if (!empty($cdata->completionstate) && (int)$cdata->completionstate !== COMPLETION_INCOMPLETE) {
                    $cdone++;
                }
            }
        }
        $pct = $cmods > 0 ? (int)round(($cdone / $cmods) * 100) : 0;
        $courseurl = (new moodle_url('/course/view.php', ['id' => $c->id]))->out(false);
        $trurl = (new moodle_url('/local/mamxanhapp/transcript.php', ['courseid' => $c->id]))->out(false);
        $fburl = (new moodle_url('/local/mamxanhapp/feedback_sheet.php', ['courseid' => $c->id]))->out(false);

        $summary = trim(strip_tags(format_text($c->summary ?? '', FORMAT_HTML)));
        if (core_text::strlen($summary) > 130) {
            $summary = core_text::substr($summary, 0, 127) . '...';
        }

        $cards .= '<article class="mx-pub-course-card" style="background:#FFFFFF;border:1px solid #E6ECE9;border-radius:10px;padding:20px;display:flex;flex-direction:column;justify-content:space-between;">'
            . '<div>'
            . '<div class="d-flex justify-content-between align-items-center mb-2">'
            . '<span class="badge" style="background:#EAF2EE;color:#234E3E;border:1px solid #E6ECE9;font-weight:700;">' . s($c->shortname) . '</span>'
            . '<span class="small text-muted fw-bold">' . $cdone . '/' . $cmods . ' ' . s($en ? 'completed' : 'hoàn thành') . ' (' . $pct . '%)</span>'
            . '</div>'
            . '<h3 class="h5 fw-bold mb-2"><a href="' . $courseurl . '" style="color:#0F172A;text-decoration:none;">' . s($c->fullname) . '</a></h3>'
            . '<p class="small text-muted mb-3">' . s($summary) . '</p>'
            . '</div>'
            . '<div>'
            . '<div class="mx-bar mb-3"><div class="mx-bar-fill" style="width:' . $pct . '%;"></div></div>'
            . '<div class="d-flex flex-wrap" style="gap:8px;">'
            . '<a href="' . $courseurl . '" class="btn btn-sm btn-primary">' . s($en ? 'Enter Course' : 'Vào lớp học') . '</a>'
            . '<a href="' . $trurl . '" class="btn btn-sm btn-secondary">' . s($en ? 'Transcript' : 'Bảng điểm') . '</a>'
            . '<a href="' . $fburl . '" class="btn btn-sm btn-secondary">' . s($en ? 'Rubric' : 'Phiếu Rubric') . '</a>'
            . '</div>'
            . '</div>'
            . '</article>';
    }

    return '<section class="mx-mycourses-catalog mt-2 mb-4">'
        . '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:18px;">'
        . $cards
        . '</div>'
        . '</section>';
}
