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

async function saveShot(page, filename, clip = null) {
    const dest = path.join(SHOT_DIR, filename);
    if (clip) {
        await page.screenshot({ path: dest, clip });
    } else {
        await page.screenshot({ path: dest, fullPage: false });
    }
    if (fs.existsSync(ARTIFACT_DIR)) {
        fs.copyFileSync(dest, path.join(ARTIFACT_DIR, filename));
    }
    console.log(`[SHOT] Saved ${filename}`);
}

const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1440,960']
});

const page = await browser.newPage();
await page.setViewport({ width: 1440, height: 960 });

// -------------------------------------------------------------
// 1. SCREENSHOT 36: Course Section "Kiểm tra & Đánh giá" (Image 1 style)
// -------------------------------------------------------------
console.log('Navigating to Course 2 (CHAMSOC01)...');
await page.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('#mx-1click-farmer')
]);

await page.goto('http://localhost:8090/course/view.php?id=2', { waitUntil: 'networkidle2' });
await new Promise(r => setTimeout(r, 1200));

// Scroll smoothly to section 5
const secBox = await page.evaluate(() => {
    const sec = document.querySelector('#section-5');
    if (!sec) return null;
    sec.scrollIntoView();
    // Offset slightly for sticky navbar
    window.scrollBy(0, -70);
    const rect = sec.getBoundingClientRect();
    return {
        x: Math.max(0, rect.left - 10),
        y: Math.max(0, rect.top + window.scrollY - 10),
        width: Math.min(1440, rect.width + 20),
        height: Math.min(850, rect.height + 20)
    };
});

await new Promise(r => setTimeout(r, 600));

if (secBox) {
    // Capture focused section view
    const clipRect = await page.evaluate(() => {
        const sec = document.querySelector('#section-5');
        const rect = sec.getBoundingClientRect();
        return {
            x: Math.round(rect.x),
            y: Math.round(rect.y),
            width: Math.round(rect.width),
            height: Math.round(Math.min(rect.height, 820))
        };
    });
    await saveShot(page, '36_course_attendance_and_assignments.png', clipRect);
} else {
    await saveShot(page, '36_course_attendance_and_assignments.png');
}

// -------------------------------------------------------------
// 2. SCREENSHOT 37: Attendance Sessions Detail & Statuses
// -------------------------------------------------------------
console.log('Navigating to Attendance Sessions view (as Teacher/CBKT)...');
await page.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('#mx-1click-cbkt')
]);

await page.goto('http://localhost:8090/mod/attendance/view.php?id=134&view=months', { waitUntil: 'networkidle2' });
await new Promise(r => setTimeout(r, 1200));
await saveShot(page, '37_attendance_sessions_detail.png');

// -------------------------------------------------------------
// 3. SCREENSHOT 38: Customizable Dashboard (/my/index.php) (Image 2 style)
// -------------------------------------------------------------
console.log('Navigating to Customizable Dashboard (/my/index.php?edit=1)...');
await page.goto('http://localhost:8090/my/index.php?edit=1', { waitUntil: 'networkidle2' });
await new Promise(r => setTimeout(r, 1500));
await saveShot(page, '38_customizable_dashboard_calendar_events.png');

await browser.close();
console.log('All 3 verification screenshots captured successfully.');
