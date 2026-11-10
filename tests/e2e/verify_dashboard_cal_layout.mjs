import puppeteer from 'puppeteer-core';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, '../../');

const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true,
    args: ['--no-sandbox']
});

const page = await browser.newPage();
await page.setViewport({ width: 1440, height: 900 });

// 1. Login as Farmer / Learner
await page.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('#mx-1click-farmer')]);

// 2. Go to Dashboard
await page.goto('http://localhost:8090/my/index.php', { waitUntil: 'networkidle2' });

// 3. Inspect Real Computed Values (NO INJECTED CSS)
const metrics = await page.evaluate(() => {
    const drawer = document.getElementById('theme_boost-drawers-blocks');
    const pageEl = document.getElementById('page');
    const content = document.querySelector('section[data-blockregion="content"]');
    const aside = document.querySelector('aside[data-blockregion="side-pre"]');
    const cal = document.querySelector('.block_calendar_month');
    const calTable = cal?.querySelector('table.calendarmonth');
    const ths = Array.from(calTable?.querySelectorAll('th') || []).map(th => ({
        text: th.innerText.trim(),
        width: th.offsetWidth
    }));
    const dayCircles = Array.from(calTable?.querySelectorAll('.day-number-circle') || []).slice(0, 7).map(c => ({
        text: c.innerText.trim(),
        width: c.offsetWidth,
        height: c.offsetHeight
    }));

    return {
        drawerWidth: drawer?.offsetWidth,
        pageMarginRight: pageEl ? window.getComputedStyle(pageEl).marginRight : null,
        pageWidth: pageEl?.offsetWidth,
        contentWidth: content?.offsetWidth,
        asideWidth: aside?.offsetWidth,
        calWidth: cal?.offsetWidth,
        calTableWidth: calTable?.offsetWidth,
        thSample: ths,
        dayCirclesSample: dayCircles
    };
});

console.log('REAL METRICS:', JSON.stringify(metrics, null, 2));

// 4. Capture Desktop Full Screenshot
const desktopPath = path.join(rootDir, 'docs/screenshots/60_fixed_desktop_dashboard_cal.png');
await page.screenshot({ path: desktopPath, fullPage: false });

// 5. Test Mobile 375x812 Viewport
await page.setViewport({ width: 375, height: 812 });
await page.goto('http://localhost:8090/my/index.php', { waitUntil: 'networkidle2' });

const mobileMetrics = await page.evaluate(() => {
    const content = document.querySelector('section[data-blockregion="content"]');
    const drawer = document.getElementById('theme_boost-drawers-blocks');
    const aside = document.querySelector('aside[data-blockregion="side-pre"]');
    const cal = document.querySelector('.block_calendar_month');
    return {
        docWidth: document.documentElement.clientWidth,
        scrollWidth: document.documentElement.scrollWidth,
        contentWidth: content?.offsetWidth,
        drawerWidth: drawer?.offsetWidth,
        asideWidth: aside?.offsetWidth,
        calWidth: cal?.offsetWidth
    };
});

console.log('MOBILE METRICS:', JSON.stringify(mobileMetrics, null, 2));

const mobilePath = path.join(rootDir, 'docs/screenshots/61_fixed_mobile_dashboard.png');
await page.screenshot({ path: mobilePath, fullPage: false });

await browser.close();
console.log('Verification completed successfully!');
