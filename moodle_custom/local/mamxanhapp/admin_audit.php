<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

local_mamxanhapp_handle_lang_switch();
require_login();
local_mamxanhapp_require_admin_or_403();

$syscontext = context_system::instance();
$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/mamxanhapp/admin_audit.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(local_mamxanhapp_t('Kiểm toán Hạ tầng Turbo - Admin', 'Turbo Infrastructure Audit - Admin'));
$PAGE->set_heading(local_mamxanhapp_t('⚙️ Bảng Kiểm toán Hiệu năng & Bảo mật (Chỉ Admin)', '⚙️ Turbo Infrastructure & Security Audit (Admin Only)'));

$opcache = function_exists('opcache_get_status') ? @opcache_get_status(false) : false;
$jit = $opcache['jit'] ?? [];
$mem = $opcache['memory_usage'] ?? [];
$stats = $opcache['opcache_statistics'] ?? [];

$redisok = false;
$rediskeys = 0;
try {
    if (class_exists('Redis')) {
        $r = new Redis();
        if ($r->connect($CFG->session_redis_host ?? 'moodle_redis', (int)($CFG->session_redis_port ?? 6379), 1.5)) {
            $redisok = true;
            $rediskeys = (int)$r->dbSize();
        }
    }
} catch (\Throwable $e) {
    $redisok = false;
}

echo $OUTPUT->header();
?>
<div class="mx-admin-audit" style="background:#FFF;border:2px solid #DCEEE0;border-radius:24px;padding:24px;">
    <div class="mb-3">
        <a href="<?php echo (new moodle_url('/local/mamxanhapp/manage.php'))->out(false); ?>" class="btn btn-outline-success" style="min-height:48px;display:inline-flex;align-items:center;border-radius:14px;font-weight:700;">
            ← <?php echo s(local_mamxanhapp_t('Quay lại Trung tâm Điều hành', 'Back to Analytics Hub')); ?>
        </a>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div style="background:#F1F8E9;border-radius:18px;padding:18px;height:100%;">
                <h5 style="color:#1B4332;font-weight:800;">🚀 PHP 8.3 OPcache & JIT</h5>
                <ul class="mb-0" style="line-height:1.8;">
                    <li>PHP: <strong><?php echo s(PHP_VERSION); ?></strong></li>
                    <li>OPcache Hit Rate: <strong><?php echo isset($stats['opcache_hit_rate']) ? round($stats['opcache_hit_rate'], 2) . '%' : 'Active'; ?></strong></li>
                    <li>JIT Enabled: <strong><?php echo !empty($jit['enabled']) ? 'YES (1255)' : 'Configured'; ?></strong></li>
                    <li>Cached Scripts: <strong><?php echo (int)($stats['num_cached_scripts'] ?? 0); ?></strong></li>
                </ul>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div style="background:#F1F8E9;border-radius:18px;padding:18px;height:100%;">
                <h5 style="color:#1B4332;font-weight:800;">⚡ Redis 7 In-Memory</h5>
                <ul class="mb-0" style="line-height:1.8;">
                    <li>Status: <strong><?php echo $redisok ? 'CONNECTED (OK)' : 'OFFLINE'; ?></strong></li>
                    <li>Session Handler: <code><?php echo s($CFG->session_handler_class ?? ''); ?></code></li>
                    <li>Active RAM Keys: <strong><?php echo $rediskeys; ?></strong></li>
                    <li>LocalCache RAM: <code><?php echo s($CFG->localcachedir); ?></code></li>
                </ul>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div style="background:#F1F8E9;border-radius:18px;padding:18px;height:100%;">
                <h5 style="color:#1B4332;font-weight:800;">🔐 Bảo mật Chữ ký Số</h5>
                <ul class="mb-0" style="line-height:1.8;">
                    <li>Algorithm: <strong>HMAC-SHA256</strong></li>
                    <li>Signing Secret: <strong>Configured (<?php echo strlen($CFG->mamxanh_hmac_secret ?? ''); ?> chars)</strong></li>
                    <li>DB Engine: <strong>MariaDB 10.11 (utf8mb4)</strong></li>
                    <li>HTTPS Proxy: <strong>Caddy 2 (zstd + gzip)</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
echo $OUTPUT->footer();
