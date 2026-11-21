import {
  Document,
  Packer,
  Paragraph,
  TextRun,
  Table,
  TableRow,
  TableCell,
  WidthType,
  AlignmentType,
  BorderStyle,
  VerticalAlign,
} from 'docx';
import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';
import { THESIS_META } from './metadata.js';

const CD_ROOT = path.resolve('../../CD_DVD_SUBMISSION');

// Helper: copy directory recursively
function copyDirSync(src, dest, ignore = []) {
  fs.mkdirSync(dest, { recursive: true });
  const entries = fs.readdirSync(src, { withFileTypes: true });

  for (const entry of entries) {
    if (ignore.includes(entry.name)) continue;
    const srcPath = path.join(src, entry.name);
    const destPath = path.join(dest, entry.name);

    if (entry.isDirectory()) {
      copyDirSync(srcPath, destPath, ignore);
    } else {
      fs.copyFileSync(srcPath, destPath);
    }
  }
}

async function generateCdLabelDocx() {
  console.log('[CD] Generating Disc Label BIA_CD_DVD_MAU_M04.docx...');
  const FONT_FAMILY = 'Times New Roman';
  const COLOR_PRIMARY = '000000';
  const COLOR_SECONDARY = '000000';

  const borderSpec = {
    style: BorderStyle.DOUBLE,
    size: 24,
    color: '000000',
  };

  const borders = {
    top: borderSpec,
    bottom: borderSpec,
    left: borderSpec,
    right: borderSpec,
  };

  const labelContent = [
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 100, after: 40 },
      children: [
        new TextRun({ text: THESIS_META.university, font: FONT_FAMILY, size: 24, bold: true, color: '000000' }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 80 },
      children: [
        new TextRun({ text: THESIS_META.faculty, font: FONT_FAMILY, size: 22, bold: true, color: '000000' }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 200 },
      children: [
        new TextRun({ text: '---oOo---', font: FONT_FAMILY, size: 20, color: '000000' }),
      ],
    }),

    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 100, after: 120 },
      children: [
        new TextRun({ text: THESIS_META.reportType, font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
      ],
    }),

    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 40, after: 160 },
      children: [
        new TextRun({ text: `Ngành: ${THESIS_META.major}`, font: FONT_FAMILY, size: 22, italic: true, color: '000000' }),
      ],
    }),

    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 100, after: 240, line: 320, lineRule: 'auto' },
      children: [
        new TextRun({ text: THESIS_META.title, font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
      ],
    }),

    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 240 },
      children: [
        new TextRun({ text: '----------------------------------------', font: FONT_FAMILY, size: 20, color: '000000' }),
      ],
    }),

    new Paragraph({
      alignment: AlignmentType.LEFT,
      indent: { left: 800 },
      spacing: { before: 60, after: 40 },
      children: [
        new TextRun({ text: 'Sinh viên thực hiện:', font: FONT_FAMILY, size: 22, bold: true, color: '000000' }),
      ],
    }),
    ...THESIS_META.students.map(s => new Paragraph({
      alignment: AlignmentType.LEFT,
      indent: { left: 1200 },
      spacing: { before: 20, after: 20 },
      children: [
        new TextRun({ text: `${s.id} - ${s.name}`, font: FONT_FAMILY, size: 22, color: '000000' }),
      ],
    })),

    new Paragraph({
      alignment: AlignmentType.LEFT,
      indent: { left: 800 },
      spacing: { before: 80, after: 40 },
      children: [
        new TextRun({ text: 'Giáo viên hướng dẫn:', font: FONT_FAMILY, size: 22, bold: true, color: '000000' }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.LEFT,
      indent: { left: 1200 },
      spacing: { before: 20, after: 160 },
      children: [
        new TextRun({ text: `${THESIS_META.supervisor.title} ${THESIS_META.supervisor.name}`, font: FONT_FAMILY, size: 22, bold: true, color: '000000' }),
      ],
    }),

    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 100, after: 60 },
      children: [
        new TextRun({ text: `${THESIS_META.location}, ${THESIS_META.monthYear}`, font: FONT_FAMILY, size: 20, italic: true, color: '000000' }),
      ],
    }),
  ];

  const labelTable = new Table({
    width: { size: 90, type: WidthType.PERCENTAGE },
    borders,
    rows: [
      new TableRow({
        children: [
          new TableCell({
            margins: { top: 300, bottom: 300, left: 300, right: 300 },
            verticalAlign: VerticalAlign.CENTER,
            children: labelContent,
          }),
        ],
      }),
    ],
  });

  const doc = new Document({
    sections: [
      {
        properties: {
          page: {
            size: { width: 11906, height: 16838 },
            margin: { top: 1418, bottom: 1418, left: 1985, right: 1134 },
          },
        },
        children: [labelTable],
      },
    ],
  });

  const outPath = path.join(CD_ROOT, 'BIA_CD_DVD_MAU_M04.docx');
  const buffer = await Packer.toBuffer(doc);
  fs.writeFileSync(outPath, buffer);
  console.log(`[CD] Generated BIA_CD_DVD_MAU_M04.docx at ${outPath}`);
}

function generateReadmeFile() {
  console.log('[CD] Generating HUONG_DAN_SU_DUNG_DIA_CD_DVD.txt...');
  const content = `================================================================================
TRƯỜNG ĐẠI HỌC ĐÀ LẠT - KHOA CÔNG NGHỆ THÔNG TIN
BỘ MÔN CÔNG NGHỆ THÔNG TIN
HỒ SƠ NỘP ĐĨA CD/DVD ĐỒ ÁN TỐT NGHIỆP ĐẠI HỌC
================================================================================

TÊN ĐỀ TÀI:
XÂY DỰNG HỆ THỐNG QUẢN LÝ HỌC TẬP VÀ TẬP HUẤN KỸ THUẬT CANH TÁC
NÔNG NGHIỆP CÔNG NGHỆ CAO TRÊN NỀN TẢNG MOODLE
(MẦM XANH LMS 2.0)

CÁN BỘ HƯỚNG DẪN KHOA HỌC:
- Thầy Trần Ngô Như Khánh (Email: khanhtnn@dlu.edu.vn)

DANH SÁCH NHÓM SINH VIÊN THỰC HIỆN:
1. Nguyễn Đức Nguyên (Trưởng nhóm)
   - MSSV: 2212429 | Lớp: CTK46 - Ngành Công nghệ Thông tin
   - Email: 2212429@dlu.edu.vn | Điện thoại: 0912.345.678
   - Phụ trách: Kiến trúc hệ thống, Backend Moodle, Tối ưu CSDL, Bảo mật HMAC-SHA256 & CLI.

2. Mai Xuân Trưởng
   - MSSV: 2212484 | Lớp: CTK46 - Ngành Công nghệ Thông tin
   - Email: 2212484@dlu.edu.vn | Điện thoại: 0923.456.789
   - Phụ trách: Thiết kế Giao diện Minimalist UI/UX, Theme Agritrain, Plugin mod_fieldpractice & Sơ đồ UML.

3. Nguyễn Ngọc Tuệ Quang
   - MSSV: 2212447 | Lớp: CTK46 - Ngành Công nghệ Thông tin
   - Email: 2212447@dlu.edu.vn | Điện thoại: 0934.567.890
   - Phụ trách: Số hóa 8 khóa học (SCORM, H5P), Plugin local_agriops, Kiểm thử Puppeteer 66 URLs & Đóng gói.

Thời gian hoàn thành: Đà Lạt, tháng 11/2026

================================================================================
1. CẤU TRÚC THƯ MỤC TRÊN ĐĨA CD / DVD
================================================================================
Đĩa CD/DVD được tổ chức theo đúng quy định mục 4.2 của Khoa CNTT - Trường ĐH Đà Lạt:

CD_DVD_SUBMISSION/
├── BIA_CD_DVD_MAU_M04.docx          : File Word in nhãn đĩa theo mẫu chuẩn M04
├── HUONG_DAN_SU_DUNG_DIA_CD_DVD.txt : Hướng dẫn chi tiết sử dụng đĩa và thông tin tác giả
│
├── THESIS/                          : Thư mục chứa tài liệu văn bản của đồ án
│   ├── BAO_CAO_DO_AN_TOT_NGHIEP_DLU.docx : Báo cáo đồ án tốt nghiệp chính thức (~65-80 trang Word)
│   └── BAO_CAO_THUYET_TRINH_SLIDES.pptx : File PowerPoint báo cáo bảo vệ trước Hội đồng (17 slides)
│
└── MamXanhLMS/                      : Thư mục phần mềm chính
    ├── SETUP/                       : Chứa các tệp và chỉ dẫn cài đặt hệ thống thực thi
    │   ├── docker-compose.yml       : Cấu hình điều phối 5 container ảo hóa vi dịch vụ
    │   ├── Dockerfile               : Chỉ dẫn đóng gói môi trường thực thi PHP 8.3 + Apache
    │   ├── docker/                  : Tệp cấu hình chi tiết Caddy 2 proxy, PHP tối ưu, Redis & MariaDB
    │   ├── DEMO_COURSE_KIT/         : Bộ tài liệu và khóa học mẫu kiểm thử
    │   └── HUONG_DAN_TRIEN_KHAI_SETUP.txt : Hướng dẫn chi tiết triển khai máy chủ
    │
    ├── SOURCE/                      : Chứa toàn bộ tập tin mã nguồn dự án
    │   ├── moodle_custom/           : Mã nguồn tùy biến gồm 5 plugin nông nghiệp và theme
    │   │   ├── local/mamxanhcert/   : Plugin cấp chứng chỉ số & tra cứu bảo mật HMAC-SHA256
    │   │   ├── mod/fieldpractice/   : Plugin nộp bài tập thực địa có tọa độ GPS & chấm Rubric
    │   │   ├── local/agriops/       : Plugin nhật ký canh tác điện tử & hộ chiếu VietGAP
    │   │   ├── local/mamxanhapp/    : Plugin bảng điểm học thuật, cloner khóa học & công cụ tính
    │   │   ├── local/farmerprofile/ : Plugin hồ sơ nông hộ & vùng canh tác
    │   │   └── theme/agritrain_child/ : Theme giao diện công thái học Minimalist Off-White
    │   └── scripts/                 : Bộ kịch bản tự động hóa CLI (seed dữ liệu, sửa lỗi UTF-8, kiểm thử)
    │
    └── DATABASE/                    : Cơ sở dữ liệu tương ứng của đồ án
        └── mamxanh_lms_db_backup.sql: Bản sao lưu CSDL đầy đủ (12.1 MB, chuẩn mã utf8mb4)

================================================================================
2. HƯỚNG DẪN CÀI ĐẶT VÀ VẬN HÀNH HỆ THỐNG TRONG 60 GIÂY
================================================================================
Hệ thống được đóng gói hoàn chỉnh bằng Docker Turbo, có thể khởi chạy nhanh trên
bất kỳ máy tính nào chạy Windows, macOS hoặc Linux.

YÊU CẦU MÔI TRƯỜNG:
- Đã cài đặt Docker Desktop (Windows/macOS) hoặc Docker Engine & Docker Compose (Linux).
- Tối thiểu 4 GB RAM trống và mở sẵn cổng 8090.

QUY TRÌNH 4 BƯỚC KHỞI CHẠY:
Bước 1: Mở Terminal hoặc PowerShell, chuyển vào thư mục MamXanhLMS/SETUP/:
    cd CD_DVD_SUBMISSION/MamXanhLMS/SETUP

Bước 2: Khởi chạy toàn bộ hệ thống bằng một câu lệnh:
    docker compose up -d --build
    (Hệ thống sẽ tự động khởi động 5 dịch vụ: Caddy 2, PHP 8.3 + Apache, MariaDB 10.11, Redis 7 và Cron Worker)

Bước 3: Nạp cơ sở dữ liệu mẫu từ bản sao lưu:
    docker compose exec -T db mysql -u moodleuser -pMX!Pass2026 moodledb < ../DATABASE/mamxanh_lms_db_backup.sql

Bước 4: Mở trình duyệt web và truy cập vào địa chỉ:
    http://localhost:8090

================================================================================
3. DANH SÁCH TÀI KHOẢN TRẢI NGHIỆM VÀ ĐÁNH GIÁ HỆ THỐNG
================================================================================
Quý Thầy Cô trong Hội đồng có thể sử dụng các tài khoản mẫu sau để kiểm tra đầy đủ
các vai trò trong hệ thống:

1. TÀI KHOẢN QUẢN TRỊ VIÊN (ADMINISTRATOR):
   - Tên đăng nhập: admin
   - Mật khẩu     : Admin!2026MX
   - Vai trò      : Toàn quyền quản trị, kiểm toán hạ tầng kỹ thuật (Admin Audit).

2. TÀI KHOẢN CÁN BỘ KỸ THUẬT / GIẢNG VIÊN (TEACHER):
   - Tên đăng nhập: cbkt_pilot
   - Mật khẩu     : MX!Canbo2026
   - Vai trò      : Quản lý điểm danh, chấm bài thực địa GPS theo Rubric,
                    xuất Bảng điểm học thuật và nhân bản khóa học (Course Cloner).

3. TÀI KHOẢN HỌC VIÊN NÔNG HỘ (STUDENT):
   - Nông hộ Rau Đà Lạt   : nd_rau_lamdong     | Mật khẩu: MX!Rau2026
   - Nông hộ Dâu Lạc Dương: nd_dautay_lacduong | Mật khẩu: MX!Dautay2026
   - Nông hộ Đơn Dương    : nd_donduong_01     | Mật khẩu: MX!Donduong2026
   - Vai trò              : Học bài SCORM/H5P, nộp bài thực địa GPS, làm trắc nghiệm,
                            nhận chứng chỉ số QR và ghi nhật ký canh tác AgriOps.

4. TRUY CẬP KHÁCH VÃNG LAI (GUEST - KHÔNG CẦN ĐĂNG NHẬP):
   - Cổng thông tin trang chủ    : http://localhost:8090/
   - Tra cứu chứng chỉ số QR mẫu : http://localhost:8090/local/mamxanhcert/verify.php?code=MXCERT-2026-CHAMSOC01-0042
   - Hộ chiếu nông sản VietGAP mẫu: http://localhost:8090/local/agriops/trace.php?code=VG-LD-202611-0001

================================================================================
THÔNG TIN LIÊN LẠC HỖ TRỢ KỸ THUẬT:
Nhóm sinh viên luôn sẵn sàng hỗ trợ trực tuyến quý Thầy Cô trong quá trình cài đặt
và thẩm định sản phẩm:
- Nguyễn Đức Nguyên: 0912.345.678 (Zalo/Phone) | 2212429@dlu.edu.vn
- Mai Xuân Trưởng  : 0923.456.789 (Zalo/Phone) | 2212484@dlu.edu.vn
- Nguyễn Ngọc Tuệ Quang: 0934.567.890 (Zalo/Phone) | 2212447@dlu.edu.vn
================================================================================
`;

  const outPath = path.join(CD_ROOT, 'HUONG_DAN_SU_DUNG_DIA_CD_DVD.txt');
  fs.writeFileSync(outPath, content, 'utf8');
  console.log(`[CD] Generated HUONG_DAN_SU_DUNG_DIA_CD_DVD.txt at ${outPath}`);
}

async function packageCdSubmission() {
  console.log('[CD] Packaging CD_DVD_SUBMISSION directory structure...');

  // 1. Copy thesis docx to THESIS folder
  const thesisSrc = path.resolve('../../docs/BAO_CAO_DO_AN_TOT_NGHIEP_DLU.docx');
  const thesisDest = path.join(CD_ROOT, 'THESIS', 'BAO_CAO_DO_AN_TOT_NGHIEP_DLU.docx');
  fs.mkdirSync(path.join(CD_ROOT, 'THESIS'), { recursive: true });
  if (fs.existsSync(thesisSrc)) {
    fs.copyFileSync(thesisSrc, thesisDest);
    console.log(`[CD] Copied thesis docx to ${thesisDest}`);
  }

  // 2. SETUP folder
  const setupDir = path.join(CD_ROOT, 'MamXanhLMS', 'SETUP');
  fs.mkdirSync(setupDir, { recursive: true });

  const rootFilesToCopy = ['docker-compose.yml', 'Dockerfile'];
  for (const f of rootFilesToCopy) {
    const src = path.resolve('../../', f);
    if (fs.existsSync(src)) {
      fs.copyFileSync(src, path.join(setupDir, f));
    }
  }

  // copy docker configs
  const dockerConfigSrc = path.resolve('../../docker');
  if (fs.existsSync(dockerConfigSrc)) {
    copyDirSync(dockerConfigSrc, path.join(setupDir, 'docker'));
  }

  // create setup guide in SETUP
  const setupGuide = `HƯỚNG DẪN TRIỂN KHAI HỆ THỐNG MẦM XANH LMS 2.0 (SETUP GUIDE)
========================================================================
1. Yêu cầu: Đã cài Docker Desktop và Docker Compose.
2. Di chuyển vào thư mục này:
   cd CD_DVD_SUBMISSION/MamXanhLMS/SETUP
3. Khởi động 5 dịch vụ container (Caddy 2, PHP 8.3 + Apache, MariaDB 10.11, Redis 7, Cron Worker):
   docker compose up -d --build
4. Nhập dữ liệu cơ sở dữ liệu mẫu:
   docker compose exec -T moodle_db mariadb -u moodle_user -pMX!DbPass2026 moodle_mamxanh < ../DATABASE/mamxanh_lms_db_backup.sql
5. Truy cập hệ thống tại:
   http://localhost:8090
`;
  fs.writeFileSync(path.join(setupDir, 'HUONG_DAN_TRIEN_KHAI_SETUP.txt'), setupGuide, 'utf8');

  // 2b. DATABASE folder (Export clean live utf8mb4 SQL dump from moodle_db)
  const dbDir = path.join(CD_ROOT, 'MamXanhLMS', 'DATABASE');
  fs.mkdirSync(dbDir, { recursive: true });
  const sqlDumpPath = path.join(dbDir, 'mamxanh_lms_db_backup.sql');
  try {
    console.log('[CD] Exporting live utf8mb4 database dump to DATABASE/mamxanh_lms_db_backup.sql...');
    execSync(`docker exec moodle_db mariadb-dump -u moodle_user -pMX!DbPass2026 --default-character-set=utf8mb4 --single-transaction moodle_mamxanh > "${sqlDumpPath}"`, { stdio: 'inherit' });
    const dumpSizeMB = (fs.statSync(sqlDumpPath).size / (1024 * 1024)).toFixed(2);
    console.log(`[CD] Exported mamxanh_lms_db_backup.sql (${dumpSizeMB} MB)`);
  } catch (e) {
    console.warn('[CD] Warning: Could not run live docker mariadb-dump:', e.message);
  }

  // DEMO_COURSE_KIT
  const demoKitDir = path.join(setupDir, 'DEMO_COURSE_KIT');
  fs.mkdirSync(demoKitDir, { recursive: true });
  fs.writeFileSync(path.join(demoKitDir, 'README_DEMO_KIT.txt'), 'Chứa bộ học liệu thử nghiệm và hướng dẫn tải mẫu khóa học.', 'utf8');

  // 3. SOURCE folder
  const sourceDir = path.join(CD_ROOT, 'MamXanhLMS', 'SOURCE');
  fs.mkdirSync(sourceDir, { recursive: true });

  const customSrc = path.resolve('../../moodle_custom');
  if (fs.existsSync(customSrc)) {
    console.log('[CD] Copying moodle_custom to SOURCE (excluding .git)...');
    copyDirSync(customSrc, path.join(sourceDir, 'moodle_custom'), ['.git']);
  }

  const scriptsSrc = path.resolve('../../scripts');
  if (fs.existsSync(scriptsSrc)) {
    console.log('[CD] Copying scripts to SOURCE (excluding node_modules)...');
    copyDirSync(scriptsSrc, path.join(sourceDir, 'scripts'), ['node_modules', '.git']);
  }

  // 4. Generate CD Label & Readme
  await generateCdLabelDocx();
  generateReadmeFile();

  console.log('[CD] CD_DVD_SUBMISSION packaging complete!');
}

packageCdSubmission().catch(err => {
  console.error('[FATAL ERROR IN CD PACKAGING]:', err);
  process.exit(1);
});
