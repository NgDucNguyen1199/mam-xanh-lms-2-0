import puppeteer from 'puppeteer-core';

const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true,
    args: ['--no-sandbox']
});
const page = await browser.newPage();
await page.setViewport({ width: 1366, height: 768 });
await page.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('#mx-1click-farmer')]);
await page.goto('http://localhost:8090/course/view.php?id=2', { waitUntil: 'networkidle2' });

const secEval = await page.evaluate(() => {
    // Find section with "Kiểm tra & Đánh giá"
    let targetSec = null;
    document.querySelectorAll('.course-section, li.section').forEach(sec => {
        if (sec.textContent.includes('Kiểm tra & Đánh giá')) {
            targetSec = sec;
        }
    });
    if (!targetSec) return { error: 'Not found' };

    const activities = [];
    targetSec.querySelectorAll('.activity-item').forEach(act => {
        const name = act.querySelector('.activityname')?.textContent.trim().replace(/\s+/g, ' ');
        const dates = act.querySelector('.activity-dates')?.textContent.trim().replace(/\s+/g, ' ');
        const desc = act.querySelector('.contentafterlink')?.textContent.trim().replace(/\s+/g, ' ');
        const comp = act.querySelector('[data-region="completion-info"]')?.textContent.trim().replace(/\s+/g, ' ');
        activities.push({ name, dates, desc: desc ? desc.substring(0, 100) : null, comp });
    });
    return { title: targetSec.querySelector('.sectionname')?.textContent.trim(), activities };
});

console.log('EVALUATION SECTION:', JSON.stringify(secEval, null, 2));
await browser.close();
