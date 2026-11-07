# 📘 Hướng dẫn Triển khai, Phân quyền & Sao lưu Hệ thống (Admin Deployment Guide)

Tài liệu này dành cho **Quản trị viên Hệ thống (System Administrator)** vận hành nền tảng **Mầm Xanh LMS 2.0**.

---

## 1. Kiến trúc Dịch vụ & Cấu hình Hiệu năng Cao

### 1.1. Danh sách 5 Dịch vụ Container (`docker-compose.yml`)
| Dịch vụ | Image / Build | Vai trò kỹ thuật | Cấu hình tối ưu trọng tâm |
| :--- | :--- | :--- | :--- |
| `moodle_app` | `mamxanh-moodle:2.0` (`php:8.3-apache`) | Máy chủ ứng dụng Moodle 4.5 LTS (mở trực tiếp cổng độc lập `8090:80`) | Cài đủ `gd`, `intl`, `zip`, `soap`, `mysqli`, `pdo_mysql`, `opcache`, `redis`, `exif`, `sodium`, `git`; `tmpfs` `/tmp` 512MB; hỗ trợ HTTP trực tiếp `http://localhost:8090` không cần Reverse Proxy |
| `moodle_db` | `mariadb:10.11` | Cơ sở dữ liệu quan hệ chuẩn `utf8mb4_unicode_ci` | `--innodb-buffer-pool-size=512M --innodb-flush-log-at-trx-commit=2 --skip-name-resolve` |
| `moodle_redis` | `redis:7-alpine` | Lưu trữ Session & Application Cache trong RAM | `maxmemory 256mb`, chính sách `allkeys-lru` |
| `moodle_proxy` | `caddy:2-alpine` | Cổng HTTPS TLS nội bộ & nén băng thông di động | Hỗ trợ `https://moodle.lvh.me:8443`, bật nén `encode zstd gzip` |
| `moodle_cron` | `mamxanh-moodle:2.0` | Tác vụ nền Moodle định kỳ | Tự động thực thi `/var/www/html/admin/cli/cron.php` mỗi 60 giây |

### 1.2. Cô lập I/O trên Docker Windows (`moodle_custom/config.php`)
Để loại bỏ hoàn toàn hiện tượng nghẽn đọc/ghi hàng chục nghìn file cache qua lớp ảo hóa NTFS–WSL2 trên Windows:
- **`$CFG->dataroot = '/var/moodledata'`** và **`$CFG->cachedir = '/var/moodledata/cache'`**: Lưu trữ trên **Linux Named Volume** (`moodle_data`).
- **`$CFG->localcachedir = '/tmp/moodle_localcache'`** và **`$CFG->tempdir = '/tmp/moodle_temp'`**: Đặt trên phân vùng **`tmpfs` (RAM)** tốc độ truy xuất dưới $0.1\text{ ms}$.
- **`docker/php/zz-performance.ini`**: Kích hoạt PHP OPcache (`memory_consumption=256`, `max_accelerated_files=30000`, `revalidate_freq=60`, `opcache.jit=1255`, `realpath_cache_size=4096K`).

---

## 2. Quy trình Cài đặt Mới hoặc Khởi tạo Lại từ Đầu

```powershell
# Bước 1: Sao chép file cấu hình môi trường (nếu triển khai trên máy chủ mới)
Copy-Item .env.example .env

# Bước 2: Build image và khởi chạy hạ tầng
docker compose up -d --build

# Bước 3: Cài đặt cấu trúc Database Moodle (chỉ chạy lần đầu khi DB trống)
docker compose exec -T -u www-data moodle_app php /var/www/html/admin/cli/install_database.php `
  --lang=vi --adminuser=admin --adminpass="Admin!2026MX" `
  --adminemail=admin@mamxanh.local --fullname="Mầm Xanh LMS 2.0" --shortname="MamXanhLMS" --agree-license

# Bước 4: Kích hoạt gói Tiếng Việt, Redis MUC Cache, Phân quyền 3 cấp, 6 Khóa học & Dữ liệu mẫu
docker compose exec -T -u www-data moodle_app php /opt/mamxanh/scripts/cli/stage1_setup_system.php
docker compose exec -T -u www-data moodle_app php /var/www/html/admin/cli/upgrade.php --non-interactive
docker compose exec -T -u www-data moodle_app php /opt/mamxanh/scripts/cli/stage2_setup_rbac.php
docker compose exec -T -u www-data moodle_app php /opt/mamxanh/scripts/cli/stage4_create_courses.php
docker compose exec -T -u www-data moodle_app php /opt/mamxanh/scripts/cli/stage5_seed_simulation.php
docker compose exec -T -u www-data moodle_app php /opt/mamxanh/scripts/cli/stage7_council_upgrades.php

# Bước 5: Biên dịch sẵn CSS Theme & làm sạch Cache
docker compose exec -T -u www-data moodle_app php /var/www/html/admin/cli/build_theme_css.php --themes=agritrain_child
docker compose exec -T -u www-data moodle_app php /var/www/html/admin/cli/purge_caches.php
```

> **Lưu ý cổng mạng trên Windows:** Nếu cổng `8080` trên máy trạm đang được sử dụng bởi dịch vụ nền khác, chỉnh `HTTP_PORT=8088` trong `.env` trong khi giữ nguyên cổng chính chuẩn HTTPS `HTTPS_PORT=8443` (`https://moodle.lvh.me:8443`).

---

## 3. Ma trận Phân quyền Bảo mật 3 Cấp (RBAC Matrix)

Hệ thống kiểm soát truy cập chặt chẽ ở tầng Moodle Capability (`has_capability` & `require_capability`), trả mã **HTTP 403 Forbidden** rõ ràng khi truy cập trái thẩm quyền:

| Chức năng / Đường dẫn | Nông dân (`student`) | Cán bộ kỹ thuật (`canbokythuat`) | Quản trị viên (`admin`) |
| :--- | :---: | :---: | :---: |
| Dashboard Học tập Nông dân (`/local/mamxanhapp/index.php`) | ✅ Cho phép | ✅ Cho phép | ✅ Cho phép |
| Hồ sơ Nông hộ & Đồng bộ Cohort (`/local/farmerprofile/index.php`) | ✅ Cá nhân | ✅ Xem/Hỗ trợ | ✅ Toàn quyền |
| Lô VietGAP & Nhật ký Giọng nói (`/local/agriops/index.php`) | ✅ Tạo & Ghi nhật ký | ✅ Giám sát | ✅ Toàn quyền |
| Điểm danh GPS/QR Thực hành vườn (`/mod/fieldpractice/view.php`) | ✅ Điểm danh & Xem điểm | ✅ Phát mã QR xoay vòng | ✅ Toàn quyền |
| **Chấm điểm Rubric 4 tiêu chí** (`/mod/fieldpractice/grade.php`) | ⛔ **Chặn 403** | ✅ Chấm điểm -> Gradebook | ✅ Toàn quyền |
| **Thống kê Vùng trồng & Xuất CSV** (`/local/mamxanhapp/manage.php`) | ⛔ **Chặn 403** | ✅ Xem biểu đồ & Xuất CSV | ✅ Toàn quyền |
| **Nhân bản Khóa học Mẫu 1-Chạm** (`/local/mamxanhapp/clone_course.php`) | ⛔ **Chặn 403** | ✅ Nhân bản & Gán Cohort | ✅ Toàn quyền |
| **Kiểm toán Bảo mật & Hạ tầng** (`/local/mamxanhapp/admin_audit.php`) | ⛔ **Chặn 403** | ⛔ **Chặn 403** | ✅ **Chỉ Admin (200)** |
| Xác thực Chứng nhận QR & VietGAP (`verify.php`, `trace.php`) | 🌐 Công khai | 🌐 Công khai | 🌐 Công khai |

### Bảo mật Chữ ký số Chứng nhận (`HMAC-SHA256`)
Mỗi chứng nhận hoàn thành khóa học trong bảng `mdl_local_mamxanhcert` được ký bằng khóa bí mật `MAMXANH_HMAC_SECRET` cấu hình tại `.env` theo chuỗi chuẩn hóa:
```text
HMAC_SHA256(cert_code | userid | courseid | final_score | timecreated, MAMXANH_HMAC_SECRET)
```
Bất kỳ chỉnh sửa trái phép nào vào điểm số hoặc mã chứng nhận trong CSDL đều bị phát hiện ngay lập tức tại cổng `/local/mamxanhcert/verify.php`.

---

## 4. Quy trình Sao lưu & Phục hồi Thảm họa (Backup & Disaster Recovery)

Bộ công cụ tự động hóa nằm tại `scripts/backup/` hỗ trợ cả **Windows PowerShell (`.ps1`)** và **Linux/WSL2 Bash (`.sh`)**.

### 4.1. Thực hiện Sao lưu Toàn diện (`.sql` + `.mbz`)
- **Trên Windows PowerShell:**
  ```powershell
  powershell -ExecutionPolicy Bypass -File .\scripts\backup\backup.ps1
  ```
- **Trên Linux / macOS / WSL2:**
  ```bash
  bash ./scripts/backup/backup.sh
  ```
**Kết quả sinh ra tại thư mục `backups/`:**
1. `mamxanh_lms_db_<YYYYMMDD_HHMMSS>.sql` & `mamxanh_lms_latest.sql`: Bản chụp toàn vẹn MariaDB (`--single-transaction --routines --triggers --default-character-set=utf8mb4`).
2. Các gói khóa học chuẩn Moodle Backup `.mbz` (bao gồm cả ngân hàng câu hỏi Quiz, hoạt động Thực hành vườn `mod_fieldpractice`, tiêu chí hoàn thành và cấu trúc điểm).

### 4.2. Thực hiện Phục hồi Hệ thống
- **Phục hồi toàn bộ Cơ sở dữ liệu từ bản mới nhất (Windows):**
  ```powershell
  powershell -ExecutionPolicy Bypass -File .\scripts\backup\restore.ps1 -SqlFile "backups/mamxanh_lms_latest.sql"
  ```
- **Khôi phục riêng lẻ một gói khóa học `.mbz` vào Danh mục khóa học:**
  ```powershell
  powershell -ExecutionPolicy Bypass -File .\scripts\backup\restore.ps1 -SqlFile "" -MbzFile "backups/<ten_goi_khoa_hoc>.mbz" -CategoryId 1
  ```
- **Trên Linux / macOS / WSL2:**
  ```bash
  bash ./scripts/backup/restore.sh backups/mamxanh_lms_latest.sql
  ```

---

## 5. Xử lý Sự cố Thường gặp (Troubleshooting)
1. **Giao diện chưa cập nhật sau khi sửa file `.scss` hoặc `.mustache`:**
   Chạy lệnh biên dịch lại CSS và xóa cache dưới quyền `www-data`:
   ```powershell
   docker compose exec -T -u www-data moodle_app php /var/www/html/admin/cli/build_theme_css.php --themes=agritrain_child
   docker compose exec -T -u www-data moodle_app php /var/www/html/admin/cli/purge_caches.php
   ```
2. **Trình duyệt cảnh báo chứng chỉ SSL nội bộ lần đầu truy cập `https://moodle.lvh.me:8443`:**
   Caddy 2 tự động sinh chứng chỉ Root CA nội bộ (`tls internal`). Bạn có thể trích xuất Root CA từ `moodle_proxy:/data/caddy/pki/authorities/local/root.crt` và cài vào Windows Trusted Root Certification Authorities để trình duyệt hiện ổ khóa xanh tuyệt đối.
