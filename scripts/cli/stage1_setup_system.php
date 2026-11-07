<?php
// ===================================================================
// Mầm Xanh LMS 2.0 - Stage 1 Post-Install System, Lang & Redis MUC Setup
// ===================================================================
define('CLI_SCRIPT', true);

require_once('/var/www/html/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filelib.php');

echo "=== [Stage 1] Configuring Language Packs, Core Settings & Redis Application Cache ===\n";

// 1. Ensure Vietnamese (vi) language pack is installed in dataroot/lang/vi
$langdir = $CFG->dataroot . '/lang';
make_writable_directory($langdir);

if (!is_dir($langdir . '/vi')) {
    if (file_exists('/opt/moodle-lang/vi.zip')) {
        echo " -> Extracting pre-cached Vietnamese language pack...\n";
        $packer = get_file_packer('application/zip');
        $packer->extract_to_pathname('/opt/moodle-lang/vi.zip', $langdir);
    } else {
        echo " -> Downloading Vietnamese language pack via Moodle tool_langimport...\n";
        try {
            $controller = new \tool_langimport\controller();
            $controller->install_languagepacks('vi');
        } catch (\Throwable $e) {
            echo " [WARN] tool_langimport fallback warning: " . $e->getMessage() . "\n";
        }
    }
}

if (is_dir($langdir . '/vi')) {
    echo " -> [OK] Language pack 'vi' is ready at {$langdir}/vi\n";
}

// 2. Set Vietnamese (vi) as default language alongside English (en)
set_config('lang', 'vi');
set_config('langmenu', 1);
set_config('langlist', 'vi,en');
set_config('autolang', 0); // Keep 'vi' deterministic unless switched via ?lang=
set_config('country', 'VN');
set_config('timezone', 'Asia/Ho_Chi_Minh');
set_config('forcetimezone', 'Asia/Ho_Chi_Minh');

// 3. Enable Completion, Badges, Mobile/UX settings, and Frontpage identity
set_config('enablecompletion', 1);
set_config('enablebadges', 1);
set_config('defaulthomepage', 0);
set_config('Frontpage', '');
set_config('forcelogin', 0); // Public QR verification pages must remain accessible without forced global redirect
set_config('registerauth', '');
set_config('guestloginbutton', 0); // Hide unnecessary guest button on login page

// Update Site Fullname & Shortname (Bilingual support via mlang or clean Vietnamese title)
global $DB;
$site = get_site();
if ($site) {
    $site->fullname  = 'Hệ thống Tập huấn Kỹ thuật Canh tác Nông nghiệp Công nghệ cao - Mầm Xanh LMS 2.0';
    $site->shortname = 'Mầm Xanh LMS 2.0';
    $site->summary   = 'Nền tảng đào tạo số hóa kỹ thuật nông nghiệp công nghệ cao, VietGAP, IoT nhà màng và truy xuất nguồn gốc nông sản.';
    $DB->update_record('course', $site);
}

// 4. Configure Redis Application Cache in Moodle Universal Cache (MUC)
try {
    $writer = \cache_config_writer::instance();
    $stores = $writer->get_all_stores();
    $storename = 'mamxanh_redis_appcache';

    if (!isset($stores[$storename])) {
        $redisconfig = [
            'server'      => (getenv('REDIS_HOST') ?: 'moodle_redis') . ':' . (getenv('REDIS_PORT') ?: '6379'),
            'prefix'      => 'mx2_muc_',
            'password'    => '',
            'serializer'  => defined('Redis::SERIALIZER_PHP') ? \Redis::SERIALIZER_PHP : 1,
            'compressor'  => 0,
        ];
        $writer->add_store_instance($storename, 'redis', $redisconfig);
        echo " -> [OK] Registered Redis MUC store '{$storename}'\n";
    }

    // Set Redis as default store for Application caches
    $writer->set_mode_mappings([
        \cache_store::MODE_APPLICATION => [$storename],
        \cache_store::MODE_SESSION     => ['default_session'],
        \cache_store::MODE_REQUEST     => ['default_request'],
    ]);
    echo " -> [OK] Mapped Moodle Application Cache to Redis 7 in RAM\n";
} catch (\Throwable $e) {
    echo " -> [INFO] MUC Redis configuration note: " . $e->getMessage() . "\n";
}

// 5. Generate crisp 192x192 & 512x512 PNG icons for PWA installability
foreach ([192, 512] as $size) {
    $pngpath = "/var/www/html/pwa-icons/icon-{$size}.png";
    if (!file_exists($pngpath) && function_exists('imagecreatetruecolor')) {
        $im = imagecreatetruecolor($size, $size);
        imagesavealpha($im, true);
        $bg = imagecolorallocate($im, 27, 67, 50); // #1B4332
        $accent = imagecolorallocate($im, 82, 183, 136); // #52B788
        $light = imagecolorallocate($im, 216, 243, 220); // #D8F3DC
        imagefilledrectangle($im, 0, 0, $size, $size, $bg);
        imagefilledellipse($im, (int)($size * 0.5), (int)($size * 0.5), (int)($size * 0.72), (int)($size * 0.72), $accent);
        imagefilledellipse($im, (int)($size * 0.5), (int)($size * 0.5), (int)($size * 0.42), (int)($size * 0.42), $light);
        @imagepng($im, $pngpath);
        imagedestroy($im);
    }
}

// 6. Mark installation complete so moodle_cron starts running cron.php every 60s
@file_put_contents('/var/moodledata/.installed', date('c'));

purge_all_caches();
echo "=== [Stage 1] System Setup Completed Successfully ===\n";
