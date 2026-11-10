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

const res = await page.evaluate(() => {
    const aside = document.querySelector('aside.block-region');
    const sec = document.querySelector('section.block-region');
    const parent = sec?.parentElement;
    return {
        secParentTag: parent?.tagName,
        secParentClass: parent?.className,
        secParentStyle: parent ? window.getComputedStyle(parent).display : null,
        secRegion: sec?.getAttribute('data-blockregion'),
        asideRegion: aside?.getAttribute('data-blockregion'),
        secWidth: sec?.offsetWidth,
        asideWidth: aside?.offsetWidth,
        parentWidth: parent?.offsetWidth
    };
});

console.log('BLOCK REGION PARENT DOM:', JSON.stringify(res, null, 2));
await browser.close();
