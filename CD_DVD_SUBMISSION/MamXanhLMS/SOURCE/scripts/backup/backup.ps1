<#
.SYNOPSIS
    Mầm Xanh LMS 2.0 - Công cụ Sao lưu Tự động (MariaDB SQL + Gói Khóa học .mbz)
.DESCRIPTION
    1. Xuất toàn bộ cơ sở dữ liệu MariaDB (utf8mb4) ra file .sql tại thư mục backups/.
    2. Đóng gói chuẩn Moodle Backup (.mbz) cho các khóa học cốt lõi (CHAMSOC01, CHEBIEN01).
#>
param(
    [string]$OutputDir = "backups",
    [string[]]$CourseShortnames = @("CHAMSOC01", "CHEBIEN01")
)

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $ProjectRoot

if (-not (Test-Path $OutputDir)) {
    New-Item -ItemType Directory -Path $OutputDir -Force | Out-Null
}

$Timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$SqlFile = Join-Path $OutputDir "mamxanh_lms_db_$Timestamp.sql"
$LatestSqlFile = Join-Path $OutputDir "mamxanh_lms_latest.sql"

Write-Host "=== [Mam Xanh LMS 2.0] Bat dau Sao luu He thong ($Timestamp) ===" -ForegroundColor Green

# Load DB credentials from .env (with safe defaults matching config.php)
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

# 1. MariaDB Full Dump (UTF-8mb4, single-transaction, routines & events)
Write-Host "[1/2] Dang xuat Co so du lieu MariaDB ($DbName) ra $SqlFile ..." -ForegroundColor Cyan
docker compose exec -T moodle_db mariadb-dump `
    "-u$DbUser" "-p$DbPass" `
    --single-transaction --routines --triggers --default-character-set=utf8mb4 `
    $DbName > $SqlFile

Copy-Item -Path $SqlFile -Destination $LatestSqlFile -Force
$SqlSizeMB = [math]::Round((Get-Item $SqlFile).Length / 1MB, 2)
Write-Host " -> [OK] Da luu SQL Dump: $SqlFile ($SqlSizeMB MB) & $LatestSqlFile" -ForegroundColor Green

# 2. Moodle Course .mbz Backups via Official Moodle CLI
Write-Host "[2/2] Dang dong goi Khoa hoc chuan Moodle (.mbz) ..." -ForegroundColor Cyan
docker compose exec -T moodle_app bash -c "mkdir -p /opt/mamxanh/backups && chown -R www-data:www-data /opt/mamxanh/backups"

foreach ($Shortname in $CourseShortnames) {
    $CourseId = (@"
<?php
define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
echo `$DB->get_field('course', 'id', ['shortname' => '$Shortname']);
"@ | docker compose exec -T -u www-data moodle_app php).Trim()
    if ($CourseId) {
        Write-Host " -> Dang dong goi khoa hoc [$Shortname] (ID=$CourseId) ..." -ForegroundColor Yellow
        docker compose exec -T -u www-data moodle_app php /var/www/html/admin/cli/backup.php `
            "--courseid=$CourseId" `
            "--destination=/opt/mamxanh/backups"
    }
}

Write-Host "=== [HOAN TAT] Danh sach ban sao luu hien co tai /$OutputDir ===" -ForegroundColor Green
Get-ChildItem -Path $OutputDir | Select-Object Name, @{Name="SizeKB";Expression={[math]::Round($_.Length/1KB,1)}}, LastWriteTime | Format-Table -AutoSize
