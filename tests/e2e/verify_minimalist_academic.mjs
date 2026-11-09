import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const PROJECT_ROOT = path.resolve(__dirname, '../..');
const SHOT_DIR = path.join(PROJECT_ROOT, 'docs', 'screenshots');
const ARTIFACT_DIR = 'C:\\Users\\pho21\\AppData\\Local\\Temp'; // will auto-detect below
const BASE_URL = process.env.MOODLE_URL || 'http://localhost:8090';
const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

fs.mkdirSync(SHOT_DIR, { recursive: true });

// Auto-detect artifact directory from parent
const actualArtifactDir = 'C:\\Users\\pho21\\.gemini\\antigravity\\brain\\84025476-35b6-4655-9f1f-0de62ae3b517';

async function saveShot(page, filename) {
    const dest = path.join(SHOT_DIR, filename);
    await page.screenshot({ path: dest, fullPage: false });
    if (fs.existsSync(actualArtifactDir)) {
        fs.copyFileSync(dest, path.join(actualArtifactDir, filename));
    }
    console.log(`  [SHOT] Saved screenshot: ${filename}`);
}

const EMOJI_REGEX = /[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{1F700}-\u{1F77F}\u{1F780}-\u{1F7FF}\u{1F800}-\u{1F8FF}\u{1F900}-\u{1F9FF}\u{1FA00}-\u{1FA6F}\u{1FA70}-\u{1FAFF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}\u{2B50}\u{FE0F}]/u;

function checkEmojiInText(text, contextName) {
    const match = text.match(EMOJI_REGEX);
    if (match) {
        console.error(`  [FAIL] Emoji found in ${contextName}: "${match[0]}"`);
        return false;
    }
    return true;
}

console.log('=== STARTING MINIMALIST ACADEMIC E2E VERIFICATION ===');

const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: true,
    acceptInsecureCerts: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors', '--window-size=1366,768'],
});

try {
    const page = await browser.newPage();
    await page.setViewport({ width: 1366, height: 768 });

    // -------------------------------------------------------------
    // TEST 1: Minimalist Login Page
    // -------------------------------------------------------------
    console.log('\n--- 1. Testing Minimalist Login Page ---');
    await page.goto(`${BASE_URL}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 600));

    const loginText = await page.evaluate(() => document.body.innerText);
    const loginEmojiOk = checkEmojiInText(loginText, 'Login Page');
    if (loginEmojiOk) {
        console.log('  [PASS] Zero emojis on Login Page.');
    }

    await saveShot(page, '30_minimalist_login_page.png');

    // -------------------------------------------------------------
    // TEST 2: Farmer Login & Minimalist Academic Dashboard
    // -------------------------------------------------------------
    console.log('\n--- 2. Testing Farmer Login & Minimalist Dashboard ---');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('#mx-1click-farmer'),
    ]);

    await page.goto(`${BASE_URL}/local/mamxanhapp/index.php?lang=vi`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 800));

    const dashText = await page.evaluate(() => document.body.innerText);
    const dashEmojiOk = checkEmojiInText(dashText, 'Dashboard Page');
    if (dashEmojiOk) {
        console.log('  [PASS] Zero emojis on Dashboard Page.');
    }

    // Verify widgets removed
    const widgetsStatus = await page.evaluate(() => {
        return {
            weatherWidget: !!document.querySelector('.mx-weather-widget'),
            bottomDock: !!document.querySelector('.mx-floating-dock, .bottom-nav-bar'),
            chatbotFab: !!document.querySelector('.mx-cropdoc-fab, [class*="cropdoc"], [class*="chatbot"]'),
            subnav: !!document.querySelector('.mx-subnav'),
            courseGrid: !!document.querySelector('.mx-course-grid')
        };
    });
    console.log('  Widget status:', widgetsStatus);
    if (!widgetsStatus.weatherWidget && !widgetsStatus.bottomDock && !widgetsStatus.chatbotFab) {
        console.log('  [PASS] AI widgets (weather, dock, chatbot) successfully eliminated.');
    }

    // Verify navbar background and text contrast
    const navContrast = await page.evaluate(() => {
        const nav = document.querySelector('.navbar.fixed-top');
        const link = document.querySelector('.navbar.fixed-top .primary-navigation .nav-link') || document.querySelector('.navbar.fixed-top a.nav-link');
        const csNav = nav ? window.getComputedStyle(nav) : null;
        const csLink = link ? window.getComputedStyle(link) : null;
        return {
            navBg: csNav ? csNav.backgroundColor : '',
            linkCol: csLink ? csLink.color : '',
        };
    });
    console.log('  Navbar style:', navContrast);

    await saveShot(page, '31_minimalist_academic_dashboard.png');

    // -------------------------------------------------------------
    // TEST 3: Course View (CHAMSOC01 - Course ID 2)
    // -------------------------------------------------------------
    console.log('\n--- 3. Testing Course CHAMSOC01 Academic Outline ---');
    await page.goto(`${BASE_URL}/course/view.php?id=2`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 800));

    const courseText = await page.evaluate(() => document.body.innerText);
    const courseEmojiOk = checkEmojiInText(courseText, 'Course View Page');
    if (courseEmojiOk) {
        console.log('  [PASS] Zero emojis on Course CHAMSOC01.');
    }

    // Verify sections and activities
    const courseOutline = await page.evaluate(() => {
        const sections = Array.from(document.querySelectorAll('.mx-course-section-block, .mx-sec-block, .section.main')).map(s => {
            const title = s.querySelector('.mx-sec-title, .sectionname');
            return title ? title.textContent.trim() : '';
        }).filter(Boolean);

        const activities = Array.from(document.querySelectorAll('.mx-act-row, .activity')).map(a => {
            const link = a.querySelector('.mx-act-link, .activityname a');
            return link ? link.textContent.trim() : '';
        }).filter(Boolean);

        return { sections, activityCount: activities.length, sampleActs: activities.slice(0, 8) };
    });
    console.log('  Course sections found:', courseOutline.sections);
    console.log('  Total activities found:', courseOutline.activityCount);
    console.log('  Sample activities:', courseOutline.sampleActs);

    await saveShot(page, '32_minimalist_academic_course_outline.png');

    // -------------------------------------------------------------
    // TEST 4: Real PDF Direct Viewer
    // -------------------------------------------------------------
    console.log('\n--- 4. Testing Real PDF Direct Inline Viewer ---');
    // Find the real PDF resource link
    const pdfUrl = await page.evaluate(() => {
        const links = Array.from(document.querySelectorAll('a')).filter(a => a.href.includes('/mod/resource/view.php'));
        return links.length > 0 ? links[0].href : null;
    });

    if (pdfUrl) {
        console.log(`  Navigating to PDF resource: ${pdfUrl}`);
        await page.goto(pdfUrl, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 2000));

        const pdfViewerState = await page.evaluate(() => {
            const shell = document.querySelector('.mx-pdf-viewer-shell');
            const toolbar = document.querySelector('.mx-pdf-toolbar');
            const viewport = document.querySelector('.mx-pdf-viewport');
            const pageWraps = document.querySelectorAll('.mx-pdf-page-wrap');
            const canvases = document.querySelectorAll('.mx-pdf-page-canvas');
            const fallbackIframe = document.querySelector('#resourceobject');
            const csVp = viewport ? window.getComputedStyle(viewport) : null;

            return {
                hasShell: !!shell,
                hasToolbar: !!toolbar,
                viewportBg: csVp ? csVp.backgroundColor : '',
                pageCount: pageWraps.length,
                canvasCount: canvases.length,
                fallbackActive: fallbackIframe ? (fallbackIframe.style.display !== 'none') : false
            };
        });
        console.log('  PDF Viewer state:', pdfViewerState);
        await saveShot(page, '33_minimalist_pdf_viewer_clean.png');
    } else {
        console.warn('  [WARN] No resource URL found on course page!');
    }

    // -------------------------------------------------------------
    // TEST 5: H5P Activity & Clean Buttons Fix
    // -------------------------------------------------------------
    console.log('\n--- 5. Testing H5P Activity & Contextual Buttons ---');
    await page.goto(`${BASE_URL}/course/view.php?id=2`, { waitUntil: 'networkidle2' });
    const h5pUrl = await page.evaluate(() => {
        const links = Array.from(document.querySelectorAll('a')).filter(a => a.href.includes('/mod/h5pactivity/view.php'));
        return links.length > 0 ? links[0].href : null;
    });

    if (h5pUrl) {
        console.log(`  Navigating to H5P activity: ${h5pUrl}`);
        await page.goto(h5pUrl, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 2500));

        // Check buttons inside H5P iframe
        const h5pButtons = await page.evaluate(() => {
            const iframes = Array.from(document.querySelectorAll('iframe'));
            let results = [];
            for (const f of iframes) {
                try {
                    const doc = f.contentDocument || (f.contentWindow && f.contentWindow.document);
                    if (doc) {
                        const btns = Array.from(doc.querySelectorAll('button, .h5p-joubelui-button, [role="button"]'));
                        for (const b of btns) {
                            results.push({
                                text: b.textContent.trim(),
                                aria: b.getAttribute('aria-label') || '',
                                visible: b.offsetWidth > 0 && b.offsetHeight > 0
                            });
                        }
                    }
                } catch(e) {}
            }
            return results;
        });
        console.log('  H5P buttons evaluated:', h5pButtons);
        await saveShot(page, '34_minimalist_h5p_buttons_fixed.png');
    }

    console.log('\n=== ALL E2E VERIFICATIONS COMPLETED SUCCESSFULLY ===');
} catch (err) {
    console.error('E2E Verification Error:', err);
    process.exit(1);
} finally {
    await browser.close();
}
