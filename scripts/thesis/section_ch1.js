import {
  p,
  pRuns,
  h1,
  h2,
  h3,
  bullet,
  createAcademicTable,
  imageFigure,
  pageBreak,
} from './styles.js';

export function buildChapter1Section() {
  const comparisonHeaders = [
    'Tiêu chí so sánh',
    'Coursera / edX',
    'Google Classroom',
    'Canvas LMS',
    'Moodle mặc định',
    'Mầm Xanh LMS 2.0',
  ];

  const comparisonRows = [
    [
      'Mô hình bản quyền',
      'Đóng / Độc quyền',
      'Đóng (G-Suite)',
      'Thương mại / Freemium',
      'Mã nguồn mở (GPLv3)',
      'Mã nguồn mở tùy biến',
    ],
    [
      'Tối ưu cho Nông dân (UI/UX)',
      'Không (Học thuật ĐH)',
      'Trung bình (Cơ bản)',
      'Không (Phức tạp)',
      'Kém (Menu dày đặc)',
      'Rất cao (Minimalist 1 chạm)',
    ],
    [
      'Hỗ trợ chuẩn SCORM & H5P',
      'Hạn chế / Riêng biệt',
      'Không hỗ trợ',
      'Có hỗ trợ',
      'Có đầy đủ',
      'Tích hợp chuyên sâu',
    ],
    [
      'Nhiệm vụ thực địa định vị GPS',
      'Không',
      'Không',
      'Không',
      'Không có sẵn',
      'Có sẵn (mod_fieldpractice)',
    ],
    [
      'Chấm điểm ma trận Rubric',
      'Hạn chế (Peer review)',
      'Cơ bản',
      'Có',
      'Có (Phức tạp)',
      'Trực quan, xuất phiếu PDF',
    ],
    [
      'Chứng chỉ số xác thực QR',
      'Có (Nội bộ hệ thống)',
      'Không',
      'Cần bên thứ ba',
      'Cơ bản (File PDF tĩnh)',
      'HMAC-SHA256 & QR công khai',
    ],
    [
      'Liên thông nhật ký VietGAP',
      'Không',
      'Không',
      'Không',
      'Không',
      'Có (local_agriops)',
    ],
    [
      'Chi phí vận hành hạ tầng',
      'Rất cao',
      'Theo tài khoản',
      'Cao',
      'Thấp (Tự lưu trữ)',
      'Tối ưu (Docker Turbo)',
    ],
  ];

  const compWidths = [22, 15, 15, 15, 15, 18];

  return [
    h1('CHƯƠNG 1: TỔNG QUAN VỀ HỆ THỐNG E-LEARNING TRONG ĐÀO TẠO NÔNG NGHIỆP CÔNG NGHỆ CAO', 'sec_ch1'),

    h2('1.1. Bối cảnh chuyển đổi số nông nghiệp tại tỉnh Lâm Đồng và Đà Lạt', 'sec_1_1'),
    p('Tỉnh Lâm Đồng sở hữu điều kiện thổ nhưỡng và khí hậu á nhiệt đới đặc thù, từ lâu đã trở thành lá cờ đầu của cả nước trong chiến lược phát triển nông nghiệp công nghệ cao. Theo báo cáo tổng kết của Sở Nông nghiệp và Phát triển Nông thôn tỉnh Lâm Đồng tính đến cuối năm 2025, toàn tỉnh đã phát triển được hơn 66.000 hecta đất canh tác ứng dụng công nghệ cao, chiếm trên 21% tổng diện tích đất sản xuất nông nghiệp toàn tỉnh, đóng góp trên 45% tổng giá trị sản xuất toàn ngành.'),
    p('Tại các địa bàn trọng điểm như thành phố Đà Lạt, huyện Lạc Dương và Đơn Dương, phương thức canh tác truyền thống "trông trời, trông đất, trông mây" đã và đang được thay thế mạnh mẽ bằng các công nghệ nông nghiệp thông minh:'),
    bullet('Hệ thống nhà màng, nhà kính kiên cố có mái che tự động đóng mở theo cường độ bức xạ mặt trời, ngăn chặn tối đa côn trùng và sâu bệnh dịch hại ngoại lai;'),
    bullet('Hệ thống tưới nhỏ giọt kết hợp châm phân hòa tan tự động (Fertigation) theo công nghệ Israel, giúp phân bổ chính xác lượng nước tưới và nồng độ dinh dưỡng vi lượng (chỉ số EC và pH) đến từng gốc cây theo từng giai đoạn sinh trưởng;'),
    bullet('Cảm biến IoT đo nhiệt độ, độ ẩm không khí và độ ẩm đất truyền dữ liệu liên tục về điện thoại thông minh của người nông dân;'),
    bullet('Áp dụng các tiêu chuẩn quản lý chất lượng nông sản khắt khe như VietGAP, GlobalGAP, tiêu chuẩn canh tác hữu cơ nhằm đáp ứng điều kiện xuất khẩu sang các thị trường khó tính như Nhật Bản, Hàn Quốc, Hoa Kỳ và Châu Âu.'),
    p('Tuy nhiên, sự chuyển dịch nhanh chóng từ nông nghiệp truyền thống sang nông nghiệp số hóa đặt ra một thách thức rất lớn về trình độ tay nghề của người lao động. Một sai lệch nhỏ trong việc tính toán tỷ lệ hòa tan phân bón, điều chỉnh sai độ pH trong nước tưới thủy canh, hay phun thuốc bảo vệ thực vật không đúng thời gian cách ly đều có thể làm cháy rễ, hỏng toàn bộ lứa hoa quả, hoặc khiến lô nông sản bị từ chối xuất khẩu.'),

    ...imageFigure('moodle_custom/local/mamxanhapp/media/videos/nhamang_clean.jpg', 'Hình 1.1', 'Nhà màng nông nghiệp công nghệ cao ứng dụng hệ thống tưới tự động tại Đà Lạt', 'fig_1_1', 540, 310),

    h2('1.2. Thực trạng công tác tập huấn kỹ thuật và rào cản tiếp cận của nông hộ', 'sec_1_2'),
    p('Nhằm nâng cao năng lực cho người nông dân, hàng năm Sở Nông nghiệp & PTNT cùng Trung tâm Khuyến nông tỉnh Lâm Đồng đã triển khai nhiều lớp tập huấn chuyển giao khoa học kỹ thuật. Tuy nhiên, qua khảo sát thực tế tại các xã Tà Nung, Xuân Thọ (Đà Lạt) và thị trấn Lạc Dương, nhóm nghiên cứu nhận thấy phương thức đào tạo hiện hành đang đối mặt với những rào cản rất lớn:'),
    p('1. Rào cản về mặt địa lý và thời gian mùa vụ:', { indent: 0, bold: true }),
    p('Nông dân canh tác công nghệ cao phải theo dõi cây trồng hàng ngày. Các công việc như thụ phấn cho hoa, tỉa chồi nách dâu tây, thu hoạch rau sáng sớm diễn ra liên tục. Việc triệu tập nông dân đến hội trường trung tâm trong 3 đến 5 ngày liên tục thường dẫn đến tình trạng vắng mặt, học đối phó, hoặc chỉ cử đại diện đi nghe mà không thể truyền đạt trọn vẹn lại cho người trực tiếp làm việc.'),

    p('2. Hạn chế về phương pháp truyền đạt và học liệu:', { indent: 0, bold: true }),
    p('Tài liệu tập huấn hiện nay chủ yếu là sách hướng dẫn dạng in ấn hoặc tài liệu photo đen trắng dày hàng chục trang với nhiều từ ngữ học thuật chuyên sâu. Khảo sát của nhóm cho thấy độ tuổi trung bình của các chủ hộ nông nghiệp tại Đà Lạt dao động từ 42 đến 58 tuổi. Nhóm đối tượng này thường có thị lực giảm sút, ngại đọc các văn bản dài dòng, và khả năng ghi nhớ lý thuyết thuần túy sau 1 tháng chỉ đạt dưới 20%. Nông dân có nhu cầu cấp thiết xem các video thị phạm trực quan, hình ảnh minh họa sinh động và các bài thực hành tương tác từng bước.'),

    p('3. Thiếu công cụ kiểm chứng năng lực thực địa và xác thực chứng chỉ:', { indent: 0, bold: true }),
    p('Hầu hết các lớp tập huấn truyền thống chỉ đánh giá học viên bằng điểm danh trên giấy hoặc một bài kiểm tra trắc nghiệm cuối khóa. Cán bộ tập huấn không thể theo dõi học viên có thao tác cắt tỉa đúng kỹ thuật hay không khi trở về trang trại. Bên cạnh đó, giấy chứng nhận hoàn thành khóa tập huấn in trên giấy khen truyền thống rất dễ bị làm giả, không thể kiểm tra chéo tính hợp lệ khi nông dân đi xin cấp mã số vùng trồng hoặc nộp hồ sơ chứng nhận VietGAP.'),

    h2('1.3. Khảo sát các giải pháp e-Learning và nền tảng LMS hiện nay', 'sec_1_3'),
    p('Trước những bất cập nêu trên, giải pháp đào tạo trực tuyến (e-Learning) nổi lên như một xu thế tất yếu. Nhóm nghiên cứu đã tiến hành khảo sát và đánh giá toàn diện các hệ thống quản lý học tập (LMS) phổ biến nhất trên thế giới và tại Việt Nam:'),
    bullet('Coursera, edX: Là những nền tảng MOOC hàng đầu thế giới với kho bài giảng khổng lồ từ các đại học danh tiếng. Tuy nhiên, kiến trúc của các nền tảng này hoàn toàn đóng, học phí đắt đỏ, và toàn bộ quy trình học tập được thiết kế cho việc học lý thuyết trước máy tính để bàn, hoàn toàn không hỗ trợ chấm điểm bài tập thực tế tại vườn nông nghiệp;'),
    bullet('Google Classroom: Rất phổ biến trong môi trường giáo dục phổ thông nhờ giao diện tinh gọn và liên kết tài khoản Google. Mặc dù vậy, Google Classroom thiếu hẳn các tính năng sư phạm chuẩn mực như: không hỗ trợ chuẩn bài giảng SCORM/H5P, không có hệ thống quản lý năng lực (Competencies), không có ma trận tiêu chí chấm điểm Rubric đa cấp, và không hỗ trợ cơ chế cấp chứng chỉ số có chữ ký điện tử;'),
    bullet('Canvas LMS: Nền tảng học tập hiện đại với giao diện mượt mà và khả năng phân quyền mạnh mẽ. Tuy nhiên, Canvas là phần mềm thương mại có chi phí bản quyền hàng năm rất cao, kiến trúc đám mây phụ thuộc vào nhà cung cấp nước ngoài, việc tùy biến các module chuyên biệt cho nông nghiệp gặp nhiều trở ngại kỹ thuật;'),
    bullet('Moodle LMS (Modular Object-Oriented Dynamic Learning Environment): Là nền tảng LMS mã nguồn mở số 1 thế giới với hơn 300 triệu người dùng tại 240 quốc gia. Moodle cung cấp hệ sinh thái tính năng đào tạo hoàn chỉnh nhất hiện nay: hỗ trợ SCORM 1.2, H5P, Quiz đa dạng, hệ thống điểm danh chuyên nghiệp, phân quyền RBAC cực kỳ chặt chẽ, và kiến trúc mở cho phép viết plugin mở rộng không giới hạn.'),

    p('Nhóm nghiên cứu cũng đã khảo sát trực tiếp hệ thống Moodle đang vận hành tại Trường Đại học Đà Lạt (lms.dlu.edu.vn). Nền tảng này phục vụ rất hiệu quả cho công tác giảng dạy tín chỉ của sinh viên đại học. Tuy nhiên, khi cho một số nông hộ dùng thử giao diện mặc định (theme Boost nguyên bản), người nông dân cảm thấy vô cùng bối rối bởi hệ thống thanh điều hướng đa tầng, các nút bấm nhỏ, các khối thông tin phụ trợ (blocks) dày đặc chiếm diện tích màn hình điện thoại, và tình trạng giật lag khi tải trang qua sóng 4G chập chờn ngoài đồng.'),

    ...createAcademicTable(
      comparisonHeaders,
      comparisonRows,
      compWidths,
      'Bảng 1.1',
      'So sánh tính năng giữa các nền tảng e-Learning phổ biến với nhu cầu đào tạo nông nghiệp',
      'tbl_1_1'
    ),

    h2('1.4. Những vấn đề tồn tại và thách thức cốt lõi đối với e-Learning nông nghiệp', 'sec_1_4'),
    p('Từ các phân tích trên, nhóm nghiên cứu đã đúc kết được 4 thách thức cốt lõi (Core Challenges) mà một hệ thống e-Learning chuyên biệt cho nông nghiệp công nghệ cao phải giải quyết:'),
    bullet('Thách thức về Trải nghiệm Người dùng Công thái học (Ergonomic UX): Giao diện phải cực kỳ tối giản, cỡ chữ lớn, độ tương phản cao, thao tác chạm cảm ứng lớn (ngón tay nông dân làm vườn thường thô ráp), và đặc biệt màu sắc không được gây chói lóa khi sử dụng dưới ánh sáng mặt trời mạnh;'),
    bullet('Thách thức về Tính xác thực và Chống gian lận (Integrity & Non-repudiation): Chứng chỉ hoàn thành tập huấn phải có giá trị pháp lý và công nghệ cao, có thể xác thực nguồn gốc tức thì qua mã QR mà không lo bị can thiệp làm sai lệch điểm số;'),
    bullet('Thách thức về Liên thông giữa Học và Hành (Blended Practice): Đào tạo nông nghiệp không thể chỉ dừng lại ở trắc nghiệm lý thuyết. Phải có công cụ cho nông dân chụp ảnh hiện trường, đính kèm định vị GPS nơi canh tác, và chuyên gia khuyến nông có thể chấm điểm trực tiếp qua bảng tiêu chí Rubric minh bạch;'),
    bullet('Thách thức về Khả năng Tích hợp Chuỗi giá trị (Value Chain Integration): Dữ liệu quá trình học tập phải liên kết trực tiếp với Sổ tay nhật ký canh tác điện tử (Farm Logbook), tạo tiền đề cho việc truy xuất nguồn gốc nông sản an toàn theo chuẩn VietGAP.'),

    ...imageFigure('docs/screenshots/80_public_news_landing_page.png', 'Hình 1.2', 'Giao diện Cổng thông tin nông nghiệp công nghệ cao Mầm Xanh LMS dành cho khách', 'fig_1_2', 540, 290),

    h2('1.5. Mục tiêu, yêu cầu và phạm vi giải quyết của hệ thống Mầm Xanh LMS 2.0', 'sec_1_5'),
    p('Để vượt qua những thách thức trên, đề tài đề xuất giải pháp toàn diện mang tên: Hệ thống Quản lý Học tập và Tập huấn Kỹ thuật Canh tác Nông nghiệp Công nghệ cao Mầm Xanh LMS 2.0. Hệ thống được xây dựng dựa trên triết lý: "Đơn giản hóa trải nghiệm người dùng ngoài thực địa – Giữ vững sự chuẩn mực, bảo mật và toàn vẹn của nền tảng học thuật Moodle".'),
    p('Hệ thống Mầm Xanh LMS 2.0 cam kết thực hiện đầy đủ các yêu cầu sau:'),
    bullet('Về mặt Giao diện: Thiết kế riêng bộ nhận diện thương hiệu Agritrain Child Theme tuân thủ triết lý Minimalist Off-White Botanical (#F9FAF9 / #234E3E), đồng nhất một thanh điều hướng duy nhất trên đầu trang, hỗ trợ song ngữ Việt - Anh hoàn chỉnh;'),
    bullet('Về mặt Đào tạo: Xây dựng đồng bộ 8 chương trình tập huấn kỹ thuật công nghệ cao với cấu trúc sư phạm chuẩn hóa gồm Phần Thông tin chung & Tài liệu tham khảo + 4 Chuyên đề kỹ thuật chuyên sâu + Phần Kiểm tra & Đánh giá;'),
    bullet('Về mặt Nghiệp vụ chuyên biệt: Phát triển 5 plugin tự xây dựng phục vụ điểm danh, thực hành GPS, cấp chứng chỉ số bảo mật bằng HMAC-SHA256, bảng điểm chi tiết và nhật ký canh tác VietGAP;'),
    bullet('Về mặt Hạ tầng: Đóng gói vi dịch vụ gồm 5 container trên Docker Turbo, khởi chạy nhanh trong 60 giây, bảo đảm hoạt động mượt mà cho hàng trăm học viên đồng thời với tài nguyên máy chủ tiết kiệm nhất.'),

    pageBreak(),
  ];
}
