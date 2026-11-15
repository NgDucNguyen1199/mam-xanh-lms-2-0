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
