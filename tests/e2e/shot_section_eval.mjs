import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: true,
    args: ['--no-sandbox', '--window-size=1366,768']
});

const page = await browser.newPage();
await page.setViewport({ width: 1366, height: 768 });

await page.goto('http://localhost:8090/login/index.php?lang=vi', { waitUntil: 'networkidle2' });
await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('#mx-1click-farmer')
]);

await page.goto('http://localhost:8090/course/view.php?id=2', { waitUntil: 'networkidle2' });
await new Promise(r => setTimeout(r, 1200));

// Scroll into view of section 5
await page.evaluate(() => {
    const sec = document.querySelector('#section-5');
    if (sec) sec.scrollIntoView();
});

await new Promise(r => setTimeout(r, 600));

// Screenshot section 5
const secEl = await page.$('#section-5');
if (secEl) {
    await secEl.screenshot({ path: 'test_sec5.png' });
    console.log('Saved test_sec5.png');
}

await browser.close();
