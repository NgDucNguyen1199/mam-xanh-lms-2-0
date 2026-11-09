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
await page.goto('http://localhost:8090/my/index.php', { waitUntil: 'networkidle2' });

// Click on day with event (e.g., Nov 2)
const clicked = await page.evaluate(() => {
    const day = document.querySelector('.block_calendar_month td.hasevent');
    if (!day) return { status: 'no day found' };
    const link = day.querySelector('a') || day;
    link.click();
    return { status: 'clicked', text: day.textContent.trim().substring(0, 100) };
});
console.log('CLICK RESULT:', clicked);

// Wait 1 second to see if modal/popover appeared
await new Promise(r => setTimeout(r, 1000));

const modalInfo = await page.evaluate(() => {
    const modal = document.querySelector('.modal.show, .popover.show');
    if (!modal) return { modalFound: false };
    return {
        modalFound: true,
        title: modal.querySelector('.modal-title, .popover-header')?.textContent.trim(),
        body: modal.querySelector('.modal-body, .popover-body')?.textContent.trim().substring(0, 200),
        actionLink: modal.querySelector('a.card-link, .modal-footer a, a[data-action="goto"]')?.href
    };
});
console.log('MODAL INFO:', modalInfo);

await browser.close();
