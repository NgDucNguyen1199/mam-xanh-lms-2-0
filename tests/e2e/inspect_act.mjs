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

const compHtmls = await page.evaluate(() => {
    const list = [];
    document.querySelectorAll('[data-region="completion-info"], .completion-info, [data-action="toggle-manual-completion"]').forEach(el => {
        list.push({
            tag: el.tagName,
            className: el.className,
            html: el.outerHTML,
            text: el.textContent.trim().replace(/\s+/g, ' ')
        });
    });
    return list;
});
console.log('COMPLETION BADGES FOUND:', JSON.stringify(compHtmls, null, 2));

await browser.close();
