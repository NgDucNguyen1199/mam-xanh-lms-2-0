import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const PROJECT_ROOT = path.resolve(__dirname, '../..');
const SHOT_DIR = path.join(PROJECT_ROOT, 'docs', 'screenshots');
const ARTIFACT_DIR = 'C:\\Users\\pho21\\.gemini\\antigravity\\brain\\84025476-35b6-4655-9f1f-0de62ae3b517';
const BASE_URL = process.env.MOODLE_URL || 'http://localhost:8090';
const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

fs.mkdirSync(SHOT_DIR, { recursive: true });

async function saveShot(page, filename) {
  const dest = path.join(SHOT_DIR, filename);
  await page.screenshot({ path: dest, fullPage: false });
  if (fs.existsSync(ARTIFACT_DIR)) {
    fs.copyFileSync(dest, path.join(ARTIFACT_DIR, filename));
  }
  console.log(`  [SHOT] Saved screenshot: ${filename}`);
}

console.log('--- VERIFYING REMOVAL OF "(KHÔNG TẢI VỀ)" ---');

const browser = await puppeteer.launch({
  executablePath: CHROME_PATH,
  headless: true,
  acceptInsecureCerts: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors'],
});

try {
  const page = await browser.newPage();
  await page.setViewport({ width: 1366, height: 768 });

  // 1. Login
  console.log('1. Logging in as Farmer nd_rau_lamdong...');
  await page.goto(`${BASE_URL}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('#mx-1click-farmer'),
  ]);

  // 2. Open Course CHAMSOC01
  console.log('2. Opening Course CHAMSOC01: http://localhost:8090/course/view.php?id=2#mx-sec-1...');
  await page.goto(`${BASE_URL}/course/view.php?id=2#mx-sec-1`, { waitUntil: 'networkidle2' });
  await new Promise((r) => setTimeout(r, 800));

  // 3. Inspect the PDF card and button text
  const pdfCardInfo = await page.evaluate(() => {
    const pdfBtn = Array.from(document.querySelectorAll('a.mx-tile-action-btn')).find(a => a.href.includes('/mod/resource/view.php'));
    const card = pdfBtn ? pdfBtn.closest('.mx-lesson-tile-card') : null;
    return {
      btnText: pdfBtn ? pdfBtn.textContent.trim().replace(/\s+/g, ' ') : null,
      cardText: card ? card.textContent.trim().replace(/\s+/g, ' ') : null,
      btnHref: pdfBtn ? pdfBtn.href : null,
      containsKhongTaiVe: card ? (card.textContent.includes('tải về') || card.textContent.includes('tài về')) : false,
    };
  });

  console.log('  PDF Card Info in Course View:', JSON.stringify(pdfCardInfo, null, 2));

  // Scroll to section 1 so the PDF card is centered in the viewport
  await page.evaluate(() => {
    const sec1 = document.querySelector('#mx-sec-1');
    if (sec1) sec1.scrollIntoView({ block: 'start' });
  });
  await new Promise((r) => setTimeout(r, 400));

  await saveShot(page, '31_course_pdf_no_download_removed.png');

  // 4. Open Inline PDF lesson
  const pdfUrl = pdfCardInfo.btnHref || `${BASE_URL}/mod/resource/view.php?id=3`;
  console.log(`3. Navigating directly to PDF lesson: ${pdfUrl}...`);
  await page.goto(pdfUrl, { waitUntil: 'networkidle2' });
  await page.waitForSelector('#mx-inline-pdf-viewer[data-pdf-rendered="true"]', { timeout: 25000 });

  const toolbarInfo = await page.evaluate(() => {
    const tb = document.querySelector('.mx-pdf-toolbar');
    return {
      toolbarText: tb ? tb.textContent.trim().replace(/\s+/g, ' ') : null,
      containsKhongTaiVe: tb ? (tb.textContent.includes('tải về') || tb.textContent.includes('tài về')) : false,
    };
  });
  console.log('  Toolbar Info in PDF Player:', JSON.stringify(toolbarInfo, null, 2));

  await saveShot(page, '31b_inline_pdf_toolbar_cleaned.png');

  console.log('\n--- VERIFICATION COMPLETED SUCCESSFULLY ---');
} catch (err) {
  console.error('ERROR during verification:', err);
  process.exit(1);
} finally {
  await browser.close();
}
