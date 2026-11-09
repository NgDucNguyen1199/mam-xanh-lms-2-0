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

// Click on the first event link inside td.hasevent
const navPromise = page.waitForNavigation({ waitUntil: 'networkidle2' });
await page.click('td.hasevent a[data-action="view-event"]');
try {
    await navPromise;
    console.log('NAVIGATED TO:', page.url());
} catch (e) {
    // Might have opened a modal instead
    const modal = await page.evaluate(() => {
        const m = document.querySelector('.modal.show');
        return m ? m.textContent.trim().substring(0, 150) : 'no modal';
    });
    console.log('DID NOT NAVIGATE, MODAL STATE:', modal, 'URL:', page.url());
}

await browser.close();
