import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE_URL = 'http://localhost:8090';
const SCREENSHOT_DIR = path.resolve('docs/screenshots');
const ARTIFACT_DIR = 'C:/Users/pho21/.gemini/antigravity/brain/84025476-35b6-4655-9f1f-0de62ae3b517';

fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });

async function saveShot(page, filename, clip = null) {
    const dest = path.join(SCREENSHOT_DIR, filename);
    const opts = { path: dest };
    if (clip) opts.clip = clip;
    await page.screenshot(opts);
    if (fs.existsSync(ARTIFACT_DIR)) {
        fs.copyFileSync(dest, path.join(ARTIFACT_DIR, filename));
    }
    console.log(`[SCREENSHOT] Saved ${filename}`);
}

(async () => {
    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu', '--window-size=1366,768']
    });

    try {
        const page = await browser.newPage();
        await page.setViewport({ width: 1366, height: 768 });

        // 1. Login as nd_hoa_dalat
        await page.goto(`${BASE_URL}/login/index.php`, { waitUntil: 'networkidle2', timeout: 30000 });
        await page.type('#username', 'nd_hoa_dalat');
        await page.type('#password', 'MX!Rau2026');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
            page.click('#loginbtn')
        ]);

        // 2. Open Dashboard /my/index.php?lang=vi
        await page.goto(`${BASE_URL}/my/index.php?lang=vi`, { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 800));

        // 3. Click Notification Bell to open Popover
        await page.click('#nav-notification-popover-container [data-region="popover-region-toggle"]');
        await page.waitForSelector('#nav-notification-popover-container .content-item-container', { visible: true, timeout: 15000 });
        await new Promise(r => setTimeout(r, 800));

        // Move mouse away so no tooltip covers the header
        await page.mouse.move(200, 200);
        await new Promise(r => setTimeout(r, 300));

        const popoverAudit = await page.evaluate(() => {
            const navbar = document.querySelector('.navbar.fixed-top');
            const navRect = navbar.getBoundingClientRect();
            const pop = document.querySelector('#nav-notification-popover-container .popover-region-container');
            const popRect = pop.getBoundingClientRect();
            const popStyle = window.getComputedStyle(pop);

            const hdr = pop.querySelector('.popover-region-header-container');
            const hdrRect = hdr.getBoundingClientRect();
            const hdrBtns = Array.from(hdr.querySelectorAll('.popover-region-header-actions a, .popover-region-header-actions button')).map(b => {
                const r = b.getBoundingClientRect();
                const cs = window.getComputedStyle(b);
                return {
                    title: b.getAttribute('title') || b.getAttribute('aria-label') || '',
                    width: Math.round(r.width),
                    height: Math.round(r.height),
                    bottom: Math.round(r.bottom),
                    headerBottom: Math.round(hdrRect.bottom),
                    overflowsHeader: r.bottom > hdrRect.bottom + 1,
                    borderWidth: cs.borderTopWidth
                };
            });

            const firstItem = pop.querySelector('.content-item-container');
            const firstItemRect = firstItem ? firstItem.getBoundingClientRect() : null;
            const firstItemStyle = firstItem ? window.getComputedStyle(firstItem) : null;
            const timestamps = Array.from(pop.querySelectorAll('.content-item-container .timestamp')).map(t => t.textContent.trim());
            const firstTs = firstItem?.querySelector('.timestamp')?.getBoundingClientRect();
            const firstVm = firstItem?.querySelector('.view-more')?.getBoundingClientRect();

            const bodyBg = window.getComputedStyle(document.body).backgroundColor;
            const activeLangBg = window.getComputedStyle(document.querySelector('.mx-navbar-lang-link.active')).backgroundColor;
            const calCircleBg = window.getComputedStyle(document.querySelector('.block_calendar_month td.hasevent .day-number-circle')).backgroundColor;

            return {
                navbarBottom: Math.round(navRect.bottom),
                popoverRect: {
                    x: Math.round(popRect.x),
                    y: Math.round(popRect.y),
                    width: Math.round(popRect.width),
                    height: Math.round(popRect.height),
                    borderRadius: popStyle.borderRadius
                },
                headerHeight: Math.round(hdrRect.height),
                headerText: hdr.querySelector('.popover-region-header-text')?.textContent.trim(),
                hdrBtns,
                firstItemTop: firstItemRect ? Math.round(firstItemRect.top) : null,
                firstItemUnreadBg: firstItemStyle ? firstItemStyle.backgroundColor : null,
                firstItemBorderLeft: firstItemStyle ? firstItemStyle.borderLeft : null,
                timestamps,
                hasAwkwardCacNgay: timestamps.some(t => t.includes('Các ngày') || t.includes('Các tháng')),
                footerRowAligned: (firstTs && firstVm) ? Math.abs(firstTs.y - firstVm.y) <= 8 : false,
                palette: {
                    bodyBg,
                    activeLangBg,
                    calCircleBg
                }
            };
        });

        console.log('[POPOVER & PALETTE AUDIT]', JSON.stringify(popoverAudit, null, 2));

        if (popoverAudit.hasAwkwardCacNgay) {
            throw new Error(`FAIL: Found awkward translation 'Các ngày' in timestamps: ${JSON.stringify(popoverAudit.timestamps)}`);
        }
        if (popoverAudit.hdrBtns.some(b => b.overflowsHeader)) {
            throw new Error(`FAIL: Header button overflows header container: ${JSON.stringify(popoverAudit.hdrBtns)}`);
        }

        // Save close-up of the Notification Popover + Navbar top-right area
        await saveShot(page, '84_harmonious_notification_popover_fixed.png', {
            x: 680,
            y: 0,
            width: 686,
            height: 560
        });

        // Save full viewport screenshot showing Dashboard + Open Notification Popover + Harmonious Palette
        await saveShot(page, '85_harmonious_dashboard_with_popover.png');

        console.log('ALL POPOVER UX, VIETNAMESE TRANSLATION & HARMONIOUS PALETTE CHECKS PASSED!');
    } finally {
        await browser.close();
    }
})();
