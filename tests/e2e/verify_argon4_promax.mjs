import puppeteer from 'puppeteer-core';
import path from 'path';
import fs from 'fs';

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE_URL = 'http://localhost:8090';
const ARTIFACT_DIR = 'C:\\Users\\pho21\\.gemini\\antigravity\\brain\\84025476-35b6-4655-9f1f-0de62ae3b517';
const SCREENSHOT_DIR = path.resolve('docs/screenshots');

fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });

async function saveShot(page, name, fullPage = false) {
    const p1 = path.join(SCREENSHOT_DIR, name);
    const p2 = path.join(ARTIFACT_DIR, name);
    await page.screenshot({ path: p1, fullPage });
    fs.copyFileSync(p1, p2);
    console.log(`[SCREENSHOT] Saved ${name}`);
}

async function run() {
    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
    });

    try {
        const page = await browser.newPage();

        // 1. Login as Farmer (nd_hoa_dalat / Nông hộ Hoa Đà Lạt, matching NH avatar in user's screenshot!)
        await page.setViewport({ width: 1366, height: 768 });
        await page.goto(`${BASE_URL}/login/index.php`, { waitUntil: 'networkidle2' });
        await page.evaluate(() => {
            document.querySelector('#username').value = '';
            document.querySelector('#password').value = '';
        });
        await page.type('#username', 'nd_hoa_dalat');
        await page.type('#password', 'MX!Rau2026');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2' }),
            page.click('#loginbtn')
        ]);

        // 2. Navigate to /my/index.php at 1366x768
        await page.goto(`${BASE_URL}/my/index.php?lang=vi`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 800));

        const metrics1366 = await page.evaluate(() => {
            const rect = (sel) => {
                const el = document.querySelector(sel);
                if (!el) return null;
                const r = el.getBoundingClientRect();
                return { x: Math.round(r.x), y: Math.round(r.y), width: Math.round(r.width), height: Math.round(r.height) };
            };
            const curEl = document.querySelector('.block_calendar_month .calendar-controls .current');
            const ths = Array.from(document.querySelectorAll('.block_calendar_month table.calendarmonth thead th')).map(th => {
                const r = th.getBoundingClientRect();
                return { text: th.innerText.trim(), x: Math.round(r.x), y: Math.round(r.y), width: Math.round(r.width) };
            });
            const eventDays = Array.from(document.querySelectorAll('.block_calendar_month table.calendarmonth td.hasevent .day-number-circle, .block_calendar_month table.calendarmonth td.hasevents .day-number-circle')).map(el => {
                const cs = window.getComputedStyle(el);
                const r = el.getBoundingClientRect();
                return { day: el.innerText.trim(), width: Math.round(r.width), height: Math.round(r.height), bg: cs.backgroundColor, color: cs.color };
            });
            return {
                viewport: '1366x768',
                regionMainBox: rect('#region-main-box'),
                regionMain: rect('#region-main'),
                sidePre: rect('#region-bs-side-pre'),
                calendarBlock: rect('.block_calendar_month'),
                upcomingBlock: rect('.block_calendar_upcoming'),
                calendarTable: rect('.block_calendar_month table.calendarmonth'),
                monthTitle: curEl ? {
                    text: curEl.innerText.trim(),
                    ...rect('.block_calendar_month .calendar-controls .current'),
                    whiteSpace: window.getComputedStyle(curEl).whiteSpace
                } : null,
                weekdayColumns: ths,
                eventDaysCount: eventDays.length,
                sampleEventDays: eventDays.slice(0, 4),
                horizontalOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth
            };
        });

        console.log('=== METRICS AT 1366x768 (/my/index.php) ===');
        console.log(JSON.stringify(metrics1366, null, 2));
        await saveShot(page, '70_argon4_dashboard_1366x768.png', false);
        await saveShot(page, '71_argon4_dashboard_1366x768_full.png', true);

        // 3. Test at 1440x900
        await page.setViewport({ width: 1440, height: 900 });
        await new Promise(r => setTimeout(r, 500));
        const metrics1440 = await page.evaluate(() => {
            const rect = (sel) => {
                const el = document.querySelector(sel);
                if (!el) return null;
                const r = el.getBoundingClientRect();
                return { x: Math.round(r.x), y: Math.round(r.y), width: Math.round(r.width), height: Math.round(r.height) };
            };
            return {
                viewport: '1440x900',
                regionMain: rect('#region-main'),
                sidePre: rect('#region-bs-side-pre'),
                calendarBlock: rect('.block_calendar_month'),
                calendarTable: rect('.block_calendar_month table.calendarmonth'),
                monthTitle: rect('.block_calendar_month .calendar-controls .current')
            };
        });
        console.log('=== METRICS AT 1440x900 (/my/index.php) ===');
        console.log(JSON.stringify(metrics1440, null, 2));
        await saveShot(page, '72_argon4_dashboard_1440x900.png', false);

        // 4. Test Mobile 375x812 on /my/index.php
        await page.setViewport({ width: 375, height: 812 });
        await new Promise(r => setTimeout(r, 500));
        const metricsMobile = await page.evaluate(() => {
            const rect = (sel) => {
                const el = document.querySelector(sel);
                if (!el) return null;
                const r = el.getBoundingClientRect();
                return { x: Math.round(r.x), y: Math.round(r.y), width: Math.round(r.width), height: Math.round(r.height) };
            };
            return {
                viewport: '375x812',
                regionMain: rect('#region-main'),
                sidePre: rect('#region-bs-side-pre'),
                calendarBlock: rect('.block_calendar_month'),
                horizontalOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth
            };
        });
        console.log('=== METRICS AT 375x812 (/my/index.php) ===');
        console.log(JSON.stringify(metricsMobile, null, 2));
        await saveShot(page, '73_argon4_dashboard_mobile_375x812.png', true);

        // 5. Test Video Page (/mod/page/view.php?id=160) at 1366x768
        await page.setViewport({ width: 1366, height: 768 });
        await page.goto(`${BASE_URL}/mod/page/view.php?id=160`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 800));
        const videoMetrics = await page.evaluate(() => {
            const v = document.querySelector('.mediaplugin_videojs, .video-js, video');
            if (!v) return null;
            const r = v.getBoundingClientRect();
            return { width: Math.round(r.width), height: Math.round(r.height) };
        });
        console.log('=== VIDEO METRICS (/mod/page/view.php?id=160) ===');
        console.log(JSON.stringify(videoMetrics, null, 2));
        await saveShot(page, '74_argon4_video_player_16_9.png', false);

        // 6. Test Course View (/course/view.php?id=2 and /course/view.php?id=19) at 1366x768
        await page.goto(`${BASE_URL}/course/view.php?id=2`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 600));
        await saveShot(page, '75_argon4_course2_view.png', false);
        await page.evaluate(() => {
            const evalEl = Array.from(document.querySelectorAll('h3')).find(el => el.innerText.includes('Kiểm tra & Đánh giá'));
            if (evalEl) evalEl.scrollIntoView({ behavior: 'instant', block: 'start' });
        });
        await new Promise(r => setTimeout(r, 300));
        await saveShot(page, '79_argon4_course2_eval_section.png', false);

        await page.goto(`${BASE_URL}/course/view.php?id=19`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 600));
        await saveShot(page, '76_argon4_course19_dautay_view.png', false);

        // 7. Login as CBKT (cbkt_pilot) to verify Course View with Editing/Standard Sections, Manage & Cloner
        await page.goto(`${BASE_URL}/login/logout.php`, { waitUntil: 'networkidle2' });
        const logoutBtn = await page.$('form[action*="logout.php"] button[type="submit"]');
        if (logoutBtn) {
            await Promise.all([
                page.waitForNavigation({ waitUntil: 'networkidle2' }),
                logoutBtn.click()
            ]);
        }
        await page.goto(`${BASE_URL}/login/index.php`, { waitUntil: 'networkidle2' });
        await page.evaluate(() => {
            document.querySelector('#username').value = '';
            document.querySelector('#password').value = '';
        });
        await page.type('#username', 'cbkt_pilot');
        await page.type('#password', 'MX!Canbo2026');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2' }),
            page.click('#loginbtn')
        ]);

        await page.goto(`${BASE_URL}/local/mamxanhapp/manage.php`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 500));
        await saveShot(page, '77_argon4_cbkt_manage.png', false);

        await page.goto(`${BASE_URL}/local/mamxanhapp/clone_course.php`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 500));
        await saveShot(page, '78_argon4_cbkt_cloner.png', false);

    } finally {
        await browser.close();
    }
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
