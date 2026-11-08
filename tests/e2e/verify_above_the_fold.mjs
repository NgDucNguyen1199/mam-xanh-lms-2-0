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
  console.log(`  [SHOT] Saved viewport screenshot: ${filename}`);
}

console.log('--- STARTING ABOVE-THE-FOLD 1366x768 UI/UX VERIFICATION ---');

const browser = await puppeteer.launch({
  executablePath: CHROME_PATH,
  headless: true,
  acceptInsecureCerts: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors'],
});

try {
  const page = await browser.newPage();
  // Exact 1366 x 768 viewport requested by user
  await page.setViewport({ width: 1366, height: 768 });

  // 1. Login as Farmer
  console.log('1. Logging in as Farmer nd_rau_lamdong...');
  await page.goto(`${BASE_URL}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('#mx-1click-farmer'),
  ]);

  console.log('2. Navigating to Dashboard http://localhost:8090/local/mamxanhapp/index.php?lang=vi...');
  await page.goto(`${BASE_URL}/local/mamxanhapp/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  await new Promise((r) => setTimeout(r, 800));

  // 3. Find and Hover on "Bảng Điều khiển" in Navbar
  console.log('3. Locating and Hovering on "Bảng Điều khiển" nav-link in .primary-navigation...');
  const hoverTarget = await page.evaluate(() => {
    const links = Array.from(document.querySelectorAll('.primary-navigation .more-nav > li a.nav-link'));
    // Find "Bảng Điều khiển" or 2nd link
    const target = links.find(a => (a.textContent.includes('Điều khiển') || a.textContent.includes('Điều khiển') || a.href.includes('/my/'))) || links[1];
    if (!target) return null;
    const r = target.getBoundingClientRect();
    return {
      text: target.textContent.trim(),
      x: r.x + r.width / 2,
      y: r.y + r.height / 2,
    };
  });

  if (hoverTarget) {
    console.log(`  Found target: "${hoverTarget.text}". Moving mouse to (${Math.round(hoverTarget.x)}, ${Math.round(hoverTarget.y)})...`);
    await page.mouse.move(hoverTarget.x, hoverTarget.y);
    await new Promise((r) => setTimeout(r, 600));

    const hoverStyles = await page.evaluate(() => {
      const links = Array.from(document.querySelectorAll('.primary-navigation .more-nav > li a.nav-link'));
      const target = links.find(a => (a.textContent.includes('Điều khiển') || a.textContent.includes('Điều khiển') || a.href.includes('/my/'))) || links[1];
      if (!target) return null;
      const cs = window.getComputedStyle(target);
      return {
        text: target.textContent.trim(),
        color: cs.color,
        backgroundColor: cs.backgroundColor,
        borderBottom: cs.borderBottom,
      };
    });
    console.log('  Hovered nav link computed styles:', JSON.stringify(hoverStyles, null, 2));
  }

  // 4. Inspect Geometry Above the Fold
  const layoutAudit = await page.evaluate(() => {
    const hero = document.querySelector('.mx-hero-banner');
    const heroRect = hero ? hero.getBoundingClientRect() : null;
    const bento = document.querySelector('.mx-bento-grid');
    const firstBentoCard = document.querySelector('.mx-bento-card');
    const cardRect = firstBentoCard ? firstBentoCard.getBoundingClientRect() : null;
    const dock = document.querySelector('.bottom-nav-bar');
    const dockRect = dock ? dock.getBoundingClientRect() : null;
    const pageHeader = document.querySelector('#page-header');
    const smartUtilBar = document.querySelector('.mx-smart-utility-bar');
    const footerHelpBtn = document.querySelector('#page-footer .btn-footer-popover, #page-footer a[data-action="footer-popover"]');
    const standaloneFab = document.querySelector('button#mx-crop-doctor-fab');
    const dockAiBtn = document.querySelector('.bottom-nav-bar #mx-crop-doctor-fab, .bottom-nav-bar .mx-bnav-ai');
    const dockItems = Array.from(document.querySelectorAll('.bottom-nav-bar a')).map(a => a.textContent.trim().replace(/\s+/g, ' '));

    return {
      hero: heroRect ? {
        top: Math.round(heroRect.top),
        bottom: Math.round(heroRect.bottom),
        height: Math.round(heroRect.height),
      } : null,
      firstBentoCard: cardRect ? {
        top: Math.round(cardRect.top),
        bottom: Math.round(cardRect.bottom),
        height: Math.round(cardRect.height),
        visibleAboveTheFold: cardRect.top < 768,
      } : null,
      dock: dockRect ? {
        width: Math.round(dockRect.width),
        height: Math.round(dockRect.height),
        bottom: Math.round(window.innerHeight - dockRect.bottom),
        left: Math.round(dockRect.left),
      } : null,
      dockItems,
      pageHeaderHidden: !pageHeader || window.getComputedStyle(pageHeader).display === 'none',
      smartUtilBarHidden: !smartUtilBar || window.getComputedStyle(smartUtilBar).display === 'none',
      footerHelpBtnHidden: !footerHelpBtn || window.getComputedStyle(footerHelpBtn).display === 'none',
      standaloneFabHidden: !standaloneFab || window.getComputedStyle(standaloneFab).display === 'none',
      dockAiBtnPresent: Boolean(dockAiBtn),
    };
  });

  console.log('  Layout Audit Above-the-fold (1366x768):', JSON.stringify(layoutAudit, null, 2));

  // 5. Capture Viewport Screenshot (Above the fold 1366x768) with "Bảng Điều khiển" hovered
  await saveShot(page, '30a_above_the_fold_1366x768_hover_dashboard.png');

  // 6. Click "Bác sĩ AI" in Dock and capture panel
  console.log('4. Testing "Bác sĩ AI" in Dock...');
  const dockAi = await page.$('.bottom-nav-bar #mx-crop-doctor-fab, .bottom-nav-bar .mx-bnav-ai');
  if (dockAi) {
    await dockAi.click();
    await new Promise((r) => setTimeout(r, 600));
    const panelOpen = await page.$eval('#mx-crop-doctor-panel', el => el.classList.contains('open'));
    console.log('  Crop Doctor panel open state:', panelOpen);
    await saveShot(page, '30b_dock_ai_doctor_panel_open.png');
  }

  console.log('\n--- ALL VERIFICATIONS COMPLETED SUCCESSFULLY ---');
} catch (err) {
  console.error('ERROR during verification:', err);
  process.exit(1);
} finally {
  await browser.close();
}
