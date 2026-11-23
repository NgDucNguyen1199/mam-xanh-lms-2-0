<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Check whether current active language is English.
 */
function local_mamxanhapp_is_en(): bool {
    global $SESSION;
    $req = optional_param('lang', '', PARAM_ALPHA);
    if ($req === 'en') {
        return true;
    }
    if ($req === 'vi') {
        return false;
    }
    if (isset($SESSION) && !empty($SESSION->lang)) {
        return strpos((string)$SESSION->lang, 'en') === 0;
    }
    return strpos(current_language(), 'en') === 0;
}

/**
 * Bilingual instant translation helper (VI / EN).
 */
function local_mamxanhapp_t(string $vi, string $en): string {
    return local_mamxanhapp_is_en() ? $en : $vi;
}

/**
 * Handle ?lang=vi or ?lang=en sticky session switch cleanly on any custom page.
 */
function local_mamxanhapp_handle_lang_switch(): void {
    global $SESSION;
    $req = optional_param('lang', '', PARAM_ALPHA);
    if ($req === 'vi' || $req === 'en') {
        $SESSION->lang = $req;
    }
}

/**
 * Classify user into 'admin', 'cbkt', or 'farmer'.
 */
function local_mamxanhapp_user_role_type(int $userid = 0): string {
    global $USER;
    if ($userid <= 0) {
        $userid = (int)$USER->id;
    }
    if ($userid <= 0 || isguestuser($userid)) {
        return 'guest';
    }
    if (is_siteadmin($userid)) {
        return 'admin';
    }
    $syscontext = \context_system::instance();
    if (has_capability('local/mamxanhapp:manage', $syscontext, $userid)) {
        return 'cbkt';
    }
    return 'farmer';
}

/**
 * Render an HTTP 403 Forbidden response with bilingual UI and exit cleanly.
 */
function local_mamxanhapp_send_403_and_exit(string $msgvi = '', string $msgen = ''): void {
    global $OUTPUT, $PAGE;
    static $rendering403 = false;

    if (!headers_sent()) {
        header('HTTP/1.1 403 Forbidden');
        http_response_code(403);
    }

    $title = local_mamxanhapp_t('403 - Truy cập bị từ chối', '403 - Access Forbidden');
    $roletype = local_mamxanhapp_user_role_type();
    $homeurl = ($roletype === 'cbkt' || $roletype === 'admin')
        ? new \moodle_url('/local/mamxanhapp/manage.php')
        : new \moodle_url('/local/mamxanhapp/index.php');

    $defaultvi = 'Tài khoản của bạn không có quyền truy cập chức năng này theo quy định phân quyền 3 cấp Mầm Xanh LMS.';
    $defaulten = 'Your account role is not authorized to access this resource under Mam Xanh 3-tier RBAC policy.';
    $desc = local_mamxanhapp_t($msgvi ?: $defaultvi, $msgen ?: $defaulten);
    $btnlabel = local_mamxanhapp_t('← Quay lại Bảng điều khiển của bạn', '← Return to Your Dashboard');

    $cardhtml = '<div class="mx-forbidden-card" style="max-width:640px;margin:2rem auto;background:#FFF5F5;border:3px solid #C62828;border-radius:24px;padding:2rem;text-align:center;">'
        . '<div style="font-size:3.2rem;line-height:1;margin-bottom:0.75rem;">403</div>'
        . '<h2 style="color:#B71C1C;font-weight:800;margin-bottom:0.75rem;">' . s($title) . '</h2>'
        . '<p style="font-size:1.05rem;color:#3E2723;margin-bottom:1.5rem;">' . s($desc) . '</p>'
        . '<a href="' . $homeurl->out(false) . '" class="btn btn-success btn-lg" style="min-height:48px;border-radius:14px;font-weight:700;background:#234E3E;border-color:#234E3E;">' . s($btnlabel) . '</a>'
        . '</div>';

    // Guard against re-entrant calls or calls after page header has already started printing.
    if ($rendering403 || (isset($PAGE) && $PAGE->state >= \moodle_page::STATE_PRINTING_HEADER)) {
        echo $cardhtml;
        exit;
    }
    $rendering403 = true;

    if (!$PAGE->has_set_url()) {
        $PAGE->set_url(new \moodle_url('/local/mamxanhapp/index.php'));
    }
    $PAGE->set_context(\context_system::instance());
    $PAGE->set_pagelayout('base');
    $PAGE->set_title($title);
    $PAGE->set_heading($title);

    $lang = local_mamxanhapp_is_en() ? 'en' : 'vi';
    $brand = 'Mầm Xanh LMS 2.0';
    echo '<!DOCTYPE html><html lang="' . $lang . '"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        . '<title>' . s($title . ' | ' . $brand) . '</title>'
        . '<style>'
        . 'body{margin:0;background:#F9FAF9;color:#1C2522;font-family:"Inter",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;}'
        . '.mx-403-nav{background:#FFFFFF;border-bottom:1px solid #E6ECE9;height:58px;display:flex;align-items:center;justify-content:space-between;padding:0 24px;}'
        . '.mx-403-brand{font-weight:700;font-size:1.05rem;color:#1C2522;text-decoration:none;}'
        . '.mx-403-wrap{max-width:720px;margin:48px auto;padding:0 16px;}'
        . '.btn-403-back{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:10px 24px;border-radius:14px;font-weight:700;background:#234E3E;color:#FFFFFF;text-decoration:none;border:1px solid #234E3E;}'
        . '.btn-403-back:hover{background:#1B3D30;color:#FFFFFF;}'
        . '</style></head><body>'
        . '<header class="mx-403-nav"><a class="mx-403-brand" href="' . $homeurl->out(false) . '">' . s($brand) . '</a></header>'
        . '<main id="region-main" class="mx-403-wrap">'
        . '<div class="mx-forbidden-card" style="max-width:640px;margin:2rem auto;background:#FFF5F5;border:3px solid #C62828;border-radius:24px;padding:2rem;text-align:center;">'
        . '<div style="font-size:3.2rem;line-height:1;margin-bottom:0.75rem;color:#B71C1C;font-weight:800;">403</div>'
        . '<h2 style="color:#B71C1C;font-weight:800;margin-bottom:0.75rem;">' . s($title) . '</h2>'
        . '<p style="font-size:1.05rem;color:#3E2723;margin-bottom:1.5rem;line-height:1.6;">' . s($desc) . '</p>'
        . '<a href="' . $homeurl->out(false) . '" class="btn btn-success btn-lg btn-403-back" style="min-height:48px;border-radius:14px;font-weight:700;background:#234E3E;border-color:#234E3E;">' . s($btnlabel) . '</a>'
        . '</div></main></body></html>';
    exit;
}

/**
 * Enforce capability or respond with HTTP 403 Forbidden.
 */
function local_mamxanhapp_require_capability_or_403(string $capability, \context $context, string $msgvi = '', string $msgen = ''): void {
    if (!is_siteadmin() && !has_capability($capability, $context)) {
        local_mamxanhapp_send_403_and_exit($msgvi, $msgen);
    }
}

/**
 * Enforce Site Admin or respond with HTTP 403 Forbidden.
 */
function local_mamxanhapp_require_admin_or_403(string $msgvi = '', string $msgen = ''): void {
    if (!is_siteadmin()) {
        local_mamxanhapp_send_403_and_exit(
            $msgvi ?: 'Trang Kiểm toán Hạ tầng Hệ thống chỉ dành riêng cho Quản trị viên cấp cao (Admin).',
            $msgen ?: 'System Infrastructure Audit is restricted exclusively to System Administrators.'
        );
    }
}

/**
 * Early Moodle hook invoked inside require_login() before core pages throw 404 nopermissions exceptions.
 */
function local_mamxanhapp_after_require_login(): void {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (isloggedin() && !isguestuser()) {
        $roletype = local_mamxanhapp_user_role_type();
        $iscbktadmin = ($roletype === 'cbkt' || $roletype === 'admin' || is_siteadmin());
        if (!$iscbktadmin && ($script === '/user/index.php' || $script === '/grade/report/grader/index.php')) {
            local_mamxanhapp_send_403_and_exit(
                'Theo quy định bảo mật thông tin học viên, tài khoản Nông hộ không được phép truy cập danh sách thành viên hoặc sổ chấm điểm của khóa học.',
                'Under learner privacy & RBAC policy, Farmer accounts are not permitted to access the course participants list or instructor grader report.'
            );
        }
    }
}

/**
 * Micro-climate weather & agricultural spraying/irrigation advisory data by region.
 */
function local_mamxanhapp_weather_data(string $region = 'Lâm Đồng'): array {
    $en = local_mamxanhapp_is_en();
    $catalog = [
        'Lâm Đồng' => [
            'region'     => $en ? 'Lam Dong Highlands (Da Lat - Don Duong)' : 'Lâm Đồng (Đà Lạt - Đơn Dương)',
            'temp'       => '21°C',
            'humidity'   => '84%',
            'wind'       => '6 km/h',
            'vpd'        => '0.78 kPa',
            'uv'         => '5 (TB / Mod)',
            'rain_prob'  => '35%',
            'status'     => 'warning',
            'badge'      => $en ? 'Morning Mist Alert' : 'Cảnh báo Sương mù Sáng sớm',
            'advice'     => $en
                ? 'High humidity (84%). Open greenhouse roof vents at 07:30, drip irrigate EC 1.6 at 08:30–10:30, avoid late-afternoon foliar spray to prevent Downy Mildew & Rust.'
                : 'Ẩm độ cao (84%). Mở cửa thông gió nhà màng lúc 07:30, tưới nhỏ giọt EC 1.6 khung 08:30–10:30, tuyệt đối không phun ướt lá sau 15:30 để ngừa nấm Sương mai & Gỉ sắt.',
            'window'     => '08:00 – 10:30',
        ],
        'Đắk Lắk' => [
            'region'     => $en ? 'Dak Lak Central Highlands (Krong Pac - Buon Ma Thuot)' : 'Đắk Lắk (Krông Pắc - Buôn Ma Thuột)',
            'temp'       => '29°C',
            'humidity'   => '66%',
            'wind'       => '9 km/h',
            'vpd'        => '1.35 kPa',
            'uv'         => '8 (Cao / High)',
            'rain_prob'  => '15%',
            'status'     => 'optimal',
            'badge'      => $en ? 'Golden Bio-Spray Window' : 'Cửa sổ Vàng Phun Sinh học',
            'advice'     => $en
                ? 'Gentle wind (9 km/h), optimal VPD 1.35 kPa. Ideal window 06:30–09:00 for biological IPM (Trichoderma / Metarhizium) on Durian & Coffee.'
                : 'Gió nhẹ (9 km/h), VPD tối ưu 1.35 kPa. Rất thuận lợi phun chế phẩm sinh học IPM (Trichoderma / Nấm xanh) cho Sầu riêng khung 06:30–09:00 sáng.',
            'window'     => '06:30 – 09:00 & 16:00 – 17:30',
        ],
        'Đồng Nai' => [
            'region'     => $en ? 'Dong Nai Southeast (Long Khanh - Xuan Loc)' : 'Đồng Nai (Long Khánh - Xuân Lộc)',
            'temp'       => '33°C',
            'humidity'   => '58%',
            'wind'       => '12 km/h',
            'vpd'        => '1.85 kPa',
            'uv'         => '10 (Rất cao / Extreme)',
            'rain_prob'  => '10%',
            'status'     => 'hot',
            'badge'      => $en ? 'Midday Heat & High UV' : 'Cảnh báo Nắng gắt & Bốc hơi cao',
            'advice'     => $en
                ? 'High midday heat (33°C, UV 10). Split drip irrigation into 3 short pulses (+15% water volume, lower EC by 0.2 mS/cm) to prevent blossom-end rot & tipburn.'
                : 'Nắng gắt giữa trưa (33°C, UV 10). Chia tưới nhỏ giọt thành 3 chu kỳ ngắn (+15% lượng nước, giảm EC 0.2 mS/cm) để chống cháy chóp lá và thối đít trái thiếu Canxi.',
            'window'     => '06:00 – 08:30',
        ],
    ];

    if (isset($catalog[$region])) {
        return $catalog[$region];
    }
    return $catalog['Lâm Đồng'];
}

/**
 * Extend Moodle global navigation with Mam Xanh AgTech shortcuts.
 */
function local_mamxanhapp_extend_navigation(\global_navigation $nav): void {
    if (!isloggedin() || isguestuser()) {
        return;
    }
    $roletype = local_mamxanhapp_user_role_type();
    if ($roletype === 'cbkt' || $roletype === 'admin') {
        $node = $nav->add(
            local_mamxanhapp_t('Quản lý & Báo cáo CBKT', 'Instructor Analytics'),
            new \moodle_url('/local/mamxanhapp/manage.php'),
            \navigation_node::TYPE_CUSTOM,
            null,
            'mamxanh_manage'
        );
        $node->showinflatnavigation = true;
    }

    $farmernode = $nav->add(
        local_mamxanhapp_t('Bảng điều khiển Nông dân', 'Farmer Dashboard'),
        new \moodle_url('/local/mamxanhapp/index.php'),
        \navigation_node::TYPE_CUSTOM,
        null,
        'mamxanh_farmer'
    );
    $farmernode->showinflatnavigation = true;

    $calcnode = $nav->add(
        local_mamxanhapp_t('Công cụ Pha phân & Thời tiết', 'AgTech Calculator'),
        new \moodle_url('/local/mamxanhapp/calculator.php'),
        \navigation_node::TYPE_CUSTOM,
        null,
        'mamxanh_calc'
    );
    $calcnode->showinflatnavigation = true;
}

/**
 * Non-blocking Cloud sync bridge from Moodle 4.5 LTS to Supabase PostgREST.
 * Fails silently with debug logging if env vars are missing, network is down, or timeout (> 2s) occurs.
 */
function local_mamxanh_sync_to_cloud(string $table, array $payload, string $onconflict = ''): bool {
    global $CFG;

    try {
        $allowedtables = ['mamxanh_certificates', 'agriops_batches', 'agriops_logs'];
        if (!in_array($table, $allowedtables, true) || !function_exists('curl_init')) {
            return false;
        }

        $sburl = trim((string)(getenv('MAMXANH_SUPABASE_URL') ?: ($CFG->mamxanh_supabase_url ?? '')));
        $sbkey = trim((string)(getenv('MAMXANH_SUPABASE_KEY') ?: ($CFG->mamxanh_supabase_key ?? '')));
        if ($sburl === '' || $sbkey === '') {
            return false;
        }

        // Normalize UTF-8 strings cleanly to NFC before JSON encoding.
        $cleanpayload = [];
        foreach ($payload as $k => $v) {
            if (is_string($v) && class_exists('\Normalizer')) {
                $norm = \Normalizer::normalize($v, \Normalizer::FORM_C);
                $cleanpayload[$k] = ($norm !== false) ? $norm : $v;
            } else {
                $cleanpayload[$k] = $v;
            }
        }

        $endpoint = rtrim($sburl, '/') . '/rest/v1/' . rawurlencode($table);
        if ($onconflict !== '') {
            $endpoint .= '?on_conflict=' . rawurlencode($onconflict);
        }

        $body = json_encode($cleanpayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            return false;
        }

        $ch = curl_init($endpoint);
        if ($ch === false) {
            return false;
        }
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_TIMEOUT        => 2,
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . $sbkey,
                'Authorization: Bearer ' . $sbkey,
                'Content-Type: application/json; charset=utf-8',
                'Prefer: resolution=merge-duplicates,return=minimal',
            ],
        ]);
        @curl_exec($ch);
        $errno = curl_errno($ch);
        $httpcode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0 || $httpcode < 200 || $httpcode >= 300) {
            @error_log("local_mamxanh_sync_to_cloud({$table}) skipped/non-2xx (errno={$errno}, http={$httpcode})");
            return false;
        }
        return true;
    } catch (\Throwable $e) {
        @error_log('local_mamxanh_sync_to_cloud exception: ' . $e->getMessage());
        return false;
    }
}
