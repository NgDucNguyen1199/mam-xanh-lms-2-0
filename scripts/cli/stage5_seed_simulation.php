<?php
/**
 * Stage 5: Realistic Simulation Data Seeder (15 Farmers across 3 Regions, Course Completions,
 * Genuine Moodle Quiz Engine Attempts in Gradebook, 4-Criteria Field Rubric Grades,
 * VietGAP Traceability Batches + Voice Logs, and HMAC-SHA256 Digital Certificates & Badges).
 */
define('CLI_SCRIPT', true);

require('/var/www/html/config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/local/farmerprofile/lib.php');
require_once($CFG->dirroot . '/local/agriops/lib.php');
require_once($CFG->dirroot . '/local/mamxanhcert/lib.php');
require_once($CFG->dirroot . '/mod/fieldpractice/lib.php');

use mod_quiz\quiz_settings;
use mod_quiz\quiz_attempt;

echo "=== [Stage 5] Seeding Simulation Data for Mam Xanh LMS 2.0 ===\n";

$admin = get_admin();
\core\session\manager::set_user($admin);

$cbkt = $DB->get_record('user', ['username' => 'cbkt_pilot'], '*', MUST_EXIST);
$studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
$editingteacherrole = $DB->get_record('role', ['shortname' => 'editingteacher'], '*', MUST_EXIST);
$cbktrole = $DB->get_record('role', ['shortname' => 'canbokythuat']);

// 15 Farmer Accounts across Lâm Đồng (5), Đắk Lắk (5), Đồng Nai (5)
$farmerdefs = [
    // --- Core 3 Pilot Farmers from Stage 2 ---
    [
        'username'  => 'nd_rau_lamdong',
        'firstname' => 'Hoàng Văn',
        'lastname'  => 'Lâm (Nông dân Rau Đà Lạt)',
        'email'     => 'nd_rau_lamdong@mamxanh.local',
        'city'      => 'Đà Lạt, Lâm Đồng',
        'farm_name' => 'Nông trại Rau Thủy Canh & Nhà Màng Xuân Thọ',
        'area_m2'   => 3500,
        'main_crop' => 'Xà lách thủy canh, Ớt chuông & Dâu tây nhà màng',
        'region'    => 'Lâm Đồng',
        'gps_lat'   => '11.946520',
        'gps_lng'   => '108.504110',
        'cert_std'  => 'VietGAP',
        'phone'     => '0918234567',
        'courses_quiz' => ['CHAMSOC01' => 11, 'IOTNHAMANG01' => 11, 'THUYCANH01' => 12], // CHEBIEN01 reserved for live E2E Quiz!
        'batch'     => ['product_name' => 'Xà lách Thủy canh Giòn Đà Lạt Lô A1', 'crop_variety' => 'Xà lách Romaine F1 Hà Lan', 'yield' => 1450],
    ],
    [
        'username'  => 'nd_saurieng_daklak',
        'firstname' => 'Y Đăm',
        'lastname'  => 'Niê (Nông dân Sầu riêng Krông Pắc)',
        'email'     => 'nd_saurieng_daklak@mamxanh.local',
        'city'      => 'Krông Pắc, Đắk Lắk',
        'farm_name' => 'Vườn Sầu riêng Xuất khẩu Ea Yông VietGAP',
        'area_m2'   => 15000,
        'main_crop' => 'Sầu riêng Dona (Monthong) & Ri6 xuất khẩu',
        'region'    => 'Đắk Lắk',
        'gps_lat'   => '12.695310',
        'gps_lng'   => '108.312450',
        'cert_std'  => 'GlobalGAP',
        'phone'     => '0987654321',
        'courses_quiz' => ['SAURIENG01' => 12, 'CHAMSOC01' => 10, 'CHEBIEN01' => 11, 'PHANVISINH01' => 10],
        'batch'     => ['product_name' => 'Sầu riêng Dona Xuất khẩu Mã vùng VN-DLOR-08', 'crop_variety' => 'Monthong Thái Tuyển chọn', 'yield' => 18500],
    ],
    [
        'username'  => 'nd_vanan',
        'firstname' => 'Trần Văn',
        'lastname'  => 'An (Nông dân Trái cây Đồng Nai)',
        'email'     => 'nd_vanan@mamxanh.local',
        'city'      => 'Long Khánh, Đồng Nai',
        'farm_name' => 'Trang trại Dưa lưới & Bưởi Da Xanh Bình Lộc',
        'area_m2'   => 8000,
        'main_crop' => 'Dưa lưới nhà màng & Bưởi da xanh VietGAP',
        'region'    => 'Đồng Nai',
        'gps_lat'   => '10.958120',
        'gps_lng'   => '107.241890',
        'cert_std'  => 'VietGAP',
        'phone'     => '0933456789',
        'courses_quiz' => ['CHAMSOC01' => 10, 'CHEBIEN01' => 10, 'IOTNHAMANG01' => 9],
        'batch'     => ['product_name' => 'Dưa lưới Hoàng Kim Nhà màng Long Khánh', 'crop_variety' => 'TL3 F1 Nhật Bản', 'yield' => 4200],
    ],
    // --- 12 Additional Regional Farmers (Total = 15 Farmers) ---
    [
        'username'  => 'nd_hoa_dalat',
        'firstname' => 'Nguyễn Thị',
        'lastname'  => 'Thanh Thảo',
        'email'     => 'nd_hoa_dalat@mamxanh.local',
        'city'      => 'Lạc Dương, Lâm Đồng',
        'farm_name' => 'Nông hộ Cà chua Beef & Ớt chuông Langbiang',
        'area_m2'   => 4200,
        'main_crop' => 'Cà chua Beef & Ớt chuông hữu cơ',
        'region'    => 'Lâm Đồng',
        'gps_lat'   => '12.012340',
        'gps_lng'   => '108.431200',
        'cert_std'  => 'VietGAP',
        'phone'     => '0912300101',
        'courses_quiz' => ['CHAMSOC01' => 11, 'IOTNHAMANG01' => 12, 'THUYCANH01' => 10],
        'batch'     => ['product_name' => 'Cà chua Beef Hữu cơ Langbiang', 'crop_variety' => 'Beef F1 Syngenta', 'yield' => 5600],
    ],
    [
        'username'  => 'nd_dau_donduong',
        'firstname' => 'Phạm Minh',
        'lastname'  => 'Khôi',
        'email'     => 'nd_dau_donduong@mamxanh.local',
        'city'      => 'Đơn Dương, Lâm Đồng',
        'farm_name' => 'HTX Rau Công nghệ cao Thạnh Mỹ',
        'area_m2'   => 6000,
        'main_crop' => 'Dâu tây New Zealand & Bắp cải tim',
        'region'    => 'Lâm Đồng',
        'gps_lat'   => '11.832100',
        'gps_lng'   => '108.561200',
        'cert_std'  => 'GlobalGAP',
        'phone'     => '0912300102',
        'courses_quiz' => ['CHAMSOC01' => 9, 'CHEBIEN01' => 11, 'PHANVISINH01' => 11],
        'batch'     => ['product_name' => 'Dâu tây Nhà màng Giá thể Xơ dừa', 'crop_variety' => 'Albion New Zealand', 'yield' => 2100],
    ],
    [
        'username'  => 'nd_thuycanh_ductrong',
        'firstname' => 'Lê Quốc',
        'lastname'  => 'Bảo',
        'email'     => 'nd_thuycanh_ductrong@mamxanh.local',
        'city'      => 'Đức Trọng, Lâm Đồng',
        'farm_name' => 'Vườn Thủy canh NFT Liên Nghĩa',
        'area_m2'   => 2800,
        'main_crop' => 'Cải kale, Xà lách Lolo & Rau chân vịt',
        'region'    => 'Lâm Đồng',
        'gps_lat'   => '11.735400',
        'gps_lng'   => '108.374500',
        'cert_std'  => 'VietGAP',
        'phone'     => '0912300103',
        'courses_quiz' => ['THUYCANH01' => 11, 'IOTNHAMANG01' => 10],
        'batch'     => ['product_name' => 'Cải Kale Thủy canh Hồi lưu NFT', 'crop_variety' => 'Curly Kale Đan Mạch', 'yield' => 1300],
    ],
    [
        'username'  => 'nd_atiso_lamha',
        'firstname' => 'Vũ Thị',
        'lastname'  => 'Hồng Nhung',
        'email'     => 'nd_atiso_lamha@mamxanh.local',
        'city'      => 'Lâm Hà, Lâm Đồng',
        'farm_name' => 'Nông trại Dược liệu & Rau Sạch Nam Ban',
        'area_m2'   => 5000,
        'main_crop' => 'Atiso, Phúc bồn tử & Rau ăn quả',
        'region'    => 'Lâm Đồng',
        'gps_lat'   => '11.884200',
        'gps_lng'   => '108.312900',
        'cert_std'  => 'Hữu cơ (Organic)',
        'phone'     => '0912300104',
        'courses_quiz' => ['CHEBIEN01' => 12, 'PHANVISINH01' => 10],
        'batch'     => ['product_name' => 'Búp Atiso Sấy lạnh Đông khô Loại 1', 'crop_variety' => 'Atiso Tím Đà Lạt', 'yield' => 1900],
    ],
    [
        'username'  => 'nd_bo_cumgar',
        'firstname' => 'H\'Hen',
        'lastname'  => 'Êban',
        'email'     => 'nd_bo_cumgar@mamxanh.local',
        'city'      => 'Cư M\'gar, Đắk Lắk',
        'farm_name' => 'Vườn Sầu riêng & Bơ Booth Quảng Tiến',
        'area_m2'   => 12000,
        'main_crop' => 'Sầu riêng Ri6 & Bơ Booth 7',
        'region'    => 'Đắk Lắk',
        'gps_lat'   => '12.811200',
        'gps_lng'   => '108.084300',
        'cert_std'  => 'VietGAP',
        'phone'     => '0987300201',
        'courses_quiz' => ['SAURIENG01' => 11, 'CHEBIEN01' => 9, 'PHANVISINH01' => 12],
        'batch'     => ['product_name' => 'Bơ Booth 7 Chín chậm VietGAP', 'crop_variety' => 'Booth 7 Tuyển chọn', 'yield' => 9400],
    ],
    [
        'username'  => 'nd_caphe_bmt',
        'firstname' => 'Đặng Hữu',
        'lastname'  => 'Phước',
        'email'     => 'nd_caphe_bmt@mamxanh.local',
        'city'      => 'Buôn Ma Thuột, Đắk Lắk',
        'farm_name' => 'Nông trại Cà phê Đặc sản & Hồ tiêu Ea Kao',
        'area_m2'   => 18000,
        'main_crop' => 'Cà phê Robusta Honey & Sầu riêng xen canh',
        'region'    => 'Đắk Lắk',
        'gps_lat'   => '12.612400',
        'gps_lng'   => '108.041900',
        'cert_std'  => '4C / Hữu cơ',
        'phone'     => '0987300202',
        'courses_quiz' => ['PHANVISINH01' => 11, 'CHAMSOC01' => 10, 'SAURIENG01' => 9],
        'batch'     => ['product_name' => 'Cà phê Trái chín Sơ chế Honey Sấy lạnh', 'crop_variety' => 'TR4 Viện Eakmat', 'yield' => 7800],
    ],
    [
        'username'  => 'nd_maca_krongnang',
        'firstname' => 'Ksor',
        'lastname'  => 'Y Tuấn',
        'email'     => 'nd_maca_krongnang@mamxanh.local',
        'city'      => 'Krông Năng, Đắk Lắk',
        'farm_name' => 'HTX Mắc ca & Sầu riêng Phú Lộc',
        'area_m2'   => 14000,
        'main_crop' => 'Mắc ca OC & Sầu riêng Dona',
        'region'    => 'Đắk Lắk',
        'gps_lat'   => '12.934500',
        'gps_lng'   => '108.352100',
        'cert_std'  => 'VietGAP',
        'phone'     => '0987300203',
        'courses_quiz' => ['CHEBIEN01' => 10, 'SAURIENG01' => 10],
        'batch'     => ['product_name' => 'Hạt Mắc ca Sấy lạnh Tách nứt Tự nhiên', 'crop_variety' => 'Macadamia OC & QN1', 'yield' => 5200],
    ],
    [
        'username'  => 'nd_tieu_eahleo',
        'firstname' => 'Bùi Văn',
        'lastname'  => 'Trường',
        'email'     => 'nd_tieu_eahleo@mamxanh.local',
        'city'      => 'Ea H\'leo, Đắk Lắk',
        'farm_name' => 'Trang trại Hồ tiêu Hữu cơ & Chanh dây Ea Nam',
        'area_m2'   => 9500,
        'main_crop' => 'Hồ tiêu hữu cơ & Chanh dây xuất khẩu',
        'region'    => 'Đắk Lắk',
        'gps_lat'   => '13.154200',
        'gps_lng'   => '108.198400',
        'cert_std'  => 'Organic EU',
        'phone'     => '0987300204',
        'courses_quiz' => ['CHAMSOC01' => 7], // 7/12 = 58.3% -> in-progress learner (<70%) for realistic analytics chart!
        'batch'     => null,
    ],
    [
        'username'  => 'nd_buoi_tanphu',
        'firstname' => 'Trương Minh',
        'lastname'  => 'Đức',
        'email'     => 'nd_buoi_tanphu@mamxanh.local',
        'city'      => 'Tân Phú, Đồng Nai',
        'farm_name' => 'Vườn Bưởi Đường Lá Cam & Sầu riêng Phú Thịnh',
        'area_m2'   => 11000,
        'main_crop' => 'Bưởi da xanh & Bưởi đường lá cam',
        'region'    => 'Đồng Nai',
        'gps_lat'   => '11.264300',
        'gps_lng'   => '107.431200',
        'cert_std'  => 'VietGAP',
        'phone'     => '0933300301',
        'courses_quiz' => ['CHAMSOC01' => 11, 'PHANVISINH01' => 9, 'CHEBIEN01' => 10],
        'batch'     => ['product_name' => 'Bưởi Da Xanh Túi bao Sinh học Tân Phú', 'crop_variety' => 'Bưởi Da Xanh Ruột hồng', 'yield' => 12500],
    ],
    [
        'username'  => 'nd_xoai_xuanloc',
        'firstname' => 'Ngô Thị',
        'lastname'  => 'Bích Ngọc',
        'email'     => 'nd_xoai_xuanloc@mamxanh.local',
        'city'      => 'Xuân Lộc, Đồng Nai',
        'farm_name' => 'HTX Xoài Cát Hòa Lộc & Thanh Long Xuân Hưng',
        'area_m2'   => 13500,
        'main_crop' => 'Xoài cát Hòa Lộc & Dưa lưới nhà màng',
        'region'    => 'Đồng Nai',
        'gps_lat'   => '10.912400',
        'gps_lng'   => '107.415600',
        'cert_std'  => 'GlobalGAP',
        'phone'     => '0933300302',
        'courses_quiz' => ['CHEBIEN01' => 11, 'IOTNHAMANG01' => 10],
        'batch'     => ['product_name' => 'Xoài Cát Hòa Lộc Xử lý Hơi nước nóng Xuất khẩu', 'crop_variety' => 'Xoài Cát Hòa Lộc Tuyển', 'yield' => 10800],
    ],
    [
        'username'  => 'nd_dualuoi_dinhquan',
        'firstname' => 'Đỗ Thành',
        'lastname'  => 'Nhân',
        'email'     => 'nd_dualuoi_dinhquan@mamxanh.local',
        'city'      => 'Định Quán, Đồng Nai',
        'farm_name' => 'Nông trại Nhà màng IoT Phú Ngọc',
        'area_m2'   => 4500,
        'main_crop' => 'Dưa lưới Ichiba & Dưa leo baby thủy canh',
        'region'    => 'Đồng Nai',
        'gps_lat'   => '11.184500',
        'gps_lng'   => '107.356200',
        'cert_std'  => 'VietGAP',
        'phone'     => '0933300303',
        'courses_quiz' => ['IOTNHAMANG01' => 11, 'THUYCANH01' => 10, 'CHAMSOC01' => 9],
        'batch'     => ['product_name' => 'Dưa lưới Ichiba Giá thể Tưới nhỏ giọt IoT', 'crop_variety' => 'Ichiba F1 Nhật Bản', 'yield' => 3900],
    ],
    [
        'username'  => 'nd_nam_cammy',
        'firstname' => 'Phan Văn',
        'lastname'  => 'Hậu',
        'email'     => 'nd_nam_cammy@mamxanh.local',
        'city'      => 'Cẩm Mỹ, Đồng Nai',
        'farm_name' => 'Trang trại Tuần hoàn Sinh học & Nấm Sạch Sông Ray',
        'area_m2'   => 6200,
        'main_crop' => 'Nấm mối đen, Phân trùn quế & Rau hữu cơ',
        'region'    => 'Đồng Nai',
        'gps_lat'   => '10.801200',
        'gps_lng'   => '107.284500',
        'cert_std'  => 'Hữu cơ (Organic)',
        'phone'     => '0933300304',
        'courses_quiz' => ['PHANVISINH01' => 8], // 8/12 = 66.7% -> in-progress learner (<70%) for realistic analytics chart!
        'batch'     => null,
    ],
];

// Load all 6 courses indexed by shortname
$coursesbyshort = [];
foreach (['CHAMSOC01', 'CHEBIEN01', 'IOTNHAMANG01', 'THUYCANH01', 'SAURIENG01', 'PHANVISINH01'] as $sn) {
    $c = $DB->get_record('course', ['shortname' => $sn], '*', MUST_EXIST);
    $coursesbyshort[$sn] = $c;
}

$manualenrol = enrol_get_plugin('manual');

// Ensure CBKT is enrolled as editingteacher & canbokythuat in all 6 courses
foreach ($coursesbyshort as $sn => $course) {
    $instances = enrol_get_instances($course->id, true);
    $manualinstance = null;
    foreach ($instances as $inst) {
        if ($inst->enrol === 'manual') {
            $manualinstance = $inst;
            break;
        }
    }
    if (!$manualinstance) {
        $eid = $manualenrol->add_default_instance($course);
        $manualinstance = $DB->get_record('enrol', ['id' => $eid], '*', MUST_EXIST);
    }
    $manualenrol->enrol_user($manualinstance, $cbkt->id, $editingteacherrole->id, time() - 86400 * 30);
    if ($cbktrole) {
        role_assign($cbktrole->id, $cbkt->id, context_course::instance($course->id)->id);
    }
}

/**
 * Helper to execute a genuine Moodle Quiz Engine attempt with exact target correct count out of 12.
 */
function mamxanh_seed_real_quiz_attempt(stdClass $course, int $userid, int $targetcorrect): ?float {
    global $DB;

    $quiz = $DB->get_record('quiz', ['course' => $course->id]);
    if (!$quiz) {
        return null;
    }
    $existing = $DB->get_records('quiz_attempts', ['quiz' => $quiz->id, 'userid' => $userid, 'state' => 'finished']);
    foreach ($existing as $ex) {
        if ((float)$ex->sumgrades <= 0.001 && $targetcorrect > 0) {
            quiz_delete_attempt($ex, $quiz);
            unset($existing[$ex->id]);
        }
    }
    if (!empty($existing)) {
        $first = reset($existing);
        return round(((float)$first->sumgrades / (float)$quiz->sumgrades) * 100.0, 1);
    }

    $cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id, false, MUST_EXIST);
    $quizobj = quiz_settings::create($quiz->id, $userid);

    $timenow = time() - rand(3600, 86400 * 12);
    $attempt = quiz_prepare_and_start_new_attempt($quizobj, 1, null, false, [], [], $userid);
    $attemptobj = quiz_attempt::create($attempt->id);

    $slots = $attemptobj->get_slots();
    $postdata = [];
    $correctgiven = 0;

    foreach ($slots as $slot) {
        $qa = $attemptobj->get_question_attempt($slot);
        $q = $qa->get_question();
        $correctresp = $q->get_correct_response();
        $chosenidx = '0';
        if ($correctgiven < $targetcorrect && is_array($correctresp) && isset($correctresp['answer'])) {
            $chosenidx = (string)$correctresp['answer'];
            $correctgiven++;
        } else {
            // Pick a wrong index intentionally
            $order = method_exists($q, 'get_order') ? $q->get_order($qa) : [];
            if (!empty($order) && isset($correctresp['answer'])) {
                foreach (array_keys($order) as $choiceidx) {
                    if ((string)$choiceidx !== (string)$correctresp['answer']) {
                        $chosenidx = (string)$choiceidx;
                        break;
                    }
                }
            }
        }
        $postdata[$qa->get_qt_field_name('answer')] = $chosenidx;
        $postdata[$qa->get_control_field_name('sequencecheck')] = (string)$qa->get_sequence_check_count();
    }

    $attemptobj->process_submitted_actions($timenow + 300, false, $postdata);
    $attemptobj = quiz_attempt::create($attempt->id);
    $attemptobj->process_finish($timenow + 480, false, $timenow + 480, true);

    $finished = $DB->get_record('quiz_attempts', ['id' => $attempt->id], '*', MUST_EXIST);
    return round(((float)$finished->sumgrades / (float)$quiz->sumgrades) * 100.0, 1);
}

$totalquizattempts = 0;
$totalrubrics = 0;
$totalbatches = 0;
$totalcompletions = 0;

foreach ($farmerdefs as $idx => $fdef) {
    $u = $DB->get_record('user', ['username' => $fdef['username'], 'mnethostid' => $CFG->mnet_localhost_id]);
    if (!$u) {
        $newu = (object)[
            'username'    => $fdef['username'],
            'password'    => 'MX!Rau2026',
            'firstname'   => $fdef['firstname'],
            'lastname'    => $fdef['lastname'],
            'email'       => $fdef['email'],
            'city'        => $fdef['city'],
            'country'     => 'VN',
            'lang'        => 'vi',
            'confirmed'   => 1,
            'mnethostid'  => $CFG->mnet_localhost_id,
            'timecreated' => time() - 86400 * (25 - $idx),
        ];
        $uid = user_create_user($newu, true, false);
        $u = $DB->get_record('user', ['id' => $uid], '*', MUST_EXIST);
    } else {
        $uid = (int)$u->id;
    }

    // Save Farmer Profile + Sync Regional Cohort
    local_farmerprofile_save_profile($uid, [
        'farm_name'     => $fdef['farm_name'],
        'area_m2'       => $fdef['area_m2'],
        'main_crop'     => $fdef['main_crop'],
        'region'        => $fdef['region'],
        'gps_lat'       => $fdef['gps_lat'],
        'gps_lng'       => $fdef['gps_lng'],
        'cert_standard' => $fdef['cert_std'],
        'phone'         => $fdef['phone'],
    ]);

    // Enrol farmer into all 6 courses as student
    foreach ($coursesbyshort as $sn => $course) {
        $instances = enrol_get_instances($course->id, true);
        foreach ($instances as $inst) {
            if ($inst->enrol === 'manual') {
                $manualenrol->enrol_user($inst, $uid, $studentrole->id, time() - 86400 * 20);
                break;
            }
        }
    }

    // For nd_rau_lamdong, pre-complete Section 1-3 Page lessons in CHEBIEN01 (leaving Quiz & FieldPractice live for Stage 6 E2E!)
    if ($fdef['username'] === 'nd_rau_lamdong') {
        $chebien = $coursesbyshort['CHEBIEN01'];
        $comp = new completion_info($chebien);
        $modinfo = get_fast_modinfo($chebien);
        foreach ($modinfo->get_cms() as $cm) {
            if ($cm->modname === 'page' && $comp->is_enabled($cm)) {
                $comp->update_state($cm, COMPLETION_COMPLETE, $uid);
                $totalcompletions++;
            }
        }
    }

    // Seed Course Completions + Genuine Quiz Attempts + FieldPractice Rubric Grades
    foreach ($fdef['courses_quiz'] as $sn => $correctcnt) {
        $course = $coursesbyshort[$sn];
        $comp = new completion_info($course);
        $modinfo = get_fast_modinfo($course);

        // Complete HTML5 Page modules in this course
        foreach ($modinfo->get_cms() as $cm) {
            if ($cm->modname === 'page' && $comp->is_enabled($cm)) {
                $comp->update_state($cm, COMPLETION_COMPLETE, $uid);
                $totalcompletions++;
            }
        }

        // Execute genuine Moodle Quiz attempt
        $pct = mamxanh_seed_real_quiz_attempt($course, $uid, $correctcnt);
        if ($pct !== null) {
            $totalquizattempts++;
        }

        // Seed 4-Criteria Rubric Grade in mod_fieldpractice (for passing courses)
        if ($correctcnt >= 9) {
            $fp = $DB->get_record('fieldpractice', ['course' => $course->id]);
            if ($fp && !$DB->record_exists('fieldpractice_sub', ['fieldpracticeid' => $fp->id, 'userid' => $uid])) {
                $c1 = min(25, 20 + ($idx % 6));
                $c2 = min(25, 21 + (($idx + 2) % 5));
                $c3 = min(25, 22 + (($idx + 1) % 4));
                $c4 = min(25, 20 + (($idx + 3) % 6));
                fieldpractice_save_rubric_grade(
                    $fp,
                    $uid,
                    $c1,
                    $c2,
                    $c3,
                    $c4,
                    'Nông hộ thực hành chuẩn quy trình kỹ thuật hiện trường, đo pH/EC chính xác và tuân thủ tốt an toàn sinh học VietGAP.',
                    (int)$cbkt->id
                );
                $totalrubrics++;
            }
        }
    }

    // Seed VietGAP Production Batch + Cultivation Diary Logs (including Web Speech Voice Log)
    if (!empty($fdef['batch']) && !$DB->record_exists('local_agriops_batch', ['userid' => $uid])) {
        $binfo = $fdef['batch'];
        $batch = local_agriops_create_batch($uid, [
            'product_name'      => $binfo['product_name'],
            'crop_variety'      => $binfo['crop_variety'],
            'region'            => $fdef['region'],
            'area_m2'           => $fdef['area_m2'],
            'expected_yield_kg' => $binfo['yield'],
            'vietgap_status'    => 'certified',
        ]);
        $totalbatches++;

        local_agriops_add_log($uid, (int)$batch->id, [
            'activity_type' => 'watering_fert',
            'notes'         => 'Châm phân nhỏ giọt sáng sớm theo công thức Venturi A+B, kiểm tra đầu nhỏ giọt đều 100%, lá xanh dày khỏe.',
            'ph_val'        => '6.1',
            'ec_val'        => '1.8',
            'phi_days'      => 0,
            'voice_input'   => 1,
            'log_time'      => time() - 86400 * 5,
        ]);
        local_agriops_add_log($uid, (int)$batch->id, [
            'activity_type' => 'bio_ipm',
            'notes'         => 'Treo bẫy dính màu vàng/xanh kiểm soát bọ trĩ và tưới nấm đối kháng Trichoderma phòng thối rễ sinh học.',
            'ph_val'        => '6.2',
            'ec_val'        => '1.7',
            'phi_days'      => 0,
            'voice_input'   => 0,
            'log_time'      => time() - 86400 * 2,
        ]);
    }
}

$certcount = $DB->count_records('local_mamxanhcert', ['status' => 'valid']);
$badgeissuedcount = $DB->count_records('badge_issued');
$gradeitemscount = $DB->count_records_select('grade_grades', 'finalgrade IS NOT NULL');

echo " -> [OK] Active Farmers Seeded: " . count($farmerdefs) . " across Lâm Đồng, Đắk Lắk, Đồng Nai\n";
echo " -> [OK] Activity Completions Recorded: {$totalcompletions}\n";
echo " -> [OK] Real Moodle Quiz Attempts Graded: {$totalquizattempts}\n";
echo " -> [OK] Field Practice 4-Criteria Rubrics Graded: {$totalrubrics}\n";
echo " -> [OK] Gradebook Populated Entries (mdl_grade_grades): {$gradeitemscount}\n";
echo " -> [OK] HMAC-SHA256 QR Certificates Auto-Issued: {$certcount}\n";
echo " -> [OK] Digital Course Badges Auto-Issued: {$badgeissuedcount}\n";
echo " -> [OK] VietGAP Traceable Batches Created: " . $DB->count_records('local_agriops_batch') . "\n";
echo "=== [Stage 5] Simulation Data Seeding Completed Successfully ===\n";
