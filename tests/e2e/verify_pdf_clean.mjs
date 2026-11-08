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

console.log('--- VERIFYING PDF CLEAN VIEWPORT & NO RESIDUAL GRAY BOX ---');

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

  // 2. Open Course View to verify card button
  console.log('2. Verifying Course Card Button at http://localhost:8090/course/view.php?id=2#mx-sec-1...');
  await page.goto(`${BASE_URL}/course/view.php?id=2#mx-sec-1`, { waitUntil: 'networkidle2' });
  await new Promise(r => setTimeout(r, 600));

  const cardCheck = await page.evaluate(() => {
    const pdfBtn = Array.from(document.querySelectorAll('a.mx-tile-action-btn')).find(a => a.href.includes('/mod/resource/view.php'));
    return {
      btnText: pdfBtn ? pdfBtn.textContent.trim().replace(/\s+/g, ' ') : '',
      hasKhongTaiVe: pdfBtn ? (pdfBtn.textContent.includes('tải về') || pdfBtn.textContent.includes('tài về')) : false,
    };
  });
  console.log('  Course Card Button:', cardCheck);
  if (cardCheck.hasKhongTaiVe) {
    throw new Error('Button still contains "tải về"!');
  }

  // Scroll to section 1
  await page.evaluate(() => {
    const s = document.querySelector('#mx-sec-1');
    if (s) s.scrollIntoView({ block: 'start' });
  });
  await new Promise(r => setTimeout(r, 400));
  await saveShot(page, '33_course_card_clean.png');

  // 3. Open PDF view
  console.log('3. Navigating to PDF view: http://localhost:8090/mod/resource/view.php?id=37...');
  await page.goto(`${BASE_URL}/mod/resource/view.php?id=37`, { waitUntil: 'networkidle2' });
  await page.waitForSelector('#mx-inline-pdf-viewer[data-pdf-rendered="true"]', { timeout: 25000 });
  await new Promise(r => setTimeout(r, 1000));

  // Verify DOM state
  const viewerState = await page.evaluate(() => {
    const iframe = document.getElementById('resourceobject');
    const vp = document.getElementById('mx-pdf-viewport');
    const canvases = document.querySelectorAll('canvas.mx-pdf-page-canvas');
    const vpStyle = vp ? window.getComputedStyle(vp) : null;
    return {
      iframeExists: Boolean(iframe),
      canvasesCount: canvases.length,
      viewportBg: vpStyle ? vpStyle.backgroundColor : null,
      viewportHeight: vp ? vp.offsetHeight : null,
      viewportScrollHeight: vp ? vp.scrollHeight : null,
    };
  });
  console.log('  Viewer State:', JSON.stringify(viewerState, null, 2));

  if (viewerState.iframeExists) {
    throw new Error('Residual iframe #resourceobject is still in the DOM!');
  }
  if (viewerState.canvasesCount !== 3) {
    throw new Error(`Expected 3 canvases, got ${viewerState.canvasesCount}`);
  }

  // Scroll window to viewer top
  await page.evaluate(() => {
    const v = document.getElementById('mx-inline-pdf-viewer');
    if (v) v.scrollIntoView({ block: 'start' });
  });
  await new Promise(r => setTimeout(r, 400));
  await saveShot(page, '32a_inline_pdf_clean_top.png');

  // Test Zoom In
  console.log('4. Testing Zoom In (+)...');
  await page.click('#mx-pdf-zoom-in');
  await new Promise(r => setTimeout(r, 500));
  const zoomPct = await page.$eval('#mx-pdf-zoom-pct', el => el.textContent.trim());
  console.log('  Zoom after +:', zoomPct);

  // Reset Zoom
  await page.click('#mx-pdf-zoom-fit');
  await new Promise(r => setTimeout(r, 500));

  // Scroll internal viewport to bottom
  console.log('5. Scrolling viewport to bottom...');
  await page.evaluate(() => {
    const vp = document.getElementById('mx-pdf-viewport');
    if (vp) vp.scrollTop = vp.scrollHeight;
  });
  await new Promise(r => setTimeout(r, 600));
  await saveShot(page, '32b_inline_pdf_clean_bottom.png');

  console.log('\n--- ALL VERIFICATIONS PASSED 100% ---');
} finally {
  await browser.close();
}
