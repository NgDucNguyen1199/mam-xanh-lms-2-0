import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE = 'http://localhost:8090';
const OUT = path.resolve('../../docs/screenshots');
fs.mkdirSync(OUT, { recursive: true });

const USERS = {
  student: ['nd_rau_lamdong', 'MX!Rau2026'],
  cbkt: ['cbkt_pilot', 'MX!Canbo2026'],
  admin: ['admin', 'Admin!2026MX'],
};

const SHOTS = [
  ['student', '103_student_quiz_view.png', '/mod/quiz/view.php?id=18&lang=vi'],
  ['student', '104_fieldpractice_student.png', '/mod/fieldpractice/view.php?id=17&lang=vi'],
  ['cbkt', '105_fieldpractice_grading_cbkt.png', '/mod/fieldpractice/grade.php?id=17&lang=vi'],
  ['student', '106_rbac_403_student_grade.png', '/mod/fieldpractice/grade.php?id=17&lang=vi'],
  ['guest', '107_public_cert_verify.png', '/local/mamxanhcert/verify.php?code=MXCERT-2026-CHAMSOC01-0042&lang=vi'],
  ['student', '108_agriops_farmer.png', '/local/agriops/index.php?lang=vi'],
  ['guest', '109_public_trace_passport.png', '/local/agriops/trace.php?code=VG-LD-202611-0001&lang=vi'],
  ['admin', '110_admin_audit.png', '/local/mamxanhapp/admin_audit.php?lang=vi'],
  ['student', '111_calculator.png', '/local/mamxanhapp/calculator.php?lang=vi'],
  ['student', '112_farmer_profile.png', '/local/farmerprofile/index.php?lang=vi'],
  ['cbkt', '113_assign_grading_cbkt.png', '/mod/assign/view.php?id=139&action=grading&lang=vi'],
  ['student', '114_assign_student.png', '/mod/assign/view.php?id=139&lang=vi'],
  ['student', '115_gradebook_student.png', '/grade/report/user/index.php?id=2&lang=vi'],
  ['student', '116_scorm_student.png', '/mod/scorm/view.php?id=39&lang=vi'],
  ['student', '117_h5p_student.png', '/mod/h5pactivity/view.php?id=38&lang=vi'],
  ['cbkt', '118_participants_cbkt.png', '/user/index.php?id=2&lang=vi'],
  ['student', '119_mobile_dashboard.png', '/my/?lang=vi', { w: 390, h: 844 }],
  ['student', '120_mobile_course.png', '/course/view.php?id=2&lang=vi', { w: 390, h: 844 }],
  ['cbkt', '121_attendance_manage_cbkt.png', '/mod/attendance/manage.php?id=134&lang=vi'],
  ['student', '122_cert_view_student.png', '/local/mamxanhcert/view.php?code=MXCERT-2026-CHAMSOC01-0042&lang=vi'],
  ['student', '123_calendar_student.png', '/calendar/view.php?view=month&lang=vi'],
  ['cbkt', '124_cbkt_dashboard.png', '/my/?lang=vi'],
  ['student', '125_quiz_attempt_review.png', '/mod/quiz/view.php?id=6&lang=vi'],
  ['cbkt', '126_cbkt_transcript_feedback.png', '/local/mamxanhapp/feedback_sheet.php?courseid=2&lang=vi'],
];

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: 'new',
  args: ['--no-sandbox', '--disable-gpu'],
  defaultViewport: { width: 1440, height: 900 },
});

async function newSession(role) {
  const ctx = await browser.createBrowserContext();
  const page = await ctx.newPage();
  if (role !== 'guest') {
    await page.goto(`${BASE}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
    const [u, p] = USERS[role];
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle2' }),
      page.evaluate((u, p) => {
        document.getElementById('username').value = u;
        document.getElementById('password').value = p;
        document.getElementById('loginbtn').click();
      }, u, p),
    ]);
  }
  return { ctx, page };
}

const sessions = {};
const report = [];
for (const [role, file, url, vp] of SHOTS) {
  if (!sessions[role]) sessions[role] = await newSession(role);
  const { page } = sessions[role];
  await page.setViewport({ width: vp?.w || 1440, height: vp?.h || 900, deviceScaleFactor: 1 });
  let status = 0;
  try {
    const resp = await page.goto(BASE + url, { waitUntil: 'networkidle2', timeout: 45000 });
    status = resp ? resp.status() : 0;
  } catch (e) {
    status = -1;
  }
  await new Promise(r => setTimeout(r, 500));
  const title = await page.title();
  const hasEmoji = await page.evaluate(() => /[\u{1F300}-\u{1FAFF}\u{2600}-\u{27BF}]/u.test(document.body.innerText));
  await page.screenshot({ path: path.join(OUT, file) });
  report.push({ file, status, title, hasEmoji });
}
console.log(JSON.stringify(report, null, 1));
await browser.close();
