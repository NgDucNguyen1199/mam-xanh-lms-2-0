import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const artifactsDir = 'C:\\Users\\pho21\\.gemini\\antigravity\\brain\\84025476-35b6-4655-9f1f-0de62ae3b517';
const docsDir = 'C:\\Users\\pho21\\Downloads\\Moodle 4.0\\docs\\screenshots';

const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true,
    args: ['--no-sandbox']
});

async function saveScreenshot(page, filename) {
    const artPath = path.join(artifactsDir, filename);
    const docPath = path.join(docsDir, filename);
    await page.screenshot({ path: artPath, fullPage: false });
    fs.copyFileSync(artPath, docPath);
    console.log(`Saved screenshot: ${filename}`);
}

const page = await browser.newPage();

// --- 1. Desktop 1366x768 - Farmer flow ---
await page.setViewport({ width: 1366, height: 768 });
await page.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('#mx-1click-farmer')]);

// Dashboard Desktop
await page.goto('http://localhost:8090/my/index.php', { waitUntil: 'networkidle2' });
await saveScreenshot(page, '39_ux_audit_desktop_dashboard.png');

// Calendar Day Navigation Desktop (Click or Navigate to Day View)
await page.goto('http://localhost:8090/calendar/view.php?view=day&time=1793552400', { waitUntil: 'networkidle2' });
await saveScreenshot(page, '47_ux_audit_calendar_day_navigation.png');

// Course Page Desktop (Section Kiểm tra & Đánh giá)
await page.goto('http://localhost:8090/course/view.php?id=2', { waitUntil: 'networkidle2' });
await saveScreenshot(page, '40_ux_audit_desktop_course_view.png');

// Inside Activity Desktop (mod_assign) - Test Wayfinding back button
await page.goto('http://localhost:8090/mod/assign/view.php?id=136', { waitUntil: 'networkidle2' });
await saveScreenshot(page, '41_ux_audit_desktop_activity_wayfinding.png');

// Inside Attendance Desktop (mod_attendance)
await page.goto('http://localhost:8090/mod/attendance/view.php?id=134', { waitUntil: 'networkidle2' });
await saveScreenshot(page, '42_ux_audit_desktop_attendance_wayfinding.png');

// --- 2. Mobile 375x812 - Farmer flow ---
await page.setViewport({ width: 375, height: 812, isMobile: true, hasTouch: true });

// Dashboard Mobile
await page.goto('http://localhost:8090/my/index.php', { waitUntil: 'networkidle2' });
await saveScreenshot(page, '43_ux_audit_mobile_dashboard.png');

// Course Page Mobile
await page.goto('http://localhost:8090/course/view.php?id=2', { waitUntil: 'networkidle2' });
await saveScreenshot(page, '44_ux_audit_mobile_course_view.png');

// Inside Activity Mobile
await page.goto('http://localhost:8090/mod/assign/view.php?id=136', { waitUntil: 'networkidle2' });
await saveScreenshot(page, '45_ux_audit_mobile_activity_wayfinding.png');

// --- 3. Teacher / CBKT Subnav Verification ---
const context2 = await browser.createBrowserContext();
const page2 = await context2.newPage();
await page2.setViewport({ width: 1366, height: 768, isMobile: false, hasTouch: false });
await page2.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([page2.waitForNavigation({ waitUntil: 'networkidle2' }), page2.click('#mx-1click-cbkt')]);

// CBKT on clone_course.php
await page2.goto('http://localhost:8090/local/mamxanhapp/clone_course.php', { waitUntil: 'networkidle2' });
await saveScreenshot(page2, '46_ux_audit_cbkt_cloner_subnav.png');

await browser.close();
console.log('All verification captures completed successfully!');
