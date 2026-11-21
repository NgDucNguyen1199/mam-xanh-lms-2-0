import {
  p,
  pRuns,
  h1,
  h2,
  h3,
  h4,
  bullet,
  createAcademicTable,
  imageFigure,
  pageBreak,
} from './styles.js';

export function buildChapter3Section() {
  const rbacHeaders = ['Chức năng nghiệp vụ', 'Khách vãng lai', 'Học viên (Nông hộ)', 'Cán bộ kỹ thuật (CBKT)', 'Quản trị viên (Admin)'];
  const rbacRows = [
    ['Xem Cổng tin tức công khai', 'Cho phép', 'Cho phép', 'Cho phép', 'Cho phép'],
    ['Tra cứu chứng nhận điện tử QR', 'Cho phép', 'Cho phép', 'Cho phép', 'Cho phép'],
    ['Quét mã truy xuất nguồn gốc VietGAP', 'Cho phép', 'Cho phép', 'Cho phép', 'Cho phép'],
    ['Đăng ký / Đăng nhập tài khoản', 'Chỉ Đăng nhập', 'Đã cấp tài khoản', 'Đã cấp tài khoản', 'Toàn quyền'],
    ['Xem bài giảng SCORM, H5P, PDF', 'Từ chối (403)', 'Cho phép', 'Cho phép', 'Toàn quyền'],
    ['Nộp bài tập thực hành hiện trường GPS', 'Từ chối (403)', 'Cho phép', 'Xem nộp', 'Toàn quyền'],
    ['Thảo luận trên diễn đàn kỹ thuật', 'Từ chối (403)', 'Gửi bài & Đọc', 'Điều hành & Đọc', 'Toàn quyền'],
    ['Xem danh sách thành viên khóa học', 'Từ chối (403)', 'Từ chối (Đã ẩn tab)', 'Xem học viên lớp mình', 'Toàn quyền'],
    ['Quản lý điểm danh từng buổi học', 'Từ chối (403)', 'Chỉ xem lịch sử', 'Điểm danh & Sửa', 'Toàn quyền'],
    ['Chấm điểm bài thực địa theo Rubric', 'Từ chối (403)', 'Từ chối (403)', 'Chấm & Nhận xét', 'Toàn quyền'],
    ['Xuất Bảng điểm & Phiếu phản hồi', 'Từ chối (403)', 'Xem của mình', 'Xuất toàn lớp', 'Toàn quyền'],
    ['Ghi sổ nhật ký điện tử AgriOps', 'Từ chối (403)', 'Ghi nhật ký vườn', 'Xem nhật ký xã viên', 'Toàn quyền'],
    ['Nhân bản khóa học địa phương (Cloner)', 'Từ chối (403)', 'Từ chối (403)', 'Nhân bản mẫu', 'Toàn quyền'],
    ['Kiểm toán hệ thống thời gian thực (Audit)', 'Từ chối (403)', 'Từ chối (403)', 'Từ chối (403)', 'Toàn quyền'],
  ];
  const rbacWidths = [32, 16, 18, 18, 16];

  const certTableHeaders = ['Tên cột dữ liệu', 'Kiểu dữ liệu', 'Ràng buộc / Khóa', 'Ý nghĩa giải thích'];
  const certTableRows = [
    ['id', 'BIGINT(10)', 'PRIMARY KEY, AUTO_INCREMENT', 'Khóa chính định danh bản ghi chứng chỉ'],
    ['userid', 'BIGINT(10)', 'FOREIGN KEY -> mdl_user(id)', 'Mã ID học viên được cấp chứng chỉ'],
    ['courseid', 'BIGINT(10)', 'FOREIGN KEY -> mdl_course(id)', 'Mã ID khóa học đã hoàn thành'],
    ['cert_code', 'VARCHAR(64)', 'UNIQUE, NOT NULL', 'Mã chứng chỉ duy nhất (VD: MXCERT-2026-CHAMSOC01-0042)'],
    ['final_score', 'DECIMAL(5,2)', 'NOT NULL', 'Điểm tổng kết khóa học (thang 100)'],
    ['hmac_signature', 'VARCHAR(64)', 'NOT NULL', 'Chữ ký số xác thực tính toàn vẹn HMAC-SHA256'],
    ['status', 'TINYINT(1)', 'DEFAULT 1', 'Trạng thái chứng chỉ (1: Hợp lệ, 0: Thu hồi)'],
    ['timecreated', 'BIGINT(10)', 'NOT NULL', 'Thời điểm cấp chứng chỉ (Unix timestamp)'],
  ];
  const certWidths = [20, 18, 32, 30];

  const fpTableHeaders = ['Tên cột dữ liệu', 'Kiểu dữ liệu', 'Ràng buộc / Khóa', 'Ý nghĩa giải thích'];
  const fpTableRows = [
    ['id', 'BIGINT(10)', 'PRIMARY KEY, AUTO_INCREMENT', 'Khóa chính bài tập thực địa (fieldpractice)'],
    ['course', 'BIGINT(10)', 'FOREIGN KEY -> mdl_course(id)', 'Mã khóa học chứa bài tập thực hành'],
    ['name', 'VARCHAR(255)', 'NOT NULL', 'Tiêu đề bài tập thực hành hiện trường'],
    ['location_name', 'VARCHAR(255)', 'NOT NULL', 'Tên trạm / vườn nhà màng thực nghiệm'],
    ['target_lat, target_lng', 'VARCHAR(32)', 'NOT NULL', 'Tọa độ GPS tâm vùng thực hành chuẩn'],
    ['grade, qr_secret', 'VARCHAR(64)', 'NOT NULL', 'Thang điểm tối đa (100) và khóa sinh mã QR xoay vòng'],
    ['crit_prep..crit_quality', 'INT(4)', 'fieldpractice_sub (0 - 25)', 'Điểm 4 tiêu chí Rubric trong bảng nộp bài fieldpractice_sub'],
    ['total_grade, feedback', 'DECIMAL(5,2)', 'fieldpractice_sub', 'Tổng điểm Rubric đồng bộ Gradebook và nhận xét CBKT'],
  ];
  const fpWidths = [22, 16, 30, 32];

  const logTableHeaders = ['Tên cột dữ liệu', 'Kiểu dữ liệu', 'Ràng buộc / Khóa', 'Ý nghĩa giải thích'];
  const logTableRows = [
    ['id', 'BIGINT(10)', 'PRIMARY KEY, AUTO_INCREMENT', 'Khóa chính bản ghi nhật ký (local_agriops_log)'],
    ['batchid', 'BIGINT(10)', 'FOREIGN KEY -> local_agriops_batch(id)', 'Mã lô nông sản VietGAP liên kết'],
    ['userid', 'BIGINT(10)', 'FOREIGN KEY -> mdl_user(id)', 'Mã ID nông hộ thực hiện ghi nhật ký'],
    ['activity_type', 'VARCHAR(64)', 'NOT NULL', 'Hạng mục tác nghiệp (Tưới châm phân A-B, Đo pH/EC, Phun sinh học)'],
    ['notes', 'LONGTEXT', 'NOT NULL', 'Nội dung chi tiết thông số kỹ thuật hiện trường'],
    ['ph_val, ec_val', 'DECIMAL(4,2)', 'NOT NULL', 'Chỉ số độ pH và độ dẫn điện dinh dưỡng EC (mS/cm)'],
    ['phi_days', 'INT(4)', 'DEFAULT 0', 'Thời gian cách ly an toàn thu hoạch PHI (ngày)'],
    ['voice_input, log_time', 'BIGINT(10)', 'NOT NULL', 'Cờ nhập liệu giọng nói Web Speech và thời gian ghi'],
  ];
  const logWidths = [22, 16, 30, 32];

  return [
    h1('CHƯƠNG 3: PHÂN TÍCH VÀ THIẾT KẾ HỆ THỐNG', 'sec_ch3'),

    h2('3.1. Phân tích yêu cầu hệ thống', 'sec_3_1'),
    p('Phân tích yêu cầu là giai đoạn quyết định tính đúng đắn và khả thi của toàn bộ dự án phần mềm. Để đáp ứng chuẩn mực một hệ thống EdTech chuyên nghiệp phục vụ đồng thời nhiều đối tượng người dùng, hệ thống Mầm Xanh LMS 2.0 được phân tích dựa trên sự tương tác của 4 nhóm tác nhân chính: Khách vãng lai, Học viên (Nông hộ), Cán bộ Kỹ thuật (CBKT), và Quản trị viên hệ thống (Admin).'),

    h3('3.1.1. Yêu cầu chức năng theo từng nhóm tác nhân', 'sec_3_1_1'),
    p('1. Nhóm tác nhân Khách vãng lai (Guest / Consumer / Enterprise):', { indent: 0, bold: true }),
    bullet('Truy cập công khai Cổng thông tin & Tin tức Nông nghiệp Công nghệ cao tại trang chủ mà không bị ép buộc đăng nhập (forcelogin = 0);'),
    bullet('Xem các bài viết hướng dẫn canh tác nông nghiệp, thông báo lịch mùa vụ và giới thiệu các chương trình tập huấn;'),
    bullet('Tra cứu tính hợp lệ của Chứng nhận tập huấn điện tử thông qua đường dẫn công khai hoặc quét mã QR in trên chứng chỉ;'),
    bullet('Quét mã QR Hộ chiếu nông sản điện tử để truy xuất nguồn gốc xuất xứ của từng lô hàng rau hoa quả theo chuẩn VietGAP.'),

    p('2. Nhóm tác nhân Nông hộ / Học viên (Student / Farmer):', { indent: 0, bold: true }),
    bullet('Đăng nhập tài khoản nhanh chóng, tiếp cận Bảng điều khiển học tập cá nhân (Dashboard) với giao diện tối giản;'),
    bullet('Theo dõi tiến độ hoàn thành các khóa học đã ghi danh một cách trực quan thông qua thanh tiến độ phần trăm (Progress Bar);'),
    bullet('Tham gia học tập tương tác: đọc tài liệu bài giảng định dạng PDF rõ nét, xem video kỹ thuật trực tuyến độ phân giải cao 16:9, tương tác với các gói bài giảng SCORM 1.2 và H5P;'),
    bullet('Thực hiện nộp bài tập thực hành hiện trường có đính kèm ảnh chụp tại vườn và tọa độ GPS xác thực;'),
    bullet('Làm bài kiểm tra trắc nghiệm đánh giá kiến thức cuối khóa, xem lại phản hồi chi tiết câu đúng/sai;'),
    bullet('Nhận Chứng chỉ số hoàn thành khóa học, tải tệp ảnh chứng chỉ điện tử về điện thoại hoặc chia sẻ mã QR xác thực;'),
    bullet('Ghi chép sổ tay nhật ký canh tác điện tử hàng ngày (AgriOps Farm Diary) và sử dụng công cụ tính toán phối trộn phân bón thông minh.'),

    p('3. Nhóm tác nhân Cán bộ Kỹ thuật / Giảng viên (Teacher / Agricultural Extension Officer):', { indent: 0, bold: true }),
    bullet('Quản lý danh sách học viên trong các lớp tập huấn được phân công phụ trách, theo dõi chuyên cần qua phân hệ Điểm danh;'),
    bullet('Chấm điểm bài tập thực hành hiện trường của nông hộ bằng ma trận tiêu chí Rubric trực quan;'),
    bullet('Xuất Bảng điểm học thuật chính thức và Phiếu phản hồi kết quả đánh giá kỹ năng để bàn giao cho tổ hợp tác;'),
    bullet('Nhân bản nhanh chóng các khóa học mẫu chuẩn hóa về cho từng chi nhánh, tổ hợp tác địa phương thông qua công cụ 1-Click Course Cloner.'),

    p('4. Nhóm tác nhân Quản trị viên (Site Administrator):', { indent: 0, bold: true }),
    bullet('Quản lý danh mục khóa học, phân quyền tài khoản người dùng, thiết lập chính sách phân quyền RBAC chặt chẽ;'),
    bullet('Giám sát trạng thái hoạt động của máy chủ, kiểm toán an ninh và hạ tầng kỹ thuật thời gian thực (Admin Infrastructure Audit);'),
    bullet('Thực hiện sao lưu (Backup) cơ sở dữ liệu định kỳ và bảo trì hệ thống qua bộ công cụ dòng lệnh CLI.'),

    ...createAcademicTable(
      rbacHeaders,
      rbacRows,
      rbacWidths,
      'Bảng 3.1',
      'Bảng ma trận phân quyền RBAC chi tiết giữa 4 nhóm tác nhân hệ thống',
      'tbl_3_1'
    ),

    h3('3.1.2. Yêu cầu phi chức năng về hiệu năng và bảo mật', 'sec_3_1_2'),
    bullet('Thời gian tải trang (Response Time): Thời gian phản hồi cho các yêu cầu xem bài học và bảng điều khiển trung bình dưới 1.5 giây trong điều kiện mạng 4G ngoài đồng ruộng;', { boldPrefix: 'Hiệu năng:' }),
    bullet('Khả năng mở rộng (Scalability): Kiến trúc container hóa bảo đảm máy chủ chịu tải đồng thời tối thiểu 500 phiên học tập trực tuyến mà không bị nghẽn CPU/RAM;', { boldPrefix: 'Mở rộng:' }),
    bullet('Độ khả dụng (Availability): Hệ thống hoạt động liên tục với chỉ số Uptime tối thiểu 99.5%, tự động khởi động lại dịch vụ khi gặp sự cố;', { boldPrefix: 'Khả dụng:' }),
    bullet('Tính toàn vẹn dữ liệu (Integrity): Toàn bộ dữ liệu điểm số và chứng chỉ được ký số bằng mã băm mật mã HMAC-SHA256, tuyệt đối ngăn chặn hành vi sửa đổi gian lận điểm số;', { boldPrefix: 'An toàn:' }),
    bullet('Khả năng tương thích (Compatibility): Giao diện hiển thị hoàn hảo trên các kích thước màn hình phổ biến (từ máy tính bảng, máy tính để bàn 1440px đến điện thoại thông minh 375px - 390px).', { boldPrefix: 'Tương thích:' }),

    h2('3.2. Thiết kế kiến trúc tổng thể 4 tầng của hệ thống Mầm Xanh LMS 2.0', 'sec_3_2'),
    p('Nhằm bảo đảm tính độc lập giữa các thành phần, tăng cường tính bảo mật và tạo điều kiện thuận lợi cho việc bảo trì mở rộng lâu dài, hệ thống Mầm Xanh LMS 2.0 được thiết kế theo mô hình kiến trúc phân tầng 4 lớp (4-Tier Decoupled Architecture):'),
    bullet('Tầng 1 - Presentation Tier (Tầng Trình diễn & Trải nghiệm): Sử dụng theme giao diện độc quyền agritrain_child kế thừa từ Boost Theme chuẩn của Moodle, định kiểu toàn diện bằng SCSS và Mustache template engine. Tầng này chịu trách nhiệm biến đổi dữ liệu thành giao diện công thái học tối giản, tích hợp PWA ready, tương thích hoàn hảo trên cả trình duyệt máy tính và điện thoại di động;'),
    bullet('Tầng 2 - Custom Application Tier (Tầng Ứng dụng Nghiệp vụ Nông nghiệp): Chứa 5 plugin chuyên biệt tự phát triển bao gồm: local_mamxanhapp (bảng điểm học thuật, nhân bản khóa học, công cụ tính phân bón), local_mamxanhcert (quản lý chứng chỉ số, ký số HMAC-SHA256 và tra cứu QR), local_agriops (nhật ký canh tác VietGAP và hộ chiếu nông sản số), local_farmerprofile (hồ sơ nông hộ), và mod_fieldpractice (bài tập thực địa có GPS và chấm Rubric);'),
    bullet('Tầng 3 - Moodle Core Platform Tier (Tầng Nền tảng Đào tạo Moodle): Khai thác các hệ thống con cốt lõi (Core Subsystems) của Moodle 4.5 LTS: Enrollment API (ghi danh học viên), Gradebook API (tính toán sổ điểm đa thành phần), Access API (phân quyền RBAC), SCORM/H5P Player Engine, Navigation API và Task Scheduler (Cron Runner);'),
    bullet('Tầng 4 - Infrastructure & Data Tier (Tầng Hạ tầng & Cơ sở Dữ liệu): Vận hành trên nền tảng ảo hóa Docker Turbo gồm 5 dịch vụ container: máy chủ cổng ngược Caddy 2 Reverse Proxy (HTTPS :8443, nén zstd/gzip), môi trường ứng dụng Moodle App PHP 8.3 + Apache tối ưu với bộ nhớ đệm OPcache + JIT, cơ sở dữ liệu MariaDB 10.11 LTS cấu hình chuẩn utf8mb4_unicode_ci, bộ nhớ đệm trong RAM Redis 7 In-Memory Cache (Session + MUC), và tiến trình chạy nền Moodle Cron Worker định kỳ mỗi 60 giây.'),

    ...imageFigure('scripts/thesis/build/diagrams/D2_layers.png', 'Hình 3.1', 'Kiến trúc phân tầng 4 lớp của hệ thống Mầm Xanh LMS 2.0 (Sơ đồ D2)', 'fig_3_1', 540, 310),

    h2('3.3. Biểu đồ ca sử dụng (Use Case) và mô hình hóa tác nhân', 'sec_3_3'),
    p('Biểu đồ ca sử dụng (Use Case Diagram) tổng thể phân rã các hành vi tương tác của 4 nhóm tác nhân với các khối chức năng nghiệp vụ của hệ thống Mầm Xanh LMS 2.0, bảo đảm tính minh bạch và ranh giới phân quyền rõ ràng.'),

    ...imageFigure('scripts/thesis/build/diagrams/D3_usecase.png', 'Hình 3.2', 'Biểu đồ ca sử dụng (Use Case Diagram) tổng thể phân rã theo 4 nhóm tác nhân (Sơ đồ D3)', 'fig_3_2', 540, 320),

    h2('3.4. Thiết kế quy trình nghiệp vụ tập huấn canh tác thông minh (Swimlane Flow)', 'sec_3_4'),
    p('Khác với quy trình học trực tuyến thông thường chỉ diễn ra giữa học viên và màn hình máy tính, quy trình tập huấn canh tác nông nghiệp công nghệ cao là một quy trình tương tác đa chiều giữa Nông hộ, Cán bộ kỹ thuật hiện trường, Hệ thống Mầm Xanh LMS và Doanh nghiệp thu mua nông sản.'),
    p('Quy trình nghiệp vụ hoàn chỉnh được mô hình hóa bằng biểu đồ Swimlane trải qua 7 bước liên tục:'),
    bullet('Bước 1: Nông hộ tham gia buổi khai mạc tập huấn tại hiện trường hoặc trực tuyến; Cán bộ kỹ thuật thực hiện điểm danh chuyên cần trên phân hệ Điểm danh;'),
    bullet('Bước 2: Nông hộ tự học các bài giảng lý thuyết đa phương tiện (video, SCORM, H5P) và tham gia thảo luận kỹ thuật trên Diễn đàn hỏi đáp;'),
    bullet('Bước 3: Nông hộ làm bài kiểm tra trắc nghiệm lý thuyết cuối chuyên đề để củng cố kiến thức;'),
    bullet('Bước 4: Nông hộ thực hành tại vườn nhà màng, dùng điện thoại thông minh chụp ảnh hiện trường, đính kèm tọa độ vị trí GPS và nộp bài lên hệ thống;'),
    bullet('Bước 5: Cán bộ kỹ thuật kiểm tra bài nộp thực địa, đối chiếu tọa độ vườn và chấm điểm theo ma trận tiêu chí Rubric minh bạch;'),
    bullet('Bước 6: Khi thỏa mãn điều kiện hoàn thành khóa học, Hệ thống Mầm Xanh LMS tự động ký số HMAC-SHA256 và sinh mã QR phát hành Chứng chỉ điện tử cho nông hộ;'),
    bullet('Bước 7: Doanh nghiệp bao tiêu hoặc người tiêu dùng quét mã QR trên nông sản/chứng chỉ để kiểm chứng ngay nguồn gốc và năng lực của người sản xuất.'),

    ...imageFigure('scripts/thesis/build/diagrams/D5_swimlane.png', 'Hình 3.3', 'Quy trình nghiệp vụ tập huấn kỹ thuật và nghiệm thu thực địa liên vai trò (Sơ đồ D5)', 'fig_3_3', 540, 310),

    h2('3.5. Thiết kế cơ sở dữ liệu và cấu trúc các bảng plugin tùy biến', 'sec_3_5'),
    p('Cơ sở dữ liệu của hệ thống Mầm Xanh LMS 2.0 kế thừa trọn vẹn các bảng quan hệ chuẩn mực của Moodle 4.5 Core (mdl_user, mdl_course...), đồng thời bổ sung hệ thống bảng dữ liệu chuyên biệt phục vụ các phân hệ mở rộng nông nghiệp (được chuẩn hóa trên Biểu đồ ERD - Hình 3.4):'),
    bullet('Bảng local_mamxanhcert: Quản lý chứng chỉ số, điểm tổng kết final_score, chữ ký số hmac_signature và mã định danh cert_code;'),
    bullet('Bảng fieldpractice và fieldpractice_sub: Định nghĩa trạm thực hành hiện trường (target_lat, target_lng, qr_secret) và lưu trữ bài nộp điểm danh QR/GPS cùng điểm 4 tiêu chí Rubric (crit_prep, crit_tech, crit_biosafe, crit_quality);'),
    bullet('Bảng local_farmerprofile: Lưu trữ hồ sơ nông trại của học viên (farm_name, area_m2, main_crop, region, gps_lat, gps_lng, cert_standard);'),
    bullet('Bảng local_agriops_batch: Quản lý các lô nông sản sản xuất theo tiêu chuẩn VietGAP (batch_code, product_name, crop_variety, expected_yield_kg, qr_token);'),
    bullet('Bảng local_agriops_log: Lưu trữ nhật ký canh tác điện tử chi tiết hàng ngày của từng nông hộ (activity_type, ph_val, ec_val, phi_days, voice_input, notes).'),

    ...imageFigure('scripts/thesis/build/diagrams/D4_er.png', 'Hình 3.4', 'Biểu đồ quan hệ thực thể (ERD) giữa Moodle Core và các bảng plugin tùy biến (Sơ đồ D4)', 'fig_3_4', 540, 310),

    ...createAcademicTable(
      certTableHeaders,
      certTableRows,
      certWidths,
      'Bảng 3.2',
      'Cấu trúc các trường dữ liệu của bảng chứng chỉ số local_mamxanhcert',
      'tbl_3_2'
    ),

    ...createAcademicTable(
      fpTableHeaders,
      fpTableRows,
      fpWidths,
      'Bảng 3.3',
      'Cấu trúc các trường dữ liệu của bảng bài tập thực địa fieldpractice và fieldpractice_sub',
      'tbl_3_3'
    ),

    ...createAcademicTable(
      logTableHeaders,
      logTableRows,
      logWidths,
      'Bảng 3.4',
      'Cấu trúc các trường dữ liệu của bảng nhật ký canh tác local_agriops_log',
      'tbl_3_4'
    ),

    ...imageFigure('scripts/thesis/build/diagrams/D7_cloneflow.png', 'Hình 3.6', 'Quy trình nhân bản nhanh khóa học cấp địa phương với Course Cloner (Sơ đồ D7)', 'fig_3_6', 540, 310),

    h2('3.6. Thiết kế giao diện người dùng và trải nghiệm học tập', 'sec_3_6'),
    p('Để tạo nên một trải nghiệm học tập nhất quán và thuận tiện nhất cho người học, giao diện Mầm Xanh LMS 2.0 được thiết kế tuân theo 3 nguyên tắc trải nghiệm công nghệ cao:'),
    bullet('Cấu trúc cây khóa học chuẩn mực: Mọi khóa học đều được tổ chức đồng bộ gồm: Phần Thông tin chung & Tài liệu tham khảo (Diễn đàn thông báo, Đề cương học phần, Sổ tay TCVN PDF xem trực tiếp) + 4 Chuyên đề kỹ thuật chuyên sâu (Video & Infographic 16:9, Bài giảng tra cứu, Tương tác H5P, Gói mô phỏng SCORM 1.2, Thẻ lật IPM, Thực hành tại vườn QR + GPS + Rubric) + Phần Kiểm tra & Đánh giá (Điểm danh lý thuyết & thực hành, Thảo luận chuyên đề, Bài thu hoạch cuối khóa chấm Rubric, Trắc nghiệm cuối khóa);'),
    bullet('Đồng bộ hóa thanh điều hướng đơn (Single Navbar): Toàn bộ các đường link trùng lặp hoặc không cần thiết bị loại bỏ triệt để; người học chỉ cần nhìn vào thanh đầu trang là tiếp cận ngay toàn bộ tính năng;'),
    bullet('Thiết kế hộp thông báo Popover lơ lửng tinh tế: Hộp thông báo được căn chỉnh vị trí chuẩn xác dưới biểu tượng chuông (top: calc(100% + 10px)), bo góc 14px mềm mại, bóng đổ êm dịu, không bao giờ bị cắt cụt hay đè lên thanh Navbar.'),

    pageBreak(),
  ];
}
