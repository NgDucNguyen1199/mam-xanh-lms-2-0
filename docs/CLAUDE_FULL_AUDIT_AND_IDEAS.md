# Hồ sơ đánh giá độc lập: Mầm Xanh LMS 2.0 (QA Lead + Chủ tịch Hội đồng)

Ngày kiểm tra: 15/11/2026. Chưa sửa mã nguồn.

**Phương pháp:**
- Dùng Puppeteer (Chrome headless, 1366x768) đăng nhập 3 vai: khách, học viên `nd_rau_lamdong`, cán bộ kỹ thuật `cbkt_pilot`.
- Duyệt 64 URL. Với mỗi URL, ghi lại: console error, HTTP >= 400, request fail, Warning/Notice của PHP, emoji, cuộn ngang, màu inline cũ, chuỗi tiếng Việt khi chọn `?lang=en`.
- Đối chiếu số liệu bằng truy vấn trực tiếp DB MariaDB.
- Dữ liệu thô: `scratch/crawl.json` trong thư mục artifact.

**Giới hạn:** chưa kiểm tra bằng tài khoản admin. Chưa chấm nội dung câu hỏi quiz. Chưa chụp ảnh từng trang để đánh giá thẩm mỹ bằng mắt (chỉ dò bằng DOM/CSS).

---

## PHẦN 1. LỖI NGẦM VÀ ĐIỂM YẾU

### 1.1 Độ đồng đều giữa các khóa học (rủi ro cao nhất khi bảo vệ)

Số liệu từ DB:

| id | Khóa | Tổng mod | PDF | Page | Điểm danh | Bài tập | Quiz | H5P | SCORM | Trạng thái |
|---|---|---|---|---|---|---|---|---|---|---|
| 2 | CHAMSOC01 | 23 | 5 | 4 | 2 | 6 | 1 | 2 | 1 | Chuẩn |
| 3 | CHEBIEN01 | 15 | 1 | 3 | 2 | 4 | 1 | 1 | 1 | Khá |
| 8 | MASTER-AGRITECH-TPL | 14 | 1 | 2 | 2 | 4 | 1 | 1 | 1 | Khá, là mẫu |
| 19 | DAUTAY-CNC-01 | 10 | 1 | 2 | 1 | 2 | 1 | 1 | 1 | Khá, thiếu bài tập |
| 21 | DAUTAY-DONDUONG-01 | 10 | 1 | 2 | 1 | 2 | 1 | 1 | 1 | Bản sao của 19 |
| 4 | IOTNHAMANG01 | 6 | **0** | 3 | **0** | **0** | 1 | **0** | **0** | **Khung rỗng** |
| 5 | THUYCANH01 | 6 | 0 | 3 | 0 | 0 | 1 | 0 | 0 | Khung rỗng |
| 6 | SAURIENG01 | 6 | 0 | 3 | 0 | 0 | 1 | 0 | 0 | Khung rỗng |
| 7 | PHANVISINH01 | 6 | 0 | 3 | 0 | 0 | 1 | 0 | 0 | Khung rỗng |
| 10-17 | TAP-HUAN-NONG-DAN-DON-xxxx (8 khóa) | 8 | 1 | 2 | **0** | **0** | 1 | 1 | 1 | Rác do thử nhân bản |
| 18 | CH123 "CHAMHOC123" | 3 | 1 | 0 | 0 | 0 | 0 | 0 | 0 | Rác kiểm thử |

Chi tiết:
- **B1. Bốn khóa phụ (id 4-7) chỉ là khung giống nhau.**
  - Mỗi khóa có 3 trang HTML, 1 diễn đàn thông báo, 1 quiz và 1 `fieldpractice`.
  - Trang "Slide Tương tác (7 Slides): Quy trình Kỹ thuật XXX" và "Thẻ lật Flashcards Sâu bệnh IPM" dùng chung một mẫu. Tên khóa chỉ được thay ở cuối tiêu đề.
  - Khóa Thủy canh, Sầu riêng, Phân vi sinh đều có cùng trang "Flashcards Sâu bệnh IPM". Nội dung này không hợp với phân vi sinh. Hội đồng bấm vào là thấy ngay.
  - Không có PDF, điểm danh, bài tập có hạn nộp, H5P hay SCORM.
  - Các khóa này không có hạn nộp nên không lên Lịch/Sự kiện sắp tới.
- **B2. Thiếu hạn nộp và Điểm danh ở khóa 19/21.**
  - Chỉ có 2 bài tập, 1 điểm danh (6 và 5 phiên). Khóa 2 có 6 bài tập, 2 điểm danh (8 phiên).
  - Khóa 19/21 chưa có cấu trúc "Điểm danh Lý thuyết / Thực hành" tách riêng như khóa 2.
- **B3. Dữ liệu rác lộ ra ở danh mục và dashboard.**
  - 8 khóa "Tập huấn Nông dân Đơn Dương - Lâm Đồng Đợt 2" trùng tên hoàn toàn, thêm `CH123`.
  - Tất cả có `visible=1`.
  - Bảng điều khiển và `/course/index.php` của học viên hiện cả khóa rác. Học viên `nd_rau_lamdong` đang bị ghi danh vào các bản nhân bản này.
- **B4. Hạn nộp và Điểm danh của khóa mẫu `MASTER-AGRITECH-TPL` (id 8) đang hiển thị công khai.** Khóa mẫu nên ẩn với học viên.
- **B5. Emoji và màu cũ.**
  - Văn bản hiển thị không có emoji (quét DOM sạch). Không quét được nội dung DB bằng regex vì MariaDB regex không an toàn với UTF-8 đa byte.
  - Còn màu inline off-palette (`#15803D`, `#DCFCE7`, `#2E7D32`, `#1B4332`...) ở nhiều trang:

| Trang | Số lần |
|---|---|
| `/mod/page/view.php?id=3` (slide) | 30 |
| `/local/agriops/index.php` | 15 |
| `/local/mamxanhapp/manage.php` | 13 |
| `/mod/fieldpractice/view.php` | 8-9 |
| `/local/mamxanhcert/verify.php` | 5 |
| `/mod/page/view.php?id=2` | 2 |

  - Cụ thể: nhãn xanh lá kiểu "BÀI GIẢNG THỰC HÀNH HIỆN TRƯỜNG • CHUẨN HD 1080P" (`#15803D` / `#DCFCE7`) lệch với `#234E3E` / `#EAF2EE`.
  - Chữ "CHUẨN HD 1080P" là tuyên bố kỹ thuật khó chứng minh. Video mẫu chỉ dài 0:24 nhưng ghi "Bài giảng". Nên bỏ hoặc đổi cho đúng.

### 1.2 Console, Network, Broken link, PHP Warning

Không có PHP Warning/Notice/Fatal trên toàn bộ 64 trang. Đã kiểm tra thêm tail `error.log` của Apache, không thấy lỗi PHP.

Phát hiện:
- **N1. 404 `favicon`.** `/theme/image.php/agritrain_child/theme/<rev>/favicon` trả 404 ở trang chủ. Ảnh hưởng: mỗi lần mở là một lỗi console đỏ khi bật DevTools trước Hội đồng. Cần cấu hình favicon cho theme.
- **N2. 404 ảnh huy hiệu.** `/pluginfile.php/{16,22,28,34}/badges/badgeimage/{1..4}/f3` trả 404 ở `/user/profile.php`.
  - Có 16 huy hiệu (`mdl_badge`) nhưng ảnh bị thiếu.
  - Nếu có mục "Huy hiệu" trên hồ sơ, sẽ hiện ảnh vỡ.
- **N3. Video và PDF báo `net::ERR_ABORTED`.** Đây là Chrome hủy tải theo kiểu range request, không phải lỗi thật. Không phải lỗi trừ khi trình duyệt khác không phát.
- Các trang H5P, SCORM, Quiz, Assign, Attendance, Calendar, Message, Gradebook, Chứng nhận, Hồ sơ, Máy tính, Quản lý, Nhân bản: không có 4xx/5xx.
- **N4. Trang lịch `/calendar/view.php?view=month`** không có tiêu đề h1/h2. Chưa kiểm tra bằng mắt. Cần mở xem có tiêu đề tháng hay không.
- **N5. Cuộn ngang:** không trang nào cuộn ngang ở 1366px. Chưa kiểm tra mobile 375px trong đợt này.

### 1.3 Dịch thuật VI ⇄ EN

Kết quả quét `?lang=en` cho thấy rất nhiều dòng tiếng Việt. Phần lớn là **dữ liệu** (tên khóa, tên bài), không phải giao diện. Vẫn cần tách:

- **L1. Dữ liệu nội dung chỉ có một ngôn ngữ.**
  - Tên khóa, tên hoạt động, mô tả, tên tỉnh/huyện ("Lâm Đồng (5 farms • 21,500 m²)") không dịch.
  - Kết quả: trang EN có 50-100 dòng tiếng Việt.
  - Đề xuất: dùng filter đa ngôn ngữ `<span lang="vi" class="multilang">` cho tên khóa và tiêu đề section chính. Hoặc chấp nhận và nêu rõ "nội dung đào tạo bằng tiếng Việt" trong thuyết minh.
- **L2. Chuỗi giao diện pha trộn khi EN:**
  - `/course/view.php?id=2&lang=en` còn "← Quay lại Bảng điều khiển".
  - Trang `/my/?lang=en` còn tên khóa nhân bản.
  - Navbar vẫn "Mầm Xanh LMS 2.0" (thương hiệu, chấp nhận được).
  - Trang EN của `manage.php` dịch tiêu đề ("Agricultural Training Analytics & Grading...") nhưng bên trong vẫn tiếng Việt (tên tỉnh, "farms • m²" pha trộn).
- **L3. Hiển thị `&amp;` kép.** Trang nhân bản `clone_course.php?lang=en` hiển thị `&amp;` trong tên khóa ("Dinh dưỡng Tự động &amp; ..."), do mã hóa HTML hai lần. Tương tự lỗi `&amp;` ở thông báo đã sửa trước đó. Cần `s()`/`format_string` đúng chỗ.
- **L4. Dòng "Trang chủ khách" `?lang=en`**: bài báo, thẻ khóa học còn tiếng Việt (7 dòng). Chấp nhận nếu nêu rõ.
- Chưa phát hiện chữ tràn khung hay lệch cột bằng DOM scan (không có phần tử lá nào tràn). Chưa kiểm tra bảng Điểm danh/Gradebook bằng mắt.

### 1.4 Các điểm Hội đồng dễ bắt lỗi (xếp theo mức nguy hiểm)

1. Bấm khóa Thủy canh hoặc Sầu riêng: chỉ thấy 3 trang, không có PDF, điểm danh, bài tập. Nguy cơ đứt mạch trình bày.
2. Danh mục khóa học có 8 bản trùng tên và `CH123`.
3. Màu xanh lá cũ (`#15803D` / `#2E7D32`) lẫn màu `#234E3E` ở trang slide, VietGAP, Quản lý, Chứng nhận.
4. Favicon 404 và ảnh huy hiệu 404 khi mở DevTools.
5. Chuyển sang EN: nửa Anh nửa Việt.
6. Cùng một trang Flashcards sâu bệnh xuất hiện ở khóa không liên quan.

---

## PHẦN 2. 5 Ý TƯỞNG CẢI THIỆN (A+)

Nguyên tắc: tái dùng Moodle gốc (Gradebook, Attendance, Rubric). Không thêm widget trang trí.

1. **Bảng điểm học tập tổng hợp (Academic Transcript).**
   - Một trang `transcript.php` cho mỗi học viên và mỗi khóa. Cột: % chuyên cần (điểm danh), điểm thảo luận, H5P/SCORM, trắc nghiệm, rubric bài thu hoạch, tổng kết có trọng số, xếp loại.
   - Gradebook đã có 1 category (`aggregation=13`, natural). Thiết lập trọng số, rồi hiển thị qua `grade_get_course_grades`.
   - Có nút "In / Lưu PDF" bằng CSS `@media print` đơn giản.
2. **Phiếu nhận xét giảng viên và Rubric in được.**
   - Trang `feedback_sheet.php?courseid=&userid=` gồm: thông tin học viên, bảng rubric đã chấm, nhận xét, chữ ký.
   - Dùng `@media print`. Không cần thư viện PDF.
3. **Đồng bộ 100% các khóa phụ.**
   - Sinh cho khóa 4-7 nội dung đặc thù thật: 1 PDF tham khảo, 2 bài tập có hạn nộp, 2 điểm danh (lý thuyết/thực hành), 1 H5P, 1 SCORM, quiz tối thiểu 10 câu, bài thu hoạch có rubric.
   - Bỏ trang Flashcards sâu bệnh khỏi khóa không liên quan.
   - Dọn các khóa rác, ẩn khóa mẫu.
4. **Liên kết khóa học và Nhật ký VietGAP.**
   - Bài tập thực hành cho phép "Đính kèm trích xuất nhật ký VietGAP" từ `local_agriops` (lô, ngày, thao tác) vào bài nộp.
   - Cách làm gọn: nút "Xuất nhật ký lô X ra tệp" tạo CSV/HTML, học viên nộp như tệp thường. Cán bộ chấm trong rubric.
   - Hiển thị tiến độ nhật ký trong trang khóa.
5. **Đạt chứng nhận khi đủ điều kiện (Course Completion) nối với chứng nhận QR.**
   - Hiện `mdl_course_completion_criteria` chưa được xác nhận có thiết lập (truy vấn bị ngắt, cần kiểm lại).
   - Đề xuất: điều kiện hoàn thành = điểm danh >= 80% + quiz >= 5/10 + bài thu hoạch được chấm. Khi đủ thì mới cấp `MXCERT-...`.
   - Hội đồng sẽ thấy chuỗi nghiệp vụ liền mạch: học, điểm danh, kiểm tra, hoàn thành, chứng nhận có QR.

---

## PHẦN 3. CHECKLIST KỸ THUẬT CHO ARGON 4.0

### P0. Lỗi hiển thị và dữ liệu

| # | Việc | File / bảng | Ghi chú |
|---|---|---|---|
| P0-1 | Ẩn hoặc xóa khóa rác id 10-17 và 18 | `mdl_course.visible=0` hoặc `admin/cli` xóa khóa | Giữ id 8 để làm mẫu nhưng ẩn với học viên |
| P0-2 | Hủy ghi danh `nd_rau_lamdong` khỏi bản trùng | `mdl_user_enrolments`, `mdl_enrol` | Chỉ giữ khóa 2, 19, 21 |
| P0-3 | Favicon 404 | `moodle_custom/theme/agritrain_child/lib.php` hoặc `pix/favicon.ico` | Cấu hình đúng đường dẫn favicon cho theme |
| P0-4 | Ảnh huy hiệu 404 | `mdl_badge` và filearea `badges/badgeimage` | Tạo lại ảnh huy hiệu từ PNG nội bộ, hoặc ẩn khối huy hiệu |
| P0-5 | `&amp;` kép trên clone page | `local/mamxanhapp/clone_course.php` | Dùng `s($course->fullname)` thay vì escape lần hai |
| P0-6 | Gỡ màu cũ khỏi nội dung page | `mdl_page.content` trang id 2, 3, 40... | Thay `#15803D`/`#DCFCE7`/`#2E7D32`/`#1B4332` bằng `#234E3E`/`#EAF2EE`. Bỏ chữ "CHUẨN HD 1080P" |
| P0-7 | Tách màu inline sang lớp CSS | `local/agriops/index.php`, `local/mamxanhapp/manage.php`, `local/mamxanhcert/verify.php`, `mod/fieldpractice/view.php` | Thay inline style bằng biến `--mx-*` trong `post.scss` |
| P0-8 | Flashcards sai chủ đề | `mdl_page` ở khóa 4-7 | Xóa hoặc viết lại theo đúng chuyên đề |

### P1. Đồng bộ nội dung

| # | Việc | File / bảng |
|---|---|---|
| P1-1 | Dựng khóa 4-7 đủ PDF, điểm danh lý thuyết/thực hành, 2 bài tập có hạn nộp, H5P, SCORM, quiz >= 10 câu, bài thu hoạch có rubric | Script CLI mới `scripts/seed_secondary_courses.php` (tái dùng script seed của khóa 2) |
| P1-2 | Bổ sung bài tập có hạn và Điểm danh Lý thuyết/Thực hành cho khóa 19 và 21 | `mdl_assign`, `mdl_attendance`, `mdl_attendance_sessions` |
| P1-3 | Kiểm tra hạn nộp xuất hiện ở "Sự kiện sắp tới" | `mdl_event` |
| P1-4 | Dịch UI còn lẫn Việt/Anh | `langlocal/vi_local/*.php`, `lang/en_local`, `drawers.mustache` ("← Quay lại Bảng điều khiển") |
| P1-5 | Bảng thống kê nông hộ tách "farms / m²" | `local/mamxanhapp/manage.php` + `lang/en/local_mamxanhapp.php` |

### P2. Tính năng A+

| # | Tính năng | File mới | Bảng / hàm |
|---|---|---|---|
| P2-1 | Bảng điểm tổng hợp + in | `local/mamxanhapp/transcript.php`, SCSS `@media print` | Dùng `grade_get_course_grades()`, `mod_attendance` summary. Thiết lập trọng số category (`mdl_grade_items.aggregationcoef`) |
| P2-2 | Phiếu nhận xét + Rubric in | `local/mamxanhapp/feedback_sheet.php` | Đọc `mdl_gradingform_rubric_*`, `mdl_assign_grades` |
| P2-3 | Xuất nhật ký VietGAP thành tệp nộp bài | `local/agriops/export_log.php`, nút ở trang assign | Truy vấn bảng nhật ký của `local_agriops` (cần xác nhận tên bảng, `mdl_local_agriops_lots` không tồn tại) |
| P2-4 | Hoàn thành khóa nối chứng nhận QR | `local/mamxanhcert/lib.php` (hook `course_completed`) | `mdl_course_completion_criteria`, `mdl_course_completions` |
| P2-5 | Kiểm tra cuối: Puppeteer crawl 64 URL không lỗi | `tests/e2e/verify_full_crawl.mjs` | Chuyển từ `_tmp_audit.mjs` |

**Điều kiện nghiệm thu:**
- Crawl không còn 404 và PHP Warning.
- Không còn màu inline cũ trong DOM.
- Khóa 2-7, 19, 21: mỗi khóa đều có PDF, điểm danh, bài tập có hạn nộp, quiz.
- `?lang=en` trên giao diện (không tính dữ liệu) không còn chữ Việt.
