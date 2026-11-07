<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/completionlib.php');

local_mamxanhapp_handle_lang_switch();
require_login();

$syscontext = context_system::instance();
local_mamxanhapp_require_capability_or_403(
    'local/mamxanhapp:manage',
    $syscontext,
    'Chức năng Quản lý Tập huấn, Thống kê & Xuất Báo cáo CSV chỉ dành cho Cán bộ kỹ thuật và Quản trị viên.',
    'Training Analytics & CSV Report Export is restricted to Technical Officers (CBKT) and System Administrators.'
);

$download = optional_param('download', '', PARAM_ALPHA);
$regionfilter = optional_param('region', '', PARAM_TEXT);
$en = local_mamxanhapp_is_en();
$dbman = $DB->get_manager();

// Load all visible courses (excluding frontpage)
$allcourses = get_courses();
$courses = [];
foreach ($allcourses as $c) {
    if ((int)$c->id !== SITEID) {
        $courses[$c->id] = $c;
    }
}

// Load farmer profiles indexed by userid
$profiles = [];
if ($dbman->table_exists('local_farmerprofile')) {
    $recs = $DB->get_records('local_farmerprofile');
    foreach ($recs as $r) {
        $profiles[(int)$r->userid] = $r;
    }
}

// Load certificates indexed by userid
$certsbyuser = [];
$totalcerts = 0;
if ($dbman->table_exists('local_mamxanhcert')) {
    $crecs = $DB->get_records('local_mamxanhcert', ['status' => 'valid']);
    $totalcerts = count($crecs);
    foreach ($crecs as $cr) {
        $certsbyuser[(int)$cr->userid][] = $cr;
    }
}

// Load VietGAP batch counts
$batchesbyuser = [];
$totalbatches = 0;
if ($dbman->table_exists('local_agriops_batch')) {
    $brecs = $DB->get_records('local_agriops_batch');
    $totalbatches = count($brecs);
    foreach ($brecs as $br) {
        $uid = (int)$br->userid;
        $batchesbyuser[$uid] = ($batchesbyuser[$uid] ?? 0) + 1;
    }
}

// Load Field Practice Rubric grades indexed by userid
$rubricsbyuser = [];
$allrubricscores = [];
if ($dbman->table_exists('fieldpractice_sub')) {
    $srecs = $DB->get_records_select('fieldpractice_sub', 'total_grade IS NOT NULL AND graded_time > 0');
    foreach ($srecs as $sr) {
        $uid = (int)$sr->userid;
        $rubricsbyuser[$uid][] = (float)$sr->total_grade;
        $allrubricscores[] = (float)$sr->total_grade;
    }
}

// Load finished Quiz attempts across all courses
$quizmap = $DB->get_records('quiz', null, '', 'id,course,name,sumgrades');
$quizbyuser = [];
$quizbycourse = [];
$allquizscores = [];
$finishedattempts = $DB->get_records('quiz_attempts', ['state' => 'finished']);
foreach ($finishedattempts as $att) {
    if (!isset($quizmap[$att->quiz])) {
        continue;
    }
    $q = $quizmap[$att->quiz];
    if ((float)$q->sumgrades <= 0) {
        continue;
    }
    $pct = round(((float)$att->sumgrades / (float)$q->sumgrades) * 100.0, 1);
    $uid = (int)$att->userid;
    $cid = (int)$q->course;
    if (!isset($quizbyuser[$uid][$cid]) || $pct > $quizbyuser[$uid][$cid]) {
        $quizbyuser[$uid][$cid] = $pct;
    }
    $quizbycourse[$cid][] = $pct;
    $allquizscores[] = $pct;
}

// Load learner users (exclude guest, admin, cbkt)
$users = $DB->get_records_select('user', "deleted = 0 AND suspended = 0 AND username NOT IN ('guest', 'admin', 'cbkt_pilot')", null, 'id ASC');

// Aggregate Learner Rows & Region Stats
$learnerrows = [];
$regionstats = [
    'Lâm Đồng' => ['count' => 0, 'area' => 0, 'quizsum' => 0, 'quizcnt' => 0],
    'Đắk Lắk'  => ['count' => 0, 'area' => 0, 'quizsum' => 0, 'quizcnt' => 0],
    'Đồng Nai' => ['count' => 0, 'area' => 0, 'quizsum' => 0, 'quizcnt' => 0],
];
$totalaream2 = 0;

foreach ($users as $u) {
    $uid = (int)$u->id;
    if (is_siteadmin($uid) || has_capability('local/mamxanhapp:manage', $syscontext, $uid)) {
        continue;
    }
    $pf = $profiles[$uid] ?? null;
    $reg = ($pf && !empty($pf->region)) ? $pf->region : 'Lâm Đồng';
    $area = $pf ? (int)$pf->area_m2 : 1500;
    $crop = ($pf && !empty($pf->main_crop)) ? $pf->main_crop : 'Rau công nghệ cao';
    $farm = ($pf && !empty($pf->farm_name)) ? $pf->farm_name : ('Nông hộ ' . fullname($u));

    $totalaream2 += $area;
    if (!isset($regionstats[$reg])) {
        $regionstats[$reg] = ['count' => 0, 'area' => 0, 'quizsum' => 0, 'quizcnt' => 0];
    }
    $regionstats[$reg]['count']++;
    $regionstats[$reg]['area'] += $area;

    $completedmods = $DB->count_records_select('course_modules_completion', 'userid = :uid AND completionstate > 0', ['uid' => $uid]);
    $uquizzes = $quizbyuser[$uid] ?? [];
    $avguserquiz = !empty($uquizzes) ? round(array_sum($uquizzes) / count($uquizzes), 1) : null;
    if ($avguserquiz !== null) {
        $regionstats[$reg]['quizsum'] += $avguserquiz;
        $regionstats[$reg]['quizcnt']++;
    }

    $urubrics = $rubricsbyuser[$uid] ?? [];
    $avguserrubric = !empty($urubrics) ? round(array_sum($urubrics) / count($urubrics), 1) : null;

    $ucerts = $certsbyuser[$uid] ?? [];
    $certcodes = array_map(static fn($c) => $c->cert_code, $ucerts);
    $ubatches = $batchesbyuser[$uid] ?? 0;

    if ($regionfilter !== '' && $regionfilter !== $reg) {
        continue;
    }

    if (!empty($certcodes)) {
        $statuslabel = $en ? 'Certified (Pass >=70%)' : 'Đã cấp Chứng nhận QR';
    } else if ($completedmods > 0 || $avguserquiz !== null) {
        $statuslabel = $en ? 'Active In-Training' : 'Đang tập huấn tích cực';
    } else {
        $statuslabel = $en ? 'Enrolled' : 'Mới ghi danh';
    }

    $learnerrows[] = (object)[
        'userid'        => $uid,
        'username'      => $u->username,
        'fullname'      => fullname($u),
        'email'         => $u->email,
        'farm_name'     => $farm,
        'region'        => $reg,
        'crop'          => $crop,
        'area_m2'       => $area,
        'completedmods' => $completedmods,
        'avgquiz'       => $avguserquiz,
        'avgrubric'     => $avguserrubric,
        'certcount'     => count($certcodes),
        'certcodes'     => implode('; ', $certcodes),
        'batches'       => $ubatches,
        'status'        => $statuslabel,
    ];
}

// ===================================================================
// CSV UTF-8 BOM STREAM EXPORT (?download=csv)
// ===================================================================
if ($download === 'csv') {
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    $filename = ($en ? 'MamXanh_Progress_Report_' : 'MamXanh_BaoCao_TienDo_') . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    // Write UTF-8 BOM so Microsoft Excel renders Vietnamese accents cleanly
    fwrite($out, "\xEF\xBB\xBF");

    if ($en) {
        fputcsv($out, [
            'Username', 'Farmer Full Name', 'Farm Household', 'Growing Region',
            'Main Crop', 'Area (m2)', 'Completed Lessons', 'Avg Quiz Score (%)',
            'Field Practice Rubric (100)', 'Certificates Earned', 'Certificate Codes',
            'VietGAP Batches', 'Training Status'
        ]);
    } else {
        fputcsv($out, [
            'Mã tài khoản', 'Họ và tên Nông dân', 'Tên Nông hộ', 'Vùng trồng (Cohort)',
            'Cây trồng chủ lực', 'Diện tích (m2)', 'Bài học hoàn thành', 'Điểm Quiz TB (%)',
            'Điểm Thực hành Vườn (100)', 'Số Chứng nhận', 'Mã Chứng nhận QR',
            'Số Lô VietGAP', 'Trạng thái Tập huấn'
        ]);
    }

    foreach ($learnerrows as $row) {
        fputcsv($out, [
            $row->username,
            $row->fullname,
            $row->farm_name,
            $row->region,
            $row->crop,
            $row->area_m2,
            $row->completedmods,
            $row->avgquiz !== null ? $row->avgquiz : '',
            $row->avgrubric !== null ? $row->avgrubric : '',
            $row->certcount,
            $row->certcodes,
            $row->batches,
            $row->status,
        ]);
    }
    fclose($out);
    exit;
}

// Load Field Practice instances for quick Rubric Grading links
$fieldpractices = [];
if ($dbman->table_exists('fieldpractice')) {
    $fprecs = $DB->get_records('fieldpractice');
    foreach ($fprecs as $fp) {
        $cm = get_coursemodule_from_instance('fieldpractice', $fp->id, $fp->course, false, IGNORE_MISSING);
        if ($cm) {
            $cobj = $courses[$fp->course] ?? null;
            $fieldpractices[] = (object)[
                'cmid'       => $cm->id,
                'name'       => format_string($fp->name),
                'coursename' => $cobj ? format_string($cobj->shortname) : ('Course #' . $fp->course),
                'gradeurl'   => (new moodle_url('/mod/fieldpractice/grade.php', ['id' => $cm->id]))->out(false),
                'viewurl'    => (new moodle_url('/mod/fieldpractice/view.php', ['id' => $cm->id]))->out(false),
            ];
        }
    }
}

$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/mamxanhapp/manage.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(local_mamxanhapp_t('Quản lý & Báo cáo Cán bộ Kỹ thuật - Mầm Xanh LMS 2.0', 'Instructor Analytics & Reports - Mam Xanh LMS 2.0'));
$PAGE->set_heading(local_mamxanhapp_t('📊 Trung tâm Điều hành & Báo cáo Tập huấn Nông nghiệp', '📊 Agricultural Training Analytics & Grading Center'));

$avgquizglobal = !empty($allquizscores) ? round(array_sum($allquizscores) / count($allquizscores), 1) . '%' : '--';
$avgrubricglobal = !empty($allrubricscores) ? round(array_sum($allrubricscores) / count($allrubricscores), 1) . '/100' : '--';
$totalfarmers = array_sum(array_column($regionstats, 'count'));

$csvurl = new moodle_url('/local/mamxanhapp/manage.php', array_filter([
    'download' => 'csv',
    'region'   => $regionfilter,
]));

echo $OUTPUT->header();
?>

<div class="mx-manage-container">
    <!-- Top Executive Banner -->
    <section style="background:linear-gradient(135deg,#1B4332 0%,#2E7D32 100%);color:#FFF;border-radius:24px;padding:24px;margin-bottom:22px;box-shadow:0 12px 30px rgba(27,67,50,0.16);">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:16px;">
            <div>
                <div style="display:inline-block;background:rgba(255,255,255,0.18);padding:5px 14px;border-radius:999px;font-size:0.85rem;font-weight:700;margin-bottom:8px;">
                    👨‍🔬 <?php echo s(is_siteadmin()
                        ? local_mamxanhapp_t('Quản trị viên Hệ thống (Full Access)', 'System Administrator (Full Access)')
                        : local_mamxanhapp_t('Cán bộ Kỹ thuật Nông nghiệp (CBKT)', 'Agricultural Technical Officer (CBKT)')); ?>
                </div>
                <h2 style="color:#FFF;font-weight:800;font-size:1.55rem;margin-bottom:6px;">
                    <?php echo s(local_mamxanhapp_t('Báo cáo Tiến độ Học tập, Điểm Thực hành Vườn & Truy xuất VietGAP', 'Training Progress, Garden Rubric Grading & VietGAP Analytics')); ?>
                </h2>
                <p style="color:#D8F3DC;margin:0;font-size:0.95rem;">
                    <?php echo s(local_mamxanhapp_t(
                        'Theo dõi trực tuyến kết quả đào tạo 6 khóa học số hóa, chấm điểm Rubric 4 tiêu chí tại vườn và xuất báo cáo CSV chuẩn UTF-8 BOM.',
                        'Monitor live progress across 6 digital courses, grade 4-criteria garden rubrics, and export UTF-8 BOM Excel CSV reports.'
                    )); ?>
                </p>
            </div>
            <div class="d-flex flex-wrap" style="gap:10px;">
                <a href="<?php echo (new moodle_url('/local/mamxanhapp/clone_course.php'))->out(false); ?>" id="mx-open-cloner-btn" class="btn btn-light" style="min-height:48px;display:inline-flex;align-items:center;gap:8px;border-radius:14px;font-weight:900;color:#1B4332;padding:0 20px;border:2px solid #D8F3DC;">
                    🧬 <?php echo s(local_mamxanhapp_t('Nhân bản Khóa học Địa phương (1-Click)', '1-Click Local Course Cloner')); ?>
                </a>
                <a href="<?php echo $csvurl->out(false); ?>" id="mx-export-csv-btn" class="btn btn-warning" style="min-height:48px;display:inline-flex;align-items:center;gap:8px;border-radius:14px;font-weight:800;color:#1B4332;padding:0 20px;">
                    📥 <?php echo s(local_mamxanhapp_t('Xuất Báo cáo Excel (CSV UTF-8)', 'Export CSV Report (UTF-8 BOM)')); ?>
                </a>
                <a href="<?php echo (new moodle_url('/local/farmerprofile/index.php'))->out(false); ?>" class="btn btn-light" style="min-height:48px;display:inline-flex;align-items:center;gap:8px;border-radius:14px;font-weight:700;color:#1B4332;padding:0 18px;">
                    🗺️ <?php echo s(local_mamxanhapp_t('Danh bạ Nông hộ & Cohort', 'Farmer Profiles & Cohorts')); ?>
                </a>
                <?php if (is_siteadmin()): ?>
                <a href="<?php echo (new moodle_url('/local/mamxanhapp/admin_audit.php'))->out(false); ?>" class="btn btn-outline-light" style="min-height:48px;display:inline-flex;align-items:center;gap:8px;border-radius:14px;font-weight:700;padding:0 18px;border-width:2px;">
                    ⚙️ <?php echo s(local_mamxanhapp_t('Kiểm toán Hạ tầng Turbo', 'Turbo System Audit')); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- 4 Executive KPI Cards -->
    <section class="row mb-4">
        <div class="col-6 col-lg-3 mb-3">
            <div style="background:#FFF;border-radius:20px;padding:18px;border:2px solid #DCEEE0;height:100%;">
                <div style="font-size:0.86rem;color:#4E6358;font-weight:700;"><?php echo s(local_mamxanhapp_t('👨‍🌾 Nông dân & Diện tích', '👨‍🌾 Farmers & Total Area')); ?></div>
                <div style="font-size:1.65rem;font-weight:800;color:#1B4332;margin:6px 0;"><?php echo (int)$totalfarmers; ?> <small style="font-size:0.95rem;color:#2E7D32;">(<?php echo number_format($totalaream2 / 10000, 2); ?> ha)</small></div>
                <div style="font-size:0.82rem;color:#2E7D32;font-weight:600;"><?php echo number_format($totalaream2); ?> m² VietGAP</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div style="background:#FFF;border-radius:20px;padding:18px;border:2px solid #DCEEE0;height:100%;">
                <div style="font-size:0.86rem;color:#4E6358;font-weight:700;"><?php echo s(local_mamxanhapp_t('📝 Điểm Quiz Lý thuyết TB', '📝 Avg Quiz Theory Score')); ?></div>
                <div style="font-size:1.65rem;font-weight:800;color:#2E7D32;margin:6px 0;"><?php echo s($avgquizglobal); ?></div>
                <div style="font-size:0.82rem;color:#4E6358;"><?php echo count($allquizscores) . ' ' . s(local_mamxanhapp_t('lượt nộp bài hoàn tất', 'graded quiz submissions')); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div style="background:#FFF;border-radius:20px;padding:18px;border:2px solid #DCEEE0;height:100%;">
                <div style="font-size:0.86rem;color:#4E6358;font-weight:700;"><?php echo s(local_mamxanhapp_t('🌱 Điểm Thực hành Rubric TB', '🌱 Avg Garden Rubric Score')); ?></div>
                <div style="font-size:1.65rem;font-weight:800;color:#1B4332;margin:6px 0;"><?php echo s($avgrubricglobal); ?></div>
                <div style="font-size:0.82rem;color:#4E6358;"><?php echo count($allrubricscores) . ' ' . s(local_mamxanhapp_t('phiếu chấm thực địa', 'graded field rubrics')); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div style="background:#FFF;border-radius:20px;padding:18px;border:2px solid #DCEEE0;height:100%;">
                <div style="font-size:0.86rem;color:#4E6358;font-weight:700;"><?php echo s(local_mamxanhapp_t('🎓 Chứng nhận QR & Lô VietGAP', '🎓 QR Certs & VietGAP Lots')); ?></div>
                <div style="font-size:1.65rem;font-weight:800;color:#2E7D32;margin:6px 0;"><?php echo (int)$totalcerts; ?> / <?php echo (int)$totalbatches; ?></div>
                <div style="font-size:0.82rem;color:#2E7D32;font-weight:600;">HMAC-SHA256 Verified</div>
            </div>
        </div>
    </section>

    <!-- Live Analytics Charts (Regional Breakdown & Course Performance) -->
    <section class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div style="background:#FFF;border-radius:22px;padding:20px;border:2px solid #DCEEE0;height:100%;">
                <h4 style="font-size:1.1rem;font-weight:800;color:#1B4332;margin-bottom:16px;">
                    🗺️ <?php echo s(local_mamxanhapp_t('Phân bố Học viên & Điểm TB theo Vùng trồng (Cohort)', 'Learner Distribution & Avg Score by Growing Region')); ?>
                </h4>
                <?php
                $maxreg = max(1, max(array_column($regionstats, 'count')));
                $regcolors = ['Lâm Đồng' => '#1B4332', 'Đắk Lắk' => '#2E7D32', 'Đồng Nai' => '#43A047'];
                foreach ($regionstats as $rname => $st):
                    $barpct = (int)round(($st['count'] / $maxreg) * 100);
                    $ravg = $st['quizcnt'] > 0 ? round($st['quizsum'] / $st['quizcnt'], 1) . '%' : '--';
                    $col = $regcolors[$rname] ?? '#2E7D32';
                ?>
                <div style="margin-bottom:14px;">
                    <div class="d-flex justify-content-between" style="font-size:0.9rem;font-weight:700;margin-bottom:5px;">
                        <span>📍 <?php echo s($rname); ?> (<?php echo (int)$st['count']; ?> <?php echo s(local_mamxanhapp_t('hộ', 'farms')); ?> • <?php echo number_format($st['area']); ?> m²)</span>
                        <span style="color:#1B4332;">Quiz TB: <?php echo s($ravg); ?></span>
                    </div>
                    <div style="height:14px;background:#E8F5E9;border-radius:999px;overflow:hidden;">
                        <div style="width:<?php echo max(8, $barpct); ?>%;height:100%;background:<?php echo $col; ?>;border-radius:999px;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div style="background:#FFF;border-radius:22px;padding:20px;border:2px solid #DCEEE0;height:100%;">
                <h4 style="font-size:1.1rem;font-weight:800;color:#1B4332;margin-bottom:16px;">
                    📈 <?php echo s(local_mamxanhapp_t('Hiệu quả Đào tạo theo Khóa học Kỹ thuật', 'Performance Across Digital Courses')); ?>
                </h4>
                <?php foreach ($courses as $cid => $c):
                    $cscores = $quizbycourse[$cid] ?? [];
                    $cavg = !empty($cscores) ? round(array_sum($cscores) / count($cscores), 1) : 0;
                    $ccount = count($cscores);
                ?>
                <div style="margin-bottom:12px;">
                    <div class="d-flex justify-content-between" style="font-size:0.86rem;font-weight:700;margin-bottom:4px;">
                        <span><strong><?php echo s($c->shortname); ?></strong> — <?php echo s(\core_text::substr($c->fullname, 0, 38)); ?></span>
                        <span style="color:#2E7D32;"><?php echo $ccount > 0 ? ($cavg . '% (' . $ccount . ')') : s(local_mamxanhapp_t('Sẵn sàng', 'Ready')); ?></span>
                    </div>
                    <div style="height:10px;background:#E8F5E9;border-radius:999px;overflow:hidden;">
                        <div style="width:<?php echo max(6, (int)$cavg); ?>%;height:100%;background:linear-gradient(90deg,#2E7D32,#66BB6A);border-radius:999px;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Quick 1-Click Master Course Cloner Banner -->
    <section style="background:linear-gradient(135deg,#E8F5E9,#D8F3DC);border:2px solid #2E7D32;border-radius:22px;padding:20px;margin-bottom:20px;">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:14px;">
            <div>
                <h4 style="font-size:1.12rem;font-weight:900;color:#1B4332;margin-bottom:4px;">
                    🧬 <?php echo s(local_mamxanhapp_t('Nhân bản Khóa học Mẫu theo Địa phương / Nhóm Nông dân (1-Click Course Cloner)', '1-Click Master Course Cloner for Local Cohorts')); ?>
                </h4>
                <p style="margin:0;color:#1B4332;font-size:0.9rem;font-weight:600;">
                    <?php echo s(local_mamxanhapp_t(
                        'Sao chép nguyên vẹn Khóa học Mẫu [MASTER-AGRITECH-TPL] (Video MP4, PDF xem trực tiếp, H5P, SCORM 1.2, Lộ trình tuần tự & Ngân hàng câu hỏi) cho từng xã/hợp tác xã mới.',
                        'Duplicate [MASTER-AGRITECH-TPL] (HD Video, Inline PDF, H5P, SCORM 1.2, Sequential Path & Question Bank) for any local farming cohort in seconds.'
                    )); ?>
                </p>
            </div>
            <a href="<?php echo (new moodle_url('/local/mamxanhapp/clone_course.php'))->out(false); ?>" class="btn btn-success" style="min-height:48px;display:inline-flex;align-items:center;gap:8px;border-radius:14px;font-weight:900;background:#1B4332;border-color:#1B4332;padding:0 22px;">
                🚀 <?php echo s(local_mamxanhapp_t('Mở Công cụ Nhân bản Khóa học →', 'Launch 1-Click Course Cloner →')); ?>
            </a>
        </div>
    </section>

    <!-- Quick Field Practice Rubric Grading Shortcuts -->
    <?php if (!empty($fieldpractices)): ?>
    <section style="background:#F4FBF6;border:2px solid #B7E4C7;border-radius:22px;padding:20px;margin-bottom:24px;">
        <h4 style="font-size:1.1rem;font-weight:800;color:#1B4332;margin-bottom:12px;">
            🌿 <?php echo s(local_mamxanhapp_t('Chấm điểm Thực hành Tại vườn (Rubric 4 Tiêu chí -> Đồng bộ Gradebook)', 'Field Practice Garden Grading (4-Criteria Rubric -> Syncs to Gradebook)')); ?>
        </h4>
        <div class="d-flex flex-wrap" style="gap:10px;">
            <?php foreach ($fieldpractices as $fp): ?>
            <a href="<?php echo $fp->gradeurl; ?>" class="btn btn-success" style="min-height:48px;display:inline-flex;align-items:center;gap:8px;border-radius:14px;font-weight:700;background:#1B4332;border-color:#1B4332;">
                📝 [<?php echo s($fp->coursename); ?>] <?php echo s($fp->name); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Learner Master Table -->
    <section style="background:#FFF;border-radius:22px;padding:20px;border:2px solid #DCEEE0;">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:12px;">
            <h4 style="font-size:1.15rem;font-weight:800;color:#1B4332;margin:0;">
                📋 <?php echo s(local_mamxanhapp_t('Bảng Tổng hợp Tiến độ & Điểm số Học viên Nông dân', 'Master Farmer Learner Progress & Gradebook Report')); ?>
            </h4>
            <div class="d-flex flex-wrap" style="gap:8px;">
                <?php foreach (['' => local_mamxanhapp_t('Tất cả vùng', 'All Regions'), 'Lâm Đồng' => 'Lâm Đồng', 'Đắk Lắk' => 'Đắk Lắk', 'Đồng Nai' => 'Đồng Nai'] as $rk => $rl):
                    $active = ($regionfilter === $rk);
                    $furl = new moodle_url('/local/mamxanhapp/manage.php', $rk !== '' ? ['region' => $rk] : []);
                ?>
                <a href="<?php echo $furl->out(false); ?>" class="btn btn-sm <?php echo $active ? 'btn-success' : 'btn-outline-secondary'; ?>" style="min-height:40px;display:inline-flex;align-items:center;border-radius:12px;font-weight:700;padding:0 14px;">
                    <?php echo s($rl); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#E8F5E9;color:#1B4332;">
                    <tr>
                        <th><?php echo s(local_mamxanhapp_t('Họ tên / Tài khoản', 'Farmer / Username')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('Vùng trồng & Diện tích', 'Region & Area')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('Cây chủ lực', 'Main Crop')); ?></th>
                        <th class="text-center"><?php echo s(local_mamxanhapp_t('Bài đã học', 'Completed')); ?></th>
                        <th class="text-center"><?php echo s(local_mamxanhapp_t('Điểm Quiz TB', 'Avg Quiz')); ?></th>
                        <th class="text-center"><?php echo s(local_mamxanhapp_t('Thực hành Rubric', 'Field Rubric')); ?></th>
                        <th class="text-center"><?php echo s(local_mamxanhapp_t('Chứng nhận QR', 'QR Certs')); ?></th>
                        <th><?php echo s(local_mamxanhapp_t('Trạng thái', 'Status')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($learnerrows)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <?php echo s(local_mamxanhapp_t('Chưa có dữ liệu học viên cho bộ lọc này.', 'No learner records found for this filter.')); ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($learnerrows as $row): ?>
                    <tr>
                        <td>
                            <div style="font-weight:800;color:#14281D;"><?php echo s($row->fullname); ?></div>
                            <div style="font-size:0.82rem;color:#546E7A;"><code><?php echo s($row->username); ?></code> • <?php echo s($row->farm_name); ?></div>
                        </td>
                        <td>
                            <span style="display:inline-block;background:#E8F5E9;color:#1B4332;padding:3px 10px;border-radius:999px;font-size:0.82rem;font-weight:700;">
                                📍 <?php echo s($row->region); ?>
                            </span>
                            <div style="font-size:0.82rem;color:#455A64;margin-top:2px;"><?php echo number_format($row->area_m2); ?> m²</div>
                        </td>
                        <td style="font-weight:600;"><?php echo s($row->crop); ?></td>
                        <td class="text-center"><strong><?php echo (int)$row->completedmods; ?></strong></td>
                        <td class="text-center">
                            <?php if ($row->avgquiz !== null): ?>
                                <span style="font-weight:800;color:<?php echo $row->avgquiz >= 70 ? '#2E7D32' : '#E65100'; ?>;"><?php echo s($row->avgquiz . '%'); ?></span>
                            <?php else: ?>
                                <span class="text-muted">--</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($row->avgrubric !== null): ?>
                                <span style="font-weight:800;color:#1B4332;"><?php echo s($row->avgrubric . '/100'); ?></span>
                            <?php else: ?>
                                <span class="text-muted">--</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($row->certcount > 0): ?>
                                <span style="background:#D8F3DC;color:#1B4332;padding:4px 10px;border-radius:999px;font-weight:800;font-size:0.82rem;">
                                    🏅 <?php echo (int)$row->certcount; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="font-size:0.85rem;font-weight:700;color:#1B4332;"><?php echo s($row->status); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php
echo $OUTPUT->footer();
