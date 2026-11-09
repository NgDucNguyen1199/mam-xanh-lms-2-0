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

const calInfo = await page.evaluate(() => {
    const title = document.querySelector('.block_calendar_month .current')?.textContent.trim() ||
                  document.querySelector('.block_calendar_month caption')?.textContent.trim() ||
                  document.querySelector('.block_calendar_month h3')?.textContent.trim();
    const cells = [];
    document.querySelectorAll('.block_calendar_month td').forEach(td => {
        cells.push({
            class: td.className,
            text: td.textContent.trim(),
            hasLink: !!td.querySelector('a'),
            linkHref: td.querySelector('a')?.href
        });
    });
    return { title, cellsCount: cells.length, sampleCells: cells.filter(c => c.text !== '') };
});

console.log('CAL INFO:', JSON.stringify(calInfo, null, 2));
await browser.close();
