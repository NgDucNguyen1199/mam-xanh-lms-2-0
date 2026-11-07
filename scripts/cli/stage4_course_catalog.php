<?php
// ===================================================================
// Mầm Xanh LMS 2.0 - Stage 4 Complete 6-Course Agronomic Catalog
// ===================================================================
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/stage4_html_builders.php');

function mamxanh_get_six_courses_catalog(): array {
    // Inline SVG Technical Schematics for Slide Decks
    $svgFertigation = '<div style="margin:14px 0;background:#F8FCF9;border:1.5px solid #B7E4C7;border-radius:16px;padding:12px;text-align:center;">
        <svg viewBox="0 0 680 150" style="max-width:100%;height:auto;" xmlns="http://www.w3.org/2000/svg">
            <rect x="12" y="25" width="115" height="95" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
            <text x="69" y="65" text-anchor="middle" font-weight="bold" font-size="13" fill="#0D47A1">Bồn Nước Sạch</text>
            <text x="69" y="88" text-anchor="middle" font-size="11" fill="#1565C0">EC &lt; 0.3 mS/cm</text>
            <rect x="165" y="15" width="115" height="50" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
            <text x="222" y="44" text-anchor="middle" font-weight="bold" font-size="12" fill="#1B4332">Bồn Stock A (Ca/Fe)</text>
            <rect x="165" y="80" width="115" height="50" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2"/>
            <text x="222" y="109" text-anchor="middle" font-weight="bold" font-size="12" fill="#E65100">Bồn Stock B (NPK/Mg)</text>
            <rect x="325" y="32" width="150" height="82" rx="14" fill="#1B4332"/>
            <text x="400" y="66" text-anchor="middle" font-weight="bold" font-size="13" fill="#FFFFFF">Bộ Châm Venturi</text>
            <text x="400" y="88" text-anchor="middle" font-size="11" fill="#B7E4C7">Cảm biến pH 6.0 / EC 2.0</text>
            <rect x="520" y="25" width="145" height="95" rx="12" fill="#E8F5E9" stroke="#1B4332" stroke-width="2.5"/>
            <text x="592" y="62" text-anchor="middle" font-weight="bold" font-size="13" fill="#1B4332">Dây Nhỏ Giọt Bù Áp</text>
            <text x="592" y="84" text-anchor="middle" font-size="11" fill="#2E7D32">2.0 L/giờ • Lọc Đĩa 120 Mesh</text>
            <line x1="127" y1="73" x2="325" y2="73" stroke="#1565C0" stroke-width="3"/>
            <line x1="280" y1="40" x2="325" y2="55" stroke="#2E7D32" stroke-width="2.5"/>
            <line x1="280" y1="105" x2="325" y2="90" stroke="#F9A825" stroke-width="2.5"/>
            <line x1="475" y1="73" x2="520" y2="73" stroke="#1B4332" stroke-width="3"/>
        </svg>
    </div>';

    $svgColdChain = '<div style="margin:14px 0;background:#F8FCF9;border:1.5px solid #B7E4C7;border-radius:16px;padding:12px;text-align:center;">
        <svg viewBox="0 0 680 140" style="max-width:100%;height:auto;" xmlns="http://www.w3.org/2000/svg">
            <rect x="10" y="25" width="145" height="90" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
            <text x="82" y="62" text-anchor="middle" font-weight="bold" font-size="12" fill="#1B4332">1. Pre-Cooling &lt;2h</text>
            <text x="82" y="84" text-anchor="middle" font-size="11" fill="#2E7D32">Hạ nhiệt đồng ruộng 4°C</text>
            <rect x="182" y="25" width="145" height="90" rx="12" fill="#E1F5FE" stroke="#0277BD" stroke-width="2"/>
            <text x="254" y="62" text-anchor="middle" font-weight="bold" font-size="12" fill="#01579B">2. Sấy Lạnh Bơm Nhiệt</text>
            <text x="254" y="84" text-anchor="middle" font-size="11" fill="#0277BD">25–40°C • Giữ màu &amp; Vitamin</text>
            <rect x="354" y="25" width="145" height="90" rx="12" fill="#FFF8E1" stroke="#F9A825" stroke-width="2"/>
            <text x="426" y="62" text-anchor="middle" font-weight="bold" font-size="12" fill="#E65100">3. Bao bì Khí quyển MAP</text>
            <text x="426" y="84" text-anchor="middle" font-size="11" fill="#EF6C00">O2 3-5% • CO2 5-8%</text>
            <rect x="526" y="25" width="142" height="90" rx="12" fill="#1B4332"/>
            <text x="597" y="62" text-anchor="middle" font-weight="bold" font-size="12" fill="#FFFFFF">4. Kho Lạnh CA &amp; QR</text>
            <text x="597" y="84" text-anchor="middle" font-size="11" fill="#B7E4C7">DataLogger 24/7 + VietGAP</text>
        </svg>
    </div>';

    // Shared rich IPM Flashcards (Rust, Mealybugs, Thrips, Spider Mites)
    $standardIpmCards = [
        [
            'icon'          => '🟠',
            'front_title'   => 'Bệnh Gỉ sắt (Puccinia / Uromyces spp.)',
            'front_symptom' => 'Mặt dưới lá già xuất hiện các ổ bào tử nổi u nhỏ màu nâu cam như gỉ sắt, mặt trên chấm vàng; bùng phát mạnh khi sương mù ẩm độ >85%.',
            'back_protocol' => '<strong>1. Canh tác:</strong> Tỉa lá bệnh phía gốc đem tiêu hủy ngoài nhà màng, bật quạt thông gió.<br><strong>2. Sinh học:</strong> Phun nấm đối kháng <em>Chaetomium</em> hoặc vi khuẩn <em>Bacillus subtilis</em> (25g/16L).<br><strong>3. Cách ly PHI:</strong> 0 ngày.',
        ],
        [
            'icon'          => '⚪',
            'front_title'   => 'Rệp sáp phấn trắng (Planococcus spp.)',
            'front_symptom' => 'Bám thành từng cụm phủ lớp sáp bông trắng ở cuống trái, nách lá, cổ rễ; tiết dịch mật ngọt thu hút kiến đen cộng sinh và nấm bồ hóng đen.',
            'back_protocol' => '<strong>1. Cơ giới:</strong> Quấn băng keo bẫy kiến quanh gốc, cắt tỉa cành tăm thông thoáng.<br><strong>2. Sinh học:</strong> Phun <strong>Dầu khoáng sinh học 0.3%</strong> làm tan lớp sáp kết hợp nấm trắng <em>Beauveria bassiana</em>.<br><strong>3. Cách ly PHI:</strong> 0–3 ngày.',
        ],
        [
            'icon'          => '🌀',
            'front_title'   => 'Bọ trĩ hại đọt non & hoa (Thrips palmi)',
            'front_symptom' => 'Đọt non xoăn tít còi cọc, mặt dưới lá có vệt sần ánh bạc màu đồng, cánh hoa cháy nâu, trái non sẹo da cám; truyền bệnh virus khảm xoăn lá.',
            'back_protocol' => '<strong>1. Giám sát:</strong> Treo 40 bẫy dính xanh dương/vàng trên mỗi 1.000m² cao hơn ngọn 15cm.<br><strong>2. Sinh học:</strong> Phun chế phẩm <em>Metarhizium + Beauveria</em> phối hợp tinh dầu Neem ép lạnh vào chiều mát.<br><strong>3. Thiên địch:</strong> Thả nhện bắt mồi <em>Amblyseius swirskii</em>.',
        ],
        [
            'icon'          => '🕸️',
            'front_title'   => 'Nhện đỏ hai chấm (Tetranychus urticae)',
            'front_symptom' => 'Lá xuất hiện vô số chấm trắng vàng li ti như bụi cám, mặt dưới lá chăng tơ mịn, lá khô giòn rụng sớm trong điều kiện nắng nóng khô hạn (<60% ẩm độ).',
            'back_protocol' => '<strong>1. Vi khí hậu:</strong> Tăng ẩm hợp lý, tránh để nhà màng khô nóng cục bộ.<br><strong>2. Sinh học:</strong> Phun ướt đẫm mặt dưới lá bằng hoạt chất sinh học <em>Abamectin</em> hoặc <em>Matrine</em> thảo mộc, luân phiên dầu khoáng.<br><strong>3. Thiên địch:</strong> Nhện bắt mồi <em>Phytoseiulus persimilis</em>.',
        ],
    ];

    $vietgapAccordions = [
        [
            'title' => '1. Quy tắc 5 Đúng trong Ghi chép Nhật ký Điện tử VietGAP',
            'body'  => 'Mọi tác động vật tư lên lô canh tác phải được ghi nhận tức thì vào Sổ Nhật ký Số (hỗ trợ giọng nói trên Mầm Xanh LMS): <strong>Đúng mã lô — Đúng ngày giờ — Đúng tên chế phẩm sinh học — Đúng liều lượng pH/EC — Đúng thời gian cách ly (PHI)</strong> trước thu hoạch.',
        ],
        [
            'title' => '2. Quy trình Cấp & Dán Mã QR Truy xuất Nguồn gốc Lô Nông sản',
            'body'  => 'Sau khi khởi tạo mã lô (ví dụ <code>VG-LD-202611-0001</code>) tại phân hệ Nhật ký VietGAP, nông hộ kiểm tra đủ thời gian cách ly an toàn, thu hoạch vào sáng mát và in tem QR dán trực tiếp lên khay/thùng để siêu thị và người tiêu dùng quét kiểm tra minh bạch.',
        ],
        [
            'title' => '3. Kiểm soát Mối nguy Vi sinh, Kim loại nặng & Dư lượng BVTV',
            'body'  => 'Nguồn nước tưới nhỏ giọt phải xét nghiệm định kỳ E.coli/Coliforms; dụng cụ cắt thu hoạch phải khử trùng cồn thực phẩm 70°; tuyệt đối không dùng thuốc ngoài danh mục hoặc thu hoạch khi chưa hết ngày cách ly PHI.',
        ],
    ];

    return [
        // ===============================================================
        // KHÓA 1 (CORE #1): CHAMSOC01
        // ===============================================================
        [
            'shortname' => 'CHAMSOC01',
            'fullname'  => 'Kỹ thuật Chăm sóc Cây trồng Công nghệ cao & Dinh dưỡng Tự động',
            'summary'   => 'Khóa học cốt lõi trang bị trọn bộ kỹ thuật tuyển chọn cây giống sạch bệnh, kiểm soát pH/EC vùng rễ, vận hành hệ thống tưới nhỏ giọt châm phân tự động Fertigation A-B và phòng trừ sâu bệnh sinh học IPM chuẩn VietGAP.',
            'station'   => 'Trạm Thực nghiệm Nhà màng Châm phân Tự động Đà Lạt',
            'lat'       => '11.940419',
            'lng'       => '108.458313',
            'sec1_video' => [
                'code'              => 'CHAMSOC01',
                'duration'          => '12:45',
                'video_title'       => 'Quy trình Tuyển chọn Cây giống F1 & Kiểm soát Thang đo pH / EC Vùng rễ',
                'infographic_title' => 'Tiêu chuẩn Cây giống Khỏe & Thang đo Dinh dưỡng pH / EC',
                'chapters' => [
                    ['time' => '00:00 - 04:10', 'short' => 'Tiêu chuẩn cây giống', 'icon' => '🌱', 'title' => 'Tuyển chọn cây con bầu ươm rễ trắng khỏe sạch bệnh', 'desc' => 'Cây giống đạt chuẩn xuất vườn có thân mập thẳng, đốt ngắn, 4–5 lá thật xanh bóng không dị dạng, bộ rễ trắng ôm chặt giá thể xơ dừa/mụn dừa đã xử lý sạch nấm bệnh và tanin (EC giá thể < 0.5 mS/cm).'],
                    ['time' => '04:11 - 08:30', 'short' => 'Đo pH & EC thực địa', 'icon' => '🧪', 'title' => 'Hiệu chuẩn bút đo và kiểm tra nước rỉ (Drainage 20-30%)', 'desc' => 'Đo đồng thời dung dịch đầu vào tại béc nhỏ giọt và nước thoát đáy bầu. Nếu EC nước thoát cao hơn đầu vào quá 0.8 mS/cm, cần tưới rửa muối tích tụ bằng nước sạch pH 6.0.'],
                    ['time' => '08:31 - 12:45', 'short' => 'Điều chỉnh theo nắng', 'icon' => '☀️', 'title' => 'Điều tiết EC linh hoạt theo bức xạ mặt trời & nhiệt độ', 'desc' => 'Ngày nắng gắt nhiệt độ >32°C cây hút nhiều nước hơn muối khoáng nên cần giảm EC 0.2–0.3 mS/cm và chia thành nhiều chu kỳ tưới ngắn để ngừa cháy mép lá thiếu Canxi.'],
                ],
                'infographic_cards' => [
                    ['icon' => '🌱', 'title' => 'Chuẩn Cây Giống Xuất Vườn', 'body' => '• Chiều cao đồng đều 12–15 cm<br>• Bầu rễ trắng kín, không nâu thối<br>• Đã huấn luyện nắng 3–5 ngày trước trồng<br>• Sạch 100% bọ trĩ, bọ phấn & virus'],
                    ['icon' => '⚖️', 'title' => 'Ngưỡng Vàng pH 5.8 – 6.2', 'body' => '• pH &lt; 5.2: Ngộ độc Mn/Al, kém hút Ca/Mg<br>• <strong>pH 5.8 – 6.2: Hấp thu tối ưu 12 khoáng</strong><br>• pH &gt; 6.8: Kết tủa Sắt (Fe), Kẽm, Lân gây vàng lá ngọn'],
                    ['icon' => '⚡', 'title' => 'Quản lý Tỷ lệ Thoát Nước 20%', 'body' => '• Thu mẫu nước rỉ sau tưới 30 phút<br>• Tỷ lệ rỉ chuẩn: <strong>15% – 25%</strong> lượng tưới<br>• Ngăn mặn hóa vùng rễ & cân bằng oxy rễ'],
                ],
                'scale_bars' => [
                    ['label' => 'Rau ăn lá (Xà lách, Cải ngọt, Cải bó xôi)', 'range' => 'pH 5.8–6.2 | EC 1.2 – 1.8 mS/cm', 'pct' => 55, 'color' => '#43A047'],
                    ['label' => 'Dưa lưới & Dưa leo Nhà màng (Nuôi trái)', 'range' => 'pH 6.0–6.5 | EC 2.2 – 2.8 mS/cm', 'pct' => 82, 'color' => '#2E7D32'],
                    ['label' => 'Cà chua Beef & Ớt chuông Công nghệ cao', 'range' => 'pH 5.8–6.3 | EC 2.4 – 3.0 mS/cm', 'pct' => 90, 'color' => '#1B4332'],
                ],
            ],
            'sec2_slides' => [
                ['tag' => 'Tổng quan Fertigation', 'title' => '1. Nguyên lý Tưới Nhỏ giọt Kết hợp Châm phân Tự động (Fertigation)', 'body' => 'Fertigation đưa nước và ion khoáng hòa tan trực tiếp tới vùng rễ hoạt động tích cực theo từng chu kỳ nhỏ trong ngày, giúp <strong>tiết kiệm 40–55% lượng phân bón</strong>, giảm 60% nước tưới và giữ ẩm độ không khí nhà màng luôn thấp để hạn chế nấm bệnh.', 'svg' => $svgFertigation],
                ['tag' => 'Quy tắc Bồn A - B', 'title' => '2. Quy tắc Bất di Bất dịch khi Pha Dung dịch Mẹ Bồn A & Bồn B', 'body' => '<ul><li><strong>Bồn A:</strong> Chứa Canxi Nitrat <code>Ca(NO3)2</code>, Kali Nitrat và Sắt Chelate <code>Fe-EDDHA</code>.</li><li><strong>Bồn B:</strong> Chứa Lân <code>MKP (KH2PO4)</code>, Magie Sunfat <code>MgSO4</code> và nhóm Vi lượng (Mn, Zn, B, Cu, Mo).</li><li><strong>Cấm kỵ tuyệt đối:</strong> Không bao giờ trộn trực tiếp dung dịch đặc A và B vì <code>Ca2+</code> gặp <code>SO4(2-)</code> và <code>PO4(3-)</code> tạo kết tủa thạch cao trắng làm tắc hoàn toàn béc nhỏ giọt!</li></ul>', 'quiz' => [
                    'question' => 'Nếu đổ chung Canxi Nitrat (Bồn A) đậm đặc trực tiếp vào Magie Sunfat (Bồn B) sẽ xảy ra hiện tượng gì?',
                    'options'  => ['A. Tăng gấp đôi hiệu lực phân bón', 'B. Phản ứng kết tủa CaSO4 (thạch cao) gây mất dinh dưỡng và tắc béc tưới', 'C. Tự động trung hòa pH về 6.0'],
                    'answer'   => 1,
                    'explain'  => 'Ion Canxi (Ca2+) phản ứng ngay với gốc Sunfat (SO4 2-) tạo kết tủa CaSO4 không tan làm nghẹt lọc và đầu nhỏ giọt.',
                ]],
                ['tag' => 'Lọc & Áp suất', 'title' => '3. Hệ thống Lọc Đĩa 120 Mesh & Đầu Nhỏ giọt Bù áp (PC Dripper)', 'body' => 'Sử dụng lọc đĩa tối thiểu <strong>120–150 mesh</strong> kết hợp van xả khí và đồng hồ áp suất (1.2 – 2.0 bar). Đầu nhỏ giọt bù áp giúp cây đầu hàng và cây cuối hàng dài 50m nhận chính xác cùng lưu lượng <strong>2.0 Lít/giờ</strong> (sai số < 5%).'],
                ['tag' => 'Chiến lược Chu kỳ Tưới', 'title' => '4. Lập lịch Tưới Xung (Pulse Irrigation) Theo Thời điểm Trong ngày', 'body' => '<ul><li><strong>07:30 – 09:00:</strong> Chu kỳ khởi động đánh thức bộ rễ sau đêm.</li><li><strong>09:30 – 14:30:</strong> Chia 4–6 lần tưới ngắn (mỗi lần 5–8 phút) bù thoát hơi nước đỉnh nắng.</li><li><strong>Trước 15:30 chiều:</strong> Ngưng tưới để giá thể ráo bớt, giàu oxy cho rễ hô hấp ban đêm và tránh nứt trái.</li></ul>'],
                ['tag' => 'Cắt tỉa & Treo dây', 'title' => '5. Kỹ thuật Tỉa Nhánh Phụ, Khoanh Gốc Hạ Thân & Thụ phấn Ong Nghệ', 'body' => 'Đối với Cà chua/Dưa lưới nhà màng: tỉa chồi nách lúc sáng nắng ráo để vết thương nhanh khô mủ; giữ mật độ lá tối ưu (14–16 lá/cây) đảm bảo thông thoáng gốc và tối đa hóa quang hợp nuôi trái.'],
                ['tag' => 'Chẩn đoán Dinh dưỡng', 'title' => '6. Phân biệt Thiếu Khoáng Đa - Trung - Vi lượng Trên Lá', 'body' => '<ul><li><strong>Lá già vàng trước:</strong> Nhóm di động (Đạm N, Lân P, Kali K, Magie Mg).</li><li><strong>Lá non/đọt biến dạng trước:</strong> Nhóm kém di động (Canxi Ca gây cháy chóp lá/thối đít trái; Sắt Fe gây vàng thịt lá gân vẫn xanh; Bo B gây giòn gãy ngọn).</li></ul>', 'quiz' => [
                    'question' => 'Hiện tượng thối đen đít trái cà chua/ớt chuông và cháy mép lá non xà lách (Tipburn) chủ yếu do thiếu nguyên tố nào tại mô non?',
                    'options'  => ['A. Đạm (N)', 'B. Canxi (Ca) do EC quá cao hoặc ẩm độ/nhiệt độ bất lợi cản trở vận chuyển', 'C. Kali (K)'],
                    'answer'   => 1,
                    'explain'  => 'Canxi di chuyển theo dòng thoát hơi nước; khi nắng gắt hoặc EC vùng rễ quá cao, Canxi không lên kịp đỉnh sinh trưởng và chóp trái.',
                ]],
                ['tag' => 'Tổng kết VietGAP', 'title' => '7. Quy trình Xúc rửa Đường ống Định kỳ & Vệ sinh Cuối vụ', 'body' => 'Mỗi 2 tuần mở van cuối hàng xả cặn đường ống nhánh; cuối vụ ngâm rửa hệ thống bằng dung dịch axit nhẹ/H2O2 sinh học để loại bỏ màng biofilm và muối bám, sẵn sàng cho vụ canh tác mới đạt năng suất cao nhất.'],
            ],
            'sec3_cards'      => $standardIpmCards,
            'sec3_accordions' => $vietgapAccordions,
            'questions' => [
                ['Khoảng pH dung dịch dinh dưỡng tối ưu giúp rễ cây trồng giá thể/nhà màng hấp thu cân đối đa-trung-vi lượng là bao nhiêu?', ['5.8 – 6.2', '3.5 – 4.2', '7.5 – 8.5', '8.5 – 9.2'], 0],
                ['Vì sao tuyệt đối KHÔNG pha chung đậm đặc phân Canxi Nitrat (Bồn A) với phân chứa gốc Sunfat/Photphat (Bồn B)?', ['Gây kết tủa CaSO4 / Ca3(PO4)2 làm mất dinh dưỡng và tắc béc nhỏ giọt', 'Làm dung dịch bay hơi hết nước', 'Làm giảm nhiệt độ nhà màng xuống 0°C', 'Không có ảnh hưởng gì'], 0],
                ['Khi trời nắng gắt nhiệt độ >33°C, người trồng cần điều chỉnh chế độ tưới Fertigation như thế nào là đúng kỹ thuật?', ['Chia nhiều lần tưới ngắn hơn và giảm nhẹ EC 0.2–0.3 mS/cm để tránh sốc mặn vùng rễ', 'Tăng gấp đôi EC lên 5.0 mS/cm', 'Ngừng tưới hoàn toàn từ sáng đến tối', 'Chỉ tưới 1 lần duy nhất lúc 21h đêm'], 0],
                ['Tỷ lệ nước rỉ thoát đáy bầu giá thể (Drainage %) tiêu chuẩn trong canh tác nhà màng nhằm ngăn tích tụ muối là bao nhiêu?', ['15% – 25%', '0% (không cho chảy giọt nào)', '80% – 95%', '100%'], 0],
                ['Triệu chứng cháy khô mép lá non xà lách (Tipburn) và thối đít trái cà chua/dưa lưới liên quan trực tiếp đến thiếu hụt nguyên tố nào?', ['Canxi (Ca)', 'Đạm (N)', 'Clo (Cl)', 'Natri (Na)'], 0],
                ['Bộ lọc đĩa trong hệ thống tưới nhỏ giọt công nghệ cao cần cấp độ mịn tối thiểu bao nhiêu để bảo vệ đầu nhỏ giọt bù áp?', ['120 – 150 mesh', '10 mesh', '20 mesh', 'Không cần lắp bộ lọc'], 0],
                ['Biện pháp giám sát và phòng trừ sinh học hiệu quả nhất đối với Bọ trĩ (Thrips) hại đọt non trong nhà màng là gì?', ['Treo bẫy dính xanh dương/vàng kết hợp phun nấm ký sinh Beauveria/Metarhizium và dầu Neem', 'Phun thuốc diệt cỏ liều cao', 'Đóng kín toàn bộ quạt thông gió tăng nhiệt lên 48°C', 'Tưới nước mặn vào ngọn cây'], 0],
                ['Để xử lý lớp sáp trắng bảo vệ trên cơ thể Rệp sáp theo hướng an toàn VietGAP, nên phối hợp vật tư sinh học nào?', ['Dầu khoáng sinh học 0.3% kết hợp nấm trắng Beauveria bassiana và chặn kiến cộng sinh', 'Bón thật nhiều phân Urê vào lá', 'Tưới nước đá lạnh buổi trưa', 'Chỉ phun nước lã không cần vệ sinh cành'], 0],
                ['Nấm đối kháng Trichoderma spp. phát huy hiệu quả tốt nhất khi xử lý bệnh nào vùng rễ?', ['Phòng trừ nấm đất Phytophthora, Fusarium, Pythium gây lở cổ rễ và thối rễ', 'Trị sâu đục quả trưởng thành bay trên ngọn', 'Thay thế hoàn toàn phân Kali', 'Làm chín ép trái cây trong 2 giờ'], 0],
                ['Vì sao nên kết thúc chu kỳ tưới nhỏ giọt cuối cùng trong ngày trước 15:30–16:00 chiều đối với dưa lưới/cà chua?', ['Để giá thể thông thoáng đủ oxy cho rễ ban đêm, hạn chế ẩm độ cao gây nấm và nứt trái', 'Vì máy bơm không hoạt động buổi chiều', 'Để tăng EC vùng rễ lên 10 mS/cm', 'Để lá cây héo rũ qua đêm'], 0],
                ['Theo quy định VietGAP, chỉ số PHI (Pre-Harvest Interval) trên nhật ký canh tác có ý nghĩa là gì?', ['Thời gian cách ly an toàn từ lần phun cuối cùng đến khi được phép thu hoạch', 'Tổng diện tích nhà màng tính bằng ha', 'Áp suất đường ống chính tính bằng bar', 'Số lượng béc tưới trên một hàng'], 0],
                ['Tiêu chuẩn cây giống khỏe trước khi đem trồng ra nhà màng công nghệ cao gồm đặc điểm nào sau đây?', ['Thân mập thẳng, bầu rễ trắng phát triển mạnh, sạch sâu bệnh và đã huấn luyện thích nghi ánh sáng', 'Rễ thâm nâu có mùi chua và lá non xoăn vàng', 'Cây vươn lóng dài yếu ớt do thiếu sáng', 'Giá thể nhiễm mặn EC > 4.0 mS/cm'], 0],
            ],
        ],

        // ===============================================================
        // KHÓA 2 (CORE #2): CHEBIEN01
        // ===============================================================
        [
            'shortname' => 'CHEBIEN01',
            'fullname'  => 'Công nghệ Sơ chế, Chế biến Sâu & Bảo quản Lạnh Nông sản Xuất khẩu',
            'summary'   => 'Làm chủ chuỗi giá trị sau thu hoạch: Hạ nhiệt đồng ruộng (Pre-cooling) trong 2 giờ vàng, công nghệ sấy lạnh bơm nhiệt giữ nguyên dưỡng chất, đóng gói khí quyển biến đổi MAP/CA, kho lạnh thông minh và cấp mã QR VietGAP.',
            'station'   => 'Xưởng Sơ chế Đóng gói & Kho lạnh Chuẩn Xuất khẩu Mầm Xanh',
            'lat'       => '11.936200',
            'lng'       => '108.462100',
            'sec1_video' => [
                'code'              => 'CHEBIEN01',
                'duration'          => '14:10',
                'video_title'       => 'Quy trình Thu hoạch Đúng Độ Chín & Hạ nhiệt Sơ bộ (Pre-Cooling) Trong 2 Giờ Vàng',
                'infographic_title' => 'Chuỗi Lạnh Liên Tục (Cold Chain) Giảm Tổn Thất Sau Thu Hoạch Từ 25% Xuống < 5%',
                'chapters' => [
                    ['time' => '00:00 - 04:30', 'short' => 'Độ chín & Thu hoạch', 'icon' => '🧺', 'title' => 'Xác định độ đường Brix & thu hoạch khung giờ mát 06:00–08:30', 'desc' => 'Sử dụng khúc xạ kế đo độ Brix (VD: Dưa lưới >= 14°Brix, Sầu riêng chất khô >= 32%), cắt cuống bằng kéo khử trùng cồn 70°, xếp nhẹ vào sọt nhựa lót xốp thoáng khí tránh dập cơ học.'],
                    ['time' => '04:31 - 09:20', 'short' => 'Pre-cooling 2 giờ vàng', 'icon' => '❄️', 'title' => 'Hạ nhiệt đồng ruộng (Field Heat Removal) bằng cưỡng bức gió lạnh / chân không', 'desc' => 'Cứ mỗi giờ chậm làm mát sau cắt, rau ăn lá mất tương đương 1 ngày tuổi thọ kệ hàng! Đưa nông sản vào buồng Pre-cooling trong vòng 2 giờ đầu để rút nhiệt tâm sản phẩm về ngưỡng chuẩn.'],
                    ['time' => '09:21 - 14:10', 'short' => 'Sục Ozone & Đóng gói', 'icon' => '📦', 'title' => 'Rửa sục khí vi bọt, ly tâm ráo nước và đóng gói phòng lạnh 15°C', 'desc' => 'Loại bỏ hoàn toàn nước tự do đọng trên bề mặt lá/vỏ trái trước khi đóng màng vi đục lỗ MAP giúp ngăn thối nhũn vi khuẩn Erwinia trong suốt hành trình vận chuyển lạnh.'],
                ],
                'infographic_cards' => [
                    ['icon' => '⏱️', 'title' => 'Quy tắc 2 Giờ Vàng', 'body' => '• Hạ nhiệt đồng ruộng ngay trong <strong>&lt; 120 phút</strong><br>• Giảm tốc độ hô hấp và mất nước gấp 4–6 lần<br>• Kéo dài thời hạn tươi từ 3 ngày lên 14–21 ngày'],
                    ['icon' => '🌡️', 'title' => 'Phân Nhóm Nhiệt Độ Kho Lạnh', 'body' => '• Rau ăn lá, Dâu tây: <strong>2 – 5°C</strong> (RH 90–95%)<br>• Ớt chuông, Dưa lưới: <strong>7 – 10°C</strong><br>• Trái nhiệt đới (Chuối, Xoài, Sầu riêng tươi): <strong>13 – 15°C</strong> (Tránh tổn thương lạnh!)'],
                    ['icon' => '🧊', 'title' => 'Cấp Đông Nhanh IQF (-35°C)', 'body' => '• Tinh thể băng cực mịn không phá vỡ màng tế bào<br>• Múi sầu riêng / xoài rã đông không chảy dịch<br>• Trữ đông ổn định 18–24 tháng ở <strong>-18°C</strong>'],
                ],
                'scale_bars' => [
                    ['label' => 'Rau ăn lá & Hoa cắt cành (Làm mát chân không / gió cưỡng bức)', 'range' => '2°C – 5°C | Ẩm độ 92–95%', 'pct' => 25, 'color' => '#0288D1'],
                    ['label' => 'Quả Á nhiệt đới (Ớt chuông, Cà chua chín tới, Dưa lưới)', 'range' => '8°C – 12°C | Ẩm độ 85–90%', 'pct' => 52, 'color' => '#2E7D32'],
                    ['label' => 'Sấy Lạnh Bơm Nhiệt Giữ Dưỡng Chất (Trà hoa, Trái cây dẻo)', 'range' => '25°C – 42°C | Ẩm độ buồng sấy 15%', 'pct' => 78, 'color' => '#F57C00'],
                ],
            ],
            'sec2_slides' => [
                ['tag' => 'Sinh lý Sau thu hoạch', 'title' => '1. Kiểm soát Hô hấp & Khí Ethylene Nội sinh Sau Thu hoạch', 'body' => 'Nông sản sau khi rời cây mẹ vẫn là cơ thể sống đang hô hấp tiêu hao đường và sinh nhiệt. Nhóm trái <strong>hô hấp đột biến (Climacteric)</strong> như Sầu riêng, Chuối, Xoài, Cà chua sinh nhiều khí Ethylene làm chín nhanh — tuyệt đối không xếp chung kho với rau ăn lá hoặc dưa leo nhạy cảm Ethylene!', 'svg' => $svgColdChain],
                ['tag' => 'Pre-Cooling', 'title' => '2. So sánh 4 Phương pháp Làm mát Sơ bộ (Pre-Cooling)', 'body' => '<ul><li><strong>Phòng lạnh tĩnh (Room Cooling):</strong> Chậm, chỉ phù hợp củ quả vỏ dày.</li><li><strong>Gió lạnh cưỡng bức (Forced-Air Cooling):</strong> Nhanh gấp 6 lần, lý tưởng cho dâu tây, nho, ớt chuông, trái cây.</li><li><strong>Làm mát chân không (Vacuum Cooling):</strong> Hạ rau ăn lá từ 28°C xuống 3°C chỉ trong 25 phút!</li><li><strong>Nước đá lạnh (Hydro-cooling):</strong> Dùng cho măng tây, cà rốt, ngô ngọt.</li></ul>'],
                ['tag' => 'Bao bì MAP & Kho CA', 'title' => '3. Công nghệ Bảo quản Khí quyển Biến đổi (MAP) & Kiểm soát (CA)', 'body' => 'Hạ nồng độ Oxy xuống <strong>3% – 5%</strong> và duy trì CO2 ở mức <strong>5% – 8%</strong> kết hợp túi hấp thụ khí Ethylene (<code>KMnO4</code>) giúp đưa trái cây/rau quả vào trạng thái "ngủ đông sinh học", ức chế nấm mốc mà không cần hóa chất bảo quản.', 'quiz' => [
                    'question' => 'Điều gì xảy ra nếu bảo quản trái cây nhiệt đới tươi (như chuối, xoài, sầu riêng nguyên quả) ở nhiệt độ quá thấp 1–2°C?',
                    'options'  => ['A. Trái tươi lâu gấp 10 lần không đổi màu', 'B. Bị tổn thương lạnh (Chilling Injury): thâm đen vỏ, sượng cơm, không chín đều khi đưa ra ngoài', 'C. Tăng độ đường Brix lên gấp đôi'],
                    'answer'   => 1,
                    'explain'  => 'Trái cây nhiệt đới nhạy cảm lạnh dưới 10–12°C sẽ bị phá hủy màng tế bào gây thâm vỏ và rối loạn chín (Chilling Injury).',
                ]],
                ['tag' => 'Sấy Lạnh Bơm Nhiệt', 'title' => '4. Công nghệ Sấy Lạnh Bơm Nhiệt (Heat-Pump Drying 25–42°C)', 'body' => 'Khác với sấy nhiệt truyền thống (65–80°C làm sẫm màu và phân hủy Vitamin C, enzyme), <strong>sấy lạnh bơm nhiệt</strong> tách ẩm không khí khô ở nhiệt độ thấp <strong>25–42°C</strong> trong buồng kín tuần hoàn, giữ nguyên 95% màu sắc tự nhiên, hương thơm tinh dầu và hoạt chất sinh học.'],
                ['tag' => 'Sấy Thăng Hoa & IQF', 'title' => '5. Sấy Thăng hoa Đông khô (Freeze Drying) & Cấp đông Siêu tốc IQF', 'body' => '<ul><li><strong>IQF (-35°C đến -40°C):</strong> Cấp đông rời từng múi sầu riêng, dâu tây trong 15–25 phút.</li><li><strong>Sấy thăng hoa:</strong> Cấp đông sâu rồi hút chân không để tinh thể đá thăng hoa thẳng thành hơi, tạo cấu trúc giòn xốp cao cấp giá trị gia tăng gấp 5–8 lần nông sản thô.</li></ul>', 'quiz' => [
                    'question' => 'Ưu điểm vượt trội của cấp đông siêu tốc IQF (-35°C) so với tủ đông gia dụng làm đông chậm là gì?',
                    'options'  => ['A. Tạo tinh thể băng siêu nhỏ không làm rách vách tế bào, khi rã đông giữ nguyên cấu trúc và không chảy dịch', 'B. Không cần dùng điện', 'C. Làm chín sầu riêng xanh thành sầu riêng chín'],
                    'answer'   => 0,
                    'explain'  => 'Làm đông chậm tạo tinh thể đá lớn sắc nhọn đâm thủng tế bào làm nhũn nát nông sản khi rã đông; IQF tạo vi tinh thể bảo toàn cấu trúc.',
                ]],
                ['tag' => 'Vệ sinh HACCP / ISO', 'title' => '6. Thiết kế Xưởng Sơ chế Một Chiều Chuẩn HACCP & ISO 22000', 'body' => 'Bố trí luồng di chuyển <strong>1 chiều không giao cắt</strong>: Khu tiếp nhận nguyên liệu bẩn → Khu phân loại & rửa sục khí → Phòng ráo lạnh sạch → Phòng đóng gói & dò kim loại → Kho lạnh thành phẩm xuất hàng.'],
                ['tag' => 'Truy xuất Số GS1/QR', 'title' => '7. Định danh Lô Đóng gói, DataLogger Nhiệt độ & Mã QR Xuất khẩu', 'body' => 'Mỗi container/lô hàng gắn thiết bị ghi nhiệt độ tự động <strong>DataLogger USB/IoT</strong> suốt tuyến đường biển và liên kết trực tiếp với mã QR lô VietGAP trên hệ thống Mầm Xanh LMS.'],
            ],
            'sec3_cards'      => $standardIpmCards,
            'sec3_accordions' => $vietgapAccordions,
            'questions' => [
                ['Mục đích cốt lõi của công đoạn làm mát sơ bộ (Pre-cooling) trong vòng 2 giờ đầu sau thu hoạch là gì?', ['Nhanh chóng rút nhiệt đồng ruộng, giảm cường độ hô hấp và sự mất nước của nông sản', 'Làm chín nhanh toàn bộ lô hàng trong 1 giờ', 'Tăng hàm lượng nước tự do đọng trên mặt lá', 'Làm đông cứng rau ăn lá ở -20°C'], 0],
                ['Phương pháp làm mát sơ bộ nào giúp hạ nhiệt rau ăn lá (xà lách, cải bó xôi) từ 28°C xuống 3°C nhanh nhất (chỉ 20–30 phút)?', ['Làm mát chân không (Vacuum Cooling)', 'Phơi ngoài sân nắng', 'Ủ kín trong bao tải dứa', 'Đặt cạnh lò sấy nhiệt'], 0],
                ['Nhiệt độ và ẩm độ kho mát tiêu chuẩn để bảo quản tươi nhóm rau ăn lá ôn đới (xà lách, cải thảo, bông cải) là bao nhiêu?', ['2°C – 5°C và ẩm độ 90% – 95%', '22°C – 28°C và ẩm độ 40%', '-18°C đóng băng hoàn toàn', '35°C và ẩm độ 99%'], 0],
                ['Vì sao không được bảo quản trái cây nhiệt đới tươi nguyên quả (xoài, chuối, sầu riêng tươi) ở nhiệt độ quá thấp 0–4°C?', ['Gây hiện tượng tổn thương lạnh (Chilling Injury) làm thâm đen vỏ và sượng thịt quả', 'Làm trái nổ vỡ ngay lập tức', 'Làm tăng trọng lượng trái lên 200%', 'Vì kho lạnh không thể chỉnh dưới 15°C'], 0],
                ['Nguyên lý chính của bao bì khí quyển biến đổi MAP (Modified Atmosphere Packaging) trong bảo quản rau quả tươi là gì?', ['Giảm nồng độ O2 (3–5%) và nâng nhẹ CO2 (5–8%) để làm chậm hô hấp và ức chế vi sinh gây hư hỏng', 'Bơm 100% khí Ethylene vào túi để thúc chín', 'Hút kiệt tạo áp suất nén nát lá rau', 'Bơm không khí nóng 60°C vào bao bì'], 0],
                ['Công nghệ sấy lạnh bơm nhiệt (Heat-Pump Drying) vận hành ở dải nhiệt độ nào để bảo toàn màu sắc và Vitamin?', ['25°C – 42°C với không khí tách ẩm khô tuần hoàn kín', '120°C – 180°C bằng than đá', '95°C – 110°C ngoài trời', '-40°C không tách ẩm'], 0],
                ['Tại sao không nên xếp chung kho lạnh giữa các trái sinh nhiều khí Ethylene (chuối chín, sầu riêng) với rau ăn lá hoặc dưa leo?', ['Khí Ethylene kích thích vàng lá, rụng cuống và làm già hóa hư hỏng nhanh rau ăn lá', 'Làm kho lạnh tiêu tốn ít điện hơn', 'Làm dưa leo ngọt thành dưa lưới', 'Không có bất kỳ tác động sinh lý nào'], 0],
                ['Công nghệ cấp đông siêu tốc IQF (-35°C đến -40°C) giúp múi sầu riêng xuất khẩu đạt chất lượng vượt trội nhờ cơ chế nào?', ['Hình thành vi tinh thể băng cực nhỏ không phá vỡ màng tế bào, không chảy dịch khi rã đông', 'Rút hết toàn bộ hương thơm của sầu riêng', 'Biến tinh bột thành chất xơ gỗ', 'Không cần bảo quản lạnh sau cấp đông'], 0],
                ['Nguyên tắc thiết kế mặt bằng nhà xưởng sơ chế nông sản đạt chuẩn HACCP / ISO 22000 là gì?', ['Nguyên tắc dòng chảy 1 chiều từ khu nguyên liệu sang khu sạch đóng gói, không nhiễm chéo', 'Nhập nguyên liệu và xuất thành phẩm chung một bàn sơ chế đất', 'Đặt kho thuốc BVTV ngay trong phòng đóng gói', 'Không cần khử trùng dao kéo thu hoạch'], 0],
                ['Trước khi đóng gói rau ăn lá vào túi màng MAP, yêu cầu bắt buộc đối với bề mặt lá sau khi rửa là gì?', ['Phải làm ráo hoàn toàn nước tự do đọng trên lá (ly tâm/quạt lạnh) để ngừa thối nhũn vi khuẩn', 'Để nguyên nước ướt sũng trong túi kín', 'Phun nước đường lên mặt lá', 'Phơi nắng trưa 35°C trong 2 giờ'], 0],
                ['Thiết bị nào bắt buộc gắn trong container lạnh xuất khẩu đường biển để minh chứng chuỗi lạnh không bị đứt gãy?', ['Thiết bị ghi hành trình nhiệt độ - ẩm độ tự động (DataLogger)', 'Bình tưới cây cầm tay', 'Bẫy dính côn trùng màu vàng', 'Bút thử độ mặn đất'], 0],
                ['Thông tin nào cần hiển thị công khai khi người tiêu dùng quét mã QR truy xuất nguồn gốc trên bao bì nông sản VietGAP?', ['Mã lô sản xuất, tên nông hộ & tọa độ vùng trồng, lịch sử nhật ký canh tác, ngày thu hoạch & chuẩn an toàn', 'Mật khẩu quản trị hệ thống của nông trại', 'Số tài khoản cá nhân của tài xế', 'Chỉ hiển thị một bức ảnh tĩnh không có dữ liệu lô'], 0],
            ],
        ],

        // ===============================================================
        // KHÓA 3: IOTNHAMANG01
        // ===============================================================
        [
            'shortname' => 'IOTNHAMANG01',
            'fullname'  => 'Vận hành Nhà màng Thông minh & Trạm Cảm biến Vi khí hậu IoT',
            'summary'   => 'Làm chủ hệ thống cảm biến nhiệt-ẩm-ánh sáng PAR-CO2, chỉ số thiếu hụt áp suất hơi nước VPD, điều khiển tự động lưới cắt nắng, quạt đối lưu, màn mưa Cooling Pad và cảnh báo sự cố qua điện thoại.',
            'station'   => 'Trung tâm Nhà màng Tự động hóa IoT Công nghệ cao',
            'lat'       => '11.948100',
            'lng'       => '108.451200',
            'sec1_video' => [
                'code'              => 'IOTNHAMANG01',
                'duration'          => '11:30',
                'video_title'       => 'Lắp đặt Trạm Cảm biến IoT & Đọc Hiểu Chỉ số VPD (Vapor Pressure Deficit)',
                'infographic_title' => 'Tam Giác Vi Khí Hậu Nhà Màng: Nhiệt độ — Ẩm độ — Bức xạ PAR',
                'chapters' => [
                    ['time' => '00:00 - 03:50', 'short' => 'Vị trí đặt cảm biến', 'icon' => '📡', 'title' => 'Treo hộp chắn bức xạ cảm biến đúng tầng tán lá ngọn', 'desc' => 'Đặt cảm biến nhiệt-ẩm trong hộp lá sách thông gió (Shield) ngay độ cao ngọn cây đang phát triển, tránh nắng chiếu trực tiếp lên đầu dò gây sai số +4°C.'],
                    ['time' => '03:51 - 07:40', 'short' => 'Chỉ số vàng VPD', 'icon' => '🌿', 'title' => 'Điều hành khí khổng mở tối ưu ở VPD 0.8 – 1.2 kPa', 'desc' => 'VPD quá thấp (<0.4 kPa) cây ngừng thoát hơi nước dễ nhiễm nấm sương mai; VPD quá cao (>1.6 kPa) cây đóng khí khổng ngừng quang hợp!'],
                    ['time' => '07:41 - 11:30', 'short' => 'Kịch bản rơ-le tự động', 'icon' => '⚙️', 'title' => 'Liên động tự động Lưới cắt nắng 50% + Quạt đối lưu + Phun sương cao áp', 'desc' => 'Cài đặt ngưỡng trễ (Hysteresis) thông minh để mô-tơ rèm và quạt không bật/tắt liên tục gây quá tải điện.'],
                ],
                'infographic_cards' => [
                    ['icon' => '🌡️', 'title' => 'VPD Chuẩn 0.8 – 1.2 kPa', 'body' => '• Cây con: <strong>0.6 – 0.8 kPa</strong><br>• Sinh trưởng &amp; Nuôi trái: <strong>0.8 – 1.2 kPa</strong><br>• Ngừa nấm &amp; tối ưu hút Canxi'],
                    ['icon' => '☀️', 'title' => 'Cường độ Sáng PAR / Lux', 'body' => '• Đóng lưới cắt nắng khi bức xạ &gt; 45.000 Lux<br>• Mở lưới đón nắng sớm 06:30 – 09:30<br>• Giữ màng PE sạch bụi truyền sáng &gt;88%'],
                    ['icon' => '🌬️', 'title' => 'Thông gió &amp; Lưới Chắn Côn trùng', 'body' => '• Lưới vách <strong>50 mesh</strong> chặn bọ trĩ, bọ phấn<br>• Buồng đệm cửa đôi (Double-door airlock)<br>• Khay sát trùng giày dép đầu cửa'],
                ],
                'scale_bars' => [
                    ['label' => 'VPD Quá Thấp (< 0.45 kPa) — Nguy cơ Nấm bệnh & Đọng sương', 'range' => 'Cần bật quạt đối lưu thông gió', 'pct' => 28, 'color' => '#0288D1'],
                    ['label' => 'VPD Tối Ưu (0.80 – 1.25 kPa) — Quang hợp & Hút Khoáng Đỉnh Cao', 'range' => 'Vùng vận hành lý tưởng', 'pct' => 70, 'color' => '#2E7D32'],
                    ['label' => 'VPD Quá Cao (> 1.60 kPa) — Đóng Khí khổng & Cháy Chóp Lá', 'range' => 'Cần kéo lưới cắt nắng / phun sương', 'pct' => 92, 'color' => '#D32F2F'],
                ],
            ],
            'sec2_slides' => [
                ['tag' => 'Kiến trúc IoT', 'title' => '1. Kiến trúc Trạm Điều khiển Nhà màng IoT (Cảm biến → Gateway → Cloud)', 'body' => 'Kết nối cảm biến RS485 Modbus công nghiệp (Nhiệt độ, Ẩm độ, Ánh sáng PAR, CO2, EC/pH đất) về tủ PLC/ESP32 công nghiệp có pin dự phòng và cảnh báo tức thì khi mất điện hoặc quá nhiệt.', 'svg' => $svgFertigation],
                ['tag' => 'Cửa Đôi Cách Ly', 'title' => '2. Quy chuẩn Buồng Đệm 2 Cửa (Double-Door) & Lưới Chắn 50 Mesh', 'body' => 'Tuyệt đối không mở đồng thời 2 cửa buồng đệm! Lưới chắn côn trùng 50 mesh ngăn bọ trĩ/bọ phấn xâm nhập nhưng làm giảm ~30% lưu lượng gió tự nhiên, do đó bắt buộc bố trí quạt đối lưu cưỡng bức.'],
                ['tag' => 'Quản lý VPD', 'title' => '3. Điều khiển VPD Thay vì Chỉ Nhìn Nhiệt độ Đơn thuần', 'body' => 'Cùng nhiệt độ 28°C nhưng nếu ẩm độ 90% (VPD = 0.38 kPa) cây không thoát hơi nước được; nếu ẩm độ 65% (VPD = 1.32 kPa) cây trao đổi chất cực mạnh.', 'quiz' => [
                    'question' => 'Khi cảm biến nhà màng báo lúc 06:00 sáng: Nhiệt độ 19°C, Ẩm độ 94% (VPD = 0.13 kPa), hành động tự động nào cần kích hoạt?',
                    'options'  => ['A. Phun sương ướt đẫm thêm trong nhà màng', 'B. Bật quạt đối lưu nhẹ và hé cửa mái thông gió đẩy ẩm sương mù ra ngoài', 'C. Đóng kín mít mọi cửa và tắt quạt'],
                    'answer'   => 1,
                    'explain'  => 'Ẩm độ 94% gây đọng giọt trên lá làm nảy mầm bào tử nấm; bật quạt đối lưu và thông gió giúp nâng VPD về ngưỡng an toàn.',
                ]],
                ['tag' => 'Lưới Cắt Nắng', 'title' => '4. Vận hành Rèm Cắt Nắng Tự động Theo Cảm biến Bức xạ', 'body' => 'Cài đặt kéo lưới cắt nắng tự động khi bức xạ vượt 750 W/m² (hoặc >45.000 Lux) giữa trưa và thu lưới ngay khi trời dịu mát sau 15:00 để cây không bị vươn lóng thiếu sáng.'],
                ['tag' => 'Làm mát Bay hơi', 'title' => '5. Tổ hợp Quạt Hút Áp suất Âm & Tấm Làm mát Cooling Pad / Fogging', 'body' => 'Phun sương cao áp (70 bar, hạt sương <10 micron bay hơi ngay trong không trung không làm ướt lá) giúp hạ 4–6°C trong những trưa khô nóng tại Đông Nam Bộ và Tây Nguyên.'],
                ['tag' => 'Bổ sung CO2', 'title' => '6. Quản lý Thông gió Đón CO2 Tự nhiên Tăng 20% Năng suất', 'body' => 'Trong nhà màng đóng kín buổi sáng, nồng độ CO2 có thể tụt từ 400 ppm xuống <220 ppm sau 1 giờ nắng làm đình trệ quang hợp; cần thông gió đổi khí đúng nhịp.'],
                ['tag' => 'An toàn Điện & Dự phòng', 'title' => '7. Bảo trì Cảm biến Định kỳ & Chế độ Vận hành Bằng Tay Khẩn cấp (Manual Override)', 'body' => 'Mọi tủ điều khiển tự động chuẩn công nghiệp phải có công tắc 3 chế độ <code>AUTO - OFF - MANUAL</code> để nông dân chủ động bật tưới/mở rèm khi bảo trì mạng.'],
            ],
            'sec3_cards'      => $standardIpmCards,
            'sec3_accordions' => $vietgapAccordions,
            'questions' => [
                ['Chỉ số VPD (Vapor Pressure Deficit) tối ưu cho giai đoạn sinh trưởng và nuôi trái trong nhà màng nằm trong khoảng nào?', ['0.8 – 1.2 kPa', '0.0 – 0.1 kPa', '3.5 – 5.0 kPa', '8.0 – 10.0 kPa'], 0],
                ['Vị trí lắp đặt cảm biến nhiệt độ - ẩm độ trong nhà màng như thế nào là đúng chuẩn kỹ thuật?', ['Đặt trong hộp chắn bức xạ thông gió ở ngang tầm ngọn cây đang phát triển', 'Phơi trần trực tiếp dưới nắng trưa sát mái nilon', 'Ngâm ngập dưới đáy bể chứa nước', 'Đặt bên ngoài nhà văn phòng cách vườn 500m'], 0],
                ['Mục đích thiết kế buồng đệm 2 cửa so le (Double-door airlock) ở lối vào nhà màng là gì?', ['Ngăn côn trùng chích hút (bọ trĩ, bọ phấn) bay theo người lao động xâm nhập vào nhà màng', 'Để trang trí cho đẹp mắt', 'Để giữ nhiệt độ nhà màng luôn ở 45°C', 'Thay thế hoàn toàn hệ thống tưới'], 0],
                ['Cấp độ mắt lưới chắn côn trùng tiêu chuẩn dùng cho vách nhà màng trồng dưa lưới/rau công nghệ cao để chặn bọ trĩ là bao nhiêu?', ['50 mesh', '4 mesh', '10 mesh', 'Không dùng lưới vách'], 0],
                ['Khi ẩm độ trong nhà màng lên quá cao >92% vào sáng sớm (VPD < 0.3 kPa), nguy cơ lớn nhất đối với cây trồng là gì?', ['Đọng sương trên lá làm bùng phát nấm Sương mai, Gỉ sắt, Mốc xám Botrytis và giảm hút Canxi', 'Cây hút quá nhiều Canxi', 'Béc tưới chạy nhanh gấp đôi', 'Độ đường trái tăng đột biến'], 0],
                ['Hệ thống rèm lưới cắt nắng tự động nên được lập trình đóng lại khi nào?', ['Khi bức xạ nắng trưa vượt ngưỡng cao (>45.000 Lux / nhiệt >33°C) và mở ra khi nắng dịu', 'Đóng kín suốt 24/24 giờ cả ngày lẫn đêm', 'Chỉ đóng vào ban đêm lúc 22h', 'Chỉ đóng khi trời mưa râm mát'], 0],
                ['Ưu điểm của hệ thống phun sương cao áp (70 bar, hạt <10 micron) so với vòi phun nước hạt lớn thông thường là gì?', ['Hạt sương siêu mịn bay hơi làm mát không khí tức thì mà không làm đọng nước ướt lá gây bệnh', 'Làm ngập sũng lối đi nhà màng', 'Tốn nước gấp 10 lần tưới tràn', 'Không làm giảm nhiệt độ'], 0],
                ['Trong tủ điện điều khiển nhà màng IoT, vì sao bắt buộc phải thiết kế công tắc chuyển mạch AUTO - OFF - MANUAL?', ['Đảm bảo vẫn vận hành tay được bơm tưới và quạt thông gió an toàn khi mất mạng hoặc bảo trì PLC', 'Để tăng tiền điện hàng tháng', 'Để vô hiệu hóa cảm biến vĩnh viễn', 'Chỉ dùng để bật đèn chiếu sáng văn phòng'], 0],
                ['Nếu nhà màng đóng kín hoàn toàn không trao đổi khí suốt buổi sáng nắng đẹp, yếu tố nào giảm mạnh làm chậm quang hợp?', ['Nồng độ khí CO2 trong nhà màng sụt giảm dưới ngưỡng tự nhiên (<250 ppm)', 'Khí Nitơ biến mất hoàn toàn', 'Độ pH nước tăng lên 14', 'Trọng lực giảm'], 0],
                ['Cài đặt khoảng trễ (Hysteresis, ví dụ bật quạt ở 31°C và tắt ở 29.5°C) trong bộ điều khiển tự động nhằm mục đích gì?', ['Chống đóng/ngắt rơ-le liên tục (chattering) giúp bảo vệ tuổi thọ động cơ quạt và khởi động từ', 'Làm hỏng cảm biến nhanh hơn', 'Tăng sai số đo nhiệt độ lên 10°C', 'Tắt toàn bộ cảnh báo điện thoại'], 0],
                ['Bao lâu cần vệ sinh bụi bẩn bám trên mái màng PE nhà màng để duy trì hiệu suất truyền ánh sáng PAR?', ['Định kỳ mỗi 3–6 tháng (đặc biệt trong mùa khô bụi) để giữ độ truyền sáng >85%', 'Không bao giờ rửa trong 10 năm', 'Sơn đen kín toàn bộ mái màng PE', 'Mỗi giờ rửa 1 lần bằng axit đặc'], 0],
                ['Khi nhận tin nhắn cảnh báo mất áp suất đường ống chính từ bộ điều khiển IoT lúc đang tưới, cần kiểm tra ngay điều gì?', ['Kiểm tra nguồn nước cấp, rò rỉ vỡ ống chính hoặc nghẹt bầu lọc đĩa đầu nguồn', 'Xóa ứng dụng cảnh báo trên điện thoại', 'Bón thêm phân đạm vào gốc', 'Cắt bỏ toàn bộ lá non'], 0],
            ],
        ],

        // ===============================================================
        // KHÓA 4: THUYCANH01
        // ===============================================================
        [
            'shortname' => 'THUYCANH01',
            'fullname'  => 'Kỹ thuật Thủy canh Hồi lưu NFT & Giá thể Xơ dừa Chuẩn VietGAP',
            'summary'   => 'Thiết kế độ dốc máng thủy canh màng dinh dưỡng NFT 1:40, kiểm soát oxy hòa tan DO >= 6 mg/L, làm mát bồn dinh dưỡng, xử lý xơ dừa xả chát Tanin/EC và kiểm soát nitrat an toàn trước thu hoạch.',
            'station'   => 'Khu Nhà màng Thủy canh Hồi lưu NFT Mầm Xanh',
            'lat'       => '11.952300',
            'lng'       => '108.439500',
            'sec1_video' => [
                'code'              => 'THUYCANH01',
                'duration'          => '13:05',
                'video_title'       => 'Vận hành Hệ thống Màng Dinh dưỡng Hồi lưu NFT & Kiểm soát Oxy Hòa tan (DO)',
                'infographic_title' => 'Thông số Vàng Thủy canh NFT: Độ dốc 1:40 • Dòng chảy 1.5 L/phút • DO >= 6 mg/L',
                'chapters' => [
                    ['time' => '00:00 - 04:15', 'short' => 'Độ dốc máng NFT', 'icon' => '📐', 'title' => 'Căn chỉnh độ dốc máng 2% – 2.5% (1:40) tạo màng nước mỏng 2mm', 'desc' => 'Nửa trên bộ rễ tiếp xúc trực tiếp không khí giàu Oxy trong lòng máng, nửa dưới chạm lớp màng dinh dưỡng chảy liên tục không đọng vũng gây thối rễ Pythium.'],
                    ['time' => '04:16 - 08:40', 'short' => 'Nhiệt nước & Oxy DO', 'icon' => '💧', 'title' => 'Giữ nhiệt độ dung dịch hồi lưu < 26°C & sục khí Venturi', 'desc' => 'Chôn ngầm bồn dinh dưỡng dưới đất hoặc bọc cách nhiệt để nước luôn mát 20–25°C, giữ hàm lượng oxy hòa tan DO đạt 6–8 mg/L giúp rễ trắng muốt.'],
                    ['time' => '08:41 - 13:05', 'short' => 'Giảm Nitrat cuối vụ', 'icon' => '🥬', 'title' => 'Hạ EC trước thu hoạch 3–5 ngày đảm bảo dư lượng Nitrat an toàn', 'desc' => 'Chuyển sang chế độ giảm đạm / hạ EC về 0.8–1.0 mS/cm và thu hoạch buổi chiều nắng hoặc sáng sau khi cây chuyển hóa hết NO3- tự do trong lá.'],
                ],
                'infographic_cards' => [
                    ['icon' => '📏', 'title' => 'Chuẩn Máng NFT', 'body' => '• Chiều dài máng tối đa: <strong>12 – 15m</strong><br>• Độ dốc chuẩn: <strong>2.0 – 2.5%</strong><br>• Lưu lượng đầu máng: <strong>1.5 – 2.0 L/phút</strong>'],
                    ['icon' => '🫧', 'title' => 'Oxy Hòa Tan &amp; Nhiệt Nước', 'body' => '• Nhiệt dung dịch: <strong>20 – 26°C</strong><br>• Oxy hòa tan DO: <strong>&ge; 6.0 mg/L</strong><br>• Chống bệnh thối nhũn rễ <em>Pythium</em>'],
                    ['icon' => '🥥', 'title' => 'Xử lý Giá thể Xơ dừa', 'body' => '• Ngâm xả sạch chất chát Tanin &amp; Lignin<br>• Đệm bằng Canxi Nitrat đẩy Na+/K+ thừa<br>• Đạt chuẩn <strong>EC &lt; 0.5 mS/cm</strong> trước trồng'],
                ],
                'scale_bars' => [
                    ['label' => 'Ươm cây con mút xốp / viên nén (7–10 ngày đầu)', 'range' => 'EC 0.8 – 1.0 mS/cm | pH 5.8', 'pct' => 38, 'color' => '#4FC3F7'],
                    ['label' => 'Lên giàn Thủy canh NFT Tăng trưởng (Ngày 11 – 28)', 'range' => 'EC 1.4 – 1.8 mS/cm | pH 5.8–6.2', 'pct' => 68, 'color' => '#2E7D32'],
                    ['label' => 'Giai đoạn Hoàn thiện Trước Thu hoạch (3 ngày cuối)', 'range' => 'EC 0.8 – 1.1 mS/cm | Kiểm soát NO3-', 'pct' => 45, 'color' => '#66BB6A'],
                ],
            ],
            'sec2_slides' => [
                ['tag' => 'Nguyên lý NFT', 'title' => '1. Bản chất Kỹ thuật Màng Dinh dưỡng (Nutrient Film Technique)', 'body' => 'NFT không ngâm ngập rễ trong nước sâu mà duy trì một <strong>lớp màng mỏng dinh dưỡng dày 1–3 mm</strong> chảy liên tục dưới đáy máng phẳng dốc 2.5%, cung cấp đồng thời nước, khoáng và oxy dồi dào cho rễ.', 'svg' => $svgFertigation],
                ['tag' => 'Ươm Giá thể Sạch', 'title' => '2. Kỹ thuật Gieo Hạt Trên Mút Xốp (Urethane Foam) & Đá Trân châu Perlite', 'body' => 'Ủ hạt trong tối ẩm 24–36h cho nứt nanh đồng loạt rồi đưa ngay ra ánh sáng sớm để cây con không bị vươn chân cao ngã đổ (leggy seedlings).'],
                ['tag' => 'Xử lý Xơ dừa', 'title' => '3. Quy trình Xả Chát & Đệm Canxi (Buffering) Cho Giá thể Mụn Xơ dừa', 'body' => 'Mụn dừa thô chứa nhiều muối <code>NaCl</code>, <code>K+</code> tự do và chất chát Tanin làm đen chóp rễ. Phải ngâm xả nước sạch và đệm bằng dung dịch <code>Ca(NO3)2</code> cho đến khi nước vắt ra đạt <strong>EC < 0.5 mS/cm</strong>.', 'quiz' => [
                    'question' => 'Nếu đem mụn xơ dừa mới xay chưa qua ngâm xả chát (EC = 3.2 mS/cm) trồng thẳng cây con sẽ gây hậu quả gì?',
                    'options'  => ['A. Cây lớn nhanh gấp đôi', 'B. Cháy thâm đen chóp rễ do ngộ độc Tanin/muối NaCl và khóa Canxi', 'C. Tự động diệt sạch sâu ăn lá'],
                    'answer'   => 1,
                    'explain'  => 'Tanin và nồng độ muối K+/Na+ cao trong xơ dừa thô làm cháy rễ non và khóa chặt khả năng hút Canxi/Magie của cây.',
                ]],
                ['tag' => 'Quản lý Bồn Tuần hoàn', 'title' => '4. Bù Nước & Châm Bổ sung Dinh dưỡng Hồi lưu Chính xác', 'body' => 'Định kỳ 14–21 ngày thay mới hoàn toàn dung dịch bể hồi lưu để tái cân bằng tỷ lệ giữa các ion đa lượng và vi lượng (tránh tích tụ ion Na+, Cl-, SO4 2- dư thừa).'],
                ['tag' => 'Phòng Pythium', 'title' => '5. Phòng chống Bệnh Thối Rễ Nhũn Nâu Pythium Trong Thủy canh Mùa Nóng', 'body' => 'Khi nhiệt độ dung dịch vượt >30°C, lượng oxy hòa tan sụt giảm mạnh khiến nấm <em>Pythium</em> tấn công làm tuột vỏ rễ. Biện pháp gốc: <strong>chôn bể ngầm mát, sơn trắng cách nhiệt máng PVC chuẩn thực phẩm và bổ sung sục khí Venturi</strong>.'],
                ['tag' => 'An toàn Nitrat', 'title' => '6. Kiểm soát Dư lượng Nitrat (NO3-) Trong Rau Thủy canh Đạt Chuẩn VietGAP', 'body' => 'Sử dụng công thức cân đối tỷ lệ <code>NO3- : NH4+</code> (90:10), đảm bảo đủ ánh sáng + vi lượng Molypden (Mo giúp hoạt hóa enzyme Nitrate Reductase chuyển hóa NO3- thành đạm hữu cơ protein).'],
                ['tag' => 'Khử trùng Giàn', 'title' => '7. Vệ sinh Khử trùng Máng NFT & Đường Ống Giữa Hai Lứa Rau', 'body' => 'Cọ sạch rêu xanh bám rọ nhựa, chạy tuần hoàn dung dịch khử trùng sinh học Oxy già thực phẩm (H2O2) hoặc Chlorine nồng độ chuẩn rồi tráng sạch nước trước khi lên lứa mới.'],
            ],
            'sec3_cards'      => $standardIpmCards,
            'sec3_accordions' => $vietgapAccordions,
            'questions' => [
                ['Độ dốc lắp đặt tiêu chuẩn của máng thủy canh hồi lưu NFT để tạo màng dinh dưỡng chảy đều không đọng vũng là bao nhiêu?', ['2.0% – 2.5% (tỷ lệ khoảng 1:40)', '0% (nằm ngang hoàn toàn đọng đầy nước)', '45% (dốc đứng)', '80%'], 0],
                ['Hàm lượng Oxy hòa tan (DO) tối thiểu cần duy trì trong dung dịch thủy canh để bộ rễ trắng khỏe không bị thối ngạt là bao nhiêu?', ['>= 6.0 mg/L', '0.0 mg/L', '0.5 mg/L', 'Chỉ cần khí CO2 không cần Oxy'], 0],
                ['Vì sao chiều dài mỗi máng thủy canh NFT thương mại không nên vượt quá 12–15 mét?', ['Tránh chênh lệch nhiệt độ tăng cao và cạn kiệt oxy hòa tan ở các hốc cây cuối máng', 'Vì ống nhựa không thể nối dài hơn 2m', 'Để giảm ánh sáng mặt trời', 'Vì cây đầu máng không hút được nước'], 0],
                ['Khi xử lý giá thể mụn xơ dừa trước khi trồng dưa lưới/cà chua, chỉ số EC nước xả cuối cùng cần đạt mức nào?', ['EC < 0.5 mS/cm và đã ngâm đệm loại bỏ Tanin/muối Na+', 'EC > 5.0 mS/cm', 'Giữ nguyên toàn bộ muối chát tự nhiên', 'Trộn thêm muối ăn NaCl vào xơ dừa'], 0],
                ['Nguyên nhân hàng đầu làm bùng phát nấm nước Pythium gây thối tuột rễ trên giàn thủy canh vào mùa nắng nóng là gì?', ['Nhiệt độ dung dịch dinh dưỡng tăng cao (>30°C) làm giảm mạnh nồng độ oxy hòa tan DO', 'Nhiệt độ nước mát 22°C giàu oxy', 'Độ pH ổn định ở 6.0', 'Ánh sáng buổi sáng dịu nhẹ'], 0],
                ['Vi lượng nào đóng vai trò thiết yếu cấu tạo enzyme Nitrate Reductase giúp cây rau chuyển hóa Nitrat (NO3-) giảm tồn dư trong lá?', ['Molypden (Mo) kết hợp đủ ánh sáng quang hợp', 'Chì (Pb)', 'Thủy ngân (Hg)', 'Nhôm (Al)'], 0],
                ['Máng nhựa dùng trong hệ thống thủy canh rau ăn lá chuẩn VietGAP phải đáp ứng yêu cầu vật liệu nào?', ['Nhựa uPVC chuyên dụng an toàn thực phẩm, không thôi nhiễm chì/kim loại nặng, lòng tối chống rêu', 'Ống nhựa tái chế phế thải công nghiệp chứa chì', 'Máng tôn gỉ sét', 'Máng gỗ mục tẩm dầu nhớt'], 0],
                ['Để giảm thiểu hàm lượng Nitrat tự do trong rau thủy canh trước khi thu hoạch 3–5 ngày, kỹ thuật nào được áp dụng?', ['Hạ nhẹ nồng độ EC dung dịch (0.8–1.0 mS/cm) và đảm bảo cây nhận đủ ánh sáng chuyển hóa đạm', 'Tăng gấp 3 lần đạm Urê vào bồn trước giờ cắt 1 tiếng', 'Che tối đen toàn bộ giàn rau 5 ngày', 'Ngâm ngập lá rau vào dung dịch Bồn A'], 0],
                ['Hiện tượng rêu tảo xanh phát triển dày đặc trong bồn chứa và rãnh máng thủy canh gây tác hại gì?', ['Cạnh tranh oxy hòa tan, làm biến động pH, bít nghẹt bơm lọc và sinh độc tố hại rễ non', 'Giúp cây lớn nhanh gấp 3 lần', 'Thay thế hoàn toàn phân bón A-B', 'Tự động làm lạnh dung dịch'], 0],
                ['Lưu lượng cấp dung dịch dinh dưỡng tiêu chuẩn ở đầu mỗi máng thủy canh NFT là khoảng bao nhiêu?', ['1.5 – 2.0 Lít/phút', '0.01 Lít/ngày', '500 Lít/phút xối bật gốc cây', 'Chỉ cấp 1 lần duy nhất mỗi tháng'], 0],
                ['Biện pháp thiết kế nào giúp giữ dung dịch thủy canh luôn mát mẻ ổn định ở 22–25°C trong điều kiện khí hậu Việt Nam?', ['Hạ ngầm bồn chứa dinh dưỡng dưới mặt đất có nắp đậy kín cách nhiệt và mái máng trắng phản xạ nhiệt', 'Đặt bồn nhựa màu đen ngoài trời nắng trưa không mái che', 'Đun nóng bồn dinh dưỡng bằng điện trở', 'Dùng máng kim loại hấp nhiệt'], 0],
                ['Khi cây con xà lách mới gieo bị vươn cổ thân dài mảnh yếu ớt (leggy), nguyên nhân kỹ thuật là do đâu?', ['Thiếu ánh sáng ngay sau khi hạt nứt nanh nảy mầm hoặc nhiệt độ ươm quá cao', 'Đủ nắng sáng sớm thông thoáng', 'Độ pH nước tưới chuẩn 6.0', 'Mút xốp sạch vô trùng'], 0],
            ],
        ],

        // ===============================================================
        // KHÓA 5: SAURIENG01
        // ===============================================================
        [
            'shortname' => 'SAURIENG01',
            'fullname'  => 'Quy trình Canh tác Sầu riêng Xuất khẩu, Mã số Vùng trồng & Xử lý Ra hoa',
            'summary'   => 'Chuẩn hóa quy trình canh tác Sầu riêng Dona/Ri6 phục vụ Nghị định thư xuất khẩu chính ngạch: Quản lý cơi đọt, tạo khô hạn sinh lý ra hoa đồng loạt, đậu trái - tỉa trái, phòng chống Phytophthora & Rệp sáp, quản lý Mã số vùng trồng (PUC) và kiểm soát tuyệt đối Cadimi/dư lượng.',
            'station'   => 'Vườn Sầu riêng Chuẩn Mã số Xuất khẩu Tây Nguyên - Đông Nam Bộ',
            'lat'       => '12.684210',
            'lng'       => '108.214500',
            'sec1_video' => [
                'code'              => 'SAURIENG01',
                'duration'          => '15:20',
                'video_title'       => 'Quản lý Cơi đọt Khỏe, Xử lý Ra hoa Sinh lý & Hồ sơ Mã số Vùng trồng (PUC)',
                'infographic_title' => '4 Cột Mốc Quyết Định Năng Suất & Phẩm Chất Sầu Riêng Xuất Khẩu',
                'chapters' => [
                    ['time' => '00:00 - 05:00', 'short' => 'Nuôi 2–3 cơi đọt', 'icon' => '🌿', 'title' => 'Phục hồi sau thu hoạch & bảo vệ trọn vẹn bộ lá cơi đọt tránh rầy phấn', 'desc' => 'Mỗi trái sầu riêng 3kg cần tối thiểu 300–400 lá khỏe quang hợp nuôi cơm vàng dẻo. Phải giữ bộ lá dày xanh bóng không bị rầy nhảy/cháy lá trước khi làm bông.'],
                    ['time' => '05:01 - 10:15', 'short' => 'Ra hoa & Chống rụng', 'icon' => '🌼', 'title' => 'Siết nước phân hóa mầm hoa, tỉa bông chùm và kiểm soát đi đọt non cạnh tranh', 'desc' => 'Giai đoạn xổ nhụy và trái non bằng quả trứng gà cực kỳ nhạy cảm: nếu cây phóng đọt non mạnh sẽ rút hết Canxi-Bo làm rụng trái non hàng loạt!'],
                    ['time' => '10:16 - 15:20', 'short' => 'Mã vùng trồng & Cadimi', 'icon' => '🌏', 'title' => 'Tuân thủ Nghị định thư Xuất khẩu: Không Rệp sáp, Không Cadimi vượt ngưỡng', 'desc' => 'Kiểm nghiệm pH đất & nguồn phân lân đầu vào để ngăn tích lũy kim loại nặng Cadimi (Cd <= 0.05 mg/kg), vệ sinh sạch 100% rệp sáp ở rãnh gai trước đóng gói.'],
                ],
                'infographic_cards' => [
                    ['icon' => '🌳', 'title' => 'Thiết kế Mô &amp; Thoát nước', 'body' => '• Lên mô cao thoát nước mùa mưa Tây Nguyên<br>• Duy trì pH đất <strong>5.8 – 6.5</strong><br>• Bón 15–25 kg phân hữu cơ vi sinh + Trichoderma/gốc'],
                    ['icon' => '🍈', 'title' => 'Tỉa Bông &amp; Nuôi Trái Chuẩn', 'body' => '• Tỉa bông 3 đợt giữ bông mập trên cành cấp 1–2<br>• Thụ phấn bổ sung 19:30 – 21:30 tối giúp tròn học<br>• Cân đối Kali Sunfat <code>K2SO4</code> (không dùng KCl!)'],
                    ['icon' => '🛡️', 'title' => 'Chuẩn Kiểm dịch Xuất khẩu', 'body' => '• Sạch tuyệt đối đối tượng kiểm dịch <strong>Rệp sáp</strong><br>• Kiểm soát <strong>Cadimi (Cd)</strong> &amp; dư lượng BVTV<br>• Nhật ký Mã số vùng trồng (PUC) minh bạch'],
                ],
                'scale_bars' => [
                    ['label' => 'Độ tuổi cơi lá chuyển lụa già tích lũy Lân-Kali làm bông', 'range' => 'Cơi 2–3 hoàn chỉnh | C:N tối ưu', 'pct' => 75, 'color' => '#2E7D32'],
                    ['label' => 'Độ chín thu hoạch xuất khẩu (Tỷ lệ chất khô cơm sầu riêng)', 'range' => 'Ri6 >= 30% | Dona/Monthong >= 32%', 'pct' => 88, 'color' => '#F9A825'],
                    ['label' => 'Kiểm soát pH đất khóa kim loại nặng Cadimi di động', 'range' => 'pH đất 5.8 – 6.5 (Không để đất chua <5.0)', 'pct' => 66, 'color' => '#1B4332'],
                ],
            ],
            'sec2_slides' => [
                ['tag' => 'Sinh lý Sầu riêng', 'title' => '1. Chu kỳ Sinh trưởng Cơi đọt — Phân hóa Mầm hoa — Nuôi trái Sầu riêng', 'body' => 'Sầu riêng ra hoa trên thân/cành ngang (Cauliflory) cần một giai đoạn <strong>khô hạn sinh lý 10–18 ngày</strong> kết hợp lá già tích lũy đủ Lân - Kali - Kẽm - Bo để chuyển từ sinh trưởng dinh dưỡng sang phân hóa mắt cua.', 'svg' => $svgFertigation],
                ['tag' => 'Tỉa Bông & Trái', 'title' => '2. Kỹ thuật Tỉa Bông 3 Đợt & Thụ phấn Chéo Ban đêm (19:30–21:30)', 'body' => 'Hoa sầu riêng nở và tung phấn vào ban đêm! Dùng chổi lông mềm quét nhẹ phấn giữa các cây lúc 20:00 tối giúp trái thụ phấn đủ 5 hộc đều đặn, loại bỏ hoàn toàn trái méo eo (trái heo), đạt chuẩn loại A xuất khẩu.'],
                ['tag' => 'Chống Rụng Trái Non', 'title' => '3. Giải pháp Ngăn Chặn Cạnh tranh Dinh dưỡng Giữa Đọt non & Trái non', 'body' => 'Chủ động kéo đọt đồng loạt ngay giai đoạn mắt cua để khi xổ nhụy bộ lá đã lụa già, đồng thời bổ sung <strong>Canxi-Bo hữu cơ</strong> làm dẻo cuống trái và điều tiết nước tưới nhỏ giọt ổn định tránh sốc nhiệt mưa đầu mùa.', 'quiz' => [
                    'question' => 'Vì sao trong giai đoạn nuôi cơm sầu riêng tuyệt đối phải dùng Kali Sunfat (K2SO4) thay vì Kali Clorua (KCl - phân muối ớt)?',
                    'options'  => ['A. Vì KCl chứa ion Clo (Cl-) cao gây tích nước làm sượng cơm, nhạt vị và cháy lá sầu riêng', 'B. Vì KCl không tan trong nước', 'C. Vì K2SO4 làm vỏ sầu riêng chuyển sang màu tím'],
                    'answer'   => 0,
                    'explain'  => 'Sầu riêng là cây mẫn cảm Clo (Chloride-sensitive); gốc Cl- làm tích nước mô quả gây hiện tượng sượng cơm và cháy chóp lá.',
                ]],
                ['tag' => 'Phòng Xì mủ Phytophthora', 'title' => '4. Quản lý Tổng hợp Bệnh Nứt thân Xì mủ & Thối rễ (Phytophthora palmivora)', 'body' => 'Không đắp ủ rác tươi sát cổ rễ; nâng pH đất bằng Dolomite, tưới định kỳ nấm đối kháng <strong>Trichoderma 50g/gốc + Humic</strong> đầu và cuối mùa mưa, kết hợp kích kháng hệ thống bằng <code>Potassium Phosphonate</code>.'],
                ['tag' => 'Kiểm soát Cadimi', 'title' => '5. Giải pháp Ngăn ngừa Tích lũy Kim loại Nặng Cadimi (Cd) Trong Cơm Sầu riêng', 'body' => 'Khi đất bị chua hóa (<code>pH < 5.0</code>), ion <code>Cd2+</code> trong đất hòa tan mạnh và bị rễ hút lên trái! Cần <strong>giữ pH đất 5.8–6.5</strong>, tăng mùn hữu cơ vi sinh cố định kim loại nặng và chỉ dùng phân bón đã kiểm định Cd thấp.'],
                ['tag' => 'Kiểm dịch Rệp sáp', 'title' => '6. Kiểm soát Đối tượng Kiểm dịch Thực vật (Rệp sáp, Sâu đục hạt) Theo Nghị định thư', 'body' => 'Chỉ cần phát hiện 1 cá thể rệp sáp sống trên lô hàng tại cửa khẩu là cả container bị trả về hoặc tạm dừng Mã số vùng trồng! Phải quản lý từ lúc đậu trái non bằng dầu khoáng sinh học + chặn kiến + xịt tia nước áp lực làm sạch gai tại nhà đóng gói.'],
                ['tag' => 'Thu hoạch Đúng Tuổi', 'title' => '7. Kiểm tra Độ Chín Sinh lý (105–125 Ngày Sau Xổ Nhụy) & Hồ sơ Truy xuất PUC', 'body' => 'Tuyệt đối không cắt sầu riêng non chạy giá! Kiểm tra đường chỉ rãnh gai, gõ âm thanh và đo <strong>hàm lượng chất khô >= 32%</strong> trước khi mở lệnh thu hoạch cho cả lô mã vùng trồng.'],
            ],
            'sec3_cards'      => $standardIpmCards,
            'sec3_accordions' => $vietgapAccordions,
            'questions' => [
                ['Loại phân Kali nào bắt buộc sử dụng ở giai đoạn nuôi cơm sầu riêng để cơm vàng ráo, ngọt béo và tránh hiện tượng sượng nước?', ['Kali Sunfat (K2SO4) không chứa Clo', 'Kali Clorua (KCl - phân muối ớt chứa nhiều Clo)', 'Muối ăn NaCl', 'Chỉ bón thuần phân đạm Urê'], 0],
                ['Hoa sầu riêng nở rộ và tiếp nhận hạt phấn tốt nhất vào khung giờ nào trong ngày để thực hiện thụ phấn bổ sung giúp trái tròn đều 5 hộc?', ['Ban đêm từ 19:30 – 21:30', 'Giữa trưa nắng 12:00 – 13:00', 'Sáng sớm 05:00 khi hoa đã rụng', 'Không bao giờ mở cánh hoa'], 0],
                ['Nguyên nhân sinh lý phổ biến nhất gây rụng trái non hàng loạt khi trái sầu riêng mới bằng quả trứng gà là gì?', ['Cây phóng cơi đọt non mạnh cạnh tranh dinh dưỡng/Canxi-Bo với trái non hoặc sốc nước mưa đầu mùa', 'Bộ lá già khỏe mạnh quang hợp tốt', 'Thụ phấn chéo ban đêm đầy đủ', 'Độ pH đất ổn định 6.2'], 0],
                ['Đối tượng dịch hại kiểm dịch thực vật hàng đầu trên trái sầu riêng xuất khẩu cần làm sạch tuyệt đối 100% là loài nào?', ['Rệp sáp phấn trắng (Planococcus / Pseudococcus) và sâu đục quả/hạt', 'Giun đất có ích trong đất', 'Ong mật thụ phấn', 'Vi khuẩn cố định đạm'], 0],
                ['Biện pháp canh tác căn cơ giúp giảm độ linh động và ngăn rễ sầu riêng hấp thu kim loại nặng Cadimi (Cd) vượt ngưỡng là gì?', ['Nâng pH đất lên 5.8–6.5, tăng chất hữu cơ vi sinh cố định Cd và chọn phân lân sạch kiểm định Cd', 'Hạ pH đất thật chua xuống 3.8', 'Ngưng bón toàn bộ phân hữu cơ', 'Tưới nước thải công nghiệp chưa xử lý'], 0],
                ['Tác nhân nấm nguy hiểm nhất gây bệnh nứt thân xì mủ và thối rễ vàng lá trên cây sầu riêng mùa mưa là loài nào?', ['Phytophthora palmivora', 'Nấm men Saccharomyces', 'Nấm đối kháng Trichoderma', 'Nấm rơm Volvariella'], 0],
                ['Chỉ tiêu hàm lượng chất khô tối thiểu trong cơm sầu riêng Dona (Monthong) để đảm bảo đủ độ già chín sinh lý khi thu hoạch xuất khẩu là bao nhiêu?', ['Đạt tối thiểu >= 32%', 'Chỉ cần 12% (trái non trắng)', '5%', '98% khô cứng như đá'], 0],
                ['Mỗi trái sầu riêng thương phẩm 3 kg cần trung bình bao nhiêu lá trưởng thành khỏe mạnh trên cành để nuôi trái đạt chuẩn?', ['Khoảng 300 – 400 lá khỏe mạnh không sâu bệnh', 'Chỉ cần 3 lá trên cả cây', 'Vặt trụi hết lá để trái phơi nắng', '10 lá'], 0],
                ['Khi sử dụng nấm đối kháng Trichoderma tưới gốc phòng bệnh thối rễ sầu riêng, cần lưu ý nguyên tắc nào?', ['Đất đủ ẩm, giàu mùn hữu cơ và cách ly việc rải vôi nóng hoặc tưới thuốc nấm hóa học gốc đồng ít nhất 10–14 ngày', 'Pha chung trực tiếp với thuốc trừ nấm gốc đồng đậm đặc trong cùng một phi', 'Rải trên mặt bê tông khô nắng gắt 45°C', 'Chỉ phun lên ngọn lá lúc 12h trưa'], 0],
                ['Mã số vùng trồng (PUC - Production Unit Code) của vườn sầu riêng xuất khẩu có thể bị đình chỉ thu hồi trong trường hợp nào?', ['Vi phạm kiểm dịch thực vật (nhiễm rệp sáp), vượt dư lượng BVTV/Cadimi hoặc gian lận mượn mã vùng trồng', 'Ghi chép nhật ký VietGAP đầy đủ chính xác', 'Áp dụng tưới tiết kiệm và IPM sinh học', 'Thu hoạch đúng độ chín chuẩn'], 0],
                ['Vì sao khi trồng sầu riêng ở vùng đất thấp hoặc mưa nhiều bắt buộc phải đắp mô nổi và xẻ rãnh thoát nước?', ['Bộ rễ sầu riêng rất mẫn cảm ngập úng thiếu oxy; đọng nước làm thối rễ tơ và kích hoạt nấm Phytophthora', 'Vì sầu riêng là cây thủy sinh ưa ngập nước sâu', 'Để không phải tưới nước mùa khô', 'Để ngăn ánh sáng chiếu vào tán cây'], 0],
                ['Khoảng thời gian cách ly và nguyên tắc ghi nhật ký đối với vườn sầu riêng có Mã số vùng trồng xuất khẩu là gì?', ['Chỉ dùng hoạt chất được nước nhập khẩu cho phép, ghi chép đầy đủ 100% và tuân thủ nghiêm ngặt thời gian cách ly PHI', 'Phun bất kỳ thuốc cấm nào sát ngày cắt trái', 'Không cần lưu hồ sơ vật tư đầu vào', 'Thu gom trái không rõ nguồn gốc đóng chung lô'], 0],
            ],
        ],

        // ===============================================================
        // KHÓA 6: PHANVISINH01
        // ===============================================================
        [
            'shortname' => 'PHANVISINH01',
            'fullname'  => 'Sản xuất Phân Hữu cơ Vi sinh, Nhân sinh khối Trichoderma & Cải tạo Đất',
            'summary'   => 'Biến phụ phẩm nông nghiệp (vỏ cà phê, phân chuồng, rơm rạ, phụ phẩm rau quả) thành vàng đen cho đất: Phối trộn tỷ lệ C/N = 25–30:1, kiểm soát đống ủ hiếu khí 55–65°C tiêu diệt mầm bệnh/hạt cỏ, nhân sinh khối Trichoderma - Bacillus - IMO bản địa phục hồi hệ vi sinh đất.',
            'station'   => 'Trạm Thực hành Ủ Phân Hữu cơ Vi sinh Tuần hoàn Mầm Xanh',
            'lat'       => '10.938150',
            'lng'       => '107.242800',
            'sec1_video' => [
                'code'              => 'PHANVISINH01',
                'duration'          => '12:15',
                'video_title'       => 'Kỹ thuật Phối trộn Tỷ lệ C/N (25–30:1), Kiểm tra Ẩm độ Nắm tay & Ủ Hiếu khí Nhiệt cao',
                'infographic_title' => 'Quy trình 45 Ngày Chuyển Hóa Phụ Phẩm Thành Phân Hữu Cơ Vi Sinh Sạch Bệnh',
                'chapters' => [
                    ['time' => '00:00 - 04:05', 'short' => 'Phối trộn C/N & Ẩm 55%', 'icon' => '♻️', 'title' => 'Cân bằng vật liệu giàu Carbon (vỏ cà phê, rơm) và giàu Nitơ (phân chuồng, bã đậu)', 'desc' => 'Kiểm tra ẩm độ thực địa bằng bài test nắm tay: bóp chặt hỗn hợp thấy nước rỉ nhẹ qua kẽ ngón tay nhưng không nhỏ giọt thành dòng, mở tay ra khối nguyên liệu kết dính là đạt chuẩn 55–60%.'],
                    ['time' => '04:06 - 08:20', 'short' => 'Pha ưa nhiệt 60°C', 'icon' => '🔥', 'title' => 'Theo dõi pha ưa nhiệt (55–65°C) tiêu diệt E.coli, trứng tuyến trùng và hạt cỏ dại', 'desc' => 'Đống ủ cao 1.2–1.5m tăng nhiệt lên 60–65°C từ ngày thứ 5–15 giúp thanh trùng hoàn toàn mầm bệnh hại. Đảo trộn cấp oxy khi tâm đống ủ vượt 68°C.'],
                    ['time' => '08:21 - 12:15', 'short' => 'Cấy Trichoderma khi nguội', 'icon' => '🍄', 'title' => 'Bổ sung nấm đối kháng Trichoderma & vi khuẩn cố định đạm khi đống ủ hạ < 38°C', 'desc' => 'Tuyệt đối không trộn chủng Trichoderma sống lúc đống ủ đang nóng 65°C! Đợi pha chín muồi hạ nhiệt <38°C mới nhân cấy để mật độ bào tử đạt >10^8 CFU/g.'],
                ],
                'infographic_cards' => [
                    ['icon' => '⚖️', 'title' => 'Tỷ lệ Vàng C/N = 25–30 : 1', 'body' => '• C/N &gt; 50 (toàn mùn cưa/vỏ khô): Ủ rất lâu hoai<br>• C/N &lt; 15 (toàn đạm tươi): Hôi thối mất NH3<br>• <strong>Phối trộn chuẩn giúp hoai nhanh không mùi</strong>'],
                    ['icon' => '🌡️', 'title' => 'Pha Thanh Trùng 55–65°C', 'body' => '• Duy trì tối thiểu 7–10 ngày kết hợp đảo trộn<br>• Diệt sạch <em>Salmonella, E.coli</em> &amp; hạt cỏ<br>• Tuyệt đối không bón phân tươi sống vào vườn!'],
                    ['icon' => '🦠', 'title' => 'Hệ Vi Sinh Có Ích >10^8 CFU', 'body' => '• <em>Trichoderma</em>: Đối kháng nấm thối rễ<br>• <em>Bacillus</em>: Phân giải Lân khó tan<br>• <em>Azotobacter</em>: Cố định đạm tự nhiên'],
                ],
                'scale_bars' => [
                    ['label' => 'Giai đoạn Khởi động (Ngày 1–4): Vi sinh vật ưa ấm phân giải đường dễ tiêu', 'range' => '30°C – 45°C | Ẩm độ 60%', 'pct' => 45, 'color' => '#43A047'],
                    ['label' => 'Giai đoạn Ưa nhiệt Thanh trùng (Ngày 5–20): Phân giải Cellulose & diệt mầm bệnh', 'range' => '55°C – 65°C | Đảo trộn cấp Oxy', 'pct' => 88, 'color' => '#EF6C00'],
                    ['label' => 'Giai đoạn Nguội & Chín Sinh học (Ngày 25–45): Cấy Trichoderma & tạo mùn Humus', 'range' => '28°C – 35°C | Thơm mùi đất rừng', 'pct' => 62, 'color' => '#1B4332'],
                ],
            ],
            'sec2_slides' => [
                ['tag' => 'Tuần hoàn Nông nghiệp', 'title' => '1. Vai trò của Chất Mùn Hữu cơ (Humus) Đối với Sức khỏe Đất Trồng', 'body' => 'Đất canh tác thâm canh hóa học lâu năm bị chai cứng, mùn <1%, pH tụt chua và mất khả năng giữ phân (CEC thấp). Bổ sung phân hữu cơ vi sinh tạo <strong>kết cấu đoàn lạp tơi xốp</strong>, giữ ẩm mùa khô, thoát nước mùa mưa và nâng hiệu suất hút phân khoáng lên gấp đôi.', 'svg' => $svgFertigation],
                ['tag' => 'Phối liệu C/N', 'title' => '2. Công thức Phối trộn Thực chiến Từ Phụ phẩm Sẵn có Tại Địa phương', 'body' => 'Công thức chuẩn cho 1 tấn nguyên liệu: <strong>600 kg vỏ cà phê / phụ phẩm thực vật băm nhỏ + 350 kg phân chuồng tiêu chuẩn + 30 kg lân nung chảy + 20 kg cám gạo + rỉ mật đường (2 lít) + chế phẩm men ủ</strong>.'],
                ['tag' => 'Kiểm soát Oxy & Ẩm', 'title' => '3. Kỹ thuật Đảo trộn Hiếu khí Ngăn Quá trình Yếm khí Sinh Độc tố Hữu cơ', 'body' => 'Nếu đống ủ quá ướt (>70% nước) và nén chặt thiếu oxy, vi khuẩn yếm khí sẽ sinh khí <code>H2S</code>, <code>CH4</code> mùi hôi thối và các axit hữu cơ độc rễ.', 'quiz' => [
                    'question' => 'Thời điểm nào là ĐÚNG KỸ THUẬT nhất để bổ sung nấm đối kháng Trichoderma vào đống phân hữu cơ nhằm đạt mật độ bào tử cao nhất?',
                    'options'  => ['A. Trộn ngay lúc tâm đống ủ đang nóng 68°C', 'B. Cấy bổ sung ở giai đoạn phân đã chuyển nguội (<38°C) cuối quá trình ủ để tránh nhiệt cao tiêu diệt bào tử nấm có ích', 'C. Đun sôi Trichoderma với nước vôi đặc trước khi tưới'],
                    'answer'   => 1,
                    'explain'  => 'Nhiệt độ trên 55–65°C trong pha ưa nhiệt sẽ tiêu diệt luôn nấm Trichoderma; cấy ở pha nguội <38°C giúp Trichoderma nhân sinh khối cực mạnh.',
                ]],
                ['tag' => 'Nhân Sinh khối IMO/Dịch Đạm', 'title' => '4. Kỹ thuật Thủy phân Đạm Cá / Đậu tương Sinh học Bằng Enzyme Protease & EM', 'body' => 'Ủ kín cá tạp/bánh dầu đậu tương với chế phẩm <em>Bacillus + Lactobacillus</em> + mật rỉ đường (pH 4.0–4.5) trong 30–45 ngày thu dịch axit amin (Amino Acid) thơm dịu dễ tiêu không mùi tanh.'],
                ['tag' => 'Tiêu chuẩn Hoai Mục', 'title' => '5. 4 Dấu hiệu Nhận biết Phân Hữu cơ Đã Hoai Mục Hoàn toàn An toàn Cho Rễ', 'body' => '<ul><li>Màu nâu đen đồng nhất, tơi xốp bóp nhẹ vụn rời.</li><li>Mùi thơm mát đặc trưng của đất rừng (xạ khuẩn <em>Actinomycetes</em>), không còn mùi khai amoniac.</li><li>Nhiệt độ đống ủ bằng nhiệt độ môi trường (~28–32°C).</li><li>Thử nảy mầm hạt cải đạt tỷ lệ >90% rễ trắng khỏe.</li></ul>'],
                ['tag' => 'Quy định VietGAP Phân Hữu cơ', 'title' => '6. Cấm Tuyệt đối Sử dụng Phân Tươi Chưa Ủ Hoai Trong Canh tác VietGAP', 'body' => 'Phân gia súc/gia cầm tươi mang mầm vi khuẩn <em>E.coli, Salmonella</em> gây ngộ độc thực phẩm, mang trứng tuyến trùng sưng rễ và sinh nhiệt đốt cháy rễ tơ cây ăn quả!'],
                ['tag' => 'Kỹ thuật Bón Hiệu quả', 'title' => '7. Phương pháp Bón Phân Hữu cơ Vi sinh Theo Hình Chiếu Tán Cây & Lấp Mùn', 'body' => 'Bón theo rãnh nông quanh rìa hình chiếu tán cây (nơi tập trung nhiều rễ tơ hút dinh dưỡng nhất), phủ lớp cỏ khô/mùn giữ ẩm và tưới đẫm nước để bảo vệ vi sinh vật khỏi tia cực tím UV mặt trời.'],
            ],
            'sec3_cards'      => $standardIpmCards,
            'sec3_accordions' => $vietgapAccordions,
            'questions' => [
                ['Tỷ lệ Carbon / Nitơ (C/N) lý tưởng nhất của hỗn hợp nguyên liệu ban đầu khi ủ phân hữu cơ vi sinh là bao nhiêu?', ['25:1 đến 30:1', '500:1 (chỉ toàn mùn cưa khô không có đạm)', '2:1 (chỉ toàn đạm đặc)', '0:0'], 0],
                ['Độ ẩm nguyên liệu chuẩn khi bắt đầu đánh đống ủ phân hữu cơ hiếu khí (kiểm tra nắm chặt rỉ nước kẽ tay không nhỏ giọt) là bao nhiêu?', ['55% – 60%', '5% khô rang như cát', '95% ngập sũng như cháo loãng', '0%'], 0],
                ['Nhiệt độ ở giai đoạn ưa nhiệt (Thermophilic phase) trong tâm đống ủ đạt 55–65°C kéo dài nhiều ngày có tác dụng quan trọng gì?', ['Tiêu diệt vi khuẩn gây bệnh (E.coli, Salmonella), trứng tuyến trùng, nấm hại và làm mất sức nảy mầm hạt cỏ dại', 'Làm phân biến thành nhựa tổng hợp', 'Giữ sống toàn bộ hạt cỏ dại để mọc nhiều hơn', 'Đóng băng đống phân'], 0],
                ['Vì sao nên cấy bổ sung chủng nấm đối kháng Trichoderma vào giai đoạn đống ủ đã hạ nhiệt nguội (<38°C) thay vì lúc đang nóng 65°C?', ['Tránh nhiệt độ quá cao 65°C tiêu diệt bào tử Trichoderma, giúp nấm nhân sinh khối tối đa trong phân thành phẩm', 'Vì Trichoderma chỉ sống ở 100°C', 'Để làm phân bốc mùi khai', 'Không có lý do kỹ thuật nào'], 0],
                ['Theo tiêu chuẩn VietGAP, vì sao nghiêm cấm bón phân chuồng tươi chưa qua ủ hoai mục trực tiếp cho rau quả?', ['Nguy cơ nhiễm vi sinh vật gây bệnh đường ruột (E.coli, Salmonella), tuyến trùng rễ và sinh nhiệt độc rễ', 'Vì phân tươi quá đắt tiền', 'Vì phân tươi có màu quá đẹp', 'Vì phân tươi làm tăng oxy đất quá mức'], 0],
                ['Khi đống ủ phân hữu cơ bốc mùi trứng thối (H2S) và chua gắt, nguyên nhân kỹ thuật là do đâu và xử lý thế nào?', ['Đống ủ quá ướt thiếu oxy (yếm khí); cần đảo trộn tơi xốp và bổ sung chất độn khô giàu Carbon (trấu, mùn dừa)', 'Đống ủ quá giàu oxy; cần đổ ngập nước kín', 'Nhiệt độ ngoài trời 25°C', 'Do có nhiều xạ khuẩn thơm'], 0],
                ['Dấu hiệu cảm quan chính xác chứng tỏ mẻ phân hữu cơ vi sinh đã chín hoai hoàn toàn sẵn sàng đem bón là gì?', ['Màu nâu đen tơi xốp, nhiệt độ hạ bằng môi trường và có mùi thơm dịu của đất rừng (xạ khuẩn)', 'Tâm đống ủ vẫn nóng bỏng 65°C và bốc mùi khai nồng', 'Nguyên liệu còn nguyên màu xanh tươi', 'Chảy nước đen hôi thối'], 0],
                ['Nhóm vi sinh vật có ích Bacillus megaterium / Bacillus subtilis bổ sung trong phân hữu cơ vi sinh có khả năng nổi bật nào?', ['Tiết enzyme và axit hữu cơ phân giải Lân khó tan trong đất thành dạng cây dễ hấp thu và đối kháng nấm khuẩn hại', 'Biến đất cát thành đá tảng', 'Hút hết oxy làm ngạt rễ cây', 'Làm chua đất xuống pH 2.0'], 0],
                ['Vị trí và cách bón phân hữu cơ vi sinh cho cây ăn quả lâu năm (Sầu riêng, Bưởi) đạt hiệu quả cao nhất là gì?', ['Bón theo hình chiếu rìa tán lá (vùng rễ tơ), lấp đất mỏng hoặc tủ rơm giữ ẩm tránh nắng UV chiếu chết vi sinh', 'Đổ thành đống dày 50cm ốp sát vào cổ thân gốc cây', 'Rải phơi khô trắng trên mặt cỏ giữa trưa nắng gắt không tưới', 'Pha nguyên xác phân thô vào ống nhỏ giọt bù áp'], 0],
                ['Khi thủy phân đạm cá / bánh dầu sinh học làm phân tưới gốc không mùi hôi, nguyên liệu nào cung cấp nguồn đường nuôi khuẩn Lactic & Bacillus?', ['Rỉ mật đường (Molasses) kết hợp chế phẩm men vi sinh EM / Protease', 'Dầu hỏa công nghiệp', 'Vôi nung CaO liều cao', 'Muối hột bão hòa'], 0],
                ['Tác dụng cải tạo vật lý - hóa học quan trọng nhất của mùn hữu cơ đối với đất đỏ bazan Tây Nguyên và đất xám Đông Nam Bộ là gì?', ['Tăng khả năng giữ phân CEC, đệm ổn định pH đất, tạo cấu trúc tơi xốp giàu oxy và nuôi hệ thiên địch đất', 'Làm đất nén chặt không thấm nước', 'Làm tăng độc tố Nhôm di động Al3+', 'Tiêu diệt hết giun đất'], 0],
                ['Mật độ vi sinh vật hữu ích tối thiểu trên nhãn/mẻ phân hữu cơ vi sinh chất lượng cao thường đạt ngưỡng nào?', ['Từ 10^6 đến 10^8 CFU/g trở lên', '0 CFU/g (vô trùng tuyệt đối)', '1 tế bào trên 1 tấn phân', 'Chỉ chứa cát sỏi khoáng'], 0],
            ],
        ],
    ];
}
