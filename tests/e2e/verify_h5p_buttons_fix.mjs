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

console.log('--- VERIFYING H5P NAVIGATION BUTTONS FIX ---');

const browser = await puppeteer.launch({
  executablePath: CHROME_PATH,
  headless: true,
  acceptInsecureCerts: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors'],
});

try {
  const page = await browser.newPage();
  await page.setViewport({ width: 1366, height: 768 });

  // 1. Login as Farmer
  console.log('1. Logging in as Farmer nd_rau_lamdong...');
  await page.goto(`${BASE_URL}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('#mx-1click-farmer'),
  ]);

  // 2. Open H5P Activity
  console.log('2. Opening H5P Activity: http://localhost:8090/mod/h5pactivity/view.php?id=47&forceview=1...');
  await page.goto(`${BASE_URL}/mod/h5pactivity/view.php?id=47&forceview=1`, { waitUntil: 'networkidle2' });
  await new Promise(r => setTimeout(r, 3500));

  let innerFrame = null;
  for (const f of page.frames()) {
    if (f.url() === 'about:blank') {
      innerFrame = f;
      break;
    }
  }

  if (!innerFrame) {
    throw new Error('Could not find H5P inner frame (about:blank)!');
  }

  // Scroll window to center H5P player
  await page.evaluate(() => {
    const h5pEl = document.querySelector('iframe.h5p-player, iframe[src*="h5p"]');
    if (h5pEl) h5pEl.scrollIntoView({ block: 'center' });
  });
  await new Promise(r => setTimeout(r, 500));

  const getVisibleButtons = async () => {
    return await innerFrame.evaluate(() => {
      const allBtns = Array.from(document.querySelectorAll('button'));
      return allBtns.filter(b => b.offsetWidth > 0 && b.offsetHeight > 0).map(b => ({
        text: b.textContent.trim(),
        ariaLabel: b.getAttribute('aria-label') || '',
        classes: b.className,
        bgColor: window.getComputedStyle(b).backgroundColor,
        color: window.getComputedStyle(b).color
      }));
    });
  };

  const initialBtns = await getVisibleButtons();
  console.log('Visible buttons in H5P:', JSON.stringify(initialBtns, null, 2));

  // Assert NO button is empty
  const emptyBtns = initialBtns.filter(b => b.text === '');
  if (emptyBtns.length > 0) {
    throw new Error(`Found ${emptyBtns.length} visible empty buttons!`);
  }

  // Save screenshot of initial state
  await saveShot(page, '34a_h5p_enhanced_nav_buttons.png');

  // If there is a check answer button, click it to see review state
  await innerFrame.evaluate(() => {
    const opts = document.querySelectorAll('.h5p-theme-list-item');
    if (opts.length > 0) opts[0].click();
    const checkBtn = document.querySelector('button.h5p-theme-check');
    if (checkBtn) checkBtn.click();
  });
  await new Promise(r => setTimeout(r, 1000));

  const reviewBtns = await getVisibleButtons();
  console.log('Visible buttons on review state:', JSON.stringify(reviewBtns, null, 2));

  const emptyReviewBtns = reviewBtns.filter(b => b.text === '');
  if (emptyReviewBtns.length > 0) {
    throw new Error(`Found ${emptyReviewBtns.length} visible empty buttons in review state!`);
  }

  await saveShot(page, '34b_h5p_enhanced_both_nav_buttons.png');

  console.log('\n--- H5P BUTTONS ENHANCER VERIFIED 100% SUCCESSFULLY ---');
} finally {
  await browser.close();
}
