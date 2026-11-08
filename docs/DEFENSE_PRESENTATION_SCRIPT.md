# 🎓 KỊCH BẢN THUYẾT TRÌNH, DEMO LIVE & PHẢN BIỆN BẢO VỆ ĐỒ ÁN TỐT NGHIỆP
## Đề tài: Hệ thống Moodle Tập huấn Kỹ thuật Canh tác Nông nghiệp Công nghệ cao — **Mầm Xanh LMS 2.0**

> **Tài liệu dành cho:** Nhóm Sinh viên Bảo vệ Đồ án Tốt nghiệp trước Hội đồng Chấm thi  
> **Cổng Demo Trực tiếp Độc lập (Direct HTTP):** `http://localhost:8090` *(Không phụ thuộc Reverse Proxy — Phản hồi trung vị TTFB `104.56 ms`)*  
> **Cổng Production HTTPS (Caddy 2 TLS):** `https://moodle.lvh.me:8443` *(Phản hồi trung vị TTFB `91.79 ms`)*  
> **Trạng thái Kiểm thử Tự động:** **51 / 51 Assertions PASS (`0 FAILED`)** — Bộ 32 ảnh chụp nghiệm thu thực tế (`01` → `27c`) tại `docs/screenshots/`

---

## PHẦN 1: BẢNG ĐỐI CHIẾU "TIẾP THU & KHẮC PHỤC Ý KIẾN HỘI ĐỒNG" (MỞ ĐẦU BÁO CÁO)

> **Lời dẫn mở đầu trước Hội đồng:**  
> *"Kính thưa Thầy Dũng, Cô Lương cùng quý Thầy Cô trong Hội đồng. Trước khi đi vào nội dung chính, nhóm em xin phép dành 1 phút đầu tiên để báo cáo Bảng Đối chiếu Tiếp thu và Khắc phục triệt để 100% các góp ý chuyên môn của Cô Lương và Thầy Dũng từ đợt chấm tiến độ trước."*

### 1.1. Bảng Đối chiếu Toàn diện Tiếp thu & Khắc phục Ý kiến của Cô Lương và Thầy Dũng

| STT | Giảng viên Góp ý | Nội dung Nhận xét & Yêu cầu ở Đợt Chấm Trước | Giải pháp Kỹ thuật & Học liệu Thực tế Nhóm Đã Triển khai Triệt để (100% Hoàn thành) | Minh chứng Trực tiếp (URL Demo, Mã nguồn & Ảnh Nghiệm thu E2E) |
| :-: | :--- | :--- | :--- | :--- |
| **1** | **Cô Lương** | **Demo cũ còn sơ sài, ít khóa học và thiếu học liệu thực tế:** Yêu cầu bổ sung đầy đủ các khóa học chuyên sâu theo từng nhóm kỹ thuật nông nghiệp công nghệ cao và dữ liệu học tập thực chất. | - Xây dựng hoàn chỉnh **7 khóa học chính quy** (**1 Khóa Mẫu Chuẩn Quốc gia `MASTER-AGRITECH-TPL`** + **6 Khóa Chuyên đề Chuyên sâu**: `CHAMSOC01`, `CHEBIEN01`, `IOTNHAMANG01`, `THUYCANH01`, `SAURIENG01`, `PHANVISINH01`) cùng khóa nhân bản địa phương.<br>- Mỗi khóa gồm **4 chuyên đề chuẩn hóa** và ngân hàng đề **12 câu hỏi trắc nghiệm tình huống/khóa** (tổng cộng **84+ câu hỏi chuyên ngành** có giải thích chi tiết). | - URL: `http://localhost:8090/course/view.php?id=2` (`CHAMSOC01`), `id=3` (`CHEBIEN01`), `id=8` (`MASTER-AGRITECH-TPL`)<br>- Script: `scripts/cli/stage4_course_catalog.php`<br>- Ảnh: `08_course_tiles_chebien01_desktop.png`, `10_interactive_slide_deck_and_tts.png`, `11_ipm_flashcards_lesson.png` |
| **2** | **Cô Lương** | **Video bài giảng cần phát trực tiếp mượt mà, có nội dung nông nghiệp thực tế:** Không dùng link YouTube dễ bị chặn nhúng hoặc lỗi mạng khi trình chiếu. | - Mã hóa bằng `ffmpeg` (`H.264 yuv420p +faststart` `.mp4` song song `.webm` VP9 + ảnh bìa `.jpg`) **4 bộ video kỹ thuật nông nghiệp thực tế** lưu trữ cục bộ ngay trên máy chủ (`/local/mamxanhapp/media/videos/`).<br>- Tích hợp trình phát HTML5 `<video>` kèm **phụ đề kỹ thuật Tiếng Việt chuẩn WebVTT (`.vtt`)**, 3 nút **tua nhanh theo phân đoạn (Chapter Seek)** và hộp **Tóm tắt Kỹ thuật + Infographic** ngay bên dưới video. | - URL: `http://localhost:8090/mod/page/view.php?id=2`<br>- Mã nguồn: `scripts/cli/stage4_html_builders.php`<br>- Ảnh: `23_real_html5_video_lesson.png` |
| **3** | **Cô Lương** | **Cần dữ liệu nông dân, điểm số và nhật ký thực tế để chứng minh tính ứng dụng:** Tránh hệ thống trống rỗng chỉ có 1 tài khoản thử nghiệm. | - Khởi tạo **15 hồ sơ nông hộ thực nghiệm** phân bổ tại 3 vùng chuyên canh trọng điểm (**Lâm Đồng, Đắk Lắk, Đồng Nai**) với tổng diện tích **13,32 ha**, tự động đồng bộ vào **3 Tổ hợp tác (System Cohorts)**.<br>- Có sẵn **45+ lượt làm bài Quiz thật** trong Sổ điểm Gradebook, **38 phiếu chấm điểm thực hành vườn Rubric**, **37 Chứng nhận Số QR** và **22 Lô nông sản VietGAP** kèm nhật ký giọng nói. | - URL: `http://localhost:8090/local/mamxanhapp/manage.php`<br>- Script: `scripts/cli/stage5_seed_simulation.php`<br>- Ảnh: `17_vietgap_batch_and_voice_diary.png`, `19_cbkt_analytics_dashboard_desktop.png` |
| **4** | **Thầy Dũng** | **Không sa đà vào hạ tầng Proxy phức tạp khi trình diễn:** Cần chạy trực tiếp dịch vụ web của Moodle đơn giản, ổn định, vào thẳng cổng HTTP để tập trung đánh giá nghiệp vụ LMS. | - Cấu hình container `moodle_app` mở trực tiếp cổng độc lập **`http://localhost:8090`** (`8090:80` trong `docker-compose.yml`).<br>- Thiết kế cơ chế **Dual-Mode `wwwroot` động** trong `moodle_custom/config.php` (tự động tách cookie phiên `directhttp` khi truy cập qua HTTP trực tiếp), giúp truy cập thẳng vào Apache của `moodle_app` mà **không phụ thuộc `moodle_proxy`**. | - URL trực tiếp: `http://localhost:8090/login/index.php`<br>- Cấu hình: `moodle_custom/config.php`, `docker-compose.yml`<br>- Kiểm thử E2E: Mục `5.1` (`51/51 PASS`) |
| **5** | **Thầy Dũng** | **Tích hợp xem trực tiếp tài liệu PDF trong khóa học mà KHÔNG cần tải về máy:** Nông dân dùng điện thoại hoặc máy tính phải đọc được ngay trên trang bài học. | - Dùng thư viện `TCPDF` (`freesans` Unicode) tự động biên soạn 2 cuốn sổ tay kỹ thuật PDF tiếng Việt (`So_Tay_Canh_Tac_Nong_Nghiep_CNC_VietGAP.pdf` và `Quy_Trinh_Bao_Quan_Che_Bien_Sau_Thu_Hoach.pdf`).<br>- Đưa vào khóa học qua module chuẩn `mod_resource` với cấu hình **`display = RESOURCELIB_DISPLAY_EMBED` (`1`)**.<br>- Xây dựng khung đọc trực tiếp **`#mx-inline-pdf-viewer`** dùng `PDF.js` tự chủ (cao **`650px` trên Desktop** / **`480px` trên Mobile**), có thanh công cụ **`➖ Thu nhỏ`**, **`➕ Phóng to (120%–220%)`**, **`↔️ Vừa khung`** và **`🖥️ Toàn màn hình`** — đọc trực tiếp 100% không tải file. | - URL: `http://localhost:8090/mod/resource/view.php?id=37` (`CHAMSOC01`) & `id=41` (`CHEBIEN01`)<br>- Mã nguồn: `moodle_custom/theme/agritrain_child/templates/drawers.mustache`<br>- Ảnh: `24a_inline_pdf_viewer_desktop.png`, `24b_inline_pdf_viewer_mobile.png` |
| **6** | **Thầy Dũng** | **Ứng dụng công cụ học liệu tương tác H5P (`mod_h5pactivity`) và chuẩn SCORM 1.2 (`mod_scorm`) có sẵn của Moodle:** Không chỉ dùng trang HTML tĩnh trong `mod_page`. | - **H5P Thực tế (`mod_h5pactivity`):** Nạp hơn **40 thư viện H5P chuẩn** vào `mdl_h5p_libraries` và đóng gói **3 gói `.h5p` hợp lệ** (`H5P.QuestionSet 1.20`, `H5P.CoursePresentation 1.26`, `H5P.Accordion 1.0`) hiển thị đúng biểu tượng H5P của Moodle và tự động đẩy điểm qua **xAPI** vào Sổ điểm **Gradebook**.<br>- **Gói Chuẩn Quốc tế ADL SCORM 1.2 (`mod_scorm`):** Đóng gói gói `.zip` chuẩn SCORM 1.2 có `imsmanifest.xml` và bộ giao tiếp `scorm_api.js` gọi trực tiếp `API.LMSInitialize("")`, `LMSSetValue("cmi.core.score.raw", "100")`, `LMSSetValue("cmi.core.lesson_status", "completed")`, `LMSCommit("")`, `LMSFinish("")` đồng bộ điểm `100/100` thẳng về Moodle SCORM API. | - URL H5P: `http://localhost:8090/mod/h5pactivity/view.php?id=38` & `id=42`<br>- URL SCORM 1.2: `http://localhost:8090/mod/scorm/view.php?id=39`<br>- Mã nguồn: `scripts/cli/stage7_package_builders.php`<br>- Ảnh: `25a_h5p_interactive_activity.png`, `25b_scorm12_vietgap_player.png` |
| **7** | **Thầy Dũng** | **Thiết kế "Khóa học Mẫu (Master Course Template)" có lộ trình tuần tự & Công cụ Nhân bản (Clone) nhanh cho các địa phương:** Giúp triển khai hàng loạt lớp tập huấn địa phương chỉ trong vài giây. | - Thiết kế khóa mẫu **`[MẪU CHUẨN] Khung Chương trình Tập huấn Nông nghiệp Công nghệ cao (Master Template)` (`MASTER-AGRITECH-TPL`)** có thanh **Stepper Lộ trình 4 Bước (`#mx-learning-path-stepper`)** ngay đầu khóa học.<br>- Áp dụng điều kiện mở khóa tuần tự nghiêm ngặt bằng **Moodle Restrict Access (`availability` JSON)**: Hoàn thành Bước 1 (Video + PDF) → mới mở Bước 2 (H5P + SCORM 1.2) → mới mở Bước 3 (`mod_fieldpractice`) → mới mở Bước 4 (Quiz $\ge 70\%$ mở Chứng nhận Số QR).<br>- Xây dựng **Công cụ Nhân bản Khóa học Mẫu 1-Chạm (`/local/mamxanhapp/clone_course.php`)** sử dụng Core API `\backup_controller` & `\restore_controller` để sao chép nguyên vẹn 100% khóa mẫu sang lớp địa phương mới (VD: *Tập huấn Nông dân Đơn Dương - Lâm Đồng Đợt 2*) và tự động ghi danh Cohort chỉ trong **`2.52 giây`**. | - URL Khóa Mẫu: `http://localhost:8090/course/view.php?id=8`<br>- URL Công cụ Clone: `http://localhost:8090/local/mamxanhapp/clone_course.php`<br>- Ảnh: `26a_master_template_learning_path_locked.png`, `26b_master_template_step2_unlocked.png`, `27a_course_cloner_form.png`, `27b_course_cloner_result.png`, `27c_cloned_local_course_view.png` |

---

## PHẦN 2: KỊCH BẢN SLIDE THUYẾT TRÌNH (12 SLIDES) & LỜI THOẠI CHI TIẾT

> **Thời lượng thuyết trình chuẩn:** 12–15 phút cho 12 Slides + 10 phút Demo Live trực tiếp trên máy chiếu.

---

### SLIDE 1: Trang Bìa & Tuyên bố Giá trị Cốt lõi
- **[Tiêu đề Slide & Bố cục hình ảnh]:**
  - **Tiêu đề chính:** HỆ THỐNG MOODLE TẬP HUẤN KỸ THUẬT CANH TÁC NÔNG NGHIỆP CÔNG NGHỆ CAO (MẦM XANH LMS — PHIÊN BẢN 2.0)
  - **Bố cục hình ảnh:** Ghép đôi màn hình Desktop & Mobile từ ảnh **`01_login_desktop_vi.png`** (bên trái) và **`02_login_mobile_vi.png`** (bên phải).
- **[Nội dung gạch đầu dòng trên Slide]:**
  - **Nền tảng cốt lõi:** Moodle 4.5 LTS · PHP 8.3 (OPcache JIT) · MariaDB 10.11 · Redis 7.
  - **Định hướng chuyên biệt:** Mobile-First (`375x812`) & Desktop (`1440x900`) · Song ngữ 1-Chạm `[🇻🇳 VI | 🇬🇧 EN]`.
  - **Chuẩn học liệu:** Video thực tế HTML5 `<video>`, Xem trực tiếp PDF (`PDF.js`), H5P (`mod_h5pactivity`), ADL SCORM 1.2 (`mod_scorm`).
  - **Hệ sinh thái 5 Custom Plugins:** Điều hướng & Clone khóa học (`local_mamxanhapp`), Hồ sơ nông hộ & Cohort (`local_farmerprofile`), Thực hành vườn QR/GPS & Rubric (`mod_fieldpractice`), Chứng nhận số `HMAC-SHA256` (`local_mamxanhcert`), Nhật ký giọng nói & Truy xuất VietGAP (`local_agriops`).
- **[Lời nói thuyết trình từng câu (Speaker Notes)]:**
  > *"Kính chào Hội đồng chấm đồ án tốt nghiệp. Em xin đại diện nhóm trình bày đề tài: **Hệ thống Moodle Tập huấn Kỹ thuật Canh tác Nông nghiệp Công nghệ cao — Mầm Xanh LMS Phiên bản 2.0**.*  
  > *Xuất phát từ bài toán thực tế tại các vùng chuyên canh Lâm Đồng, Đắk Lắk và Đồng Nai: bà con nông dân rất cần tiếp cận quy trình kỹ thuật tưới nhỏ giọt, kiểm soát pH/EC, phòng trừ sâu bệnh sinh học IPM và bảo quản sau thu hoạch chuẩn VietGAP, nhưng các hệ thống LMS truyền thống lại có giao diện quá phức tạp trên điện thoại và thiếu tính gắn kết với thực hành ngoài đồng ruộng.*  
  > *Vì vậy, nhóm em đã nghiên cứu và tùy biến sâu trên lõi Moodle 4.5 LTS, tích hợp trọn vẹn các chuẩn e-Learning quốc tế gồm H5P, SCORM 1.2, trình đọc PDF trực tiếp không cần tải về, cùng 5 plugin chuyên ngành nông nghiệp để tạo ra một nền tảng tập huấn thực địa hoàn chỉnh từ học lý thuyết, chấm điểm tại vườn đến cấp chứng nhận số QR và truy xuất nguồn gốc nông sản."*

---

### SLIDE 2: Bảng Báo cáo Tiếp thu & Khắc phục 100% Ý kiến Hội đồng (Cô Lương & Thầy Dũng)
- **[Tiêu đề Slide & Bố cục hình ảnh]:**
  - **Tiêu đề chính:** BÁO CÁO TIẾP THU & HOÀN THIỆN 100% THEO NHẬN XÉT CỦA HỘI ĐỒNG
  - **Bố cục hình ảnh:** Lưới 4 ảnh minh chứng trực quan gồm **`23_real_html5_video_lesson.png`** (Video thực tế), **`24a_inline_pdf_viewer_desktop.png`** (Xem trực tiếp PDF), **`25b_scorm12_vietgap_player.png`** (SCORM 1.2) và **`27b_course_cloner_result.png`** (Clone khóa học 1-chạm).
- **[Nội dung gạch đầu dòng trên Slide]:**
  - **Tiếp thu ý kiến Cô Lương:**
    - Mở rộng thành **7 khóa học thực tế** (84+ câu hỏi ngân hàng đề, Slide tương tác, Thẻ lật IPM).
    - Tích hợp **Video nông nghiệp thực tế (`.mp4`/`.webm`) + phụ đề Tiếng Việt (`.vtt`)** phát trực tiếp mượt mà 100%, không bị chặn nhúng YouTube.
    - Nạp **15 hồ sơ nông hộ thực nghiệm** tại 3 vùng trồng (13,32 ha), 45+ lượt thi Quiz và 22 lô VietGAP.
  - **Tiếp thu ý kiến Thầy Dũng:**
    - Mở cổng HTTP trực tiếp **`http://localhost:8090`** trên `moodle_app`, vận hành đơn giản không phụ thuộc Reverse Proxy.
    - **Xem trực tiếp PDF trên trang bài học** (`RESOURCELIB_DISPLAY_EMBED = 1`, khung 650px/480px + nút Phóng to/Thu nhỏ, không cần tải về).
    - Đóng gói và tích hợp **H5P thật (`mod_h5pactivity`)** & **gói bài giảng chuẩn SCORM 1.2 (`mod_scorm`)** đồng bộ điểm tự động vào Gradebook.
    - Thiết kế **Khóa học Mẫu (`MASTER-AGRITECH-TPL`)** có lộ trình khóa mở tuần tự 4 bước kèm **Công cụ Nhân bản 1-Chạm (`clone_course.php`)**.
- **[Lời nói thuyết trình từng câu (Speaker Notes)]:**
  > *"Thưa Cô Lương và Thầy Dũng, ngay sau buổi góp ý đợt trước, nhóm em đã rà soát và thực hiện nâng cấp toàn diện 5 hạng mục cốt lõi:*  
  > *Thứ nhất, đáp ứng yêu cầu của Cô Lương về dữ liệu và học liệu thực tế: toàn bộ các bài học Video hiện đã sử dụng tệp video nông nghiệp thực tế định dạng MP4 và WebM lưu cục bộ kèm phụ đề Tiếng Việt, phát mượt tức thì mà không bao giờ gặp lỗi chặn nhúng của YouTube. Hệ thống đã có 7 khóa học đầy đủ 4 chuyên đề và 15 hồ sơ nông hộ thực nghiệm tại Lâm Đồng, Đắk Lắk, Đồng Nai.*  
  > *Thứ hai, đáp ứng toàn bộ 4 yêu cầu kỹ thuật của Thầy Dũng: nhóm đã mở cổng HTTP trực tiếp `localhost:8090` để chạy thẳng vào ứng dụng; nhúng trình xem tài liệu PDF trực tiếp ngay trong trang bài học kèm thanh phóng to thu nhỏ để bà con đọc ngay mà không phải tải file về máy; đóng gói các hoạt động H5P và chuẩn SCORM 1.2 thật đẩy điểm tự động vào Sổ điểm Moodle; đồng thời xây dựng Khóa học Mẫu có lộ trình tuần tự 4 bước và công cụ Nhân bản 1-Chạm cho phép tạo lớp tập huấn địa phương mới chỉ trong hơn 2 giây."*

---

### SLIDE 3: Kiến trúc Tổng thể & Phân quyền Bảo mật 3 Cấp (RBAC)
- **[Tiêu đề Slide & Bố cục hình ảnh]:**
  - **Tiêu đề chính:** KIẾN TRÚC HỆ THỐNG & MÔ HÌNH PHÂN QUYỀN 3 CẤP CHUẨN MOODLE API
  - **Bố cục hình ảnh:** Sơ đồ kiến trúc bên trái kết hợp ảnh **`04_farmer_dashboard_desktop.png`** (Dashboard Nông dân) và **`22_admin_infrastructure_audit.png`** (Trang Kiểm toán Admin).
- **[Nội dung gạch đầu dòng trên Slide]:**
  - **Kiến trúc vận hành linh hoạt:** Kết nối trực tiếp qua `http://localhost:8090` (hoặc HTTPS `:8443`), tách biệt thư mục dữ liệu (`moodledata`) và bộ nhớ đệm (`Redis 7` + `tmpfs` RAM) đạt tốc độ phản hồi TTFB chỉ **`91–104 ms`**.
  - **Phân quyền 3 cấp chặt chẽ bằng Moodle Capabilities (`access.php`):**
    1. **Nông dân Học viên (`student` — `nd_rau_lamdong`, `nd_saurieng_daklak`, `nd_vanan`):** Học tập, điểm danh vườn, làm bài kiểm tra, nhận chứng nhận QR & ghi nhật ký VietGAP.
    2. **Cán bộ Kỹ thuật (`canbokythuat` — `cbkt_pilot`):** Biên soạn học liệu, nhân bản khóa học địa phương, chấm điểm Rubric thực hành vườn & xuất báo cáo Excel CSV.
    3. **Quản trị viên (`admin`):** Toàn quyền hệ thống, cấu hình bảo mật và kiểm toán hạ tầng.
  - **Chặn quyền nghiêm ngặt (`HTTP 403 Forbidden`):** Ngăn chặn tuyệt đối nông dân truy cập trang quản lý CBKT, trang nhân bản khóa học hoặc trang chấm điểm Rubric.
- **[Lời nói thuyết trình từng câu (Speaker Notes)]:**
  > *"Trên Slide 3 là kiến trúc vận hành và ma trận phân quyền 3 cấp của hệ thống. Thay vì sửa đổi mã nguồn lõi của Moodle gây khó khăn khi nâng cấp, nhóm tuân thủ 100% kiến trúc Plugin của Moodle gồm Child Theme `theme_agritrain_child`, Activity Module `mod_fieldpractice` và 4 Local Plugins.*  
  > *Hệ thống định nghĩa rõ 3 vai trò: Nông dân học viên, Cán bộ kỹ thuật với role tùy chỉnh `canbokythuat`, và Quản trị viên hệ thống. Ngay sau khi đăng nhập, cơ chế điều hướng thông minh sẽ đưa từng vai trò về đúng không gian làm việc của mình. Mọi đường dẫn nhạy cảm như trang thống kê vùng trồng, trang nhân bản khóa học hay trang chấm điểm Rubric đều được bảo vệ ở tầng Moodle Capability, trả về mã lỗi HTTP 403 Forbidden nếu truy cập trái quyền."*

---

### SLIDE 4: Giao diện Mobile-First (`theme_agritrain_child`) & Tối ưu Trải nghiệm Thực địa cho Nông dân
- **[Tiêu đề Slide & Bố cục hình ảnh]:**
  - **Tiêu đề chính:** GIAO DIỆN MOBILE-FIRST, SONG NGỮ 1-CHẠM & CHẾ ĐỘ NGOÀI TRỜI NẮNG GẮT
  - **Bố cục hình ảnh:** Ba ảnh đặt cạnh nhau: **`03_farmer_dashboard_mobile.png`** (Mobile `375x812` & Bottom Nav), **`05_farmer_outdoor_sunlight_mode.png`** (Chế độ Ngoài trời siêu tương phản) và **`10_interactive_slide_deck_and_tts.png`** (Trợ lý giọng đọc `#mx-tts-player`).
- **[Nội dung gạch đầu dòng trên Slide]:**
  - **Chuẩn công thái học cho nông dân:** Mọi nút bấm đạt chiều cao tối thiểu **`>= 48px` (Chuẩn WCAG AAA)**, dễ chạm ngay cả khi tay đang đeo găng làm vườn.
  - **Thanh điều hướng đáy cố định (`bottom-nav-bar`):** 5 lối tắt một ngón cái (*Khóa học · Nhật ký · Công cụ · Chứng nhận · Nông hộ*).
  - **Chế độ Ngoài trời Nắng gắt (`☀️ Ngoài trời`):** Chuyển tức thì sang nền trắng `#FFFFFF`, chữ đen `#000000` phóng lớn **115%**, viền đậm **3px** chống lóa dưới nắng trưa.
  - **Trợ lý Giọng đọc Rảnh tay (`#mx-tts-player`) & Song ngữ `[🇻🇳 VI | 🇬🇧 EN]`:** Tự động đọc to nội dung bài giảng bằng giọng Tiếng Việt (`vi-VN`) hoặc Tiếng Anh (`en-US`) với 3 mức tốc độ `0.85x / 1.0x / 1.15x`.
- **[Lời nói thuyết trình từng câu (Speaker Notes)]:**
  > *"Khi khảo sát thói quen sử dụng điện thoại của nông dân ngoài vườn, nhóm nhận thấy hai rào cản lớn nhất là ánh nắng mặt trời gây lóa màn hình và việc bà con đang bận tay chăm sóc cây trồng không tiện đọc văn bản dài.*  
  > *Để giải quyết triệt để, Child Theme `theme_agritrain_child` được thiết kế theo chuẩn Mobile-First với toàn bộ nút thao tác cao từ 48 pixel trở lên và thanh điều hướng cố định dưới đáy màn hình. Đặc biệt, khi bà con chạm vào nút **Ngoài trời**, giao diện lập tức chuyển sang chế độ siêu tương phản chữ đen nền trắng phóng to 115% với viền dày 3 pixel cực kỳ rõ nét dưới nắng gắt. Đồng thời, đầu mỗi bài học đều tích hợp sẵn **Trợ lý Giọng đọc AI** giúp bà con chỉ cần bấm một nút là có thể vừa tỉa cành, pha phân vừa nghe điện thoại đọc to toàn bộ bài giảng."*

---

### SLIDE 5: Trợ lý AI "Bác sĩ Cây trồng 24/7" & Trung tâm Công cụ Nông vụ Thông minh
- **[Tiêu đề Slide & Bố cục hình ảnh]:**
  - **Tiêu đề chính:** TRỢ LÝ AI "BÁC SĨ CÂY TRỒNG 24/7" & BỘ 3 CÔNG CỤ TÍNH TOÁN NÔNG VỤ
  - **Bố cục hình ảnh:** Ba ảnh minh chứng: **`06_crop_doctor_chatbot_247.png`** (Chatbot Bác sĩ Cây trồng), **`07a_smart_fertigation_calculator.png`** (Máy tính pha phân A–B) và **`07b_microclimate_weather_advisory.png`** (Cảnh báo thời tiết phun tưới tiểu vùng).
- **[Nội dung gạch đầu dòng trên Slide]:**
  - **Chatbot nổi `"🌱 Bác sĩ Cây trồng 24/7"`:** Tích hợp chip câu hỏi nhanh 1-chạm (*pH/EC, Sâu bệnh IPM, Nấm Trichoderma, Kho lạnh*) tư vấn tức thì phác đồ sinh học an toàn.
  - **Công cụ 1 — Máy tính Pha phân & Tưới nhỏ giọt (`calculator.php`):** Tự động tính thể tích dung dịch mẹ Stock A, Stock B, lượng axit chỉnh pH và số phút mở van tưới theo diện tích vườn ($\text{m}^2$) và giai đoạn sinh trưởng.
  - **Công cụ 2 — Chẩn đoán Triệu chứng Lá bệnh IPM:** Tra cứu triệu chứng thiếu khoáng hoặc dịch hại trên lá non/lá già kèm thời gian cách ly PHI.
  - **Công cụ 3 — Trạm Cảnh báo Thời tiết Phun tưới Tiểu vùng:** Cảnh báo độ ẩm, tốc độ gió, chỉ số VPD và khung giờ vàng nên phun thuốc sinh học cho từng vùng Lâm Đồng, Đắk Lắk, Đồng Nai.
- **[Lời nói thuyết trình từng câu (Speaker Notes)]:**
  > *"Không chỉ dừng lại ở việc cung cấp bài giảng, Mầm Xanh LMS 2.0 còn đóng vai trò là người bạn đồng hành kỹ thuật hàng ngày của nhà nông thông qua Chatbot nổi **Bác sĩ Cây trồng 24/7** và **Trung tâm Công cụ Nông vụ**.*  
  > *Bà con không cần nhớ công thức hóa học phức tạp: chỉ cần nhập diện tích vườn và chọn loại cây như Rau thủy canh, Dưa lưới hay Sầu riêng, hệ thống sẽ tính chính xác cần pha bao nhiêu mililít dung dịch mẹ bồn A, bồn B và mở van tưới nhỏ giọt trong bao nhiêu phút. Bên cạnh đó, Trạm cảnh báo thời tiết tiểu vùng sẽ khuyến nghị ngay trên Dashboard xem hôm nay tại Đà Lạt hay Đắk Lắk có gió lớn hoặc sắp mưa hay không để bà con tránh phun chế phẩm sinh học bị rửa trôi lãng phí."*

---

### SLIDE 6: Xem Trực tiếp Tài liệu PDF Không Cần Tải Về & Video Thực tế HTML5 (Ý kiến Cô Lương & Thầy Dũng)
- **[Tiêu đề Slide & Bố cục hình ảnh]:**
  - **Tiêu đề chính:** HỌC LIỆU ĐA PHƯƠNG TIỆN: VIDEO THỰC TẾ HTML5 & XEM TRỰC TIẾP PDF TRÊN WEB
  - **Bố cục hình ảnh:** Ba ảnh chụp sắc nét: **`23_real_html5_video_lesson.png`** (Video MP4 + Phụ đề `.vtt`), **`24a_inline_pdf_viewer_desktop.png`** (Khung đọc PDF 650px trên Desktop) và **`24b_inline_pdf_viewer_mobile.png`** (Khung đọc PDF 480px trên Mobile).
- **[Nội dung gạch đầu dòng trên Slide]:**
  - **Trình phát Video Nông nghiệp Thực tế (`HTML5 <video>`):**
    - Lưu trữ cục bộ song song 2 định dạng `.mp4` (H.264) và `.webm` (VP9) kèm phụ đề Tiếng Việt `.vtt`.
    - Tích hợp 3 nút chuyển phân đoạn kỹ thuật (**Chapter Seek**) đồng bộ với bảng tóm tắt thông số và Infographic bên dưới.
  - **Trình đọc Sổ tay PDF Nhúng Trực tiếp (`mod_resource` `RESOURCELIB_DISPLAY_EMBED = 1`):**
    - Tự động sinh tài liệu PDF kỹ thuật Tiếng Việt chuẩn Unicode bằng `TCPDF`.
    - Khung xem trực tiếp **`#mx-inline-pdf-viewer`** (cao **`650px` trên Desktop** / **`480px` trên Mobile**) dựng từng trang bằng `PDF.js` Canvas độ nét cao.
    - Trang bị thanh công cụ **`➖ Thu nhỏ`**, **`➕ Phóng to`**, **`↔️ Vừa khung`**, **`🖥️ Toàn màn hình`** — học viên đọc trực tiếp trên trình duyệt **không cần tải file về máy**.
- **[Lời nói thuyết trình từng câu (Speaker Notes)]:**
  > *"Trên Slide 6 là hai cải tiến quan trọng theo đúng yêu cầu của Cô Lương và Thầy Dũng.*  
  > *Về phía Video bài giảng: nhóm đã tích hợp các video thực tế quay cảnh hệ thống tưới nhỏ giọt nhà màng, sơ chế đóng gói sau thu hoạch và ủ phân vi sinh, kèm phụ đề Tiếng Việt hiển thị trực tiếp trên khung hình và nút tua nhanh tới từng bước quy trình.*  
  > *Về phía Tài liệu kỹ thuật PDF: thay vì bắt nông dân tải file PDF về điện thoại gây tốn dung lượng và phải cài thêm ứng dụng đọc file, nhóm đã cấu hình module `mod_resource` ở chế độ `RESOURCELIB_DISPLAY_EMBED` kết hợp trình dựng `PDF.js` ngay trong trang bài học Moodle. Khung xem có chiều cao chuẩn 650 pixel trên máy tính và 480 pixel trên điện thoại, hỗ trợ phóng to 120% đến 220%, thu nhỏ và xem toàn màn hình cực kỳ sắc nét."*

---

### SLIDE 7: Tích hợp Học liệu Tương tác Chuẩn Quốc tế H5P (`mod_h5pactivity`) & ADL SCORM 1.2 (`mod_scorm`)
- **[Tiêu đề Slide & Bố cục hình ảnh]:**
  - **Tiêu đề chính:** TÍCH HỢP H5P (`mod_h5pactivity`) & GÓI BÀI GIẢNG CHUẨN SCORM 1.2 (`mod_scorm`)
  - **Bố cục hình ảnh:** Đặt song song 2 ảnh **`25a_h5p_interactive_activity.png`** (Bài tập tương tác H5P Question Set) và **`25b_scorm12_vietgap_player.png`** (Trình phát SCORM 1.2 báo `100/100 COMPLETED` & nhật ký giao tiếp API).
- **[Nội dung gạch đầu dòng trên Slide]:**
  - **Học liệu tương tác H5P thật (`mod_h5pactivity`):**
    - Cài đặt đầy đủ hệ thống thư viện lõi H5P vào `mdl_h5p_libraries`.
    - Đóng gói và triển khai 3 dạng tương tác chuẩn: **H5P Question Set**, **H5P Course Presentation** (trình chiếu tích hợp câu hỏi) và **H5P Accordion**.
    - Tự động gửi sự kiện **xAPI (Experience API)** lưu điểm vào bảng `mdl_h5pactivity_attempts` và **Moodle Gradebook**.
  - **Gói bài giảng chuẩn quốc tế ADL SCORM 1.2 (`mod_scorm`):**
    - Đóng gói `.zip` chuẩn SCORM 1.2 có lược đồ `imsmanifest.xml` (`ADL SCORM 1.2 SCO`).
    - Bộ điều hợp `scorm_api.js` tự động tìm đối tượng `window.parent.API` của Moodle và thực thi chuỗi giao thức chuẩn:
      `LMSInitialize("")` → `LMSSetValue("cmi.core.score.raw", "100")` → `LMSSetValue("cmi.core.lesson_status", "completed")` → `LMSCommit("")` → `LMSFinish("")`.
- **[Lời nói thuyết trình từng câu (Speaker Notes)]:**
  > *"Thực hiện đúng yêu cầu của Thầy Dũng về việc khai thác chiều sâu các chuẩn e-Learning sẵn có của Moodle thay vì chỉ viết HTML tĩnh, nhóm đã triển khai song song cả hai chuẩn H5P và SCORM 1.2:*  
  > *Ở hình bên trái là hoạt động `mod_h5pactivity` sử dụng thư viện chuẩn `H5P.QuestionSet` và `H5P.CoursePresentation`. Khi học viên chọn đáp án và nhấn **Kiểm tra đáp án**, H5P chấm điểm tại chỗ và bắn gói tin xAPI về Moodle để cập nhật thẳng vào Sổ điểm Gradebook.*  
  > *Ở hình bên phải là gói bài giảng chuẩn quốc tế **ADL SCORM 1.2** về Quy trình Thực hành Nông nghiệp Tốt VietGAP và Kiểm soát Mối nguy. Ngay khi mở bài, gói SCORM bắt tay với Moodle thông qua hàm `LMSInitialize` (hiển thị trạng thái màu xanh lá *Đã kết nối Moodle SCORM 1.2 API*). Khi học viên hoàn thành 4 câu hỏi kiểm soát mối nguy, gói SCORM gọi `LMSSetValue` đẩy điểm `100/100` và trạng thái `completed` về máy chủ Moodle."*

---

### SLIDE 8: Khóa học Mẫu Chuẩn (`MASTER-AGRITECH-TPL`) & Công cụ Nhân bản 1-Chạm (1-Click Course Cloner)
- **[Tiêu đề Slide & Bố cục hình ảnh]:**
  - **Tiêu đề chính:** KHÓA HỌC MẪU TUẦN TỰ (`MASTER-AGRITECH-TPL`) & CÔNG CỤ NHÂN BẢN 1-CHẠM CHO ĐỊA PHƯƠNG
  - **Bố cục hình ảnh:** Cụm 4 ảnh liên hoàn: **`26a_master_template_learning_path_locked.png`** (Bước 2–4 đang khóa) → **`26b_master_template_step2_unlocked.png`** (Hoàn thành Bước 1 tự mở khóa Bước 2) → **`27a_course_cloner_form.png`** (Form nhân bản) → **`27b_course_cloner_result.png`** (Nhân bản thành công trong `2.52s`).
- **[Nội dung gạch đầu dòng trên Slide]:**
  - **Khóa học Mẫu Chuẩn Quốc gia (`MASTER-AGRITECH-TPL`):**
    - Hiển thị **Sơ đồ Lộ trình Học tập Tuần tự (`#mx-learning-path-stepper`)** gồm 5 nút trực quan ngay đầu khóa học.
    - Cấu hình **Moodle Restrict Access (`availability` JSON)** khóa mở tuần tự tự động:
      `Bước 1 (Video + PDF)` $\rightarrow$ `Bước 2 (H5P + SCORM 1.2)` $\rightarrow$ `Bước 3 (Thực hành Vườn)` $\rightarrow$ `Bước 4 (Quiz >= 70%)` $\rightarrow$ `Bước 5 (Chứng nhận Số QR)`.
  - **Công cụ Nhân bản Khóa học Mẫu 1-Chạm (`/local/mamxanhapp/clone_course.php`):**
    - Dành cho Cán bộ kỹ thuật khi mở lớp mới tại từng xã/huyện (VD: *Tập huấn Nông dân Đơn Dương - Lâm Đồng Đợt 2*).
    - Vận hành bằng **`\backup_controller`** & **`\restore_controller`** của Moodle Core: sao chép nguyên vẹn 100% học liệu, tự động ánh xạ lại toàn bộ ID điều kiện mở khóa tuần tự (`Restrict Access`) và ghi danh tự động **Cohort nông dân địa phương** chỉ trong **`2.52 giây`**.
- **[Lời nói thuyết trình từng câu (Speaker Notes)]:**
  > *"Một bài toán thực tế rất lớn mà Thầy Dũng đã gợi mở cho nhóm là: khi Sở Nông nghiệp hoặc Trung tâm Khuyến nông muốn nhân rộng chương trình tập huấn này cho hàng chục xã, huyện khác nhau, làm sao để đảm bảo mọi nơi đều học đúng một khung chương trình chuẩn mà giảng viên không phải tạo lại từng bài học thủ công?*  
  > *Nhóm đã giải quyết bằng hai tính năng gắn kết chặt chẽ:*  
  > *Thứ nhất là thiết kế **Khóa học Mẫu Chuẩn `MASTER-AGRITECH-TPL`** có sơ đồ Stepper 4 bước ngay đầu trang. Nhờ cơ chế Restrict Access của Moodle, học viên bắt buộc phải xem xong Video và đọc Sổ tay PDF ở Bước 1 thì hệ thống mới tự động mở khóa Bước 2 (H5P & SCORM 1.2), tiếp tục hoàn thành mới mở Bước 3 (Thực hành vườn) và Bước 4 (Kiểm tra cuối khóa).*  
  > *Thứ hai là **Công cụ Nhân bản Khóa học 1-Chạm**. Cán bộ kỹ thuật chỉ cần chọn Khóa học Mẫu, gõ tên lớp địa phương mới như *Tập huấn Nông dân Đơn Dương - Lâm Đồng Đợt 2*, chọn Tổ hợp tác Lâm Đồng rồi bấm **Nhân bản ngay**. Hệ thống gọi trực tiếp `backup_controller` và `restore_controller` của Moodle để tạo ra khóa học mới nguyên vẹn 100% lộ trình, tự động ánh xạ lại toàn bộ ID điều kiện mở khóa và ghi danh học viên chỉ trong 2,52 giây!"*

---

### SLIDE 9: Hoạt động Thực hành Hiện trường tại Vườn (`mod_fieldpractice`) & Chấm điểm Rubric 4 Tiêu chí
- **[Tiêu đề Slide & Bố cục hình ảnh]:**
  - **Tiêu đề chính:** MODULE THỰC HÀNH TẠI VƯỜN (`mod_fieldpractice`): ĐIỂM DANH QR/GPS & CHẤM RUBRIC
  - **Bố cục hình ảnh:** Hai ảnh đối xứng: **`12_fieldpractice_qr_gps_checkin.png`** (Nông dân điểm danh QR xoay vòng + tọa độ GPS tại vườn) và **`21_cbkt_field_rubric_grading.png`** (Cán bộ kỹ thuật chấm điểm Rubric 4 tiêu chí đẩy thẳng vào Gradebook).
- **[Nội dung gạch đầu dòng trên Slide]:**
  - **Giải quyết bài toán "Học đi đôi với Hành" ngoài vườn mẫu:**
    - Xây dựng Activity Module chuẩn Moodle **`mod_fieldpractice`** (hỗ trợ đầy đủ Gradebook API, Activity Completion API và Backup/Restore `.mbz`).
  - **Chống điểm danh hộ bằng Mã QR Xoay vòng 5 phút + Tọa độ GPS:**
    - Mã phiên 6 ký tự tự động đổi mới mỗi `300 giây` theo thuật toán băm `HMAC-SHA256(qr_secret, floor(time() / 300))`.
    - Ghi nhận đồng thời tọa độ **GPS (`latitude`, `longitude`)** của điện thoại nông dân tại vườn thực nghiệm.
  - **Chấm điểm Thực hành theo Rubric 4 Tiêu chí (Thang 100 điểm):**
    1. *Chuẩn bị vật tư & Bảo hộ (0–25đ)* · 2. *Thao tác kỹ thuật (0–25đ)* · 3. *An toàn sinh học VietGAP (0–25đ)* · 4. *Chất lượng thành phẩm & Nhật ký (0–25đ)*.
    - Tự động đồng bộ điểm tổng vào **`mdl_grade_grades`** qua hàm chuẩn `grade_update()`.
- **[Lời nói thuyết trình từng câu (Speaker Notes)]:**
  > *"Tập huấn nông nghiệp không thể chỉ chấm điểm qua lý thuyết trắc nghiệm mà phải đánh giá được thao tác thực tế ngoài vườn như đấu nối béc tưới nhỏ giọt, hiệu chuẩn bút đo pH/EC hay bao trái sầu riêng.*  
  > *Để làm được điều đó, nhóm đã tự phát triển một Activity Module mới hoàn toàn theo chuẩn Moodle tên là `mod_fieldpractice`. Tại buổi tập huấn hiện trường, màn hình của Cán bộ kỹ thuật sẽ phát một mã QR tự động xoay vòng mỗi 5 phút. Nông dân đứng tại vườn dùng điện thoại bấm định vị GPS và nhập mã phiên để điểm danh.*  
  > *Ngay sau đó, Cán bộ kỹ thuật mở bảng chấm điểm **Rubric 4 tiêu chí** (mỗi tiêu chí tối đa 25 điểm) để chấm trực tiếp thao tác của từng nông dân. Khi bấm **Lưu điểm**, điểm số được đẩy thẳng vào Sổ điểm trung tâm Moodle Gradebook và tự động mở khóa bài thi cuối khóa."*

---

### SLIDE 10: Kiểm tra Cuối khóa (`mod_quiz`), Chứng nhận Số QR (`HMAC-SHA256`) & Hộ chiếu Truy xuất VietGAP
- **[Tiêu đề Slide & Bố cục hình ảnh]:**
  - **Tiêu đề chính:** CHỨNG NHẬN SỐ QR (`HMAC-SHA256`) & HỘ CHIẾU TRUY XUẤT NGUỒN GỐC VIETGAP
  - **Bố cục hình ảnh:** Bốn ảnh minh chứng: **`14_quiz_graded_review_100pct.png`** (Kết quả Quiz 100%), **`15_farmer_digital_certificates.png`** (Danh sách Chứng nhận số), **`16_public_qr_cert_verification.png`** (Cổng xác thực QR Chứng nhận công khai) và **`18_public_vietgap_qr_passport.png`** (Hộ chiếu truy xuất QR lô nông sản VietGAP).
- **[Nội dung gạch đầu dòng trên Slide]:**
  - **Cơ chế Cấp Chứng nhận Số & Huy hiệu Tự động (`local_mamxanhcert`):**
    - Khi học viên hoàn thành bài kiểm tra 12 câu (`mod_quiz`) đạt **$\ge 70\%$**, hệ thống tự động cấp **Huy hiệu Khóa học (Moodle Badges)** và sinh **Chứng nhận Số PDF/Web**.
    - Chống làm giả bằng chữ ký mật mã **`HMAC-SHA256`** tính trên chuỗi định danh `(cert_code | userid | courseid | final_score | timecreated)`.
    - Cổng xác thực QR công khai không cần đăng nhập: `/local/mamxanhcert/verify.php`.
  - **Nhật ký Canh tác Giọng nói & Truy xuất Nguồn gốc VietGAP (`local_agriops`):**
    - Nông dân tạo lô sản xuất (`VG-LD-...`) và bấm **Micro đọc nhật ký bằng giọng nói Tiếng Việt (`Web Speech API`)** ngay ngoài ruộng.
    - Phát hành **Hộ chiếu QR Truy xuất Nguồn gốc (`/local/agriops/trace.php`)** hiển thị công khai thông tin nông hộ, tọa độ GPS, thông số pH/EC, thời gian cách ly PHI và chứng nhận tập huấn của chủ vườn.
- **[Lời nói thuyết trình từng câu (Speaker Notes)]:**
  > *"Sau khi hoàn thành lộ trình học và đạt từ 70% điểm bài kiểm tra cuối khóa trở lên, học viên sẽ nhận được **Chứng nhận Hoàn thành Tập huấn** cả bản Web và bản in PDF Tiếng Việt. Mỗi chứng nhận đều được ký số bằng thuật toán `HMAC-SHA256` kèm mã QR để Hợp tác xã hoặc doanh nghiệp thu mua quét kiểm tra tính hợp lệ trực tuyến mà không cần tài khoản đăng nhập.*  
  > *Điểm đột phá khép kín quy trình chuyển đổi số nông nghiệp của đồ án là plugin `local_agriops`: sau khi được cấp chứng nhận, bà con ứng dụng ngay kiến thức vào quản lý lô nông sản VietGAP của gia đình. Bà con chỉ cần bấm biểu tượng Micro trên điện thoại và đọc bằng giọng nói, hệ thống sẽ chuyển giọng nói thành văn bản nhật ký canh tác và sinh ra **Mã QR Hộ chiếu Nông sản** để dán lên từng sọt rau, trái sầu riêng khi xuất bán ra thị trường."*

---

### SLIDE 11: Trung tâm Điều hành & Báo cáo Vùng trồng cho Cán bộ Kỹ thuật (`manage.php`)
- **[Tiêu đề Slide & Bố cục hình ảnh]:**
  - **Tiêu đề chính:** TRUNG TÂM ĐIỀU HÀNH, THỐNG KÊ TIẾN ĐỘ ĐA VÙNG TRỒNG & XUẤT BÁO CÁO EXCEL CSV
  - **Bố cục hình ảnh:** Hai ảnh **`19_cbkt_analytics_dashboard_desktop.png`** (Dashboard CBKT trên Desktop) và **`20_cbkt_analytics_dashboard_mobile.png`** (Dashboard CBKT trên Mobile `375x812`).
- **[Nội dung gạch đầu dòng trên Slide]:**
  - **Bảng điều khiển Thời gian thực (`/local/mamxanhapp/manage.php`):**
    - Thẻ KPI tổng hợp: **15 nông hộ**, **13,32 ha** diện tích VietGAP, điểm trung bình Quiz toàn hệ thống, điểm trung bình Thực hành vườn Rubric, **37 Chứng nhận QR** và **22 Lô VietGAP**.
  - **Phân tích Chuyên sâu theo 3 Vùng chuyên canh (Lâm Đồng · Đắk Lắk · Đồng Nai):**
    - Biểu đồ so sánh năng lực học tập và bộ lọc 1-chạm theo từng tỉnh/Cohort.
  - **Xuất Báo cáo Excel CSV Chuẩn Tiếng Việt & Song ngữ (`manage.php?download=csv`):**
    - Đính kèm sẵn Byte Order Mark **UTF-8 BOM (`\xEF\xBB\xBF`)** giúp mở trực tiếp bằng Microsoft Excel hiển thị hoàn hảo 100% dấu Tiếng Việt (và tự động chuyển sang cột Tiếng Anh khi xuất ở chế độ `EN`).
- **[Lời nói thuyết trình từng câu (Speaker Notes)]:**
  > *"Đối với Cán bộ kỹ thuật và lãnh đạo Trung tâm Khuyến nông, trang điều hành `manage.php` cung cấp bức tranh toàn cảnh thời gian thực về tiến độ tập huấn của cả 3 vùng trồng Lâm Đồng, Đắk Lắk và Đồng Nai.*  
  > *Cán bộ có thể lọc danh sách nông hộ theo từng địa phương, theo dõi ai đã hoàn thành bao nhiêu bài học, đạt bao nhiêu điểm Quiz, bao nhiêu điểm thực hành vườn và đã được cấp chứng nhận hay chưa. Khi cần báo cáo lên cấp trên, chỉ cần bấm nút **Xuất Báo cáo Excel CSV**, hệ thống sẽ sinh file CSV gắn sẵn mã UTF-8 BOM để mở trực tiếp trên Microsoft Excel không bao giờ bị lỗi phông chữ Tiếng Việt."*

---

### SLIDE 12: Kiểm thử Tự động E2E (51/51 PASS), Hiệu năng TTFB, Sao lưu & Kết luận
- **[Tiêu đề Slide & Bố cục hình ảnh]:**
  - **Tiêu đề chính:** KẾT QUẢ KIỂM THỬ TỰ ĐỘNG E2E (51/51 PASS), BENCHMARK TTFB & CÔNG CỤ SAO LƯU
  - **Bố cục hình ảnh:** Ghép ảnh **`22_admin_infrastructure_audit.png`** (Kiểm toán hạ tầng Admin) và **`27c_cloned_local_course_view.png`** (Khóa học địa phương sau nhân bản).
- **[Nội dung gạch đầu dòng trên Slide]:**
  - **Kiểm thử Tự động Toàn diện (`tests/e2e/run_e2e_and_benchmark.mjs`):**
    - Vượt qua **51 / 51 Assertions (`0 FAILED`)** trên trình duyệt thật cho cả 3 vai trò (Nông dân, CBKT, Admin) và cả 2 khung hình Desktop (`1440x900`) & Mobile (`375x812`).
  - **Hiệu năng Phản hồi Nhanh (Warm TTFB Benchmark):**
    - Trang đăng nhập Direct HTTP (`:8090`): **`104.56 ms`** · HTTPS (`:8443`): **`91.79 ms`**.
    - Cổng xác thực QR Chứng nhận & Truy xuất VietGAP: **`216.02 ms` – `229.73 ms`**.
    - Nhân bản trọn vẹn 1 khóa học mới bằng Backup/Restore API: **`2.52 giây`**.
  - **Sẵn sàng Bàn giao Vận hành Thực tế:**
    - Bộ kịch bản **Sao lưu & Phục hồi tự động (`backup.ps1` / `restore.ps1`)** xuất trọn vẹn Database `.sql` (`15.2 MB`) và gói khóa học chuẩn `.mbz`.
    - Bộ **5 tài liệu hướng dẫn Tiếng Việt** đầy đủ cho Quản trị viên, Giảng viên CBKT và Cẩm nang Nông dân.
- **[Lời nói thuyết trình từng câu (Speaker Notes)]:**
  > *"Để đảm bảo chất lượng phần mềm ở mức độ cao nhất trước khi trình hội đồng, nhóm đã xây dựng bộ kịch bản kiểm thử tự động E2E bằng Puppeteer đi qua toàn bộ 6 phân hệ và 51 điểm kiểm tra nghiệm thu: từ đăng nhập 1-chạm, phát video thực tế, đọc PDF trực tiếp, làm bài H5P, đồng bộ điểm SCORM 1.2, mở khóa lộ trình tuần tự, nhân bản khóa học địa phương cho tới kiểm tra chặn quyền bảo mật 403. Kết quả đạt tuyệt đối 51 trên 51 bài kiểm thử PASS với 0 lỗi.*  
  > *Hệ thống cũng đi kèm bộ công cụ sao lưu phục hồi tự động `.sql` và `.mbz` cùng đầy đủ tài liệu bàn giao. Ngay sau đây, em xin phép chuyển sang phần **Demo Live trực tiếp trên hệ thống tại địa chỉ `http://localhost:8090`** để quý Thầy Cô kiểm chứng thực tế ạ!"*

---

## PHẦN 3: KỊCH BẢN DEMO LIVE TRỰC TIẾP TRÊN MÁY CHIẾU (10 PHÚT — ĐI QUA ĐỦ 23 CHỨC NĂNG)

> **Chuẩn bị trước khi lên bục bảo vệ (30 giây):**
> 1. Mở trình duyệt Chrome ở chế độ cửa sổ rộng `1440x900`, bật sẵn 1 tab tại địa chỉ trực tiếp: **`http://localhost:8090/login/index.php?lang=vi`**
> 2. Chuẩn bị sẵn phím tắt `F12` (DevTools Device Toolbar `375x812` iPhone X) khi muốn cho Hội đồng xem giao diện Mobile chỉ trong 1 giây.

---

### 🎬 PHÂN CẢNH 1 (2.0 Phút): Giao diện Đăng nhập, Song ngữ VI/EN & Trải nghiệm Thực địa của Nông dân (`nd_rau_lamdong`)
*(Đi qua các Chức năng: **#1, #2, #3, #5, #6, #7, #8, #23-HTTP**)*

1. **Bước 1.1 — Trình diễn Cổng HTTP Trực tiếp `:8090` & Chuyển đổi Ngôn ngữ 1-Chạm (Chức năng #2 & #23):**
   - **Thao tác:** Chỉ chuột lên thanh địa chỉ trình duyệt `http://localhost:8090/login/index.php?lang=vi` (chứng minh chạy trực tiếp trên cổng HTTP của `moodle_app` theo góp ý của Thầy Dũng).
   - **Click chuột:** Bấm nút **`🇬🇧 EN`** ở góc trên thẻ đăng nhập $\rightarrow$ tiêu đề chuyển ngay sang *"High-Tech Agriculture Training LMS"*. Bấm lại **`🇻🇳 VI`** để về Tiếng Việt.
2. **Bước 1.2 — Đăng nhập 1-Chạm Tức thì vào Vai trò Nông dân (Chức năng #1):**
   - **Click chuột:** Tại khung *"⚡ 1-Chạm Đăng nhập Tức thì"*, bấm nút màu xanh lá **`👨‍🌾 Nông dân (Học & Nhật ký)`** (`#mx-1click-farmer`).
   - **Kết quả trên màn chiếu:** Hệ thống tự động gắn CSRF `logintoken` và điều hướng thẳng vào **Dashboard Nông dân** tại `http://localhost:8090/local/mamxanhapp/index.php`.
3. **Bước 1.3 — Trình diễn Giao diện Mobile, Thanh Điều hướng Đáy & Chế độ Ngoài trời Nắng gắt (Chức năng #3 & #6):**
   - **Click chuột:** Chỉ vào **Bản tin Cảnh báo Thời tiết Tiểu vùng Lâm Đồng** ngay đầu Dashboard và **Thanh điều hướng đáy 5 nút (`bottom-nav-bar`)** kèm cấu hình **PWA (`manifest.json`)**.
   - **Click chuột:** Bấm nút **`☀️ Ngoài trời`** (`#mx-outdoor-toggle`) trên thanh tiện ích đầu trang $\rightarrow$ màn hình chuyển tức thì sang nền trắng `#FFFFFF`, chữ đen `#000000` cỡ **115%**, viền đậm **3px**. Bấm lại **`☀️ Ngoài trời`** một lần nữa để về giao diện xanh tiêu chuẩn.
4. **Bước 1.4 — Hỏi Chatbot AI `"🌱 Bác sĩ Cây trồng 24/7"` (Chức năng #5):**
   - **Click chuột:** Bấm nút nổi **`🌱 Bác sĩ Cây trồng 24/7`** (`#mx-crop-doctor-fab`) ở góc dưới bên phải $\rightarrow$ Bấm chip câu hỏi nhanh **`🧪 pH / EC`** (hoặc **`🍄 Nấm Trichoderma`**).
   - **Kết quả:** Chatbot trả lời ngay phác đồ chuẩn pH `5.8–6.2`, EC `1.2–2.8 mS/cm` và quy tắc pha bồn A – B. Bấm **`✕`** để đóng hộp chat.
5. **Bước 1.5 — Hồ sơ Nông hộ GPS & Bộ 3 Công cụ Tính toán Nông vụ (Chức năng #7 & #8):**
   - **Click chuột:** Bấm mục **`🏡 Nông hộ`** dưới thanh đáy (`http://localhost:8090/local/farmerprofile/index.php`) $\rightarrow$ chỉ nhanh vào diện tích $3.500\text{ m}^2$, tọa độ GPS Đà Lạt và trạng thái tự động đồng bộ vào **`COHORT_LAMDONG`**.
   - **Click chuột:** Bấm mục **`🧮 Công cụ`** dưới thanh đáy (`http://localhost:8090/local/mamxanhapp/calculator.php`):
     - Tại **Tab 1 (`💧 Máy tính Pha Phân & Tưới Nhỏ giọt`)**: Bấm nút tính toán để hiển thị lượng phân Stock A, Stock B và thời gian mở van tưới.
     - Bấm sang **Tab 2 (`🍃 Chẩn đoán Triệu chứng Lá bệnh IPM`)** và **Tab 3 (`🌦️ Trạm Cảnh báo Thời tiết Tiểu vùng`)** để Hội đồng thấy đủ 3 công cụ thực địa.

---

### 🎬 PHÂN CẢNH 2 (3.0 Phút): Học liệu Số hóa Đa phương tiện — Video Thực tế, Xem Trực tiếp PDF, H5P & SCORM 1.2 (Trả bài trực tiếp cho Cô Lương & Thầy Dũng)
*(Đi qua các Chức năng: **#4, #9, #19, #20, #23-Video**)*

1. **Bước 2.1 — Mở Khóa học Cốt lõi `CHAMSOC01` & Giao diện Thẻ lưới Bài học (Chức năng #9):**
   - **Thao tác:** Mở trực tiếp `http://localhost:8090/course/view.php?id=2` (hoặc bấm **Khóa học** dưới thanh đáy $\rightarrow$ chọn khóa **`CHAMSOC01 — Kỹ thuật Chăm sóc Cây trồng Công nghệ cao & Tưới Tiết kiệm`**).
   - **Thuyết minh nhanh:** Chỉ vào các nhãn màu nổi bật trên từng thẻ bài học: `HTML5 MP4`, `PDF EMBED`, `H5P`, `SCORM 1.2`.
2. **Bước 2.2 — Phát Video Nông nghiệp Thực tế HTML5 + Phụ đề Tiếng Việt + Tua Chapter + Giọng đọc AI (Chức năng #4 & #23 — Trả bài Cô Lương):**
   - **Click chuột:** Bấm vào bài **`Video & Infographic: Tiêu chuẩn Cây giống Khỏe & Thang đo Dinh dưỡng pH / EC`** (`http://localhost:8090/mod/page/view.php?id=2`).
   - **Click chuột 1 (Giọng đọc AI `#mx-tts-player`):** Bấm nút **`▶️ Đọc to bài học`** ở hộp xanh đầu bài khoảng 3 giây rồi bấm **`⏹️ Dừng đọc`**.
   - **Click chuột 2 (Video thực tế):** Cuộn xuống khung Video $\rightarrow$ bấm **Play (`▶`)** để phát video tưới nhỏ giọt thực tế có **phụ đề Tiếng Việt (`.vtt`)** chạy trực tiếp trên video $\rightarrow$ bấm nút **`▶ Phần 2: Hệ thống Châm phân`** bên dưới để video tự động tua sang phân đoạn 2 và đổi nội dung hộp **Tóm tắt Kỹ thuật**!
3. **Bước 2.3 — Xem Trực tiếp Sổ tay PDF Không Cần Tải Về (Chức năng #19 — Trả bài Thầy Dũng #1):**
   - **Click chuột:** Mở trực tiếp `http://localhost:8090/mod/resource/view.php?id=37` (hoặc quay lại khóa học bấm nút **`📖 Xem trực tiếp PDF (Không tải về)`** tại bài *Sổ tay Kỹ thuật Canh tác Nông nghiệp CNC & VietGAP*).
   - **Click chuột:** Cuộn tới khung đọc **`#mx-inline-pdf-viewer`** (cao `650px`) $\rightarrow$ bấm nút **`➕ Phóng to`** trên thanh công cụ màu xanh đậm để tỷ lệ phóng lên **`120%`**, cuộn dọc qua 3 trang tài liệu Tiếng Việt sắc nét, rồi bấm **`↔️ Vừa khung`** (`100%`).
   - **Điểm nhấn với Thầy Dũng:** *"Thưa Thầy, module `mod_resource` được đặt `display = RESOURCELIB_DISPLAY_EMBED` (giá trị `1`) kết hợp `PDF.js` nên bà con đọc trực tiếp ngay trên trang bài học mà không phải tải file PDF về máy."*
4. **Bước 2.4 — Tương tác Hoạt động H5P Thật & Lưu điểm vào Gradebook (Chức năng #20a — Trả bài Thầy Dũng #2a):**
   - **Click chuột:** Mở `http://localhost:8090/mod/h5pactivity/view.php?id=38` (*Bài tập H5P Tương tác: Chẩn đoán Dinh dưỡng pH/EC & Sâu bệnh Sinh học IPM*).
   - **Click chuột:** Trong khung H5P Question Set, chọn đáp án đầu tiên **`pH từ 5.8 đến 6.2`** $\rightarrow$ bấm nút **`Kiểm tra đáp án`** $\rightarrow$ hệ thống hiện ngôi sao `+1` và phản hồi màu xanh *"Chính xác! Ở pH 5.8–6.2, cả đa lượng và vi lượng đều ở dạng ion hòa tan..."* đồng thời gửi sự kiện xAPI về Gradebook.
5. **Bước 2.5 — Học Gói Bài giảng Chuẩn Quốc tế ADL SCORM 1.2 & Đồng bộ Điểm 100/100 (Chức năng #20b — Trả bài Thầy Dũng #2b):**
   - **Click chuột:** Mở `http://localhost:8090/mod/scorm/view.php?id=39` (*Bài giảng Chuẩn SCORM 1.2: Quy trình Thực hành Nông nghiệp Tốt VietGAP & Kiểm soát Mối nguy*) $\rightarrow$ bấm nút **`Bắt đầu`** (Enter) để vào trình phát SCORM (`/mod/scorm/player.php`).
   - **Click chuột:** Chỉ cho Thầy Dũng thấy nhãn màu xanh lá **`🟢 Đã kết nối Moodle SCORM 1.2 API (LMSInitialize OK)`** ở góc trên bên phải gói SCORM $\rightarrow$ tích chọn đáp án đầu tiên cho cả 4 câu hỏi (Câu 1, Câu 2, Câu 3, Câu 4) $\rightarrow$ bấm nút **`✅ Hoàn thành Bài học & Đồng bộ Điểm SCORM 1.2 về Moodle (100 điểm)`**.
   - **Kết quả trên màn chiếu:** Hộp kết quả hiện ngay **`🏅 HOÀN THÀNH XUẤT SẮC: 100/100 điểm • Trạng thái SCORM: COMPLETED`** kèm log giao tiếp thực tế `LMSSetValue("cmi.core.score.raw", 100) -> true` và `LMSCommit("") & LMSFinish("") -> Đã đồng bộ về Moodle Gradebook!`.

---

### 🎬 PHÂN CẢNH 3 (2.5 Phút): Lộ trình Tuần tự Khóa học Mẫu (`MASTER-AGRITECH-TPL`), Thực hành Vườn, Thi Quiz, Chứng nhận Số QR & Nhật ký VietGAP
*(Đi qua các Chức năng: **#10, #11, #12, #18, #21**)*

1. **Bước 3.1 — Trình diễn Khóa học Mẫu `MASTER-AGRITECH-TPL` & Lộ trình Mở khóa Tuần tự 4 Bước (Chức năng #21 — Trả bài Thầy Dũng #3a):**
   - **Click chuột:** Mở `http://localhost:8090/course/view.php?id=8` (*`[MẪU CHUẨN] Khung Chương trình Tập huấn Nông nghiệp Công nghệ cao (Master Template)`*).
   - **Click chuột:** Chỉ vào thanh **Sơ đồ Lộ trình Học tập Tuần tự (`Bước 1 → Bước 2 → Bước 3 → Bước 4 → Bước 5: Chứng nhận Số QR`)** ngay đầu trang và cuộn xuống cho Hội đồng thấy các chuyên đề sau đang hiển thị biểu tượng **`🔒 Lộ trình Tuần tự (Chưa mở khóa)`** do Moodle `Restrict Access` kiểm soát.
   - **Thao tác mở khóa trực tiếp:** Bấm mở nhanh **Bước 1.1 (Video)** (`http://localhost:8090/mod/page/view.php?id=45`) và **Bước 1.2 (Xem trực tiếp PDF)** (`http://localhost:8090/mod/resource/view.php?id=46`), sau đó bấm **`← Quay lại khóa học`** (`http://localhost:8090/course/view.php?id=8`) $\rightarrow$ **Chuyên đề 2 (H5P & SCORM 1.2) đã tự động mở khóa màu xanh (`✅ Bước lộ trình tuần tự đang mở`)** và thanh tiến độ nhảy lên **`29%`**!
2. **Bước 3.2 — Điểm danh Thực hành tại Vườn bằng QR Xoay vòng + GPS (Chức năng #10):**
   - **Click chuột:** Mở hoạt động Thực hành tại vườn của khóa `CHAMSOC01` tại `http://localhost:8090/mod/fieldpractice/view.php?id=5`.
   - **Click chuột:** Chỉ vào mã QR xoay vòng 5 phút và tọa độ GPS hiện trường $\rightarrow$ bấm nút **`📍 Xác nhận Điểm danh Tại Vườn`** $\rightarrow$ hệ thống báo điểm danh thành công kèm khoảng cách mét tới tâm vườn mẫu.
3. **Bước 3.3 — Kiểm tra Cuối khóa 12 Câu (`mod_quiz`) & Nhận Chứng nhận Số QR `HMAC-SHA256` (Chức năng #11 & #18a):**
   - **Click chuột:** Mở trang Chứng nhận của nông dân tại `http://localhost:8090/local/mamxanhcert/index.php` (hoặc bấm mục **`🎓 Chứng nhận`** dưới thanh đáy).
   - **Click chuột:** Bấm **`👁️ Xem & In Chứng nhận`** để mở bản thiết kế chứng nhận trang trọng có mã QR và chữ ký số `HMAC-SHA256`; bấm **`📥 Tải PDF`** để tải file PDF chứng nhận Tiếng Việt; bấm **`🔍 Xác thực QR Công khai`** (`http://localhost:8090/local/mamxanhcert/verify.php?code=MXCERT-2026-CHEBIEN01-0043`) để thấy trạng thái **`✅ XÁC THỰC HỢP LỆ — CHỮ KÝ SỐ HMAC-SHA256 CHÍNH XÁC`**.
4. **Bước 3.4 — Ghi Nhật ký Canh tác bằng Giọng nói & Hộ chiếu Truy xuất VietGAP (Chức năng #12 & #18b):**
   - **Click chuột:** Bấm mục **`📒 Nhật ký`** dưới thanh đáy (`http://localhost:8090/local/agriops/index.php`).
   - **Click chuột:** Tại khung *Ghi Nhật ký Canh tác Hàng ngày*, bấm nút **`🎙️ Bấm để Đọc bằng Giọng nói (Tiếng Việt)`** (hoặc nhập nhanh thông số pH `6.0`, EC `1.8`, PHI `7 ngày`) rồi bấm **Lưu Nhật ký**.
   - **Click chuột:** Bấm vào nút **`🌐 Xem Hộ chiếu Truy xuất QR`** của lô nông sản (`http://localhost:8090/local/agriops/trace.php?code=VG-LD-202611-0001`) để hiển thị trang Hộ chiếu Nông sản VietGAP công khai dành cho người tiêu dùng và siêu thị.

---

### 🎬 PHÂN CẢNH 4 (2.5 Phút): Góc nhìn Cán bộ Kỹ thuật (`cbkt_pilot`), Nhân bản Khóa học 1-Chạm & Quản trị Hệ thống (`admin`)
*(Đi qua các Chức năng: **#13, #14, #15, #16, #17, #22**)*

1. **Bước 4.1 — Đăng nhập 1-Chạm vào Cán bộ Kỹ thuật (`cbkt_pilot`) & Xem Thống kê Vùng trồng (Chức năng #13):**
   - **Click chuột:** Đăng xuất (hoặc mở `http://localhost:8090/login/index.php?lang=vi`) $\rightarrow$ bấm nút màu cam **`👨‍🔬 Cán bộ Kỹ thuật (Chấm & Báo cáo)`** (`#mx-1click-cbkt`).
   - **Kết quả:** Hệ thống tự động đưa thẳng vào **Trung tâm Điều hành & Báo cáo Tập huấn (`http://localhost:8090/local/mamxanhapp/manage.php`)**.
   - **Click chuột:** Chỉ vào 6 thẻ KPI, biểu đồ so sánh 3 vùng trồng (**Lâm Đồng, Đắk Lắk, Đồng Nai**), bấm lọc thử vùng **Lâm Đồng**, sau đó bấm nút **`📥 Xuất Báo cáo Excel (CSV UTF-8)`** (`http://localhost:8090/local/mamxanhapp/manage.php?download=csv&lang=vi`) để tải file CSV chuẩn UTF-8 BOM về máy.
2. **Bước 4.2 — Chấm điểm Thực hành Vườn theo Rubric 4 Tiêu chí đẩy thẳng vào Gradebook (Chức năng #14):**
   - **Click chuột:** Truy cập `http://localhost:8090/mod/fieldpractice/grade.php?id=5` (hoặc bấm từ hoạt động Thực hành tại vườn).
   - **Click chuột:** Điều chỉnh điểm 4 tiêu chí (`TC1..TC4`, mỗi tiêu chí 0–25đ) cho nông dân `nd_rau_lamdong` $\rightarrow$ bấm **`💾 Lưu điểm`** $\rightarrow$ hệ thống hiển thị thông báo xanh xác nhận đã đẩy điểm tổng `100/100` thẳng vào **Moodle Gradebook**.
3. **Bước 4.3 — Nhân bản Khóa học Mẫu 1-Chạm theo Địa phương ngay trước mặt Thầy Dũng (Chức năng #22 & #15 — Trả bài Thầy Dũng #3b):**
   - **Click chuột:** Từ Dashboard CBKT `manage.php`, bấm nút nổi bật **`🧬 Nhân bản Khóa học Mẫu (1-Click Cloner)`** để mở `http://localhost:8090/local/mamxanhapp/clone_course.php`.
   - **Click chuột:**
     1. Ô *Khóa học gốc*: Giữ mặc định **`[MASTER-AGRITECH-TPL] [MẪU CHUẨN] Khung Chương trình Tập huấn Nông nghiệp Công nghệ cao`**.
     2. Ô *Tên Lớp Tập huấn Địa phương Mới*: Nhập (hoặc giữ sẵn) **`Tập huấn Nông dân Đơn Dương - Lâm Đồng Đợt 3`**.
     3. Ô *Gán Tổ Hợp tác / Nhóm Nông dân (Cohort)*: Chọn **`📍 Tổ hợp tác Vùng trồng Lâm Đồng (Rau Hoa & Nhà màng IoT) (5 nông hộ)`**.
     4. Bấm nút lớn **`🧬 Nhân bản ngay (Clone Khóa học Địa phương)`** (`#mx-clone-submit-btn`).
   - **Kết quả ấn tượng sau ~2.5 giây:** Xuất hiện thẻ xanh **`✅ NHÂN BẢN THÀNH CÔNG 100%`** liệt kê đầy đủ số học viên đã ghi danh tự động, 3 chuyên đề đã tự động ánh xạ ID mở khóa tuần tự và đủ các module `PAGE`, `RESOURCE (PDF)`, `H5PACTIVITY`, `SCORM`, `FIELDPRACTICE`, `QUIZ`.
   - **Click chuột:** Bấm nút **`🚀 Mở Khóa học Địa phương Vừa Tạo →`** để mở ngay khóa học mới tạo hoàn chỉnh 100%!
4. **Bước 4.4 — Kiểm toán Hạ tầng Admin (`admin_audit.php`) & Bộ Công cụ Sao lưu `.sql` / `.mbz` (Chức năng #16 & #17):**
   - **Click chuột:** Đăng nhập 1-chạm vào **`🛡️ Quản trị viên Hệ thống (`admin`)`** $\rightarrow$ mở `http://localhost:8090/local/mamxanhapp/admin_audit.php` để hiển thị trạng thái OPcache JIT `1255`, Redis 7 MUC Cache, khóa ký số `HMAC-SHA256` và danh sách 5 Custom Plugins.
   - **Trình diễn file bàn giao:** Mở nhanh thư mục `backups/` (chứa file `mamxanh_lms_latest.sql` `15.2 MB` và các gói khóa học `.mbz`) cùng thư mục `docs/` (5 tài liệu hướng dẫn) để kết thúc phần Demo Live tròn trịa trong 10 phút!

---

## PHẦN 4: BỘ 10 CÂU HỎI PHẢN BIỆN XOÁY SÂU & CÂU TRẢ LỜI MẪU GHI ĐIỂM

> Dưới đây là 10 câu hỏi chuyên môn sâu sát nhất mà **Cô Lương, Thầy Dũng, Thầy Thắng và Thầy Khánh** thường đặt ra trong buổi bảo vệ, kèm câu trả lời chuẩn kiến trúc sư hệ thống dẫn chứng trực tiếp tới tên file, hàm API và bảng dữ liệu trong dự án.

---

### Câu hỏi 1 (Thầy Dũng / Thầy Thắng): Tại sao nhóm chọn tùy biến trên nền tảng Moodle LMS mà không tự viết mới từ đầu bằng Laravel hay Node.js? Nhóm đã can thiệp vào những tầng nào của Moodle và làm sao để khi nâng cấp Moodle Core không bị mất code?
**🎯 Câu trả lời mẫu ghi điểm:**
> *"Dạ thưa Thầy, nhóm quyết định chọn xây dựng trên lõi **Moodle 4.5 LTS** thay vì viết một website CRUD từ đầu vì 3 lý do cốt lõi của kỹ thuật phần mềm:*  
> *1. **Thừa hưởng các chuẩn e-Learning quốc tế phức tạp:** Một hệ thống LMS thực thụ cần bộ máy quản lý Ngân hàng câu hỏi đa phiên bản (`core_question`), Sổ điểm trọng số (`Gradebook API`), Điều kiện hoàn thành & Mở khóa tuần tự (`Completion & Availability API`), trình chạy chuẩn quốc tế **H5P (`core_h5p`)**, **ADL SCORM 1.2 (`mod_scorm`)** và cơ chế đóng gói sao lưu khóa học **Moodle2 Backup/Restore (`.mbz`)**. Việc kế thừa lõi Moodle giúp các tính năng này đạt độ ổn định chuẩn quốc tế ngay lập tức.*  
> *2. **Nguyên tắc Kiến trúc "Zero Core Hack" (Không sửa mã nguồn lõi):** Toàn bộ 100% tính năng mới của nhóm được đóng gói tách biệt hoàn toàn vào **6 thành phần mở rộng chuẩn Moodle Plugin API** nằm trong thư mục `moodle_custom/`:*
>    - *1 Child Theme: `theme/agritrain_child` (kế thừa `theme_boost` qua `config.php` và ghi đè `drawers.mustache`, `loginform.mustache`).*
>    - *1 Activity Module: `mod/fieldpractice` (tuân thủ đầy đủ `lib.php`, `grade_update()`, `backup/moodle2/` và `db/install.xml`).*
>    - *4 Local Plugins: `local/mamxanhapp`, `local/farmerprofile`, `local/agriops`, `local/mamxanhcert` (sử dụng Event Observers tại `db/events.php` và Capabilities tại `db/access.php`).*  
> *3. **Khả năng nâng cấp bền vững:** Vì nhóm tuyệt đối không chỉnh sửa bất kỳ file nào của Moodle Core, khi nâng cấp lên bản vá bảo mật mới của Moodle, toàn bộ 6 plugin và cấu trúc bảng riêng (`mdl_local_*`, `mdl_fieldpractice*`) đều được bảo toàn 100% thông qua cơ chế `admin/cli/upgrade.php`."*

---

### Câu hỏi 2 (Thầy Dũng): Hãy giải thích cơ chế kỹ thuật giúp học viên xem trực tiếp tài liệu PDF ngay trong trang bài học Moodle mà không cần tải file về máy? Nếu trình duyệt trên điện thoại nông dân không có PDF Viewer mặc định thì sao?
**🎯 Câu trả lời mẫu ghi điểm:**
> *"Dạ thưa Thầy, để đảm bảo 100% thiết bị (kể cả WebView trên điện thoại Android đời cũ không có trình đọc PDF встроенный) đều xem trực tiếp được tài liệu PDF ngay trong trang bài học mà không bị bật hộp thoại tải file về máy, nhóm đã kết hợp **2 tầng xử lý ở cả Backend và Frontend**:*  
> *1. **Ở tầng Backend Moodle (`scripts/cli/stage7_council_upgrades.php`):** Khi khởi tạo hoạt động `mod_resource`, nhóm thiết lập thuộc tính hiển thị `display = RESOURCELIB_DISPLAY_EMBED` (tương ứng hằng số `1` trong `/lib/resourcelib.php`). Với giá trị này, Moodle không gửi HTTP Header `Content-Disposition: attachment` mà gọi hàm `resourcelib_embed_pdf()` để nhúng đường dẫn `pluginfile.php` vào vùng nội dung `.resourcecontent.resourcepdf`.*  
> *2. **Ở tầng Frontend (`moodle_custom/theme/agritrain_child/templates/drawers.mustache` & `scss/post.scss`):** Nhóm tích hợp bộ thư viện tự chủ **Mozilla PDF.js (`v3.11.174`)** tại `/local/mamxanhapp/assets/pdfjs/pdf.min.js` (nạp qua RequireJS AMD module `'pdfjs-dist/build/pdf'` tương thích hoàn toàn với trình nạp AMD của Moodle). Script tự động đọc luồng byte PDF qua phiên đăng nhập (`withCredentials: true`) và vẽ từng trang tài liệu lên các thẻ `<canvas class="mx-pdf-page-canvas">` độ phân giải cao (`devicePixelRatio`) bên trong khung cuộn `#mx-pdf-viewport` có chiều cao cố định **`650px` trên Desktop** và **`480px` trên Mobile**.*  
> *Nhờ vẽ trực tiếp lên HTML5 Canvas kèm các nút điều khiển `updateZoom()` (`➖ Thu nhỏ`, `➕ Phóng to 120%–220%`, `↔️ Vừa khung`, `🖥️ Toàn màn hình`), nông dân đọc mượt mà trên mọi điện thoại mà không bao giờ phải tải file PDF về máy."*

---

### Câu hỏi 3 (Thầy Dũng): Gói bài giảng chuẩn SCORM 1.2 của nhóm giao tiếp với Moodle như thế nào? Khi học viên bấm nộp bài trong iframe SCORM, điểm số đi qua những hàm nào để vào được Sổ điểm Moodle Gradebook?
**🎯 Câu trả lời mẫu ghi điểm:**
> *"Dạ thưa Thầy, gói bài giảng `SCORM12_VietGAP_KiemSoat_MoiNguy.zip` được nhóm đóng gói tuân thủ nghiêm ngặt đặc tả **ADL SCORM 1.2 Run-Time Environment**:*  
> *1. **Cấu trúc gói (`scripts/cli/stage7_package_builders.php`):** Gồm file khai báo `imsmanifest.xml` định nghĩa `<organization>` và `<resource identifier="RES_VIETGAP_SCO1" type="webcontent" adlcp:scormtype="sco" href="index.html">`, bộ điều hợp `scorm_api.js` và giao diện bài học `index.html`.*  
> *2. **Cơ chế bắt tay tìm API (`findAPI(window)` trong `scorm_api.js`):** Khi Moodle mở `/mod/scorm/player.php`, Moodle khởi tạo đối tượng JavaScript `window.API` (Moodle SCORM 1.2 API Adapter) ở cửa sổ cha và nạp gói bài giảng vào `#scorm_object` iframe. Ngay sự kiện `onload`, hàm `MXScorm12.init()` duyệt ngược chuỗi `window.parent` để tìm đối tượng `window.API` và gọi:*
>    - `API.LMSInitialize("")`: Mở phiên giao tiếp SCORM với Moodle (nhãn trạng thái trên giao diện chuyển sang `🟢 Đã kết nối Moodle SCORM 1.2 API`).
>    - `API.LMSSetValue("cmi.core.lesson_status", "incomplete")`, `score.min = "0"`, `score.max = "100"` và `API.LMSCommit("")`.  
> *3. **Đồng bộ điểm và trạng thái hoàn thành (`completeWithScore`):** Khi học viên trả lời 4 câu hỏi kiểm soát mối nguy VietGAP và bấm nộp, `scorm_api.js` tính điểm phần trăm và gọi tuần tự:*
>    - `API.LMSSetValue("cmi.core.score.raw", "100")`
>    - `API.LMSSetValue("cmi.core.lesson_status", "completed")`
>    - `API.LMSSetValue("cmi.core.session_time", "0000:00:xx")`
>    - `API.LMSCommit("")` $\rightarrow$ kích hoạt AJAX POST của Moodle tới `/mod/scorm/datamodel.php`, ghi vào bảng `mdl_scorm_scoes_track`, sau đó gọi `scorm_update_grades()` cập nhật thẳng điểm `100` vào bảng `mdl_grade_grades` (Gradebook) và đánh dấu hoàn thành hoạt động trước khi đóng phiên bằng `API.LMSFinish("")`."*

---

### Câu hỏi 4 (Thầy Dũng): Trong chức năng "Nhân bản Khóa học Mẫu 1-Chạm" (`clone_course.php`), làm sao hệ thống sao chép được toàn bộ các điều kiện mở khóa tuần tự (Restrict Access) mà không bị trỏ nhầm về ID bài học của khóa gốc?
**🎯 Câu trả lời mẫu ghi điểm:**
> *"Dạ thưa Thầy, đây là điểm khác biệt lớn nhất giữa việc dùng **Moodle Backup & Restore Controller API** chính quy so với việc copy bảng dữ liệu bằng SQL thông thường.*  
> *Trong khóa học mẫu `MASTER-AGRITECH-TPL`, cột `availability` của Chuyên đề 2 lưu chuỗi JSON dạng:*  
> `{"op":"&","c":[{"type":"completion","cm":45,"e":1},{"type":"completion","cm":46,"e":1}]}` *(trong đó `45` và `46` là `coursemoduleid` của bài Video và bài PDF ở khóa gốc).*  
> *Nếu chỉ copy bản ghi SQL sang khóa mới, `cm: 45` vẫn trỏ về khóa học gốc nên học viên ở lớp địa phương mới học xong cũng không mở khóa được Chuyên đề 2.*  
> *Để giải quyết triệt để, trong file `moodle_custom/local/mamxanhapp/clone_course.php`, nhóm sử dụng quy trình 3 bước của Moodle Core:*  
> *1. Khởi tạo `\backup_controller(backup::TYPE_1COURSE, $sourcecourse->id, ..., backup::MODE_SAMESITE)` với cờ `'activities' => 1, 'users' => 0` và thực thi `execute_plan()` để đóng gói toàn bộ cấu trúc khóa học, file PDF, gói H5P, gói SCORM, ngân hàng câu hỏi và cây điều kiện `availability`.*  
> *2. Tạo khung khóa học địa phương mới qua `restore_dbops::create_new_course()` và chạy `\restore_controller($backupid, $newcourseid, ..., backup::TARGET_NEW_COURSE)`.*  
> *3. Trong pha cuối của `restore_controller::execute_plan()`, bước `restore_update_availability` của Moodle tự động tra bảng ánh xạ tạm `mdl_backup_ids_temp` để **viết lại (remap) toàn bộ mã `cm` cũ sang mã `coursemoduleid` mới tương ứng của khóa học địa phương vừa tạo**, đồng thời ánh xạ lại cả `grade_item id` cho điều kiện mở khóa Chứng nhận $\ge 70\%$. Nhờ vậy chỉ mất khoảng **2,5 giây**, khóa học địa phương mới hoạt động tuần tự chính xác 100% và tự động ghi danh luôn toàn bộ nông dân trong Cohort vùng trồng được chọn."*

---

### Câu hỏi 5 (Cô Lương): Các hoạt động H5P trong hệ thống được đóng gói như thế nào? Điểm khác biệt giữa H5P trong `mod_h5pactivity` hiện tại so với các slide HTML/JS ở phiên bản trước là gì?
**🎯 Câu trả lời mẫu ghi điểm:**
> *"Dạ thưa Cô, ở phiên bản trước các slide tương tác được viết bằng HTML/JS đặt trong `mod_page` nên chỉ phục vụ tự ôn tập tại chỗ mà không đẩy được điểm từng câu hỏi về Sổ điểm trung tâm của Moodle.*  
> *Ở phiên bản hiện tại (`scripts/cli/stage7_package_builders.php`), nhóm đã nạp hơn **40 thư viện H5P chuẩn quốc tế** vào bảng `mdl_h5p_libraries` (như `H5P.QuestionSet 1.20`, `H5P.MultiChoice 1.16`, `H5P.CoursePresentation 1.26`, `H5P.Accordion 1.0`) và đóng gói các tệp `.h5p` chuẩn (gồm `h5p.json` khai báo `preloadedDependencies` và `content/content.json` chứa dữ liệu câu hỏi nông nghiệp Tiếng Việt).*  
> *Khi nạp vào Moodle qua module chính quy **`mod_h5pactivity`** (bật `enabletracking = 1`, `grademethod = 1`, `grade = 100`), mỗi khi nông dân hoàn thành bài tập H5P, trình phát H5P phát sinh chuẩn bản tin **xAPI (`answered` / `completed`)** có chứa `result.score.raw` và `result.score.max`. Moodle lắng nghe sự kiện xAPI này, lưu chi tiết từng lần làm bài vào bảng `mdl_h5pactivity_attempts` / `mdl_h5pactivity_attempts_results` và tự động cập nhật điểm số vào **Moodle Gradebook**."*

---

### Câu hỏi 6 (Thầy Thắng / Thầy Khánh): Hãy trình bày thuật toán sinh và xác thực chữ ký số trên Chứng nhận Số QR (`local_mamxanhcert`) và Mã QR điểm danh tại vườn (`mod_fieldpractice`)? Nếu học viên sửa điểm trong URL hoặc chụp ảnh mã QR điểm danh gửi về nhà cho người khác điểm danh hộ thì hệ thống chặn bằng cách nào?
**🎯 Câu trả lời mẫu ghi điểm:**
> *"Dạ thưa Thầy, nhóm áp dụng cơ chế mật mã **`HMAC-SHA256` (Hash-based Message Authentication Code)** cho cả hai nghiệp vụ này với hai mục tiêu bảo mật khác nhau:*  
> *1. **Đối với Chứng nhận Số QR (`moodle_custom/local/mamxanhcert/lib.php`):***
>    - *Khi học viên đạt Quiz $\ge 70\%$, hệ thống tạo bản ghi chứng nhận và tính chữ ký số:*  
>      $$\text{Signature} = \text{HMAC\_SHA256}(\text{cert\_code} \;\|\; \text{userid} \;\|\; \text{courseid} \;\|\; \text{final\_score} \;\|\; \text{timecreated},\; \text{MAMXANH\_HMAC\_SECRET})$$
>    - *Tại trang tra cứu công khai `/local/mamxanhcert/verify.php`, hệ thống chỉ nhận `code` từ URL, truy vấn bản ghi gốc trong CSDL và **tính toán lại giá trị băm HMAC-SHA256 ở phía Server** rồi so sánh hằng thời gian (`hash_equals`) với chữ ký đã lưu. Do điểm số không lấy từ tham số URL và khóa bí mật `MAMXANH_HMAC_SECRET` chỉ nằm trong biến môi trường Server, không ai có thể giả mạo mã chứng nhận hoặc can thiệp sửa điểm.*  
> *2. **Đối với Mã QR Điểm danh Thực hành Vườn Xoay vòng (`moodle_custom/mod/fieldpractice/lib.php`):***
>    - *Để chống việc chụp ảnh mã QR gửi qua Zalo cho người ở nhà điểm danh hộ, mã phiên 6 ký tự được gắn với **cửa sổ thời gian 5 phút (`300 giây`)** tương tự cơ chế TOTP:*  
>      $$\text{Token}_{t} = \text{UPPER}\!\left(\text{SUBSTR}\!\left(\text{HMAC\_SHA256}\!\left(\text{qr\_secret},\; \lfloor \text{time}() / 300 \rfloor\right),\; 0,\; 6\right)\right)$$
>    - *Sau 5 phút mã cũ tự động hết hạn, kết hợp kiểm tra tọa độ định vị **GPS (`latitude`, `longitude`)** của thiết bị so với tâm vườn mẫu (`target_lat`, `target_lng`), đảm bảo học viên phải có mặt thực tế tại vườn."*

---

### Câu hỏi 7 (Cô Lương): Tính năng Ghi nhật ký canh tác bằng giọng nói (`local_agriops`) và Trợ lý đọc bài giảng (`#mx-tts-player`) hoạt động theo cơ chế nào? Có tốn phí API đám mây hàng tháng không?
**🎯 Câu trả lời mẫu ghi điểm:**
> *"Dạ thưa Cô, để hệ thống có thể vận hành bền vững, hoàn toàn miễn phí cho các Hợp tác xã nông nghiệp mà không phát sinh chi phí thuê bao API đám mây theo từng phút âm thanh, nhóm sử dụng chuẩn **W3C Web Speech API** tích hợp sẵn trong trình duyệt di động hiện đại:*  
> *1. **Chiều Tổng hợp Giọng nói — Text-to-Speech (`#mx-tts-player` trong `drawers.mustache`):** Sử dụng đối tượng `window.speechSynthesis` và `SpeechSynthesisUtterance`. Khi nông dân bấm **Đọc to bài học**, hàm `mxSpeakLesson()` tự động lọc lấy văn bản chuyên môn trong `#region-main`, nhận diện ngôn ngữ hiện tại (`vi-VN` hoặc `en-US`) và đọc theo tốc độ người dùng chọn (`0.85x`, `1.0x`, `1.15x`).*  
> *2. **Chiều Nhận diện Giọng nói — Speech-to-Text (`moodle_custom/local/agriops/index.php`):** Sử dụng `webkitSpeechRecognition` / `SpeechRecognition` với cấu hình `lang = 'vi-VN'`, `continuous = false`, `interimResults = true`. Khi nông dân bấm nút Micro ngoài ruộng và nói nội dung công việc chăm sóc cây, trình duyệt chuyển trực tiếp giọng nói thành chuỗi văn bản Tiếng Việt đổ vào ô nhật ký và đánh dấu cờ `voice_input = 1` trong bảng `mdl_local_agriops_logs`."*

---

### Câu hỏi 8 (Thầy Khánh): Tại sao khi xuất báo cáo tiến độ ra file CSV thường bị lỗi phông chữ Tiếng Việt khi mở bằng Microsoft Excel trên Windows, và nhóm đã xử lý vấn đề này trong `manage.php` như thế nào?
**🎯 Câu trả lời mẫu ghi điểm:**
> *"Dạ thưa Thầy, nguyên nhân khiến Microsoft Excel trên Windows hiển thị lỗi chữ Tiếng Việt khi mở file `.csv` mã hóa UTF-8 thông thường là vì nếu không có ký hiệu nhận diện đầu file, Excel mặc định đọc file theo bảng mã legacy `Windows-1252` (ANSI) thay vì `UTF-8`.*  
> *Để khắc phục triệt để 100%, trong file `moodle_custom/local/mamxanhapp/manage.php` (khi nhận tham số `download=csv`), trước khi gọi `fputcsv()` để ghi dòng tiêu đề cột và 15 dòng dữ liệu nông hộ, nhóm ghi trực tiếp **3 byte chuỗi nhận diện UTF-8 BOM (Byte Order Mark)** ra đầu luồng xuất:*  
> `echo "\xEF\xBB\xBF";`  
> *Trong kịch bản kiểm thử tự động `tests/e2e/run_e2e_and_benchmark.mjs` (Mục 4), nhóm cũng viết assertion kiểm tra trực tiếp 3 byte đầu tiên của HTTP response body (`body[0] === 0xEF && body[1] === 0xBB && body[2] === 0xBF`) cho cả bản xuất Tiếng Việt (`lang=vi`) và Tiếng Anh (`lang=en`), đảm bảo Cán bộ kỹ thuật tải về nhấp đúp mở bằng Excel là hiển thị chuẩn dấu Tiếng Việt ngay lập tức."*

---

### Câu hỏi 9 (Thầy Thắng): Làm thế nào hệ thống vừa chạy được trực tiếp qua cổng HTTP `http://localhost:8090` lại vừa chạy được qua HTTPS `https://moodle.lvh.me:8443` mà Moodle không bị lỗi chuyển hướng vòng lặp (Redirect Loop) hoặc xung đột Cookie phiên đăng nhập?
**🎯 Câu trả lời mẫu ghi điểm:**
> *"Dạ thưa Thầy, bình thường Moodle yêu cầu khai báo cứng một hằng số `$CFG->wwwroot` duy nhất trong `config.php`, và nếu bật `$CFG->sslproxy = true` thì mọi truy cập qua HTTP thuần sẽ bị Moodle ép redirect sang HTTPS hoặc bị trình duyệt từ chối lưu Cookie có cờ `Secure`.*  
> *Để hỗ trợ đồng thời cả hai chế độ theo yêu cầu của Hội đồng, trong file `moodle_custom/config.php` (dòng 26–45), nhóm đã thiết kế bộ nhận diện giao thức động an toàn (có kiểm tra biểu thức chính quy whitelist host `moodle.lvh.me | localhost | 127.0.0.1`):*
> - *Khi truy cập qua `http://localhost:8090`: Hệ thống phát hiện giao thức HTTP thuần (`$ishttps === false`), tự động gán `$CFG->wwwroot = 'http://localhost:8090'`, tắt `$CFG->sslproxy = false`, tắt cờ `$CFG->cookiesecure = false` và **đặt tên hậu tố Cookie riêng biệt `$CFG->sessioncookie = 'directhttp'` (`MoodleSessiondirecthttp`)**.*
> - *Nhờ tách riêng tên Cookie phiên giữa HTTP (`MoodleSessiondirecthttp`) và HTTPS (`MoodleSession`), trình duyệt không bao giờ bị xung đột Cookie giữa hai cổng và cả hai cổng đều đạt tốc độ phản hồi ~91–104 ms."*

---

### Câu hỏi 10 (Cô Lương / Thầy Dũng): Nếu ngày mai bàn giao hệ thống này cho một Hợp tác xã hoặc Trung tâm Khuyến nông địa phương sử dụng thực tế, quy trình sao lưu dữ liệu và mở rộng thêm khóa học mới sẽ diễn ra như thế nào?
**🎯 Câu trả lời mẫu ghi điểm:**
> *"Dạ thưa Cô và Thầy, nhóm đã chuẩn bị sẵn quy trình bàn giao khép kín gồm **Công cụ Nhân bản trên Giao diện Web**, **Bộ Script Sao lưu Tự động** và **Bộ 5 Tài liệu Hướng dẫn Tiếng Việt**:*  
> *1. **Về mở rộng khóa học mới:** Cán bộ kỹ thuật của Trung tâm Khuyến nông không cần biết lập trình. Họ chỉ cần vào `/local/mamxanhapp/clone_course.php`, chọn khóa mẫu `MASTER-AGRITECH-TPL`, nhập tên lớp tập huấn của xã/huyện mới và chọn nhóm nông dân. Sau 2,5 giây đã có ngay khóa học mới đầy đủ lộ trình 4 bước, sau đó bật nút **Chế độ chỉnh sửa (Edit mode)** để thay đổi video, file PDF hoặc câu hỏi nếu muốn.*  
> *2. **Về sao lưu và phục hồi thảm họa (`scripts/backup/`):** Quản trị viên chỉ cần chạy một lệnh duy nhất `powershell -File .\scripts\backup\backup.ps1` (hoặc `backup.sh` trên Linux). Hệ thống tự động xuất toàn bộ cơ sở dữ liệu MariaDB ra file `backups/mamxanh_lms_latest.sql` (đầy đủ `--single-transaction --routines --triggers`) đồng thời đóng gói các khóa học ra chuẩn `.mbz` của Moodle. Khi chuyển sang máy chủ mới, chỉ cần chạy `restore.ps1` là khôi phục nguyên trạng 100% dữ liệu trong chưa đầy 1 phút."*

---

## PHỤ LỤC: DANH MỤC 32 ẢNH CHỤP MÀN HÌNH NGHIỆM THU (`docs/screenshots/`) ĐỂ ĐƯA VÀO SLIDE POWERPOINT

| Mã Ảnh | Tên File trong `docs/screenshots/` | Nội dung Minh chứng | Dùng cho Slide |
| :-: | :--- | :--- | :-: |
| `01` | `01_login_desktop_vi.png` | Giao diện Đăng nhập Desktop bo góc 24px, 1-Click Login & Song ngữ VI/EN | Slide 1 |
| `02` | `02_login_mobile_vi.png` | Giao diện Đăng nhập Mobile `375x812` chuẩn chạm WCAG AAA $\ge 48\text{px}$ | Slide 1 |
| `03` | `03_farmer_dashboard_mobile.png` | Dashboard Nông dân trên Mobile `375x812` & Thanh điều hướng đáy 5 nút | Slide 4 |
| `04` | `04_farmer_dashboard_desktop.png` | Dashboard Nông dân trên Desktop `1440x900` & Cảnh báo thời tiết tiểu vùng | Slide 3 |
| `05` | `05_farmer_outdoor_sunlight_mode.png` | Chế độ Ngoài trời Nắng gắt siêu tương phản (`#FFF` / `#000` / `115%` / viền `3px`) | Slide 4 |
| `06` | `06_crop_doctor_chatbot_247.png` | Chatbot nổi "🌱 Bác sĩ Cây trồng 24/7" tư vấn tức thì phác đồ pH/EC | Slide 5 |
| `07a` | `07a_smart_fertigation_calculator.png` | Máy tính Pha phân Dinh dưỡng A–B & Lịch tưới Nhỏ giọt Tự động | Slide 5 |
| `07b` | `07b_microclimate_weather_advisory.png` | Trạm Cảnh báo Thời tiết Phun tưới theo 3 Tiểu vùng Khí hậu | Slide 5 |
| `08` | `08_course_tiles_chebien01_desktop.png` | Giao diện Thẻ lưới Bài học (Course Tiles UI) trên Desktop | Slide 1 |
| `09` | `09_course_tiles_chebien01_mobile.png` | Giao diện Thẻ lưới Bài học trên Điện thoại Mobile `375x812` | Slide 4 |
| `10` | `10_interactive_slide_deck_and_tts.png` | Bài giảng Slide tương tác 7 trang & Trợ lý Giọng đọc `#mx-tts-player` | Slide 4 |
| `11` | `11_ipm_flashcards_lesson.png` | Học liệu Thẻ lật Sâu bệnh Sinh học IPM & Quy trình QR VietGAP | Slide 2 |
| `12` | `12_fieldpractice_qr_gps_checkin.png` | Điểm danh Thực hành tại Vườn bằng Mã QR Xoay vòng 5 phút & Tọa độ GPS | Slide 9 |
| `13` | `13_quiz_12_questions_attempt.png` | Giao diện làm bài Kiểm tra Trắc nghiệm 12 câu hỏi (`mod_quiz`) | Slide 10 |
| `14` | `14_quiz_graded_review_100pct.png` | Kết quả chấm điểm Quiz tự động đạt `100%` (`10.00 / 10.00`) | Slide 10 |
| `15` | `15_farmer_digital_certificates.png` | Trung tâm Chứng nhận Số & Huy hiệu của Nông dân (`local_mamxanhcert`) | Slide 10 |
| `16` | `16_public_qr_cert_verification.png` | Cổng Xác thực Công khai Chứng nhận Số QR (`HMAC-SHA256 VERIFIED`) | Slide 10 |
| `17` | `17_vietgap_batch_and_voice_diary.png` | Quản lý Lô sản xuất VietGAP & Ghi Nhật ký Canh tác bằng Giọng nói | Slide 10 |
| `18` | `18_public_vietgap_qr_passport.png` | Hộ chiếu Truy xuất Nguồn gốc Lô Nông sản VietGAP Công khai (`trace.php`) | Slide 10 |
| `19` | `19_cbkt_analytics_dashboard_desktop.png` | Trung tâm Điều hành, Thống kê 3 Vùng trồng & Xuất CSV của CBKT (Desktop) | Slide 11 |
| `20` | `20_cbkt_analytics_dashboard_mobile.png` | Trung tâm Điều hành CBKT trên Mobile `375x812` (`.table-responsive`) | Slide 11 |
| `21` | `21_cbkt_field_rubric_grading.png` | Bảng Chấm điểm Thực hành Vườn theo Rubric 4 Tiêu chí đẩy vào Gradebook | Slide 9 |
| `22` | `22_admin_infrastructure_audit.png` | Trang Kiểm toán Hạ tầng Turbo & Bảo mật dành riêng cho Admin | Slide 3, 12 |
| `23` | `23_real_html5_video_lesson.png` | **Video Nông nghiệp Thực tế HTML5 MP4 + Phụ đề Tiếng Việt `.vtt` + Tóm tắt Kỹ thuật** | Slide 2, 6 |
| `24a` | `24a_inline_pdf_viewer_desktop.png` | **Xem Trực tiếp Sổ tay PDF trên Desktop (`650px`, Zoom `120%`, Không tải về)** | Slide 2, 6 |
| `24b` | `24b_inline_pdf_viewer_mobile.png` | **Xem Trực tiếp Sổ tay PDF trên Điện thoại Mobile `375x812` (`480px`)** | Slide 6 |
| `25a` | `25a_h5p_interactive_activity.png` | **Hoạt động Tương tác H5P Thật (`mod_h5pactivity`) chấm điểm vào Gradebook** | Slide 7 |
| `25b` | `25b_scorm12_vietgap_player.png` | **Gói Bài giảng Chuẩn ADL SCORM 1.2 (`mod_scorm`) đồng bộ `100/100 COMPLETED`** | Slide 2, 7 |
| `26a` | `26a_master_template_learning_path_locked.png` | **Khóa học Mẫu `MASTER-AGRITECH-TPL` với Stepper Lộ trình 4 Bước (Đang khóa Bước 2–4)** | Slide 8 |
| `26b` | `26b_master_template_step2_unlocked.png` | **Hoàn thành Bước 1 (Video + PDF) tự động mở khóa Bước 2 (H5P + SCORM 1.2)** | Slide 8 |
| `27a` | `27a_course_cloner_form.png` | **Giao diện Công cụ Nhân bản Khóa học Mẫu 1-Chạm (`clone_course.php`)** | Slide 8 |
| `27b` | `27b_course_cloner_result.png` | **Kết quả Nhân bản Thành công 100% lớp Đơn Dương - Lâm Đồng trong `2.52s`** | Slide 2, 8 |
| `27c` | `27c_cloned_local_course_view.png` | **Giao diện Khóa học Địa phương vừa nhân bản bảo toàn 100% học liệu & lộ trình** | Slide 8, 12 |
