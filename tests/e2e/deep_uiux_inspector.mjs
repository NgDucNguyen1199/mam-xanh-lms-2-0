import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const PROJECT_ROOT = path.resolve(__dirname, '../..');
const DOCS_DIR = path.join(PROJECT_ROOT, 'docs', 'screenshots');
const ARTIFACT_DIR = 'C:\\Users\\pho21\\.gemini\\antigravity\\brain\\84025476-35b6-4655-9f1f-0de62ae3b517';
const BASE_URL = process.env.MOODLE_URL || 'http://localhost:8090';
const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

fs.mkdirSync(DOCS_DIR, { recursive: true });

const VIEWPORTS = [
    { name: 'Desktop (1440x900)', width: 1440, height: 900, isMobile: false },
    { name: 'Laptop (1366x768)', width: 1366, height: 768, isMobile: false },
    { name: 'Mobile (375x812)', width: 375, height: 812, isMobile: true, hasTouch: true }
];

async function saveCapture(page, filename) {
    const dest = path.join(DOCS_DIR, filename);
    await page.screenshot({ path: dest, fullPage: false });
    if (fs.existsSync(ARTIFACT_DIR)) {
        fs.copyFileSync(dest, path.join(ARTIFACT_DIR, filename));
    }
    console.log(`    [SCREENSHOT SAVED] ${filename}`);
}

async function inspectPageDOM(page, pageName, roleName, viewportName) {
    return await page.evaluate((pageName, roleName, viewportName) => {
        const issues = [];

        // 1. Check Horizontal Overflow
        const docWidth = document.documentElement.clientWidth;
        const scrollWidth = document.documentElement.scrollWidth;
        const bodyScrollWidth = document.body.scrollWidth;
        if (scrollWidth > docWidth + 2 || bodyScrollWidth > docWidth + 2) {
            const culprits = [];
            const all = document.querySelectorAll('*');
            for (let el of all) {
                const rect = el.getBoundingClientRect();
                if (rect.right > docWidth + 2 && rect.width > 0 && rect.height > 0) {
                    const selector = el.className ? `${el.tagName.toLowerCase()}.${String(el.className).split(' ')[0]}` : el.tagName.toLowerCase();
                    culprits.push(`${selector} (right: ${Math.round(rect.right)}px > win: ${docWidth}px)`);
                    if (culprits.length >= 3) break;
                }
            }
            issues.push({
                type: 'OVERFLOW',
                severity: 'HIGH',
                message: `Horizontal overflow detected: scrollWidth ${Math.max(scrollWidth, bodyScrollWidth)}px > clientWidth ${docWidth}px`,
                culprits
            });
        }

        // 2. Scan for Emojis in visible text nodes
        const emojiRegex = /[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{1F700}-\u{1F77F}\u{1F780}-\u{1F7FF}\u{1F800}-\u{1F8FF}\u{1F900}-\u{1F9FF}\u{1FA00}-\u{1FA6F}\u{1FA70}-\u{1FAFF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}\u{2B50}\u{FE0F}]/u;
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
        let node;
        const foundEmojis = [];
        while (node = walker.nextNode()) {
            const parent = node.parentElement;
            if (!parent || parent.tagName === 'SCRIPT' || parent.tagName === 'STYLE') continue;
            const text = node.nodeValue;
            const match = text.match(emojiRegex);
            if (match) {
                foundEmojis.push({
                    emoji: match[0],
                    text: text.trim().slice(0, 60),
                    tag: parent.tagName,
                    class: parent.className || ''
                });
                if (foundEmojis.length >= 5) break;
            }
        }
        if (foundEmojis.length > 0) {
            issues.push({
                type: 'EMOJI_DETECTED',
                severity: 'HIGH',
                message: `Found ${foundEmojis.length} emoji(s) in DOM`,
                details: foundEmojis
            });
        }

        // 3. Inspect Form & Button Styles (Touch Target for Interactive Buttons)
        const buttons = document.querySelectorAll('button:not(.btn-icon):not(.close):not([data-action="view-day-link"]), .btn:not(.btn-icon):not([data-action="view-day-link"]), input[type="submit"]');
        let unpaddedButtons = 0;
        for (let btn of buttons) {
            const cs = window.getComputedStyle(btn);
            const pt = parseFloat(cs.paddingTop) || 0;
            const pb = parseFloat(cs.paddingBottom) || 0;
            const pl = parseFloat(cs.paddingLeft) || 0;
            const pr = parseFloat(cs.paddingRight) || 0;
            if (btn.offsetWidth > 40 && (pt + pb < 6 || pl + pr < 10)) {
                unpaddedButtons++;
            }
        }
        if (unpaddedButtons > 0) {
            issues.push({
                type: 'BUTTON_PADDING',
                severity: 'MEDIUM',
                message: `${unpaddedButtons} button(s) have tight padding`
            });
        }

        // 4. Inspect Data Tables (Exclude mini calendar grids)
        const tables = document.querySelectorAll('table.generaltable, .table, table:not(.calendarmonth):not(.minicalendar)');
        let unpaddedTables = 0;
        for (let tbl of tables) {
            const th = tbl.querySelector('th');
            if (th) {
                const cs = window.getComputedStyle(th);
                const pl = parseFloat(cs.paddingLeft) || 0;
                if (pl < 8) unpaddedTables++;
            }
        }
        if (unpaddedTables > 0) {
            issues.push({
                type: 'TABLE_PADDING',
                severity: 'MEDIUM',
                message: `${unpaddedTables} table header(s) have padding < 8px`
            });
        }

        // 5. Typography Scale
        const h1 = document.querySelector('h1, .page-header-headings h1, .mx-course-title');
        const h1Style = h1 ? {
            fontSize: window.getComputedStyle(h1).fontSize,
            fontWeight: window.getComputedStyle(h1).fontWeight,
            color: window.getComputedStyle(h1).color
        } : null;

        const bodyStyle = {
            bgColor: window.getComputedStyle(document.body).backgroundColor,
            color: window.getComputedStyle(document.body).color,
            fontSize: window.getComputedStyle(document.body).fontSize
        };

        return {
            pageName,
            roleName,
            viewportName,
            docWidth,
            scrollWidth,
            h1Style,
            bodyStyle,
            issues
        };
    }, pageName, roleName, viewportName);
}

async function loginUser(page, role) {
    await page.goto(`${BASE_URL}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
    if (role === 'farmer') {
        const btn = await page.$('#mx-1click-farmer');
        if (btn) {
            await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), btn.click()]);
            return;
        }
    } else if (role === 'cbkt') {
        const btn = await page.$('#mx-1click-cbkt');
        if (btn) {
            await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), btn.click()]);
            return;
        }
    } else if (role === 'admin') {
        const btn = await page.$('#mx-1click-admin');
        if (btn) {
            await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), btn.click()]);
            return;
        }
    }
}

async function runAudit() {
    console.log('===================================================================');
    console.log('PASS 4: POST-REFACTOR E2E AUDIT & REGRESSION VERIFICATION');
    console.log('===================================================================\n');

    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: true,
        acceptInsecureCerts: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1440,900']
    });

    const allAuditResults = [];

    const screens = [
        { id: 1, name: 'Login Page', path: '/login/index.php?lang=vi', role: 'guest', shotDesktop: '30_minimalist_login_page.png' },
        { id: 2, name: 'Dashboard Normal', path: '/my/index.php', role: 'farmer', shotDesktop: '48_pass5_desktop_dashboard.png', shotMobile: '55_pass5_mobile_dashboard.png' },
        { id: 2.1, name: 'Dashboard Edit Mode', path: '/my/index.php?edit=1', role: 'farmer' },
        { id: 3, name: 'Agricultural Space', path: '/local/mamxanhapp/index.php', role: 'farmer' },
        { id: 4, name: 'Course Outline View', path: '/course/view.php?id=2', role: 'farmer', shotDesktop: '49_pass5_desktop_course_view.png', shotMobile: '56_pass5_mobile_course_view.png' },
        { id: 5, name: 'Embedded PDF Resource', path: '/mod/resource/view.php?id=37', role: 'farmer' },
        { id: 6, name: 'Video & Theory Lecture', path: '/mod/page/view.php?id=2', role: 'farmer' },
        { id: 7.1, name: 'Interactive H5P Activity', path: '/mod/h5pactivity/view.php?id=38', role: 'farmer' },
        { id: 7.2, name: 'SCORM 1.2 Package', path: '/mod/scorm/view.php?id=39', role: 'farmer' },
        { id: 8.1, name: 'Attendance Student View', path: '/mod/attendance/view.php?id=134', role: 'farmer', shotDesktop: '50_pass5_desktop_attendance.png' },
        { id: 8.2, name: 'Attendance Teacher Manage', path: '/mod/attendance/manage.php?id=134', role: 'cbkt', shotMobile: '57_pass5_mobile_attendance_manage.png' },
        { id: 9.1, name: 'Class Discussion mod_assign', path: '/mod/assign/view.php?id=136', role: 'farmer', shotDesktop: '51_pass5_desktop_discussion_rubric.png' },
        { id: 9.2, name: 'Harvest Report & Rubric mod_assign', path: '/mod/assign/view.php?id=139', role: 'farmer' },
        { id: 10, name: 'Quiz View & Review', path: '/mod/quiz/view.php?id=6', role: 'farmer' },
        { id: 11.1, name: 'CBKT Management', path: '/local/mamxanhapp/manage.php', role: 'cbkt', shotDesktop: '53_pass5_desktop_cbkt_manage.png' },
        { id: 11.2, name: 'Course Cloner', path: '/local/mamxanhapp/clone_course.php', role: 'cbkt', shotDesktop: '54_pass5_desktop_cbkt_cloner.png' },
        { id: 12.1, name: 'VietGAP Diary agriops', path: '/local/agriops/index.php', role: 'farmer', shotMobile: '59_pass5_mobile_diary_agriops.png' },
        { id: 12.2, name: 'Agricultural Calculator', path: '/local/mamxanhapp/calculator.php', role: 'farmer', shotDesktop: '52_pass5_desktop_calculator.png', shotMobile: '58_pass5_mobile_calculator.png' },
        { id: 12.3, name: 'QR Certificate mamxanhcert', path: '/local/mamxanhcert/index.php', role: 'farmer' },
        { id: 12.4, name: 'Farmer Profile', path: '/local/farmerprofile/index.php', role: 'farmer' }
    ];

    for (const vp of VIEWPORTS) {
        console.log(`\n>>> AUDITING VIEWPORT: ${vp.name} <<<`);

        for (const role of ['guest', 'farmer', 'cbkt']) {
            const roleScreens = screens.filter(s => s.role === role);
            if (roleScreens.length === 0) continue;

            const context = await browser.createBrowserContext();
            const page = await context.newPage();
            await page.setViewport({
                width: vp.width,
                height: vp.height,
                isMobile: vp.isMobile,
                hasTouch: vp.hasTouch || false
            });

            if (role !== 'guest') {
                await loginUser(page, role);
            }

            for (const scr of roleScreens) {
                try {
                    await page.goto(`${BASE_URL}${scr.path}`, { waitUntil: 'networkidle2', timeout: 25000 });
                    await new Promise(r => setTimeout(r, 600));

                    const res = await inspectPageDOM(page, scr.name, role, vp.name);
                    allAuditResults.push(res);

                    const status = res.issues.length === 0 ? '[PASS]' : `[WARN: ${res.issues.length} issues]`;
                    console.log(`  ${status} ${scr.name} (${vp.name}) - Width: ${res.docWidth}px, BodyBG: ${res.bodyStyle.bgColor}`);
                    if (res.issues.length > 0) {
                        for (let iss of res.issues) {
                            console.log(`    ↳ [${iss.type}] ${iss.message}`);
                            if (iss.culprits) console.log(`      Culprits: ${iss.culprits.join(', ')}`);
                        }
                    }

                    // Save verified screenshots for Pass 5
                    if (vp.name.startsWith('Desktop') && scr.shotDesktop) {
                        await saveCapture(page, scr.shotDesktop);
                    }
                    if (vp.name.startsWith('Mobile') && scr.shotMobile) {
                        await saveCapture(page, scr.shotMobile);
                    }
                } catch (err) {
                    console.error(`  [ERROR] Failed loading ${scr.name}: ${err.message}`);
                }
            }
            await context.close();
        }
    }

    await browser.close();

    const totalChecks = allAuditResults.length;
    const totalIssues = allAuditResults.reduce((acc, r) => acc + r.issues.length, 0);
    const overflowIssues = allAuditResults.flatMap(r => r.issues.filter(i => i.type === 'OVERFLOW'));
    const emojiIssues = allAuditResults.flatMap(r => r.issues.filter(i => i.type === 'EMOJI_DETECTED'));
    const buttonIssues = allAuditResults.flatMap(r => r.issues.filter(i => i.type === 'BUTTON_PADDING'));
    const tableIssues = allAuditResults.flatMap(r => r.issues.filter(i => i.type === 'TABLE_PADDING'));

    console.log('\n===================================================================');
    console.log('PASS 4 & PASS 5 POST-REFACTOR SUMMARY REPORT');
    console.log('===================================================================');
    console.log(`Total Pages/Viewports Inspected: ${totalChecks}`);
    console.log(`Total Visual/UX Issues Detected: ${totalIssues}`);
    console.log(`  - Horizontal Overflow Issues: ${overflowIssues.length}`);
    console.log(`  - Emoji Remnants in DOM: ${emojiIssues.length}`);
    console.log(`  - Button Touch Target/Padding: ${buttonIssues.length}`);
    console.log(`  - Table Header Padding: ${tableIssues.length}`);

    fs.writeFileSync(
        path.join(DOCS_DIR, 'pass4_audit_report.json'),
        JSON.stringify(allAuditResults, null, 2),
        'utf8'
    );
    console.log(`Saved audit log to docs/screenshots/pass4_audit_report.json`);
}

runAudit().catch(console.error);
