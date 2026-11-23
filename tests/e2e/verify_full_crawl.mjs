import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE = 'http://localhost:8090';
const ARTIFACT_DIR = 'C:\\Users\\pho21\\.gemini\\antigravity\\brain\\84025476-35b6-4655-9f1f-0de62ae3b517';
const SCREENSHOT_DIR = path.resolve('docs/screenshots');
if (!fs.existsSync(SCREENSHOT_DIR)) {
    fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

const LEGACY_TOKENS = [
    '#15803D', '#15803d',
    '#DCFCE7', '#dcfce7',
    '#2E7D32', '#2e7d32',
    '#1B4332', '#1b4332',
    '#166534', '#16A34A', '#16a34a',
    '#F1F8E9', '#f1f8e9',
    '#D8F3DC', '#d8f3dc',
    '#E8F5E9', '#e8f5e9',
    'CHUẨN HD 1080P',
];

async function login(page, username, password) {
    await page.goto(`${BASE}/login/logout.php`, { waitUntil: 'networkidle2' }).catch(() => {});
    const logoutBtn = await page.$('form[action*="logout.php"] button[type="submit"]');
    if (logoutBtn) {
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}),
            logoutBtn.click(),
        ]);
    }
    await page.goto(`${BASE}/login/index.php`, { waitUntil: 'networkidle2' });
    await page.waitForSelector('#username', { timeout: 10000 });
    await page.evaluate(() => {
        document.querySelector('#username').value = '';
        document.querySelector('#password').value = '';
    });
    await page.type('#username', username);
    await page.type('#password', password);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('#loginbtn'),
    ]);
}

(async () => {
    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu', '--window-size=1440,900'],
        defaultViewport: { width: 1440, height: 900 },
    });

    const report = {
        totalUrlsCrawled: 0,
        httpErrors: [],
        phpErrors: [],
        legacyColorHits: [],
        junkCourseHits: [],
        courseParityChecks: {},
        badgeImageChecks: [],
        bilingualChecks: {},
        aplusFeatureChecks: {},
    };

    const page = await browser.newPage();

    page.on('response', (res) => {
        const status = res.status();
        const url = res.url();
        if (status >= 400 && url.startsWith(BASE)) {
            report.httpErrors.push({ url, status });
        }
    });

    async function visitAndAudit(relPath, roleLabel, screenshotName = null) {
        const fullUrl = `${BASE}${relPath}`;
        const res = await page.goto(fullUrl, { waitUntil: 'networkidle2', timeout: 30000 });
        report.totalUrlsCrawled++;
        const status = res ? res.status() : 0;
        const html = await page.content();
        const bodyText = await page.evaluate(() => document.body ? document.body.innerText : '');

        // Check PHP warnings/notices/exceptions
        if (/(Warning:|Fatal error:|Notice:|Coding error detected|dml_read_exception|Exception -)/i.test(bodyText)) {
            report.phpErrors.push({ role: roleLabel, url: relPath, snippet: bodyText.slice(0, 300) });
        }

        // Check legacy inline hex colors in #region-main HTML
        const regionHtml = await page.evaluate(() => {
            const el = document.querySelector('#region-main');
            return el ? el.innerHTML : '';
        });
        for (const tok of LEGACY_TOKENS) {
            if (regionHtml.includes(tok)) {
                report.legacyColorHits.push({ role: roleLabel, url: relPath, token: tok });
            }
        }

        if (screenshotName) {
            await new Promise((r) => setTimeout(r, 900));
            const p1 = path.join(SCREENSHOT_DIR, screenshotName);
            const p2 = path.join(ARTIFACT_DIR, screenshotName);
            await page.screenshot({ path: p1, fullPage: false });
            try { fs.copyFileSync(p1, p2); } catch (e) {}
            console.log(`   [SCREENSHOT] Saved ${screenshotName}`);
        }
        return { status, html, bodyText };
    }

    console.log('1. Auditing Guest / Public Portal & Favicon...');
    await visitAndAudit('/?redirect=0', 'guest');
    await visitAndAudit('/?redirect=0&lang=en', 'guest');
    await visitAndAudit('/?redirect=0&lang=vi', 'guest');
    await visitAndAudit('/login/index.php', 'guest');
    await visitAndAudit('/local/mamxanhcert/verify.php', 'guest');
    const favRes = await page.goto(`${BASE}/theme/image.php/agritrain_child/theme/1/favicon`, { waitUntil: 'networkidle2' });
    report.totalUrlsCrawled++;
    console.log('   Favicon HTTP status:', favRes.status());

    console.log('2. Logging in as Learner (nd_rau_lamdong) & crawling courses + A+ features...');
    await login(page, 'nd_rau_lamdong', 'MX!Rau2026');

    const dashAudit = await visitAndAudit('/my/index.php', 'learner');
    const myCoursesAudit = await visitAndAudit('/my/courses.php', 'learner', '90_clean_my_courses_no_junk.png');
    const hubAudit = await visitAndAudit('/local/mamxanhapp/index.php', 'learner');

    // Verify 0 junk courses and 0 MASTER-AGRITECH-TPL for learner
    for (const checkObj of [
        { name: '/my/index.php', text: dashAudit.bodyText },
        { name: '/my/courses.php', text: myCoursesAudit.bodyText },
        { name: '/local/mamxanhapp/index.php', text: hubAudit.bodyText },
    ]) {
        if (checkObj.text.includes('TAP-HUAN-NONG-DAN-DON-') || checkObj.text.includes('CH123') || checkObj.text.includes('MASTER-AGRITECH-TPL')) {
            report.junkCourseHits.push(checkObj.name);
        }
    }

    // Crawl all 8 active courses (2, 3, 4, 5, 6, 7, 19, 21) and check full parity
    const courseScreenshots = {
        4: '91_course4_iotnhamang_full_parity.png',
        5: '92_course5_thuycanh_full_parity.png',
        6: '93_course6_saurieng_full_parity.png',
        7: '94_course7_phanvisinh_full_parity.png',
    };
    for (const cid of [2, 3, 4, 5, 6, 7, 19, 21]) {
        const cRes = await visitAndAudit(`/course/view.php?id=${cid}`, 'learner', courseScreenshots[cid] || null);
        const parity = await page.evaluate(() => {
            const txt = document.body.innerText;
            const modCounts = {};
            document.querySelectorAll('[data-mx-modname]').forEach((li) => {
                const m = li.getAttribute('data-mx-modname');
                modCounts[m] = (modCounts[m] || 0) + 1;
            });
            return {
                modCounts,
                hasTheoryAtt: txt.includes('Điểm danh Lý thuyết'),
                hasPracAtt: txt.includes('Điểm danh Thực hành'),
                hasDisc1: txt.includes('Thảo luận tại lớp buổi 1'),
                hasDisc2: txt.includes('Thảo luận tại lớp buổi 2'),
                hasDueDates: txt.includes('Đã mở:') && txt.includes('Đến hạn:'),
                hasOffTopicIpmFlashcards: txt.includes('Flashcards Sâu bệnh IPM'),
            };
        });
        report.courseParityChecks[cid] = parity;

        // Visit first 3 activity links in each course
        const actLinks = await page.evaluate(() => {
            return Array.from(document.querySelectorAll('.mx-act-link[href]'))
                .map((a) => new URL(a.href).pathname + new URL(a.href).search)
                .slice(0, 3);
        });
        for (const link of actLinks) {
            await visitAndAudit(link, 'learner');
        }
    }

    // Check A+ Features as Learner
    const trAudit = await visitAndAudit('/local/mamxanhapp/transcript.php?courseid=2', 'learner', '95_aplus_academic_transcript.png');
    await visitAndAudit('/local/mamxanhapp/transcript.php?courseid=4', 'learner');
    await visitAndAudit('/local/mamxanhapp/transcript.php?courseid=19', 'learner');
    report.aplusFeatureChecks.transcript = {
        hasWeightedComponents: trAudit.bodyText.includes('10%') && trAudit.bodyText.includes('20%') && trAudit.bodyText.includes('25%'),
        hasPrintBtn: trAudit.bodyText.includes('In Bảng điểm (A4 Print)'),
    };

    const fbAudit = await visitAndAudit('/local/mamxanhapp/feedback_sheet.php?courseid=2', 'learner', '96_aplus_rubric_feedback_sheet.png');
    await visitAndAudit('/local/mamxanhapp/feedback_sheet.php?courseid=4', 'learner');
    await visitAndAudit('/local/mamxanhapp/feedback_sheet.php?courseid=19', 'learner');
    report.aplusFeatureChecks.feedbackSheet = {
        has4Criteria: fbAudit.bodyText.includes('Tiêu chí 1') && fbAudit.bodyText.includes('Tiêu chí 4'),
        hasInstructorFeedback: fbAudit.bodyText.includes('Kỹ sư Trần Minh Tuấn') || fbAudit.bodyText.includes('KS. Trần Văn Hùng'),
    };

    const exAudit = await visitAndAudit('/local/agriops/export_log.php?courseid=2', 'learner', '97_aplus_vietgap_log_export.png');
    report.aplusFeatureChecks.vietgapExport = {
        hasHmacHash: exAudit.bodyText.includes('HMAC-SHA256'),
        hasDownloadBtn: exAudit.bodyText.includes('Tải File Báo cáo (.HTML)'),
    };

    const certAudit = await visitAndAudit('/local/mamxanhcert/index.php', 'learner', '98_aplus_cert_eligibility_checklist.png');
    const certLower = certAudit.bodyText.toLowerCase();
    report.aplusFeatureChecks.certChecklist = {
        has3StepChecklist: certLower.includes('1. điểm danh') && certLower.includes('2. trắc nghiệm') && certLower.includes('3. rubric thực hành'),
    };

    // Check AgriOps, Calculator, Farmer Profile, Calendar, User Profile Badges
    await visitAndAudit('/local/agriops/index.php', 'learner');
    await visitAndAudit('/local/agriops/trace.php', 'learner');
    await visitAndAudit('/local/mamxanhapp/calculator.php', 'learner');
    await visitAndAudit('/local/farmerprofile/index.php', 'learner');
    const calAudit = await visitAndAudit('/calendar/view.php?view=month', 'learner');
    report.aplusFeatureChecks.calendarHeading = calAudit.bodyText.includes('Lịch Học thuật & Hạn nộp Bài');

    await visitAndAudit('/user/profile.php', 'learner', '99_profile_badges_200_ok.png');
    report.badgeImageChecks = await page.evaluate(() => {
        return Array.from(document.querySelectorAll('img[src*="badgeimage"]')).map((img) => ({
            src: img.src,
            complete: img.complete,
            naturalWidth: img.naturalWidth,
        }));
    });

    // Check Bilingual VI <-> EN switch on Course View & Lesson View
    const enCourse = await visitAndAudit('/course/view.php?id=2&lang=en', 'learner', '100_bilingual_en_course_view.png');
    const enPage = await visitAndAudit('/mod/page/view.php?id=160&lang=en', 'learner');
    await visitAndAudit('/course/view.php?id=2&lang=vi', 'learner');
    report.bilingualChecks.courseBackEn = enCourse.bodyText.includes('← Back to Dashboard');
    report.bilingualChecks.lessonBackEn = enPage.bodyText.includes('← Back to:');

    console.log('3. Logging in as Instructor (cbkt_pilot) & crawling management/cloner/grading...');
    await login(page, 'cbkt_pilot', 'MX!Canbo2026');
    await visitAndAudit('/local/mamxanhapp/manage.php', 'cbkt');
    const enManage = await visitAndAudit('/local/mamxanhapp/manage.php?lang=en', 'cbkt');
    report.bilingualChecks.manageEn = enManage.bodyText.includes('Lam Dong') && enManage.html.includes('Training Management');
    await visitAndAudit('/local/mamxanhapp/manage.php?lang=vi', 'cbkt');
    const clonerAudit = await visitAndAudit('/local/mamxanhapp/clone_course.php', 'cbkt');
    report.bilingualChecks.clonerNoDoubleAmp = !clonerAudit.bodyText.includes('&amp;');
    await visitAndAudit('/local/mamxanhapp/transcript.php?courseid=2&userid=4', 'cbkt');
    await visitAndAudit('/local/mamxanhapp/feedback_sheet.php?courseid=2&userid=4', 'cbkt');

    console.log('4. Logging in as Admin & checking admin_audit.php...');
    await login(page, 'admin', 'MX!Admin2026');
    await visitAndAudit('/local/mamxanhapp/admin_audit.php', 'admin');
    await visitAndAudit('/local/mamxanhapp/manage.php', 'admin');

    await browser.close();

    console.log('\n===================================================================');
    console.log('FULL CRAWL AUDIT SUMMARY');
    console.log('===================================================================');
    console.log(JSON.stringify(report, null, 2));

    fs.writeFileSync('docs/FULL_CRAWL_VERIFICATION_RESULT.json', JSON.stringify(report, null, 2));
})();
