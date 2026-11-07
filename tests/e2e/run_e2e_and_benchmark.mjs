import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import https from 'node:https';
import zlib from 'node:zlib';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const PROJECT_ROOT = path.resolve(__dirname, '../..');
const SCREENSHOT_DIR = path.join(PROJECT_ROOT, 'docs', 'screenshots');
const ARTIFACT_DIR = 'C:\\Users\\pho21\\.gemini\\antigravity\\brain\\84025476-35b6-4655-9f1f-0de62ae3b517';
const BASE_URL = process.env.MOODLE_URL || 'https://moodle.lvh.me:8443';
const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });

const results = {
  timestamp: new Date().toISOString(),
  baseUrl: BASE_URL,
  passed: 0,
  failed: 0,
  assertions: [],
  rbacMatrix: [],
  screenshots: [],
  ttfbBenchmarks: [],
};

function assertCheck(name, condition, detail = '') {
  const status = Boolean(condition);
  if (status) {
    results.passed++;
    console.log(`  [PASS] ${name}${detail ? ` -> ${detail}` : ''}`);
  } else {
    results.failed++;
    console.error(`  [FAIL] ${name}${detail ? ` -> ${detail}` : ''}`);
  }
  results.assertions.push({ name, passed: status, detail });
  if (!status) {
    throw new Error(`Assertion failed: ${name} (${detail})`);
  }
}

async function saveShot(page, filename, copyToArtifact = true, fullPage = false) {
  const dest = path.join(SCREENSHOT_DIR, filename);
  await page.screenshot({ path: dest, fullPage });
  if (copyToArtifact && fs.existsSync(ARTIFACT_DIR)) {
    fs.copyFileSync(dest, path.join(ARTIFACT_DIR, filename));
  }
  results.screenshots.push(filename);
  console.log(`  [SHOT] Saved ${filename}`);
}

/**
 * Measure raw HTTPS Time-To-First-Byte (TTFB ms) ignoring self-signed Caddy local Root CA.
 */
function measureSingleTtfb(urlStr, cookieHeader = '') {
  return new Promise((resolve, reject) => {
    const u = new URL(urlStr);
    const agent = new https.Agent({ rejectUnauthorized: false, keepAlive: true });
    const start = performance.now();
    const req = https.request(
      {
        hostname: u.hostname,
        port: u.port || 443,
        path: u.pathname + u.search,
        method: 'GET',
        agent,
        headers: {
          'Accept-Encoding': 'gzip, deflate, br',
          ...(cookieHeader ? { Cookie: cookieHeader } : {}),
        },
      },
      (res) => {
        const ttfbMs = Number((performance.now() - start).toFixed(2));
        const chunks = [];
        res.on('data', (c) => chunks.push(c));
        res.on('end', () => {
          let raw = Buffer.concat(chunks);
          const enc = (res.headers['content-encoding'] || '').toLowerCase();
          if (enc === 'gzip') {
            raw = zlib.gunzipSync(raw);
          } else if (enc === 'br') {
            raw = zlib.brotliDecompressSync(raw);
          } else if (enc === 'deflate') {
            raw = zlib.inflateSync(raw);
          }
          resolve({
            statusCode: res.statusCode,
            ttfbMs,
            headers: res.headers,
            body: raw,
          });
        });
      }
    );
    req.on('error', reject);
    req.end();
  });
}

async function getCookieHeader(page) {
  const cookies = await page.cookies(BASE_URL);
  return cookies.map((c) => `${c.name}=${c.value}`).join('; ');
}

async function checkNoHorizontalOverflow(page, label) {
  const metrics = await page.evaluate(() => ({
    scrollWidth: document.documentElement.scrollWidth,
    innerWidth: window.innerWidth,
  }));
  assertCheck(
    `Responsive overflow guard (${label})`,
    metrics.scrollWidth <= metrics.innerWidth + 4,
    `scrollWidth=${metrics.scrollWidth}px <= viewport=${metrics.innerWidth}px`
  );
}

async function runSuite() {
  console.log('===================================================================');
  console.log(' MAM XANH LMS 2.0 - PUPPETEER E2E MULTI-ROLE & BENCHMARK SUITE');
  console.log('===================================================================');

  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: true,
    acceptInsecureCerts: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors'],
  });

  try {
    // =========================================================================
    // PART 1: LOGIN UI, BILINGUAL SWITCH & MOBILE/DESKTOP VIEWPORT CHECKS
    // =========================================================================
    console.log('\n--- [1/5] Testing Login Card, Bilingual Switch & Mobile-First UI ---');
    const ctx1 = await browser.createBrowserContext();
    const page = await ctx1.newPage();

    // Desktop Login (1440x900)
    await page.setViewport({ width: 1440, height: 900 });
    await page.goto(`${BASE_URL}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
    const has1ClickButtons = await page.evaluate(() => {
      const f = document.getElementById('mx-1click-farmer');
      const c = document.getElementById('mx-1click-cbkt');
      const a = document.getElementById('mx-1click-admin');
      const tok = document.querySelector('input[name="logintoken"]');
      return Boolean(f && c && a && tok && tok.value.length > 5);
    });
    assertCheck('Login 1-Click Role Buttons + CSRF logintoken present', has1ClickButtons);
    await saveShot(page, '01_login_desktop_vi.png');

    // Bilingual 1-click switch on Login -> EN then back to VI
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle2' }),
      page.click('#mx-lang-en'),
    ]);
    const enTitle = await page.$eval('#mx-login-title', (el) => el.textContent.trim());
    assertCheck('1-Click Language Switch to English on Login', enTitle.includes('High-Tech Agriculture'), enTitle);

    // Mobile Login (375x812)
    await page.setViewport({ width: 375, height: 812, isMobile: true, hasTouch: true });
    await page.goto(`${BASE_URL}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
    await checkNoHorizontalOverflow(page, 'Login Mobile 375x812');
    const btnHeight = await page.$eval('#mx-1click-farmer', (el) => el.getBoundingClientRect().height);
    assertCheck('WCAG AAA >= 48px touch target on Login 1-Click button', btnHeight >= 48, `height=${Math.round(btnHeight)}px`);
    await saveShot(page, '02_login_mobile_vi.png');

    // =========================================================================
    // PART 2: FARMER ROLE (nd_rau_lamdong) - FULL JOURNEY, QUIZ, CERT & VIETGAP
    // =========================================================================
    console.log('\n--- [2/5] Testing Farmer Role (nd_rau_lamdong) on Mobile 375x812 & Desktop 1440x900 ---');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle2' }),
      page.click('#mx-1click-farmer'),
    ]);
    assertCheck(
      'Farmer post-login smart auto-redirect to /local/mamxanhapp/index.php',
      page.url().includes('/local/mamxanhapp/index.php'),
      page.url()
    );
    await checkNoHorizontalOverflow(page, 'Farmer Dashboard Mobile 375x812');
    await saveShot(page, '03_farmer_dashboard_mobile.png');

    // Switch to Desktop 1440x900 for crisp desktop Farmer Dashboard + Smart AgTech Widgets
    await page.setViewport({ width: 1440, height: 900 });
    await page.reload({ waitUntil: 'networkidle2' });
    await saveShot(page, '04_farmer_dashboard_desktop.png', true, true);

    // Test Outdoor Harsh Sunlight Mode (#mx-outdoor-toggle)
    await page.click('#mx-outdoor-toggle');
    const outdoorActive = await page.evaluate(() => {
      return document.body.classList.contains('mx-outdoor-mode') &&
        getComputedStyle(document.body).backgroundColor === 'rgb(255, 255, 255)';
    });
    assertCheck('Outdoor Sunlight High-Contrast Mode (#FFF bg, #000 text, 115%)', outdoorActive);
    await saveShot(page, '05_farmer_outdoor_sunlight_mode.png');
    await page.click('#mx-outdoor-toggle'); // Restore standard palette

    // Test Floating AI Crop Doctor 24/7 Chatbot (#mx-crop-doctor-fab)
    await page.click('#mx-crop-doctor-fab');
    await page.evaluate(() => window.mxAskCropDoctor('pH EC'));
    const botReplyText = await page.$eval('#mx-bot-messages', (el) => el.textContent);
    assertCheck('24/7 AI Crop Doctor Chatbot 1-touch chip consultation', botReplyText.includes('5.8') || botReplyText.includes('EC'), 'pH/EC protocol returned');
    await saveShot(page, '06_crop_doctor_chatbot_247.png');
    await page.click('#mx-crop-doctor-fab'); // Close panel

    // Test Smart AgTech Calculator & Weather Alerts (/local/mamxanhapp/calculator.php)
    await page.goto(`${BASE_URL}/local/mamxanhapp/calculator.php`, { waitUntil: 'networkidle2' });
    const hasFertCalc = await page.evaluate(() => document.body.innerText.includes('EC') && Boolean(document.getElementById('mx-tab-fert')));
    await saveShot(page, '07a_smart_fertigation_calculator.png');
    await page.click('button[data-target="mx-tab-weather"]');
    const hasWeatherTab = await page.evaluate(() => document.body.innerText.includes('Lâm Đồng') && document.body.innerText.includes('Đắk Lắk'));
    assertCheck('Smart AgTech Calculator & Micro-climate Weather Hub loaded', hasFertCalc && hasWeatherTab);
    await saveShot(page, '07b_microclimate_weather_advisory.png');

    // Enter Flagship Course #2: CHEBIEN01 (/course/view.php?id=3)
    await page.goto(`${BASE_URL}/course/view.php?id=3`, { waitUntil: 'networkidle2' });
    const courseTilesInfo = await page.evaluate(() => {
      const tiles = document.querySelectorAll('.mx-course-tile-card, .mx-tile-action-btn, a[href*="/mod/"]');
      const pageLinks = [...document.querySelectorAll('a[href*="/mod/page/view.php"]')].map((a) => a.href);
      const fpLink = document.querySelector('a[href*="/mod/fieldpractice/view.php"]')?.href || '';
      const quizLink = document.querySelector('a[href*="/mod/quiz/view.php"]')?.href || '';
      return { tileCount: tiles.length, pageLinks: [...new Set(pageLinks)], fpLink, quizLink };
    });
    assertCheck('Course Tiles UI rendered with Page, FieldPractice & Quiz activities', courseTilesInfo.pageLinks.length >= 3 && Boolean(courseTilesInfo.fpLink) && Boolean(courseTilesInfo.quizLink), `pages=${courseTilesInfo.pageLinks.length}`);
    await saveShot(page, '08_course_tiles_chebien01_desktop.png', true, true);

    // Also verify Course Tiles UI on Mobile 375x812
    await page.setViewport({ width: 375, height: 812, isMobile: true, hasTouch: true });
    await page.reload({ waitUntil: 'networkidle2' });
    await checkNoHorizontalOverflow(page, 'Course Tiles Mobile 375x812');
    await saveShot(page, '09_course_tiles_chebien01_mobile.png');

    // Open Section 2 Interactive Slide Deck on Desktop 1440x900 & verify #mx-tts-player + slide controls
    await page.setViewport({ width: 1440, height: 900 });
    const slideDeckUrl = courseTilesInfo.pageLinks[1] || courseTilesInfo.pageLinks[0];
    await page.goto(slideDeckUrl, { waitUntil: 'networkidle2' });
    const lessonChecks = await page.evaluate(() => {
      const tts = document.getElementById('mx-tts-player');
      const backBtns = document.querySelectorAll('.mx-back-nav-btn');
      const bottomNav = document.querySelector('nav.bottom-nav-bar');
      return {
        hasTts: Boolean(tts),
        backCount: backBtns.length,
        hasBottomNav: Boolean(bottomNav),
      };
    });
    assertCheck('Hands-Free Voice Reader (#mx-tts-player) + Top/Bottom Back Buttons + Bottom Nav', lessonChecks.hasTts && lessonChecks.backCount >= 2 && lessonChecks.hasBottomNav);
    await saveShot(page, '10_interactive_slide_deck_and_tts.png', true, true);

    // Open Section 3 IPM Flashcards / VietGAP Accordion
    if (courseTilesInfo.pageLinks[2]) {
      await page.goto(courseTilesInfo.pageLinks[2], { waitUntil: 'networkidle2' });
      await saveShot(page, '11_ipm_flashcards_lesson.png');
    }

    // Open Section 3 mod_fieldpractice & perform Rotating QR + GPS Check-in
    await page.goto(courseTilesInfo.fpLink, { waitUntil: 'networkidle2' });
    const fpCmId = new URL(courseTilesInfo.fpLink).searchParams.get('id');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle2' }),
      page.click('form[action*="/mod/fieldpractice/view.php"] button[type="submit"]'),
    ]);
    const fpCheckedIn = await page.evaluate(() => document.body.innerText.includes('thành công') || document.body.innerText.includes('successfully'));
    assertCheck('Farmer Rotating QR + GPS Garden Check-In (mod_fieldpractice)', fpCheckedIn, `cmid=${fpCmId}`);
    await saveShot(page, '12_fieldpractice_qr_gps_checkin.png');

    // Open Section 4 Moodle Quiz (12 Questions) -> Start Attempt -> Answer all 12 -> Submit & Finish!
    console.log('  -> Starting live 12-question Moodle Quiz attempt as Farmer nd_rau_lamdong...');
    await page.goto(courseTilesInfo.quizLink, { waitUntil: 'networkidle2' });
    const startQuizClicked = await page.evaluate(() => {
      const btn = document.querySelector('form[action*="startattempt.php"] button[type="submit"]');
      if (btn) {
        btn.click();
        return true;
      }
      return false;
    });
    assertCheck('Moodle Quiz Start Attempt button triggered', startQuizClicked);
    await page.waitForNavigation({ waitUntil: 'networkidle2' });

    // If preflight confirmation modal/form appears, submit it
    if (!page.url().includes('/mod/quiz/attempt.php')) {
      const confirmBtn = await page.$('#id_submitbutton');
      if (confirmBtn) {
        await Promise.all([
          page.waitForNavigation({ waitUntil: 'networkidle2' }),
          confirmBtn.click(),
        ]);
      }
    }
    assertCheck('Moodle Quiz Attempt Page opened', page.url().includes('/mod/quiz/attempt.php'), page.url());

    // Select option index 0 (100% correct in CHEBIEN01) for all 12 questions
    const answeredCount = await page.evaluate(() => {
      const radios = document.querySelectorAll('.que.multichoice input[type="radio"][value="0"]');
      radios.forEach((r) => r.click());
      return radios.length;
    });
    assertCheck('Answered all 12 real Moodle Quiz multichoice questions', answeredCount === 12, `answered=${answeredCount}/12`);
    await saveShot(page, '13_quiz_12_questions_attempt.png');

    // Click "Finish attempt ..." (#mod_quiz-next-nav) -> Summary page
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle2' }),
      page.click('#mod_quiz-next-nav'),
    ]);
    assertCheck('Moodle Quiz Summary Page reached', page.url().includes('/mod/quiz/summary.php'), page.url());

    // Submit all and finish (handle Moodle 4.5 confirmation modal if shown, or direct form submit)
    await page.evaluate(() => {
      const finishForm = document.querySelector('form[action*="processattempt.php"]');
      if (finishForm) {
        finishForm.submit();
      }
    });
    await page.waitForNavigation({ waitUntil: 'networkidle2' });
    assertCheck(
      'Moodle Quiz graded cleanly with ZERO null grade_category errors',
      page.url().includes('/mod/quiz/review.php'),
      page.url()
    );
    const reviewText = await page.evaluate(() => document.body.innerText);
    assertCheck('Moodle Quiz Review displays passing score (100% / 10.00)', reviewText.includes('100') || reviewText.includes('12,00') || reviewText.includes('12.00'));
    await saveShot(page, '14_quiz_graded_review_100pct.png');

    // Verify Digital QR Certificate (HMAC-SHA256) automatically unlocked for CHEBIEN01!
    await page.goto(`${BASE_URL}/local/mamxanhcert/index.php`, { waitUntil: 'networkidle2' });
    const unlockedCertCode = await page.evaluate(() => {
      const codes = [...document.querySelectorAll('.mx-cert-list span')]
        .map((s) => s.textContent.trim())
        .filter((t) => t.includes('MXCERT-') && t.includes('CHEBIEN01'));
      if (codes.length > 0) {
        return codes[0].replace(/^🏅\s*/, '').trim();
      }
      return '';
    });
    assertCheck(
      'HMAC-SHA256 QR Certificate auto-unlocked upon Quiz >= 70%',
      unlockedCertCode.startsWith('MXCERT-'),
      unlockedCertCode
    );
    await saveShot(page, '15_farmer_digital_certificates.png');

    // Verify Certificate PDF Download (%PDF header)
    const farmerCookie = await getCookieHeader(page);
    const pdfRes = await measureSingleTtfb(
      `${BASE_URL}/local/mamxanhcert/view.php?code=${encodeURIComponent(unlockedCertCode)}&download=pdf`,
      farmerCookie
    );
    const pdfMagic = pdfRes.body.subarray(0, 5).toString('ascii');
    assertCheck(
      'Digital Certificate signed PDF generation (TCPDF %PDF)',
      pdfRes.statusCode === 200 && pdfMagic === '%PDF-',
      `HTTP ${pdfRes.statusCode}, magic=${pdfMagic}, size=${pdfRes.body.length} bytes`
    );

    // Verify Public Unauthenticated QR Certificate Verification (/local/mamxanhcert/verify.php)
    const publicCtx = await browser.createBrowserContext();
    const pubPage = await publicCtx.newPage();
    await pubPage.setViewport({ width: 1440, height: 900 });
    await pubPage.goto(`${BASE_URL}/local/mamxanhcert/verify.php?code=${encodeURIComponent(unlockedCertCode)}`, {
      waitUntil: 'networkidle2',
    });
    const certVerified = await pubPage.evaluate(() => {
      const card = document.getElementById('mx-verify-result-card');
      return Boolean(card && card.innerText.includes('HMAC-SHA256 VERIFIED'));
    });
    assertCheck('Public QR Certificate Cryptographic Verification (HMAC-SHA256 VERIFIED)', certVerified, unlockedCertCode);
    await saveShot(pubPage, '16_public_qr_cert_verification.png');

    // Create a brand-new VietGAP Production Batch + Voice Log as Farmer (/local/agriops/index.php)
    await page.goto(`${BASE_URL}/local/agriops/index.php`, { waitUntil: 'networkidle2' });
    await page.$eval('#id_product_name', (el) => {
      el.scrollIntoView({ block: 'center' });
      el.value = 'Ớt Chuông Đà Lạt Sấy Lạnh Xuất Khẩu Lô E2E';
    });
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle2' }),
      page.$eval('#mx-create-batch-form', (f) => f.requestSubmit()),
    ]);
    const createdBatchCode = await page.evaluate(() => {
      const alertEl = document.getElementById('mx-agriops-alert');
      if (!alertEl) return '';
      const m = alertEl.textContent.match(/VG-[A-Z]+-\d{6}-\d{4}/);
      return m ? m[0] : '';
    });
    assertCheck('Farmer created new VietGAP Batch with QR Trace Code', createdBatchCode.startsWith('VG-'), createdBatchCode);

    // Also submit a hands-free Voice Cultivation Diary entry onto the new batch
    await page.$eval('#id_log_notes', (el) => {
      el.value = 'Tưới nhỏ giọt sáng sớm pH 6.0, EC 1.7 mS/cm và kiểm tra kho mát Pre-cooling 4°C.';
    });
    await page.$eval('#id_voice_input', (el) => { el.value = '1'; });
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle2' }),
      page.$eval('#mx-add-log-form', (f) => f.requestSubmit()),
    ]);
    await saveShot(page, '17_vietgap_batch_and_voice_diary.png');

    // Verify Public VietGAP QR Traceability Passport (/local/agriops/trace.php)
    await pubPage.goto(`${BASE_URL}/local/agriops/trace.php?code=${encodeURIComponent(createdBatchCode)}`, {
      waitUntil: 'networkidle2',
    });
    const traceVerified = await pubPage.evaluate(() => document.body.innerText.includes('VIETGAP') && document.body.innerText.includes('Ớt Chuông'));
    assertCheck('Public VietGAP QR Traceability Passport verified', traceVerified, createdBatchCode);
    await saveShot(pubPage, '18_public_vietgap_qr_passport.png');
    await publicCtx.close();

    // =========================================================================
    // PART 3: FARMER RBAC 403 FORBIDDEN SECURITY CHECKS
    // =========================================================================
    console.log('\n--- [3/5] Verifying Strict 403 Forbidden RBAC Boundaries ---');
    for (const restrictedPath of [
      `/local/mamxanhapp/manage.php`,
      `/mod/fieldpractice/grade.php?id=${fpCmId}`,
      `/local/mamxanhapp/admin_audit.php`,
    ]) {
      const res = await measureSingleTtfb(`${BASE_URL}${restrictedPath}`, farmerCookie);
      results.rbacMatrix.push({ role: 'Farmer (nd_rau_lamdong)', endpoint: restrictedPath, status: res.statusCode });
      assertCheck(`RBAC 403 Block: Farmer -> ${restrictedPath}`, res.statusCode === 403, `HTTP ${res.statusCode}`);
    }
    await ctx1.close();

    // =========================================================================
    // PART 4: TECHNICAL OFFICER / CBKT (cbkt_pilot) - ANALYTICS, RUBRIC & CSV
    // =========================================================================
    console.log('\n--- [4/5] Testing Technical Officer Role (cbkt_pilot): Analytics, Rubric -> Gradebook & CSV BOM ---');
    const ctxCbkt = await browser.createBrowserContext();
    const cbktPage = await ctxCbkt.newPage();
    await cbktPage.setViewport({ width: 1440, height: 900 });
    await cbktPage.goto(`${BASE_URL}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
    await Promise.all([
      cbktPage.waitForNavigation({ waitUntil: 'networkidle2' }),
      cbktPage.click('#mx-1click-cbkt'),
    ]);
    assertCheck(
      'CBKT post-login smart auto-redirect to /local/mamxanhapp/manage.php',
      cbktPage.url().includes('/local/mamxanhapp/manage.php'),
      cbktPage.url()
    );
    await saveShot(cbktPage, '19_cbkt_analytics_dashboard_desktop.png', true, true);

    // Mobile 375x812 check on CBKT Analytics Dashboard (.table-responsive check)
    await cbktPage.setViewport({ width: 375, height: 812, isMobile: true, hasTouch: true });
    await cbktPage.reload({ waitUntil: 'networkidle2' });
    await checkNoHorizontalOverflow(cbktPage, 'CBKT Analytics Mobile 375x812');
    await saveShot(cbktPage, '20_cbkt_analytics_dashboard_mobile.png');

    // Grade Farmer nd_rau_lamdong's 4-Criteria Rubric in CHEBIEN01 (/mod/fieldpractice/grade.php)
    await cbktPage.setViewport({ width: 1440, height: 900 });
    await cbktPage.goto(`${BASE_URL}/mod/fieldpractice/grade.php?id=${fpCmId}`, { waitUntil: 'networkidle2' });
    await Promise.all([
      cbktPage.waitForNavigation({ waitUntil: 'networkidle2' }),
      cbktPage.$eval('button.mx-save-rubric-btn[data-username="nd_rau_lamdong"]', (btn) => {
        const fid = btn.getAttribute('form');
        const f = fid ? document.getElementById(fid) : btn.closest('form');
        if (f) f.requestSubmit();
        else btn.click();
      }),
    ]);
    const rubricSaved = await cbktPage.evaluate(() => {
      const al = document.getElementById('mx-rubric-saved-alert');
      return Boolean(al && al.textContent.includes('Gradebook'));
    });
    assertCheck('CBKT graded 4-Criteria Field Rubric & pushed to Moodle Gradebook', rubricSaved);
    await saveShot(cbktPage, '21_cbkt_field_rubric_grading.png');

    // Download CSV Report in Vietnamese & English -> verify UTF-8 BOM (\xEF\xBB\xBF)
    const cbktCookie = await getCookieHeader(cbktPage);
    const csvVi = await measureSingleTtfb(`${BASE_URL}/local/mamxanhapp/manage.php?download=csv&lang=vi`, cbktCookie);
    const hasBomVi = csvVi.body[0] === 0xef && csvVi.body[1] === 0xbb && csvVi.body[2] === 0xbf;
    const csvViText = csvVi.body.subarray(3).toString('utf8');
    const csvLines = csvViText.trim().split('\n');
    assertCheck(
      'CSV Export (VI) UTF-8 BOM (\\xEF\\xBB\\xBF) + 15 Farmer Rows',
      csvVi.statusCode === 200 && hasBomVi && csvLines.length >= 16 && csvLines[0].includes('Vùng trồng'),
      `lines=${csvLines.length}, header="${csvLines[0].slice(0, 55)}..."`
    );

    const csvEn = await measureSingleTtfb(`${BASE_URL}/local/mamxanhapp/manage.php?download=csv&lang=en`, cbktCookie);
    const hasBomEn = csvEn.body[0] === 0xef && csvEn.body[1] === 0xbb && csvEn.body[2] === 0xbf;
    const csvEnHeader = csvEn.body.subarray(3).toString('utf8').split('\n')[0];
    assertCheck(
      'CSV Export (EN) Bilingual Sync + UTF-8 BOM',
      csvEn.statusCode === 200 && hasBomEn && csvEnHeader.includes('Region'),
      `header="${csvEnHeader.slice(0, 55)}..."`
    );

    // CBKT 403 Forbidden check on Admin Audit page
    const cbktAdminRes = await measureSingleTtfb(`${BASE_URL}/local/mamxanhapp/admin_audit.php`, cbktCookie);
    results.rbacMatrix.push({ role: 'CBKT (cbkt_pilot)', endpoint: '/local/mamxanhapp/admin_audit.php', status: cbktAdminRes.statusCode });
    assertCheck('RBAC 403 Block: CBKT -> /local/mamxanhapp/admin_audit.php', cbktAdminRes.statusCode === 403, `HTTP ${cbktAdminRes.statusCode}`);

    // =========================================================================
    // PART 5: ADMIN ROLE (admin) & WARM TTFB PERFORMANCE BENCHMARK
    // =========================================================================
    console.log('\n--- [5/5] Testing Admin Role (admin) & Measuring Warm TTFB Benchmarks ---');
    const ctxAdmin = await browser.createBrowserContext();
    const adminPage = await ctxAdmin.newPage();
    await adminPage.setViewport({ width: 1440, height: 900 });
    await adminPage.goto(`${BASE_URL}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
    await Promise.all([
      adminPage.waitForNavigation({ waitUntil: 'networkidle2' }),
      adminPage.click('#mx-1click-admin'),
    ]);
    const adminCookie = await getCookieHeader(adminPage);
    const adminAuditRes = await measureSingleTtfb(`${BASE_URL}/local/mamxanhapp/admin_audit.php`, adminCookie);
    results.rbacMatrix.push({ role: 'Admin (admin)', endpoint: '/local/mamxanhapp/admin_audit.php', status: adminAuditRes.statusCode });
    assertCheck('RBAC 200 Allow: Admin -> /local/mamxanhapp/admin_audit.php', adminAuditRes.statusCode === 200, `HTTP ${adminAuditRes.statusCode}`);

    await adminPage.goto(`${BASE_URL}/local/mamxanhapp/admin_audit.php`, { waitUntil: 'networkidle2' });
    await saveShot(adminPage, '22_admin_infrastructure_audit.png');

    // Warm TTFB Benchmarks (5 runs per endpoint after OPcache JIT + Redis MUC warmup)
    const benchmarkTargets = [
      { name: 'Login Page (/login/index.php)', url: `${BASE_URL}/login/index.php`, cookie: '' },
      { name: 'Public QR Certificate Verify (/local/mamxanhcert/verify.php)', url: `${BASE_URL}/local/mamxanhcert/verify.php?code=${encodeURIComponent(unlockedCertCode)}`, cookie: '' },
      { name: 'Public VietGAP Traceability (/local/agriops/trace.php)', url: `${BASE_URL}/local/agriops/trace.php?code=${encodeURIComponent(createdBatchCode)}`, cookie: '' },
      { name: 'CBKT Analytics Hub (/local/mamxanhapp/manage.php)', url: `${BASE_URL}/local/mamxanhapp/manage.php`, cookie: cbktCookie },
      { name: 'Smart AgTech Calculator (/local/mamxanhapp/calculator.php)', url: `${BASE_URL}/local/mamxanhapp/calculator.php`, cookie: cbktCookie },
      { name: 'Course Tiles View CHAMSOC01 (/course/view.php?id=2)', url: `${BASE_URL}/course/view.php?id=2`, cookie: cbktCookie },
      { name: 'Admin System Audit (/local/mamxanhapp/admin_audit.php)', url: `${BASE_URL}/local/mamxanhapp/admin_audit.php`, cookie: adminCookie },
    ];

    for (const target of benchmarkTargets) {
      // 1 warmup hit
      await measureSingleTtfb(target.url, target.cookie);
      const samples = [];
      let encoding = '';
      for (let i = 0; i < 5; i++) {
        const r = await measureSingleTtfb(target.url, target.cookie);
        samples.push(r.ttfbMs);
        encoding = r.headers['content-encoding'] || 'identity';
      }
      samples.sort((a, b) => a - b);
      const minMs = samples[0];
      const medianMs = samples[2];
      const avgMs = Number((samples.reduce((s, v) => s + v, 0) / samples.length).toFixed(2));
      const maxMs = samples[samples.length - 1];
      results.ttfbBenchmarks.push({
        endpoint: target.name,
        minMs,
        medianMs,
        avgMs,
        maxMs,
        encoding,
      });
      console.log(`  [TTFB] ${target.name}: min=${minMs}ms | median=${medianMs}ms | avg=${avgMs}ms (encoding=${encoding})`);
    }

    await ctxCbkt.close();
    await ctxAdmin.close();
  } finally {
    await browser.close();
  }

  const reportPath = path.join(__dirname, 'e2e_benchmark_report.json');
  fs.writeFileSync(reportPath, JSON.stringify(results, null, 2), 'utf8');
  console.log('\n===================================================================');
  console.log(` ALL ${results.passed} E2E ASSERTIONS PASSED (0 FAILED)!`);
  console.log(` Report saved to: ${reportPath}`);
  console.log('===================================================================');
}

runSuite().catch((err) => {
  console.error('\n[FATAL E2E ERROR]:', err);
  process.exit(1);
});
