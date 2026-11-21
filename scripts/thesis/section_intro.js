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

export function buildIntroSection() {
  return [
    h1('MỞ ĐẦU', 'sec_modau'),

    h2('1. Lí do chọn đề tài', 'sec_intro_1'),
    p('Tỉnh Lâm Đồng nói chung và thành phố Đà Lạt nói riêng từ lâu đã khẳng định vị thế là trung tâm nông nghiệp công nghệ cao hàng đầu của cả nước, nổi tiếng với các vùng chuyên canh rau an toàn, hoa ôn đới, dâu tây nhà màng và cây ăn trái giá trị cao. Trong định hướng chiến lược phát triển kinh tế - xã hội giai đoạn 2021 – 2030, tỉnh xác định chuyển đổi số nông nghiệp là một trong những trụ cột then chốt nhằm thúc đẩy năng suất, tối ưu hóa chuỗi cung ứng và nâng tầm thương hiệu nông sản Việt Nam trên thị trường quốc tế.'),
    p('Tuy nhiên, nhân tố quyết định sự thành công của chuyển đổi số nông nghiệp chính là lực lượng lao động trực tiếp – những người nông dân và thành viên các tổ hợp tác sản xuất. Để có thể vận hành hiệu quả các hệ thống nhà màng thông minh, thiết bị châm phân tự động, hệ thống tưới nhỏ giọt kiểm soát qua cảm biến IoT, cũng như tuân thủ nghiêm ngặt các tiêu chuẩn an toàn thực phẩm như VietGAP hay GlobalGAP, người nông dân bắt buộc phải liên tục cập nhật kiến thức khoa học kỹ thuật chuyên sâu.'),
    p('Trên thực tế, phương thức tập huấn kỹ thuật truyền thống hiện nay chủ yếu dựa vào các lớp bồi dưỡng tập trung do Trung tâm Khuyến nông hoặc Chi cục Trồng trọt & Bảo vệ Thực vật tổ chức. Phương thức này đang bộc lộ nhiều điểm nghẽn nghiêm trọng:'),
    bullet('Chi phí tổ chức lớp học tập trung tốn kém về địa điểm, in ấn tài liệu giấy và thù lao chuyên gia, khiến tần suất tổ chức bị giới hạn;'),
    bullet('Thời gian tập huấn cố định thường xung đột trực tiếp với mùa vụ thu hoạch và chăm sóc đồng ruộng của nông dân, dẫn đến tỷ lệ tham gia không đều đặn;'),
    bullet('Thiếu công cụ theo dõi, kiểm soát và xác thực quá trình thực hành kỹ thuật ngoài thực địa. Việc cấp giấy chứng nhận sau khóa học thường mang tính hình thức, thiếu cơ chế số hóa chống làm giả.'),
    p('Trong khi đó, việc ứng dụng các hệ thống e-Learning sẵn có (như Moodle mặc định, Google Classroom hay Canvas) lại gặp rào cản lớn về mặt trải nghiệm người dùng (UX). Giao diện của các nền tảng này thường được thiết kế cho môi trường học đường với mật độ thông tin dày đặc, nhiều bước điều hướng phức tạp, sử dụng các thuật ngữ học thuật trừu tượng và không tương thích tốt với điều kiện sử dụng bằng điện thoại thông minh dưới ánh nắng gắt ngoài đồng ruộng.'),
    p('Xuất phát từ những trăn trở thực tiễn và đòi hỏi cấp bách của quá trình chuyển đổi số nông nghiệp tại địa phương, nhóm nghiên cứu đã lựa chọn đề tài: "Xây dựng hệ thống quản lý học tập và tập huấn kỹ thuật canh tác nông nghiệp công nghệ cao trên nền tảng Moodle" (Mầm Xanh LMS 2.0). Đề tài hướng đến việc kiến tạo một môi trường đào tạo số chuyên biệt, công thái học, bảo mật và liên thông giữa học lý thuyết với thực hành hiện trường.'),

    h2('2. Mục đích nghiên cứu của đề tài', 'sec_intro_2'),
    p('Mục tiêu trọng tâm của đề tài là nghiên cứu, thiết kế và phát triển một giải pháp e-Learning chuyên biệt cho đào tạo nông nghiệp công nghệ cao, cụ thể hóa qua các mục tiêu chi tiết sau:'),
    bullet('Nghiên cứu và làm chủ kiến trúc mở của Moodle 4.5 LTS, từ đó xây dựng hạ tầng ảo hóa Docker Turbo tối ưu hiệu năng phục vụ đào tạo diện rộng với chi phí tài nguyên thấp;', { boldPrefix: 'Thứ nhất:' }),
    bullet('Nghiên cứu nguyên lý thiết kế giao diện công thái học tối giản (Minimalist Off-White UI/UX) để loại bỏ rào cản công nghệ cho nông hộ, đảm bảo khả năng tương tác trực quan, một chạm trên điện thoại di động;', { boldPrefix: 'Thứ hai:' }),
    bullet('Chuẩn hóa và số hóa 8 chương trình tập huấn kỹ thuật canh tác công nghệ cao đặc thù của Lâm Đồng theo các chuẩn e-Learning quốc tế (SCORM 1.2, H5P Activity);', { boldPrefix: 'Thứ ba:' }),
    bullet('Lập trình các phân hệ plugin nghiệp vụ chuyên sâu: thực hành thực địa GPS (mod_fieldpractice), cấp chứng chỉ số bảo mật bằng mã hóa HMAC-SHA256 kết hợp mã QR (local_mamxanhcert), và nhật ký điện tử VietGAP (local_agriops);', { boldPrefix: 'Thứ tư:' }),
    bullet('Xây dựng quy trình kiểm thử tự động toàn diện End-to-End với Puppeteer để đảm bảo tính ổn định và an toàn thông tin của toàn hệ thống.', { boldPrefix: 'Thứ năm:' }),

    h2('3. Đối tượng và phạm vi nghiên cứu', 'sec_intro_3'),
    p('3.1. Đối tượng nghiên cứu:', { indent: 0, bold: true }),
    bullet('Quy trình quản lý đào tạo, tập huấn kỹ thuật và chuyển giao công nghệ canh tác nông nghiệp công nghệ cao;'),
    bullet('Cấu trúc kiến trúc module hóa, hệ thống bảng cơ sở dữ liệu và cơ chế hooks / events của hệ thống LMS mã nguồn mở Moodle 4.5;'),
    bullet('Các chuẩn đóng gói bài giảng điện tử tương tác SCORM 1.2, H5P, và phương pháp đánh giá năng lực thực hành dựa trên ma trận tiêu chí Rubric;'),
    bullet('Giải thuật mật mã học hàm băm HMAC-SHA256 và cơ chế mã hóa thông tin trên mã phản hồi nhanh (QR Code).'),

    p('3.2. Phạm vi nghiên cứu:', { indent: 0, bold: true }),
    bullet('Về không gian: Hệ thống được xây dựng và thử nghiệm với dữ liệu thực tế tại các vùng canh tác nông nghiệp công nghệ cao thuộc tỉnh Lâm Đồng (Đà Lạt, Lạc Dương, Đơn Dương, Đức Trọng);'),
    bullet('Về nội dung chuyên môn: Tập trung vào 8 chuyên đề canh tác chủ lực: Canh tác rau an toàn VietGAP, Dâu tây công nghệ cao nhà màng, Rau thủy canh hồi lưu, Sầu riêng xuất khẩu, Phân bón hữu cơ vi sinh, Hệ thống nhà màng IoT, Sơ chế sau thu hoạch và Khóa học mẫu chuẩn hóa;'),
    bullet('Về mặt kỹ thuật: Triển khai trên nền tảng Docker Container, hỗ trợ đa nền tảng (Desktop và Mobile Web) thông qua giao diện Web thích ứng (Responsive Web Design).'),

    h2('4. Phương pháp nghiên cứu', 'sec_intro_4'),
    p('Để đạt được các mục tiêu đã đề ra, đề tài kết hợp linh hoạt các phương pháp nghiên cứu sau:'),
    bullet('Phương pháp nghiên cứu lý thuyết: Thu thập, tổng hợp và phân tích các tài liệu chuẩn quốc tế về e-Learning (ADL SCORM, H5P.org), tài liệu kiến trúc Moodle Core Architecture, cùng các tiêu chuẩn kỹ thuật nông nghiệp VietGAP do Bộ Nông nghiệp & PTNT ban hành;'),
    bullet('Phương pháp điều tra, khảo sát thực tế: Tiếp xúc, phỏng vấn trực tiếp các cán bộ khuyến nông, kỹ sư nông nghiệp và các chủ hộ nông dân để thấu hiểu thói quen sử dụng thiết bị số, rào cản thao tác và mong muốn đối với một hệ thống đào tạo trực tuyến;'),
    bullet('Phương pháp mô hình hóa và thiết kế phần mềm: Sử dụng ngôn ngữ mô hình hóa thống nhất (UML) để xây dựng biểu đồ ca sử dụng (Use Case), biểu đồ quan hệ thực thể (ERD), và biểu đồ luồng nghiệp vụ (Swimlane) thể hiện sự tương tác đa vai trò;'),
    bullet('Phương pháp kỹ nghệ phần mềm dựa trên thành phần: Kế thừa nền tảng Moodle 4.5 LTS, phát triển tùy biến tầng giao diện (Child Theme) và phát triển độc lập 5 plugin nghiệp vụ tuân thủ nghiêm ngặt chuẩn Moodle Coding Style;'),
    bullet('Phương pháp kiểm thử và thực nghiệm khoa học: Xây dựng bộ kịch bản kiểm thử tự động hóa E2E sử dụng Puppeteer trên trình duyệt Chrome không đầu (Headless Browser) qua 66 URL chức năng; đánh giá độ khả dụng hệ thống bằng thang đo tiêu chuẩn System Usability Scale (SUS).'),

    h2('5. Ý nghĩa khoa học và giá trị thực tiễn', 'sec_intro_5'),
    p('5.1. Ý nghĩa khoa học:', { indent: 0, bold: true }),
    p('Đề tài đóng góp một mô hình đào tạo kết hợp (Blended Learning) sáng tạo cho ngành nông nghiệp, nơi kiến thức lý thuyết tương tác được liên kết chặt chẽ với nhiệm vụ thực hành tại vườn có ghi nhận vị trí địa lý GPS và đánh giá khách quan bằng thang đo Rubric. Đồng thời, nghiên cứu đề xuất giải pháp ứng dụng chữ ký số HMAC-SHA256 trên mã QR trong chứng nhận đào tạo nghề nông thôn, bảo đảm tính xác thực và chống làm giả với chi phí tính toán tối thiểu.'),

    p('5.2. Giá trị thực tiễn:', { indent: 0, bold: true }),
    bullet('Đối với người nông dân: Cung cấp một cổng học tập trực quan, thân thiện, dễ hiểu, giúp học viên có thể tranh thủ học mọi lúc mọi nơi ngay trên đồng ruộng, xem lại kỹ thuật và ghi chép nhật ký sản xuất điện tử để nâng cao giá trị thương phẩm nông sản;'),
    bullet('Đối với cán bộ khuyến nông / Giảng viên: Tiết kiệm tối đa thời gian chấm bài, dễ dàng theo dõi sát sao mức độ hiểu bài và tiến độ làm vườn của từng nông hộ thông qua bảng điều khiển số liệu thống nhất;'),
    bullet('Đối với các hợp tác xã và doanh nghiệp nông nghiệp: Sở hữu một công cụ chuẩn hóa tay nghề cho xã viên và nhân công canh tác, dễ dàng cung cấp hồ sơ năng lực và chứng chỉ đào tạo minh bạch cho đối tác thu mua trong và ngoài nước.'),

    h2('6. Bố cục của đồ án tốt nghiệp', 'sec_intro_6'),
    p('Ngoài các trang bìa, lời cảm ơn, mục lục, danh mục hình vẽ, danh mục bảng biểu và từ viết tắt, nội dung chính của đồ án được cấu trúc thành 4 chương như sau:'),
    bullet('MỞ ĐẦU: Trình bày lí do chọn đề tài, mục đích, đối tượng, phạm vi, phương pháp nghiên cứu, ý nghĩa khoa học và thực tiễn của đề tài.'),
    bullet('CHƯƠNG 1: TỔNG QUAN VỀ HỆ THỐNG E-LEARNING TRONG ĐÀO TẠO NÔNG NGHIỆP CÔNG NGHỆ CAO: Phân tích bối cảnh nông nghiệp Lâm Đồng, thực trạng đào tạo nông dân, khảo sát các nền tảng e-Learning hiện hữu và xác định bài toán đặt ra cho hệ thống Mầm Xanh LMS 2.0.'),
    bullet('CHƯƠNG 2: CƠ SỞ LÝ THUYẾT VÀ CÔNG NGHỆ ÁP DỤNG: Trình bày các nền tảng công nghệ cốt lõi gồm Moodle 4.5 LTS, SCORM 1.2, H5P, ma trận Rubric, thuật toán mã hóa chữ ký số HMAC-SHA256 & QR Code, triết lý thiết kế Minimalist Off-White và kiến trúc Docker Turbo.'),
    bullet('CHƯƠNG 3: PHÂN TÍCH VÀ THIẾT KẾ HỆ THỐNG: Chi tiết hóa các yêu cầu chức năng, phi chức năng, biểu đồ Use Case, kiến trúc phân tầng, quy trình nghiệp vụ Swimlane, mô hình dữ liệu ERD và thiết kế giao diện.'),
    bullet('CHƯƠNG 4: XÂY DỰNG, TRIỂN KHAI VÀ THỰC NGHIỆM HỆ THỐNG: Mô tả quá trình hiện thực hóa 8 khóa học, 5 plugin chuyên biệt, bộ công cụ bảo trì CLI, và trình bày kết quả kiểm thử tự động 66 kịch bản cùng số liệu thực nghiệm.'),
    bullet('KẾT LUẬN & HƯỚNG PHÁT TRIỂN: Đánh giá tổng kết các kết quả đạt được, đóng góp mới của đề tài và đề xuất định hướng phát triển ứng dụng di động ngoại tuyến và AI trong tương lai.'),
    bullet('DANH MỤC TÀI LIỆU THAM KHẢO & PHỤ LỤC: Danh mục tài liệu tham khảo theo chuẩn DLU và hướng dẫn triển khai hệ thống chi tiết.'),

    pageBreak(),
  ];
}
