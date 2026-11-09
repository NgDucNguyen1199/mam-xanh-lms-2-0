<?php
// ===================================================================
// Mầm Xanh LMS 2.0 - High-Performance Production Configuration
// ===================================================================

unset($CFG);
global $CFG;
$CFG = new stdClass();

// 1. MariaDB 10.11 Database Configuration
$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = getenv('MOODLE_DB_HOST') ?: 'moodle_db';
$CFG->dbname    = getenv('MOODLE_DB_NAME') ?: 'moodle_mamxanh';
$CFG->dbuser    = getenv('MOODLE_DB_USER') ?: 'moodle_user';
$CFG->dbpass    = getenv('MOODLE_DB_PASS') ?: 'MX!DbPass2026';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = [
    'dbpersist'   => false,
    'dbport'      => 3306,
    'dbsocket'    => '',
    'dbcollation' => 'utf8mb4_unicode_ci',
];

// 2. Dual-Mode URL Support: Reverse Proxy HTTPS (:8443) + Standalone Direct HTTP (:8090)
$defaultwwwroot = getenv('MOODLE_WWWROOT') ?: 'https://moodle.lvh.me:8443';
if (PHP_SAPI !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
    $rawhost = strtolower(trim((string)$_SERVER['HTTP_HOST']));
    if (preg_match('/^(moodle\.lvh\.me|localhost|127\.0\.0\.1)(:\d{1,5})?$/', $rawhost)) {
        $ishttps = ((!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'));
        $CFG->wwwroot  = ($ishttps ? 'https://' : 'http://') . $rawhost;
        $CFG->sslproxy = $ishttps && empty($_SERVER['HTTPS']);
        if (!$ishttps) {
            $CFG->cookiesecure  = false;
            $CFG->sessioncookie = 'directhttp';
        }
    } else {
        $CFG->wwwroot  = $defaultwwwroot;
        $CFG->sslproxy = str_starts_with($CFG->wwwroot, 'https://');
    }
} else {
    $CFG->wwwroot  = $defaultwwwroot;
    $CFG->sslproxy = str_starts_with($CFG->wwwroot, 'https://');
}

// 3. Zero-Bottleneck Linux Named Volume & RAM (/tmp) I/O Isolation
$CFG->dataroot      = '/var/moodledata';
$CFG->cachedir      = '/var/moodledata/cache';
$CFG->localcachedir = '/tmp/moodle_localcache';
$CFG->tempdir       = '/tmp/moodle_temp';

$CFG->directorypermissions = 0777;
$CFG->filepermissions      = 0666;
$CFG->admin = 'admin';

// 4. Redis 7 In-Memory Session Handler
$CFG->session_handler_class              = '\core\session\redis';
$CFG->session_redis_host                 = getenv('REDIS_HOST') ?: 'moodle_redis';
$CFG->session_redis_port                 = (int)(getenv('REDIS_PORT') ?: 6379);
$CFG->session_redis_database             = 0;
$CFG->session_redis_prefix               = 'mx2_sess_';
$CFG->session_redis_acquire_lock_timeout = 120;
$CFG->session_redis_lock_expire          = 7200;

// 5. Performance & AgTech Custom Keys
$CFG->yuicomboloading     = 1;
$CFG->cachejs             = true;
$CFG->langstringcache     = true;
// Version-controlled Vietnamese string overrides (see theme/agritrain_child/langlocal/vi_local/).
$CFG->langlocalroot       = __DIR__ . '/theme/agritrain_child/langlocal';
$CFG->slasharguments      = 1;
$CFG->noemailever         = true;
$CFG->additionalhtmlhead  = '<link rel="manifest" href="/manifest.json"><meta name="theme-color" content="#1B4332"><link rel="apple-touch-icon" href="/pwa-icons/icon-192.png">';
$CFG->mamxanh_hmac_secret = getenv('MAMXANH_HMAC_SECRET') ?: 'MX2_HMAC_2026_VIETGAP_AGRITECH_DIGITAL_SIGNATURE_KEY';

$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 0;

require_once(__DIR__ . '/lib/setup.php');
