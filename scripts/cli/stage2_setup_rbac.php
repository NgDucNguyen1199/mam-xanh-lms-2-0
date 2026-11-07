<?php
// ===================================================================
// Mầm Xanh LMS 2.0 - Stage 2: 3-Tier RBAC, Core Users, Cohorts & Theme
// ===================================================================
define('CLI_SCRIPT', true);

require_once('/var/www/html/config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/local/farmerprofile/lib.php');

echo "=== [Stage 2] Configuring 3-Tier RBAC, Core Users, Cohorts & Child Theme ===\n";

$syscontext = context_system::instance();

// 1. Update Admin profile cleanly
$admin = $DB->get_record('user', ['username' => 'admin']);
if ($admin) {
    $admin->firstname = 'Quản trị viên';
    $admin->lastname  = 'Mầm Xanh';
    $admin->email     = 'admin@mamxanh.vn';
    $admin->city      = 'Đà Lạt';
    $admin->country   = 'VN';
    $admin->lang      = 'vi';
    update_internal_user_password($admin, 'Admin!2026MX');
    $DB->update_record('user', $admin);
    echo " -> [OK] Updated Admin account (admin / Admin!2026MX)\n";
}

// 2. Create or update Role 'canbokythuat'
$role = $DB->get_record('role', ['shortname' => 'canbokythuat']);
if (!$role) {
    $roleid = create_role(
        'Cán bộ Kỹ thuật Nông nghiệp',
        'canbokythuat',
        'Biên soạn học liệu số nông nghiệp, chấm điểm thực hành vườn theo Rubric, kiểm định VietGAP và xuất báo cáo CSV.',
        'editingteacher'
    );
    reset_role_capabilities($roleid);
    echo " -> [OK] Created custom role 'canbokythuat' (ID={$roleid})\n";
} else {
    $roleid = (int)$role->id;
    echo " -> [OK] Found existing custom role 'canbokythuat' (ID={$roleid})\n";
}

set_role_contextlevels($roleid, [CONTEXT_SYSTEM, CONTEXT_COURSECAT, CONTEXT_COURSE, CONTEXT_MODULE]);

$cbktcaps = [
    'local/mamxanhapp:manage',
    'local/mamxanhapp:viewfarmerdashboard',
    'local/farmerprofile:viewall',
    'local/farmerprofile:editown',
    'local/agriops:audit',
    'local/agriops:manageown',
    'local/mamxanhcert:manage',
    'local/mamxanhcert:viewown',
    'mod/fieldpractice:addinstance',
    'mod/fieldpractice:view',
    'mod/fieldpractice:grade',
    'moodle/course:create',
    'moodle/course:update',
    'moodle/course:manageactivities',
    'moodle/course:view',
    'moodle/course:viewparticipants',
    'moodle/grade:viewall',
    'moodle/grade:edit',
    'moodle/cohort:view',
    'moodle/backup:backupcourse',
    'mod/quiz:viewreports',
    'mod/quiz:grade',
];

foreach ($cbktcaps as $cap) {
    if ($DB->record_exists('capabilities', ['name' => $cap])) {
        assign_capability($cap, CAP_ALLOW, $roleid, $syscontext->id, true);
    }
}

// Ensure Student/Authenticated User CANNOT access manage or grade capabilities
foreach (['student', 'user'] as $stdshort) {
    $r = $DB->get_record('role', ['shortname' => $stdshort]);
    if ($r) {
        foreach (['local/mamxanhapp:manage', 'mod/fieldpractice:grade', 'local/mamxanhcert:manage', 'local/farmerprofile:viewall'] as $denycap) {
            if ($DB->record_exists('capabilities', ['name' => $denycap])) {
                assign_capability($denycap, CAP_PREVENT, $r->id, $syscontext->id, true);
            }
        }
    }
}

/**
 * Helper to create or update an internal Moodle user cleanly.
 */
function mamxanh_upsert_user(string $username, string $password, string $firstname, string $lastname, string $email, string $city): stdClass {
    global $CFG, $DB;

    $u = $DB->get_record('user', ['username' => $username, 'deleted' => 0]);
    if (!$u) {
        $newu = (object)[
            'auth'        => 'manual',
            'confirmed'   => 1,
            'mnethostid'  => $CFG->mnet_localhost_id,
            'username'    => $username,
            'password'    => $password,
            'firstname'   => $firstname,
            'lastname'    => $lastname,
            'email'       => $email,
            'city'        => $city,
            'country'     => 'VN',
            'lang'        => 'vi',
            'timezone'    => 'Asia/Ho_Chi_Minh',
            'timecreated' => time(),
        ];
        $newu->id = user_create_user($newu, true, false);
        return $DB->get_record('user', ['id' => $newu->id]);
    } else {
        $u->firstname = $firstname;
        $u->lastname  = $lastname;
        $u->email     = $email;
        $u->city      = $city;
        $u->confirmed = 1;
        $u->suspended = 0;
        $DB->update_record('user', $u);
        update_internal_user_password($u, $password);
        return $u;
    }
}

// 3. Create Cán bộ Kỹ thuật account: cbkt_pilot / MX!Canbo2026
$cbkt = mamxanh_upsert_user(
    'cbkt_pilot',
    'MX!Canbo2026',
    'Kỹ sư Trần Minh',
    'Tuấn (CBKT)',
    'cbkt_pilot@mamxanh.vn',
    'Đà Lạt'
);
role_assign($roleid, $cbkt->id, $syscontext->id);
echo " -> [OK] Created & assigned CBKT user (cbkt_pilot / MX!Canbo2026 -> role 'canbokythuat')\n";

// 4. Create Core Farmer Accounts (Pass: MX!Rau2026) & Sync Profiles + Regional Cohorts
$corefarmers = [
    [
        'username'  => 'nd_rau_lamdong',
        'firstname' => 'Nguyễn Văn',
        'lastname'  => 'Hùng',
        'email'     => 'nd_rau_lamdong@mamxanh.vn',
        'city'      => 'Đà Lạt',
        'farm_name' => 'Nông trại Rau Thủy canh Hùng Phát Đà Lạt',
        'area_m2'   => 3500,
        'main_crop' => 'Rau ăn lá thủy canh NFT & Ớt chuông',
        'region'    => 'Lâm Đồng',
        'gps_lat'   => '11.946520',
        'gps_lng'   => '108.441930',
        'standard'  => 'VietGAP',
    ],
    [
        'username'  => 'nd_saurieng_daklak',
        'firstname' => 'Lê Thị Mai',
        'lastname'  => 'Hương',
        'email'     => 'nd_saurieng_daklak@mamxanh.vn',
        'city'      => 'Krông Pắc',
        'farm_name' => 'Vườn Sầu riêng Xuất khẩu Mai Hương Krông Pắc',
        'area_m2'   => 12000,
        'main_crop' => 'Sầu riêng Dona & Ri6 VietGAP',
        'region'    => 'Đắk Lắk',
        'gps_lat'   => '12.684210',
        'gps_lng'   => '108.214500',
        'standard'  => 'VietGAP',
    ],
    [
        'username'  => 'nd_vanan',
        'firstname' => 'Phạm Văn',
        'lastname'  => 'An',
        'email'     => 'nd_vanan@mamxanh.vn',
        'city'      => 'Long Khánh',
        'farm_name' => 'Nông hộ Dưa lưới Nhà màng Văn An Long Khánh',
        'area_m2'   => 4500,
        'main_crop' => 'Dưa lưới Nhà màng IoT & Bưởi da xanh',
        'region'    => 'Đồng Nai',
        'gps_lat'   => '10.938150',
        'gps_lng'   => '107.242800',
        'standard'  => 'GlobalGAP',
    ],
];

foreach ($corefarmers as $f) {
    $u = mamxanh_upsert_user($f['username'], 'MX!Rau2026', $f['firstname'], $f['lastname'], $f['email'], $f['city']);
    local_farmerprofile_save_profile((int)$u->id, [
        'farm_name'     => $f['farm_name'],
        'area_m2'       => $f['area_m2'],
        'main_crop'     => $f['main_crop'],
        'region'        => $f['region'],
        'gps_lat'       => $f['gps_lat'],
        'gps_lng'       => $f['gps_lng'],
        'cert_standard' => $f['standard'],
        'phone'         => '0909888777',
    ]);
    echo " -> [OK] Created Farmer '{$f['username']}' & synced Cohort ({$f['region']})\n";
}

// 5. Activate AgriTrain Child Theme
set_config('theme', 'agritrain_child');
set_config('allowuserthemes', 0);
echo " -> [OK] Activated Child Theme 'theme_agritrain_child'\n";

$syscontext->mark_dirty();
purge_all_caches();
echo "=== [Stage 2 & 3 Base] RBAC & Child Theme Activated Successfully ===\n";
