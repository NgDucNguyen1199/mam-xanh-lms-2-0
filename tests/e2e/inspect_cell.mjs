import puppeteer from 'puppeteer-core';

const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true,
    args: ['--no-sandbox']
});
const page = await browser.newPage();
await page.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('#mx-1click-farmer')]);
await page.goto('http://localhost:8090/my/index.php', { waitUntil: 'networkidle2' });

const cellDetail = await page.evaluate(() => {
    const td = document.querySelector('.block_calendar_month td.hasevent');
    if (!td) return null;
    return {
        outerHTML: td.outerHTML,
        dataset: Object.assign({}, td.dataset)
    };
});

console.log('CELL DETAIL:', JSON.stringify(cellDetail, null, 2));
await browser.close();
