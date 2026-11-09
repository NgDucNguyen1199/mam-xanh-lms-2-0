# TÀI LIỆU HƯỚNG DẪN THỰC HÀNH TẠO KHÓA HỌC MOODLE TỪ CON SỐ 0
## (HANDS-ON COURSE CREATION PLAYBOOK & DEFENSE DEMO SCRIPT)

- **Hệ thống áp dụng:** Mầm Xanh LMS 2.0 (Nền tảng Moodle 4.5+ chuẩn LMS Đại học Đà Lạt)
- **Địa chỉ truy cập:** `http://localhost:8090`
- **Tài khoản thực hành:** `admin` (hoặc `cbkt_pilot`)
- **Bộ học liệu thực hành:** Thư mục `DEMO_COURSE_KIT/` (ngay trong thư mục gốc dự án)
- **Chủ đề khóa học thực hành:** **Kỹ thuật Canh tác Dâu tây & Hoa Cúc Công nghệ cao trong Nhà màng Đà Lạt**
- **Mã khóa học viết tắt:** `DAUTAY-CNC-01`
- **Mục đích:** Hướng dẫn từng bước thao tác bấm chuột trực tiếp trên giao diện web để giảng viên/sinh viên tự tay xây dựng một khóa học chuẩn học thuật từ con số 0 trong 10 đến 15 phút, chứng minh đầy đủ 100% tính năng trước Hội đồng bảo vệ đồ án.

---

## MỤC LỤC 10 BƯỚC THỰC HÀNH LIÊN HOÀN

1. [Bước 1: Khởi tạo Khóa học mới & Cấu hình Định dạng Học thuật](#bước-1-khởi-tạo-khóa-học-mới--cấu-hình-định-dạng-học-thuật)
2. [Bước 2: Thiết lập Mục Thông tin chung & Nhúng Tài liệu PDF xem trực tiếp](#bước-2-thiết-lập-mục-thông-tin-chung--nhúng-tài-liệu-pdf-xem-trực-tiếp)
3. [Bước 3: Tạo 4 Chuyên đề Cốt lõi & Đưa Video Thực tế vào Bài giảng](#bước-3-tạo-4-chuyên-đề-cốt-lõi--đưa-video-thực-tế-vào-bài-giảng)
4. [Bước 4: Tạo Hoạt động Tương tác H5P & Gói Chuẩn SCORM 1.2](#bước-4-tạo-hoạt-động-tương-tác-h5p--gói-chuẩn-scorm-12)
5. [Bước 5: Tạo Mục "Kiểm tra & Đánh giá" với Điểm danh Lý thuyết & Thực hành](#bước-5-tạo-mục-kiểm-tra--đánh-giá-với-điểm-danh-lý-thuyết--thực-hành)
6. [Bước 6: Tạo Phiên Thảo luận & Bài tập Nộp file Đồng bộ sang Calendar](#bước-6-tạo-phiên-thảo-luận--bài-tập-nộp-file-đồng-bộ-sang-calendar)
7. [Bước 7: Tạo Bài thu hoạch Cuối khóa Chấm bằng Rubric 4 Tiêu chí](#bước-7-tạo-bài-thu-hoạch-cuối-khóa-chấm-bằng-rubric-4-tiêu-chí)
8. [Bước 8: Tạo Bài kiểm tra Trắc nghiệm & Import Ngân hàng Câu hỏi 1-Chạm](#bước-8-tạo-bài-kiểm-tra-trắc-nghiệm--import-ngân-hàng-câu-hỏi-1-chạm)
9. [Bước 9: Thiết lập Điều kiện Tiên quyết & Cấp Chứng nhận Số QR](#bước-9-thiết-lập-điều-kiện-tiên-quyết--cấp-chứng-nhận-số-qr)
10. [Bước 10: Demo Nhân bản Khóa học (1-Click Clone) & Đóng vai Học viên](#bước-10-demo-nhân-bản-khóa-học-1-click-clone--đóng-vai-học-viên)

---

## CHUẨN BỊ TRƯỚC KHI BẮT ĐẦU

Hãy mở sẵn thư mục `DEMO_COURSE_KIT/` trên máy tính song song với cửa sổ trình duyệt:
- `01_De_Cuong_Va_Noi_Dung_Copy_Paste.txt`: Chứa toàn bộ văn bản để Copy & Paste.
- `02_Tai_Lieu_Tham_Khao_VietGAP_Thuc_Te.pdf`: File PDF VietGAP chính thống của Bộ NN&PTNT.
- `03_Video_Thuc_Te_Nha_Mang.mp4` & `.vtt`: Video quay thực tế nhà màng công nghệ cao kèm phụ đề.
- `04_Hoc_Lieu_Tuong_Tac_Chuan.h5p`: Gói câu hỏi tương tác H5P chuẩn quốc tế.
- `05_Bai_Giang_VietGAP_SCORM12.zip`: Gói bài giảng chuẩn e-Learning ADL SCORM 1.2.
- `06_Ngan_Hang_10_Cau_Hoi_Trac_Nghiem_MoodleXML.xml`: Ngân hàng 10 câu hỏi trắc nghiệm định dạng Moodle XML.

---

### BƯỚC 1: KHỞI TẠO KHÓA HỌC MỚI & CẤU HÌNH ĐỊNH DẠNG HỌC THUẬT

**Mục tiêu:** Tạo một vỏ khóa học mới với cấu hình ngày tháng, theo dõi tiến độ hoàn thành và giao diện theo phong cách LMS trường Đại học Đà Lạt.

1. **Đăng nhập hệ thống:**
   - Truy cập: `http://localhost:8090/login/index.php?lang=vi`.
   - Bấm nút đăng nhập nhanh **"Quản trị viên (Admin)"** hoặc nhập tài khoản `admin` / mật khẩu `MamXanh@2026!`.
2. **Truy cập trang tạo khóa học:**
   - Trên thanh điều hướng Navbar trên cùng, bấm **Quản trị khu vực (Site administration)**.
   - Chọn thẻ **Các khoá học (Courses)** -> Bấm **Quản lý các khoá học và thể loại (Manage courses and categories)**.
   - *(Hoặc truy cập trực tiếp đường dẫn nhanh: `http://localhost:8090/course/edit.php?category=1`)*.
   - Bấm nút **Tạo khóa học mới (Create new course)**.
3. **Điền thông tin khóa học (Mở file `01_...txt` để Copy & Paste):**
   - **Tên đầy đủ của khoá học (Course full name):**
     `Kỹ thuật Canh tác Dâu tây & Hoa Cúc Công nghệ cao trong Nhà màng Đà Lạt`
   - **Tên viết tắt của khoá học (Course short name):**
     `DAUTAY-CNC-01`
   - **Danh mục khoá học (Course category):** Chọn danh mục bất kỳ (ví dụ: `Danh mục 1` hoặc `Trồng trọt CNC`).
   - **Hiển thị khoá học (Course visibility):** Chọn `Hiện (Show)`.
   - **Ngày bắt đầu khoá học (Course start date):** Chọn ngày hiện tại hoặc `02 Tháng 11 2026`.
   - **Tóm tắt khoá học (Course summary):** Dán đoạn tóm tắt từ file `01_...txt`:
     > *Khóa học cung cấp kiến thức thực tiễn và quy chuẩn kỹ thuật sản xuất nông nghiệp công nghệ cao đối với hai cây trồng chủ lực của thành phố Đà Lạt (tỉnh Lâm Đồng): Dâu tây và Hoa cúc. Nội dung bao gồm quản lý vi khí hậu nhà màng, công nghệ tưới nhỏ giọt bù áp, quản lý dinh dưỡng khoáng tự động theo giai đoạn sinh trưởng, quản lý dịch hại tổng hợp (IPM) bằng giải pháp sinh học, truy xuất nguồn gốc số và thực hành quy trình nông nghiệp tốt VietGAP (TCVN 11892-1:2017).*
4. **Cấu hình quan trọng bắt buộc (Hiển thị ngày & Theo dõi hoàn thành):**
   - Mở mục **Định dạng khoá học (Course format)**:
     + Định dạng (Format): Chọn **Định dạng theo chủ đề (Topics format)**.
     + Các phần ẩn (Hidden sections): Chọn *Hidden sections are completely invisible*.
   - Mở mục **Giao diện (Appearance)**:
     + **Hiển thị ngày diễn ra hoạt động (Show activity dates):** Chọn **Có (Yes)** *(Điểm cốt lõi giúp hiện nhãn "Đã mở / Hạn nộp" như `lms.dlu.edu.vn`)*.
     + Hiển thị báo cáo hoạt động (Show activity reports): Chọn *Có (Yes)*.
   - Mở mục **Theo dõi hoàn tất (Completion tracking)**:
     + **Kích hoạt tính năng theo dõi tiến trình (Enable completion tracking):** Chọn **Có (Yes)**.
5. **Lưu khóa học:**
   - Cuộn xuống cuối trang, bấm nút **Lưu và hiển thị (Save and display)**.
   - Khóa học mới được tạo ra và màn hình chuyển ngay đến giao diện chính của khóa học `DAUTAY-CNC-01`.

---

### BƯỚC 2: THIẾT LẬP MỤC THÔNG TIN CHUNG & NHÚNG TÀI LIỆU PDF XEM TRỰC TIẾP

**Mục tiêu:** Cung cấp đề cương và sổ tay kỹ thuật chuẩn VietGAP dưới dạng nhúng PDF (`Embed`) xem ngay trên trình duyệt mà không ép người dùng phải tải file về máy.

1. **Bật chế độ chỉnh sửa:**
   - Nhìn lên góc trên bên phải trang web, gạt công tắc **Chế độ chỉnh sửa (Edit mode)** sang màu xanh (Bật).
2. **Đổi tên mục đầu tiên (Section 0):**
   - Tại dòng tiêu đề đầu tiên, bấm vào biểu tượng chiếc bút chì (hoặc bấm dấu 3 chấm `...` -> Chọn *Chỉnh sửa mục*).
   - Đặt tên: `Thông tin chung & Tài liệu tham khảo` -> Nhấn phím `Enter` để lưu.
3. **Thêm tài liệu PDF nhúng trực tiếp (mod_resource):**
   - Bấm vào nút **+ Thêm hoạt động hoặc tài nguyên (Add an activity or resource)** trong mục này.
   - Chọn tab **Tài nguyên (Resources)** -> Chọn mục **Tập tin (File)**.
   - **Tên (Name):**
     `Sổ tay Hướng dẫn Kỹ thuật Canh tác Nông nghiệp CNC & VietGAP (Xem Trực tiếp PDF)`
   - **Mô tả (Description):**
     `Sổ tay hướng dẫn thực hành quy trình kỹ thuật canh tác công nghệ cao và tiêu chuẩn VietGAP Trồng trọt do Cục Trồng trọt và Trung tâm Khuyến nông Quốc gia ban hành. Học viên xem trực tiếp tài liệu trên giao diện web.`
   - **Tải lên tập tin (Select files):**
     + Mở thư mục `DEMO_COURSE_KIT/`, kéo file `02_Tai_Lieu_Tham_Khao_VietGAP_Thuc_Te.pdf` và thả thẳng vào ô nét đứt có mũi tên xanh.
   - **Cấu hình xem trực tiếp không tải về (Appearance):**
     + Mở rộng mục **Giao diện (Appearance)**.
     + Tại dòng **Hiển thị (Display)**: Bấm chọn menu xổ xuống và chọn **Nhúng (Embed)**.
     + Tích chọn *Hiển thị kích thước* và *Hiển thị ngày tải lên/sửa đổi*.
   - **Cấu hình Hoàn tất hoạt động (Activity completion):**
     + Mở mục *Theo dõi hoàn tất*.
     + Chọn: *Hiển thị hoạt động là hoàn thành khi đáp ứng các điều kiện*.
     + Tích chọn: **Học viên phải xem hoạt động này để hoàn thành**.
   - Bấm **Lưu và trở về khoá học (Save and return to course)**.
4. **Kiểm tra kết quả:** Bấm thử vào tài liệu vừa tạo -> Màn hình trình diễn ngay khung đọc PDF trực tiếp với thanh công cụ thu phóng, cuộn trang mượt mà không xuất hiện hộp thoại download.

---

### BƯỚC 3: TẠO 4 CHUYÊN ĐỀ CỐT LÕI & ĐƯA VIDEO THỰC TẾ VÀO BÀI GIẢNG

**Mục tiêu:** Cấu trúc khóa học thành 4 chuyên đề chuẩn nông nghiệp công nghệ cao và nhúng video quay thực tế tại nhà màng kèm phụ đề tiếng Việt.

1. **Đổi tên 4 Chuyên đề:**
   - Tại Mục 1, bấm biểu tượng bút chì, dán:
     `Chuyên đề 1: Kỹ thuật Chăm sóc Cây trồng & Quản lý Vi khí hậu Nhà màng` (Nhấn Enter).
   - Tại Mục 2, bấm biểu tượng bút chì, dán:
     `Chuyên đề 2: Kỹ thuật Tưới tiêu & Vận hành Hệ thống Tưới nhỏ giọt Bù áp` (Nhấn Enter).
   - Tại Mục 3, bấm biểu tượng bút chì, dán:
     `Chuyên đề 3: Quản lý Dinh dưỡng Khoáng & Châm phân Tự động (Fertigation)` (Nhấn Enter).
   - Tại Mục 4, bấm biểu tượng bút chì, dán:
     `Chuyên đề 4: Quản lý Dịch hại Tổng hợp (IPM) & Kiểm soát Sinh học` (Nhấn Enter).
   *(Ghi chú: Nếu khóa học chưa đủ 4 mục, bấm nút **+ Thêm chủ đề (Add topics)** ở cuối trang để tạo thêm).*
2. **Tạo bài giảng Video thực tế tại Chuyên đề 1:**
   - Trong khung Chuyên đề 1, bấm **+ Thêm hoạt động hoặc tài nguyên** -> Chọn **Trang (Page)**.
   - **Tên (Name):**
     `Video Thực tế & Infographic: Tiêu chuẩn Cây giống Khỏe & Điều tiết Vi khí hậu Nhà màng`
   - **Mô tả (Description):**
     `Video hướng dẫn thực nghiệm tại nhà màng Đà Lạt: Kỹ thuật kiểm soát nhiệt ẩm, vận hành quạt thông gió đỉnh mái và chỉ tiêu cây giống dâu tây, hoa cúc đạt chuẩn xuất vườn.`
   - **Nội dung trang (Page content):**
     + Trong khung soạn thảo Atto/TinyMCE, bấm vào biểu tượng **Chèn hoặc sửa tệp âm thanh/video (Insert or edit an audio/video file)**.
     + Chuyển sang thẻ **Video** -> Bấm nút *Tìm duyệt các kho lưu trữ (Browse repositories)* -> Chọn *Tải tệp tin lên (Upload a file)*.
     + Chọn file `DEMO_COURSE_KIT/03_Video_Thuc_Te_Nha_Mang.mp4` -> Bấm *Tải tệp tin này lên*.
     + Chuyển sang thẻ **Phụ đề (Subtitles and captions)** -> Bấm *Tìm duyệt các kho lưu trữ* -> Chọn file phụ đề `DEMO_COURSE_KIT/03_Video_Thuc_Te_Nha_Mang.vtt` -> Chọn ngôn ngữ `Tiếng Việt` -> Bấm *Tải tệp tin này lên*.
     + Bấm nút **Chèn tập tin đa phương tiện (Insert media)**.
     + Bên dưới video, có thể gõ thêm đoạn tóm tắt thông số kỹ thuật (Nhiệt độ ngày 18-24°C, đêm 12-16°C, độ ẩm 65-75%).
   - **Hoàn tất hoạt động:** Chọn *Học viên phải xem hoạt động này để hoàn thành*.
   - Bấm **Lưu và trở về khoá học**.

---

### BƯỚC 4: TẠO HOẠT ĐỘNG TƯƠNG TÁC H5P & GÓI CHUẨN SCORM 1.2

**Mục tiêu:** Tích hợp các định dạng học liệu tương tác tiên tiến theo chuẩn quốc tế, tự động đồng bộ điểm số vào Sổ điểm (Gradebook).

1. **Thêm hoạt động H5P trong Chuyên đề 2:**
   - Trong Chuyên đề 2, bấm **+ Thêm hoạt động hoặc tài nguyên** -> Chọn hoạt động **H5P** (`mod_h5pactivity`).
   - **Tên (Name):**
     `Bài tập Tương tác H5P: Chẩn đoán Dinh dưỡng pH/EC & Phác đồ IPM`
   - **Gói tập tin (Package file):**
     + Kéo thả file `DEMO_COURSE_KIT/04_Hoc_Lieu_Tuong_Tac_Chuan.h5p` vào ô tải file.
   - **Điểm số (Grade):**
     + Loại điểm: *Điểm (Point)*, Điểm tối đa: `100`.
     + **Điểm để qua (Grade to pass):** Nhập `70`.
   - **Theo dõi hoàn tất (Activity completion):**
     + Chọn: *Hiển thị hoạt động là hoàn thành khi đáp ứng các điều kiện*.
     + Tích chọn: **Phải nhận điểm** và **Phải đạt điểm qua (Receive a passing grade)**.
   - Bấm **Lưu và trở về khoá học**.
2. **Thêm gói bài giảng chuẩn SCORM 1.2 trong Chuyên đề 3:**
   - Trong Chuyên đề 3, bấm **+ Thêm hoạt động hoặc tài nguyên** -> Chọn **Gói SCORM (SCORM package)** (`mod_scorm`).
   - **Tên (Name):**
     `Bài giảng Chuẩn SCORM 1.2: Quy trình Thực hành Nông nghiệp Tốt VietGAP & Kiểm soát Mối nguy`
   - **Tập tin gói (Package file):**
     + Kéo thả file `DEMO_COURSE_KIT/05_Bai_Giang_VietGAP_SCORM12.zip` vào ô tải file.
   - **Điểm (Grade):**
     + Phương pháp chấm điểm (Grading method): *Điểm cao nhất (Highest grade)*.
     + Điểm tối đa: `100`.
   - **Theo dõi hoàn tất:** Chọn *Yêu cầu trạng thái đạt/hoàn thành (Passed/Completed)*.
   - Bấm **Lưu và trở về khoá học**.

---

### BƯỚC 5: TẠO MỤC "KIỂM TRA & ĐÁNH GIÁ" VỚI ĐIỂM DANH LÝ THUYẾT & THỰC HÀNH

**Mục tiêu:** Xây dựng mục đánh giá chuyên cần bài bản giống hệ thống `lms.dlu.edu.vn` với 2 hoạt động Điểm danh chuyên nghiệp (`mod_attendance`).

1. **Tạo Section "Kiểm tra & Đánh giá":**
   - Cuộn xuống dưới, bấm **+ Thêm chủ đề (Add topics)** -> Tạo 1 mục mới.
   - Bấm bút chì đổi tên thành: `Kiểm tra & Đánh giá`.
   *(Mẹo: Giữ chuột vào biểu tượng dấu thập mũi tên bên trái tiêu đề để kéo mục này lên vị trí đầu khóa học ngay dưới mục Thông tin chung).*
2. **Tạo hoạt động Điểm danh Lý thuyết:**
   - Bấm **+ Thêm hoạt động hoặc tài nguyên** trong mục Kiểm tra & Đánh giá -> Chọn **Attendance (Điểm danh)**.
   - **Tên (Name):** `Điểm danh Lý thuyết`.
   - **Điểm (Grade):** Điểm tối đa `100`, Điểm để qua `70`.
   - Bấm **Lưu và hiển thị (Save and display)**.
3. **Thêm các phiên điểm danh lặp lại hàng tuần (Add Sessions):**
   - Trên thanh tab của hoạt động Điểm danh, bấm vào tab **Thêm buổi học (Add session)**.
   - **Loại phiên (Session type):** Chọn *Tất cả học viên (All students)*.
   - **Ngày điểm danh (Date):** `02 Tháng 11 2026`.
   - **Thời gian (Time):** Từ `07:30` đến `09:30`.
   - **Mô tả (Description):** `Buổi 1: Tổng quan Vi khí hậu Nhà màng & Tiêu chuẩn Cây giống`.
   - **Cấu hình lặp lại nhiều phiên (Multiple sessions):**
     + Tích chọn ô vuông **Lặp lại buổi học như trên (Repeat the session above as follows)**.
     + Tích chọn thứ: **Thứ Hai (Monday)**.
     + Lặp lại mỗi (Repeat every): `1 tuần (week)`.
     + **Lặp lại đến ngày (Repeat until):** Chọn `30 Tháng 11 2026`.
   - Bấm nút **Thêm (Add)** ở cuối trang.
   - **Kết quả:** Hệ thống lập tức tự động sinh ra đủ **5 phiên điểm danh lý thuyết** trong suốt tháng 11/2026!
4. **Biểu diễn chấm điểm danh cho học viên (Take Attendance):**
   - Tại dòng của buổi học bất kỳ (ví dụ buổi ngày 2/11/2026), bấm vào biểu tượng **vòng tròn mũi tên xanh (Take attendance)**.
   - Màn hình hiển thị danh sách học viên với 4 trạng thái chuẩn:
     + **P (Present - Có mặt):** 2 điểm
     + **L (Late - Đi trễ):** 1 điểm
     + **E (Excused - Vắng có phép):** 1 điểm
     + **A (Absent - Vắng không phép):** 0 điểm
   - Bấm chọn **P** cho học viên -> Bấm **Lưu điểm danh (Save attendance)**.
5. **Tạo tiếp hoạt động Điểm danh Thực hành:**
   - Quay lại khóa học, tạo thêm hoạt động Attendance mang tên: `Điểm danh Thực hành tại Nhà màng`.
   - Vào *Add session*, chọn ngày `11 Tháng 11 2026`, thời gian từ `08:00` đến `11:30`, lặp lại vào **Thứ Tư hàng tuần** đến ngày `25 Tháng 11 2026` (sinh ra 3 buổi thực hành nhà màng).

---

### BƯỚC 6: TẠO PHIÊN THẢO LUẬN & BÀI TẬP NỘP FILE ĐỒNG BỘ SANG CALENDAR

**Mục tiêu:** Tạo bài tập thảo luận tại lớp có hiển thị tài liệu tham khảo, câu hỏi và hạn nộp trực tiếp trên mặt khóa học (giống ảnh mẫu `lms.dlu.edu.vn`) và tự động xuất hiện trên Lịch biểu cá nhân `/my/index.php`.

1. **Tạo hoạt động Thảo luận tại lớp buổi 1 (`mod_assign`):**
   - Trong mục *Kiểm tra & Đánh giá*, bấm **+ Thêm hoạt động hoặc tài nguyên** -> Chọn **Bài tập (Assignment)**.
   - **Tên bài tập (Assignment name):** `Thảo luận tại lớp buổi 1`.
   - **Mô tả hoạt động (Activity instructions / Description):** Dán đoạn nội dung từ file `01_...txt`:
     ```html
     <p>Tài liệu tham khảo chuyên đề vi khí hậu &amp; dinh dưỡng tưới:</p>
     <ul>
       <li><a href="https://cuctoan.gov.vn/quy-trinh-ky-thuat-vietgap-nha-mang-dalat" target="_blank">https://cuctoan.gov.vn/quy-trinh-ky-thuat-vietgap-nha-mang-dalat</a></li>
       <li><a href="https://faovietnam.org/sustainable-agritech-greenhouse-climate-control" target="_blank">https://faovietnam.org/sustainable-agritech-greenhouse-climate-control</a></li>
       <li><a href="https://lamdong-smartfarm.vn/giao-trinh-dinh-duong-tuoi-nho-giot-ec-ph" target="_blank">https://lamdong-smartfarm.vn/giao-trinh-dinh-duong-tuoi-nho-giot-ec-ph</a></li>
     </ul>
     <p><strong>Câu hỏi:</strong> Theo anh/chị yếu tố kỹ thuật nào là quan trọng nhất khi kiểm soát vi khí hậu và dinh dưỡng tưới nhỏ giọt trong nhà màng? Nêu giải pháp khắc phục khi ẩm độ không khí tăng cao trong mùa mưa Đà Lạt.</p>
     ```
   - **ĐIỀU KIỆN TIÊN QUYẾT BẮT BUỘC:** Tích chọn ô vuông **Hiển thị mô tả ở trang khoá học (Display description on course page)**.
   - **Thời gian nộp (Availability):**
     + Tích chọn **Cho phép nộp từ (Allow submissions from):** `09 Tháng 11 2026`, `07:30`.
     + Tích chọn **Hạn chót (Due date):** `13 Tháng 11 2026`, `23:30`.
   - **Kiểu nộp bài (Submission types):**
     + Tích chọn **Văn bản trực tuyến (Online text)** (cho phép gõ câu trả lời trực tiếp) và **Tập tin nộp (File submissions)**.
   - **Theo dõi hoàn tất (Activity completion):**
     + Chọn: *Hiển thị hoạt động là hoàn thành khi đáp ứng các điều kiện*.
     + Tích chọn: **Học viên phải nộp bài cho hoạt động này để hoàn thành**.
   - Bấm **Lưu và trở về khoá học**.
2. **Tạo tương tự cho Thảo luận tại lớp buổi 2:**
   - Tên: `Thảo luận tại lớp buổi 2`.
   - Dán nội dung câu hỏi về quang chu kỳ chiếu sáng hoa cúc từ file `01_...txt`.
   - Tích chọn *Hiển thị mô tả ở trang khóa học*.
   - Hạn chót (Due date): `20 Tháng 11 2026`, `23:30`.
   - Bấm *Lưu và trở về khóa học*.
3. **Chứng minh đồng bộ Lịch biểu tức thì trước Hội đồng:**
   - Mở một tab trình duyệt mới, gõ địa chỉ: `http://localhost:8090/my/index.php`.
   - Nhìn vào cột phải:
     + Trong khối **Lịch (Calendar)** tháng 11/2026: Ngày 13 và ngày 20 được khoanh tròn xanh nổi bật.
     + Trong khối **Sự kiện sắp tới (Upcoming events)**: Xuất hiện ngay dòng `Thảo luận tại lớp buổi 1 có hạn nộp - Thứ Sáu, 13 tháng 11, 11:30 PM`. Bấm vào link sự kiện, hệ thống chuyển ngay đến phiên nộp bài!

---

### BƯỚC 7: TẠO BÀI THU HOẠCH CUỐI KHÓA CHẤM BẰNG RUBRIC 4 TIÊU CHÍ

**Mục tiêu:** Thiết lập bài thi thực hành lớn chấm điểm theo bảng Rubric khoa học, minh bạch, chuẩn kiểm định chất lượng đào tạo đại học.

1. **Tạo hoạt động Bài thu hoạch (`mod_assign`):**
   - Trong mục *Kiểm tra & Đánh giá*, bấm **+ Thêm hoạt động hoặc tài nguyên** -> Chọn **Bài tập (Assignment)**.
   - **Tên bài tập:** `Bài thu hoạch cuối khóa: Đề án Chuyển đổi Canh tác Số & VietGAP`.
   - **Mô tả:** `Học viên xây dựng bản Đề án chuyển đổi mô hình nông hộ sang canh tác công nghệ cao chuẩn VietGAP cho diện tích từ 1.000 m2 trở lên. Bài làm nộp dưới dạng file PDF hoặc báo cáo Word kèm nhật ký sản xuất mẫu.`
   - Hạn nộp: `30 Tháng 11 2026, 23:30`.
2. **Cấu hình phương pháp chấm Rubric:**
   - Mở rộng mục **Điểm (Grade)**:
     + Loại điểm: *Điểm (Point)*, Điểm tối đa: `100`.
     + **Phương pháp chấm điểm (Grading method):** Bấm chọn menu xổ xuống và chọn **Rubric**.
     + Điểm để qua: `70`.
   - Bấm nút **Lưu và hiển thị (Save and display)**.
3. **Định nghĩa tiêu chí Rubric từ đầu (Define Rubric):**
   - Màn hình chuyển sang trang quản lý chấm điểm nâng cao, bấm nút **Định nghĩa biểu mẫu chấm điểm mới từ đầu (Define new grading form from scratch)**.
   - **Tên (Name):** `Rubric 4 Tiêu chí Đánh giá Đề án VietGAP CNC`.
   - Tại bảng Rubric bên dưới, lần lượt bấm vào từng dòng để thêm 4 tiêu chí (Mỗi tiêu chí có 3 mức điểm: 0đ, 15đ, 25đ):
     + **Tiêu chí 1:** `Khảo sát hiện trạng & Phân tích tính khả thi kỹ thuật` (Mức 1: 0đ - Chưa đạt; Mức 2: 15đ - Đạt cơ bản; Mức 3: 25đ - Xuất sắc, có xét nghiệm đất nước).
     + Bấm *+ Thêm tiêu chí (Add criterion)* -> **Tiêu chí 2:** `Thiết kế quy hoạch nhà màng & Hệ thống tưới nhỏ giọt` (0đ - 15đ - 25đ).
     + Bấm *+ Thêm tiêu chí* -> **Tiêu chí 3:** `Quy trình dinh dưỡng Fertigation & Phòng trừ sinh học IPM` (0đ - 15đ - 25đ).
     + Bấm *+ Thêm tiêu chí* -> **Tiêu chí 4:** `Hiệu quả kinh tế, Ghi nhật ký số & Đạt chuẩn VietGAP` (0đ - 15đ - 25đ).
   - Cuộn xuống dưới, bấm nút **Lưu rubric và làm cho nó sẵn sàng (Save rubric and make it ready)**.

---

### BƯỚC 8: TẠO BÀI KIỂM TRA TRẮC NGHIỆM & IMPORT NGÂN HÀNG CÂU HỎI 1-CHẠM

**Mục tiêu:** Tạo bài trắc nghiệm cuối khóa và biểu diễn kỹ năng sư phạm đỉnh cao bằng cách import toàn bộ ngân hàng 10 câu hỏi chuẩn Moodle XML trong đúng 5 giây trước mặt Hội đồng.

1. **Khởi tạo bài Quiz:**
   - Trong mục *Kiểm tra & Đánh giá*, bấm **+ Thêm hoạt động hoặc tài nguyên** -> Chọn **Bài trắc nghiệm (Quiz)**.
   - **Tên (Name):** `Kiểm tra Trắc nghiệm Cuối khóa (10 Câu hỏi) - DAUTAY-CNC-01`.
   - **Thời gian (Timing):**
     + Thời gian làm bài (Time limit): Bật *Kích hoạt* -> Nhập `15 phút (minutes)`.
   - **Điểm (Grade):**
     + Điểm để qua (Grade to pass): Nhập `7.0`.
     + Số lần làm bài cho phép: `2` (Phương pháp chấm: Điểm cao nhất).
   - Bấm **Lưu và hiển thị (Save and display)**.
2. **Nhập ngân hàng câu hỏi (Import Question Bank):**
   - Trên thanh menu phụ của Quiz, bấm vào tab **Ngân hàng câu hỏi (Question bank)**.
   - Bấm vào menu chọn ở góc trên bên trái của trang ngân hàng câu hỏi -> Chọn mục **Nhập (Import)**.
     *(Đường dẫn trực tiếp: `/question/bank/importquestions/import.php`)*.
   - **Định dạng tập tin (File format):** Bấm chọn ô tròn **Định dạng Moodle XML (Moodle XML format)**.
   - **Mục Nhập câu hỏi từ tệp tin (Import questions from file):**
     + Mở thư mục `DEMO_COURSE_KIT/`, kéo file `06_Ngan_Hang_10_Cau_Hoi_Trac_Nghiem_MoodleXML.xml` và thả vào khung tải file.
   - Bấm nút **Nhập (Import)** màu xanh ở cuối trang.
   - **Hiệu ứng ấn tượng:** Hệ thống hiển thị ngay một danh sách màu xanh lá gồm đủ 10 câu hỏi về dâu tây và hoa cúc Đà Lạt kèm thông báo *"10 câu hỏi đã được nhập từ tập tin"*.
   - Bấm nút **Tiếp tục (Continue)**.
3. **Thêm câu hỏi vào đề thi Quiz:**
   - Bấm vào tab **Câu hỏi (Questions)** của Quiz.
   - Bấm vào chữ **Thêm (Add)** ở bên phải -> Chọn **từ ngân hàng câu hỏi (from question bank)**.
   - Tích chọn ô vuông trên cùng để chọn toàn bộ **10 câu hỏi**.
   - Bấm nút **Thêm các câu hỏi đã chọn vào bài trắc nghiệm (Add selected questions to the quiz)**.
   - Đặt lại điểm tối đa là `10.0` -> Bấm nút **Lưu (Save)**.
   - Bấm nút **Xem trước bài trắc nghiệm ngay bây giờ (Preview quiz now)** để chứng minh đề thi chạy mượt mà từng câu với tính năng xáo trộn đáp án tự động!

---

### BƯỚC 9: THIẾT LẬP ĐIỀU KIỆN TIÊN QUYẾT & CẤP CHỨNG NHẬN SỐ QR

**Mục tiêu:** Ràng buộc lộ trình học tập tuần tự (phải hoàn thành chuyên đề trước mới được thi cuối khóa; đạt điểm thi mới được nhận Chứng nhận số QR).

1. **Cài đặt điều kiện tiên quyết cho Bài Quiz:**
   - Vào bài Quiz vừa tạo -> Bấm vào tab **Cài đặt (Settings)**.
   - Mở rộng mục **Hạn chế truy cập (Restrict access)** -> Bấm nút **Thêm hạn chế (Add restriction)**.
   - Chọn loại hạn chế: **Hoàn thành hoạt động (Activity completion)**.
   - Chọn: Hoạt động `Bài thu hoạch cuối khóa: Đề án Chuyển đổi Canh tác Số & VietGAP` phải `phải được đánh dấu là hoàn thành (must be marked complete)`.
   - Bấm **Lưu và trở về khoá học**.
   - *Kết quả ngoài trang khóa học:* Bên dưới bài Quiz xuất hiện dòng thông báo rõ ràng: *"Bị hạn chế: Không có sẵn trừ khi hoạt động Bài thu hoạch cuối khóa được hoàn thành"*.
2. **Tạo trang Cấp chứng nhận số QR:**
   - Bấm **+ Thêm hoạt động hoặc tài nguyên** trong mục cuối -> Chọn **Trang (Page)**.
   - **Tên:** `Nhận Chứng nhận Số QR Hoàn thành Khóa học`.
   - Mở mục **Hạn chế truy cập (Restrict access)** -> Bấm *Thêm hạn chế* -> Chọn loại **Điểm số (Grade)**.
   - Chọn bài thi: `Kiểm tra Trắc nghiệm Cuối khóa (10 Câu hỏi) - DAUTAY-CNC-01` phải đạt `>= 70%`.
   - Bấm **Lưu và trở về khoá học**.

---

### BƯỚC 10: DEMO NHÂN BẢN KHÓA HỌC (1-CLICK CLONE) & ĐÓNG VAI HỌC VIÊN

**Mục tiêu:** Chứng minh kiến trúc hệ thống đột phá giải quyết bài toán của Cán bộ kỹ thuật/Giảng viên khi triển khai đào tạo nhân rộng cho hàng chục hợp tác xã nông nghiệp chỉ với 1 cú click, sau đó kiểm tra trải nghiệm học tập thực tế của người nông dân.

1. **Biểu diễn Nhân bản Khóa học (Course Cloner):**
   - Nhìn lên thanh điều hướng phụ (`.mx-subnav`), bấm vào tab **Nhân bản khóa học** (hoặc truy cập `http://localhost:8090/local/mamxanhapp/clone_course.php`).
   - Màn hình công cụ nhân bản khóa học hiện ra:
     + **Khóa học mẫu nguồn (Master Template):** Bấm chọn khóa học vừa tạo: `Kỹ thuật Canh tác Dâu tây & Hoa Cúc Công nghệ cao trong Nhà màng Đà Lạt (DAUTAY-CNC-01)`.
     + **Địa phương / Hợp tác xã thụ hưởng:** Chọn một đơn vị (ví dụ: `Hợp tác xã Nông nghiệp Công nghệ cao Đơn Dương` hoặc `HTX Xuân Thọ`).
     + **Học vụ / Cán bộ kỹ thuật phụ trách:** Chọn cán bộ kỹ thuật.
     + Bấm nút màu xanh **Nhân bản khóa học ngay (1-Click Clone)**.
   - **Hiệu ứng thực tế:** Chỉ trong 3 đến 5 giây, hệ thống tự động nhân bản nguyên vẹn toàn bộ 4 chuyên đề, file PDF nhúng, video, H5P, SCORM, bài tập rubric, ngân hàng câu hỏi sang một khóa học mới tinh dành riêng cho địa phương đó mà không cần cấu hình lại thủ công!
2. **Ghi danh học viên & Kiểm tra dưới góc nhìn Nông dân:**
   - Quay lại khóa học `DAUTAY-CNC-01` -> Bấm vào tab **Người tham gia (Participants)**.
   - Bấm nút **Ghi danh người dùng (Enrol users)**.
   - Tìm và chọn tài khoản học viên: `Nguyễn Văn Hùng (nd_rau_lamdong)` -> Vai trò: *Học viên (Student)* -> Bấm *Ghi danh người dùng*.
3. **Đăng nhập vai trò Học viên (Farmer Perspective):**
   - Đăng xuất tài khoản Admin.
   - Tại trang đăng nhập, bấm nút 1-Click: **"Học viên: Nguyễn Văn Hùng (Nông dân)"** (hoặc đăng nhập `nd_rau_lamdong` / `MamXanh@2026!`).
   - Màn hình chuyển vào **Bảng điều khiển cá nhân (`/my/index.php`)**:
     + Khóa học `DAUTAY-CNC-01` xuất hiện ngay tại khối *Các khoá học được truy cập gần đây* và *Khóa học của tôi*.
     + Lịch tháng 11/2026 hiển thị các chấm xanh hạn nộp bài tập thảo luận và các buổi điểm danh.
     + Bấm vào khóa học: Đọc tài liệu PDF trực tiếp không bị tải về, xem video phụ đề, làm bài Quiz và nhận ngay phản hồi điểm số!

---

## BẢNG TỔNG HỢP KIỂM CHỨNG TRƯỚC BUỔI BẢO VỆ (CHECKLIST)

| STT | Chức năng kiểm chứng | Kết quả kỳ vọng | Thao tác chứng minh |
| :---: | :--- | :--- | :--- |
| 1 | **Tạo khóa học chuẩn** | Fullname, Shortname đúng chuẩn, bật Show activity dates | Vào `/course/view.php?id=...` thấy tiêu đề sắc nét, không lỗi font |
| 2 | **Nhúng PDF xem trực tiếp** | PDF mở thẳng trên giao diện web, không tải file | Bấm vào tài liệu VietGAP, cuộn xem trang 1 đến trang cuối |
| 3 | **Video HTML5 & Phụ đề** | Video chạy mượt, có nút bật/tắt phụ đề tiếng Việt | Bấm Play video nhà màng, bật phụ đề CC |
| 4 | **Học liệu H5P & SCORM** | Chấm điểm tự động đồng bộ Gradebook | Làm thử câu hỏi H5P/SCORM, mở Grader report thấy điểm nhảy |
| 5 | **Điểm danh chuyên cần** | 5 phiên lý thuyết, 3 phiên thực hành tháng 11/2026 | Vào tab Sessions của Attendance, bấm chấm P/L/E/A |
| 6 | **Bài tập & Hiển thị mô tả** | Câu hỏi thảo luận và hạn nộp hiện ngay ngoài trang khóa | Kiểm tra mặt khóa học giống 100% bố cục `lms.dlu.edu.vn` |
| 7 | **Đồng bộ Lịch biểu** | Hạn nộp xuất hiện trên Calendar & Upcoming events | Mở `/my/index.php` thấy ngày 13 và 20/11 khoanh tròn |
| 8 | **Chấm điểm Rubric** | Biểu mẫu 4 tiêu chí chấm điểm trực quan | Bấm View submissions của bài thu hoạch, thấy lưới chấm điểm Rubric |
| 9 | **Import Moodle XML** | Nạp 10 câu hỏi trong 5 giây | Vào Question bank -> Import file `.xml`, thành công 10/10 câu |
| 10 | **Nhân bản 1-Click** | Sao chép toàn bộ khóa học sang HTX mới trong 3 giây | Bấm Clone trên thanh Subnav, khóa học mới sinh ra hoàn chỉnh |

---
*Tài liệu được biên soạn phục vụ công tác thuyết minh và thực hành bảo vệ đồ án tốt nghiệp - Hệ thống Mầm Xanh LMS 2.0.*
