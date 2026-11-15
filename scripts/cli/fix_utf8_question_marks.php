<?php
/**
 * CLI Script: Fix all UTF-8 '??' corruption across the Moodle database
 * using Moodle's native $DB API (utf8mb4) and standardize all Course Sections
 * (Chuyên đề 1 -> 4 & Kiểm tra & Đánh giá) across all courses.
 */
define('CLI_SCRIPT', true);
require_once('/var/www/html/config.php');
require_once($CFG->dirroot . '/course/lib.php');

global $DB;

echo "===================================================================\n";
echo "STARTING UTF-8 '??' DATABASE REPAIR & SECTION STANDARDIZATION\n";
echo "===================================================================\n";

// -------------------------------------------------------------------------
// 1. Fix corrupted mdl_page records (id = 9, 12, 15, 18 or any page with ??)
// -------------------------------------------------------------------------
$cleanpages = [
    4 => [
        'name' => 'Cẩm nang Thực hành IPM & Tự động hóa Cảnh báo Vi khí hậu Nhà màng (IOTNHAMANG01)',
        'intro' => 'Tài liệu tra cứu chuyên sâu về ngưỡng vận hành cảm biến IoT nhà màng, ma trận điều khiển khí hậu và phòng trừ dịch hại sinh học.',
        'content' => '<div style="font-family:Inter,system-ui,sans-serif;color:#0F172A;line-height:1.65;">
            <div style="background:#EAF2EE;border:1px solid #E6ECE9;border-left:4px solid #234E3E;padding:16px 20px;border-radius:8px;margin-bottom:20px;">
                <div style="font-size:0.78rem;font-weight:700;color:#234E3E;text-transform:uppercase;letter-spacing:0.04em;">HỌC LIỆU KỸ THUẬT CHUYÊN SÂU • IOTNHAMANG01</div>
                <h3 style="color:#234E3E;font-weight:700;margin:6px 0 6px;">Ma trận Ngưỡng Cảm biến Vi khí hậu IoT &amp; Quy trình Kiểm soát Dịch hại Nhà màng</h3>
                <p style="margin:0;color:#334155;font-size:0.92rem;">Quy chuẩn vận hành dành cho nhà màng công nghệ cao tại Lâm Đồng (TCVN 11892-1:2017 VietGAP).</p>
            </div>
            <ul style="line-height:1.75;color:#334155;">
                <li><strong>Kiểm soát Thiếu hụt Áp suất Hơi nước (VPD 0,8 – 1,2 kPa):</strong> Duy trì cân bằng thoát hơi nước giúp cây hấp thu Canxi/Bo tối ưu và ngăn ngừa nấm <em>Botrytis cinerea</em>.</li>
                <li><strong>Phòng trừ sinh học IPM trong nhà màng:</strong> Kết hợp lưới chắn côn trùng 50 mesh, bẫy dính màu vàng/xanh (20 bẫy/1.000 m²) và phun chế phẩm sinh học <em>Beauveria bassiana</em> định kỳ.</li>
                <li><strong>Truy xuất nguồn gốc QR VietGAP:</strong> Mọi thao tác điều chỉnh thông số tưới và phun chế phẩm sinh học đều được ghi nhận tự động vào sổ nhật ký điện tử AgriOps.</li>
            </ul>
        </div>',
    ],
    5 => [
        'name' => 'Cẩm nang Thực hành IPM & Kiểm soát An toàn Sinh học Hệ thống Thủy canh (THUYCANH01)',
        'intro' => 'Hướng dẫn định lượng muối khoáng thủy canh chuẩn Hoagland cải tiến, kiểm soát oxy hòa tan (DO) và phòng bệnh thối rễ Pythium.',
        'content' => '<div style="font-family:Inter,system-ui,sans-serif;color:#0F172A;line-height:1.65;">
            <div style="background:#EAF2EE;border:1px solid #E6ECE9;border-left:4px solid #234E3E;padding:16px 20px;border-radius:8px;margin-bottom:20px;">
                <div style="font-size:0.78rem;font-weight:700;color:#234E3E;text-transform:uppercase;letter-spacing:0.04em;">HỌC LIỆU KỸ THUẬT CHUYÊN SÂU • THUYCANH01</div>
                <h3 style="color:#234E3E;font-weight:700;margin:6px 0 6px;">Định lượng Dung dịch Dinh dưỡng Thủy canh A-B &amp; Quản lý Oxy Hòa tan (DO)</h3>
                <p style="margin:0;color:#334155;font-size:0.92rem;">Áp dụng cho hệ thống Thủy canh Hồi lưu NFT và Bán thủy canh Giá thể Xơ dừa (TCVN 11892-1:2017).</p>
            </div>
            <ul style="line-height:1.75;color:#334155;">
                <li><strong>Ngưỡng Oxy hòa tan (DO):</strong> Duy trì tối thiểu 6,0 – 8,0 mg/L bằng venturi sục khí ở đường hồi về bể ngầm; nếu DO &lt; 4,0 mg/L rễ sẽ chuyển màu nâu và nhiễm nấm <em>Pythium</em>.</li>
                <li><strong>Kiểm soát Nitrat trước thu hoạch:</strong> Giảm 50% nồng độ EC bằng nước sạch 3 ngày trước thu hoạch đối với rau ăn lá thủy canh để đảm bảo dư lượng Nitrat đạt chuẩn VietGAP.</li>
            </ul>
        </div>',
    ],
    6 => [
        'name' => 'Cẩm nang Thực hành IPM: Kiểm soát Nấm Phytophthora, Rệp sáp & Mã số Vùng trồng Sầu riêng (SAURIENG01)',
        'intro' => 'Quy trình đồng bộ cơi đọt, phân hóa mầm hoa và quản lý dịch hại tổng hợp phục vụ Mã số vùng trồng xuất khẩu chính ngạch.',
        'content' => '<div style="font-family:Inter,system-ui,sans-serif;color:#0F172A;line-height:1.65;">
            <div style="background:#EAF2EE;border:1px solid #E6ECE9;border-left:4px solid #234E3E;padding:16px 20px;border-radius:8px;margin-bottom:20px;">
                <div style="font-size:0.78rem;font-weight:700;color:#234E3E;text-transform:uppercase;letter-spacing:0.04em;">HỌC LIỆU KỸ THUẬT CHUYÊN SÂU • SAURIENG01</div>
                <h3 style="color:#234E3E;font-weight:700;margin:6px 0 6px;">Phác đồ Quản lý Cơi đọt, Làm bông &amp; Phòng trừ Nấm Phytophthora Sầu riêng Xuất khẩu</h3>
                <p style="margin:0;color:#334155;font-size:0.92rem;">Tuân thủ Nghị định thư Xuất khẩu &amp; Quy chuẩn VietGAP TCVN 11892-1:2017.</p>
            </div>
            <ul style="line-height:1.75;color:#334155;">
                <li><strong>Phòng xì mủ thân &amp; thối trái:</strong> Tưới gốc định kỳ nấm đối kháng <em>Trichoderma harzianum</em> (10^8 CFU/g) đầu và cuối mùa mưa; kết hợp Potassium Phosphonate kích kháng lưu dẫn 2 chiều.</li>
                <li><strong>Kiểm soát đối tượng kiểm dịch thực vật (Rệp sáp sầu riêng):</strong> Tỉa thông thoáng tán trái, phun dầu khoáng sinh học và tuân thủ nghiêm ngặt thời gian cách ly (PHI).</li>
            </ul>
        </div>',
    ],
    7 => [
        'name' => 'Cẩm nang Thực hành IPM: Ứng dụng Phân Hữu cơ Vi sinh Trichoderma Phòng Bệnh Héo Rũ Vùng Rễ (PHANVISINH01)',
        'intro' => 'Hướng dẫn điều chỉnh tỷ lệ C/N, diễn biến nhiệt độ 4 giai đoạn ủ hiếu khí và tiêu chuẩn nghiệm thu phân hữu cơ vi sinh.',
        'content' => '<div style="font-family:Inter,system-ui,sans-serif;color:#0F172A;line-height:1.65;">
            <div style="background:#EAF2EE;border:1px solid #E6ECE9;border-left:4px solid #234E3E;padding:16px 20px;border-radius:8px;margin-bottom:20px;">
                <div style="font-size:0.78rem;font-weight:700;color:#234E3E;text-transform:uppercase;letter-spacing:0.04em;">HỌC LIỆU KỸ THUẬT CHUYÊN SÂU • PHANVISINH01</div>
                <h3 style="color:#234E3E;font-weight:700;margin:6px 0 6px;">Kỹ thuật Ủ Hiếu khí Phụ phẩm Nông nghiệp &amp; Hoạt hóa Vi sinh Đối kháng Trichoderma</h3>
                <p style="margin:0;color:#334E3E;font-size:0.92rem;">Biên soạn theo Sổ tay Kỹ thuật Nông nghiệp Tuần hoàn FAO &amp; Tiêu chuẩn Phân bón Hữu cơ Vi sinh Việt Nam.</p>
            </div>
            <ul style="line-height:1.75;color:#334155;">
                <li><strong>Giai đoạn ưa nhiệt (Ngày 5 – Ngày 20):</strong> Nhiệt độ tâm đống ủ đạt 60 – 65°C giúp tiêu diệt hoàn toàn hạt cỏ dại, trứng tuyến trùng và mầm bệnh <em>E.coli / Salmonella</em>.</li>
                <li><strong>Giai đoạn làm nguội &amp; Cấy chủng vi sinh (Ngày 25 – Ngày 40):</strong> Khi nhiệt độ đống ủ hạ xuống dưới 38°C, bổ sung bào tử nấm <em>Trichoderma spp.</em> và <em>Bacillus subtilis</em> để nhân mật độ tối đa.</li>
            </ul>
        </div>',
    ],
];

$allpages = $DB->get_records('page');
foreach ($allpages as $pg) {
    if (strpos((string)$pg->name, '??') !== false || strpos((string)$pg->intro, '??') !== false || strpos((string)$pg->content, '??') !== false) {
        $cid = (int)$pg->course;
        $tpl = $cleanpages[$cid] ?? $cleanpages[4];
        $pg->name = $tpl['name'];
        $pg->intro = '<p>' . $tpl['intro'] . '</p>';
        $pg->introformat = FORMAT_HTML;
        $pg->content = $tpl['content'];
        $pg->contentformat = FORMAT_HTML;
        $pg->timemodified = time();
        $DB->update_record('page', $pg);
        echo "[OK] Repaired corrupted mdl_page id={$pg->id} in course {$cid}.\n";
    }
}

// -------------------------------------------------------------------------
// 2. Fix all mdl_grading_definitions, mdl_gradingform_rubric_criteria,
//    mdl_gradingform_rubric_levels, mdl_grading_instances, and
//    mdl_gradingform_rubric_fillings containing '??'
// -------------------------------------------------------------------------
$critdefs = [
    1 => [
        'desc' => 'Tiêu chí 1: Chuẩn bị Vật tư, Giá thể & Hiệu chuẩn Thiết bị Đo (EC/pH/Ẩm kế)',
        'levels' => [
            0  => 'Chưa đạt (0đ): Thiếu bước xử lý giá thể hoặc không hiệu chuẩn bút đo EC/pH.',
            15 => 'Đạt chuẩn (15đ): Chuẩn bị đủ vật tư và thiết bị đo nhưng ghi chép thông số đầu vào chưa chi tiết.',
            25 => 'Xuất sắc (25đ): Xử lý giá thể chuẩn kỹ thuật, hiệu chuẩn chính xác bút đo EC/pH và kiểm tra nguồn nước.',
        ],
    ],
    2 => [
        'desc' => 'Tiêu chí 2: Thực hành Quy trình Kỹ thuật Canh tác & Điều tiết Dinh dưỡng / Vi khí hậu',
        'levels' => [
            0  => 'Chưa đạt (0đ): Pha sai nồng độ dinh dưỡng hoặc tưới không đúng ngưỡng giai đoạn sinh trưởng.',
            15 => 'Đạt chuẩn (15đ): Vận hành đúng quy trình tưới và chăm sóc cơ bản, sai số EC/pH nhỏ.',
            25 => 'Xuất sắc (25đ): Kiểm soát chuẩn xác ngưỡng EC/pH, nhiệt ẩm nhà màng và kỹ thuật chăm sóc theo giai đoạn.',
        ],
    ],
    3 => [
        'desc' => 'Tiêu chí 3: Tuân thủ An toàn Sinh học, Phác đồ IPM & Thời gian Cách ly (PHI)',
        'levels' => [
            0  => 'Chưa đạt (0đ): Vi phạm nguyên tắc sử dụng thuốc BVTV hoặc thiếu biện pháp phòng trừ sinh học.',
            15 => 'Đạt chuẩn (15đ): Áp dụng đúng biện pháp IPM cơ bản và ghi nhận thời gian cách ly.',
            25 => 'Xuất sắc (25đ): Ưu tiên chế phẩm sinh học (Trichoderma, thiên địch), tuân thủ tuyệt đối PHI chuẩn VietGAP.',
        ],
    ],
    4 => [
        'desc' => 'Tiêu chí 4: Chất lượng Nông sản & Hồ sơ Nhật ký Đồng ruộng Điện tử VietGAP',
        'levels' => [
            0  => 'Chưa đạt (0đ): Thiếu nhật ký canh tác hoặc báo cáo thu hoạch sơ sài, không đủ điều kiện truy xuất.',
            15 => 'Đạt chuẩn (15đ): Có nhật ký canh tác và đánh giá sản lượng đạt yêu cầu cơ bản.',
            25 => 'Xuất sắc (25đ): Nhật ký VietGAP điện tử đầy đủ, minh chứng rõ ràng, đủ điều kiện cấp mã QR truy xuất.',
        ],
    ],
];

$alldefs = $DB->get_records('grading_definitions');
foreach ($alldefs as $def) {
    if (strpos((string)$def->name, '??') !== false || strpos((string)$def->description, '??') !== false) {
        $def->name = 'Rubric Đánh giá Thực hành Hiện trường 4 Tiêu chí Chuẩn VietGAP';
        $def->description = '<p>Thang chấm điểm thực hành 4 tiêu chí chuẩn Quốc gia VietGAP TCVN 11892-1:2017 (Tổng 100 điểm).</p>';
        $DB->update_record('grading_definitions', $def);
    }
    $crits = array_values($DB->get_records('gradingform_rubric_criteria', ['definitionid' => $def->id], 'sortorder ASC, id ASC'));
    foreach ($crits as $idx => $cr) {
        $cnum = ($idx % 4) + 1;
        $tpl = $critdefs[$cnum];
        if (strpos((string)$cr->description, '??') !== false || empty($cr->description)) {
            $cr->description = $tpl['desc'];
            $DB->update_record('gradingform_rubric_criteria', $cr);
        }
        $levels = $DB->get_records('gradingform_rubric_levels', ['criterionid' => $cr->id], 'score ASC');
        foreach ($levels as $lv) {
            if (strpos((string)$lv->definition, '??') !== false) {
                $sc = (int)round((float)$lv->score);
                $lv->definition = $tpl['levels'][$sc] ?? $tpl['levels'][25];
                $DB->update_record('gradingform_rubric_levels', $lv);
                echo "[OK] Repaired mdl_gradingform_rubric_levels id={$lv->id} (score={$sc}).\n";
            }
        }
    }
}

// Also clean up or repair any orphaned mdl_gradingform_rubric_levels records
$alllevels = $DB->get_records('gradingform_rubric_levels', null, 'id ASC');
foreach ($alllevels as $lv) {
    if (strpos((string)$lv->definition, '??') !== false) {
        $parentcrit = $DB->get_record('gradingform_rubric_criteria', ['id' => $lv->criterionid]);
        if (!$parentcrit) {
            $DB->delete_records('gradingform_rubric_levels', ['id' => $lv->id]);
            echo "[OK] Deleted orphaned corrupted mdl_gradingform_rubric_levels id={$lv->id}.\n";
        } else {
            $cnum = (((int)$parentcrit->sortorder - 1) % 4) + 1;
            $sc = (int)round((float)$lv->score);
            $lv->definition = $critdefs[$cnum]['levels'][$sc] ?? $critdefs[$cnum]['levels'][25];
            $DB->update_record('gradingform_rubric_levels', $lv);
            echo "[OK] Repaired mdl_gradingform_rubric_levels id={$lv->id}.\n";
        }
    }
}

$cleanremarks = [
    'Chuẩn bị đầy đủ vật tư, giá thể xơ dừa đã xử lý tanin và hiệu chuẩn bút đo EC/pH chuẩn xác trước buổi thực hành.',
    'Thực hiện đúng định lượng dung dịch dinh dưỡng, vận hành tưới nhỏ giọt bù áp và điều tiết vi khí hậu đạt yêu cầu kỹ thuật.',
    'Tuân thủ nghiêm ngặt phác đồ IPM sinh học (nấm đối kháng Trichoderma, bẫy dính), đảm bảo thời gian cách ly PHI = 0 ngày.',
    'Nhật ký đồng ruộng điện tử VietGAP trích xuất từ hệ thống AgriOps đầy đủ thông số; khuyến nghị bổ sung ảnh chụp đối chứng cuối vụ.',
];

$allfillings = $DB->get_records('gradingform_rubric_fillings', null, 'id ASC');
$repaired_fillings = 0;
foreach ($allfillings as $f) {
    if (strpos((string)$f->remark, '??') !== false) {
        $f->remark = $cleanremarks[((int)$f->id - 1) % 4];
        $f->remarkformat = FORMAT_HTML;
        $DB->update_record('gradingform_rubric_fillings', $f);
        $repaired_fillings++;
    }
}
echo "[OK] Repaired {$repaired_fillings} records in mdl_gradingform_rubric_fillings.\n";

$allinsts = $DB->get_records('grading_instances');
foreach ($allinsts as $inst) {
    if (strpos((string)$inst->feedback, '??') !== false) {
        $inst->feedback = 'Học viên nắm vững quy trình kỹ thuật canh tác công nghệ cao và tiêu chuẩn VietGAP (TCVN 11892-1:2017). Hồ sơ thực hành và Nhật ký đồng ruộng điện tử đạt chuẩn cấp Chứng nhận số QR.';
        $DB->update_record('grading_instances', $inst);
    }
}

// -------------------------------------------------------------------------
// 3. Fix all mdl_event records containing '??'
// -------------------------------------------------------------------------
$allevents = $DB->get_records('event');
foreach ($allevents as $ev) {
    if (strpos((string)$ev->name, '??') !== false || strpos((string)$ev->description, '??') !== false) {
        $c = $DB->get_record('course', ['id' => $ev->courseid]);
        $code = $c ? $c->shortname : 'AGRITECH';
        if ($ev->modulename === 'assign' && $ev->instance > 0) {
            $asg = $DB->get_record('assign', ['id' => $ev->instance]);
            if ($asg) {
                $ev->name = $asg->name . ' — Hạn nộp (' . $code . ')';
                $ev->description = $asg->intro;
            }
        }
        if (strpos((string)$ev->name, '??') !== false) {
            $ev->name = 'Thảo luận chuyên đề tại lớp — Hạn nộp (' . $code . ')';
        }
        if (strpos((string)$ev->description, '??') !== false) {
            $ev->description = '<p>Hoàn thành bài thảo luận chuyên đề và nộp báo cáo thực hành theo chuẩn VietGAP.</p>';
        }
        $DB->update_record('event', $ev);
        echo "[OK] Repaired mdl_event id={$ev->id}: {$ev->name}\n";
    }
}

// -------------------------------------------------------------------------
// 4. Clean up duplicate Discussion Assignments (cmid 172, 173 in Course 2
//    and cmid 174, 175 in Course 3) and organize Course 2 Sections 1..5
// -------------------------------------------------------------------------
foreach ([172, 173, 174, 175] as $dupcmid) {
    $dupcm = $DB->get_record('course_modules', ['id' => $dupcmid]);
    if ($dupcm) {
        try {
            course_delete_module($dupcmid);
            echo "[OK] Deleted duplicate assignment course_module id={$dupcmid}.\n";
        } catch (\Throwable $e) {
            $DB->set_field('course_modules', 'visible', 0, ['id' => $dupcmid]);
        }
    }
}

// Ensure Course 2 (CHAMSOC01) has clean Section 1..4 (4 Chuyên đề) and Section 5 (Kiểm tra & Đánh giá)
$c2_sec4 = $DB->get_record('course_sections', ['course' => 2, 'section' => 4]);
$c2_sec5 = $DB->get_record('course_sections', ['course' => 2, 'section' => 5]);
if ($c2_sec4 && $c2_sec5) {
    $c2_sec4->name = 'Chuyên đề 4: Quản lý Dịch hại Tổng hợp (IPM) & Kiểm soát Sinh học';
    $c2_sec4->summary = '<p>Phương pháp điều tra sinh thái đồng ruộng, bẫy dính màu, thiên địch và chế phẩm sinh học Beauveria bassiana thay thế thuốc hóa học độc hại.</p>';
    $c2_sec4->summaryformat = FORMAT_HTML;

    // Move cmid=6 (Quiz) from Sec 4 to Sec 5 (Kiểm tra & Đánh giá) if in Sec 4
    $seq4 = array_filter(array_map('intval', explode(',', (string)$c2_sec4->sequence)));
    $seq5 = array_filter(array_map('intval', explode(',', (string)$c2_sec5->sequence)));
    if (in_array(6, $seq4, true)) {
        $seq4 = array_values(array_diff($seq4, [6, 172, 173]));
        if (!in_array(6, $seq5, true)) {
            $seq5[] = 6;
        }
        $DB->set_field('course_modules', 'section', $c2_sec5->id, ['id' => 6]);
    }
    $c2_sec4->sequence = implode(',', $seq4);
    $c2_sec5->sequence = implode(',', $seq5);
    $DB->update_record('course_sections', $c2_sec4);
    $DB->update_record('course_sections', $c2_sec5);
    echo "[OK] Restored Course 2 Sec 4 ('Chuyên đề 4: Quản lý Dịch hại Tổng hợp (IPM) & Kiểm soát Sinh học') and moved Quiz cmid=6 to Sec 5 ('Kiểm tra & Đánh giá').\n";
}

// -------------------------------------------------------------------------
// 5. Standardize Section Names & Summaries Across ALL Courses (2,3,4,5,6,7,8,19,21)
// -------------------------------------------------------------------------
$eval_summary_text = 'Hệ thống điểm danh chuyên cần (Lý thuyết & Thực hành), thảo luận chuyên đề theo buổi học, bài kiểm tra trắc nghiệm và bài thu hoạch cuối khóa chấm theo tiêu chí Rubric.';
$eval_summary_html = '<p>' . $eval_summary_text . '</p>';

$dautay_summaries = [
    0 => '<p>Đề cương chi tiết học phần, lịch trình tập huấn lý thuyết - thực hành và tài liệu hướng dẫn kỹ thuật canh tác Dâu tây &amp; Hoa Cúc công nghệ cao đạt chuẩn VietGAP.</p>',
    1 => '<p>Tiêu chuẩn chọn cây giống cấy mô sạch bệnh, thiết kế thông gió nhà màng và kiểm soát bức xạ quang hợp PAR, nhiệt độ ngày/đêm và độ ẩm tương đối (RH).</p>',
    2 => '<p>Tính toán thủy lực đường ống, vận hành hệ thống tưới nhỏ giọt bù áp tự động và kiểm soát độ ẩm giá thể xơ dừa theo từng giai đoạn sinh trưởng.</p>',
    3 => '<p>Phác đồ pha chế dung dịch dinh dưỡng bồn A-B, kiểm soát ngưỡng độ dẫn điện (EC 1,2 – 1,8 mS/cm) và độ pH (5,5 – 6,2) qua hệ thống châm phân Fertigation.</p>',
    4 => '<p>Phương pháp điều tra sinh thái nhà màng, sử dụng bẫy dính sinh học, nhện bắt mồi thiên địch và chế phẩm nấm đối kháng <em>Trichoderma / Beauveria bassiana</em>.</p>',
];

$targetcourses = [2, 3, 4, 5, 6, 7, 8, 19, 21];
foreach ($targetcourses as $cid) {
    $secs = $DB->get_records('course_sections', ['course' => $cid], 'section ASC');
    if (empty($secs)) {
        continue;
    }
    $maxsec = max(array_map(function($s) { return (int)$s->section; }, $secs));

    foreach ($secs as $sec) {
        $snum = (int)$sec->section;
        $changed = false;

        // Fix any '??' in section name or summary first
        if (strpos((string)$sec->name, '??') !== false) {
            $sec->name = 'Kiểm tra & Đánh giá';
            $changed = true;
        }

        // Determine if this section is the Evaluation section ("Kiểm tra & Đánh giá")
        $iseval = ($snum === $maxsec)
            || (strpos((string)$sec->name, 'Kiểm tra') !== false)
            || (strpos((string)$sec->name, 'Đánh giá') !== false);

        if ($iseval && $snum > 0) {
            $sec->name = 'Kiểm tra & Đánh giá';
            $sec->summary = $eval_summary_html;
            $sec->summaryformat = FORMAT_HTML;
            $changed = true;
        } else if (($cid === 19 || $cid === 21) && isset($dautay_summaries[$snum])) {
            if (empty(trim(strip_tags((string)$sec->summary)))) {
                $sec->summary = $dautay_summaries[$snum];
                $sec->summaryformat = FORMAT_HTML;
                $changed = true;
            }
        }

        // Also ensure Section 4 of Courses 19 and 21 has at least 1 module if empty
        if (($cid === 19 || $cid === 21) && $snum === 4 && empty(trim((string)$sec->sequence))) {
            // Check if there is an IPM page in this course or create one
            $ipmpage = new stdClass();
            $ipmpage->course = $cid;
            $ipmpage->name = 'Phác đồ Quản lý Dịch hại Tổng hợp (IPM) & Thiên địch Sinh học Nhà màng Đà Lạt';
            $ipmpage->intro = '<p>Tài liệu hướng dẫn nhận diện Bọ trĩ, Nhện đỏ, Nấm phấn trắng và quy trình thả thiên địch sinh học trên cây Dâu tây &amp; Hoa Cúc.</p>';
            $ipmpage->introformat = FORMAT_HTML;
            $ipmpage->content = '<div style="font-family:Inter,system-ui,sans-serif;color:#0F172A;line-height:1.65;">
                <div style="background:#EAF2EE;border:1px solid #E6ECE9;border-left:4px solid #234E3E;padding:16px 20px;border-radius:8px;margin-bottom:16px;">
                    <div style="font-size:0.78rem;font-weight:700;color:#234E3E;text-transform:uppercase;">CHUYÊN ĐỀ 4 • AN TOÀN SINH HỌC &amp; IPM VIETGAP</div>
                    <h3 style="color:#234E3E;font-weight:700;margin:6px 0;">Quy trình Phòng trừ Tổng hợp (IPM) Bọ trĩ, Nhện đỏ &amp; Nấm Botrytis</h3>
                </div>
                <ul style="line-height:1.75;color:#334155;">
                    <li><strong>Giám sát mật độ sâu hại:</strong> Treo 25 bẫy dính màu xanh/vàng trên 1.000 m² nhà màng, kiểm đếm mật độ bọ trĩ <em>Frankliniella occidentalis</em> định kỳ 3 ngày/lần.</li>
                    <li><strong>Thiên địch &amp; Chế phẩm sinh học:</strong> Phóng thích nhện bắt mồi <em>Amblyseius swirskii</em> và phun nấm ký sinh <em>Beauveria bassiana</em> vào chiều mát (RH &ge; 70%).</li>
                    <li><strong>Đảm bảo Thời gian Cách ly (PHI):</strong> Tuyệt đối tuân thủ thời gian cách ly an toàn và ghi nhận đầy đủ vào Sổ Nhật ký Điện tử VietGAP trước khi thu hoạch.</li>
                </ul>
            </div>';
            $ipmpage->contentformat = FORMAT_HTML;
            $ipmpage->legacyfiles = 0;
            $ipmpage->display = 5;
            $ipmpage->displayoptions = 'a:2:{s:12:"printheading";s:1:"1";s:10:"printintro";s:1:"1";}';
            $ipmpage->revision = 1;
            $ipmpage->timemodified = time();
            $pageid = $DB->insert_record('page', $ipmpage);

            $pmod = $DB->get_record('modules', ['name' => 'page'], '*', MUST_EXIST);
            $cm = new stdClass();
            $cm->course = $cid;
            $cm->module = $pmod->id;
            $cm->instance = $pageid;
            $cm->section = $sec->id;
            $cm->added = time();
            $cm->visible = 1;
            $cm->visibleoncoursepage = 1;
            $cm->completion = 1;
            $cmid = $DB->insert_record('course_modules', $cm);
            context_module::instance($cmid);
            $sec->sequence = (string)$cmid;
            $changed = true;
            echo "[OK] Added IPM technical page (cmid={$cmid}) to Section 4 of Course {$cid}.\n";
        }

        if ($changed) {
            $sec->timemodified = time();
            $DB->update_record('course_sections', $sec);
            echo "[OK] Updated Course {$cid} Section {$snum} (id={$sec->id}): {$sec->name}\n";
        }
    }

    rebuild_course_cache($cid, true);
}

// -------------------------------------------------------------------------
// 6. Final Full-Database Verification Scan Across All Text Columns
// -------------------------------------------------------------------------
echo "\n=== FINAL VERIFICATION SCAN FOR '??' ACROSS ALL TABLES ===\n";
$tables = [
    'course' => ['fullname', 'shortname', 'summary'],
    'course_sections' => ['name', 'summary'],
    'assign' => ['name', 'intro'],
    'attendance' => ['name', 'intro'],
    'attendance_statuses' => ['description', 'acronym'],
    'attendance_sessions' => ['description'],
    'page' => ['name', 'intro', 'content'],
    'resource' => ['name', 'intro'],
    'quiz' => ['name', 'intro'],
    'question' => ['name', 'questiontext', 'generalfeedback'],
    'question_answers' => ['answer', 'feedback'],
    'grading_definitions' => ['name', 'description'],
    'gradingform_rubric_criteria' => ['description'],
    'gradingform_rubric_levels' => ['definition'],
    'gradingform_rubric_fillings' => ['remark'],
    'grading_instances' => ['feedback'],
    'badge' => ['name', 'description', 'issuername'],
    'fieldpractice' => ['name', 'intro', 'location_name'],
    'h5pactivity' => ['name', 'intro'],
    'scorm' => ['name', 'intro'],
    'forum' => ['name', 'intro'],
    'label' => ['name', 'intro'],
    'event' => ['name', 'description'],
];

$remaining = 0;
foreach ($tables as $tbl => $cols) {
    if (!$DB->get_manager()->table_exists($tbl)) continue;
    $recs = $DB->get_records($tbl);
    foreach ($recs as $r) {
        foreach ($cols as $col) {
            $val = (string)($r->$col ?? '');
            if (strpos($val, '??') !== false) {
                $remaining++;
                echo "  [STILL HAS ??] {$tbl}.{$col} (id={$r->id}): " . mb_substr(strip_tags($val), 0, 100) . "\n";
            }
        }
    }
}
echo "Remaining records with '??': {$remaining}\n";
