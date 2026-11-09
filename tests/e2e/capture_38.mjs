import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const PROJECT_ROOT = path.resolve(__dirname, '../..');
const SHOT_DIR = path.join(PROJECT_ROOT, 'docs', 'screenshots');
const ARTIFACT_DIR = 'C:\\Users\\pho21\\.gemini\\antigravity\\brain\\84025476-35b6-4655-9f1f-0de62ae3b517';
const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: true,
    args: ['--no-sandbox', '--window-size=1440,960']
});

const page = await browser.newPage();
await page.setViewport({ width: 1440, height: 960 });

await page.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('#mx-1click-farmer')
]);

await page.goto('http://localhost:8090/my/index.php?edit=1', { waitUntil: 'networkidle2' });
await new Promise(r => setTimeout(r, 1500));

const dest = path.join(SHOT_DIR, '38_customizable_dashboard_calendar_events.png');
await page.screenshot({ path: dest, fullPage: false });

if (fs.existsSync(ARTIFACT_DIR)) {
    fs.copyFileSync(dest, path.join(ARTIFACT_DIR, '38_customizable_dashboard_calendar_events.png'));
}

console.log('Saved 38_customizable_dashboard_calendar_events.png');
await browser.close();
