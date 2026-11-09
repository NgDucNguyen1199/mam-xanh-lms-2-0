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

const drawerState = await page.evaluate(() => {
    const drawer = document.getElementById('theme_boost-drawers-blocks');
    const toggleBtn = document.querySelector('[data-toggler="drawers"][data-target="theme_boost-drawers-blocks"]');
    return {
        drawerExists: !!drawer,
        drawerClasses: drawer ? drawer.className : null,
        drawerHidden: drawer ? drawer.getAttribute('aria-hidden') : null,
        drawerOffsetWidth: drawer ? drawer.offsetWidth : null,
        toggleBtn: toggleBtn ? { text: toggleBtn.textContent.trim(), classes: toggleBtn.className } : null
    };
});

console.log('DRAWER STATE:', JSON.stringify(drawerState, null, 2));
await browser.close();
