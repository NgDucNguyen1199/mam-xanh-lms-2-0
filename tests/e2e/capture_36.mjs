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

await page.goto('http://localhost:8090/course/view.php?id=2', { waitUntil: 'networkidle2' });
await new Promise(r => setTimeout(r, 1200));

// Temporarily hide fixed navbar so it does not occlude the section header
await page.evaluate(() => {
    const nav = document.querySelector('.navbar.fixed-top');
    if (nav) nav.style.display = 'none';
    const subnav = document.querySelector('.mx-subnav');
    if (subnav) subnav.style.display = 'none';
    const sec = document.querySelector('#section-5');
    if (sec) {
        sec.scrollIntoView({ behavior: 'instant', block: 'start' });
    }
});
await new Promise(r => setTimeout(r, 600));

const dest = path.join(SHOT_DIR, '36_course_attendance_and_assignments.png');

// Screenshot section element
const secEl = await page.$('#section-5');
if (secEl) {
    await secEl.screenshot({ path: dest });
    console.log('Saved 36_course_attendance_and_assignments.png (element)');
} else {
    await page.screenshot({ path: dest, fullPage: false });
    console.log('Saved 36_course_attendance_and_assignments.png (viewport)');
}

if (fs.existsSync(ARTIFACT_DIR)) {
    fs.copyFileSync(dest, path.join(ARTIFACT_DIR, '36_course_attendance_and_assignments.png'));
}

await browser.close();
