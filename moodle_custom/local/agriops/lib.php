<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Create a new VietGAP production batch with a unique traceable code.
 */
function local_agriops_create_batch(int $userid, array $data): stdClass {
    global $CFG, $DB;

    $regmap = [
        'Lâm Đồng' => 'LD',
        'Đắk Lắk'  => 'DL',
        'Đồng Nai' => 'DN',
    ];
    $region = $data['region'] ?? 'Lâm Đồng';
    $rcode = $regmap[$region] ?? 'VN';
    $seq = $DB->count_records('local_agriops_batch') + 1;
    $batchcode = sprintf('VG-%s-%s-%04d', $rcode, date('Ym'), $seq);

    while ($DB->record_exists('local_agriops_batch', ['batch_code' => $batchcode])) {
        $seq++;
        $batchcode = sprintf('VG-%s-%s-%04d', $rcode, date('Ym'), $seq);
    }

    $secret = $CFG->mamxanh_hmac_secret ?? 'MX2_SECRET';
    $now = time();
    $qrtoken = substr(hash_hmac('sha256', "{$batchcode}|{$userid}|{$now}", $secret), 0, 32);

    $rec = (object)[
        'userid'            => $userid,
        'batch_code'        => $batchcode,
        'product_name'      => trim((string)($data['product_name'] ?? 'Nông sản VietGAP')),
        'crop_variety'      => trim((string)($data['crop_variety'] ?? 'Giống F1 Công nghệ cao')),
        'region'            => $region,
        'area_m2'           => max(50, (int)($data['area_m2'] ?? 1000)),
        'expected_yield_kg' => max(10, (int)($data['expected_yield_kg'] ?? 800)),
        'vietgap_status'    => $data['vietgap_status'] ?? 'active',
        'qr_token'          => $qrtoken,
        'timecreated'       => $now,
    ];

    $rec->id = $DB->insert_record('local_agriops_batch', $rec);
    return $rec;
}

/**
 * Record a cultivation diary entry on a VietGAP batch.
 */
function local_agriops_add_log(int $userid, int $batchid, array $data): stdClass {
    global $DB;

    $rec = (object)[
        'batchid'       => $batchid,
        'userid'        => $userid,
        'activity_type' => trim((string)($data['activity_type'] ?? 'watering_fert')),
        'notes'         => trim((string)($data['notes'] ?? '')),
        'ph_val'        => trim((string)($data['ph_val'] ?? '6.0')),
        'ec_val'        => trim((string)($data['ec_val'] ?? '1.6')),
        'phi_days'      => max(0, (int)($data['phi_days'] ?? 0)),
        'voice_input'   => !empty($data['voice_input']) ? 1 : 0,
        'log_time'      => !empty($data['log_time']) ? (int)$data['log_time'] : time(),
    ];

    $rec->id = $DB->insert_record('local_agriops_log', $rec);
    return $rec;
}

/**
 * Ensure user has at least 1 VietGAP batch and sample cultivation logs.
 */
function local_agriops_ensure_seed_data(int $userid): void {
    global $DB;
    if ($userid <= 0) {
        return;
    }
    $batches = $DB->get_records('local_agriops_batch', ['userid' => $userid], 'id ASC');
    if (empty($batches)) {
        $batch = local_agriops_create_batch($userid, [
            'product_name'      => 'Dâu tây & Rau ăn quả Nhà màng Đà Lạt',
            'crop_variety'      => 'Giống Cấy mô Sạch bệnh F1',
            'region'            => 'Lâm Đồng',
            'area_m2'           => 2500,
            'expected_yield_kg' => 4200,
            'vietgap_status'    => 'VietGAP TCVN 11892-1:2017',
        ]);
        local_agriops_add_log($userid, (int)$batch->id, [
            'activity_type' => 'env_check',
            'notes'         => 'Kiểm tra giá thể xơ dừa đã xử lý tanin, hiệu chuẩn bút đo pH 5.8 và EC 1.4 mS/cm.',
            'ph_val'        => '5.8',
            'ec_val'        => '1.4',
            'phi_days'      => 0,
            'log_time'      => mktime(7, 30, 0, 11, 5, 2026),
        ]);
        local_agriops_add_log($userid, (int)$batch->id, [
            'activity_type' => 'watering_fert',
            'notes'         => 'Vận hành tưới nhỏ giọt bù áp 6 chu kỳ/ngày kết hợp châm phân tự động A-B giai đoạn nuôi quả.',
            'ph_val'        => '6.0',
            'ec_val'        => '1.8',
            'phi_days'      => 0,
            'log_time'      => mktime(8, 15, 0, 11, 9, 2026),
        ]);
        local_agriops_add_log($userid, (int)$batch->id, [
            'activity_type' => 'ipm_bio',
            'notes'         => 'Tưới gốc nấm đối kháng Trichoderma harzianum (10^8 CFU/g) và treo bẫy dính sinh học phòng bọ trĩ.',
            'ph_val'        => '6.0',
            'ec_val'        => '1.7',
            'phi_days'      => 0,
            'log_time'      => mktime(16, 0, 0, 11, 12, 2026),
        ]);
    }
}

/**
 * Get all VietGAP batches for a user (fallback to all batches if none owned).
 */
function local_agriops_get_user_batches(int $userid): array {
    global $DB;
    $batches = $DB->get_records('local_agriops_batch', ['userid' => $userid], 'id DESC');
    if (empty($batches)) {
        $batches = $DB->get_records('local_agriops_batch', null, 'id DESC');
    }
    return $batches;
}

/**
 * Get all cultivation log entries for a batch.
 */
function local_agriops_get_batch_logs(int $batchid): array {
    global $DB;
    return $DB->get_records('local_agriops_log', ['batchid' => $batchid], 'log_time DESC');
}

/**
 * Translate activity_type code into human-readable bilingual label.
 */
function local_agriops_translate_activity_type(string $code): string {
    $en = function_exists('local_mamxanhapp_is_en') && local_mamxanhapp_is_en();
    $map = [
        'watering_fert' => $en ? 'Drip Irrigation & A-B Fertigation' : 'Tưới nhỏ giọt & Châm phân A-B',
        'ipm_bio'       => $en ? 'Biological IPM & Trichoderma' : 'Phun sinh học IPM / Trichoderma',
        'env_check'     => $en ? 'pH / EC & Microclimate Check' : 'Đo kiểm pH / EC & Vi khí hậu nhà màng',
        'pruning'       => $en ? 'Canopy Pruning & Trellising' : 'Tỉa cành, treo dây & chăm sóc tán',
        'harvest'       => $en ? 'Harvest & Cold-Chain Pre-cooling' : 'Thu hoạch & Làm mát sơ bộ Pre-cooling',
    ];
    return $map[$code] ?? $code;
}

/**
 * Return clean field note string.
 */
function local_agriops_translate_note(string $note): string {
    return trim($note);
}

/**
 * Push a VietGAP batch and/or cultivation log to Supabase via local_mamxanh_sync_to_cloud().
 */
function local_agriops_push_cloud_records(?stdClass $batch, ?stdClass $log = null): void {
    global $CFG, $DB;

    if (!function_exists('local_mamxanh_sync_to_cloud') && file_exists($CFG->dirroot . '/local/mamxanhapp/lib.php')) {
        require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');
    }
    if (!function_exists('local_mamxanh_sync_to_cloud')) {
        return;
    }

    if ($batch) {
        $farmer = $DB->get_record('user', ['id' => (int)$batch->userid], 'id,firstname,lastname,firstnamephonetic,lastnamephonetic,middlename,alternatename');
        $farmname = 'Nông trại Mầm Xanh';
        $gps = '11.940419, 108.458313';
        if ($DB->get_manager()->table_exists('local_farmerprofile')) {
            $pf = $DB->get_record('local_farmerprofile', ['userid' => (int)$batch->userid], 'id,farm_name');
            if ($pf && !empty($pf->farm_name)) {
                $farmname = (string)$pf->farm_name;
            }
        }
        $secret = $CFG->mamxanh_hmac_secret ?? 'MX2_HMAC_2026_VIETGAP_AGRITECH_DIGITAL_SIGNATURE_KEY';
        $batchhmac = hash_hmac('sha256', implode('|', [
            trim((string)$batch->batch_code),
            trim((string)$batch->region),
            (int)$batch->area_m2,
            trim((string)$batch->qr_token),
        ]), $secret);

        local_mamxanh_sync_to_cloud('agriops_batches', [
            'batch_code'        => (string)$batch->batch_code,
            'product_name'      => (string)$batch->product_name,
            'crop_variety'      => (string)$batch->crop_variety,
            'farmer_name'       => $farmer ? fullname($farmer) : ('Nông hộ #' . (int)$batch->userid),
            'farm_name'         => $farmname,
            'region'            => (string)$batch->region,
            'gps_coords'        => $gps,
            'area_m2'           => (int)$batch->area_m2,
            'expected_yield_kg' => (int)$batch->expected_yield_kg,
            'vietgap_status'    => (string)($batch->vietgap_status ?? 'active'),
            'qr_token'          => (string)$batch->qr_token,
            'hmac_signature'    => $batchhmac,
            'created_at'        => gmdate('c', (int)($batch->timecreated ?: time())),
        ], 'batch_code');
    }

    if ($batch && $log) {
        local_mamxanh_sync_to_cloud('agriops_logs', [
            'batch_code'    => (string)$batch->batch_code,
            'activity_type' => (string)$log->activity_type,
            'notes'         => (string)$log->notes,
            'ph_val'        => (string)$log->ph_val,
            'ec_val'        => (string)$log->ec_val,
            'phi_days'      => (int)$log->phi_days,
            'logged_at'     => gmdate('c', (int)($log->log_time ?: time())),
        ]);
    }
}

/**
 * Query latest VietGAP PHI (Pre-Harvest Interval) records per batch for Dashboard alerts.
 * Prioritizes entries where phi_days > 0 and computes safe_date = timecreated + (phi_days * 86400).
 */
function local_agriops_get_phi_countdown_items(int $userid, int $limit = 4): array {
    global $DB;

    $dbman = $DB->get_manager();
    if (!$dbman->table_exists('local_agriops_log') || !$dbman->table_exists('local_agriops_batch')) {
        return [];
    }

    $hasownbatches = ($userid > 0) && $DB->record_exists('local_agriops_batch', ['userid' => $userid]);
    $where = $hasownbatches ? 'WHERE (l.userid = :uid OR b.userid = :buid)' : '';
    $params = $hasownbatches ? ['uid' => $userid, 'buid' => $userid] : [];

    $sql = "SELECT l.id, l.batchid, l.activity_type, l.notes, l.phi_days, l.log_time,
                   b.batch_code, b.product_name, b.region
              FROM {local_agriops_log} l
              JOIN {local_agriops_batch} b ON b.id = l.batchid
              {$where}
          ORDER BY CASE WHEN l.phi_days > 0 THEN 1 ELSE 0 END DESC,
                   l.log_time DESC,
                   l.id DESC";
    $rows = $DB->get_records_sql($sql, $params, 0, 40);
    if (empty($rows)) {
        return [];
    }

    $now = time();
    $bybatch = [];
    foreach ($rows as $r) {
        $bid = (int)$r->batchid;
        if (isset($bybatch[$bid])) {
            continue;
        }
        $phidays = max(0, (int)$r->phi_days);
        $timecreated = (int)$r->log_time;
        $safedate = $timecreated + ($phidays * 86400);
        $remaining = ($phidays > 0 && $safedate > $now)
            ? (int)ceil(($safedate - $now) / 86400)
            : 0;

        $bybatch[$bid] = (object)[
            'batchid'        => $bid,
            'batch_code'     => (string)$r->batch_code,
            'product_name'   => (string)$r->product_name,
            'region'         => (string)$r->region,
            'activity_type'  => (string)$r->activity_type,
            'activity_label' => local_agriops_translate_activity_type((string)$r->activity_type),
            'phi_days'       => $phidays,
            'timecreated'    => $timecreated,
            'safe_date'      => $safedate,
            'remaining_days' => $remaining,
            'is_locked'      => ($remaining > 0),
        ];
        if (count($bybatch) >= $limit) {
            break;
        }
    }

    return array_values($bybatch);
}

/**
 * Render the VietGAP PHI Harvest Safety Countdown card for Learner Dashboard & Overview Hub.
 */
function local_agriops_render_phi_widget_html(int $userid, bool $en = false): string {
    $items = local_agriops_get_phi_countdown_items($userid, 4);
    if (empty($items)) {
        return '';
    }

    $diaryurl = (new moodle_url('/local/agriops/index.php'))->out(false);
    $eyebrow = $en ? 'VIETGAP HARVEST SAFETY (PHI COUNTDOWN)' : 'CẢNH BÁO AN TOÀN THU HOẠCH VIETGAP (ĐẾM NGƯỢC PHI)';
    $heading = $en ? 'Pre-Harvest Interval (PHI) Status by Crop Batch' : 'Theo dõi Thời gian Cách ly An toàn Thu hoạch theo Lô';
    $btnlabel = $en ? 'Open VietGAP Diary' : 'Mở Sổ Nhật ký VietGAP';
    $dfmt = $en ? '%d %b %Y %H:%M' : '%d/%m/%Y %H:%M';

    $cards = '';
    foreach ($items as $it) {
        if ($it->is_locked) {
            $urgent = ($it->remaining_days >= 5);
            $cardbg = $urgent ? '#FEF2F2' : '#FFF8E1';
            $bordercol = $urgent ? '#C62828' : '#B45309';
            $badgebg = $urgent ? '#C62828' : '#B45309';
            $badgecol = '#FFFFFF';
            $statustext = $en
                ? ('In Quarantine — ' . $it->remaining_days . ' day(s) remaining')
                : ('Đang trong thời gian cách ly — Còn ' . $it->remaining_days . ' ngày');
        } else {
            $cardbg = '#F9FAF9';
            $bordercol = '#234E3E';
            $badgebg = '#EAF2EE';
            $badgecol = '#234E3E';
            $statustext = $en ? 'Safe to Harvest' : 'An toàn thu hoạch';
        }

        $cards .= '<article class="mx-phi-item-card" style="background:' . $cardbg . ';border:1px solid #E6ECE9;border-left:4px solid ' . $bordercol . ';border-radius:10px;padding:14px 16px;">'
            . '<div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap:6px;">'
            . '<code style="background:#FFFFFF;border:1px solid #CFD9D4;color:#1C2522;padding:2px 8px;border-radius:4px;font-weight:700;">' . s($it->batch_code) . '</code>'
            . '<span class="badge" style="background:' . $badgebg . ';color:' . $badgecol . ';border:1px solid ' . $bordercol . ';font-weight:700;padding:4px 10px;border-radius:999px;font-size:0.78rem;">' . s($statustext) . '</span>'
            . '</div>'
            . '<div style="font-weight:700;color:#1C2522;font-size:0.95rem;margin-bottom:4px;">' . s($it->product_name) . '</div>'
            . '<div class="small" style="color:#4A5552;line-height:1.55;">'
            . s($it->activity_label) . ' (' . s($it->region) . ')<br>'
            . s($en ? 'PHI Duration: ' : 'Cách ly PHI: ') . '<strong>' . (int)$it->phi_days . ' ' . s($en ? 'days' : 'ngày') . '</strong>'
            . ' • ' . s($en ? 'Safe harvest: ' : 'Ngày an toàn: ') . '<strong>' . s(userdate($it->safe_date, $dfmt)) . '</strong>'
            . '</div>'
            . '</article>';
    }

    return '<section class="mx-phi-dashboard-card mb-4" aria-label="' . s($eyebrow) . '" style="background:#FFFFFF;border:1px solid #E6ECE9;border-radius:12px;padding:20px 22px;box-shadow:0 1px 3px rgba(28,37,34,0.03);">'
        . '<div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:12px;">'
        . '<div>'
        . '<div style="font-size:0.75rem;font-weight:700;letter-spacing:0.06em;color:#234E3E;margin-bottom:2px;">' . s($eyebrow) . '</div>'
        . '<h2 class="h4 mb-0" style="color:#1C2522;font-weight:700;">' . s($heading) . '</h2>'
        . '</div>'
        . '<a href="' . $diaryurl . '" class="btn btn-primary" style="min-height:44px;display:inline-flex;align-items:center;font-weight:600;border-radius:8px;background:#234E3E;border-color:#234E3E;">'
        . s($btnlabel)
        . '</a>'
        . '</div>'
        . '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;">'
        . $cards
        . '</div>'
        . '</section>';
}
