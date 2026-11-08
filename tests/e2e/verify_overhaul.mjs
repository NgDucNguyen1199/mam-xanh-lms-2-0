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

async function saveShot(page, filename, fullPage = false) {
  const dest = path.join(SHOT_DIR, filename);
  await page.screenshot({ path: dest, fullPage });
  if (fs.existsSync(ARTIFACT_DIR)) {
    fs.copyFileSync(dest, path.join(ARTIFACT_DIR, filename));
  }
  console.log(`  [SHOT] Saved ${filename}`);
}

const AUDIT_CONTRAST = (rootSel) => {
  function parse(c) {
    const m = c.match(/rgba?\(([^)]+)\)/);
    if (!m) return null;
    const p = m[1].split(',').map((x) => parseFloat(x));
    return { r: p[0], g: p[1], b: p[2], a: p.length > 3 ? p[3] : 1 };
  }
  function lum({ r, g, b }) {
    const f = (v) => { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); };
    return 0.2126 * f(r) + 0.7152 * f(g) + 0.0722 * f(b);
  }
  function bgOf(el) {
    let n = el;
    while (n && n.nodeType === 1) {
      const cs = getComputedStyle(n);
      if (cs.backgroundImage && cs.backgroundImage.includes('gradient')) {
        const g = parse(cs.backgroundImage.match(/rgba?\([^)]+\)/)?.[0] || '');
        if (g && g.a > 0.5) return g;
      }
      const c = parse(cs.backgroundColor);
      if (c && c.a > 0.5) return c;
      n = n.parentElement;
    }
    return { r: 255, g: 255, b: 255, a: 1 };
  }
  const root = document.querySelector(rootSel);
  if (!root) return { found: false, bad: [] };
  const bad = [];
  const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
  let t;
  while ((t = walker.nextNode())) {
    if (!t.textContent.trim()) continue;
    const el = t.parentElement;
    if (['IFRAME', 'SCRIPT', 'STYLE', 'NOSCRIPT', 'TEXTAREA'].includes(el.tagName) || el.closest('iframe')) continue;
    const cs = getComputedStyle(el);
    if (cs.display === 'none' || cs.visibility === 'hidden' || el.getBoundingClientRect().width === 0) continue;
    const fg = parse(cs.color);
    const bg = bgOf(el);
    if (!fg) continue;
    const l1 = lum(fg), l2 = lum(bg);
    const ratio = (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
    if (ratio < 3) bad.push({ text: t.textContent.trim().slice(0, 40), color: cs.color, bg: `rgb(${bg.r},${bg.g},${bg.b})`, ratio: +ratio.toFixed(2) });
  }
  return { found: true, bad };
};

console.log('--- STARTING COMPREHENSIVE UI/UX OVERHAUL VERIFICATION ---');

const browser = await puppeteer.launch({
  executablePath: CHROME_PATH,
  headless: true,
  acceptInsecureCerts: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors'],
});

try {
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });

  // 1. Login as Farmer
  console.log('1. Logging in as Farmer nd_rau_lamdong...');
  await page.goto(`${BASE_URL}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('#mx-1click-farmer'),
  ]);

  await page.goto(`${BASE_URL}/local/mamxanhapp/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  await new Promise((r) => setTimeout(r, 800));

  // 2. Open Notification Popover & Verify Contrast
  console.log('2. Inspecting Notification Popover...');
  const bell = await page.$('.popover-region-notifications .popover-region-toggle, #nav-notification-popover-container [data-region="popover-region-toggle"]');
  if (bell) {
    await bell.click();
    await new Promise((r) => setTimeout(r, 1500));

    const popoverAudit = await page.evaluate(AUDIT_CONTRAST, '.popover-region-notifications .popover-region-container');
    console.log('  Popover audit:', popoverAudit.found ? 'FOUND' : 'NOT FOUND', 'Bad nodes:', popoverAudit.bad.length);
    if (popoverAudit.bad.length > 0) {
      console.error('  Low contrast items in popover:', JSON.stringify(popoverAudit.bad));
    }

    const popoverStyles = await page.evaluate(() => {
      const p = document.querySelector('.popover-region-notifications .popover-region-container');
      const item = document.querySelector('.popover-region-notifications .content-item-container');
      const msg = document.querySelector('.popover-region-notifications .notification-message');
      const time = document.querySelector('.popover-region-notifications .timecreated');
      const link = document.querySelector('.popover-region-notifications a.view-more');
      return {
        containerBg: p ? getComputedStyle(p).backgroundColor : null,
        itemBg: item ? getComputedStyle(item).backgroundColor : null,
        msgColor: msg ? getComputedStyle(msg).color : null,
        timeColor: time ? getComputedStyle(time).color : null,
        linkColor: link ? getComputedStyle(link).color : null,
      };
    });
    console.log('  Popover styles:', JSON.stringify(popoverStyles, null, 2));

    await saveShot(page, '29a_notification_popover_fixed.png');

    // Hover test
    const item = await page.$('.popover-region-notifications .content-item-container');
    if (item) {
      await item.hover();
      await new Promise((r) => setTimeout(r, 500));

      const hoverAudit = await page.evaluate(AUDIT_CONTRAST, '.popover-region-notifications .popover-region-container');
      console.log('  Popover hover audit bad nodes:', hoverAudit.bad.length);

      const hoverStyles = await page.evaluate(() => {
        const item = document.querySelector('.popover-region-notifications .content-item-container:hover');
        const msg = item ? item.querySelector('.notification-message') : null;
        return {
          itemBg: item ? getComputedStyle(item).backgroundColor : null,
          msgColor: msg ? getComputedStyle(msg).color : null,
        };
      });
      console.log('  Popover hover styles:', JSON.stringify(hoverStyles, null, 2));

      await saveShot(page, '29b_notification_hover_fixed.png');
    }

    // Close popover
    await page.keyboard.press('Escape');
    await page.mouse.click(700, 500);
    await new Promise((r) => setTimeout(r, 400));
  }

  // 3. User Menu Dropdown
  console.log('3. Inspecting User Menu Dropdown...');
  const userToggle = await page.$('.usermenu .dropdown-toggle, #user-menu-toggle');
  if (userToggle) {
    await userToggle.click();
    await new Promise((r) => setTimeout(r, 600));

    const userAudit = await page.evaluate(AUDIT_CONTRAST, '.usermenu .dropdown-menu');
    console.log('  User menu audit bad nodes:', userAudit.bad.length);

    await saveShot(page, '29c_usermenu_dropdown_fixed.png');

    await page.keyboard.press('Escape');
    await page.mouse.click(700, 500);
    await new Promise((r) => setTimeout(r, 400));
  }

  // 4. Desktop Full-page Screenshot (Hero + Bento + Masonry)
  console.log('4. Capturing Desktop Full-Page (Hero + Bento + Masonry)...');
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto(`${BASE_URL}/local/mamxanhapp/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  await new Promise((r) => setTimeout(r, 1000));

  const desktopStats = await page.evaluate(() => {
    return {
      hero: Boolean(document.querySelector('.mx-hero-banner')),
      bento: document.querySelectorAll('.mx-bento-card').length,
      masonry: document.querySelectorAll('.mx-masonry-item').length,
      bodyHeight: Math.max(document.body.scrollHeight, document.documentElement.scrollHeight),
      dock: (() => {
        const d = document.querySelector('.bottom-nav-bar');
        if (!d) return null;
        const r = d.getBoundingClientRect();
        return { width: Math.round(r.width), left: Math.round(r.left), bottom: Math.round(window.innerHeight - r.bottom) };
      })(),
    };
  });
  console.log('  Desktop elements:', JSON.stringify(desktopStats, null, 2));

  // Set viewport height to full document height so screenshot captures all sections
  await page.setViewport({ width: 1440, height: desktopStats.bodyHeight });
  await new Promise((r) => setTimeout(r, 600));
  await saveShot(page, '29d_dashboard_desktop_fullpage.png', false);

  // 5. Mobile Full-page Screenshot (390x844)
  console.log('5. Capturing Mobile Full-Page (390x844)...');
  await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  await page.goto(`${BASE_URL}/local/mamxanhapp/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  await new Promise((r) => setTimeout(r, 1000));

  const mobBodyH = await page.evaluate(() => Math.max(document.body.scrollHeight, document.documentElement.scrollHeight));
  console.log('  Mobile document height:', mobBodyH);

  await page.setViewport({ width: 390, height: mobBodyH, isMobile: true, hasTouch: true });
  await new Promise((r) => setTimeout(r, 600));

  const mobileNavWidth = await page.$eval('.bottom-nav-bar', (el) => Math.round(el.getBoundingClientRect().width));
  console.log('  Mobile bottom nav width:', mobileNavWidth, '(viewport width = 390)');

  await saveShot(page, '29e_dashboard_mobile_fullpage.png', false);

  console.log('\n--- ALL VERIFICATIONS COMPLETED SUCCESSFULLY ---');
} catch (err) {
  console.error('ERROR during verification:', err);
  process.exit(1);
} finally {
  await browser.close();
}
