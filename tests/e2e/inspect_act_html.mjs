import puppeteer from 'puppeteer-core';

const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true,
    args: ['--no-sandbox']
});
const page = await browser.newPage();
await page.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('#mx-1click-farmer')]);
await page.goto('http://localhost:8090/course/view.php?id=2', { waitUntil: 'networkidle2' });

const actHtml = await page.evaluate(() => {
    let html = null;
    document.querySelectorAll('.activity-item').forEach(act => {
        if (act.textContent.includes('Thảo luận tại lớp buổi 1')) {
            html = act.outerHTML;
        }
    });
    return html;
});

console.log('ACT HTML:', actHtml);
await browser.close();
