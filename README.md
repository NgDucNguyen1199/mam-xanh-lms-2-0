# Mầm Xanh LMS 2.0 — Hệ thống Quản lý Học tập & Tập huấn Kỹ thuật Canh tác Nông nghiệp Công nghệ cao

> **Đồ án Tốt nghiệp Đại học — Khoa Công nghệ Thông tin, Trường Đại học Đà Lạt**  
> Nền tảng e-Learning chuyên biệt cho nông hộ và hợp tác xã nông nghiệp công nghệ cao, xây dựng trên lõi **Moodle 4.5 LTS** kết hợp kiến trúc **Docker Turbo 5 vi dịch vụ**.

---

## 1. Thông tin Đề tài & Nhóm Thực hiện

- **Tên đề tài:** Xây dựng hệ thống quản lý học tập và tập huấn kỹ thuật canh tác nông nghiệp công nghệ cao trên nền tảng Moodle
- **Giảng viên hướng dẫn:** Thầy Trần Ngô Như Khánh
- **Nhóm sinh viên thực hiện (Lớp CTK46A — Ngành Công nghệ Thông tin):**
  1. **Nguyễn Đức Nguyên** — MSSV: `2212429` (Trưởng nhóm)
  2. **Mai Xuân Trưởng** — MSSV: `2212484`
  3. **Nguyễn Ngọc Tuệ Quang** — MSSV: `2212447`

---

## 2. Kiến trúc Hạ tầng Docker Turbo (5 Containers)

| Container | Công nghệ | Vai trò |
| :--- | :--- | :--- |
| `moodle_proxy` | **Caddy 2** | Reverse Proxy HTTPS/TLS tự động, nén `zstd` + `gzip` |
| `moodle_app` | **PHP 8.3 + Apache 2.4** | Moodle 4.5.15+ LTS tối ưu OPcache JIT (`1255`) |
| `moodle_db` | **MariaDB 10.11 LTS** | Lưu trữ 508 bảng dữ liệu (`utf8mb4_unicode_ci`) |
| `moodle_redis` | **Redis 7 Alpine** | In-memory Session Handler & MUC Application Cache |
| `moodle_cron` | **Moodle Cron Worker** | Xử lý tác vụ nền (Completion, Huy hiệu, Chứng chỉ) |

---

## 3. Tính năng & Phân hệ Cốt lõi

- **8 Chương trình Tập huấn Nông nghiệp Công nghệ cao:** `CHAMSOC01`, `CHEBIEN01`, `IOTNHAMANG01`, `THUYCANH01`, `SAURIENG01`, `PHANVISINH01`, `DAUTAY-CNC-01`, `DAUTAY-DONDUONG-01` (đầy đủ Video 16:9, PDF xem trực tiếp, H5P, SCORM 1.2, Điểm danh, Thảo luận, Rubric, Quiz).
- **`mod_fieldpractice`:** Hoạt động thực hành tại vườn với mã QR xoay vòng 5 phút (kèm đồng hồ đếm ngược), định vị GPS Haversine và chấm điểm theo ma trận Rubric 4 tiêu chí (100 điểm) đồng bộ Gradebook.
- **`local_mamxanhcert`:** Tự động cấp Chứng nhận Số QR ký bằng thuật toán **HMAC-SHA256 (RFC 2104)** kèm cổng tra cứu công khai (`verify.php`) và kiểm thử chống giả mạo.
- **`local_agriops`:** Sổ tay Nhật ký Canh tác Điện tử VietGAP (`TCVN 11892-1:2017`) hỗ trợ nhập liệu giọng nói (Web Speech API), xuất báo cáo HTML/CSV và Hộ chiếu Truy xuất Nguồn gốc Nông sản QR (`trace.php`).
- **`local_mamxanhapp` & `local_farmerprofile`:** Bảng điều khiển Nông hộ & CBKT, Bảng điểm Học thuật A4 (`transcript.php`), Phiếu Chấm Rubric A4 (`feedback_sheet.php`), Máy tính Pha phân & Cảnh báo Thời tiết (`calculator.php`), Công cụ Nhân bản Khóa học 1 chạm (`clone_course.php`) và Kiểm toán Hạ tầng (`admin_audit.php`).
- **Giao diện `agritrain_child` Theme:** Tuân thủ chuẩn công thái học Minimalist Off-White Botanical (`#F9FAF9` / `#234E3E`), vùng chạm Mobile $\ge 44\text{px}$, hỗ trợ song ngữ Việt – Anh tức thời và phân quyền bảo mật RBAC 3 cấp.

---

## 4. Hướng dẫn Khởi chạy Nhanh (60 giây)

### Yêu cầu môi trường
- Docker Desktop / Docker Engine v27+ & Docker Compose v2+

### Các bước triển khai
```bash
# 1. Khởi động toàn bộ 5 container Docker Turbo
docker compose up -d --build

# 2. Truy cập hệ thống qua trình duyệt
# - Cổng HTTP trực tiếp: http://localhost:8090
# - Cổng HTTPS Caddy:    https://moodle.lvh.me:8443
```

### Kiểm thử Tự động E2E (Puppeteer)
```bash
cd tests/e2e
npm install
node verify_full_crawl.mjs
```

---

## 5. Cấu trúc Thư mục Chính

- `moodle_custom/`: Toàn bộ mã nguồn 5 plugin mở rộng (`local_*`, `mod/fieldpractice`) và child theme (`theme/agritrain_child`).
- `docker/` & `docker-compose.yml`: Cấu hình hạ tầng Docker Turbo.
- `scripts/`: Bộ công cụ PHP CLI bảo trì, seed dữ liệu và đồng bộ khóa học Moodle.
- `tests/e2e/`: Kịch bản kiểm thử tự động End-to-End bằng Puppeteer.
- `DEMO_COURSE_KIT/`: Bộ học liệu mẫu SCORM, H5P, PDF phục vụ triển khai.
