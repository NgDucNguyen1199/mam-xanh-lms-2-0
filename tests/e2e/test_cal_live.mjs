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

// Inject test CSS
await page.addStyleTag({
    content: `
        #theme_boost-drawers-blocks {
            width: 340px !important;
        }
        #page.drawers.show-drawer-right {
            margin-right: 340px !important;
        }
        #page-my-index section[data-blockregion="content"] {
            flex: 1 1 100% !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        #page-my-index aside[data-blockregion="side-pre"],
        #page-my-index #block-region-side-pre {
            flex: 1 1 100% !important;
            max-width: 100% !important;
            width: 100% !important;
            min-width: 100% !important;
        }
        #page-my-index .block {
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
        }
        #page-my-index .block_calendar_month .maincalendar {
            width: 100% !important;
        }
        #page-my-index .block_calendar_month table.calendarmonth {
            width: 100% !important;
            table-layout: fixed !important;
        }
    `
});

const measurements = await page.evaluate(() => {
    const aside = document.querySelector('aside.block-region');
    const cal = document.querySelector('.block_calendar_month');
    const calTable = cal?.querySelector('table.calendarmonth');
    const ths = Array.from(calTable?.querySelectorAll('th') || []).map(th => ({
        text: th.innerText.trim(),
        width: th.offsetWidth
    }));
    const days = Array.from(calTable?.querySelectorAll('td') || []).slice(0, 7).map(td => ({
        text: td.innerText.trim(),
        width: td.offsetWidth
    }));
    const drawer = document.querySelector('#theme_boost-drawers-blocks');
    const pageEl = document.querySelector('#page');
    const content = document.querySelector('section[data-blockregion="content"]');

    return {
        drawerWidth: drawer?.offsetWidth,
        pageWidth: pageEl?.offsetWidth,
        contentWidth: content?.offsetWidth,
        asideWidth: aside?.offsetWidth,
        calWidth: cal?.offsetWidth,
        calTableWidth: calTable?.offsetWidth,
        thSample: ths,
        daySample: days
    };
});

console.log(JSON.stringify(measurements, null, 2));

await page.screenshot({ path: 'docs/screenshots/test_cal_fix.png', fullPage: false });
await browser.close();
