import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const PROJECT_ROOT = path.resolve(__dirname, '../..');
const SHOT_DIR = path.join(PROJECT_ROOT, 'docs', 'screenshots');
const ARTIFACT_DIR = 'C:\\Users\\pho21\\.gemini\\antigravity\\brain\\84025476-35b6-4655-9f1f-0de62ae3b517';
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

const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors', '--window-size=1366,768'],
});

const page = await browser.newPage();
await page.setViewport({ width: 1366, height: 768 });

await page.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('#mx-1click-farmer'),
]);

await page.goto('http://localhost:8090/local/mamxanhapp/index.php?lang=vi', { waitUntil: 'networkidle2' });
await new Promise(r => setTimeout(r, 800));

// Click notification bell
await page.click('.popover-region-toggle');
await new Promise(r => setTimeout(r, 1200));

// Inspect notifications and inject unread class to simulate unread state if none unread
const popoverEval = await page.evaluate(async () => {
    const items = Array.from(document.querySelectorAll('.content-item-container'));
    // Make first two items explicitly unread to test exact color synchronization
    if (items.length > 0) items[0].classList.add('unread');
    if (items.length > 1) items[1].classList.add('unread');

    // Wait for CSS transition (0.12s) to complete
    await new Promise(r => setTimeout(r, 250));

    return items.slice(0, 3).map(it => {
        const msg = it.querySelector('.notification-message');
        const itBg = window.getComputedStyle(it).backgroundColor;
        const msgBg = msg ? window.getComputedStyle(msg).backgroundColor : 'none';
        return {
            classes: it.className,
            containerBg: itBg,
            messageBg: msgBg,
            isUnified: msg ? (msgBg === 'rgba(0, 0, 0, 0)' || msgBg === itBg) : true
        };
    });
});

console.log('Evaluated Notification Styles:', JSON.stringify(popoverEval, null, 2));

// Focus on the notification popover
const popoverEl = await page.$('.popover-region-container');
if (popoverEl) {
    const shotPath = path.join(SHOT_DIR, '37_notification_popover_unified.png');
    await popoverEl.screenshot({ path: shotPath });
    if (fs.existsSync(ARTIFACT_DIR)) {
        fs.copyFileSync(shotPath, path.join(ARTIFACT_DIR, '37_notification_popover_unified.png'));
    }
    console.log('  [SHOT] Saved focused popover screenshot: 37_notification_popover_unified.png');
}

await saveShot(page, '38_notification_popover_fullpage.png');

await browser.close();
