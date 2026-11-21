import {
  COLOR_PRIMARY,
  COLOR_SECONDARY,
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

export function buildChapter2Section() {
  const rubricHeaders = ['Tiêu chí đánh giá', 'Trọng số', 'Mức 1: Cần cải thiện (0 - 49%)', 'Mức 2: Đạt chuẩn (50 - 79%)', 'Mức 3: Xuất sắc (80 - 100%)'];
  const rubricRows = [
    [
      '1. Bảo hộ lao động & An toàn thực phẩm',
      '20%',
      'Không trang bị găng tay, ủng, hoặc mang đồ bảo hộ không đúng quy định VietGAP.',
      'Trang bị bảo hộ cơ bản, tuân thủ vệ sinh tay trước khi tiếp xúc với chồi non và quả.',
      'Tuyệt đối tuân thủ quy chuẩn VietGAP, khử trùng dụng cụ kéo cắt trước và sau thao tác.',
    ],
    [
      '2. Thao tác kỹ thuật hiện trường',
      '30%',
      'Cắt tỉa sai vị trí mắt ghép, làm dập thân cây hoặc gãy cành mang quả non.',
      'Cắt đúng cành tăm, chồi nách, vết cắt tương đối gọn, không ảnh hưởng sinh trưởng cây.',
      'Vết cắt dứt khoát góc 45 độ, bôi keo liền sẹo đúng cách, tỉa chồi chuẩn xác 100%.',
    ],
    [
      '3. Đo đạc & Cân đối nồng độ dinh dưỡng',
      '30%',
      'Đo sai chỉ số EC/pH, pha trộn sai tỷ lệ dung dịch A và B gây sốc rễ.',
      'Pha đúng nồng độ theo chỉ dẫn, chỉ số EC và pH nằm trong ngưỡng cho phép (±10%).',
      'Chỉ số EC đạt chuẩn 1.8 mS/cm, pH 5.8 chuẩn xác, ghi chép nhật ký pha chế đầy đủ.',
    ],
    [
      '4. Thu dọn & Vệ sinh đồng ruộng',
      '20%',
      'Để rác thải, bao bì thuốc BVTV bừa bãi trong khu vực nhà màng sau thao tác.',
      'Thu gom tàn dư lá bệnh vào túi ni-lông, để đúng khu vực quy định của vườn.',
      'Thu dọn sạch sẽ, phân loại rác thải nguy hại và hữu cơ đúng quy trình chứng nhận VietGAP.',
    ],
  ];
  const rubricWidths = [20, 10, 23, 23, 24];

  const paletteHeaders = ['Tên vai trò màu sắc', 'Mã Hex', 'Ứng dụng trong Giao diện', 'Tiêu chuẩn tương phản WCAG'];
  const paletteRows = [
    ['Off-White Canvas', '#F9FAF9', 'Nền chính của toàn bộ trang web (Dashboard, Bài học, Form)', 'Giảm phản xạ ánh sáng mặt trời ngoài trời'],
    ['Surface Card', '#FFFFFF', 'Nền các thẻ bài học, hộp thông báo, bảng điểm', 'Tạo lớp phân cách thị giác êm dịu'],
    ['Deep Botanical Green', '#1B4332', 'Thanh Header, Tiêu đề chương mục chính, Nút hành động chính', 'WCAG AAA (Tương phản 12.8:1 trên nền trắng)'],
    ['Academic Forest', '#234E3E', 'Màu thanh Navbar, Nút bấm Primary, Thanh tiến độ khóa học', 'WCAG AAA (Tương phản 9.5:1 trên nền trắng)'],
    ['Sage Mint', '#E8F0EC', 'Nền các thẻ huy hiệu, Hộp ghi chú Callout, Khung điểm danh', 'Tạo cảm giác dễ chịu, thư giãn thị giác'],
    ['Charcoal Slate Text', '#1F2937', 'Văn bản nội dung chính, Bảng điểm, Nhãn form', 'Rõ nét, không gây lóa mắt như màu đen tuyệt đối'],
    ['Muted Cool Gray', '#64748B', 'Văn bản phụ, Mô tả bài học, Thời gian đăng bài', 'WCAG AA (Tương phản 4.8:1 trên nền trắng)'],
    ['Amber Gold Alert', '#D97706', 'Thông báo cảnh báo, Đánh giá cần chú ý, Badge vàng', 'Nổi bật nhưng không gây khó chịu thị giác'],
  ];
  const palWidths = [22, 14, 40, 24];

  return [
    h1('CHƯƠNG 2: CƠ SỞ LÝ THUYẾT VÀ CÔNG NGHỆ ÁP DỤNG', 'sec_ch2'),

    h2('2.1. Nền tảng Moodle LMS 4.5 LTS và kiến trúc module hóa', 'sec_2_1'),
    p('Moodle là một hệ thống quản lý học tập mã nguồn mở được khởi xướng bởi Martin Dougiamas từ năm 2002 dựa trên triết lý giáo dục kiến tạo xã hội (Social Constructivist Pedagogy). Trải qua hơn hai thập kỷ phát triển, Moodle đã khẳng định vị thế là nền tảng e-Learning hàng đầu toàn cầu. Phiên bản Moodle 4.5 LTS (Long-Term Support) được chính thức phát hành vào tháng 10 năm 2024, mang đến sự cải tiến đột phá về cả kiến trúc lõi lẫn giao diện người dùng, đồng thời được hỗ trợ an ninh bảo mật dài hạn đến tháng 10 năm 2027.'),
    p('Moodle 4.5 sở hữu các ưu điểm công nghệ vượt trội:'),
    bullet('Hỗ trợ đầy đủ các phiên bản PHP hiện đại (PHP 8.1, PHP 8.2 và tối ưu cho PHP 8.3) kết hợp cơ chế bộ nhớ đệm OPcache JIT (Just-In-Time Compiler), mang lại tốc độ biên dịch mã nguồn và thời gian phản hồi máy chủ nhanh gấp 2.5 lần so với các phiên bản 3.x trước đây;'),
    bullet('Hệ thống giao diện Boost Theme hiện đại xây dựng trên Bootstrap 5 và công cụ mẫu Mustache (Logic-less templates), cho phép phân tách triệt để giữa mã xử lý logic nghiệp vụ và mã hiển thị giao diện;'),
    bullet('Hệ thống kiểm soát truy cập dựa trên vai trò cực kỳ chi tiết (Role-Based Access Control - RBAC) với hơn 600 năng lực quyền hạn (Capabilities) khác nhau, cho phép tổ chức hệ thống phân quyền đa cấp một cách chặt chẽ;'),
    bullet('Kiến trúc module hóa (Modular Component Architecture): Cho phép lập trình viên phát triển các tính năng nghiệp vụ độc lập dưới dạng plugin mà không cần can thiệp trực tiếp vào mã nguồn cốt lõi (Core Moodle). Điều này giúp bảo đảm tính toàn vẹn của hệ thống khi nâng cấp các bản vá lỗi bảo mật định kỳ.'),

    ...imageFigure('scripts/thesis/build/diagrams/D2_layers.png', 'Hình 2.1', 'Cấu trúc module hóa các phân hệ chức năng mở rộng trên nền tảng Moodle 4.5', 'fig_2_1', 540, 310),

    h2('2.2. Các chuẩn dữ liệu e-Learning: SCORM 1.2 và H5P Interactive Content', 'sec_2_2'),
    p('Nhằm bảo đảm tính liên thông, khả năng chia sẻ và tương tác cao của học liệu số hóa trong đào tạo kỹ thuật nông nghiệp, đề tài đã nghiên cứu và ứng dụng hai chuẩn công nghệ e-Learning quốc tế phổ biến nhất hiện nay: chuẩn SCORM 1.2 và chuẩn H5P.'),

    h3('2.2.1. Chuẩn đóng gói bài giảng điện tử SCORM 1.2', 'sec_2_2_1'),
    p('SCORM (Sharable Content Object Reference Model) do tổ chức ADL (Advanced Distributed Learning) thuộc Bộ Quốc phòng Hoa Kỳ khởi xướng. SCORM 1.2 là tiêu chuẩn công nghiệp e-Learning được hỗ trợ rộng rãi nhất hiện nay. Bản chất của một gói SCORM là một tệp lưu trữ nén (.zip) chứa tệp kê khai danh mục cấu trúc imsmanifest.xml cùng các đối tượng học tập có thể chia sẻ (Shareable Content Objects - SCO).'),
    p('Khi người học kích hoạt bài học SCORM trên giao diện trình duyệt, Moodle sẽ khởi tạo một môi trường giao tiếp thực thi JavaScript (SCORM Run-Time Environment API) cung cấp các hàm tiêu chuẩn:'),
    bullet('LMSInitialize(""): Khởi tạo phiên giao tiếp dữ liệu giữa gói bài giảng và máy chủ LMS;'),
    bullet('LMSGetValue("cmi.core.student_name"), LMSGetValue("cmi.core.student_id"): Truy xuất thông tin học viên;'),
    bullet('LMSSetValue("cmi.core.lesson_status", "completed"): Cập nhật trạng thái hoàn thành bài học;'),
    bullet('LMSSetValue("cmi.core.score.raw", "95"): Đồng bộ điểm số đạt được trong các trò chơi mô phỏng vào sổ điểm chính thức của khóa học;'),
    bullet('LMSCommit(""): Đẩy dữ liệu lưu vào cơ sở dữ liệu máy chủ;'),
    bullet('LMSFinish(""): Đóng phiên học tập an toàn.'),
    p('Trong hệ thống Mầm Xanh LMS 2.0, chuẩn SCORM 1.2 được ứng dụng hiệu quả trong các bài học mô phỏng quy trình tương tác cao, điển hình như mô phỏng các bước cân đo hòa tan phân bón vi lượng theo trình tự an toàn.'),

    ...imageFigure('docs/screenshots/116_scorm_student.png', 'Hình 2.2', 'Gói bài giảng SCORM 1.2 mô phỏng kỹ thuật phối trộn phân bón hòa tan', 'fig_2_2', 540, 310),

    h3('2.2.2. Chuẩn nội dung tương tác trực tiếp H5P (HTML5 Package)', 'sec_2_2_2'),
    p('Nếu SCORM đòi hỏi các phần mềm biên soạn chuyên dụng (như Adobe Captivate, Articulate Storyline), thì H5P là chuẩn nội dung mã nguồn mở hoàn toàn mới dựa trên HTML5, CSS3 và JavaScript thuần túy. H5P cho phép cán bộ kỹ thuật và giảng viên trực tiếp tạo ra các hoạt động tương tác sinh động ngay trên trình duyệt mà không cần cài đặt phần mềm phức tạp.'),
    p('H5P cung cấp hơn 50 định dạng tương tác phong phú, trong đó nhóm nghiên cứu đã khai thác triệt để các định dạng phù hợp nhất với nông dân:'),
    bullet('Interactive Video: Video clip hướng dẫn kỹ thuật tỉa chồi nách dâu tây, đến giây thứ 45 video tự động dừng lại và hiển thị câu hỏi trắc nghiệm kiểm tra nhận thức; người học trả lời đúng mới được xem tiếp;'),
    bullet('Flashcards: Bộ thẻ nhận diện các loại sâu bệnh hại (nhện đỏ, rệp sáp, phấn trắng), giúp nông hộ lật thẻ xem triệu chứng và phương pháp phòng trị sinh học;'),
    bullet('Drag and Drop (Kéo thả): Bài tập phân loại phân bón hữu cơ và phân bón vô cơ;'),
    bullet('Branching Scenario (Tình huống phân nhánh): Nông dân đưa ra các quyết định xử lý vườn khi thời tiết sương mù bất thường, hệ thống sẽ mô phỏng kết quả mùa vụ tương ứng với từng quyết định.'),

    ...imageFigure('docs/screenshots/117_h5p_student.png', 'Hình 2.3', 'Hoạt động tương tác H5P kiểm tra thao tác cắt tỉa cành theo chuẩn kỹ thuật', 'fig_2_3', 540, 310),

    h2('2.3. Phương pháp đánh giá năng lực thực hành với thang đo Rubric đa tiêu chí', 'sec_2_3'),
    p('Một trong những điểm yếu lớn nhất của các hệ thống đào tạo trực tuyến truyền thống là chỉ đánh giá người học qua các câu hỏi trắc nghiệm khách quan (Multiple Choice Questions - MCQ). Đối với đào tạo nghề nông nghiệp, kỹ năng thực hành tại vườn mới là yếu tố quyết định giá trị mùa màng. Để giải quyết triệt để vấn đề này, đề tài đã nghiên cứu và ứng dụng phương pháp đánh giá theo thang đo tiêu chí Rubric (Rubric Assessment Methodology).'),
    p('Rubric là một ma trận đánh giá hai chiều trong đó: trục dọc là danh sách các tiêu chí kỹ thuật cốt lõi (Criteria), và trục ngang là các mức độ thành thục đạt được (Performance Levels) kèm theo các mô tả định lượng cụ thể. Khác với cách chấm điểm cảm tính theo thang điểm 10 truyền thống, phương pháp Rubric mang lại sự minh bạch tuyệt đối: người học biết chính xác mình cần làm những gì để đạt điểm tối đa, và người chấm điểm có căn cứ nhất quán để đánh giá công bằng.'),

    ...createAcademicTable(
      rubricHeaders,
      rubricRows,
      rubricWidths,
      'Bảng 2.1',
      'Quy cách các tiêu chí đánh giá thực hành kỹ thuật theo ma trận Rubric',
      'tbl_2_1'
    ),

    h2('2.4. Cơ chế bảo mật và xác thực chứng chỉ số bằng mã hóa HMAC-SHA256 & QR Code', 'sec_2_4'),
    p('Trong môi trường đào tạo nông nghiệp số, chứng chỉ điện tử hoàn thành khóa tập huấn là cơ sở quan trọng để các tổ hợp tác kết nạp xã viên, các đơn vị thu mua bao tiêu sản phẩm tin tưởng ký hợp đồng, và các cơ quan nhà nước cấp mã số vùng trồng xuất khẩu. Do đó, việc bảo đảm tính toàn vẹn (Integrity) và tính không thể chối bỏ (Non-repudiation) của chứng chỉ là yêu cầu an ninh tối thượng.'),
    p('Nhóm nghiên cứu đề xuất giải pháp chữ ký số phân tán sử dụng mã xác thực thông điệp dựa trên hàm băm mật mã HMAC-SHA256 (Hash-based Message Authentication Code). HMAC kết hợp một hàm băm mật mã học một chiều (SHA-256) với một khóa bí mật (Secret Key) được lưu trữ an toàn tại máy chủ Moodle:'),
    p('Công thức toán học của giải thuật HMAC được định nghĩa như sau:', { indent: 0, italic: true }),
    p('HMAC(K, m) = H((K\' ⊕ opad) || H((K\' ⊕ ipad) || m))', { indent: 1200, bold: true, color: COLOR_PRIMARY }),
    p('Trong đó: H là hàm băm mật mã SHA-256 (cho kết quả đầu ra có kích thước cố định 256 bit / 64 ký tự hex); K là khóa bí mật chỉ máy chủ sở hữu; K\' là khóa đã được chuẩn hóa kích thước theo độ dài khối băm (64 byte); m là chuỗi dữ liệu (Payload) cần ký số; opad là hằng số ngoài (Outer Padding) gồm các byte 0x5C lặp lại; ipad là hằng số trong (Inner Padding) gồm các byte 0x36 lặp lại; và dấu || biểu diễn phép ghép chuỗi.'),
    p('Quy trình sinh mã xác thực và tra cứu chứng chỉ số trong Mầm Xanh LMS 2.0 diễn ra như sau:'),
    bullet('Khi nông hộ thỏa mãn tất cả các điều kiện tốt nghiệp (hoàn thành 100% chuyên đề, điểm danh đủ số buổi, điểm tổng kết đạt từ 80 điểm trở lên), hệ thống tự động tổng hợp chuỗi Payload m bao gồm: Mã chứng chỉ, ID học viên, ID khóa học, Điểm số tổng kết và Thời gian cấp;'),
    bullet('Hệ thống tính toán chữ ký số Signature = HMAC-SHA256(SecretKey, m);'),
    bullet('Tạo mã phản hồi nhanh (QR Code) in trực tiếp lên phôi chứng chỉ điện tử, chứa liên kết tra cứu công khai kèm mã chứng chỉ và chữ ký kiểm chứng;'),
    bullet('Khi doanh nghiệp thu mua nông sản hoặc cơ quan chức năng dùng bất kỳ thiết bị điện thoại nào quét mã QR, máy chủ sẽ đọc Payload từ cơ sở dữ liệu, tự động tính toán lại giá trị HMAC với khóa bí mật và so sánh với giá trị băm lưu trữ. Nếu khớp hoàn toàn, trang xác thực sẽ hiện dấu tích xanh "CHỨNG NHẬN HỢP LỆ VÀ NGUYÊN BẢN". Bất kỳ sự can thiệp trái phép nào vào điểm số trong cơ sở dữ liệu đều làm cho giá trị băm bị sai lệch ngay lập tức, và hệ thống sẽ cảnh báo "CHỨNG CHỈ ĐÃ BỊ LÀM GIẢ HOẶC CHỈNH SỬA TRÁI PHÉP".'),

    ...imageFigure('scripts/thesis/build/diagrams/D6_certflow.png', 'Hình 2.4', 'Cơ chế xác thực chữ ký số HMAC-SHA256 trên mã QR chứng chỉ điện tử', 'fig_2_4', 540, 300),

    h2('2.5. Nguyên lý thiết kế giao diện công thái học Minimalist Off-White cho nông dân', 'sec_2_5'),
    p('Người nông dân và chủ trang trại là nhóm người dùng có những đặc điểm tâm sinh lý và điều kiện làm việc rất đặc thù: thường xuyên thao tác trên điện thoại thông minh giá rẻ với kích thước màn hình nhỏ, bàn tay khi làm việc dính đất hoặc đeo găng làm giảm độ nhạy cảm ứng, và môi trường làm việc ngoài trời có cường độ ánh sáng rất mạnh gây lóa mắt nghiêm trọng.'),
    p('Áp dụng các hướng dẫn từ bộ tiêu chuẩn thiết kế trải nghiệm người dùng ui-ux-pro-max-skill, nhóm nghiên cứu đã xây dựng một bộ quy chuẩn giao diện công thái học Minimalist Off-White Botanical dành riêng cho Mầm Xanh LMS 2.0 với các nguyên tắc cốt lõi:'),
    bullet('Bảng màu Off-White tự nhiên: Thay vì sử dụng nền trắng tinh khiết (#FFFFFF) gây lóa chói mắt khi mở dưới trời nắng, hệ thống sử dụng tông nền Off-White dịu nhẹ (#F9FAF9 và #F3F4F6) kết hợp với sắc xanh lục lâm nghiệp (#1B4332 và #234E3E). Bảng màu này đạt chứng nhận khả năng tiếp cận WCAG AAA với tỷ lệ tương phản vượt trội (12.8:1);'),
    bullet('Nguyên tắc "Một thanh điều hướng duy nhất" (Single Navbar): Loại bỏ hoàn toàn các thanh menu phụ (.mx-subnav) gây rối mắt; toàn bộ các nút điều hướng nghiệp vụ chính (Bảng điều khiển, Khóa học của tôi, Điểm số, Chứng chỉ, Đăng xuất) được tích hợp gọn gàng trong một thanh Navbar duy nhất trên đầu trang;'),
    bullet('Vùng chạm cảm ứng lớn (Touch Targets): Tất cả các nút bấm, thẻ bài học và hộp kiểm đều có chiều cao tối thiểu 48px, khoảng cách giữa các phần tử tối thiểu 12px, bảo đảm nông dân có thể bấm chạm chính xác một chạm mà không bao giờ bị bấm nhầm;'),
    bullet('Tuyệt đối loại bỏ biểu tượng cảm xúc phi học thuật (No-Emoji Standard): Thay thế toàn bộ emoji bằng biểu tượng vector FontAwesome và SVG chuyên nghiệp, mang lại diện mạo đĩnh đạc, hiện đại và nghiêm túc của một nền tảng giáo dục hàn lâm.'),

    ...createAcademicTable(
      paletteHeaders,
      paletteRows,
      palWidths,
      'Bảng 2.2',
      'Bảng mã màu công thái học áp dụng trong giao diện Agritrain Boost Theme',
      'tbl_2_2'
    ),

    ...imageFigure('scripts/thesis/build/diagrams/D8_coursestruct.png', 'Hình 2.5', 'Cấu trúc sư phạm chuẩn hóa của một khóa học canh tác (Thông tin chung + 4 Chuyên đề + Kiểm tra & Đánh giá)', 'fig_2_5', 540, 310),

    h2('2.6. Kiến trúc ảo hóa và tối ưu hóa hiệu năng container với Docker Turbo', 'sec_2_6'),
    p('Để hệ thống có thể vận hành ổn định trong nhiều kịch bản thực tế – từ việc triển khai trên máy chủ đám mây của Sở Nông nghiệp, máy chủ Trường Đại học Đà Lạt, cho đến việc mang về chạy cục bộ tại các Hợp tác xã vùng sâu vùng xa bằng máy chủ biên (Edge Server Mini PC) – công nghệ ảo hóa đóng gói container hóa (Containerization) bằng Docker là sự lựa chọn tối ưu.'),
    p('Mô hình kiến trúc Docker Turbo của đề tài được cấu hình qua Docker Compose, phân rã hệ thống thành 5 dịch vụ container độc lập kết nối qua mạng nội bộ ảo (như minh họa trên Sơ đồ D1 - Hình 2.6):'),
    bullet('Caddy 2 Reverse Proxy Container (moodle_proxy - HTTPS :8443): Đảm nhiệm vai trò tiếp nhận yêu cầu HTTPS, tự động quản lý chứng chỉ bảo mật TLS nội bộ, định tuyến truy cập và thực hiện nén dữ liệu tốc độ cao bằng thuật toán zstd / gzip;'),
    bullet('Moodle Application PHP 8.3 + Apache Container (moodle_app - HTTP :8090): Chạy môi trường thực thi PHP 8.3 kết hợp máy chủ Web Apache, tích hợp sẵn bộ nhớ đệm biên dịch OPcache + JIT cùng các tiện ích mở rộng gd, intl, zip, mysqli, redis, exif. Container này gắn kết trực tiếp thư mục moodle_custom chứa 5 plugin tùy biến và Theme giao diện;'),
    bullet('Database MariaDB 10.11 LTS Container (moodle_db - Cổng 3306): Hệ quản trị cơ sở dữ liệu quan hệ mạnh mẽ, được cấu hình tối ưu tham số innodb_buffer_pool_size = 512M, innodb_log_file_size = 128M, và bắt buộc sử dụng bảng mã utf8mb4_unicode_ci để hỗ trợ trọn vẹn văn bản tiếng Việt có dấu;'),
    bullet('Redis 7 In-Memory Cache Container (moodle_redis - Cổng 6379): Hệ thống lưu trữ dữ liệu khóa-giá trị (Key-Value Store) trên bộ nhớ RAM, đảm nhiệm lưu trữ phiên đăng nhập (Session) và Moodle Universal Cache (MUC), giúp giảm hơn 75% truy vấn SQL lặp lại vào cơ sở dữ liệu;'),
    bullet('Moodle Cron Worker Container (moodle_cron): Dịch vụ chạy nền độc lập khởi chạy định kỳ mỗi 60 giây một lần kịch bản cron.php của Moodle, tự động thực hiện tính toán tiến độ hoàn thành khóa học, đối chiếu điều kiện mở khóa chứng chỉ số QR và đồng bộ sổ điểm.'),

    ...imageFigure('scripts/thesis/build/diagrams/D1_deployment.png', 'Hình 2.6', 'Mô hình kiến trúc ảo hóa vi dịch vụ 5 container Docker Turbo cho hệ thống Mầm Xanh LMS 2.0 (Sơ đồ D1)', 'fig_2_6', 540, 310),

    pageBreak(),
  ];
}
