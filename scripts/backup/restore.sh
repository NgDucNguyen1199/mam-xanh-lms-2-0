#!/usr/bin/env bash
# ==============================================================================
# Mầm Xanh LMS 2.0 - Công cụ Phục hồi Hệ thống (Linux / macOS / WSL2)
# Phục hồi cơ sở dữ liệu từ file .sql và/hoặc gói khóa học .mbz
# ==============================================================================
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
cd "${PROJECT_ROOT}"

SQL_FILE="${1:-backups/mamxanh_lms_latest.sql}"
MBZ_FILE="${2:-}"
CATEGORY_ID="${3:-1}"

echo "=== [Mam Xanh LMS 2.0] Cong cu Phuc hoi He thong ==="

if [[ -f ".env" ]]; then
    set -a
    source <(tr -d '\r' < .env)
    set +a
fi
DB_NAME="${MOODLE_DB_NAME:-moodle_mamxanh}"
DB_USER="${MOODLE_DB_USER:-moodle_user}"
DB_PASS="${MOODLE_DB_PASS:-MX!DbPass2026}"

if [[ -n "${SQL_FILE}" && -f "${SQL_FILE}" ]]; then
    echo "[1/3] Dang phuc hoi Co so du lieu MariaDB (${DB_NAME}) tu ${SQL_FILE} ..."
    docker compose exec -T moodle_db mariadb "-u${DB_USER}" "-p${DB_PASS}" "${DB_NAME}" < "${SQL_FILE}"
    echo " -> [OK] Phuc hoi Database thanh cong."
else
    echo "[1/3] Bo qua phuc hoi SQL (${SQL_FILE} khong ton tai)."
fi

if [[ -n "${MBZ_FILE}" && -f "${MBZ_FILE}" ]]; then
    MBZ_BASENAME="$(basename "${MBZ_FILE}")"
    echo "[2/3] Dang phuc hoi goi khoa hoc ${MBZ_BASENAME} vao Category #${CATEGORY_ID} ..."
    docker compose exec -T -u www-data moodle_app php /var/www/html/admin/cli/restore_backup.php \
        --file="/opt/mamxanh/backups/${MBZ_BASENAME}" \
        --categoryid="${CATEGORY_ID}"
    echo " -> [OK] Phuc hoi khoa hoc .mbz thanh cong."
else
    echo "[2/3] Bo qua phuc hoi .mbz."
fi

echo "[3/3] Xoa cache toan he thong ..."
docker compose exec -T -u www-data moodle_app php /var/www/html/admin/cli/purge_caches.php
echo "=== [HOAN TAT] Phuc hoi he thong thanh cong! ==="
