# 📗 Hướng dẫn Biên soạn Khóa học Số hóa & Chấm điểm Thực hành (Instructor Authoring Guide)

Tài liệu này dành cho **Cán bộ Kỹ thuật Nông nghiệp (CBKT — tài khoản mẫu: `cbkt_pilot` / `MX!Canbo2026`)** phụ trách xây dựng học liệu tương tác, tổ chức tập huấn hiện trường và theo dõi tiến độ nông hộ trên **Mầm Xanh LMS 2.0**.

---

## 1. Đăng nhập & Trung tâm Quản lý Tập huấn (`manage.php`)

1. Truy cập `https://moodle.lvh.me:8443/login/index.php`.
2. Nhấn nút **"🧑‍🔬 Cán bộ Kỹ thuật (1-Chạm)"** (hoặc nhập `cbkt_pilot` / `MX!Canbo2026`).
3. Hệ thống tự động điều hướng thẳng vào **Trung tâm Quản lý & Báo cáo Tập huấn** (`/local/mamxanhapp/manage.php`):
   - **Thẻ KPI Thời gian thực:** Tổng số nông hộ đang học, tổng diện tích canh tác ($\text{ha}$), điểm trung bình Quiz toàn hệ thống, điểm trung bình Thực hành vườn Rubric, số Chứng nhận số QR đã cấp và số lô VietGAP đang hoạt động.
   - **Biểu đồ Trực quan theo Tiểu vùng:** So sánh năng lực học tập giữa **Lâm Đồng**, **Đắk Lắk** và **Đồng Nai**, kèm bộ lọc nhanh 1-chạm theo từng vùng trồng.
   - **Xuất Báo cáo Excel Chuẩn Tiếng Việt (`manage.php?download=csv`):** Nhấn nút **"📥 Xuất Báo cáo CSV (Chuẩn Excel UTF-8)"**. File đính kèm sẵn ký tự **UTF-8 BOM (`\xEF\xBB\xBF`)** giúp mở trực tiếp bằng Microsoft Excel hiển thị hoàn hảo dấu tiếng Việt (hoặc xuất bản tiếng Anh khi đang bật chế độ `🇬🇧 EN`).

---

## 2. Quy chuẩn Biên soạn Khóa học Tương tác 4 Chuyên đề (Không dùng PDF tĩnh)

Để đảm bảo nông dân đọc tốt trên màn hình điện thoại ngoài vườn và nghe được bằng **Trợ lý Giọng đọc `#mx-tts-player`**, mọi khóa học trên Mầm Xanh LMS 2.0 tuân thủ cấu trúc **4 Chuyên đề Số hóa**:

### Chuyên đề 1: Video Minh họa Trực quan & Infographic Kỹ thuật (Activity: `Page`)
- Vào khóa học -> Bật **Chế độ Chỉnh sửa (Edit mode)** ở góc trên bên phải -> **Thêm hoạt động hoặc tài nguyên** -> chọn **Trang (Page)**.
- Nhúng khung Video/Animation HTML5 kèm lưới **Infographic thẻ thông số** (ví dụ: ngưỡng pH $5.8 - 6.3$, EC $1.4 - 2.2\text{ mS/cm}$, nhiệt độ làm mát sơ bộ Pre-cooling).
- **Mẹo hỗ trợ Giọng đọc Tự động:** Trợ lý `#mx-tts-player` sẽ tự động quét văn bản trong vùng nội dung bài học để đọc to bằng giọng `vi-VN` hoặc `en-US` mà cán bộ không cần thu âm thủ công.
- Mọi bảng biểu thông số (`<table>`) đều được theme tự động bọc lớp `.table-responsive` để vuốt ngang mượt mà trên điện thoại `375px`.

### Chuyên đề 2: Bài giảng Tương tác Đa trang — Interactive Slide Deck (Activity: `Page`)
- Thiết kế từ **6 đến 8 slides ngắn gọn**, mỗi slide tập trung giải quyết 1 bước quy trình kỹ thuật (kèm sơ đồ SVG như hệ thống châm phân Venturi, buồng sấy lạnh bơm nhiệt, bao bì khí quyển biến đổi MAP).
- Đặt **Câu hỏi Tình huống Thực tế (Quick Check)** ngay tại slide cuối để học viên tự kiểm tra phản xạ xử lý sự cố ngoài đồng trước khi sang chuyên đề sau.

### Chuyên đề 3: Học liệu Thẻ lật Sâu bệnh IPM & Thực hành tại Vườn (`Page` + `mod_fieldpractice`)
1. **Thẻ lật / Accordion Chẩn đoán Sâu bệnh (IPM):** Trình bày rõ 2 mặt cho từng đối tượng dịch hại (*Bệnh Gỉ sắt, Rệp sáp, Bọ trĩ, Nhện đỏ*):
   - **Mặt trước:** Triệu chứng nhận diện bằng mắt thường trên lá, đọt non, trái.
   - **Mặt sau:** Phác đồ xử lý sinh học an toàn (thiên địch, bẫy dính màu, nấm *Trichoderma* / *Beauveria*, dầu khoáng sinh học) và thời gian cách ly (PHI).
2. **Hoạt động Thực hành tại vườn (`Thực hành tại vườn / Field Practice`):**
   - Thêm hoạt động **Thực hành tại vườn** vào Chuyên đề 3.
   - Khai báo **Tên địa điểm vườn mẫu**, **Tọa độ GPS tâm vườn (`target_lat`, `target_lng`)** và thang điểm tối đa (`100`).

### Chuyên đề 4: Bài Kiểm tra Đánh giá Năng lực — Moodle Quiz 12 Câu hỏi (`mod_quiz`)
- Thiết lập **12 câu hỏi trắc nghiệm tình huống thực tế**, bật trộn đáp án, cho phép làm lại để củng cố kiến thức, điểm đạt **7.0 / 10.0 ($\ge 70\%$)**.
- **Lưu ý Kỹ thuật Quan trọng:** Luôn đảm bảo khóa học đã có `grade_categories` mặc định (tự động tạo qua API `grade_category::fetch_course_category($courseid)`) và bảng `quiz_sections` để tránh lỗi khi nộp bài.
- Bật **Activity Completion** để ngay khi học viên nộp bài đạt $\ge 70\%$, Observer của hệ thống sẽ tự động phát hành **Chứng nhận Số QR `HMAC-SHA256`** và **Huy hiệu Khóa học (Badge)**.

---

## 3. Quy trình Tổ chức & Chấm điểm Thực hành tại Vườn (Rubric 4 Tiêu chí)

Tại buổi tập huấn thực địa ở vườn mẫu hoặc nhà màng:

1. **Điểm danh Hiện trường bằng QR Xoay vòng & GPS:**
   - CBKT mở hoạt động **Thực hành tại vườn** (`/mod/fieldpractice/view.php?id=<cmid>`).
   - Hệ thống hiển thị **Mã QR & Mã số 6 ký tự tự động xoay vòng mỗi 5 phút** để chống điểm danh hộ từ xa.
   - Nông dân dùng điện thoại tại vườn bấm định vị GPS và nhập/quét mã điểm danh.
2. **Chấm điểm Thực hành theo Rubric 4 Tiêu chí (`/mod/fieldpractice/grade.php`):**
   - CBKT nhấn nút **"📋 Mở Bảng Chấm điểm Rubric (CBKT)"**.
   - Chấm điểm trực tiếp cho từng nông dân theo **4 tiêu chí chuẩn hóa (mỗi tiêu chí tối đa 25 điểm — tổng 100 điểm)**:
     1. **Chuẩn bị Vật tư & Bảo hộ Lao động (0–25đ):** Dụng cụ đo pH/EC đã hiệu chuẩn, đồ bảo hộ đúng chuẩn an toàn.
     2. **Thao tác Kỹ thuật Đúng Quy trình (0–25đ):** Pha dinh dưỡng, tỉa cành, vận hành van châm phân hoặc sơ chế đúng thông số.
     3. **Tuân thủ An toàn Sinh học & VietGAP (0–25đ):** Vệ sinh khử khuẩn, phân loại phụ phẩm, tuân thủ thời gian cách ly PHI.
     4. **Chất lượng Thành phẩm & Ghi chép Nhật ký (0–25đ):** Kết quả đạt yêu cầu thực tế và cập nhật đầy đủ nhật ký lô sản xuất.
   - Nhập nhận xét chuyên môn và nhấn **"💾 Lưu Điểm Rubric -> Gradebook"**.
   - Điểm tổng kết và nhận xét được **đẩy tức thì vào Sổ điểm Moodle Gradebook (`mdl_grade_grades`)**, đồng thời tự động đánh dấu **Hoàn thành Hoạt động** cho nông dân!
