# 🗺️ Sơ đồ Nghiệp vụ & Hướng dẫn Thực hiện Toàn bộ Chức năng — Mầm Xanh LMS 2.0

Tài liệu này mô hình hóa **5 sơ đồ nghiệp vụ cốt lõi** và hướng dẫn thao tác từng bước cho **18 chức năng thực tế** của hệ thống **Mầm Xanh LMS 2.0** (`https://moodle.lvh.me:8443`).

---

## PHẦN I: HỆ THỐNG SƠ ĐỒ NGHIỆP VỤ (BUSINESS PROCESS DIAGRAMS)

### 1. Sơ đồ Tổng thể Hệ sinh thái Nghiệp vụ Liên thông 4 Tác nhân

```mermaid
flowchart TD
    subgraph Auth["1. Cổng Truy cập & Điều hướng Thông minh"]
        Login["Trang Đăng nhập (1-Click Instant Login + Song ngữ VI/EN)"]
        Router{"Bộ định tuyến Vai trò (observer::user_loggedin)"}
        Login --> Router
    end

    subgraph AdminFlow["2. Luồng Quản trị viên (admin)"]
        Audit["Kiểm toán Hạ tầng Turbo (OPcache JIT, Redis 7, HMAC)"]
        Backup["Sao lưu & Phục hồi (.sql MariaDB & gói khóa học .mbz)"]
        RBAC["Quản lý Phân quyền 3 Cấp & Cấu hình Hệ thống"]
    end

    subgraph CBKTFlow["3. Luồng Cán bộ Kỹ thuật (cbkt_pilot)"]
        Manage["Dashboard Điều hành & Thống kê 3 Vùng trồng (manage.php)"]
        Author["Biên soạn 6 Khóa học Tương tác (4 Chuyên đề/khóa - Zero PDF)"]
        QRStation["Phát Mã QR Xoay vòng 5 phút tại Vườn thực nghiệm"]
        Rubric["Chấm điểm Thực hành Rubric 4 Tiêu chí (grade.php)"]
        CSV["Xuất Báo cáo Tiến độ & Điểm số CSV chuẩn UTF-8 BOM"]
    end

    subgraph FarmerFlow["4. Luồng Nông dân Học viên (15 Nông hộ 3 Vùng)"]
        Dash["Dashboard Nông dân Mobile-First & Cảnh báo Thời tiết (index.php)"]
        Profile["Khai báo Hồ sơ Nông hộ & GPS -> Tự động vào Cohort Vùng"]
        Tools["Công cụ Pha phân A-B, Chẩn đoán Lá & Bác sĩ Cây trồng 24/7"]
        Learn["Học 4 Chuyên đề Số hóa (Nghe Giọng đọc TTS & Chế độ Ngoài trời)"]
        Checkin["Điểm danh Thực hành Vườn bằng QR Xoay vòng + Tọa độ GPS"]
        Quiz["Làm Bài Kiểm tra Quiz 12 Câu hỏi"]
        AgriOps["Tạo Lô VietGAP & Ghi Nhật ký Canh tác bằng Giọng nói"]
    end

    subgraph PublicFlow["5. Cổng Xác thực Công khai (Không cần Đăng nhập)"]
        CertVerify["Xác thực Chứng nhận Số QR ký HMAC-SHA256 (verify.php)"]
        VietGAPTrace["Hộ chiếu Truy xuất Nguồn gốc Lô Nông sản VietGAP (trace.php)"]
    end

    Router -->|"Vai trò Admin"| Audit
    Audit --> Backup
    Audit --> RBAC

    Router -->|"Vai trò Cán bộ Kỹ thuật"| Manage
    Manage --> Author
    Manage --> QRStation
    Manage --> CSV

    Router -->|"Vai trò Nông dân"| Dash
    Dash --> Profile
    Dash --> Tools
    Dash --> Learn
    Dash --> AgriOps

    Author -.->|"Cung cấp học liệu số"| Learn
    QRStation -.->|"Mã phiên 6 ký tự (5 phút)"| Checkin
    Checkin --> Rubric
    Rubric -->|"Đồng bộ điểm tự động"| Manage

    Learn --> Quiz
    Quiz -->|"Đạt >= 70%: Tự động ký cấp Chứng nhận + Huy hiệu"| CertVerify
    AgriOps -->|"Sinh mã QR Lô sản xuất VG-..."| VietGAPTrace
```

---

### 2. Sơ đồ Tuần tự: Luồng Học tập Số hóa, Thi Quiz & Tự động Cấp Chứng nhận QR `HMAC-SHA256`

```mermaid
sequenceDiagram
    autonumber
    actor ND as Nông dân Học viên
    participant UI as Giao diện AgriTrain Child (PWA/TTS/Ngoài trời)
    participant Course as Khóa học 4 Chuyên đề (mod_page & mod_quiz)
    participant Obs as Event Observer (local_mamxanhapp)
    participant Cert as Sổ cái Chứng nhận (local_mamxanhcert)
    actor Pub as Hợp tác xã / Đơn vị Thu mua

    ND->>UI: Chạm nút "Nông dân (1-Chạm)" trên trang Đăng nhập
    UI->>ND: Tự động chuyển thẳng tới Dashboard Nông dân (/local/mamxanhapp/index.php)
    ND->>UI: Bật "Ngoài trời" (tương phản cao) & chọn khóa học (VD: CHEBIEN01)
    UI->>Course: Hiển thị giao diện Thẻ lưới (Course Tiles UI) 4 Chuyên đề
    ND->>Course: Mở Chuyên đề 1-3 (Video Infographic, Slide 7 trang, Thẻ lật IPM)
    ND->>UI: Bấm "Đọc to bài học" (#mx-tts-player đọc giọng Tiếng Việt vi-VN)
    Course-->>Obs: Đánh dấu hoàn thành từng bài học (Activity Completion)
    ND->>Course: Mở Chuyên đề 4 -> Làm bài Quiz 12 câu & bấm "Nộp bài và kết thúc"
    Course->>Course: Chấm điểm tự động vào Gradebook (grade_categories & grade_grades)
    Course->>Obs: Phát sự kiện \mod_quiz\event\attempt_submitted
    alt Điểm Quiz >= 70% (từ 9/12 câu đúng)
        Obs->>Cert: Gọi local_mamxanhcert_issue_certificate() ký số HMAC-SHA256
        Obs->>Course: Cấp tự động Huy hiệu Khóa học (Course Badge)
        ND->>Cert: Xem chứng nhận Web, tải file PDF chuẩn Unicode & lấy mã QR
        Pub->>Cert: Quét mã QR mở /local/mamxanhcert/verify.php?code=MXCERT-...
        Cert-->>Pub: Trả kết quả "XÁC THỰC HỢP LỆ - HMAC-SHA256 VERIFIED"
    else Điểm Quiz < 70%
        Obs-->>ND: Lưu lịch sử điểm & khuyến nghị ôn lại Thẻ lật/Slide để thi lại
    end
```

---

### 3. Sơ đồ Tuần tự: Luồng Điểm danh Thực hành Vườn (QR Xoay vòng + GPS) & Chấm Rubric 4 Tiêu chí

```mermaid
sequenceDiagram
    autonumber
    actor CBKT as Cán bộ Kỹ thuật (cbkt_pilot)
    participant FP as Hoạt động Thực hành Vườn (mod_fieldpractice)
    actor ND as Nông dân tại Vườn
    participant GB as Sổ điểm Moodle Gradebook

    CBKT->>FP: Mở hoạt động Thực hành Vườn tại hiện trường (/mod/fieldpractice/view.php)
    FP-->>CBKT: Sinh Mã QR & Mã 6 ký tự xoay vòng mỗi 5 phút (HMAC time-slot)
    ND->>FP: Mở bài Thực hành trên điện thoại -> Lấy tọa độ GPS & nhập Mã 6 ký tự
    FP->>FP: Kiểm tra hiệu lực cửa sổ thời gian 5 phút & lưu tọa độ GPS thực địa
    FP-->>ND: Xác nhận "Đã điểm danh Thực hành Tại vườn thành công"
    CBKT->>FP: Mở Bảng Chấm điểm Rubric (/mod/fieldpractice/grade.php)
    CBKT->>FP: Chấm 4 tiêu chí: Chuẩn bị (25đ) + Kỹ thuật (25đ) + Sinh học (25đ) + Kết quả (25đ)
    FP->>GB: Gọi fieldpractice_grade_item_update() đẩy điểm tổng (0-100) thẳng vào Gradebook
    FP->>FP: Tự động đánh dấu Hoàn thành Hoạt động (COMPLETION_COMPLETE) cho Nông dân
    GB-->>CBKT: Cập nhật tức thì biểu đồ Điểm Rubric TB trên Dashboard CBKT & Báo cáo CSV
```

---

### 4. Sơ đồ Luồng Hồ sơ Nông hộ, Phân nhóm Cohort Tự động & Truy xuất Nguồn gốc VietGAP

```mermaid
flowchart LR
    A["Nông dân khai báo Hồ sơ Vườn (Tên trại, m2, Cây trồng, GPS)"] --> B{"Chọn Vùng trồng"}
    B -->|"Lâm Đồng"| C1["Tự động gán vào COHORT_LAMDONG"]
    B -->|"Đắk Lắk"| C2["Tự động gán vào COHORT_DAKLAK"]
    B -->|"Đồng Nai"| C3["Tự động gán vào COHORT_DONGNAI"]

    C1 & C2 & C3 --> D["Cá nhân hóa Cảnh báo Thời tiết Phun/Tưới trên Dashboard"]
    C1 & C2 & C3 --> E["Khởi tạo Lô Nông sản VietGAP (Mã VG-LD / VG-DL / VG-DN)"]

    E --> F["Ghi Nhật ký Canh tác Hàng ngày (Bấm Micro đọc giọng nói Web Speech API)"]
    F --> G["Lưu thông số pH, EC, Thời gian cách ly PHI & Cờ giọng nói"]
    G --> H["Phát hành Tem QR Hộ chiếu Nông sản (/local/agriops/trace.php)"]
    H --> I["Thương lái / Siêu thị quét QR xem Nguồn gốc Vườn & Nhật ký Minh bạch"]
```

---

### 5. Sơ đồ Vòng đời Trạng thái Học viên

```mermaid
stateDiagram-v2
    [*] --> TaiKhoanMoi: Khởi tạo tài khoản Nông dân
    TaiKhoanMoi --> DaDongBoCohort: Lưu Hồ sơ Nông hộ & GPS (local_farmerprofile)
    DaDongBoCohort --> DangHocTap: Học Chuyên đề 1-3 (Video, Slide Deck, Thẻ lật IPM)
    DangHocTap --> DaDiemDanhVuon: Quét QR xoay vòng + GPS (mod_fieldpractice)
    DaDiemDanhVuon --> DaChamRubric: CBKT chấm Rubric 4 tiêu chí vào Gradebook
    DangHocTap --> ChuaDatQuiz: Nộp bài Quiz 12 câu (< 70%)
    ChuaDatQuiz --> DangHocTap: Xem phản hồi giải thích & ôn tập lại
    DangHocTap --> DaCapChungNhanQR: Nộp bài Quiz 12 câu (>= 70%)
    DaChamRubric --> DaCapChungNhanQR: Hoàn thành toàn diện Lý thuyết + Thực hành
    DaCapChungNhanQR --> CanhTacVietGAP: Tạo Lô nông sản & Ghi nhật ký giọng nói (local_agriops)
    CanhTacVietGAP --> [*]: Xuất bán nông sản kèm QR Chứng nhận & QR Truy xuất
```

---

## PHẦN II: HƯỚNG DẪN THỰC HIỆN CHI TIẾT 18 CHỨC NĂNG

### Tài khoản Thực hành Nhanh
- **Cổng truy cập:** `https://moodle.lvh.me:8443` *(hoặc `http://localhost:8088`)*
- **Nông dân:** `nd_rau_lamdong` / `MX!Rau2026` *(hoặc `nd_saurieng_daklak`, `nd_vanan`)*
- **Cán bộ kỹ thuật:** `cbkt_pilot` / `MX!Canbo2026`
- **Quản trị viên:** `admin` / `Admin!2026MX`

### Nhóm A — Chức năng Chung & 6 Tiện ích Thông minh Thực địa
1. **Đăng nhập 1-Chạm & Điều hướng Tự động (`/login/index.php`):** Chạm một trong 3 nút vai trò trong khung *"⚡ 1-Chạm Đăng nhập Tức thì"* để tự động gắn CSRF `logintoken` và chuyển thẳng tới Dashboard tương ứng.
2. **Song ngữ 1-Chạm `[🇻🇳 VI | 🇬🇧 EN]`:** Chạm nút `🇬🇧 EN` hoặc `🇻🇳 VI` trên thanh tiện ích đầu trang để chuyển ngôn ngữ tức thì cho toàn bộ giao diện, Chatbot và file xuất CSV.
3. **Chế độ Ngoài trời Nắng gắt (`☀️ Ngoài trời`):** Bấm nút `☀️ Ngoài trời` trên thanh tiện ích để kích hoạt giao diện nền trắng `#FFFFFF`, chữ đen `#000000` cỡ `115%`, viền đậm `3px`.
4. **Trợ lý Giọng đọc Bài giảng (`#mx-tts-player`):** Mở bài học bất kỳ -> Bấm `▶️ Đọc to bài học`, chọn tốc độ `0.85x / 1.0x / 1.15x` để nghe rảnh tay bằng giọng đọc Tiếng Việt / Tiếng Anh.
5. **Chatbot nổi `"🌱 Bác sĩ Cây trồng 24/7"`:** Bấm nút nổi góc dưới phải -> Chạm chip câu hỏi nhanh (`pH / EC`, `Sâu bệnh IPM`, `Nấm Trichoderma`, `Kho lạnh`) hoặc nhập triệu chứng để nhận phác đồ sinh học.
6. **Cài đặt PWA ra Màn hình Chính:** Trên Chrome/Safari điện thoại, chọn *"Thêm vào màn hình chính"* để sử dụng như ứng dụng độc lập có hỗ trợ trang `offline.html` khi mất mạng.

### Nhóm B — Chức năng Dành cho Nông dân Học viên
7. **Hồ sơ Nông hộ & Đồng bộ Cohort (`/local/farmerprofile/index.php`):** Khai báo tên nông hộ, diện tích ($\text{m}^2$), cây trồng chính, vùng trồng (`Lâm Đồng / Đắk Lắk / Đồng Nai`) và tọa độ GPS -> Tự động đồng bộ vào Cohort vùng trồng.
8. **Trung tâm Công cụ Nông vụ (`/local/mamxanhapp/calculator.php`):** Chuyển đổi 3 tab để tính công thức pha phân tưới nhỏ giọt A–B, chẩn đoán triệu chứng lá bệnh IPM và xem cảnh báo thời tiết phun tưới tiểu vùng.
9. **Học 4 Chuyên đề Số hóa trên Thẻ lưới (`/course/view.php?id=2..7`):** Bấm `🚀 Vào học ngay` để học Video + Infographic HTML5, lật Slide tương tác 7 trang (kèm câu hỏi tình huống) và lật Thẻ Flashcards IPM.
10. **Điểm danh Thực hành Vườn QR + GPS (`/mod/fieldpractice/view.php`):** Nhập mã phiên 6 ký tự xoay vòng 5 phút từ màn hình CBKT kèm tọa độ GPS hiện trường -> Bấm `📍 Xác nhận Điểm danh Tại Vườn`.
11. **Làm bài Quiz 12 câu & Nhận Chứng nhận QR (`/mod/quiz/view.php` -> `/local/mamxanhcert/index.php`):** Đạt $\ge 70\%$ để mở khóa tự động Huy hiệu & Chứng nhận ký số `HMAC-SHA256`, tải file PDF tiếng Việt và lấy mã QR.
12. **Quản lý Lô VietGAP & Nhật ký Giọng nói (`/local/agriops/index.php`):** Đăng ký lô sản xuất mới để nhận mã `VG-...`, sau đó bấm `🎙️ Bấm để Đọc bằng Giọng nói` (Web Speech API) để ghi nhật ký pH, EC, PHI vào sổ điện tử.

### Nhóm C — Chức năng Dành cho Cán bộ Kỹ thuật (`cbkt_pilot`)
13. **Giám sát Dashboard Vùng trồng & Xuất CSV (`/local/mamxanhapp/manage.php`):** Xem KPI 3 vùng trồng, lọc danh sách nông dân theo từng tỉnh và bấm `📥 Xuất Báo cáo Excel (CSV UTF-8)` chuẩn UTF-8 BOM.
14. **Chấm điểm Thực hành Vườn Rubric 4 Tiêu chí (`/mod/fieldpractice/grade.php`):** Nhập điểm 4 tiêu chí (mỗi tiêu chí 0–25 điểm) + nhận xét -> Bấm `💾 Lưu điểm` để đẩy thẳng điểm tổng 100 vào Sổ điểm Moodle Gradebook.
15. **Biên soạn Học liệu Tương tác Mới:** Bật công tắc `Chế độ chỉnh sửa` trên thanh đầu trang khóa học để thêm hoạt động `Page`, `Thực hành tại vườn` hoặc `Quiz`.

### Nhóm D — Chức năng Quản trị (`admin`) & Tra cứu Công khai
16. **Kiểm toán Hiệu năng & Bảo mật (`/local/mamxanhapp/admin_audit.php`):** Chỉ dành riêng cho `admin` (chặn `403` với vai trò khác) để giám sát OPcache JIT, Redis 7, MariaDB 10.11 và chữ ký `HMAC-SHA256`.
17. **Sao lưu & Phục hồi Hệ thống (`scripts/backup/`):** Chạy `backup.ps1` / `backup.sh` để xuất `.sql` và gói khóa học `.mbz`; chạy `restore.ps1` / `restore.sh` để khôi phục nhanh.
18. **Xác thực QR Công khai Không cần Đăng nhập:**
    - Tra cứu Chứng nhận số QR: `/local/mamxanhcert/verify.php?code=MXCERT-2026-CHEBIEN01-0043`
    - Tra cứu Hộ chiếu Lô nông sản VietGAP: `/local/agriops/trace.php?code=VG-LD-202611-0016`
