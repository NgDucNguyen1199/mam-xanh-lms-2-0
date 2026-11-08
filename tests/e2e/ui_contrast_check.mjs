import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SHOT_DIR = path.resolve(__dirname, '../../docs/screenshots');
const ARTIFACT_DIR = 'C:\\Users\\pho21\\.gemini\\antigravity\\brain\\84025476-35b6-4655-9f1f-0de62ae3b517';
const BASE = process.env.MOODLE_URL || 'http://localhost:8090';
const PREFIX = process.env.SHOT_PREFIX || '28';
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
fs.mkdirSync(SHOT_DIR, { recursive: true });

let failed = 0;
function check(name, ok, detail = '') {
  console.log(`  [${ok ? 'PASS' : 'FAIL'}] ${name}${detail ? ' -> ' + detail : ''}`);
  if (!ok) failed++;
}
async function shot(page, name) {
  const f = `${PREFIX}${name}.png`;
  const dest = path.join(SHOT_DIR, f);
  await page.screenshot({ path: dest });
  if (fs.existsSync(ARTIFACT_DIR)) fs.copyFileSync(dest, path.join(ARTIFACT_DIR, f));
  console.log('  [SHOT]', f);
}

// Luminance helpers computed in-page: report every visible text node whose colour is too close to its effective background.
const AUDIT_FN = (rootSel) => {
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

const browser = await puppeteer.launch({
  executablePath: CHROME, headless: true, acceptInsecureCerts: true,
  args: ['--no-sandbox', '--ignore-certificate-errors'],
});
try {
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto(`${BASE}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('#mx-1click-farmer')]);
  await page.goto(`${BASE}/local/mamxanhapp/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  await new Promise((r) => setTimeout(r, 800));
  await shot(page, 'a_dashboard_desktop_full');

  // --- Notification bell ---
  const bell = await page.$('.popover-region-notifications .popover-region-toggle, #nav-notification-popover-container [data-region="popover-region-toggle"], .popover-region-notifications');
  check('Notification bell exists', !!bell);
  if (bell) {
    await bell.click();
    await new Promise((r) => setTimeout(r, 1800));
    const audit = await page.evaluate(AUDIT_FN, '.popover-region-notifications .popover-region-container');
    check('Notification popover opened', audit.found);
    check('Notification popover has no low-contrast text', audit.bad.length === 0, JSON.stringify(audit.bad.slice(0, 5)));
    await shot(page, 'b_notification_popover');
    // hover state
    const item = await page.$('.popover-region-notifications .content-item-container');
    if (item) {
      await item.hover();
      await new Promise((r) => setTimeout(r, 400));
      const audit2 = await page.evaluate(AUDIT_FN, '.popover-region-notifications .popover-region-container');
      check('Notification hover state readable', audit2.bad.length === 0, JSON.stringify(audit2.bad.slice(0, 5)));
      await shot(page, 'c_notification_hover');
    }
    await page.keyboard.press('Escape');
    await page.mouse.click(700, 500);
  }

  // --- User menu ---
  await page.goto(`${BASE}/local/mamxanhapp/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  const um = await page.$('.usermenu .dropdown-toggle, #user-menu-toggle, .usermenu a[data-toggle="dropdown"]');
  check('User menu toggle exists', !!um);
  if (um) {
    await um.click();
    await new Promise((r) => setTimeout(r, 700));
    const audit = await page.evaluate(AUDIT_FN, '.usermenu .dropdown-menu');
    check('User menu has no low-contrast text', audit.found && audit.bad.length === 0, JSON.stringify(audit.bad.slice(0, 5)));
    await shot(page, 'd_usermenu_dropdown');
  }

  // --- Message drawer ---
  await page.goto(`${BASE}/local/mamxanhapp/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  const msg = await page.$('.popover-region-messages a[id^="message-drawer-toggle"], a[id^="message-drawer-toggle"]');
  check('Message drawer toggle exists', !!msg);
  if (msg) {
    await msg.click();
    await new Promise((r) => setTimeout(r, 1800));
    const a = await page.evaluate(AUDIT_FN, '#theme_boost-drawers-communication, [data-region="message-drawer"]');
    check('Message drawer has no low-contrast text', a.found && a.bad.length === 0, JSON.stringify(a.bad.slice(0, 5)));
    await shot(page, 'd2_message_drawer');
  }

  // --- Dock geometry ---
  await page.goto(`${BASE}/local/mamxanhapp/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  const dock = await page.$eval('.bottom-nav-bar', (el) => {
    const r = el.getBoundingClientRect();
    return { w: r.width, left: r.left, bottom: window.innerHeight - r.bottom, radius: getComputedStyle(el).borderTopLeftRadius };
  });
  check('Desktop dock is floating (<=560px wide, centred)', dock.w <= 561 && Math.abs(dock.left + dock.w / 2 - 720) < 4, JSON.stringify(dock));

  // --- Other pages audit ---
  const pages = [
    ['/local/mamxanhapp/calculator.php?lang=vi', 'e_calculator', '#region-main'],
    ['/course/view.php?id=2&lang=vi', 'f_course_chamsoc', '#region-main'],
    ['/mod/resource/view.php?id=37&lang=vi', 'g_pdf_lesson', '#region-main'],
    ['/course/view.php?id=8&lang=vi', 'h_master_template', '#region-main'],
  ];
  for (const [u, n, sel] of pages) {
    await page.goto(`${BASE}${u}`, { waitUntil: 'networkidle2' });
    await new Promise((r) => setTimeout(r, 600));
    const a = await page.evaluate(AUDIT_FN, sel);
    check(`Contrast audit ${u}`, a.found && a.bad.length === 0, JSON.stringify(a.bad.slice(0, 6)));
    await shot(page, n);
  }

  // --- Mobile ---
  await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  await page.goto(`${BASE}/local/mamxanhapp/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  const mdock = await page.$eval('.bottom-nav-bar', (el) => el.getBoundingClientRect().width);
  check('Mobile bottom nav spans full width', mdock >= 388, `${mdock}px`);
  await shot(page, 'i_dashboard_mobile');

  // --- CBKT manage.php ---
  const ctx = await browser.createBrowserContext();
  const cp = await ctx.newPage();
  await cp.setViewport({ width: 1440, height: 900 });
  await cp.goto(`${BASE}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
  await Promise.all([cp.waitForNavigation({ waitUntil: 'networkidle2' }), cp.click('#mx-1click-cbkt')]);
  await cp.goto(`${BASE}/local/mamxanhapp/manage.php?lang=vi`, { waitUntil: 'networkidle2' });
  const ma = await cp.evaluate(AUDIT_FN, '#region-main');
  check('Contrast audit manage.php', ma.found && ma.bad.length === 0, JSON.stringify(ma.bad.slice(0, 6)));
  await shot(cp, 'j_manage_desktop');
} catch (e) {
  console.error('ERROR', e);
  failed++;
} finally {
  await browser.close();
}
console.log(failed ? `\nRESULT: ${failed} FAILED` : '\nRESULT: ALL PASS');
process.exit(failed ? 1 : 0);
