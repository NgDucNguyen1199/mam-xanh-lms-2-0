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
await page.goto('http://localhost:8090/calendar/view.php?view=day&course=2&time=1794358800#event_7', { waitUntil: 'networkidle2' });

const dayView = await page.evaluate(() => {
    const events = [];
    document.querySelectorAll('.eventlist .event').forEach(ev => {
        const title = ev.querySelector('.name')?.textContent.trim();
        const actionBtn = ev.querySelector('.card-footer a, a.card-link, .action-link');
        const desc = ev.querySelector('.description')?.textContent.trim();
        events.push({
            title,
            actionUrl: actionBtn ? actionBtn.href : null,
            actionText: actionBtn ? actionBtn.textContent.trim() : null,
            desc: desc ? desc.substring(0, 100) : null
        });
    });
    return events;
});

console.log('DAY VIEW EVENTS:', JSON.stringify(dayView, null, 2));
await browser.close();
