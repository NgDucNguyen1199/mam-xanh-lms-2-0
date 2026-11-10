import puppeteer from 'puppeteer-core';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, '../../');

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
        /* 1. Reset drawers and aside widths */
        #theme_boost-drawers-blocks {
            width: 340px !important;
            max-width: 340px !important;
            background: #f8fafc !important;
            border-left: 1px solid #e2e8f0 !important;
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
            display: block !important;
        }

        /* 2. Block styling inside drawer */
        #theme_boost-drawers-blocks .block {
            width: 100% !important;
            max-width: 100% !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            background: #ffffff !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
            margin-bottom: 20px !important;
        }
        #theme_boost-drawers-blocks .block .card-body {
            padding: 16px !important;
        }

        /* 3. Calendar month grid styling */
        .block_calendar_month {
            .maincalendar {
                width: 100% !important;
            }
            .calendarmonth {
                width: 100% !important;
                table-layout: fixed !important;
                border-collapse: separate !important;
                border-spacing: 0 !important;

                th {
                    color: #475569 !important;
                    font-size: 0.78rem !important;
                    font-weight: 600 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.04em !important;
                    text-align: center !important;
                    padding: 8px 2px !important;
                    border: none !important;
                }

                td {
                    font-size: 0.85rem !important;
                    padding: 4px 2px !important;
                    text-align: center !important;
                    border: none !important;
                    vertical-align: middle !important;

                    .day-number-circle {
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        width: 32px !important;
                        height: 32px !important;
                        border-radius: 50% !important;
                        font-weight: 500 !important;
                        color: #1e293b !important;
                        transition: all 0.15s ease !important;
                    }

                    &.hasevent .day-number-circle,
                    &.hasevents .day-number-circle {
                        background-color: #dcfce7 !important;
                        color: #166534 !important;
                        font-weight: 700 !important;
                        border: 1px solid #86efac !important;
                    }

                    &.today .day-number-circle {
                        background-color: #1e3a2f !important;
                        color: #ffffff !important;
                        font-weight: 700 !important;
                    }

                    &:hover .day-number-circle {
                        background-color: #f1f5f9 !important;
                    }
                    &.hasevent:hover .day-number-circle,
                    &.hasevents:hover .day-number-circle {
                        background-color: #166534 !important;
                        color: #ffffff !important;
                    }
                }
            }
        }
    `
});

const drawerPath = path.join(rootDir, 'docs/screenshots/cal_drawer_preview.png');
const dashPath = path.join(rootDir, 'docs/screenshots/dashboard_preview_full.png');

const drawerEl = await page.$('#theme_boost-drawers-blocks');
if (drawerEl) {
    await drawerEl.screenshot({ path: drawerPath });
}
await page.screenshot({ path: dashPath, fullPage: false });

await browser.close();
console.log('Screenshots saved to', drawerPath, 'and', dashPath);
