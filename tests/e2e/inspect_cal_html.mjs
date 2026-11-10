import puppeteer from 'puppeteer-core';

const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true,
    args: ['--no-sandbox']
});
const page = await browser.newPage();
await page.setViewport({ width: 1440, height: 900 });
await page.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('#mx-1click-farmer')]);
await page.goto('http://localhost:8090/my/index.php', { waitUntil: 'networkidle2' });

const html = await page.evaluate(() => {
    const cal = document.querySelector('.block_calendar_month');
    const table = cal?.querySelector('table.calendarmonth');
    const th1 = table?.querySelector('th');
    const td1 = table?.querySelector('td.hasevents') || table?.querySelector('td');
    return {
        tableClass: table?.className,
        th1Outer: th1?.outerHTML,
        td1Outer: td1?.outerHTML
    };
});

console.log(JSON.stringify(html, null, 2));
await browser.close();
