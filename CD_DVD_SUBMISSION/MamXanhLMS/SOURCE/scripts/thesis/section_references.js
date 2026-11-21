import {
  Paragraph,
  TextRun,
  AlignmentType,
} from 'docx';
import {
  p,
  pRuns,
  h1,
  h2,
  h3,
  bullet,
  pageBreak,
} from './styles.js';

export function buildReferencesSection() {
  const references = [
    {
      num: '[1]',
      text: 'Advanced Distributed Learning (ADL) Initiative, Sharable Content Object Reference Model (SCORM) 2004 4th Edition Overview, Department of Defense, Alexandria, VA, USA, 2009.',
    },
    {
      num: '[2]',
      text: 'Bangor A., Kortum P., Miller J., Determining What Individual SUS Scores Mean: Adding an Adjective Rating Scale, Journal of Usability Studies, Vol. 4, Issue 3, 2009, pp. 114 - 123.',
    },
    {
      num: '[3]',
      text: 'Bộ Khoa học và Công nghệ, Tiêu chuẩn Quốc gia TCVN 11892-1:2017 về Thực hành nông nghiệp tốt (VietGAP) – Phần 1: Trồng trọt, Nhà xuất bản Tiêu chuẩn, Hà Nội, 2017.',
    },
    {
      num: '[4]',
      text: 'Bộ Nông nghiệp và Phát triển Nông thôn, Thông tư số 48/2012/TT-BNNPTNT về Quy định chứng nhận sản phẩm thủy sản, trồng trọt, chăn nuôi được sản xuất, sơ chế phù hợp với Quy trình thực hành sản xuất nông nghiệp tốt (VietGAP), Hà Nội, 2012.',
    },
    {
      num: '[5]',
      text: 'Brooke J., SUS: A \'quick and dirty\' usability scale, Usability Evaluation in Industry, Taylor & Francis, London, 1996, pp. 189 - 194.',
    },
    {
      num: '[6]',
      text: 'Dougiamas M., Taylor P., Moodle: Using Learning Communities to Create an Open Source Course Management System, Proceedings of the ED-MEDIA 2003 Conference, Honolulu, Hawaii, USA, 2003, pp. 171 - 178.',
    },
    {
      num: '[7]',
      text: 'H5P Group, H5P Documentation and Developer Guide: Building Interactive HTML5 Content for the Web, https://h5p.org/documentation, 2024.',
    },
    {
      num: '[8]',
      text: 'Krawczyk H., Bellare M., Canetti R., HMAC: Keyed-Hashing for Message Authentication, Request for Comments (RFC 2104), Internet Engineering Task Force (IETF), Network Working Group, 1997.',
    },
    {
      num: '[9]',
      text: 'Moodle HQ, Moodle 4.5 Developer Documentation: Architecture, Coding Style and Plugin APIs, https://moodledev.io/, Perth, Australia, 2024.',
    },
    {
      num: '[10]',
      text: 'Nielsen J., Usability Engineering, Morgan Kaufmann Publishers Inc., San Francisco, California, USA, 1994.',
    },
    {
      num: '[11]',
      text: 'Sở Nông nghiệp và Phát triển Nông thôn tỉnh Lâm Đồng, Báo cáo Tổng kết Đề án Phát triển Nông nghiệp Toàn diện, Bền vững và Hiện đại tỉnh Lâm Đồng giai đoạn 2021 - 2025, Đà Lạt, 2025.',
    },
    {
      num: '[12]',
      text: 'Sở Thông tin và Truyền thông tỉnh Lâm Đồng, Đề án Chuyển đổi số tỉnh Lâm Đồng đến năm 2025, định hướng đến năm 2030, Quyết định số 1336/QĐ-UBND, Đà Lạt, 2022.',
    },
    {
      num: '[13]',
      text: 'World Wide Web Consortium (W3C), Web Content Accessibility Guidelines (WCAG) 2.2, W3C Recommendation, https://www.w3.org/TR/WCAG22/, 2023.',
    },
  ];

  return [
    h1('DANH MỤC TÀI LIỆU THAM KHẢO', 'sec_references'),
    p('Tất cả các tài liệu tham khảo dưới đây được sắp xếp theo thứ tự bảng chữ cái Latinh của tên tác giả (hoặc tên cơ quan ban hành) theo đúng quy định của Khoa Công nghệ Thông tin – Trường Đại học Đà Lạt:', { indent: 0, italic: true }),
    ...references.map(r => new Paragraph({
      alignment: AlignmentType.JUSTIFIED,
      indent: { left: 567, hanging: 567 },
      spacing: { before: 80, after: 80, line: 360, lineRule: 'auto' },
      children: [
        new TextRun({ text: `${r.num} `, font: 'Times New Roman', size: 26, bold: true, color: '000000' }),
        new TextRun({ text: r.text, font: 'Times New Roman', size: 26, color: '000000' }),
      ],
    })),
    pageBreak(),

    h1('PHỤ LỤC', 'sec_appendix'),

    h2('Phụ lục A: Hướng dẫn cài đặt và vận hành hệ thống bằng Docker Turbo', 'sec_app_a'),
    p('1. Yêu cầu tiên quyết về môi trường máy chủ:', { indent: 0, bold: true }),
    bullet('Máy tính hoặc máy chủ chạy hệ điều hành Windows 10/11 64-bit hoặc Linux Ubuntu Server 20.04/22.04 LTS;'),
    bullet('Đã cài đặt sẵn Docker Desktop (hoặc Docker Engine trên Linux) phiên bản từ 24.0 trở lên;'),
    bullet('Tối thiểu 4 GB RAM trống và 10 GB dung lượng ổ cứng khả dụng;'),
    bullet('Các cổng mạng (Ports) 8090, 8443, 3306, 6379 chưa bị chiếm dụng bởi ứng dụng khác.'),

    p('2. Các bước triển khai và khởi chạy hệ thống:', { indent: 0, bold: true }),
    bullet('Giải nén gói mã nguồn từ đĩa CD/DVD vào một thư mục trên máy (ví dụ: C:\\MamXanhLMS hoặc /var/www/mamxanh);', { boldPrefix: 'Bước 1:' }),
    bullet('Mở cửa sổ dòng lệnh PowerShell (trên Windows) hoặc Terminal (trên Linux) và di chuyển vào thư mục dự án;', { boldPrefix: 'Bước 2:' }),
    bullet('Khởi chạy toàn bộ hệ thống bằng câu lệnh duy nhất:\n    docker compose up -d --build\nQuá trình xây dựng và khởi chạy 5 container dịch vụ (Caddy 2 Reverse Proxy, Moodle App PHP 8.3 + Apache, MariaDB 10.11, Redis 7 In-Memory Cache, Moodle Cron Worker) sẽ hoàn tất trong khoảng 45 - 60 giây;', { boldPrefix: 'Bước 3:' }),
    bullet('Nạp dữ liệu cơ sở dữ liệu mẫu từ bản sao lưu sạch:\n    docker compose exec -T db mysql -u moodleuser -pMX!Pass2026 moodledb < CD_DVD_SUBMISSION/MamXanhLMS/DATABASE/mamxanh_lms_db_backup.sql', { boldPrefix: 'Bước 4:' }),
    bullet('Mở trình duyệt web bất kỳ (Chrome, Edge, Firefox, Safari) và truy cập vào địa chỉ:\n    http://localhost:8090\nHệ thống sẽ hiển thị ngay Cổng thông tin & Tin tức Nông nghiệp Công nghệ cao Mầm Xanh LMS 2.0.', { boldPrefix: 'Bước 5:' }),

    p('3. Danh sách tài khoản thử nghiệm các vai trò hệ thống:', { indent: 0, bold: true }),
    bullet('Quản trị viên (Admin): Tên đăng nhập: admin | Mật khẩu: Admin!2026MX (Toàn quyền quản trị);'),
    bullet('Cán bộ Kỹ thuật (Teacher): Tên đăng nhập: cbkt_pilot | Mật khẩu: MX!Canbo2026 (Quản lý điểm danh, chấm Rubric, Course Cloner);'),
    bullet('Học viên Nông hộ Rau Đà Lạt: Tên đăng nhập: nd_rau_lamdong | Mật khẩu: MX!Rau2026 (Học tập, nộp thực địa GPS, sổ nhật ký VietGAP);'),
    bullet('Học viên Nông hộ Dâu tây Lạc Dương: Tên đăng nhập: nd_dautay_lacduong | Mật khẩu: MX!Dautay2026 (Học dâu tây công nghệ cao, nhận chứng chỉ số).'),

    h2('Phụ lục B: Quy trình 10 bước chuẩn hóa khóa học canh tác công nghệ cao', 'sec_app_b'),
    p('Nhằm bảo đảm tính nhất quán sư phạm và chất lượng kỹ thuật của mọi khóa học trên hệ thống, nhóm nghiên cứu đã xây dựng quy trình chuẩn hóa gồm 10 bước nghiêm ngặt:'),
    bullet('Tiếp nhận đề cương kỹ thuật canh tác chính thức từ Chi cục Trồng trọt & BVTV hoặc Viện Khoa học Kỹ thuật Nông Lâm nghiệp Tây Nguyên (WASI);', { boldPrefix: 'Bước 1:' }),
    bullet('Phân rã nội dung thành cấu trúc sư phạm chuẩn hóa gồm: Phần Thông tin chung & Tài liệu tham khảo + 4 Chuyên đề kỹ thuật chuyên sâu + Phần Kiểm tra & Đánh giá;', { boldPrefix: 'Bước 2:' }),
    bullet('Biên tập và quay phim video thị phạm động tác chuẩn tại vườn thực tế, biên tập định dạng 16:9 chuẩn Full HD, không có tạp âm;', { boldPrefix: 'Bước 3:' }),
    bullet('Đóng gói bài học tương tác theo chuẩn SCORM 1.2 mô phỏng các thao tác nguy cơ cao (pha chế thuốc, châm phân);', { boldPrefix: 'Bước 4:' }),
    bullet('Xây dựng các hoạt động tương tác H5P (Flashcards, câu hỏi dừng giữa video, kéo thả phân loại bệnh);', { boldPrefix: 'Bước 5:' }),
    bullet('Thiết lập ma trận tiêu chí đánh giá thực hành Rubric với 4 tiêu chí định lượng rõ ràng;', { boldPrefix: 'Bước 6:' }),
    bullet('Xây dựng ngân hàng câu hỏi trắc nghiệm tối thiểu 15 câu cho mỗi khóa học, có phản hồi giải thích khoa học;', { boldPrefix: 'Bước 7:' }),
    bullet('Cấu hình điều kiện hoàn thành khóa học (Course Completion Tracking): Điểm danh tối thiểu 80%, hoàn thành bài tập thực địa, điểm Quiz từ 80% trở lên;', { boldPrefix: 'Bước 8:' }),
    bullet('Kích hoạt liên kết tự động cấp Chứng chỉ số HMAC-SHA256 và gắn mã QR xác thực công khai;', { boldPrefix: 'Bước 9:' }),
    bullet('Chạy kịch bản kiểm thử tự động hóa Puppeteer để kiểm tra đường truyền, kiểm tra định dạng hiển thị tiếng Việt utf8mb4 và xác nhận không có bất kỳ lỗi hiển thị hay emoji phi chuẩn mực.', { boldPrefix: 'Bước 10:' }),

    pageBreak(),
  ];
}
