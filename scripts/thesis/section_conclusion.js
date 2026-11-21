import {
  p,
  pRuns,
  h1,
  h2,
  h3,
  bullet,
  pageBreak,
} from './styles.js';
import { THESIS_META } from './metadata.js';

export function buildConclusionSection() {
  return [
    h1('KẾT LUẬN', 'sec_conclusion'),
    p('Sau 12 tuần nghiên cứu nghiêm túc, khảo sát thực tế và nỗ lực hiện thực hóa công nghệ, nhóm sinh viên thực hiện đề tài đã hoàn thành xuất sắc toàn bộ các mục tiêu nghiên cứu và phát triển đề ra ban đầu. Đề tài "Xây dựng hệ thống quản lý học tập và tập huấn kỹ thuật canh tác nông nghiệp công nghệ cao trên nền tảng Moodle" (Mầm Xanh LMS 2.0) đã khẳng định tính đúng đắn về mặt phương pháp luận và giá trị thực tiễn to lớn đối với tiến trình chuyển đổi số nông nghiệp tại tỉnh Lâm Đồng.'),
    p('Đề tài đã hoàn thành và đạt được 4 đóng góp kỹ thuật cốt lõi sau:'),
    bullet('Về mặt Trải nghiệm Người dùng (UI/UX): Đã nghiên cứu và áp dụng thành công bộ quy chuẩn công thái học Minimalist Off-White Botanical (#F9FAF9 / #234E3E), loại bỏ triệt để các menu đa tầng rườm rà, thiết lập một thanh điều hướng đơn (Single Navbar) trực quan, tối ưu kích thước nút bấm cảm ứng lớn, chống chói lóa mắt dưới ánh nắng mặt trời và loại bỏ hoàn toàn các biểu tượng cảm xúc phi học thuật. Giao diện được kiểm chứng đạt điểm khả dụng SUS 86.5/100 (Hạng A+ Xuất sắc);', { boldPrefix: 'Đóng góp 1:' }),
    bullet('Về mặt Số hóa Nội dung Sư phạm: Chuẩn hóa thành công 8 chương trình tập huấn kỹ thuật canh tác nông nghiệp công nghệ cao chủ lực (Dâu tây nhà kính, Rau thủy canh, Rau VietGAP, Cây ăn trái xuất khẩu, Phân bón vi sinh, Nhà màng IoT) theo cấu trúc sư phạm chuẩn hóa (Phần Thông tin chung & Tài liệu tham khảo + 4 Chuyên đề kỹ thuật chuyên sâu + Phần Kiểm tra & Đánh giá), tích hợp hài hòa các chuẩn bài giảng e-Learning quốc tế tiên tiến nhất hiện nay gồm SCORM 1.2 và H5P Interactive Content;', { boldPrefix: 'Đóng góp 2:' }),
    bullet('Về mặt Kỹ nghệ Phần mềm và Đổi mới Nghiệp vụ: Phát triển hoàn chỉnh 5 plugin chuyên biệt độc lập tuân thủ chuẩn mực Moodle Architecture: mod_fieldpractice (nộp bài thực hành ngoài vườn có định vị GPS và chấm theo Rubric), local_mamxanhcert (ký số chứng chỉ điện tử bằng HMAC-SHA256 kết hợp mã QR chống giả mạo), local_agriops (sổ tay nhật ký canh tác điện tử và hộ chiếu nông sản số liên thông VietGAP), local_mamxanhapp (bảng điểm học thuật và công cụ nhân bản khóa học địa phương trong 4 giây), và theme_agritrain_child (song ngữ Việt - Anh);', { boldPrefix: 'Đóng góp 3:' }),
    bullet('Về mặt Vận hành, Bảo mật và Kiểm thử Tự động: Xây dựng hạ tầng ảo hóa vi dịch vụ 5 container Docker Turbo giúp khởi chạy toàn bộ hệ thống chỉ trong 60 giây; đồng thời thiết lập bộ kịch bản kiểm thử hồi quy tự động hóa End-to-End bằng Puppeteer trên 66 URL chức năng, bảo đảm 100% kịch bản kiểm thử thành công, an toàn tuyệt đối trước các nguy cơ tấn công leo thang đặc quyền RBAC.', { boldPrefix: 'Đóng góp 4:' }),
    p('Tóm lại, sản phẩm phần mềm của đề tài đã đáp ứng trọn vẹn các yêu cầu nghiệp vụ khắt khe của mô hình đào tạo kết hợp (Blended Learning) cho nông nghiệp, sẵn sàng để đưa vào triển khai thí điểm tại các hợp tác xã và trung tâm khuyến nông trên địa bàn tỉnh Lâm Đồng.'),

    pageBreak(),

    h1('HƯỚNG PHÁT TRIỂN', 'sec_future'),
    p('Nhằm tiếp tục nâng cao giá trị ứng dụng thực tiễn và mở rộng quy mô phục vụ của hệ thống trong giai đoạn tiếp theo, nhóm nghiên cứu đề xuất các hướng phát triển trọng tâm sau:'),
    pRuns([
      { text: '1. Ứng dụng AI chẩn đoán sâu bệnh: ', bold: true },
      { text: 'Tích hợp Trí tuệ Nhân tạo (AI & Computer Vision) hỗ trợ chẩn đoán sâu bệnh tự động bằng cách xây dựng mô hình học sâu (Deep Learning / Convolutional Neural Networks) huấn luyện trên tập dữ liệu ảnh bệnh cây trồng của Lâm Đồng, cho phép ứng dụng tự động nhận diện các triệu chứng bệnh (như nhện đỏ, phấn trắng, đốm lá) ngay khi nông hộ chụp ảnh nộp bài thực địa, đưa ra gợi ý xử lý tức thì cho nông dân và hỗ trợ sơ bộ cho cán bộ kỹ thuật trước khi chấm điểm;' },
    ]),
    pRuns([
      { text: '2. Hoàn thiện PWA Offline: ', bold: true },
      { text: 'Phát triển Ứng dụng Di động Ngoại tuyến (Progressive Web App - PWA Offline Mode) ứng dụng công nghệ Service Worker và cơ sở dữ liệu cục bộ IndexedDB trên điện thoại, cho phép người nông dân tải trước bài học về máy, ghi chép nhật ký canh tác và chụp ảnh hiện trường ngay cả khi mất mạng hoàn toàn; dữ liệu sẽ tự động đồng bộ hóa lên máy chủ trung tâm ngay khi thiết bị có lại kết nối Internet;' },
    ]),
    pRuns([
      { text: '3. Kết nối Sàn Nông sản Số: ', bold: true },
      { text: 'Liên thông Hệ sinh thái Nông nghiệp Số Quốc gia bằng cách kết nối giao tiếp API với Hệ thống Cơ sở Dữ liệu Nông nghiệp của Sở Nông nghiệp & PTNT tỉnh Lâm Đồng và Cổng Dịch vụ công Quốc gia để liên thông dữ liệu cấp Mã số vùng trồng (Planting Area Code), đồng thời kết nối trực tiếp với các Sàn giao dịch Nông sản số nhằm giúp nông dân có chứng chỉ VietGAP bán được nông sản với giá trị cao nhất.' },
    ]),

    pageBreak(),
  ];
}
