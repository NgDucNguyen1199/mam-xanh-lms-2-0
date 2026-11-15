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
        // TEST 1: INCOGNITO / GUEST VISIT TO http://localhost:8090/
        // ===================================================================
        console.log('--- 1. Testing Guest / Unauthenticated Access to http://localhost:8090/ ---');
        const guestContext = await browser.createBrowserContext();
        const guestPage = await guestContext.newPage();
        await guestPage.setViewport({ width: 1366, height: 768 });

        await guestPage.goto(`${BASE_URL}/`, { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 800));

        const guestMetrics = await guestPage.evaluate(() => {
            const loginBtn = document.querySelector('#mx-navbar-login-btn');
            const loginBtnStyle = loginBtn ? window.getComputedStyle(loginBtn) : null;
            const pubNavLinks = Array.from(document.querySelectorAll('.mx-public-navbar-links .nav-link')).map(a => a.textContent.trim());
            const subnav = document.querySelector('.mx-subnav');
            const heroTitle = document.querySelector('.mx-portal-hero-title')?.textContent.trim() || null;
            const featuredTitle = document.querySelector('.mx-featured-title')?.textContent.trim() || null;
            const newsTitles = Array.from(document.querySelectorAll('#mx-public-news h3')).map(h => h.textContent.trim());
            const courseCodes = Array.from(document.querySelectorAll('#mx-public-courses .mx-pub-course-code')).map(c => c.textContent.trim());
            const certLookupForm = !!document.querySelector('#mx-public-cert-lookup form.mx-lookup-form');
            const imagesLoaded = Array.from(document.querySelectorAll('#mx-frontpage-portal img')).every(img => img.complete && img.naturalWidth > 0);

            return {
                currentUrl: window.location.href,
                subnavPresent: !!subnav,
                pubNavLinks,
                loginBtn: loginBtn ? {
                    text: loginBtn.textContent.trim(),
                    href: loginBtn.getAttribute('href'),
                    bg: loginBtnStyle.backgroundColor,
                    color: loginBtnStyle.color,
                    borderRadius: loginBtnStyle.borderRadius,
                    padding: loginBtnStyle.padding,
                    fontWeight: loginBtnStyle.fontWeight
                } : null,
                heroTitle,
                featuredTitle,
                newsCount: newsTitles.length,
                newsTitles,
                courseCount: courseCodes.length,
                courseCodes,
                certLookupForm,
                imagesLoaded,
                horizontalOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth
            };
        });

        console.log('[GUEST PORTAL METRICS]', JSON.stringify(guestMetrics, null, 2));

        if (guestMetrics.currentUrl.includes('/login/index.php')) {
            throw new Error(`FAIL: Guest was redirected to login page: ${guestMetrics.currentUrl}`);
        }
        if (guestMetrics.subnavPresent) {
            throw new Error('FAIL: .mx-subnav should be hidden for unauthenticated visitors');
        }
        if (!guestMetrics.loginBtn) {
            throw new Error('FAIL: #mx-navbar-login-btn is missing on Navbar');
        }

        // Capture required screenshot 80_public_news_landing_page.png (viewport + full page)
        await saveShot(guestPage, '80_public_news_landing_page.png', false);
        await saveShot(guestPage, '80_public_news_landing_page_full.png', true);

        // Test interactive Article Reader Modal before clicking Login
        await guestPage.click('.js-open-mx-article[data-article-id="art1"]');
        await new Promise(r => setTimeout(r, 400));
        const modalVisible = await guestPage.evaluate(() => {
            const m = document.getElementById('mx-article-reader-modal');
            return m && m.style.display === 'flex' && document.getElementById('mx-modal-title')?.textContent.length > 10;
        });
        console.log('[ARTICLE MODAL OPENED]', modalVisible);
        await saveShot(guestPage, '81_public_news_article_modal.png', false);
        await guestPage.click('.mx-modal-close-btn');
        await new Promise(r => setTimeout(r, 300));

        // ===================================================================
        // TEST 2: CLICK "ĐĂNG NHẬP" ON NAVBAR -> SMOOTH REDIRECT TO /login/index.php
        // ===================================================================
        console.log('--- 2. Clicking "Đăng nhập" Button on Navbar ---');
        await Promise.all([
            guestPage.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
            guestPage.click('#mx-navbar-login-btn')
        ]);
        const afterClickUrl = guestPage.url();
        console.log('[AFTER NAVBAR LOGIN CLICK URL]', afterClickUrl);
        if (!afterClickUrl.includes('/login/index.php')) {
            throw new Error(`FAIL: Clicking #mx-navbar-login-btn did not navigate to /login/index.php (got ${afterClickUrl})`);
        }

        // ===================================================================
        // TEST 3: LOGGED-IN USER VISITING FRONTPAGE (/?redirect=0)
        // ===================================================================
        console.log('--- 3. Testing Logged-In User Viewing Frontpage News Portal ---');
        await guestPage.type('input[name="username"]', 'nd_hoa_dalat');
        await guestPage.type('input[name="password"]', 'MX!Rau2026');
        await Promise.all([
            guestPage.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
            guestPage.click('#loginbtn, button[type="submit"]')
        ]);

        await guestPage.goto(`${BASE_URL}/?redirect=0`, { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 500));

        const loggedInPortal = await guestPage.evaluate(() => ({
            url: window.location.href,
            subnavPresent: !!document.querySelector('.mx-subnav'),
            heroDashboardBtn: document.querySelector('#mx-hero-dashboard-btn')?.textContent.trim() || null,
            portalPresent: !!document.querySelector('#mx-frontpage-portal')
        }));
        console.log('[LOGGED-IN PORTAL METRICS]', JSON.stringify(loggedInPortal, null, 2));

        await guestContext.close();
        console.log('ALL PUBLIC NEWS PORTAL & NAVBAR LOGIN E2E CHECKS PASSED!');
    } finally {
        await browser.close();
    }
})();
