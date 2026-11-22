const COURSE_BLUEPRINTS = {
  CHAMSOC01: {
    pdf_title: 'Tài liệu Kỹ thuật TCVN 11892-1:2017 — Canh tác Nhà màng & Fertigation',
    pdf_summary: 'Quy định tiêu chuẩn giá thể sạch, nguồn nước tưới (EC < 0.5 mS/cm, E.coli < 1.000 CFU/100ml), công thức pha Stock A (Ca(NO3)2 + Fe-EDTA) và Stock B (KH2PO4, KNO3, MgSO4, vi lượng) tách bồn chống kết tủa CaSO4.',
    station_name: 'Nhà màng Thông minh A1 — Trạm Khuyến nông Đà Lạt',
    target_lat: 11.94652,
    target_lng: 108.50411,
    expected_qr_prefix: 'QR-CHAMSOC01',
    modules: [
      { id: 1, title: 'Chuyên đề 1: Quản lý vi khí hậu & chỉ số VPD (0.8–1.2 kPa) trong nhà màng', duration: '45 phút', type: 'Video HD + PDF' },
      { id: 2, title: 'Chuyên đề 2: Thiết kế tưới nhỏ giọt bù áp & bộ châm phân Venturi A-B', duration: '50 phút', type: 'Video HD + Thực hành' },
      { id: 3, title: 'Chuyên đề 3: Cân bằng pH (5.6–6.2) bằng H3PO4 & đo EC giá thể định kỳ', duration: '40 phút', type: 'H5P + SCORM 1.2' },
      { id: 4, title: 'Chuyên đề 4: Phác đồ IPM sinh học Trichoderma & ghi sổ Nhật ký VietGAP', duration: '45 phút', type: 'SCORM 1.2 + GPS' },
    ],
    h5p_prompt: 'Mô phỏng xử lý sự cố: Khi EC dung dịch hồi lưu vượt 2.8 mS/cm do nắng nóng bốc hơi, nông hộ cần hạ EC về 1.6 mS/cm bằng bước rửa giá thể nước sạch (pH 6.0) trong 2 chu kỳ tưới trước khi cấp lại Stock A-B.',
    questions: [
      {
        q: 'Vì sao tuyệt đối KHÔNG đổ trực tiếp dung dịch mẹ Stock A (chứa Canxi Nitrat) vào Stock B (chứa Sunfat và Photphat) ở nồng độ đậm đặc?',
        options: [
          'Gây phản ứng kết tủa thạch cao CaSO4 và Canxi Photphat làm tắc đầu nhỏ giọt',
          'Làm tăng oxy hòa tan DO quá mức khiến rễ bị ngộ độc',
          'Làm giảm pH xuống dưới 2.0 ngay lập tức',
          'Biến đổi màu nước thành xanh lục'
        ],
        correct: 0,
      },
      {
        q: 'Ngưỡng pH tối ưu giúp cây rau ăn quả và dâu tây hấp thu đồng đều đa-trung-vi lượng trong giá thể xơ dừa là bao nhiêu?',
        options: ['3.5 – 4.2', '5.6 – 6.2', '7.8 – 8.5', '8.5 – 9.2'],
        correct: 1,
      },
      {
        q: 'Chỉ số VPD (thiếu hụt áp suất hơi nước) lý tưởng trong nhà màng Đà Lạt để khí khổng mở tối ưu quang hợp là:',
        options: ['0.1 – 0.2 kPa', '0.8 – 1.2 kPa', '3.0 – 4.5 kPa', '5.0 – 6.0 kPa'],
        correct: 1,
      },
      {
        q: 'Theo tiêu chuẩn VietGAP TCVN 11892-1:2017, chế phẩm sinh học Trichoderma spp. kết hợp Bacillus subtilis có tác dụng chính là gì?',
        options: [
          'Kích thích chín ép đồng loạt sau thu hoạch',
          'Đối kháng nấm Fusarium/Phytophthora gây thối rễ với thời gian cách ly PHI = 0 ngày',
          'Thay thế hoàn toàn nguồn nước tưới nhỏ giọt',
          'Làm tăng độ dẫn điện EC lên trên 4.0 mS/cm'
        ],
        correct: 1,
      },
      {
        q: 'Lưu lượng chuẩn của đầu nhỏ giọt bù áp phổ biến trên giá thể rau ăn quả trong nhà màng là:',
        options: ['0.1 L/giờ', '1.6 – 2.0 L/giờ', '25 L/giờ', '100 L/giờ'],
        correct: 1,
      },
    ],
  },
  CHEBIEN01: {
    pdf_title: 'Quy chuẩn Chuỗi Lạnh Sau Thu hoạch & Đóng gói MAP/CA Xuất khẩu',
    pdf_summary: 'Hướng dẫn hạ nhiệt đồng ruộng Pre-cooling trong 120 phút vàng sau hái (xuống 2–4°C đối với rau lá và dâu tây), sấy lạnh bơm nhiệt ở 35–42°C giữ hoạt chất, và bao gói khí quyển điều biến MAP.',
    station_name: 'Xưởng Sơ chế & Kho Lạnh Pre-cooling Đà Lạt',
    target_lat: 11.9412,
    target_lng: 108.4581,
    expected_qr_prefix: 'QR-CHEBIEN01',
    modules: [
      { id: 1, title: 'Chuyên đề 1: Thu hái đúng độ chín sinh lý & hạ nhiệt đồng ruộng trong 2 giờ vàng', duration: '45 phút', type: 'Video HD + PDF' },
      { id: 2, title: 'Chuyên đề 2: Công nghệ rửa Ozone/Nước điện giải khử khuẩn bề mặt nông sản', duration: '40 phút', type: 'Video HD + H5P' },
      { id: 3, title: 'Chuyên đề 3: Sấy lạnh bơm nhiệt 35–42°C & Đóng gói khí quyển biến đổi MAP', duration: '50 phút', type: 'SCORM 1.2' },
      { id: 4, title: 'Chuyên đề 4: Dán tem truy xuất QR Code lô hàng & Kiểm soát Cold-chain', duration: '45 phút', type: 'Thực hành GPS' },
    ],
    h5p_prompt: 'Mô phỏng Cold-chain: Khi nông sản thu hái ở 24°C ngoài đồng, cần đưa qua phòng Pre-cooling hút chân không hoặc gió lạnh cưỡng bức trong vòng tối đa 2 giờ để giảm hô hấp tế bào xuống dưới 20%.',
    questions: [
      {
        q: 'Khoảng thời gian vàng để thực hiện hạ nhiệt sơ bộ (Pre-cooling) sau khi thu hoạch rau ăn lá & dâu tây là:',
        options: ['Trong vòng 2 giờ đầu sau thu hái', 'Sau 24 giờ phơi nắng nhẹ', 'Sau 48 giờ khi đóng thùng carton', 'Không cần hạ nhiệt sơ bộ'],
        correct: 0,
      },
      {
        q: 'Ưu điểm lớn nhất của công nghệ sấy lạnh bơm nhiệt (35–42°C) so với sấy nhiệt truyền thống là gì?',
        options: [
          'Tiêu tốn nhiều điện hơn và làm cháy cạnh sản phẩm',
          'Giữ nguyên màu sắc, hương vị và hoạt chất vitamin nhạy nhiệt',
          'Làm tăng độ ẩm sản phẩm lên 60%',
          'Chỉ dùng được cho gỗ xây dựng'
        ],
        correct: 1,
      },
      {
        q: 'Đóng gói khí quyển biến đổi (MAP) hoạt động theo nguyên lý điều chỉnh hỗn hợp khí nào?',
        options: [
          'Tăng O2 lên 80% để trái hô hấp mạnh hơn',
          'Giảm O2 (2–5%) và tăng nhẹ CO2 (3–10%) để làm chậm tốc độ hô hấp & lão hóa',
          'Bơm khí Clo 10% vào túi',
          'Hút 100% chân không đối với rau xà lách giòn'
        ],
        correct: 1,
      },
      {
        q: 'Nhiệt độ bảo quản lạnh tối ưu giúp kéo dài thời gian tươi giòn của rau xà lách & cải kale VietGAP là:',
        options: ['18°C – 22°C', '1°C – 4°C với ẩm độ tương đối 90–95%', '-18°C cấp đông đá', '10°C và ẩm độ 30%'],
        correct: 1,
      },
      {
        q: 'Mã QR truy xuất nguồn gốc gắn trên mỗi khay nông sản đóng gói cần liên kết trực tiếp với thông tin nào?',
        options: [
          'Mật khẩu quản trị của nông trại',
          'Mã lô sản xuất, nhật ký canh tác điện tử, thời gian cách ly PHI và chữ ký số toàn vẹn',
          'Số tài khoản ngân hàng của khách hàng',
          'Giá bán sỉ nội bộ'
        ],
        correct: 1,
      },
    ],
  },
  IOTNHAMANG01: {
    pdf_title: 'Cẩm nang Vận hành Cảm biến Vi khí hậu IoT & Bộ Điều khiển Nhà màng',
    pdf_summary: 'Hướng dẫn hiệu chuẩn cảm biến DHT/SHT, cảm biến bức xạ PAR, đầu dò EC/pH nội tuyến, giao thức MQTT/Modbus RS485 và kịch bản tự động mở lưới cắt nắng, quạt hút, cooling pad theo ngưỡng VPD.',
    station_name: 'Trạm Nhà màng IoT Thực nghiệm — Phường 8, Đà Lạt',
    target_lat: 11.9588,
    target_lng: 108.4421,
    expected_qr_prefix: 'QR-IOTNHAMANG01',
    modules: [
      { id: 1, title: 'Chuyên đề 1: Kiến trúc hệ thống IoT nông nghiệp (Sensor → Gateway → Cloud LMS)', duration: '45 phút', type: 'Video HD + PDF' },
      { id: 2, title: 'Chuyên đề 2: Hiệu chuẩn cảm biến EC/pH công nghiệp bằng dung dịch chuẩn 1.41 mS/cm', duration: '45 phút', type: 'SCORM 1.2' },
      { id: 3, title: 'Chuyên đề 3: Tự động hóa rèm cắt nắng, quạt đối lưu & Cooling Pad theo ngưỡng nhiệt-ẩm', duration: '50 phút', type: 'H5P Tương tác' },
      { id: 4, title: 'Chuyên đề 4: Xử lý cảnh báo mất kết nối cảm biến & vận hành dự phòng an toàn', duration: '40 phút', type: 'Thực hành QR' },
    ],
    h5p_prompt: 'Kịch bản IoT: Khi bức xạ mặt trời vượt 800 W/m2 và nhiệt độ nhà màng chạm 29°C, rơ-le tự động đóng lưới cắt nắng 50% và kích hoạt quạt đối lưu kết hợp tấm làm mát Cooling Pad.',
    questions: [
      {
        q: 'Thông số nào dùng để hiệu chuẩn 2 điểm (2-point calibration) cho đầu dò pH nội tuyến của hệ thống châm phân IoT?',
        options: [
          'Dung dịch đệm chuẩn pH 4.01 và pH 7.00 (hoặc 6.86)',
          'Nước máy sinh hoạt và nước mưa',
          'Dung dịch muối ăn 10%',
          'Dung dịch đường Glucose 5%'
        ],
        correct: 0,
      },
      {
        q: 'Dung dịch chuẩn phổ biến nhất để hiệu chuẩn cảm biến đo độ dẫn điện EC trong thủy canh là:',
        options: ['1.413 mS/cm (1413 µS/cm) ở 25°C', '10.0 pH', '100 mg/L O2', '500 lux'],
        correct: 0,
      },
      {
        q: 'Độ ẩm tương đối (RH) trong nhà màng quá cao (> 90%) kéo dài về đêm dễ kích phát rủi ro nào nhất?',
        options: [
          'Cháy nắng đầu lá do bức xạ UV quá lớn',
          'Phát sinh nấm sương mai, thối xám Botrytis và giảm thoát hơi nước vận chuyển Canxi',
          'Tăng tốc độ thụ phấn tự nhiên',
          'Giảm nhu cầu điện chiếu sáng ban đêm'
        ],
        correct: 1,
      },
      {
        q: 'Giao thức truyền thông nhẹ thường được sử dụng để gửi gói tin telemetry nhiệt độ/ẩm/EC từ ESP32/Gateway lên máy chủ là:',
        options: ['FTP', 'MQTT', 'SMTP', 'POP3'],
        correct: 1,
      },
      {
        q: 'Tác dụng chính của hệ thống lưới cắt nắng tự động đóng mở theo cảm biến bức xạ PAR là:',
        options: [
          'Chống sốc nhiệt và cháy lá trưa nắng đồng thời tối ưu ánh sáng buổi sáng/chiều',
          'Ngăn hoàn toàn lượng mưa ngoài trời thay cho màng PE',
          'Thay thế hệ thống tưới nhỏ giọt',
          'Tăng nồng độ CO2 lên 5.000 ppm'
        ],
        correct: 0,
      },
    ],
  },
  THUYCANH01: {
    pdf_title: 'Quy trình Thủy canh Hồi lưu NFT & Kiểm soát Nitrat (NO3-) Chuẩn VietGAP',
    pdf_summary: 'Thông số độ dốc máng 1:40 (2.5%), lưu lượng màng nước mỏng 1.5–2.0 L/phút/máng, oxy hòa tan DO >= 6 mg/L, hạ nhiệt bồn hồi lưu < 24°C và cắt dinh dưỡng (Flush) 48h trước thu hoạch.',
    station_name: 'Giàn Thủy canh Hồi lưu NFT — Đức Trọng, Lâm Đồng',
    target_lat: 11.7354,
    target_lng: 108.3745,
    expected_qr_prefix: 'QR-THUYCANH01',
    modules: [
      { id: 1, title: 'Chuyên đề 1: Thiết kế máng NFT độ dốc 1:40 & lưu lượng màng dinh dưỡng tối ưu', duration: '45 phút', type: 'Video HD + PDF' },
      { id: 2, title: 'Chuyên đề 2: Duy trì oxy hòa tan DO >= 6 mg/L & làm mát bồn hồi lưu < 24°C', duration: '45 phút', type: 'H5P Tương tác' },
      { id: 3, title: 'Chuyên đề 3: Công thức dinh dưỡng thủy canh rau ăn lá (EC 1.2–1.6 mS/cm)', duration: '50 phút', type: 'SCORM 1.2' },
      { id: 4, title: 'Chuyên đề 4: Quy trình Flush nước sạch giảm tồn dư Nitrat trước thu hoạch', duration: '40 phút', type: 'Thực hành GPS' },
    ],
    h5p_prompt: 'Lưu ý sinh lý rễ NFT: Khi nhiệt độ dung dịch vượt quá 28°C, lượng oxy hòa tan giảm mạnh gây thối rễ Pythium. Cần sục khí liên tục và cách nhiệt bồn chứa ngầm.',
    questions: [
      {
        q: 'Độ dốc tiêu chuẩn của máng thủy canh hồi lưu NFT giúp màng dinh dưỡng chảy đều, không đọng úng là:',
        options: ['0% (nằm ngang tuyệt đối)', '1:40 đến 1:50 (khoảng 2–2.5 cm trên 1 mét chiều dài)', '1:2 (dốc 50%)', '1:500'],
        correct: 1,
      },
      {
        q: 'Ngưỡng oxy hòa tan (DO) tối thiểu cần duy trì trong bồn dinh dưỡng thủy canh để bộ rễ hô hấp khỏe mạnh là:',
        options: ['< 1 mg/L', '>= 6 mg/L', '0.5 mg/L', 'Chỉ cần sục khí 1 giờ/tuần'],
        correct: 1,
      },
      {
        q: 'Biện pháp hiệu quả để giảm dư lượng Nitrat (NO3-) trong rau thủy canh ngay trước thu hoạch đạt chuẩn VietGAP là:',
        options: [
          'Tăng gấp đôi lượng đạm Canxi Nitrat trước khi cắt 1 ngày',
          'Chạy nước sạch điều chỉnh pH 6.0 (Flush) trong 24–48 giờ trước thu hoạch',
          'Tắt hoàn toàn máy bơm 5 ngày',
          'Tăng EC lên 3.5 mS/cm'
        ],
        correct: 1,
      },
      {
        q: 'Khi xử lý giá thể mụn xơ dừa mới trước khi gieo ươm cây con, thao tác bắt buộc là:',
        options: [
          'Xả rửa Tanin/Lignin và kiểm tra EC nước xả xuống dưới 0.5 mS/cm',
          'Trộn trực tiếp vôi sống 50% rồi gieo hạt ngay',
          'Ngâm dầu khoáng trước khi ươm',
          'Phơi khô và không cần làm ẩm'
        ],
        correct: 0,
      },
      {
        q: 'Ngưỡng EC thích hợp nhất cho xà lách Romaine giai đoạn phát triển thân lá trên giàn NFT là:',
        options: ['0.1 – 0.2 mS/cm', '1.2 – 1.6 mS/cm', '4.5 – 5.0 mS/cm', '6.0 – 7.5 mS/cm'],
        correct: 1,
      },
    ],
  },
  SAURIENG01: {
    pdf_title: 'Quy trình Canh tác Sầu riêng Xuất khẩu, Mã số Vùng trồng (PUC) & Kiểm soát Cadimi',
    pdf_summary: 'Chuẩn hóa quy trình xuất khẩu chính ngạch: Quản lý cơi đọt đồng loạt, xiết nước & đậy bạt tạo khô hạn phân hóa mầm hoa, thụ phấn bổ sung ban đêm, phòng nấm Phytophthora và kiểm soát tuyệt đối Cadimi/kim loại nặng trong phân bón.',
    station_name: 'Vườn Sầu riêng Xuất khẩu VietGAP — Đạ Huoai / Krông Pắc',
    target_lat: 12.69531,
    target_lng: 108.31245,
    expected_qr_prefix: 'QR-SAURIENG01',
    modules: [
      { id: 1, title: 'Chuyên đề 1: Quản lý 2–3 cơi đọt khỏe trước xử lý ra hoa đồng loạt', duration: '50 phút', type: 'Video HD + PDF' },
      { id: 2, title: 'Chuyên đề 2: Kỹ thuật xiết nước tạo khô hạn, tỉa bông & thụ phấn bổ sung ban đêm', duration: '45 phút', type: 'SCORM 1.2' },
      { id: 3, title: 'Chuyên đề 3: Phòng trừ xì mủ thân Phytophthora & Rệp sáp tuân thủ Mã vùng PUC', duration: '45 phút', type: 'H5P Tương tác' },
      { id: 4, title: 'Chuyên đề 4: Kiểm soát Cadimi & ghi sổ nhật ký điện tử xuất khẩu chính ngạch', duration: '40 phút', type: 'Thực hành GPS' },
    ],
    h5p_prompt: 'Cảnh báo xuất khẩu: Chỉ sử dụng phân Lân/DAP đã kiểm nghiệm hàm lượng Cadimi đạt chuẩn cho phép; ghi chép 100% vật tư đầu vào theo Mã số vùng trồng (PUC) để thông quan.',
    questions: [
      {
        q: 'Để đạt tiêu chuẩn xuất khẩu chính ngạch Sầu riêng, kim loại nặng nào trong phân bón lân và đất cần được kiểm soát đặc biệt nghiêm ngặt?',
        options: ['Canxi (Ca)', 'Cadimi (Cd)', 'Magie (Mg)', 'Lưu huỳnh (S)'],
        correct: 1,
      },
      {
        q: 'Bệnh nứt thân xì mủ gây chết nhanh trên cây Sầu riêng chủ yếu do tác nhân nào gây ra?',
        options: ['Nấm Phytophthora palmivora', 'Thiếu ánh sáng nhẹ', 'Bọ rùa ăn rệp', 'Tưới nhỏ giọt quá ít'],
        correct: 0,
      },
      {
        q: 'Thời điểm thực hiện thụ phấn bổ sung bằng chổi lông cho hoa Sầu riêng đạt tỷ lệ đậu trái tròn cao nhất là:',
        options: ['12 giờ trưa nắng gắt', '19 giờ – 22 giờ tối khi hoa Sầu riêng nở rộ và tung phấn', '5 giờ sáng sau mưa lớn', 'Khi trái đã nặng 2 kg'],
        correct: 1,
      },
      {
        q: 'Mã số vùng trồng (PUC) và Mã cơ sở đóng gói (PHC) trong xuất khẩu Sầu riêng có vai trò gì?',
        options: [
          'Chỉ dùng để trang trí bao bì',
          'Định danh nguồn gốc vườn trồng & nhà đóng gói phục vụ truy xuất kiểm dịch thực vật của nước nhập khẩu',
          'Thay thế hoàn toàn giấy phép kinh doanh',
          'Dùng để miễn thuế thu nhập'
        ],
        correct: 1,
      },
      {
        q: 'Mục tiêu giữ lá già xanh khỏe và chặn đọt non đúng thời điểm khi Sầu riêng đang xổ nhụy – đậu trái non nhằm tránh hiện tượng gì?',
        options: [
          'Tránh cạnh tranh dinh dưỡng giữa đọt non và trái non gây rụng trái hàng loạt',
          'Giúp cây cao thêm 5 mét trong 1 tuần',
          'Làm rụng hết lá trên cây',
          'Thay đổi giống Dona sang Ri6'
        ],
        correct: 0,
      },
    ],
  },
  PHANVISINH01: {
    pdf_title: 'Kỹ thuật Ủ Phân Hữu cơ Vi sinh Tỷ lệ C/N = 25–30:1 & Nhân Sinh khối Trichoderma',
    pdf_summary: 'Công thức phối trộn phế phụ phẩm nông nghiệp (vỏ cà phê, rơm rạ, phân chuồng) đạt ẩm độ 55–60%, giữ pha ưa nhiệt 55–65°C diệt hạt cỏ và mầm bệnh, sau đó cấy bổ sung Trichoderma + Bacillus khi nhiệt độ hạ xuống < 40°C.',
    station_name: 'Khu Ủ Phân Hữu cơ Tuần hoàn — Đà Lạt',
    target_lat: 11.8842,
    target_lng: 108.3129,
    expected_qr_prefix: 'QR-PHANVISINH01',
    modules: [
      { id: 1, title: 'Chuyên đề 1: Tính toán phối trộn nguyên liệu đạt tỷ lệ Carbon/Nitơ (C/N = 25–30:1)', duration: '45 phút', type: 'Video HD + PDF' },
      { id: 2, title: 'Chuyên đề 2: Quản lý ẩm độ 55–60% & kiểm soát nhiệt độ đống ủ hiếu khí 55–65°C', duration: '45 phút', type: 'SCORM 1.2' },
      { id: 3, title: 'Chuyên đề 3: Quy trình cấy chủng nấm đối kháng Trichoderma & vi khuẩn Bacillus đúng nhiệt độ', duration: '45 phút', type: 'H5P Tương tác' },
      { id: 4, title: 'Chuyên đề 4: Đánh giá độ hoai mục & ứng dụng cải tạo đất trồng rau/cà phê', duration: '45 phút', type: 'Thực hành GPS' },
    ],
    h5p_prompt: 'Nguyên tắc vàng vi sinh: Tuyệt đối KHÔNG cấy nấm Trichoderma khi tâm đống ủ đang nóng 60°C vì bào tử nấm có ích sẽ bị chết nhiệt.',
    questions: [
      {
        q: 'Tỷ lệ Carbon trên Nitơ (C/N) lý tưởng ban đầu khi phối trộn đống ủ phân hữu cơ hiếu khí là bao nhiêu?',
        options: ['5 : 1', '25 – 30 : 1', '120 : 1', '200 : 1'],
        correct: 1,
      },
      {
        q: 'Mục đích của giai đoạn nhiệt độ đống ủ tăng lên 55–65°C kéo dài vài ngày trong ủ hiếu khí là gì?',
        options: [
          'Làm bay hơi hết chất hữu cơ',
          'Tiêu diệt nấm bệnh, tuyến trùng, ấu trùng sâu hại và hạt cỏ dại',
          'Giúp đống ủ đóng băng',
          'Tạo mùi hôi thối amoniac'
        ],
        correct: 1,
      },
      {
        q: 'Thời điểm thích hợp nhất để cấy bổ sung chế phẩm nấm đối kháng Trichoderma spp. vào phân ủ là khi nhiệt độ đống ủ:',
        options: [
          'Đang ở đỉnh nóng 65–70°C',
          'Đã qua giai đoạn ủ nóng, hạ xuống ổn định dưới 40°C (giai đoạn ủ chín)',
          'Vừa trộn nguyên liệu tươi phút đầu tiên',
          'Khi phân khô hoàn toàn 0% ẩm độ'
        ],
        correct: 1,
      },
      {
        q: 'Cách kiểm tra nhanh độ ẩm đống ủ đạt khoảng 55–60% tại nông trại mà không cần máy đo là:',
        options: [
          'Nắm chặt một nắm nguyên liệu, thấy ẩm tay, rỉ nhẹ 1–2 giọt nước qua kẽ tay nhưng không chảy thành dòng',
          'Đổ nước ngập lên bề mặt đống ủ 20 cm',
          'Nguyên liệu bay bụi trắng khi thổi',
          'Nguyên liệu nổi trên mặt hồ'
        ],
        correct: 0,
      },
      {
        q: 'Dấu hiệu nhận biết đống phân hữu cơ đã hoai mục hoàn toàn sẵn sàng bón cho cây trồng là:',
        options: [
          'Nhiệt độ tâm đống ủ trở về ngang nhiệt độ môi trường, màu nâu đen đồng nhất, mùi đất ẩm thơm nhẹ',
          'Còn nóng 65°C và bốc mùi khai nồng nặc',
          'Vẫn nguyên vẹn hình dạng phân tươi ban đầu',
          'Có nhiều giòi ruồi và nhớt'
        ],
        correct: 0,
      },
    ],
  },
  'DAUTAY-CNC-01': {
    pdf_title: 'Quy chuẩn VietGAP Canh tác Dâu tây Albion & Hoa Cúc Nhà màng Đà Lạt',
    pdf_summary: 'Canh tác giá thể xơ dừa-đá trân châu Perlite trên giàn treo, cân bằng EC 1.4–1.8 mS/cm và pH 5.6–6.0, bổ sung ong/thiên địch thụ phấn, treo bẫy dính xanh dương ngừa bọ trĩ Frankliniella occidentalis và đạt PHI = 0 ngày.',
    station_name: 'Nhà màng Dâu tây Công nghệ cao Phường 7, Đà Lạt',
    target_lat: 11.9642,
    target_lng: 108.4385,
    expected_qr_prefix: 'QR-DAUTAY-CNC-01',
    modules: [
      { id: 1, title: 'Chuyên đề 1: Thiết kế giàn máng giá thể sạch & cấy giống dâu tây nuôi cấy mô F1', duration: '45 phút', type: 'Video HD + PDF' },
      { id: 2, title: 'Chuyên đề 2: Điều tiết vi khí hậu nhà màng & thụ phấn nâng tỷ lệ trái loại A', duration: '45 phút', type: 'SCORM 1.2' },
      { id: 3, title: 'Chuyên đề 3: Châm phân Fertigation nuôi trái giàu Kali & Canxi-Bo chống thối đầu quả', duration: '50 phút', type: 'H5P Tương tác' },
      { id: 4, title: 'Chuyên đề 4: Phòng trừ bọ trĩ/nhện đỏ sinh học IPM & truy xuất QR hộp dâu', duration: '45 phút', type: 'Thực hành QR+GPS' },
    ],
    h5p_prompt: 'Chẩn đoán nhanh Dâu tây: Cháy mép lá non và sẹo trái thường do thiếu Canxi cục bộ khi RH quá cao khiến cây không thoát hơi nước kéo Ca lên lá non.',
    questions: [
      {
        q: 'Loại bẫy dính màu nào thu hút hiệu quả nhất đối với đối tượng bọ trĩ (Thrips) hại hoa và trái Dâu tây trong nhà màng?',
        options: ['Màu đen tuyền', 'Màu xanh dương (hoặc vàng kết hợp xanh dương)', 'Màu trong suốt', 'Màu xám bê tông'],
        correct: 1,
      },
      {
        q: 'Ngưỡng EC và pH khuyến nghị cho Dâu tây giai đoạn ra hoa – nuôi trái trên giá thể xơ dừa là:',
        options: ['EC 1.5 – 1.8 mS/cm và pH 5.6 – 6.0', 'EC 0.2 mS/cm và pH 8.5', 'EC 4.5 mS/cm và pH 3.5', 'EC 3.5 mS/cm và pH 8.0'],
        correct: 0,
      },
      {
        q: 'Hỗn hợp giá thể sạch nhẹ, giữ ẩm nhưng thoát nước nhanh thường dùng trong máng treo Dâu tây Đà Lạt gồm:',
        options: [
          '100% đất sét nặng',
          'Mụn xơ dừa đã xả chát + Đá trân châu Perlite (hoặc vỏ trấu hun)',
          'Cát biển chưa rửa mặn',
          'Đá sỏi xây dựng cỡ lớn'
        ],
        correct: 1,
      },
      {
        q: 'Vì sao trồng Dâu tây trên giàn máng cao cách mặt đất 1.0–1.2m giúp giảm mạnh bệnh thối trái Botrytis?',
        options: [
          'Do quả không tiếp xúc trực tiếp với đất ẩm, thông thoáng gió và khô sương nhanh',
          'Do giàn cao ngăn ánh sáng mặt trời chiếu tới quả',
          'Do cây không cần tưới nước',
          'Do làm quả đổi sang màu xanh'
        ],
        correct: 0,
      },
      {
        q: 'Thời điểm thu hoạch trái Dâu tây để đưa vào hộp truy xuất mã QR đạt độ ngọt Brix và thời gian bảo quản tốt nhất là:',
        options: [
          'Sáng sớm mát trời khi trái chín đỏ đạt 80–90%, giữ nguyên đài lá xanh',
          '13 giờ trưa nắng nóng khi trái chín nhũn',
          'Khi trái còn xanh trắng hoàn toàn',
          'Ngâm nước lã 5 tiếng trước khi đóng hộp'
        ],
        correct: 0,
      },
    ],
  },
  'DAUTAY-DONDUONG-01': {
    pdf_title: 'Tài liệu Tập huấn Chuyển giao HTX Đơn Dương — Dâu tây & Hoa Cúc VietGAP',
    pdf_summary: 'Phiên bản chuẩn hóa dành riêng cho vùng khí hậu Đơn Dương (nhiệt độ ban ngày ấm hơn Đà Lạt 2–3°C): Tăng cường lưới cắt nắng, chu kỳ tưới nhỏ giọt ngắn 6 lần/ngày và kiểm tra thực địa QR+GPS tại HTX Đơn Dương.',
    station_name: 'Nhà màng Dâu tây Hợp tác xã Đơn Dương, Lâm Đồng',
    target_lat: 11.7925,
    target_lng: 108.5321,
    expected_qr_prefix: 'QR-DAUTAY-DONDUONG',
    modules: [
      { id: 1, title: 'Chuyên đề 1: Thích ứng vi khí hậu Đơn Dương & xử lý giá thể trước xuống giống', duration: '45 phút', type: 'Video HD + PDF' },
      { id: 2, title: 'Chuyên đề 2: Chia nhỏ chu kỳ tưới Fertigation tránh sốc nhiệt vùng rễ', duration: '45 phút', type: 'SCORM 1.2' },
      { id: 3, title: 'Chuyên đề 3: Quản lý IPM nhện đỏ & bọ phấn trong nhà màng hợp tác xã', duration: '45 phút', type: 'H5P Tương tác' },
      { id: 4, title: 'Chuyên đề 4: Kiểm tra Rubric 4 tiêu chí & Cấp hộ chiếu QR lô sản phẩm HTX', duration: '45 phút', type: 'Thực hành QR+GPS' },
    ],
    h5p_prompt: 'Tùy biến Đơn Dương: Ban trưa nhiệt độ nhà màng tại Đơn Dương có thể tăng nhanh; cần chia làm 6 lần tưới nhỏ giọt ngắn (5–7 phút/lần) thay vì tưới dồn 1 lần để ổn định ẩm độ và EC quanh vùng rễ.',
    questions: [
      {
        q: 'Tại vùng khí hậu Đơn Dương có nền nhiệt ban ngày ấm hơn trung tâm Đà Lạt, chiến lược tưới Fertigation hợp lý nhất là:',
        options: [
          'Chỉ tưới 1 lần thật đẫm vào lúc 12 giờ trưa',
          'Chia nhỏ thành nhiều lần tưới ngắn (5–6 lần/ngày) giúp giữ ẩm ổn định và mát vùng rễ',
          'Ngừng tưới suốt ban ngày',
          'Tăng EC lên 3.8 mS/cm vào giữa trưa'
        ],
        correct: 1,
      },
      {
        q: 'Giải pháp hạ nhiệt thụ động kết hợp chủ động hiệu quả nhất trong nhà màng HTX Đơn Dương là:',
        options: [
          'Kéo lưới cắt nắng 50% khi bức xạ cao kết hợp quạt đối lưu/thông gió mái',
          'Đóng kín toàn bộ cửa thông gió suốt buổi trưa',
          'Sơn đen mái nilon PE',
          'Thắp thêm đèn sưởi ban ngày'
        ],
        correct: 0,
      },
      {
        q: 'Đối tượng dịch hại thường bùng phát mạnh trong điều kiện thời tiết nóng và khô ở nhà màng Đơn Dương là:',
        options: ['Sên trần mùa đông', 'Nhện đỏ hai chấm & bọ phấn trắng', 'Tảo lục dưới nước sâu', 'Chuột đồng lúa nước'],
        correct: 1,
      },
      {
        q: 'Trong phiếu đánh giá thực hành Rubric 4 tiêu chí (100 điểm), mỗi tiêu chí chiếm trọng số bao nhiêu điểm?',
        options: ['10 điểm', '25 điểm (4 tiêu chí x 25 = 100 điểm)', '50 điểm', '5 điểm'],
        correct: 1,
      },
      {
        q: 'Khi đóng gói Dâu tây theo chuỗi Hợp tác xã VietGAP, lô hàng chỉ được cấp mã QR truy xuất hợp lệ khi nào?',
        options: [
          'Sau khi hoàn thành ghi chép nhật ký đồng ruộng điện tử và đảm bảo thời gian cách ly PHI đạt chuẩn (0 ngày)',
          'Ngay khi vừa mua cây giống về',
          'Không cần ghi nhật ký điện tử',
          'Chỉ cần chụp ảnh đại diện'
        ],
        correct: 0,
      },
    ],
  },
};

function getCourseBlueprint(courseCode) {
  const code = String(courseCode || '').trim().toUpperCase();
  if (COURSE_BLUEPRINTS[code]) return COURSE_BLUEPRINTS[code];
  // Cloned courses inherit their closest base blueprint
  for (const key of Object.keys(COURSE_BLUEPRINTS)) {
    const prefix = key.split('-')[0];
    if (code.startsWith(prefix)) return COURSE_BLUEPRINTS[key];
  }
  return COURSE_BLUEPRINTS.CHAMSOC01;
}

function getClientQuizQuestions(courseCode) {
  const bp = getCourseBlueprint(courseCode);
  return bp.questions.map((item, idx) => ({
    index: idx,
    question: item.q,
    options: item.options,
  }));
}

function gradeQuizAnswers(courseCode, answers) {
  const bp = getCourseBlueprint(courseCode);
  const submitted = Array.isArray(answers) ? answers : [];
  let correctCount = 0;
  const breakdown = bp.questions.map((q, idx) => {
    const chosen = Number(submitted[idx]);
    const isCorrect = Number.isInteger(chosen) && chosen === q.correct;
    if (isCorrect) correctCount++;
    return { index: idx, chosen: Number.isInteger(chosen) ? chosen : null, correct: q.correct, is_correct: isCorrect };
  });
  const total = bp.questions.length;
  const score100 = Number(((correctCount / total) * 100).toFixed(2));
  return { correctCount, total, score100, breakdown };
}

function computeDistanceMeters(lat1, lng1, lat2, lng2) {
  const nLat1 = Number(lat1);
  const nLng1 = Number(lng1);
  const nLat2 = Number(lat2);
  const nLng2 = Number(lng2);
  if (![nLat1, nLng1, nLat2, nLng2].every(Number.isFinite)) return 0;
  const toRad = d => (d * Math.PI) / 180;
  const R = 6371000;
  const dLat = toRad(nLat2 - nLat1);
  const dLng = toRad(nLng2 - nLng1);
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos(toRad(nLat1)) * Math.cos(toRad(nLat2)) * Math.sin(dLng / 2) ** 2;
  return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
}

module.exports = {
  COURSE_BLUEPRINTS,
  getCourseBlueprint,
  getClientQuizQuestions,
  gradeQuizAnswers,
  computeDistanceMeters,
};
