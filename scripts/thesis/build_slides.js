import pptxgen from 'pptxgenjs';
import path from 'path';
import fs from 'fs';
import { THESIS_META } from './metadata.js';

async function generateSlideDeck() {
  console.log('[SLIDES] Generating Academic Defense Slide Deck...');
  const pres = new pptxgen();
  pres.layout = 'LAYOUT_16x9';

  // Theme constants
  const C_BG = 'F9FAF9';
  const C_WHITE = 'FFFFFF';
  const C_PRIMARY = '1B4332';
  const C_SECONDARY = '234E3E';
  const C_ACCENT = '3B7A57';
  const C_TEXT = '1F2937';
  const C_MUTED = '4B5563';
  const C_GOLD = 'D97706';
  const FONT_TITLE = 'Times New Roman';
  const FONT_BODY = 'Times New Roman';

  // Helper: Standard Slide Base with Header & Footer
  function createBaseSlide(titleText, categoryText = 'BÁO CÁO ĐỒ ÁN TỐT NGHIỆP') {
    const slide = pres.addSlide();
    slide.background = { color: C_BG };

    // Top Header Banner
    slide.addShape(pres.ShapeType.rect, {
      x: 0, y: 0, w: '100%', h: 0.9,
      fill: { color: C_PRIMARY }
    });

    slide.addText(categoryText.toUpperCase(), {
      x: 0.8, y: 0.12, w: 10, h: 0.25,
      fontSize: 10, fontFace: FONT_BODY, color: 'A7F3D0', bold: true
    });

    slide.addText(titleText, {
      x: 0.8, y: 0.35, w: 11, h: 0.45,
      fontSize: 20, fontFace: FONT_TITLE, color: C_WHITE, bold: true
    });

    // Bottom Footer
    slide.addShape(pres.ShapeType.line, {
      x: 0.8, y: 7.0, w: 11.7, h: 0,
      line: { color: 'CBD5E1', width: 1 }
    });

    slide.addText(`${THESIS_META.university} - ${THESIS_META.faculty} | GVHD: ${THESIS_META.supervisor.title} ${THESIS_META.supervisor.name}`, {
      x: 0.8, y: 7.05, w: 9, h: 0.3,
      fontSize: 10, fontFace: FONT_BODY, color: C_MUTED
    });

    slide.addText('MẦM XANH LMS 2.0', {
      x: 10.0, y: 7.05, w: 2.5, h: 0.3,
      fontSize: 10, fontFace: FONT_BODY, color: C_PRIMARY, bold: true, align: 'right'
    });

    return slide;
  }

  // Helper: Card Container
  function addCard(slide, x, y, w, h, title = '', bgColor = C_WHITE, borderColor = 'E2E8F0') {
    slide.addShape(pres.ShapeType.roundRect, {
      x, y, w, h, rectRadius: 0.1,
      fill: { color: bgColor },
      line: { color: borderColor, width: 1 }
    });
    if (title) {
      slide.addText(title, {
        x: x + 0.2, y: y + 0.15, w: w - 0.4, h: 0.35,
        fontSize: 14, fontFace: FONT_TITLE, color: C_PRIMARY, bold: true
      });
    }
  }

  // =========================================================================
  // SLIDE 1: Title Slide (Cover)
  // =========================================================================
  const s1 = pres.addSlide();
  s1.background = { color: C_PRIMARY };

  s1.addText(THESIS_META.university.toUpperCase(), {
    x: 0.8, y: 0.6, w: 11.7, h: 0.4,
    fontSize: 16, fontFace: FONT_TITLE, color: 'D1FAE5', bold: true, align: 'center'
  });
  s1.addText(`${THESIS_META.faculty.toUpperCase()} - ${THESIS_META.department.toUpperCase()}`, {
    x: 0.8, y: 1.0, w: 11.7, h: 0.35,
    fontSize: 13, fontFace: FONT_BODY, color: 'A7F3D0', align: 'center'
  });

  s1.addShape(pres.ShapeType.line, {
    x: 4.5, y: 1.5, w: 4.3, h: 0,
    line: { color: 'FCD34D', width: 2 }
  });

  s1.addText(THESIS_META.reportType.toUpperCase(), {
    x: 0.8, y: 1.8, w: 11.7, h: 0.5,
    fontSize: 22, fontFace: FONT_TITLE, color: 'FCD34D', bold: true, align: 'center'
  });

  s1.addText(THESIS_META.title, {
    x: 1.2, y: 2.4, w: 10.9, h: 1.4,
    fontSize: 26, fontFace: FONT_TITLE, color: C_WHITE, bold: true, align: 'center', lineSpacing: 34
  });

  s1.addText('HỆ THỐNG MẦM XANH LMS 2.0', {
    x: 0.8, y: 3.9, w: 11.7, h: 0.4,
    fontSize: 15, fontFace: FONT_BODY, color: 'A7F3D0', italic: true, align: 'center'
  });

  // Author & Supervisor Box
  s1.addShape(pres.ShapeType.roundRect, {
    x: 2.2, y: 4.5, w: 8.9, h: 1.8, rectRadius: 0.15,
    fill: { color: '234E3E' },
    line: { color: '3B7A57', width: 1.5 }
  });

  s1.addText(`Cán bộ hướng dẫn: ${THESIS_META.supervisor.title} ${THESIS_META.supervisor.name}`, {
    x: 2.5, y: 4.7, w: 8.3, h: 0.35,
    fontSize: 14, fontFace: FONT_BODY, color: 'FDE68A', bold: true
  });

  s1.addText('Sinh viên thực hiện:', {
    x: 2.5, y: 5.1, w: 8.3, h: 0.3,
    fontSize: 13, fontFace: FONT_BODY, color: C_WHITE, bold: true
  });

  const svList = THESIS_META.students.map(s => `• ${s.id} – ${s.name}`).join('    |    ');
  s1.addText(svList, {
    x: 2.5, y: 5.45, w: 8.3, h: 0.4,
    fontSize: 13, fontFace: FONT_BODY, color: 'D1FAE5'
  });

  s1.addText(`${THESIS_META.location}, ${THESIS_META.monthYear}`, {
    x: 0.8, y: 6.7, w: 11.7, h: 0.4,
    fontSize: 12, fontFace: FONT_BODY, color: '9CA3AF', align: 'center'
  });

  // =========================================================================
  // SLIDE 2: Agenda
  // =========================================================================
  const s2 = createBaseSlide('NỘI DUNG BÁO CÁO BẢO VỆ');
  const agendaItems = [
    { num: '01', title: 'LÝ DO CHỌN ĐỀ TÀI & MỤC TIÊU', desc: 'Bối cảnh nông nghiệp công nghệ cao Lâm Đồng và bài toán đào tạo thực tế' },
    { num: '02', title: 'CƠ SỞ LÝ THUYẾT & CÔNG NGHỆ', desc: 'Moodle 4.5, SCORM/H5P, Rubric, HMAC-SHA256, Minimalist UX & Docker Turbo' },
    { num: '03', title: 'PHÂN TÍCH & THIẾT KẾ HỆ THỐNG', desc: 'Kiến trúc phân tầng 4 lớp, Use Case, quy trình Swimlane và Database Schema' },
    { num: '04', title: 'HIỆN THỰC HÓA & KẾT QUẢ THỰC NGHIỆM', desc: '8 khóa học chuẩn hóa, 5 plugin chuyên biệt, kiểm thử Puppeteer 66 URLs' },
    { num: '05', title: 'KẾT LUẬN & HƯỚNG PHÁT TRIỂN', desc: 'Tổng kết đóng góp của đề tài và định hướng ứng dụng AI, Mobile PWA ngoại tuyến' },
  ];

  agendaItems.forEach((item, idx) => {
    const y = 1.3 + idx * 1.05;
    addCard(s2, 1.2, y, 10.9, 0.9, '', C_WHITE, 'E2E8F0');
    s2.addShape(pres.ShapeType.roundRect, {
      x: 1.4, y: y + 0.15, w: 0.8, h: 0.6, rectRadius: 0.1,
      fill: { color: C_PRIMARY }
    });
    s2.addText(item.num, {
      x: 1.4, y: y + 0.22, w: 0.8, h: 0.45,
      fontSize: 16, fontFace: FONT_TITLE, color: C_WHITE, bold: true, align: 'center'
    });
    s2.addText(item.title, {
      x: 2.4, y: y + 0.15, w: 9.3, h: 0.35,
      fontSize: 14, fontFace: FONT_TITLE, color: C_PRIMARY, bold: true
    });
    s2.addText(item.desc, {
      x: 2.4, y: y + 0.48, w: 9.3, h: 0.3,
      fontSize: 12, fontFace: FONT_BODY, color: C_MUTED
    });
  });

  // =========================================================================
  // SLIDE 3: Bối cảnh & Vấn đề tồn tại
  // =========================================================================
  const s3 = createBaseSlide('BỐI CẢNH NÔNG NGHIỆP CÔNG NGHỆ CAO & VẤN ĐỀ TỒN TẠI', 'PHẦN 1: MỞ ĐẦU');
  addCard(s3, 0.8, 1.3, 5.6, 5.3, 'BỐI CẢNH THỰC TIỄN TẠI LÂM ĐỒNG');
  s3.addText([
    { text: '• Thủ phủ nông nghiệp công nghệ cao:', options: { bold: true, color: C_PRIMARY } },
    { text: ' Hơn 66.000 ha canh tác ứng dụng công nghệ cao (chiếm >21% diện tích toàn tỉnh, >45% giá trị toàn ngành).\n\n' },
    { text: '• Công nghệ hiện đại phát triển mạnh:', options: { bold: true, color: C_PRIMARY } },
    { text: ' Nhà màng, tưới châm phân tự động Fertigation, cảm biến IoT, tiêu chuẩn VietGAP/GlobalGAP.\n\n' },
    { text: '• Đòi hỏi chuẩn hóa tay nghề nông hộ:', options: { bold: true, color: C_PRIMARY } },
    { text: ' Nông dân trực tiếp vận hành hệ thống cần liên tục cập nhật kỹ thuật chính xác, sai sót nhỏ có thể hỏng cả lứa sản phẩm.' }
  ], { x: 1.0, y: 1.8, w: 5.2, h: 4.5, fontSize: 13, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 22 });

  addCard(s3, 6.9, 1.3, 5.6, 5.3, 'BẤT CẬP CỦA PHƯƠNG THỨC TẬP HUẤN CŨ');
  s3.addText([
    { text: '• Rào cản không gian & thời gian:', options: { bold: true, color: 'DC2626' } },
    { text: ' Lớp học tập trung tốn kém; nông dân bận mùa vụ thu hoạch, tỷ lệ tham gia không đều đặn.\n\n' },
    { text: '• Tài liệu giấy dày đặc, mau quên:', options: { bold: true, color: 'DC2626' } },
    { text: ' Nông dân độ tuổi 45-60 ngại đọc sách chữ nhỏ; tỷ lệ ghi nhớ kỹ thuật sau 1 tháng <20%.\n\n' },
    { text: '• Thiếu công cụ kiểm chứng thực địa:', options: { bold: true, color: 'DC2626' } },
    { text: ' Không kiểm soát được học viên thao tác đúng tại vườn hay không; giấy chứng nhận truyền thống dễ làm giả.' }
  ], { x: 7.1, y: 1.8, w: 5.2, h: 4.5, fontSize: 13, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 22 });

  // =========================================================================
  // SLIDE 4: Mục tiêu đề tài
  // =========================================================================
  const s4 = createBaseSlide('MỤC TIÊU & ĐỊNH HƯỚNG GIẢI PHÁP MẦM XANH LMS 2.0', 'PHẦN 1: MỞ ĐẦU');
  const goals = [
    { title: 'CÔNG THÁI HỌC UI/UX TỐI GIẢN', text: 'Thiết kế giao diện Minimalist Off-White Botanical (#F9FAF9/#234E3E), loại bỏ menu rườm rà, nút bấm lớn 1 chạm, chống chói ngoài trời.' },
    { title: 'CHUẨN HÓA SƯ PHẠM ĐA PHƯƠNG TIỆN', text: 'Số hóa 8 chương trình tập huấn công nghệ cao theo chuẩn SCORM 1.2 & H5P, cấu trúc 6 chuyên đề bài bản.' },
    { title: 'LIÊN THÔNG HỌC & THỰC HÀNH HIỆN TRƯỜNG', text: 'Nộp bài thực địa có định vị GPS (mod_fieldpractice), cán bộ kỹ thuật chấm điểm bằng ma trận Rubric minh bạch.' },
    { title: 'BẢO MẬT CHỨNG CHỈ SỐ BẰNG HMAC-SHA256', text: 'Chữ ký số mật mã chống sửa điểm, tra cứu tức thì qua mã QR công khai (local_mamxanhcert).' },
    { title: 'KẾT NỐI NHẬT KÝ ĐIỆN TỬ VIETGAP', text: 'Ghi chép sổ tay canh tác điện tử, phát hành Hộ chiếu nông sản số (local_agriops) phục vụ truy xuất nguồn gốc.' },
    { title: 'TRIỂN KHAI NHANH TRÊN DOCKER TURBO', text: 'Đóng gói 4 container vi dịch vụ, khởi chạy trong 60 giây, kiểm thử tự động 100% Pass với Puppeteer.' },
  ];

  goals.forEach((g, idx) => {
    const col = idx % 3;
    const row = Math.floor(idx / 3);
    const x = 0.8 + col * 3.95;
    const y = 1.3 + row * 2.75;
    addCard(s4, x, y, 3.75, 2.5, g.title);
    s4.addText(g.text, {
      x: x + 0.2, y: y + 0.65, w: 3.35, h: 1.7,
      fontSize: 12, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 18
    });
  });

  // =========================================================================
  // SLIDE 5: Nền tảng Moodle 4.5 & Docker Turbo
  // =========================================================================
  const s5 = createBaseSlide('NỀN TẢNG MOODLE 4.5 LTS & KIẾN TRÚC DOCKER TURBO', 'PHẦN 2: CƠ SỞ LÝ THUYẾT');
  addCard(s5, 0.8, 1.3, 5.6, 5.3, 'NỀN TẢNG MOODLE 4.5 LTS MÃ NGUỒN MỞ');
  s5.addText([
    { text: '• Phiên bản LTS hỗ trợ dài hạn đến 2027:', options: { bold: true } },
    { text: ' Tối ưu PHP 8.3 FPM kết hợp bộ nhớ đệm OPcache JIT, tăng 2.5x tốc độ phản hồi.\n\n' },
    { text: '• Kiến trúc module hóa (Modular Architecture):', options: { bold: true } },
    { text: ' Phát triển plugin độc lập (mod/, local/, theme/) không can thiệp lõi Moodle, an toàn khi cập nhật.\n\n' },
    { text: '• Hệ thống phân quyền RBAC đa cấp:', options: { bold: true } },
    { text: ' Hơn 600 năng lực quyền hạn kiểm soát chặt chẽ vai trò Khách, Nông hộ, Cán bộ kỹ thuật và Admin.' }
  ], { x: 1.0, y: 1.8, w: 5.2, h: 4.5, fontSize: 13, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 22 });

  addCard(s5, 6.9, 1.3, 5.6, 5.3, 'KIẾN TRÚC VI DỊCH VỤ DOCKER TURBO');
  s5.addText([
    { text: '• Nginx Reverse Proxy (Port 8090):', options: { bold: true, color: C_PRIMARY } },
    { text: ' Định tuyến truy cập, nén gzip, cache tài nguyên tĩnh, giảm 70% tải máy chủ.\n\n' },
    { text: '• PHP 8.3 FPM App Container:', options: { bold: true, color: C_PRIMARY } },
    { text: ' Thực thi logic nghiệp vụ Moodle và 5 plugin tùy biến.\n\n' },
    { text: '• MariaDB 10.11 LTS Container:', options: { bold: true, color: C_PRIMARY } },
    { text: ' Tối ưu InnoDB Buffer Pool 512MB, bắt buộc bộ ký tự utf8mb4_unicode_ci.\n\n' },
    { text: '• Background Worker Cron Container:', options: { bold: true, color: C_PRIMARY } },
    { text: ' Tác vụ nền chạy mỗi 1 phút: tính điều kiện hoàn thành khóa, phát hành chứng chỉ.' }
  ], { x: 7.1, y: 1.8, w: 5.2, h: 4.5, fontSize: 12.5, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 20 });

  // =========================================================================
  // SLIDE 6: Chuẩn e-Learning & Rubric
  // =========================================================================
  const s6 = createBaseSlide('CHUẨN DỮ LIỆU E-LEARNING (SCORM/H5P) & MA TRẬN RUBRIC', 'PHẦN 2: CƠ SỞ LÝ THUYẾT');
  addCard(s6, 0.8, 1.3, 5.6, 5.3, 'CHUẨN BÀI GIẢNG SCORM 1.2 & H5P');
  s6.addText([
    { text: '• SCORM 1.2 Runtime API:', options: { bold: true, color: C_PRIMARY } },
    { text: ' Đóng gói bài học mô phỏng các bước cân đo dinh dưỡng Fertigation; giao tiếp LMSInitialize(), LMSSetValue("cmi.core.score.raw"), LMSCommit().\n\n' },
    { text: '• Hoạt động tương tác H5P HTML5:', options: { bold: true, color: C_PRIMARY } },
    { text: ' Video dừng hỏi đáp kiến thức cắt tỉa cành, Flashcards nhận diện triệu chứng sâu bệnh sinh học, kéo thả phân loại thuốc BVTV.' }
  ], { x: 1.0, y: 1.8, w: 5.2, h: 4.5, fontSize: 13, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 22 });

  addCard(s6, 6.9, 1.3, 5.6, 5.3, 'MA TRẬN ĐÁNH GIÁ THỰC ĐỊA RUBRIC');
  s6.addText([
    { text: '• 4 Tiêu chí đánh giá định lượng minh bạch:\n', options: { bold: true } },
    { text: '  1. Bảo hộ lao động & An toàn VietGAP (Trọng số 20%)\n' },
    { text: '  2. Thao tác kỹ thuật hiện trường (Trọng số 30%)\n' },
    { text: '  3. Đo đạc & Cân đối nồng độ EC/pH (Trọng số 30%)\n' },
    { text: '  4. Thu dọn & Vệ sinh đồng ruộng (Trọng số 20%)\n\n' },
    { text: '• Đánh giá 3 mức độ thành thạo:', options: { bold: true, color: C_PRIMARY } },
    { text: ' Cần cải thiện (0-49%) | Đạt chuẩn (50-79%) | Xuất sắc (80-100%). Xuất Phiếu phản hồi chi tiết cho học viên.' }
  ], { x: 7.1, y: 1.8, w: 5.2, h: 4.5, fontSize: 12.5, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 20 });

  // =========================================================================
  // SLIDE 7: Bảo mật HMAC-SHA256 & QR
  // =========================================================================
  const s7 = createBaseSlide('BẢO MẬT CHỨNG CHỈ SỐ BẰNG MÃ HÓA HMAC-SHA256 & QR', 'PHẦN 2: CƠ SỞ LÝ THUYẾT');
  addCard(s7, 0.8, 1.3, 11.7, 5.3, 'GIẢI THUẬT KÝ SỐ VÀ CƠ CHẾ XÁC THỰC THỜI GIAN THỰC');

  s7.addText('Công thức toán học của giải thuật HMAC-SHA256:', {
    x: 1.2, y: 1.8, w: 10.9, h: 0.35, fontSize: 13, fontFace: FONT_BODY, bold: true, color: C_MUTED
  });
  s7.addText('HMAC(K, m) = SHA256((K\' ⊕ opad) || SHA256((K\' ⊕ ipad) || m))', {
    x: 1.2, y: 2.15, w: 10.9, h: 0.5, fontSize: 16, fontFace: FONT_TITLE, bold: true, color: C_PRIMARY
  });

  const hmacSteps = [
    { step: '1. TỔNG HỢP PAYLOAD', desc: 'Mã chứng chỉ + User ID + Course ID + Điểm số + Thời gian cấp' },
    { step: '2. KÝ SỐ MẬT MÃ', desc: 'Máy chủ tính toán HMAC-SHA256 với Khóa bí mật chỉ máy chủ nắm giữ' },
    { step: '3. PHÁT HÀNH MÃ QR', desc: 'In mã QR chứa liên kết tra cứu và token chữ ký lên phôi chứng chỉ' },
    { step: '4. KIỂM THỬ XÁC MINH', desc: 'Quét mã QR -> Máy chủ băm lại và so khớp; cảnh báo đỏ nếu phát hiện sửa điểm' },
  ];

  hmacSteps.forEach((st, idx) => {
    const x = 1.1 + idx * 2.75;
    addCard(s7, x, 3.0, 2.55, 3.2, st.step, 'F9FAFB', 'CBD5E1');
    s7.addText(st.desc, {
      x: x + 0.15, y: 3.6, w: 2.25, h: 2.3,
      fontSize: 12, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 18
    });
  });

  // =========================================================================
  // SLIDE 8: Kiến trúc 4 tầng
  // =========================================================================
  const s8 = createBaseSlide('THIẾT KẾ KIẾN TRÚC PHÂN TẦNG 4 LỚP (4-TIER ARCHITECTURE)', 'PHẦN 3: PHÂN TÍCH & THIẾT KẾ');
  const tiers = [
    { name: 'TẦNG 1: TRÌNH DIỄN (PRESENTATION TIER)', desc: 'Theme agritrain_child kế thừa Boost Theme, định kiểu SCSS Minimalist, Mustache Templates, Responsive Web cho Mobile và Desktop.', color: '1B4332' },
    { name: 'TẦNG 2: ỨNG DỤNG NGHIỆP VỤ (CUSTOM APP TIER)', desc: '5 Plugin chuyên biệt: local_mamxanhapp, local_mamxanhcert, local_agriops, local_farmerprofile, mod_fieldpractice.', color: '234E3E' },
    { name: 'TẦNG 3: NỀN TẢNG MOODLE CORE (MOODLE CORE TIER)', desc: 'Khai thác các hệ thống con: Enrollment API, Gradebook API, RBAC Access Control, SCORM/H5P Player, Task Scheduler Cron.', color: '3B7A57' },
    { name: 'TẦNG 4: HẠ TẦNG & DỮ LIỆU (INFRASTRUCTURE & DATA TIER)', desc: 'Docker Turbo Containerization: Nginx Proxy, PHP 8.3 FPM, MariaDB 10.11 utf8mb4 (508 core + 5 custom tables), MoodleData Volume.', color: '4B5563' },
  ];

  tiers.forEach((t, idx) => {
    const y = 1.4 + idx * 1.35;
    s8.addShape(pres.ShapeType.roundRect, {
      x: 1.0, y, w: 11.3, h: 1.15, rectRadius: 0.1,
      fill: { color: C_WHITE },
      line: { color: t.color, width: 2 }
    });
    s8.addShape(pres.ShapeType.rect, {
      x: 1.0, y, w: 0.25, h: 1.15,
      fill: { color: t.color }
    });
    s8.addText(t.name, {
      x: 1.5, y: y + 0.12, w: 10.5, h: 0.35,
      fontSize: 14, fontFace: FONT_TITLE, color: t.color, bold: true
    });
    s8.addText(t.desc, {
      x: 1.5, y: y + 0.48, w: 10.5, h: 0.55,
      fontSize: 12, fontFace: FONT_BODY, color: C_TEXT
    });
  });

  // =========================================================================
  // SLIDE 9: Biểu đồ Use Case & Phân quyền RBAC
  // =========================================================================
  const s9 = createBaseSlide('BIỂU ĐỒ CA SỬ DỤNG (USE CASE) & MA TRẬN PHÂN QUYỀN RBAC', 'PHẦN 3: PHÂN TÍCH & THIẾT KẾ');
  const roles = [
    { title: 'KHÁCH VÃNG LAI', items: '• Xem tin tức nông nghiệp\n• Tra cứu chứng nhận QR\n• Quét mã truy xuất VietGAP\n• Không bị ép đăng nhập' },
    { title: 'HỌC VIÊN (NÔNG HỘ)', items: '• Dashboard 1 chạm trực quan\n• Học SCORM, H5P, PDF\n• Nộp bài thực địa GPS\n• Nhận chứng chỉ số QR\n• Ghi nhật ký AgriOps' },
    { title: 'CÁN BỘ KỸ THUẬT', items: '• Quản lý điểm danh phiên học\n• Chấm điểm Rubric thực địa\n• Xuất Bảng điểm & Phiếu phản hồi\n• Nhân bản khóa học (Cloner)' },
    { title: 'QUẢN TRỊ VIÊN (ADMIN)', items: '• Quản trị phân quyền RBAC\n• Kiểm toán hệ thống (Audit)\n• Giám sát hạ tầng kỹ thuật\n• Sao lưu & Khôi phục CSDL' },
  ];

  roles.forEach((r, idx) => {
    const x = 0.8 + idx * 2.95;
    addCard(s9, x, 1.3, 2.8, 5.3, r.title);
    s9.addText(r.items, {
      x: x + 0.2, y: 1.8, w: 2.4, h: 4.5,
      fontSize: 12.5, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 22
    });
  });

  // =========================================================================
  // SLIDE 10: Quy trình Swimlane
  // =========================================================================
  const s10 = createBaseSlide('QUY TRÌNH NGHIỆP VỤ TẬP HUẤN THỰC ĐỊA (SWIMLANE FLOW)', 'PHẦN 3: PHÂN TÍCH & THIẾT KẾ');
  const swimSteps = [
    { role: 'NÔNG HỘ', step: 'Điểm danh phiên học', action: 'Tham gia lớp khai mạc, CBKT điểm danh chuyên cần' },
    { role: 'NÔNG HỘ', step: 'Học lý thuyết tương tác', action: 'Học SCORM, H5P, xem video kỹ thuật, thảo luận diễn đàn' },
    { role: 'NÔNG HỘ', step: 'Kiểm tra Quiz & Nộp thực địa', action: 'Làm trắc nghiệm cuối khóa, chụp ảnh vườn đính kèm GPS' },
    { role: 'CÁN BỘ KỸ THUẬT', step: 'Chấm điểm Rubric thực tế', action: 'Kiểm tra tọa độ, chấm 4 tiêu chí định lượng, gửi phản hồi' },
    { role: 'HỆ THỐNG LMS', step: 'Ký số HMAC & Cấp chứng chỉ', action: 'Tự động tổng hợp điểm, ký số, phát hành chứng chỉ có QR' },
    { role: 'DOANH NGHIỆP', step: 'Quét QR kiểm chứng nguồn gốc', action: 'Kiểm tra tức thì tính hợp lệ và hộ chiếu nông sản số' },
  ];

  swimSteps.forEach((s, idx) => {
    const col = idx % 3;
    const row = Math.floor(idx / 3);
    const x = 0.8 + col * 3.95;
    const y = 1.3 + row * 2.75;
    addCard(s10, x, y, 3.75, 2.5, `${idx + 1}. ${s.step}`);
    s10.addText(`Vai trò: ${s.role}`, {
      x: x + 0.2, y: y + 0.55, w: 3.35, h: 0.3,
      fontSize: 11, fontFace: FONT_BODY, bold: true, color: C_PRIMARY
    });
    s10.addText(s.action, {
      x: x + 0.2, y: y + 0.9, w: 3.35, h: 1.4,
      fontSize: 12, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 18
    });
  });

  // =========================================================================
  // SLIDE 11: Hiện thực 8 khóa học & Thống kê
  // =========================================================================
  const s11 = createBaseSlide('HIỆN THỰC HÓA 8 KHÓA HỌC & SỐ LIỆU HỆ THỐNG', 'PHẦN 4: HIỆN THỰC & THỰC NGHIỆM');
  addCard(s11, 0.8, 1.3, 6.2, 5.3, '8 KHÓA HỌC CÔNG NGHỆ CAO CHUẨN HÓA');
  const courseList = [
    '1. CHAMSOC01: Canh tác rau an toàn VietGAP (18 mô-đun)',
    '2. CHEBIEN01: Sơ chế, đóng gói & bảo quản nông sản (16 mô-đun)',
    '3. IOTNHAMANG01: Vi khí hậu & tưới nhỏ giọt nhà màng (16 mô-đun)',
    '4. THUYCANH01: Canh tác rau thủy canh hồi lưu (16 mô-đun)',
    '5. SAURIENG01: Quy trình sầu riêng xuất khẩu chính ngạch (16 mô-đun)',
    '6. PHANVISINH01: Kỹ thuật ủ phân hữu cơ vi sinh (16 mô-đun)',
    '7. DAUTAY-CNC-01: Dâu tây giá thể ứng dụng IoT (18 mô-đun)',
    '8. DAUTAY-DONDUONG-01: Chi nhánh HTX Đơn Dương Cloned (18 mô-đun)',
  ];
  s11.addText(courseList.join('\n\n'), {
    x: 1.0, y: 1.8, w: 5.8, h: 4.6,
    fontSize: 12, fontFace: FONT_BODY, color: C_TEXT
  });

  addCard(s11, 7.3, 1.3, 5.2, 5.3, 'THỐNG KÊ HỆ THỐNG TRONG CSDL');
  const statsList = [
    `• Người dùng hệ thống: ${THESIS_META.stats.totalUsers} tài khoản (14 nông hộ)`,
    `• Tổng số mô-đun học tập: ${THESIS_META.stats.totalModules} hoạt động`,
    `• Ngân hàng câu hỏi Quiz: ${THESIS_META.stats.totalQuestions} câu hỏi`,
    `• Phiên điểm danh chuyên cần: ${THESIS_META.stats.attendanceSessions} phiên`,
    `• Bản ghi nhật ký AgriOps: ${THESIS_META.stats.fieldPracticeLogs} bản ghi`,
    `• Bài nộp thực hành GPS: ${THESIS_META.stats.evaluations} bài có tọa độ`,
    `• Chứng chỉ số HMAC-SHA256: ${THESIS_META.stats.verifiedCerts} chứng chỉ`,
    `• Bảng CSDL: ${THESIS_META.stats.dbTables} bảng (508 core + 5 custom)`,
  ];
  s11.addText(statsList.join('\n\n'), {
    x: 7.5, y: 1.8, w: 4.8, h: 4.6,
    fontSize: 12.5, fontFace: FONT_BODY, color: C_PRIMARY, bold: true
  });

  // =========================================================================
  // SLIDE 12: 5 Plugin chuyên biệt
  // =========================================================================
  const s12 = createBaseSlide('5 MODULE & PLUGIN TÙY BIẾN CHUYÊN BIỆT', 'PHẦN 4: HIỆN THỰC & THỰC NGHIỆM');
  const plugins = [
    { name: 'local_mamxanhcert', desc: 'Quản lý và phát hành chứng chỉ số tự động. Ký số HMAC-SHA256 chống làm giả điểm số. Cung cấp trang tra cứu bảo mật công khai qua mã QR.' },
    { name: 'mod_fieldpractice', desc: 'Hoạt động nộp bài thực hành thực địa ngoài vườn. Bắt buộc ghi nhận tọa độ GPS. Cán bộ kỹ thuật chấm điểm theo ma trận Rubric 4 tiêu chí.' },
    { name: 'local_agriops', desc: 'Sổ tay nhật ký canh tác điện tử VietGAP. Ghi nhận phân bón, thuốc BVTV, thời gian cách ly. Phát hành Hộ chiếu nông sản số cho người tiêu dùng.' },
    { name: 'local_mamxanhapp', desc: 'Bảng điểm học thuật chính thức, Phiếu phản hồi Rubric, Công cụ tính nồng độ phân bón Fertigation, Công cụ 1-Click Course Cloner nhân bản khóa trong 4s.' },
    { name: 'theme_agritrain_child', desc: 'Theme giao diện công thái học Minimalist Off-White Botanical (#F9FAF9/#234E3E), thanh Navbar đơn, ẩn tab thành viên với học viên, hỗ trợ song ngữ i18n.' },
  ];

  plugins.forEach((pl, idx) => {
    const y = 1.35 + idx * 1.1;
    addCard(s12, 1.0, y, 11.3, 0.95, pl.name, C_WHITE, 'E2E8F0');
    s12.addText(pl.desc, {
      x: 1.2, y: y + 0.42, w: 10.9, h: 0.45,
      fontSize: 12, fontFace: FONT_BODY, color: C_TEXT
    });
  });

  // =========================================================================
  // SLIDE 13: Kiểm thử tự động Puppeteer & RBAC
  // =========================================================================
  const s13 = createBaseSlide('KIỂM THỬ TỰ ĐỘNG E2E PUPPETEER & BẢO VỆ PHÂN QUYỀN RBAC', 'PHẦN 4: HIỆN THỰC & THỰC NGHIỆM');
  addCard(s13, 0.8, 1.3, 5.6, 5.3, 'KIỂM THỬ E2E TỰ ĐỘNG TRÊN 66 URLS');
  s13.addText([
    { text: '• Công nghệ kiểm thử:', options: { bold: true } },
    { text: ' Puppeteer trên Chrome Headless tự động duyệt 66 URL chức năng qua 4 vai trò.\n\n' },
    { text: '• Tỷ lệ thành công: 100% (66/66 URLs Pass)\n', options: { bold: true, color: C_PRIMARY } },
    { text: '  - Cổng thông tin & Khách: 6/6 (320 ms)\n' },
    { text: '  - Bảng điều khiển Học viên: 8/8 (410 ms)\n' },
    { text: '  - 8 Khóa học & Hoạt động: 24/24 (580 ms)\n' },
    { text: '  - Bài tập thực địa GPS: 8/8 (450 ms)\n' },
    { text: '  - Chứng chỉ số & QR: 6/6 (280 ms)\n' },
    { text: '  - Nghiệp vụ CBKT & Cloner: 8/8 (490 ms)\n' },
    { text: '  - Kiểm thử an ninh RBAC: 6/6 (180 ms)\n\n' },
    { text: '• 0 Lỗi layout, 0 lỗi 500, 0 Emoji phi học thuật.', options: { bold: true } }
  ], { x: 1.0, y: 1.8, w: 5.2, h: 4.6, fontSize: 12, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 18 });

  addCard(s13, 6.9, 1.3, 5.6, 5.3, 'BẢO VỆ PHÂN QUYỀN RBAC & AN TOÀN');
  s13.addText([
    { text: '• Ngăn chặn tấn công leo thang đặc quyền:\n', options: { bold: true, color: C_PRIMARY } },
    { text: ' Học viên cố tình truy cập trang nhập điểm /mod/fieldpractice/grade.php hoặc /grade/report/ -> Hệ thống ngắt phiên, trả mã HTTP 403 Forbidden.\n\n' },
    { text: '• Phát hiện chứng chỉ số bị sửa đổi:\n', options: { bold: true, color: 'DC2626' } },
    { text: ' Giả mạo điểm số trong CSDL từ 83.33 lên 100 -> Khi quét QR verify.php, hàm hash_equals phát hiện sai lệch chữ ký HMAC và cảnh báo đỏ lập tức.\n\n' },
    { text: '• Bảo vệ quyền riêng tư học viên:\n', options: { bold: true } },
    { text: ' Thu hồi moodle/course:viewparticipants đối với student; ẩn hoàn toàn tab Thành viên trên thanh điều hướng.' }
  ], { x: 7.1, y: 1.8, w: 5.2, h: 4.6, fontSize: 12, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 19 });

  // =========================================================================
  // SLIDE 14: Đánh giá SUS & Khảo sát
  // =========================================================================
  const s14 = createBaseSlide('ĐÁNH GIÁ ĐỘ KHẢ DỤNG THEO THANG ĐO QUỐC TẾ SUS', 'PHẦN 4: HIỆN THỰC & THỰC NGHIỆM');
  addCard(s14, 0.8, 1.3, 4.5, 5.3, 'KẾT QUẢ ĐIỂM SUS TỔNG HỢP');
  s14.addShape(pres.ShapeType.roundRect, {
    x: 1.3, y: 2.2, w: 3.5, h: 2.0, rectRadius: 0.15,
    fill: { color: C_PRIMARY }
  });
  s14.addText('86.5', {
    x: 1.3, y: 2.3, w: 3.5, h: 1.1,
    fontSize: 52, fontFace: FONT_TITLE, color: 'FCD34D', bold: true, align: 'center'
  });
  s14.addText('TRÊN THANG ĐIỂM 100', {
    x: 1.3, y: 3.3, w: 3.5, h: 0.35,
    fontSize: 12, fontFace: FONT_BODY, color: C_WHITE, align: 'center'
  });
  s14.addText('XẾP HẠNG: XUẤT SẮC (GRADE A+)\n(Theo chuẩn Bangor, Kortum & Miller)', {
    x: 1.0, y: 4.5, w: 4.1, h: 0.8,
    fontSize: 13, fontFace: FONT_BODY, color: C_PRIMARY, bold: true, align: 'center'
  });
  s14.addText('Thử nghiệm trên 30 người dùng đại diện:\n• 15 Nông hộ tại Lạc Dương & Đơn Dương\n• 10 Cán bộ kỹ thuật khuyến nông\n• 5 Sinh viên CNTT ĐH Đà Lạt', {
    x: 1.0, y: 5.4, w: 4.1, h: 1.0,
    fontSize: 11.5, fontFace: FONT_BODY, color: C_MUTED
  });

  addCard(s14, 5.6, 1.3, 6.9, 5.3, 'CÁC CHỈ SỐ KHẢ DỤNG NỔI BẬT');
  const susHighlights = [
    { q: 'Giao diện nhất quán, các trang đều dễ hiểu:', val: '4.8 / 5.0' },
    { q: 'Dễ dàng thao tác ngay cả khi ở ngoài trời nắng:', val: '4.7 / 5.0' },
    { q: 'Nông dân học cách sử dụng hệ thống rất nhanh:', val: '4.6 / 5.0' },
    { q: 'Thích sử dụng hệ thống này thường xuyên:', val: '4.6 / 5.0' },
    { q: 'Các tính năng tích hợp rất đồng bộ và mạch lạc:', val: '4.5 / 5.0' },
    { q: 'Không cảm thấy rườm rà hay phiền toái khi học:', val: '4.5 / 5.0' },
  ];
  susHighlights.forEach((sh, idx) => {
    const y = 2.0 + idx * 0.7;
    s14.addText(`• ${sh.q}`, {
      x: 5.9, y, w: 5.0, h: 0.4,
      fontSize: 12.5, fontFace: FONT_BODY, color: C_TEXT
    });
    s14.addText(sh.val, {
      x: 10.8, y, w: 1.4, h: 0.4,
      fontSize: 13, fontFace: FONT_BODY, bold: true, color: C_PRIMARY, align: 'right'
    });
  });

  // =========================================================================
  // SLIDE 15: Đóng góp chính
  // =========================================================================
  const s15 = createBaseSlide('TỔNG KẾT CÁC ĐÓNG GÓP CHÍNH CỦA ĐỒ ÁN', 'PHẦN 5: KẾT LUẬN & HƯỚNG PHÁT TRIỂN');
  const contribs = [
    { title: '1. CÔNG THÁI HỌC UI/UX CHO NÔNG DÂN', desc: 'Bộ chuẩn Minimalist Off-White Botanical (#F9FAF9/#234E3E), Single Navbar, nút bấm lớn 1 chạm, chống chói, loại bỏ emoji, đạt SUS 86.5/100 (Hạng A+).' },
    { title: '2. CHUẨN HÓA 8 KHÓA HỌC SƯ PHẠM', desc: 'Số hóa 8 chương trình tập huấn công nghệ cao theo cấu trúc 6 chuyên đề, tích hợp bài giảng SCORM 1.2 và H5P tương tác cao.' },
    { title: '3. 5 PLUGIN NGHIỆP VỤ CHUYÊN BIỆT', desc: 'Thực hành hiện trường GPS (mod_fieldpractice), chứng chỉ số HMAC-SHA256 & QR (local_mamxanhcert), nhật ký VietGAP (local_agriops), bảng điểm học thuật (local_mamxanhapp).' },
    { title: '4. VẬN HÀNH DOCKER & KIỂM THỬ TỰ ĐỘNG', desc: 'Khởi chạy toàn bộ hệ thống trong 60s trên Docker Turbo; kiểm thử tự động hóa E2E với Puppeteer trên 66 URL đạt tỷ lệ thành công 100%.' },
  ];

  contribs.forEach((c, idx) => {
    const col = idx % 2;
    const row = Math.floor(idx / 2);
    const x = 0.8 + col * 5.95;
    const y = 1.3 + row * 2.75;
    addCard(s15, x, y, 5.75, 2.5, c.title);
    s15.addText(c.desc, {
      x: x + 0.2, y: y + 0.65, w: 5.35, h: 1.6,
      fontSize: 13, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 20
    });
  });

  // =========================================================================
  // SLIDE 16: Hướng phát triển
  // =========================================================================
  const s16 = createBaseSlide('HẠN CHẾ & HƯỚNG PHÁT TRIỂN TIẾP THEO', 'PHẦN 5: KẾT LUẬN & HƯỚNG PHÁT TRIỂN');
  addCard(s16, 0.8, 1.3, 5.6, 5.3, 'HẠN CHẾ HIỆN TẠI');
  s16.addText([
    { text: '• Phụ thuộc kết nối mạng Internet:', options: { bold: true, color: 'DC2626' } },
    { text: ' Tại các thung lũng sâu sóng 4G chập chờn, việc tải video dung lượng lớn gặp độ trễ.\n\n' },
    { text: '• Chấm điểm thực địa phụ thuộc thủ công:', options: { bold: true, color: 'DC2626' } },
    { text: ' Cán bộ kỹ thuật phải xem ảnh bằng mắt thường; khi số lượng học viên tăng hàng nghìn người sẽ tạo áp lực chấm bài.' }
  ], { x: 1.0, y: 1.8, w: 5.2, h: 4.5, fontSize: 13, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 22 });

  addCard(s16, 6.9, 1.3, 5.6, 5.3, 'HƯỚNG PHÁT TRIỂN TƯƠNG LAI');
  s16.addText([
    { text: '• Trí tuệ Nhân tạo chẩn đoán sâu bệnh (AI Vision):', options: { bold: true, color: C_PRIMARY } },
    { text: ' Tích hợp mô hình Deep Learning tự động nhận diện bệnh cây trồng khi nông dân chụp ảnh nộp bài, gợi ý phương án điều trị tức thì.\n\n' },
    { text: '• Ứng dụng Di động Ngoại tuyến (PWA Offline):', options: { bold: true, color: C_PRIMARY } },
    { text: ' Service Worker & IndexedDB cho phép học tập và ghi nhật ký không cần mạng; tự động đồng bộ khi có sóng.\n\n' },
    { text: '• Liên thông Sàn Giao dịch Nông sản Số:', options: { bold: true, color: C_PRIMARY } },
    { text: ' Kết nối cơ sở dữ liệu Mã số vùng trồng của Bộ Nông nghiệp & PTNT và liên kết chuỗi tiêu thụ sản phẩm.' }
  ], { x: 7.1, y: 1.8, w: 5.2, h: 4.5, fontSize: 12.5, fontFace: FONT_BODY, color: C_TEXT, lineSpacing: 20 });

  // =========================================================================
  // SLIDE 17: Thank You & Q&A
  // =========================================================================
  const s17 = pres.addSlide();
  s17.background = { color: C_PRIMARY };

  s17.addText('XIN TRÂN TRỌNG CẢM ƠN', {
    x: 0.8, y: 1.8, w: 11.7, h: 0.6,
    fontSize: 32, fontFace: FONT_TITLE, color: 'FCD34D', bold: true, align: 'center'
  });

  s17.addText('QUÝ THẦY CÔ TRONG HỘI ĐỒNG CHẤM ĐỒ ÁN TỐT NGHIỆP\nKHOA CÔNG NGHỆ THÔNG TIN - TRƯỜNG ĐẠI HỌC ĐÀ LẠT', {
    x: 1.0, y: 2.6, w: 11.3, h: 0.9,
    fontSize: 18, fontFace: FONT_TITLE, color: C_WHITE, bold: true, align: 'center', lineSpacing: 26
  });

  s17.addShape(pres.ShapeType.line, {
    x: 4.5, y: 3.8, w: 4.3, h: 0,
    line: { color: 'FCD34D', width: 2 }
  });

  s17.addText('Nhóm sinh viên thực hiện:', {
    x: 0.8, y: 4.2, w: 11.7, h: 0.4,
    fontSize: 15, fontFace: FONT_BODY, color: 'D1FAE5', align: 'center'
  });

  const svInfo = THESIS_META.students.map(s => `${s.id} – ${s.name}`).join('    |    ');
  s17.addText(svInfo, {
    x: 0.8, y: 4.65, w: 11.7, h: 0.4,
    fontSize: 15, fontFace: FONT_BODY, color: C_WHITE, bold: true, align: 'center'
  });

  s17.addText(`Cán bộ hướng dẫn: ${THESIS_META.supervisor.title} ${THESIS_META.supervisor.name}`, {
    x: 0.8, y: 5.2, w: 11.7, h: 0.4,
    fontSize: 14, fontFace: FONT_BODY, color: 'FDE68A', italic: true, align: 'center'
  });

  s17.addText('KÍNH MỜI QUÝ THẦY CÔ ĐẶT CÂU HỎI & GÓP Ý CHO ĐỒ ÁN', {
    x: 0.8, y: 6.0, w: 11.7, h: 0.4,
    fontSize: 15, fontFace: FONT_TITLE, color: 'FCD34D', bold: true, align: 'center'
  });

  // Write file
  const outDir = path.resolve('../../CD_DVD_SUBMISSION/THESIS');
  fs.mkdirSync(outDir, { recursive: true });
  const outPath = path.join(outDir, 'BAO_CAO_THUYET_TRINH_SLIDES.pptx');

  await pres.writeFile({ fileName: outPath });
  const stats = fs.statSync(outPath);
  console.log(`[SUCCESS] Presentation slides generated successfully!`);
  console.log(`  File: ${outPath}`);
  console.log(`  Size: ${(stats.size / 1024).toFixed(1)} KB (${stats.size} bytes)`);
}

generateSlideDeck().catch(err => {
  console.error('[FATAL ERROR IN SLIDES]:', err);
  process.exit(1);
});
