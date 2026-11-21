#!/usr/bin/env bash
# ==============================================================================
# Mầm Xanh LMS 2.0 - Công cụ Sao lưu Tự động (Linux / macOS / WSL2)
# Sao lưu trọn bộ MariaDB (.sql) và gói khóa học chuẩn Moodle (.mbz)
# ==============================================================================
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
cd "${PROJECT_ROOT}"

OUTPUT_DIR="${1:-backups}"
mkdir -p "${OUTPUT_DIR}"

TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
SQL_FILE="${OUTPUT_DIR}/mamxanh_lms_db_${TIMESTAMP}.sql"
LATEST_SQL="${OUTPUT_DIR}/mamxanh_lms_latest.sql"

echo "=== [Mam Xanh LMS 2.0] Bat dau Sao luu He thong (${TIMESTAMP}) ==="

if [[ -f ".env" ]]; then
    set -a
    source <(tr -d '\r' < .env)
    set +a
fi
DB_NAME="${MOODLE_DB_NAME:-moodle_mamxanh}"
DB_USER="${MOODLE_DB_USER:-moodle_user}"
DB_PASS="${MOODLE_DB_PASS:-MX!DbPass2026}"

echo "[1/2] Xuat Co so du lieu MariaDB (${DB_NAME}) ra ${SQL_FILE} ..."
docker compose exec -T moodle_db mariadb-dump \
    "-u${DB_USER}" "-p${DB_PASS}" \
    --single-transaction --routines --triggers --default-character-set=utf8mb4 \
    "${DB_NAME}" > "${SQL_FILE}"

cp -f "${SQL_FILE}" "${LATEST_SQL}"
echo " -> [OK] Da luu SQL Dump: ${SQL_FILE}"

echo "[2/2] Dong goi Khoa hoc chuan Moodle (.mbz) ..."
docker compose exec -T moodle_app bash -c "mkdir -p /opt/mamxanh/backups && chown -R www-data:www-data /opt/mamxanh/backups"

for SHORTNAME in CHAMSOC01 CHEBIEN01; do
    COURSE_ID="$(docker compose exec -T -u www-data moodle_app php -r "define('CLI_SCRIPT',true); require('/var/www/html/config.php'); echo \$DB->get_field('course','id',['shortname'=>'${SHORTNAME}']);" | tr -d '\r\n')"
    if [[ -n "${COURSE_ID}" ]]; then
        echo " -> Dong goi khoa hoc [${SHORTNAME}] (ID=${COURSE_ID}) ..."
        docker compose exec -T -u www-data moodle_app php /var/www/html/admin/cli/backup.php \
            --courseid="${COURSE_ID}" \
            --destination=/opt/mamxanh/backups
    fi
done

echo "=== [HOAN TAT] Danh sach file sao luu tai ${OUTPUT_DIR}/ ==="
ls -lh "${OUTPUT_DIR}"
