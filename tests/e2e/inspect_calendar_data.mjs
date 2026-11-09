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

const calData = await page.evaluate(() => {
    // Upcoming events
    const upcoming = [];
    document.querySelectorAll('.block_calendar_upcoming .event').forEach(ev => {
        const titleLink = ev.querySelector('.name a') || ev.querySelector('a');
        const dateEl = ev.querySelector('.date');
        upcoming.push({
            title: titleLink ? titleLink.textContent.trim() : null,
            href: titleLink ? titleLink.href : null,
            date: dateEl ? dateEl.textContent.trim() : null
        });
    });

    // Calendar month days with events
    const eventDays = [];
    document.querySelectorAll('.block_calendar_month td.hasevents').forEach(td => {
        const dayNumber = td.querySelector('.day-number')?.textContent.trim();
        const links = [];
        td.querySelectorAll('a').forEach(a => {
            links.push({
                text: a.textContent.trim(),
                href: a.href,
                title: a.getAttribute('title')
            });
        });
        eventDays.push({ dayNumber, links });
    });

    return { upcoming, eventDays };
});

console.log('CALENDAR DATA:', JSON.stringify(calData, null, 2));
await browser.close();
