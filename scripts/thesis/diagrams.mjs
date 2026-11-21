// Tạo các sơ đồ minh họa (kiến trúc, use case, ER, quy trình) dưới dạng PNG bằng SVG + Chrome headless.
import puppeteer from '../../tests/e2e/node_modules/puppeteer-core/lib/esm/puppeteer/puppeteer-core.js';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT = path.join(__dirname, 'build', 'diagrams');
fs.mkdirSync(OUT, { recursive: true });

const C = { sage: '#234E3E', soft: '#EAF2EE', line: '#475569', border: '#94A3B8', ink: '#0F172A', warn: '#FEF3C7', warnb: '#B45309', blue: '#E0ECF8', blueb: '#2F5D8A', grey: '#F1F5F9' };
const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

function txt(x, y, lines, o = {}) {
  const fs_ = o.fs || 14, lh = o.lh || fs_ + 4;
  const arr = Array.isArray(lines) ? lines : [lines];
  const startY = y - ((arr.length - 1) * lh) / 2;
  return arr.map((l, i) => `<text x="${x}" y="${startY + i * lh}" text-anchor="${o.anchor || 'middle'}" dominant-baseline="middle" font-family="Arial, Helvetica, sans-serif" font-size="${fs_}" font-weight="${(o.bold && i === 0) || o.boldAll ? 700 : 400}" fill="${o.color || C.ink}">${esc(l)}</text>`).join('');
}
const box = (x, y, w, h, lines, o = {}) =>
  `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${o.rx ?? 8}" fill="${o.fill || '#fff'}" stroke="${o.stroke || C.sage}" stroke-width="${o.sw || 1.6}" ${o.dash ? 'stroke-dasharray="6 4"' : ''}/>` + txt(x + w / 2, y + h / 2, lines, o);
const ell = (cx, cy, rx, ry, lines, o = {}) =>
  `<ellipse cx="${cx}" cy="${cy}" rx="${rx}" ry="${ry}" fill="${o.fill || C.soft}" stroke="${o.stroke || C.sage}" stroke-width="1.4"/>` + txt(cx, cy, lines, { fs: 12.5, lh: 15, ...o });
const dia = (cx, cy, w, h, lines, o = {}) =>
  `<polygon points="${cx},${cy - h / 2} ${cx + w / 2},${cy} ${cx},${cy + h / 2} ${cx - w / 2},${cy}" fill="${o.fill || C.warn}" stroke="${o.stroke || C.warnb}" stroke-width="1.5"/>` + txt(cx, cy, lines, { fs: 12.5, lh: 15, ...o });
function arrow(pts, o = {}) {
  const d = pts.map((p, i) => (i ? 'L' : 'M') + p[0] + ' ' + p[1]).join(' ');
  let s = `<path d="${d}" fill="none" stroke="${o.color || C.line}" stroke-width="1.6" ${o.dash ? 'stroke-dasharray="6 4"' : ''} ${o.noarrow ? '' : 'marker-end="url(#ah)"'} ${o.both ? 'marker-start="url(#ahs)"' : ''}/>`;
  if (o.label) {
    const a = pts[Math.floor((pts.length - 1) / 2)], b = pts[Math.floor((pts.length - 1) / 2) + 1];
    const mx = (a[0] + b[0]) / 2 + (o.lx || 0), my = (a[1] + b[1]) / 2 + (o.ly || -9);
    s += `<rect x="${mx - (o.label.length * 3.4 + 4)}" y="${my - 9}" width="${o.label.length * 6.8 + 8}" height="17" fill="#fff" opacity="0.92"/>` + txt(mx, my, o.label, { fs: 11.5, color: C.line });
  }
  return s;
}
function actor(x, y, label) {
  return `<circle cx="${x}" cy="${y}" r="11" fill="#fff" stroke="${C.sage}" stroke-width="2"/><path d="M${x} ${y + 11} V${y + 44} M${x - 20} ${y + 24} H${x + 20} M${x} ${y + 44} L${x - 16} ${y + 70} M${x} ${y + 44} L${x + 16} ${y + 70}" stroke="${C.sage}" stroke-width="2" fill="none"/>` + txt(x, y + 90, label, { fs: 13, lh: 16, bold: true });
}
const wrap = (w, h, inner) => `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}" viewBox="0 0 ${w} ${h}"><defs><marker id="ah" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="8" markerHeight="8" orient="auto-start-reverse"><path d="M0 0 L10 5 L0 10 z" fill="${C.line}"/></marker><marker id="ahs" viewBox="0 0 10 10" refX="1" refY="5" markerWidth="8" markerHeight="8" orient="auto-start-reverse"><path d="M10 0 L0 5 L10 10 z" fill="${C.line}"/></marker></defs><rect width="${w}" height="${h}" fill="#fff"/>${inner}</svg>`;

const D = {};

// ---------------- D1: Sơ đồ triển khai ----------------
D.D1_deployment = (() => {
  let s = '';
  s += box(20, 215, 170, 110, ['Người dùng', 'Trình duyệt / PWA', '(Nông hộ, CBKT, Admin)'], { bold: true, fill: C.grey, stroke: C.border });
  s += box(260, 40, 230, 100, ['moodle_proxy', 'Caddy 2 (Alpine)', 'HTTPS :8443, TLS nội bộ', 'Nén zstd / gzip'], { bold: true, fill: C.blue, stroke: C.blueb, lh: 18 });
  s += box(260, 205, 230, 130, ['moodle_app', 'PHP 8.3 + Apache, Moodle 4.5 LTS', 'OPcache + JIT, HTTP trực tiếp :8090', 'Plugin tùy biến (5) + Theme'], { bold: true, fill: C.soft, lh: 19 });
  s += box(260, 410, 230, 90, ['moodle_cron', 'Chạy cron.php mỗi 60 giây'], { bold: true, fill: C.soft });
  s += box(640, 120, 250, 100, ['moodle_db', 'MariaDB 10.11', 'utf8mb4_unicode_ci, 512 MB buffer'], { bold: true, fill: C.warn, stroke: C.warnb });
  s += box(640, 255, 250, 100, ['moodle_redis', 'Redis 7 (Alpine)', 'Session + Moodle Universal Cache'], { bold: true, fill: C.warn, stroke: C.warnb });
  s += box(640, 390, 250, 110, ['Lưu trữ', 'Named Volume: /var/moodledata', 'tmpfs RAM: /tmp (bộ nhớ đệm cục bộ)', 'Bind mount: moodle_custom, scripts'], { bold: true, fill: C.grey, stroke: C.border, lh: 19 });
  s += arrow([[190, 250], [225, 250], [225, 90], [260, 90]], { label: 'HTTPS 8443', lx: -4, ly: -8 });
  s += arrow([[190, 270], [260, 270]], { label: 'HTTP 8090', ly: -10 });
  s += arrow([[375, 140], [375, 205]], { label: 'HTTP :80 nội bộ', lx: 56, ly: 0 });
  s += arrow([[490, 250], [565, 250], [565, 170], [640, 170]], { label: 'SQL 3306', ly: -8 });
  s += arrow([[490, 290], [565, 290], [565, 305], [640, 305]], { label: '6379', ly: -2 });
  s += arrow([[490, 320], [530, 320], [530, 445], [640, 445]], { label: 'I/O tệp', ly: -9 });
  s += arrow([[375, 410], [375, 335]], { label: 'gọi cron', lx: 40, ly: 0 });
  return wrap(940, 530, s);
})();

// ---------------- D2: Kiến trúc phân tầng ----------------
D.D2_layers = (() => {
  let s = '';
  const L = (y, h, title, items, fill, stroke) => {
    s += `<rect x="20" y="${y}" width="900" height="${h}" rx="10" fill="${fill}" stroke="${stroke}" stroke-width="1.6"/>`;
    s += txt(40, y + 20, title, { anchor: 'start', fs: 14, bold: true, color: stroke });
    const n = items.length, gap = 12, w = (860 - gap * (n - 1)) / n;
    items.forEach((it, i) => { s += box(40 + i * (w + gap), y + 38, w, h - 52, it, { fs: 12.5, lh: 16, bold: true, fill: '#fff', stroke }); });
  };
  L(15, 110, 'TẦNG TRÌNH BÀY', [['Theme agritrain_child', 'Boost con, SCSS, Mustache'], ['Thanh điều hướng thống nhất', 'VI / EN, popover thông báo'], ['PWA', 'manifest, service worker'], ['Cổng thông tin công khai', 'Tin tức, tra cứu QR']], C.soft, C.sage);
  L(140, 130, 'TẦNG ỨNG DỤNG TÙY BIẾN (moodle_custom)', [['local_mamxanhapp', 'Điều hướng, quản lý,', 'nhân bản, bảng điểm'], ['local_farmerprofile', 'Hồ sơ nông hộ, cohort'], ['local_agriops', 'Lô VietGAP, nhật ký,', 'truy xuất QR'], ['local_mamxanhcert', 'Chứng nhận HMAC-SHA256'], ['mod_fieldpractice', 'QR + GPS, Rubric 4', 'tiêu chí'], ['mod_attendance', 'Điểm danh theo phiên']], C.blue, C.blueb);
  L(285, 120, 'TẦNG LÕI MOODLE 4.5 LTS', [['Khóa học & Ghi danh'], ['Quiz / Assign / Rubric'], ['SCORM 1.2 / H5P'], ['Gradebook & Hoàn thành'], ['Badges / Calendar'], ['RBAC (Capability)']], C.warn, C.warnb);
  L(420, 105, 'TẦNG DỮ LIỆU & HẠ TẦNG', [['MariaDB 10.11', 'utf8mb4'], ['Redis 7', 'Session, MUC'], ['moodledata', 'Named Volume'], ['Docker Compose', '5 dịch vụ']], C.grey, C.line);
  s += arrow([[470, 125], [470, 140]], { noarrow: true });
  return wrap(940, 540, s);
})();

// ---------------- D3: Use case ----------------
D.D3_usecase = (() => {
  let s = '';
  s += `<rect x="215" y="15" width="610" height="650" rx="12" fill="#fff" stroke="${C.sage}" stroke-width="2" stroke-dasharray="8 5"/>`;
  s += txt(520, 36, 'HỆ THỐNG MẦM XANH LMS 2.0', { fs: 15, bold: true, color: C.sage });
  const ax = { guest: 70, farmer: 70, cbkt: 970, admin: 970 };
  s += actor(70, 120, ['Khách', '(chưa đăng nhập)']);
  s += actor(70, 420, ['Nông hộ /', 'Học viên']);
  s += actor(970, 190, ['Cán bộ', 'kỹ thuật']);
  s += actor(970, 480, ['Quản trị', 'viên']);
  const c1 = [
    [330, 75, ['Xem cổng thông tin', 'và tin tức']],
    [330, 135, ['Tra cứu chứng nhận', 'và lô VietGAP bằng QR']],
    [330, 195, ['Đăng nhập, chuyển', 'ngôn ngữ VI / EN']],
    [330, 255, ['Học bài giảng: video,', 'PDF, H5P, SCORM']],
    [330, 315, ['Điểm danh lý thuyết,', 'điểm danh QR + GPS']],
    [330, 375, ['Nộp bài thảo luận,', 'bài thu hoạch']],
    [330, 435, ['Làm bài trắc nghiệm']],
    [330, 495, ['Ghi nhật ký canh tác', 'VietGAP, nhập giọng nói']],
    [330, 555, ['Xem bảng điểm,', 'chứng nhận, huy hiệu']],
    [330, 615, ['Quản lý hồ sơ nông hộ']],
  ];
  const c2 = [
    [710, 75, ['Quản lý điểm danh', 'các phiên học']],
    [710, 140, ['Chấm điểm Rubric', '4 tiêu chí']],
    [710, 205, ['Phát mã QR xoay vòng']],
    [710, 270, ['Thống kê vùng trồng,', 'xuất CSV']],
    [710, 335, ['Nhân bản khóa học mẫu', 'theo địa phương']],
    [710, 400, ['Xuất báo cáo nhật ký', 'VietGAP (HTML, CSV)']],
    [710, 465, ['Quản trị người dùng,', 'phân quyền 3 cấp']],
    [710, 530, ['Kiểm toán bảo mật', 'và hạ tầng']],
    [710, 595, ['Sao lưu, phục hồi', 'dữ liệu']],
  ];
  c1.forEach(([x, y, t]) => (s += ell(x, y, 104, 23, t)));
  c2.forEach(([x, y, t]) => (s += ell(x, y, 104, 23, t)));
  const ln = (x1, y1, x2, y2) => (s += `<path d="M${x1} ${y1} L${x2} ${y2}" stroke="${C.line}" stroke-width="1.2" fill="none"/>`);
  [0, 1, 2].forEach((i) => ln(90, 150, 226 + 0, c1[i][1]));
  [2, 3, 4, 5, 6, 7, 8, 9].forEach((i) => ln(90, 450, 226, c1[i][1]));
  [0, 1, 4].forEach((i) => ln(950, 220, 814, c2[i === 4 ? 0 : i === 0 ? 0 : 1][1]));
  [0, 1, 2, 3, 4, 5].forEach((i) => ln(950, 220, 814, c2[i][1]));
  [6, 7, 8, 4, 3].forEach((i) => ln(950, 510, 814, c2[i][1]));
  return wrap(1040, 680, s);
})();

// ---------------- D4: ER ----------------
D.D4_er = (() => {
  let s = '';
  const ent = (x, y, w, name, fields, fill = '#fff') => {
    const lh = 19, h = 30 + fields.length * lh;
    s += `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="6" fill="${fill}" stroke="${C.sage}" stroke-width="1.6"/>`;
    s += `<rect x="${x}" y="${y}" width="${w}" height="28" rx="6" fill="${C.sage}"/>` + txt(x + w / 2, y + 14, name, { fs: 13, bold: true, color: '#fff' });
    fields.forEach((f, i) => (s += txt(x + 10, y + 42 + i * lh, f, { anchor: 'start', fs: 11.5, color: f.startsWith('PK') || f.startsWith('FK') ? C.sage : C.ink, boldAll: f.startsWith('PK') })));
    return { x, y, w, h };
  };
  const user = ent(380, 20, 190, 'mdl_user', ['PK id', 'username', 'firstname, lastname', 'email'], C.soft);
  const course = ent(380, 180, 190, 'mdl_course', ['PK id', 'shortname', 'fullname', 'visible'], C.soft);
  ent(20, 20, 220, 'local_farmerprofile', ['PK id', 'FK userid', 'farm_name, area_m2', 'main_crop, region', 'gps_lat, gps_lng', 'cert_standard, phone', 'cohortid'], C.blue);
  ent(690, 20, 230, 'local_mamxanhcert', ['PK id', 'FK userid', 'FK courseid', 'cert_code (duy nhất)', 'final_score', 'hmac_signature', 'status, timecreated'], C.blue);
  ent(20, 270, 220, 'local_agriops_batch', ['PK id', 'FK userid', 'batch_code, product_name', 'crop_variety, region', 'area_m2, expected_yield_kg', 'vietgap_status', 'qr_token'], C.blue);
  ent(20, 470, 220, 'local_agriops_log', ['PK id', 'FK batchid', 'FK userid', 'activity_type, notes', 'ph_val, ec_val, phi_days', 'voice_input, log_time'], C.blue);
  ent(380, 330, 190, 'fieldpractice', ['PK id', 'FK course', 'name, location_name', 'target_lat, target_lng', 'grade, qr_secret'], C.blue);
  ent(660, 330, 260, 'fieldpractice_sub', ['PK id', 'FK fieldpracticeid', 'FK userid', 'checkin_code, checkin_time', 'gps_lat, gps_lng', 'crit_prep, crit_tech', 'crit_biosafe, crit_quality', 'total_grade, feedback', 'graderid, graded_time'], C.blue);
  const ar = (pts, label, lx = 0, ly = -8) => (s += arrow(pts, { noarrow: true, label, lx, ly }));
  ar([[240, 70], [380, 70]], '1 - 1');
  ar([[570, 70], [690, 70]], '1 - N');
  ar([[475, 128], [475, 180]], '', 0, 0);
  ar([[690, 120], [570, 215]], '1 - N', 30, -6);
  ar([[130, 330 - 60], [130, 270]], '', 0, 0);
  ar([[240, 300], [310, 300], [310, 110], [380, 110]], '1 - N', 0, -8);
  ar([[130, 470], [130, 435]], '1 - N', 28, 0);
  ar([[475, 270], [475, 330]], '1 - N', 28, 0);
  ar([[570, 380], [660, 380]], '1 - N');
  ar([[790, 330], [790, 160], [570, 160], [570, 100]], '1 - N', 0, -9);
  return wrap(940, 640, s);
})();

// ---------------- D5: Swimlane Điểm danh - Thảo luận - Chấm Rubric ----------------
D.D5_swimlane = (() => {
  let s = '';
  const lanes = [['Nông hộ / Học viên', 20], ['Cán bộ kỹ thuật', 200], ['Hệ thống Moodle', 380]];
  lanes.forEach(([n, y], i) => {
    s += `<rect x="20" y="${y}" width="960" height="170" fill="${i % 2 ? '#fff' : C.grey}" stroke="${C.border}"/>`;
    s += `<rect x="20" y="${y}" width="40" height="170" fill="${C.sage}"/>` + `<text x="40" y="${y + 85}" transform="rotate(-90 40 ${y + 85})" text-anchor="middle" font-family="Arial" font-size="13" font-weight="700" fill="#fff">${esc(n)}</text>`;
  });
  s += box(85, 55, 150, 70, ['Tham gia phiên', 'học, quét mã QR', 'và chia sẻ GPS'], { fs: 12.5, lh: 16 });
  s += box(290, 55, 150, 70, ['Nộp bài thảo luận', 'và bài thu hoạch', 'cuối khóa'], { fs: 12.5, lh: 16 });
  s += box(85, 235, 150, 70, ['Mở phiên điểm danh,', 'phát mã QR', 'xoay vòng 5 phút'], { fs: 12.5, lh: 16 });
  s += box(525, 235, 170, 70, ['Chấm Rubric', '4 tiêu chí', '(tổng 100 điểm)'], { fs: 12.5, lh: 16 });
  s += box(755, 235, 190, 70, ['Nhận xét, phản hồi', 'từng tiêu chí'], { fs: 12.5, lh: 16 });
  s += box(85, 415, 150, 70, ['Kiểm tra mã QR,', 'khoảng cách GPS,', 'ghi trạng thái'], { fs: 12.5, lh: 16 });
  s += box(290, 415, 150, 70, ['Lưu bài nộp,', 'cập nhật lịch', 'và hạn nộp'], { fs: 12.5, lh: 16 });
  s += box(525, 415, 170, 70, ['Ghi điểm vào', 'Gradebook, đánh dấu', 'hoàn thành hoạt động'], { fs: 12.5, lh: 16 });
  s += box(755, 415, 190, 70, ['Cập nhật tiến độ,', 'bảng điểm học thuật,', 'điều kiện chứng nhận'], { fs: 12.5, lh: 16 });
  s += arrow([[160, 235], [160, 125]], { label: 'Phát mã', lx: 28, ly: 0 });
  s += arrow([[160, 125], [160, 125]], { noarrow: true });
  s += arrow([[85 + 150, 90], [262, 90], [262, 450], [235, 450]], { noarrow: false });
  s += arrow([[365, 125], [365, 415]], { label: 'Gửi bài', lx: 30, ly: 0 });
  s += arrow([[440, 450], [525, 450]], { label: '' });
  s += arrow([[610, 415], [610, 305]], { label: 'Đồng bộ', lx: 34, ly: 0 });
  s += arrow([[440, 450], [480, 450], [480, 270], [525, 270]], { label: 'Chờ chấm', ly: -10 });
  s += arrow([[695, 270], [755, 270]]);
  s += arrow([[695, 450], [755, 450]]);
  s += arrow([[850, 305], [850, 415]], { label: 'Lưu', lx: 22, ly: 0 });
  return wrap(1000, 560, s);
})();

// ---------------- D6: Cấp và xác thực chứng nhận ----------------
D.D6_certflow = (() => {
  let s = '';
  s += box(20, 30, 190, 60, ['Học viên hoàn thành', 'khóa học'], { fill: C.soft, bold: true, lh: 17 });
  s += dia(335, 60, 220, 90, ['Đủ 3 điều kiện?', 'Điểm danh >= 80%', 'Quiz >= 70%, đã chấm Rubric'], { lh: 14, fs: 11.5 });
  s += box(460, 30, 190, 60, ['Từ chối, hiển thị', 'danh sách còn thiếu'], { fill: '#FDECEC', stroke: '#B91C1C', lh: 17 });
  s += box(20, 170, 200, 70, ['Sinh mã cert_code', 'MXCERT-năm-khóa-số'], { lh: 17, fill: C.soft });
  s += box(260, 170, 230, 70, ['Tính HMAC-SHA256', 'trên chuỗi chuẩn hóa', 'cert_code|userid|courseid|điểm|thời gian'], { lh: 15, fs: 12, fill: C.soft });
  s += box(530, 170, 200, 70, ['Lưu bản ghi vào', 'local_mamxanhcert,', 'cấp huy hiệu'], { lh: 16, fill: C.soft });
  s += box(20, 310, 200, 70, ['Tạo mã QR trỏ tới', 'verify.php?code=...'], { lh: 17, fill: C.soft });
  s += box(260, 310, 200, 70, ['Bên thứ ba quét QR', 'hoặc nhập mã tra cứu'], { lh: 17, fill: C.grey, stroke: C.border });
  s += box(500, 310, 230, 70, ['Hệ thống tính lại HMAC', 'và so khớp hash_equals'], { lh: 17, fill: C.soft });
  s += dia(615, 460, 200, 80, ['Chữ ký khớp?'], { fs: 13 });
  s += box(20, 430, 220, 60, ['HỢP LỆ: hiển thị họ tên,', 'khóa học, điểm, ngày cấp'], { lh: 16, fill: C.soft, bold: true });
  s += box(740, 430, 220, 60, ['KHÔNG HỢP LỆ: cảnh báo', 'dữ liệu bị chỉnh sửa'], { lh: 16, fill: '#FDECEC', stroke: '#B91C1C', bold: true });
  s += arrow([[210, 60], [225, 60]]);
  s += arrow([[445, 60], [460, 60]], { label: 'Không', ly: -12 });
  s += arrow([[335, 105], [335, 140], [120, 140], [120, 170]], { label: 'Đủ', lx: 40, ly: -9 });
  s += arrow([[220, 205], [260, 205]]);
  s += arrow([[490, 205], [530, 205]]);
  s += arrow([[630, 240], [630, 275], [120, 275], [120, 310]]);
  s += arrow([[220, 345], [260, 345]]);
  s += arrow([[460, 345], [500, 345]]);
  s += arrow([[615, 380], [615, 420]]);
  s += arrow([[515, 460], [240, 460]], { label: 'Khớp', ly: -10 });
  s += arrow([[715, 460], [740, 460]], { label: 'Lệch', ly: -12 });
  return wrap(990, 520, s);
})();

// ---------------- D7: Nhân bản khóa học ----------------
D.D7_cloneflow = (() => {
  let s = '';
  const st = [
    ['CBKT chọn khóa học mẫu', '(MASTER-AGRITECH-TPL)'],
    ['Nhập tên lớp, mã lớp,', 'cohort / vùng trồng'],
    ['backup_controller', 'sao lưu cấu trúc,', 'hoạt động, câu hỏi'],
    ['restore_controller', 'phục hồi thành', 'khóa học mới'],
    ['Đổi tên, mã ngắn,', 'hiển thị khóa học,', 'đặt lại ngày khai giảng'],
    ['Ghi danh cohort,', 'xây lại bộ nhớ đệm', 'khóa học'],
  ];
  st.forEach((t, i) => {
    const x = 20 + (i % 3) * 320, y = i < 3 ? 30 : 200;
    s += box(x, y, 270, 90, t, { fs: 13, lh: 18, bold: i === 2 || i === 3, fill: i === 2 || i === 3 ? C.blue : C.soft, stroke: i === 2 || i === 3 ? C.blueb : C.sage });
  });
  s += arrow([[290, 75], [340, 75]]);
  s += arrow([[610, 75], [660, 75]]);
  s += arrow([[795, 120], [795, 150], [155, 150], [155, 200]]);
  s += arrow([[290, 245], [340, 245]]);
  s += arrow([[610, 245], [660, 245]]);
  s += box(250, 350, 500, 60, ['Kết quả: khóa học địa phương mới, giữ 100% cấu trúc và học liệu của khóa mẫu'], { fill: C.sage, stroke: C.sage, color: '#fff', bold: true, fs: 14 });
  s += arrow([[795, 290], [795, 320], [500, 320], [500, 350]]);
  return wrap(980, 440, s);
})();

// ---------------- D8: Cấu trúc khóa học chuẩn ----------------
D.D8_coursestruct = (() => {
  let s = '';
  const cols = [
    ['Thông tin chung', ['Diễn đàn thông báo', 'Đề cương học phần (Trang)', 'Tiêu chuẩn / sổ tay (PDF xem trực tiếp)']],
    ['Chuyên đề 1', ['Video và infographic (Trang)', 'Sổ tay kỹ thuật (PDF)']],
    ['Chuyên đề 2', ['Bài giảng tra cứu (Trang)', 'H5P tương tác', 'Gói SCORM 1.2']],
    ['Chuyên đề 3', ['Thẻ lật / học liệu IPM', 'Thực hành tại vườn', '(QR + GPS + Rubric)']],
    ['Chuyên đề 4', ['Quản lý dịch hại tổng hợp', 'Phương pháp điều tra sinh', 'thái, thiên địch']],
    ['Kiểm tra và Đánh giá', ['Điểm danh lý thuyết', 'Điểm danh thực hành', 'Thảo luận buổi 1, 2', 'Bài tập, bài thu hoạch (Rubric)', 'Trắc nghiệm cuối khóa']],
  ];
  cols.forEach(([t, items], i) => {
    const x = 15 + i * 158, w = 148;
    s += box(x, 20, w, 54, t.split(' ').length > 2 ? [t.split(' ').slice(0, 2).join(' '), t.split(' ').slice(2).join(' ')] : t, { fill: i === 5 ? C.sage : C.soft, color: i === 5 ? '#fff' : C.ink, bold: true, fs: 13, lh: 16, boldAll: true });
    items.forEach((it, j) => { s += box(x, 86 + j * 52, w, 44, it.length > 24 ? [it.slice(0, it.lastIndexOf(' ', 24)), it.slice(it.lastIndexOf(' ', 24) + 1)] : it, { fs: 11.5, lh: 14, stroke: C.border }); });
  });
  s += `<path d="M15 360 H953" stroke="${C.border}" stroke-width="1"/>`;
  s += txt(484, 385, 'Mỗi khóa học: 1 phần chung + 4 chuyên đề + 1 phần kiểm tra và đánh giá (hoàn thành tuần tự, hỗ trợ khóa mở khóa theo điều kiện)', { fs: 13, color: C.line });
  return wrap(968, 410, s);
})();

// ---------------- D9: Quy trình kiểm thử tự động ----------------
D.D9_testflow = (() => {
  let s = '';
  s += box(20, 60, 190, 90, ['Puppeteer', 'Chrome headless', 'verify_full_crawl.mjs'], { bold: true, fill: C.blue, stroke: C.blueb, lh: 18 });
  s += box(260, 20, 190, 50, ['Khách (Guest)'], { fill: C.soft });
  s += box(260, 80, 190, 50, ['Nông hộ (student)'], { fill: C.soft });
  s += box(260, 140, 190, 50, ['Cán bộ kỹ thuật'], { fill: C.soft });
  s += box(260, 200, 190, 50, ['Quản trị viên'], { fill: C.soft });
  s += box(500, 90, 200, 110, ['66 URL', 'cổng thông tin, 8 khóa học,', 'chức năng mở rộng,', 'trang quản trị'], { bold: true, lh: 17, fill: C.grey, stroke: C.border });
  s += box(750, 15, 210, 56, ['HTTP 404 / 500 = 0'], { fill: '#fff' });
  s += box(750, 80, 210, 56, ['Lỗi PHP / cảnh báo = 0'], { fill: '#fff' });
  s += box(750, 145, 210, 56, ['Mã màu inline cũ = 0'], { fill: '#fff' });
  s += box(750, 210, 210, 56, ['Chuỗi lỗi "??" trong DOM = 0'], { fill: '#fff' });
  s += arrow([[210, 105], [235, 105], [235, 45], [260, 45]]);
  s += arrow([[210, 105], [260, 105]]);
  s += arrow([[210, 105], [235, 105], [235, 165], [260, 165]]);
  s += arrow([[210, 105], [235, 105], [235, 225], [260, 225]]);
  [45, 105, 165, 225].forEach((y) => (s += arrow([[450, y], [500, 145]])));
  [43, 108, 173, 238].forEach((y) => (s += arrow([[700, 145], [750, y]])));
  return wrap(980, 290, s);
})();

// ---------------- Render ----------------
const browser = await puppeteer.launch({ executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe', headless: 'new', args: ['--no-sandbox'] });
const page = await browser.newPage();
for (const [name, svg] of Object.entries(D)) {
  const m = svg.match(/width="(\d+)" height="(\d+)"/);
  const w = +m[1], h = +m[2];
  await page.setViewport({ width: w, height: h, deviceScaleFactor: 2 });
  await page.setContent(`<html><body style="margin:0">${svg}</body></html>`);
  await page.screenshot({ path: path.join(OUT, name + '.png'), clip: { x: 0, y: 0, width: w, height: h } });
  console.log('OK', name, w, h);
}
await browser.close();
