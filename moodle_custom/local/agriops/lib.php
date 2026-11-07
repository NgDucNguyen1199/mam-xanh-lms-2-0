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
