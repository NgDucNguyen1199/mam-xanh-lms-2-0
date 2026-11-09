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

const elInfo = await page.evaluate(() => {
    const el = document.querySelector('td.hasevent a[data-action="view-event"]');
    if (!el) return { found: false };
    const rect = el.getBoundingClientRect();
    const computed = window.getComputedStyle(el);
    const parentComputed = window.getComputedStyle(el.parentElement);
    return {
        found: true,
        rect: { x: rect.x, y: rect.y, width: rect.width, height: rect.height },
        display: computed.display,
        visibility: computed.visibility,
        opacity: computed.opacity,
        parentDisplay: parentComputed.display
    };
});

console.log('EL INFO:', JSON.stringify(elInfo, null, 2));
await browser.close();
