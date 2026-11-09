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

const details = await page.evaluate(() => {
    const items = [];
    document.querySelectorAll('.activity-item').forEach((item, idx) => {
        if (idx > 7) return; // just inspect first 8
        const nameEl = item.querySelector('.activityname');
        const iconEl = item.querySelector('.activityicon');
        const compEl = item.querySelector('[data-region="completion-info"]');
        const datesEl = item.querySelector('.activity-dates');
        const descEl = item.querySelector('.contentafterlink');
        items.push({
            name: nameEl ? nameEl.textContent.trim().replace(/\s+/g, ' ') : null,
            icon: iconEl ? {
                src: iconEl.src,
                width: iconEl.clientWidth,
                height: iconEl.clientHeight,
                classes: iconEl.className
            } : null,
            compBadge: compEl ? {
                text: compEl.textContent.trim().replace(/\s+/g, ' '),
                classes: compEl.firstElementChild ? compEl.firstElementChild.className : '',
                html: compEl.innerHTML.trim().substring(0, 150)
            } : null,
            hasDates: !!datesEl,
            hasDesc: !!descEl
        });
    });
    return items;
});

console.log('COURSE ACTIVITY ITEMS:', JSON.stringify(details, null, 2));
await browser.close();
