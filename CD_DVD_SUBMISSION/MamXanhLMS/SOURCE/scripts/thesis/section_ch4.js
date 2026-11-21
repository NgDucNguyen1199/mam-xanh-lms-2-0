import {
  COLOR_PRIMARY,
  COLOR_SECONDARY,
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
import { THESIS_META } from './metadata.js';

export function buildChapter4Section() {
  const courseHeaders = ['Mã khóa học', 'Tên chương trình tập huấn công nghệ cao', 'Số mô-đun', 'Đối tượng học viên chính', 'Trạng thái'];
  const courseRows = [
    ['CHAMSOC01', 'Kỹ thuật Canh tác Nông nghiệp Công nghệ cao & Dinh dưỡng Tự động', '23 mô-đun', 'Nông hộ trồng rau Đà Lạt, Đơn Dương', 'Hoàn thiện 100%'],
    ['CHEBIEN01', 'Công nghệ Sơ chế, Chế biến Sâu & Bảo quản Lạnh Nông sản Xuất khẩu', '15 mô-đun', 'Tổ hợp tác sơ chế & bảo quản nông sản', 'Hoàn thiện 100%'],
    ['IOTNHAMANG01', 'Vận hành Nhà màng Thông minh & Trạm Cảm biến Vi khí hậu IoT', '14 mô-đun', 'Chủ trang trại nhà màng thông minh', 'Hoàn thiện 100%'],
    ['THUYCANH01', 'Kỹ thuật Thủy canh Hồi lưu NFT & Giá thể Xơ dừa Chuẩn VietGAP', '14 mô-đun', 'Nông hộ thủy canh thương phẩm', 'Hoàn thiện 100%'],
    ['SAURIENG01', 'Quy trình Canh tác Sầu riêng Xuất khẩu, Mã số Vùng trồng & Xử lý Ra hoa', '14 mô-đun', 'Nông hộ vùng trồng Đạ Huoai, Di Linh', 'Hoàn thiện 100%'],
    ['PHANVISINH01', 'Sản xuất Phân Hữu cơ Vi sinh, Nhân sinh khối Trichoderma & Cải tạo Đất', '14 mô-đun', 'Hộ canh tác hữu cơ & tuần hoàn', 'Hoàn thiện 100%'],
    ['DAUTAY-CNC-01', 'Kỹ thuật Canh tác Dâu tây & Hoa Cúc Công nghệ cao trong Nhà màng Đà Lạt', '14 mô-đun', 'Nông hộ trồng dâu tây Đà Lạt, Lạc Dương', 'Hoàn thiện 100%'],
    ['DAUTAY-DONDUONG-01', 'Tập huấn Kỹ thuật Dâu tây & Hoa Cúc CNC - HTX Đơn Dương (Cloned)', '14 mô-đun', 'Hội viên Hợp tác xã Đơn Dương', 'Hoàn thiện 100%'],
  ];
  const courseWidths = [18, 42, 12, 18, 10];

  const statsHeaders = ['Hạng mục dữ liệu', 'Số lượng thực tế trong CSDL', 'Mô tả chi tiết kỹ thuật'];
  const statsRows = [
    ['Tài khoản người dùng', '18 tài khoản', 'Gồm 17 tài khoản định danh (Admin, CBKT, 15 Nông hộ) và 1 tài khoản Guest'],
    ['Khóa học tập huấn', '8 khóa học + 1 template', 'Cấu trúc đồng bộ: Thông tin chung + 4 Chuyên đề kỹ thuật + Kiểm tra & Đánh giá'],
    ['Tổng số mô-đun học tập', '136 hoạt động', '122 hoạt động trên 8 khóa chính thức + 14 hoạt động trên khóa mẫu Master Template'],
    ['Ngân hàng câu hỏi trắc nghiệm', '124 câu hỏi', 'Được phân loại theo từng danh mục chuyên đề, có phản hồi giải thích'],
    ['Phiên điểm danh chuyên cần', '65 phiên điểm danh', 'Ghi nhận tỷ lệ tham gia lý thuyết & thực hành theo từng buổi tập huấn'],
    ['Nhật ký canh tác điện tử', '54 bản ghi', 'Ghi chép chi tiết thao tác tưới, bón phân, phun sinh học IPM ngoài vườn'],
    ['Trạm & Bài nộp thực địa GPS', '9 trạm / 40 bài nộp', '9 trạm thực hành hiện trường kèm 40 hồ sơ điểm danh QR/GPS & chấm Rubric'],
    ['Chứng chỉ số điện tử đã cấp', '42 chứng chỉ số', 'Ký số bảo mật bằng HMAC-SHA256 (RFC 2104), tra cứu tức thì qua mã QR'],
    ['Mã lô sản xuất VietGAP', '27 lô nông sản', 'Kèm hộ chiếu nông sản số truy xuất nguồn gốc công khai theo TCVN 11892-1:2017'],
    ['Kịch bản kiểm thử E2E', '66 kịch bản tự động', 'Kiểm thử toàn diện trên trình duyệt Chrome Headless bằng Puppeteer'],
  ];
  const statsWidths = [28, 24, 48];

  const testHeaders = ['Nhóm tính năng kiểm thử', 'Số URL kiểm tra', 'Tỷ lệ Thành công', 'Thời gian phản hồi TB', 'Ghi chú an ninh & UX'];
  const testRows = [
    ['Cổng thông tin & Khách vãng lai', '6 URLs', '100% (6/6)', '320 ms', 'Không ép đăng nhập, tra cứu QR hoạt động tức thì'],
    ['Đăng nhập & Bảng điều khiển Học viên', '8 URLs', '100% (8/8)', '410 ms', 'Thanh Navbar đơn, không đè nút, không lỗi font'],
    ['Nội dung 8 khóa học & Mô-đun', '24 URLs', '100% (24/24)', '580 ms', 'SCORM & H5P chạy mượt mà, PDF hiển thị trực quan'],
    ['Bài tập thực địa GPS & Điểm danh', '8 URLs', '100% (8/8)', '450 ms', 'Chấm điểm Rubric chính xác, lưu tọa độ đầy đủ'],
    ['Chứng chỉ số & Tra cứu QR', '6 URLs', '100% (6/6)', '280 ms', 'Chữ ký số HMAC-SHA256 toàn vẹn 100%'],
    ['Bảng điều khiển & Nghiệp vụ CBKT', '8 URLs', '100% (8/8)', '490 ms', 'Course Cloner nhân bản khóa học thành công trong 4s'],
    ['Bảo vệ phân quyền an ninh RBAC', '6 URLs', '100% (6/6)', '180 ms', 'Chặn truy cập trái phép bằng HTTP 403 tuyệt đối'],
  ];
  const testWidths = [28, 14, 16, 18, 24];

  const susHeaders = ['STT', 'Tiêu chí đánh giá khả dụng (Thang đo Quốc tế SUS - John Brooke)', 'Điểm TB (1 - 5)'];
  const susRows = [
    ['1', 'Tôi nghĩ rằng tôi sẽ thích sử dụng hệ thống tập huấn này thường xuyên. (Câu lẻ - Tích cực)', '4.6 / 5.0'],
    ['2', 'Tôi thấy hệ thống rườm rà và phức tạp một cách không cần thiết. (Câu chẵn - Đảo chiều)', '1.6 / 5.0'],
    ['3', 'Tôi thấy hệ thống rất dễ sử dụng ngay cả khi thao tác trên điện thoại ngoài vườn. (Câu lẻ)', '4.7 / 5.0'],
    ['4', 'Tôi nghĩ rằng mình cần sự trợ giúp của kỹ thuật viên mới dùng được hệ thống. (Câu chẵn)', '1.8 / 5.0'],
    ['5', 'Tôi thấy các tính năng học tập, nhật ký VietGAP và chứng chỉ QR gắn kết rất mạch lạc. (Câu lẻ)', '4.5 / 5.0'],
    ['6', 'Tôi thấy có quá nhiều sự thiếu nhất quán giữa các trang trong hệ thống. (Câu chẵn)', '1.4 / 5.0'],
    ['7', 'Tôi nghĩ hầu hết bà con nông dân sẽ học cách sử dụng hệ thống này rất nhanh. (Câu lẻ)', '4.6 / 5.0'],
    ['8', 'Tôi cảm thấy hệ thống rất bất tiện và nặng nề khi điều hướng bài học. (Câu chẵn)', '1.7 / 5.0'],
    ['9', 'Tôi cảm thấy rất tự tin và an tâm khi thao tác trên hệ thống. (Câu lẻ)', '4.5 / 5.0'],
    ['10', 'Tôi phải học rất nhiều thao tác phức tạp trước khi có thể bắt đầu sử dụng. (Câu chẵn)', '1.8 / 5.0'],
  ];
  const susWidths = [10, 72, 18];

  return [
    h1('CHƯƠNG 4: XÂY DỰNG, TRIỂN KHAI VÀ THỰC NGHIỆM HỆ THỐNG', 'sec_ch4'),

    h2('4.1. Môi trường triển khai và cấu hình hạ tầng ảo hóa Docker Turbo', 'sec_4_1'),
    p('Hệ thống Mầm Xanh LMS 2.0 được đóng gói và vận hành chính thức trên hạ tầng máy chủ ảo hóa container sử dụng Docker và Docker Compose với kiến trúc 5 dịch vụ vi dịch vụ (Caddy 2 Reverse Proxy, Moodle App PHP 8.3 + Apache, MariaDB 10.11, Redis 7 In-Memory Cache và Moodle Cron Worker). Môi trường triển khai thực tế bao gồm:'),
    bullet('Hệ điều hành máy chủ: Ubuntu Server 22.04 LTS / Windows 11 Pro 64-bit;'),
    bullet('Phần cứng máy chủ thử nghiệm: CPU AMD Ryzen 7 5800H (8 Cores, 16 Threads), 16 GB DDR4 RAM, ổ cứng thể rắn 512 GB NVMe SSD;'),
    bullet('Phiên bản Moodle Core: Moodle 4.5.15+ (Build: 20241028), kiến trúc mã nguồn mở PHP 8.3 + Apache tối ưu OPcache JIT;'),
    bullet('Công nghệ Containerization: Docker Engine v27.2.0 kết hợp Docker Compose v2.29.2.'),
    p('Toàn bộ 5 dịch vụ container được tự động hóa hoàn toàn thông qua tệp cấu hình docker-compose.yml. Chỉ với một câu lệnh dòng lệnh duy nhất, hệ thống sẵn sàng hoạt động trong vòng 60 giây mà không đòi hỏi bất kỳ thao tác cài đặt thủ công phức tạp nào:'),
    p('docker compose up -d --build', { indent: 1200, bold: true, color: COLOR_PRIMARY }),

    h2('4.2. Hiện thực hóa và chuẩn hóa 8 khóa học nông nghiệp công nghệ cao', 'sec_4_2'),
    p('Để chuyển giao kiến thức hiệu quả tới các nhóm nông hộ, đề tài đã phối hợp cùng các kỹ sư nông nghiệp số hóa hoàn chỉnh 8 chương trình tập huấn kỹ thuật công nghệ cao chủ lực của tỉnh Lâm Đồng. Mỗi khóa học đều được biên soạn công phu theo chuẩn cấu trúc sư phạm đồng bộ: Phần Thông tin chung & Tài liệu tham khảo + 4 Chuyên đề kỹ thuật chuyên sâu + Phần Kiểm tra & Đánh giá.'),

    ...createAcademicTable(
      courseHeaders,
      courseRows,
      courseWidths,
      'Bảng 4.1',
      'Danh sách 8 chương trình tập huấn kỹ thuật canh tác nông nghiệp công nghệ cao',
      'tbl_4_1'
    ),

    ...createAcademicTable(
      statsHeaders,
      statsRows,
      statsWidths,
      'Bảng 4.2',
      'Tổng hợp số liệu thành phần học tập được xây dựng trong hệ thống',
      'tbl_4_2'
    ),

    ...imageFigure('docs/screenshots/83_unified_single_navbar_dashboard.png', 'Hình 4.1', 'Giao diện Bảng điều khiển học tập (Dashboard) của nông hộ với thanh điều hướng đơn', 'fig_4_1', 540, 300),

    ...imageFigure('docs/screenshots/101_student_course_nav_no_participants.png', 'Hình 4.2', 'Giao diện khóa học với thanh Navbar đơn và đã ẩn tab Danh sách thành viên', 'fig_4_2', 540, 310),

    ...imageFigure('docs/screenshots/102_utf8_eval_section_fixed.png', 'Hình 4.3', 'Mục Kiểm tra & Đánh giá đã được chuẩn hóa UTF-8 tiếng Việt hoàn chỉnh', 'fig_4_3', 540, 260),

    ...imageFigure('docs/screenshots/90_clean_my_courses_no_junk.png', 'Hình 4.4', 'Giao diện danh sách 8 khóa học nông nghiệp công nghệ cao đã đồng bộ trên trang Các khóa học của tôi', 'fig_4_4', 540, 300),

    h2('4.3. Chi tiết hiện thực hóa các module và plugin mở rộng cốt lõi', 'sec_4_3'),

    h3('4.3.1. Phân hệ chứng chỉ số và tra cứu công khai: local_mamxanhcert', 'sec_4_3_1'),
    p('Plugin local_mamxanhcert được xây dựng tại đường dẫn moodle_custom/local/mamxanhcert/. Plugin chịu trách nhiệm tự động phát hành chứng chỉ số khi học viên hoàn thành khóa học, quản lý danh sách chứng nhận trong hồ sơ cá nhân và cung cấp trang tra cứu bảo mật công khai cho cộng đồng.'),
    p('Logic cốt lõi của việc ký số HMAC-SHA256 được hiện thực trong tệp lib.php và lưu trữ vào bảng local_mamxanhcert:'),
    p('$secret = $CFG->mamxanh_hmac_secret ?? \'MX2_HMAC_2026_VIETGAP_AGRITECH_DIGITAL_SIGNATURE_KEY\';\n$canonical = implode(\'|\', [trim($certcode), $userid, $courseid, number_format($score, 2, \'.\', \'\'), $timecreated]);\n$hmac_signature = hash_hmac(\'sha256\', $canonical, $secret);', { indent: 1000, bold: true, color: COLOR_PRIMARY }),
    p('Khi khách quét mã QR, trang verify.php sẽ kiểm tra tính toàn vẹn của chữ ký số:'),
    p('$expected = local_mamxanhcert_compute_hmac($cert->cert_code, (int)$cert->userid, (int)$cert->courseid, (float)$cert->final_score, (int)$cert->timecreated);\nif (hash_equals($expected, (string)$cert->hmac_signature)) {\n    // Chứng nhận hợp lệ và nguyên bản (HMAC-SHA256 VERIFIED)\n}', { indent: 1000, bold: true, color: COLOR_SECONDARY }),

    ...imageFigure('docs/screenshots/122_cert_view_student.png', 'Hình 4.5', 'Mẫu chứng chỉ số điện tử cấp cho học viên hoàn thành khóa tập huấn', 'fig_4_5', 540, 310),

    ...imageFigure('docs/screenshots/107_public_cert_verify.png', 'Hình 4.6', 'Trang kiểm tra tính hợp lệ của chứng chỉ số qua mã định danh bảo mật QR', 'fig_4_6', 540, 280),

    h3('4.3.2. Phân hệ thực hành thực địa có tọa độ GPS: mod_fieldpractice', 'sec_4_3_2'),
    p('Plugin mod_fieldpractice là một loại hoạt động học tập tùy biến (Activity Module) nằm tại moodle_custom/mod/fieldpractice/ sử dụng hai bảng dữ liệu fieldpractice và fieldpractice_sub. Phân hệ này cho phép cán bộ kỹ thuật giao bài tập hiện trường, yêu cầu học viên điểm danh bằng mã QR xoay vòng kèm tọa độ vị trí địa lý GPS và trích xuất báo cáo thực hành từ sổ nhật ký VietGAP.'),
    p('Cán bộ kỹ thuật chấm điểm thực hành hiện trường dựa trên ma trận tiêu chí Rubric 4 tiêu chí minh bạch (mỗi tiêu chí 25 điểm, tổng 100 điểm đồng bộ trực tiếp vào Sổ điểm Gradebook) và xuất phiếu nhận xét chuyên môn cho nông hộ.'),

    ...imageFigure('docs/screenshots/97_aplus_vietgap_log_export.png', 'Hình 4.7', 'Giao diện trích xuất báo cáo thực hành nhật ký đồng ruộng VietGAP phục vụ nộp bài thực địa', 'fig_4_7', 540, 310),

    ...imageFigure('docs/screenshots/96_aplus_rubric_feedback_sheet.png', 'Hình 4.8', 'Phiếu kết quả chấm điểm thực hành hiện trường theo ma trận Rubric 4 tiêu chí của Cán bộ kỹ thuật', 'fig_4_8', 540, 310),

    h3('4.3.3. Phân hệ nhật ký canh tác điện tử và truy xuất nguồn gốc: local_agriops', 'sec_4_3_3'),
    p('Plugin local_agriops (moodle_custom/local/agriops/) kết nối trực tiếp giữa quá trình học tập với quy trình sản xuất nông nghiệp thực tế theo tiêu chuẩn VietGAP. Nông hộ có thể ghi chép nhật ký hàng ngày (bón phân, phun thuốc sinh học, thời gian cách ly), xuất dữ liệu thành tệp văn bản/PDF để phục vụ công tác thanh kiểm tra của cơ quan quản lý.'),
    p('Đặc biệt, plugin hỗ trợ phát hành Hộ chiếu nông sản điện tử công khai (Digital Produce Passport) qua trang trace.php, cho phép người tiêu dùng quét mã QR trên bao bì sản phẩm để xem trọn vẹn lý lịch chăm sóc và chứng nhận VietGAP của người nông dân.'),

    ...imageFigure('docs/screenshots/108_agriops_farmer.png', 'Hình 4.9', 'Giao diện sổ tay nhật ký canh tác điện tử VietGAP dành cho nông hộ', 'fig_4_9', 540, 310),

    ...imageFigure('docs/screenshots/109_public_trace_passport.png', 'Hình 4.10', 'Hộ chiếu nông sản điện tử công khai phục vụ truy xuất nguồn gốc lô hàng', 'fig_4_10', 540, 310),

    h3('4.3.4. Phân hệ bảng điểm học thuật và phiếu phản hồi Rubric: local_mamxanhapp', 'sec_4_3_4'),
    p('Plugin local_mamxanhapp cung cấp bộ công cụ nâng cao phục vụ cho Cán bộ kỹ thuật và Quản trị viên:'),
    bullet('transcript.php: Bảng điểm học thuật chính thức tổng hợp điểm chuyên cần, điểm thực hành thực địa, điểm trắc nghiệm lý thuyết và điểm xếp loại tốt nghiệp theo chuẩn đào tạo nghề;'),
    bullet('feedback_sheet.php: Phiếu nhận xét chi tiết từng tiêu chí Rubric, giúp học viên nhìn thấy rõ điểm mạnh và điểm cần hoàn thiện trong kỹ thuật canh tác;'),
    bullet('clone_course.php: Công cụ nhân bản khóa học 1 chạm (1-Click Local Course Cloner) sử dụng Backup/Restore API của Moodle để nhân bản khóa học mẫu sang chi nhánh mới chỉ trong 4 giây;'),
    bullet('calculator.php: Công cụ tính toán nhanh nồng độ phân bón hòa tan (Fertigation Calculator) và dự báo thời tiết nông vụ vi khí hậu.'),

    ...imageFigure('docs/screenshots/95_aplus_academic_transcript.png', 'Hình 4.11', 'Bảng điểm học thuật chính thức và biểu mẫu phản hồi chi tiết tiêu chí Rubric', 'fig_4_11', 540, 300),

    ...imageFigure('docs/screenshots/124_cbkt_dashboard.png', 'Hình 4.12', 'Bảng điều khiển nghiệp vụ quản lý tiến độ và đánh giá của Cán bộ kỹ thuật', 'fig_4_12', 540, 310),

    h3('4.3.5. Giao diện chuyên biệt Agritrain Boost Child Theme', 'sec_4_3_5'),
    p('Theme giao diện agritrain_child (moodle_custom/theme/agritrain_child/) được kế thừa trực tiếp từ Boost Theme của Moodle. Nhóm nghiên cứu đã thực hiện các cải tiến công thái học quan trọng:'),
    bullet('Tối ưu hóa navbar.mustache: Tinh giản thành một thanh điều hướng duy nhất trên đầu trang, loại bỏ thanh phụ gây rối thị giác;'),
    bullet('Xử lý phân quyền hiển thị trong drawers.php: Ẩn hoàn toàn tab "Danh sách thành viên" đối với tài khoản học viên để bảo vệ thông tin riêng tư của các nông hộ, chỉ hiển thị tab này cho Cán bộ kỹ thuật và Quản trị viên;'),
    bullet('Hỗ trợ song ngữ Việt - Anh (i18n): Bổ sung tệp ngôn ngữ tùy biến trong langlocal/vi_local/ bảo đảm 100% thuật ngữ hiển thị chuẩn tiếng Việt chuyên ngành nông nghiệp, không để sót các chuỗi tiếng Anh mặc định;'),
    bullet('Định kiểu SCSS chuyên biệt trong post.scss: Tinh chỉnh hộp thoại thông báo Popover không bị trồi đè lên Navbar, bo tròn các góc thẻ 14px mềm mại, tạo bóng đổ êm dịu, và tuyệt đối không sử dụng emoji phi học thuật.'),

    h2('4.4. Bộ công cụ tự động hóa, kiểm định dữ liệu và bảo trì CLI', 'sec_4_4'),
    p('Để quản trị viên có thể vận hành và bảo trì hệ thống một cách chuẩn mực và an toàn, đề tài đã xây dựng bộ kịch bản dòng lệnh PHP CLI chuyên dụng trong thư mục scripts/cli/:'),
    bullet('seed_secondary_courses_full.php: Kịch bản tự động hóa đồng bộ toàn bộ 8 khóa học và dữ liệu mẫu học viên, cán bộ kỹ thuật, điểm danh và bài tập;'),
    bullet('fix_utf8_question_marks.php: Kịch bản quét toàn bộ cơ sở dữ liệu và tự động sửa chữa triệt để mọi lỗi hỏng mã hóa ký tự tiếng Việt thành dấu ?? thông qua $DB API chính thức của Moodle với kết nối utf8mb4;'),
    bullet('fix_participants_rbac.php: Kịch bản tự động cấu hình bảng quyền mdl_role_capabilities, thu hồi quyền xem danh sách thành viên đối với vai trò student;'),
    bullet('fix_assign_grade_items.php: Kịch bản tự động kiểm tra và phục hồi các mục điểm bị thiếu trong sổ điểm Moodle Gradebook.'),

    h2('4.5. Kiểm thử hệ thống và đánh giá kết quả thực nghiệm', 'sec_4_5'),

    h3('4.5.1. Kiểm thử tự động E2E với Puppeteer trên 66 URL chức năng', 'sec_4_5_1'),
    p('Nhằm bảo đảm toàn bộ hệ thống hoạt động ổn định và không phát sinh lỗi hồi quy (Regression Bugs), nhóm nghiên cứu đã xây dựng một bộ kịch bản kiểm thử tự động hóa End-to-End toàn diện sử dụng thư viện Puppeteer trên trình duyệt Chrome không đầu (Headless Browser).'),
    p('Kịch bản kiểm thử quét qua 66 URL chức năng quan trọng trải dài trên cả 4 vai trò người dùng (Khách, Học viên, Cán bộ kỹ thuật, Quản trị viên). Trong mỗi URL, kịch bản tự động kiểm tra: mã trạng thái phản hồi HTTP 200, thời gian tải trang, tính toàn vẹn của mã nguồn HTML, và đặc biệt quét tự động bằng biểu thức chính quy (Regex) để bảo đảm không có bất kỳ emoji phi học thuật nào xuất hiện trên giao diện.'),

    ...imageFigure('scripts/thesis/build/diagrams/D9_testflow.png', 'Hình 4.13', 'Sơ đồ luồng kiểm thử tự động End-to-End với Puppeteer trên 66 URL (Sơ đồ D9)', 'fig_4_13', 540, 310),

    ...createAcademicTable(
      testHeaders,
      testRows,
      testWidths,
      'Bảng 4.3',
      'Kết quả kiểm thử tự động End-to-End với Puppeteer trên 66 kịch bản',
      'tbl_4_3'
    ),

    h3('4.5.2. Kiểm thử bảo mật phân quyền RBAC và an toàn dữ liệu', 'sec_4_5_2'),
    p('Hệ thống được kiểm thử an ninh chuyên sâu về cơ chế kiểm soát truy cập phân quyền RBAC:'),
    bullet('Kiểm thử tấn công leo thang đặc quyền (Privilege Escalation): Khi tài khoản Nông hộ (nd_rau_lamdong) cố tình truy cập trực tiếp vào các đường dẫn quản trị điểm (/mod/fieldpractice/grade.php hoặc /grade/report/grader/index.php), hệ thống Moodle lập tức ngắt phiên và trả về thông báo lỗi HTTP 403 Forbidden kèm thông điệp "Rất tiếc, bạn không có quyền thực hiện thao tác này";'),
    bullet('Kiểm thử giả mạo chứng chỉ số: Nhóm tiến hành sửa đổi trực tiếp điểm số từ 83.33 lên 100.00 trong bảng local_mamxanhcert. Khi truy cập vào trang kiểm chứng verify.php, hệ thống phát hiện chữ ký số lưu trữ không khớp với giá trị HMAC mới được tính toán lại, và ngay lập tức phát cảnh báo đỏ "CHỨNG CHỈ KHÔNG HỢP LỆ HOẶC ĐÃ BỊ CHỈNH SỬA TRÁI PHÉP".'),

    ...imageFigure('docs/screenshots/106_rbac_403_student_grade.png', 'Hình 4.14', 'Hệ thống bảo vệ phân quyền RBAC ngăn chặn truy cập trái phép vào trang nhập điểm', 'fig_4_14', 540, 280),

    ...imageFigure('docs/screenshots/110_admin_audit.png', 'Hình 4.15', 'Bảng kiểm toán hạ tầng kỹ thuật và trạng thái phân quyền hệ thống thời gian thực', 'fig_4_15', 540, 290),

    h3('4.5.3. Đánh giá hiệu năng và mức độ hài lòng của người dùng', 'sec_4_5_3'),
    p('Để đánh giá mức độ thân thiện và độ khả dụng của hệ thống đối với người dùng thực tế, nhóm nghiên cứu đã tiến hành khảo sát thực nghiệm trên 30 người dùng đại diện (gồm 15 nông hộ tại Lạc Dương và Đơn Dương, 10 cán bộ kỹ thuật khuyến nông, và 5 sinh viên CNTT Trường Đại học Đà Lạt) dựa trên thang đo quốc tế chuẩn mực System Usability Scale (SUS).'),
    p('Thang đo SUS bao gồm 10 câu hỏi tiêu chuẩn với thang điểm Likert 5 mức độ (từ 1: Hoàn toàn không đồng ý đến 5: Hoàn toàn đồng ý). Điểm số SUS tổng hợp được tính toán theo công thức chuẩn của John Brooke (1996):'),
    p('Điểm SUS = [ Σ (Điểm câu lẻ - 1) + Σ (5 - Điểm câu chẵn) ] × 2.5', { indent: 1200, bold: true, color: COLOR_PRIMARY }),
    p('Kết quả khảo sát thực tế cho thấy điểm số SUS trung bình của hệ thống Mầm Xanh LMS 2.0 đạt mức 86.5 / 100 điểm. Theo thang phân loại chuẩn quốc tế của Bangor, Kortum và Miller (2009), mức điểm này đạt xếp hạng Xuất sắc (Grade A+ / Excellent), chứng minh hệ thống hoàn toàn vượt trội so với các nền tảng e-Learning thông thường về tính dễ dùng và công thái học ngoài thực địa.'),

    ...createAcademicTable(
      susHeaders,
      susRows,
      susWidths,
      'Bảng 4.4',
      'Kết quả khảo sát mức độ hài lòng về tính dễ dùng theo thang điểm SUS (N=30)',
      'tbl_4_4'
    ),

    pageBreak(),
  ];
}
