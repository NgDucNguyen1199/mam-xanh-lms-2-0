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

// Find notification bell
const bell = await page.evaluate(() => {
    const toggles = Array.from(document.querySelectorAll('.popover-region-toggle, [data-region="popover-region-toggle"]'));
    return toggles.length > 0 ? true : false;
});
console.log('Bell exists:', bell);

// Click first popover toggle (notification bell)
await page.click('.popover-region-toggle');
await new Promise(r => setTimeout(r, 1200));

const popoverData = await page.evaluate(() => {
    const container = document.querySelector('.popover-region-container');
    const items = Array.from(document.querySelectorAll('.content-item-container'));
    return {
        containerVisible: container ? (window.getComputedStyle(container).display !== 'none') : false,
        containerBg: container ? window.getComputedStyle(container).backgroundColor : '',
        itemCount: items.length,
        items: items.slice(0, 3).map(it => {
            const msg = it.querySelector('.notification-message');
            const body = it.querySelector('.content-item-body');
            return {
                classes: it.className,
                itemBg: window.getComputedStyle(it).backgroundColor,
                bodyBg: body ? window.getComputedStyle(body).backgroundColor : '',
                msgBg: msg ? window.getComputedStyle(msg).backgroundColor : '',
                msgText: msg ? msg.textContent.trim().substring(0, 60) : ''
            };
        })
    };
});
console.log('Popover Data:', JSON.stringify(popoverData, null, 2));

await saveShot(page, '36_notification_popover_before.png');

await browser.close();
