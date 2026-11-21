<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/cohort/lib.php');

/**
 * Return regional Cohort definitions mapped by region name.
 */
function local_farmerprofile_region_cohorts(): array {
    return [
        'Lâm Đồng' => [
            'idnumber'    => 'COHORT_LAMDONG',
            'name'        => 'Tổ hợp tác Vùng trồng Lâm Đồng (Rau Hoa & Nhà màng IoT)',
            'description' => 'Nhóm nông hộ canh tác công nghệ cao tại Đà Lạt, Đơn Dương, Đức Trọng, Lâm Hà.',
            'lat'         => '11.940419',
            'lng'         => '108.458313',
        ],
        'Đắk Lắk' => [
            'idnumber'    => 'COHORT_DAKLAK',
            'name'        => 'Tổ hợp tác Vùng trồng Đắk Lắk (Sầu riêng & Cây ăn quả XK)',
            'description' => 'Nhóm nông hộ canh tác Sầu riêng, Bơ, Cà phê VietGAP tại Krông Pắc, Cư M\'gar, Buôn Ma Thuột.',
            'lat'         => '12.666667',
            'lng'         => '108.050000',
        ],
        'Đồng Nai' => [
            'idnumber'    => 'COHORT_DONGNAI',
            'name'        => 'Tổ hợp tác Vùng trồng Đồng Nai (Trái cây & Nông sản Sơ chế)',
            'description' => 'Nhóm nông hộ canh tác Bưởi, Dưa lưới, Rau quả VietGAP tại Long Khánh, Xuân Lộc, Định Quán.',
            'lat'         => '10.933333',
            'lng'         => '107.233333',
        ],
    ];
}

/**
 * Ensure the system cohort exists for a given region and sync user membership.
 */
function local_farmerprofile_sync_cohort(int $userid, string $region): int {
    global $DB;

    $defs = local_farmerprofile_region_cohorts();
    if (!isset($defs[$region])) {
        $region = 'Lâm Đồng';
    }
    $syscontext = context_system::instance();

    $cohortids = [];
    foreach ($defs as $regkey => $meta) {
        $cohort = $DB->get_record('cohort', ['idnumber' => $meta['idnumber']]);
        if (!$cohort) {
            $newc = (object)[
                'contextid'         => $syscontext->id,
                'name'              => $meta['name'],
                'idnumber'          => $meta['idnumber'],
                'description'       => $meta['description'],
                'descriptionformat' => FORMAT_HTML,
                'visible'           => 1,
            ];
            $cid = cohort_add_cohort($newc);
        } else {
            $cid = (int)$cohort->id;
        }
        $cohortids[$regkey] = $cid;
    }

    $targetcid = $cohortids[$region];
    foreach ($cohortids as $regkey => $cid) {
        if ($cid === $targetcid) {
            if (!$DB->record_exists('cohort_members', ['cohortid' => $cid, 'userid' => $userid])) {
                cohort_add_member($cid, $userid);
            }
        } else {
            if ($DB->record_exists('cohort_members', ['cohortid' => $cid, 'userid' => $userid])) {
                cohort_remove_member($cid, $userid);
            }
        }
    }

    return $targetcid;
}

/**
 * Create or update a farmer profile and automatically sync their regional Cohort.
 */
function local_farmerprofile_save_profile(int $userid, array $data): stdClass {
    global $DB;

    $defs = local_farmerprofile_region_cohorts();
    $region = isset($defs[$data['region'] ?? '']) ? $data['region'] : 'Lâm Đồng';
    $cohortid = local_farmerprofile_sync_cohort($userid, $region);

    $existing = $DB->get_record('local_farmerprofile', ['userid' => $userid]);
    $record = (object)[
        'userid'        => $userid,
        'farm_name'     => trim((string)($data['farm_name'] ?? 'Nông hộ Mầm Xanh')),
        'area_m2'       => max(50, (int)($data['area_m2'] ?? 1500)),
        'main_crop'     => trim((string)($data['main_crop'] ?? 'Rau ăn lá công nghệ cao')),
        'region'        => $region,
        'gps_lat'       => trim((string)($data['gps_lat'] ?? $defs[$region]['lat'])),
        'gps_lng'       => trim((string)($data['gps_lng'] ?? $defs[$region]['lng'])),
        'cert_standard' => trim((string)($data['cert_standard'] ?? 'VietGAP')),
        'phone'         => trim((string)($data['phone'] ?? '')),
        'cohortid'      => $cohortid,
        'timemodified'  => time(),
    ];

    if ($existing) {
        $record->id = $existing->id;
        $DB->update_record('local_farmerprofile', $record);
    } else {
        $record->id = $DB->insert_record('local_farmerprofile', $record);
    }

    return $record;
}
