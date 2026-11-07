<#
.SYNOPSIS
    Mầm Xanh LMS 2.0 - Công cụ Phục hồi Hệ thống (SQL Database & Gói Khóa học .mbz)
.DESCRIPTION
    Phục hồi cơ sở dữ liệu từ file .sql và/hoặc khôi phục gói khóa học .mbz vào danh mục Moodle,
    sau đó tự động xóa cache để hệ thống hoạt động tức thì.
#>
param(
    [string]$SqlFile = "backups/mamxanh_lms_latest.sql",
    [string]$MbzFile = "",
    [int]$CategoryId = 1
)

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $ProjectRoot

Write-Host "=== [Mam Xanh LMS 2.0] Cong cu Phuc hoi He thong ===" -ForegroundColor Cyan

$DbName = "moodle_mamxanh"
$DbUser = "moodle_user"
$DbPass = "MX!DbPass2026"
if (Test-Path ".env") {
    Get-Content ".env" | ForEach-Object {
        if ($_ -match "^MOODLE_DB_NAME=(.+)$") { $DbName = $matches[1].Trim() }
        if ($_ -match "^MOODLE_DB_USER=(.+)$") { $DbUser = $matches[1].Trim() }
        if ($_ -match "^MOODLE_DB_PASS=(.+)$") { $DbPass = $matches[1].Trim() }
    }
}

if ($SqlFile -and (Test-Path $SqlFile)) {
    Write-Host "[1/3] Dang phuc hoi Co so du lieu MariaDB ($DbName) tu: $SqlFile ..." -ForegroundColor Yellow
    Get-Content -Raw -Encoding UTF8 $SqlFile | docker compose exec -T moodle_db mariadb "-u$DbUser" "-p$DbPass" $DbName
    Write-Host " -> [OK] Phuc hoi Database thanh cong." -ForegroundColor Green
} else {
    Write-Host "[1/3] Bo qua phuc hoi SQL (khong tim thay $SqlFile hoac khong chi dinh)." -ForegroundColor Gray
}

if ($MbzFile -and (Test-Path $MbzFile)) {
    $MbzLeaf = Split-Path -Leaf $MbzFile
    Write-Host "[2/3] Dang phuc hoi goi khoa hoc .mbz: $MbzLeaf vao Category #$CategoryId ..." -ForegroundColor Yellow
    docker compose exec -T -u www-data moodle_app php /var/www/html/admin/cli/restore_backup.php `
        "--file=/opt/mamxanh/backups/$MbzLeaf" `
        "--categoryid=$CategoryId"
    Write-Host " -> [OK] Phuc hoi khoa hoc .mbz thanh cong." -ForegroundColor Green
} else {
    Write-Host "[2/3] Bo qua phuc hoi .mbz (truyen -MbzFile backups/<ten_file>.mbz neu can khoi phuc le khoa hoc)." -ForegroundColor Gray
}

Write-Host "[3/3] Dang lam sach Moodle Cache & bien dich CSS ..." -ForegroundColor Cyan
docker compose exec -T -u www-data moodle_app php /var/www/html/admin/cli/purge_caches.php
Write-Host "=== [HOAN TAT] He thong Mam Xanh LMS 2.0 da san sang! ===" -ForegroundColor Green
