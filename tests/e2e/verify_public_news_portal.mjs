import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE_URL = 'http://localhost:8090';
const SCREENSHOT_DIR = path.resolve('docs/screenshots');
const ARTIFACT_DIR = 'C:/Users/pho21/.gemini/antigravity/brain/84025476-35b6-4655-9f1f-0de62ae3b517';

fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });

async function saveShot(page, filename, fullPage = false) {
    const dest = path.join(SCREENSHOT_DIR, filename);
    await page.screenshot({ path: dest, fullPage });
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
        // ===================================================================
        // TEST 1: GUEST / UNAUTHENTICATED NAVBAR & PORTAL (http://localhost:8090/)
        // ===================================================================
        console.log('--- 1. Testing Guest Navbar (No 4 Anchor Links) & News Portal ---');
        const guestContext = await browser.createBrowserContext();
        const guestPage = await guestContext.newPage();
        await guestPage.setViewport({ width: 1366, height: 768 });

        await guestPage.goto(`${BASE_URL}/`, { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 800));

        const guestMetrics = await guestPage.evaluate(() => {
            const brand = document.querySelector('.navbar .navbar-brand')?.textContent.trim() || null;
            const navLinks = Array.from(document.querySelectorAll('.navbar .primary-navigation .nav-link')).map(a => a.textContent.trim());
            const langLinks = Array.from(document.querySelectorAll('.navbar .mx-navbar-lang-link')).map(a => a.textContent.trim());
            const loginBtn = document.querySelector('#mx-navbar-login-btn');
            const loginBtnStyle = loginBtn ? window.getComputedStyle(loginBtn) : null;
            const subnav = document.querySelector('.mx-subnav');

            return {
                currentUrl: window.location.href,
                brand,
                navLinksCount: navLinks.length,
                navLinks,
                langLinks,
                subnavPresent: !!subnav,
                loginBtn: loginBtn ? {
                    text: loginBtn.textContent.trim(),
                    href: loginBtn.getAttribute('href'),
                    bg: loginBtnStyle.backgroundColor,
                    color: loginBtnStyle.color,
                    borderRadius: loginBtnStyle.borderRadius,
                    padding: loginBtnStyle.padding,
                    fontWeight: loginBtnStyle.fontWeight
                } : null,
                horizontalOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth
            };
        });

        console.log('[GUEST NAVBAR METRICS]', JSON.stringify(guestMetrics, null, 2));

        if (guestMetrics.navLinksCount !== 0) {
            throw new Error(`FAIL: Guest navbar should have 0 anchor links, found: ${guestMetrics.navLinks.join(', ')}`);
        }
        if (guestMetrics.subnavPresent) {
            throw new Error('FAIL: .mx-subnav must not exist');
        }

        await saveShot(guestPage, '80_public_news_landing_page.png', false);
        await saveShot(guestPage, '82_minimalist_guest_navbar_portal.png', false);

        // ===================================================================
        // TEST 2: CLICK "ĐĂNG NHẬP" -> LOG IN -> VERIFY UNIFIED SINGLE NAVBAR
        // ===================================================================
        console.log('--- 2. Clicking "Đăng nhập" & Logging In as nd_hoa_dalat ---');
        await Promise.all([
            guestPage.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
            guestPage.click('#mx-navbar-login-btn')
        ]);

        await guestPage.type('#username', 'nd_hoa_dalat');
        await guestPage.type('#password', 'MX!Rau2026');
        await Promise.all([
            guestPage.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
            guestPage.click('#loginbtn')
        ]);

        // 2A. Check /my/index.php (Dashboard)
        await guestPage.goto(`${BASE_URL}/my/index.php?lang=vi`, { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 800));

        const dashboardNavMetrics = await guestPage.evaluate(() => {
            const topTabs = Array.from(document.querySelectorAll('.navbar .mx-unified-nav > .nav-item > .nav-link')).map(a => ({
                text: a.textContent.trim(),
                active: a.classList.contains('active')
            }));
            const dropdownItems = Array.from(document.querySelectorAll('.navbar .mx-ops-dropdown-menu .dropdown-item')).map(a => a.textContent.trim());
            const subnav = document.querySelector('.mx-subnav');
            const langLinks = Array.from(document.querySelectorAll('.navbar .mx-navbar-lang-link')).map(a => a.textContent.trim());
            const calRect = document.querySelector('.block_calendar_month')?.getBoundingClientRect();

            return {
                url: window.location.href,
                subnavPresent: !!subnav,
                topTabs,
                activeTabs: topTabs.filter(t => t.active).map(t => t.text),
                dropdownItems,
                langLinks,
                calendarTopY: calRect ? Math.round(calRect.y) : null,
                calendarWidth: calRect ? Math.round(calRect.width) : null,
                horizontalOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth
            };
        });

        console.log('[LOGGED-IN /my/index.php NAVBAR METRICS]', JSON.stringify(dashboardNavMetrics, null, 2));

        if (dashboardNavMetrics.subnavPresent) {
            throw new Error('FAIL: .mx-subnav should be completely removed when logged in');
        }
        if (dashboardNavMetrics.activeTabs.length !== 1 || dashboardNavMetrics.activeTabs[0] !== 'Bảng Điều khiển') {
            throw new Error(`FAIL: Expected only 'Bảng Điều khiển' active on /my/index.php, got: ${JSON.stringify(dashboardNavMetrics.activeTabs)}`);
        }

        await saveShot(guestPage, '83_unified_single_navbar_dashboard.png', false);

        // 2B. Check /?redirect=0 (Site Home when logged in)
        await guestPage.goto(`${BASE_URL}/?redirect=0`, { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 500));
        const homeActiveTabs = await guestPage.evaluate(() =>
            Array.from(document.querySelectorAll('.navbar .mx-unified-nav > .nav-item > .nav-link.active')).map(a => a.textContent.trim())
        );
        console.log('[LOGGED-IN /?redirect=0 ACTIVE TABS]', homeActiveTabs);
        if (homeActiveTabs.length !== 1 || homeActiveTabs[0] !== 'Trang chủ') {
            throw new Error(`FAIL: Expected only 'Trang chủ' active on /?redirect=0, got: ${JSON.stringify(homeActiveTabs)}`);
        }

        // 2C. Check /local/mamxanhapp/index.php (Smart Agriculture space)
        await guestPage.goto(`${BASE_URL}/local/mamxanhapp/index.php`, { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 500));
        const mamxanhActiveTabs = await guestPage.evaluate(() =>
            Array.from(document.querySelectorAll('.navbar .mx-unified-nav > .nav-item > .nav-link.active')).map(a => a.textContent.trim())
        );
        console.log('[LOGGED-IN /local/mamxanhapp/index.php ACTIVE TABS]', mamxanhActiveTabs);
        if (mamxanhActiveTabs.length !== 1 || mamxanhActiveTabs[0] !== 'Không gian Nông nghiệp') {
            throw new Error(`FAIL: Expected only 'Không gian Nông nghiệp' active on /local/mamxanhapp/index.php, got: ${JSON.stringify(mamxanhActiveTabs)}`);
        }

        await guestContext.close();
        console.log('ALL SINGLE UNIFIED NAVBAR & ACTIVE STATE CHECKS PASSED!');
    } finally {
        await browser.close();
    }
})();
