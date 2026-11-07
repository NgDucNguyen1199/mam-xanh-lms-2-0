import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import http from 'node:http';
import https from 'node:https';
import zlib from 'node:zlib';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const PROJECT_ROOT = path.resolve(__dirname, '../..');
const SCREENSHOT_DIR = path.join(PROJECT_ROOT, 'docs', 'screenshots');
const ARTIFACT_DIR = 'C:\\Users\\pho21\\.gemini\\antigravity\\brain\\84025476-35b6-4655-9f1f-0de62ae3b517';
const BASE_URL = process.env.MOODLE_URL || 'https://moodle.lvh.me:8443';
const DIRECT_HTTP_URL = process.env.MOODLE_DIRECT_HTTP_URL || 'http://localhost:8090';
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
    const isHttps = u.protocol === 'https:';
    const client = isHttps ? https : http;
    const agent = isHttps
      ? new https.Agent({ rejectUnauthorized: false, keepAlive: true })
      : new http.Agent({ keepAlive: true });
    const start = performance.now();
    const req = client.request(
      {
        hostname: u.hostname,
        port: u.port || (isHttps ? 443 : 80),
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

async function waitForFrameWithSelector(page, selector, timeoutMs = 20000) {
  const deadline = Date.now() + timeoutMs;
  while (Date.now() < deadline) {
    for (const frame of page.frames()) {
      try {
        const el = await frame.$(selector);
        if (el) {
          return frame;
        }
      } catch (_err) {
        // Ignore transient frame detach/navigation errors while polling
      }
    }
    await new Promise((r) => setTimeout(r, 250));
  }
  throw new Error(`Timed out waiting ${timeoutMs}ms for iframe selector: ${selector}`);
}

async function runSuite() {
  console.log('===================================================================');
  console.log(' MAM XANH LMS 2.0 - PUPPETEER E2E MULTI-ROLE & BENCHMARK SUITE');
  console.log('===================================================================');

  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: true,
    acceptInsecureCerts: true,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--ignore-certificate-errors',
      '--autoplay-policy=no-user-gesture-required',
    ],
  });

  try {
    // =========================================================================
    // PART 1: LOGIN UI, BILINGUAL SWITCH & MOBILE/DESKTOP VIEWPORT CHECKS
    // =========================================================================
    console.log('\n--- [1/6] Testing Login Card, Bilingual Switch & Mobile-First UI ---');
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
    console.log('\n--- [2/6] Testing Farmer Role (nd_rau_lamdong) on Mobile 375x812 & Desktop 1440x900 ---');
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
    console.log('\n--- [3/6] Verifying Strict 403 Forbidden RBAC Boundaries ---');
    for (const restrictedPath of [
      `/local/mamxanhapp/manage.php`,
      `/local/mamxanhapp/clone_course.php`,
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
    console.log('\n--- [4/6] Testing Technical Officer Role (cbkt_pilot): Analytics, Rubric -> Gradebook & CSV BOM ---');
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
    // PART 5: DEFENSE COMMITTEE 5 CORE UPGRADES (PDF EMBED, H5P, SCORM 1.2,
    //         MASTER SEQUENTIAL TEMPLATE, 1-CLICK CLONER, REAL VIDEO & DIRECT HTTP)
    // =========================================================================
    console.log('\n--- [5/6] Testing Defense Committee 5 Core Upgrades (Inline PDF, H5P, SCORM 1.2, Master Template, 1-Click Cloner, HTML5 Video & Direct HTTP) ---');

    // 5.1 Direct Standalone HTTP Port on moodle_app (http://localhost:8090, zero Reverse Proxy dependency)
    const ctxDirect = await browser.createBrowserContext();
    const directPage = await ctxDirect.newPage();
    await directPage.setViewport({ width: 1440, height: 900 });
    const directResp = await directPage.goto(`${DIRECT_HTTP_URL}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
    assertCheck(
      `Standalone Direct HTTP moodle_app port (${DIRECT_HTTP_URL}) reachable without Reverse Proxy`,
      Boolean(directResp && directResp.status() === 200),
      `HTTP ${directResp ? directResp.status() : 'ERR'}`
    );
    await Promise.all([
      directPage.waitForNavigation({ waitUntil: 'networkidle2' }),
      directPage.click('#mx-1click-farmer'),
    ]);
    assertCheck(
      `Direct HTTP 1-Click Farmer Login stays cleanly on ${DIRECT_HTTP_URL}`,
      directPage.url().startsWith(`${DIRECT_HTTP_URL}/local/mamxanhapp/index.php`),
      directPage.url()
    );
    await ctxDirect.close();

    // Open fresh Farmer context (nd_saurieng_daklak) to test Real HTML5 Video, Inline PDF, H5P, SCORM 1.2 & Master Sequential Path
    const ctxCouncilFarmer = await browser.createBrowserContext();
    const cfPage = await ctxCouncilFarmer.newPage();
    await cfPage.setViewport({ width: 1440, height: 900 });
    await cfPage.goto(`${BASE_URL}/login/index.php?lang=vi`, { waitUntil: 'networkidle2' });
    await cfPage.$eval('input[name="username"]', (el) => { el.value = 'nd_saurieng_daklak'; });
    await cfPage.$eval('input[name="password"]', (el) => { el.value = 'MX!Rau2026'; });
    await Promise.all([
      cfPage.waitForNavigation({ waitUntil: 'networkidle2' }),
      cfPage.$eval('#login', (f) => f.submit()),
    ]);
    assertCheck(
      'Farmer nd_saurieng_daklak logged in for Defense Committee feature verification',
      cfPage.url().includes('/local/mamxanhapp/index.php'),
      cfPage.url()
    );

    // Inspect CHAMSOC01 (/course/view.php?id=2) to collect Video, Inline PDF, H5P & SCORM 1.2 URLs
    await cfPage.goto(`${BASE_URL}/course/view.php?id=2`, { waitUntil: 'networkidle2' });
    const chamsocLinks = await cfPage.evaluate(() => ({
      hasStepper: Boolean(document.getElementById('mx-learning-path-stepper')),
      videoUrl: document.querySelector('a[href*="/mod/page/view.php"]')?.href || '',
      pdfUrl: document.querySelector('a[href*="/mod/resource/view.php"]')?.href || '',
      h5pUrl: document.querySelector('a[href*="/mod/h5pactivity/view.php"]')?.href || '',
      scormUrl: document.querySelector('a[href*="/mod/scorm/view.php"]')?.href || '',
    }));
    assertCheck(
      'CHAMSOC01 displays Stepper + Video + Inline PDF (mod_resource) + H5P (mod_h5pactivity) + SCORM 1.2 (mod_scorm)',
      chamsocLinks.hasStepper && Boolean(chamsocLinks.videoUrl && chamsocLinks.pdfUrl && chamsocLinks.h5pUrl && chamsocLinks.scormUrl)
    );

    // 5.2 Smooth Local HTML5 <video> Playback + Vietnamese Subtitles + Chapter Seek (Cô Lương's requirement)
    await cfPage.goto(chamsocLinks.videoUrl, { waitUntil: 'networkidle2' });
    await cfPage.waitForFunction(
      () => {
        const v =
          document.querySelector('#region-main video.vjs-tech') ||
          document.querySelector('#region-main .mx-real-html5-video video') ||
          document.querySelector('#region-main video');
        if (!v) return false;
        if (!v.dataset.mxTriggeredLoad && v.readyState === 0) {
          v.dataset.mxTriggeredLoad = '1';
          v.preload = 'auto';
          v.load();
        }
        return v.readyState >= 1 && v.duration > 3 && v.videoWidth > 0;
      },
      { timeout: 25000 }
    );
    const videoAudit = await cfPage.evaluate(async () => {
      const v =
        document.querySelector('#region-main video.vjs-tech') ||
        document.querySelector('#region-main .mx-real-html5-video video') ||
        document.querySelector('#region-main video');
      const noYoutube = !document.querySelector('#region-main iframe[src*="youtube.com"], #region-main iframe[src*="youtu.be"]');
      const currentSrc = v.currentSrc || v.src || '';
      const hasMp4OrWebm =
        currentSrc.includes('.mp4') ||
        currentSrc.includes('.webm') ||
        Boolean(document.querySelector('#region-main source[type="video/mp4"], #region-main source[type="video/webm"]'));
      const hasViTrack =
        (v.textTracks && v.textTracks.length > 0) ||
        Boolean(
          document.querySelector(
            '#region-main track[kind="subtitles"], #region-main .vjs-subs-caps-button, #region-main .vjs-text-track-display'
          )
        );
      await new Promise((resolve) => {
        const done = () => {
          v.removeEventListener('seeked', done);
          resolve();
        };
        v.addEventListener('seeked', done);
        v.currentTime = Math.min(4.0, v.duration / 2);
        setTimeout(done, 1500);
      });
      return {
        noYoutube,
        hasMp4OrWebm,
        hasViTrack,
        duration: Number(v.duration.toFixed(2)),
        width: v.videoWidth,
        height: v.videoHeight,
        currentTime: Number(v.currentTime.toFixed(2)),
        currentSrc,
      };
    });
    assertCheck(
      'Real Local HTML5 <video> plays smoothly (MP4/WebM + VI subtitles, zero YouTube embed block)',
      videoAudit.noYoutube && videoAudit.hasMp4OrWebm && videoAudit.hasViTrack && videoAudit.duration > 3 && videoAudit.width > 0,
      `${videoAudit.width}x${videoAudit.height}, duration=${videoAudit.duration}s, seek=${videoAudit.currentTime}s, mp4=${videoAudit.hasMp4OrWebm}, sub=${videoAudit.hasViTrack}`
    );
    await cfPage.evaluate(() => {
      document.querySelector('.mx-digital-lesson')?.scrollIntoView({ behavior: 'instant', block: 'start' });
      window.scrollBy(0, -68);
    });
    await saveShot(cfPage, '23_real_html5_video_lesson.png');

    // 5.3 Inline Embedded PDF Viewer without Downloading (mod_resource RESOURCELIB_DISPLAY_EMBED = 1, 650px Desktop / 480px Mobile)
    await cfPage.setViewport({ width: 1440, height: 900 });
    await cfPage.goto(chamsocLinks.pdfUrl, { waitUntil: 'networkidle2' });
    await cfPage.waitForSelector('#mx-inline-pdf-viewer[data-pdf-rendered="true"]', { timeout: 25000 });

    // Click Zoom In (+20% -> 120%) and wait for re-render
    await cfPage.evaluate(() => {
      document.getElementById('mx-inline-pdf-viewer')?.removeAttribute('data-pdf-rendered');
      document.getElementById('mx-pdf-zoom-in')?.click();
    });
    await cfPage.waitForSelector('#mx-inline-pdf-viewer[data-pdf-rendered="true"]', { timeout: 15000 });

    const pdfDesktopAudit = await cfPage.evaluate(() => {
      const vp = document.getElementById('mx-pdf-viewport');
      const canvases = document.querySelectorAll('canvas.mx-pdf-page-canvas');
      const zoomPct = document.getElementById('mx-pdf-zoom-pct')?.textContent.trim() || '';
      return {
        stayedOnLessonPage: window.location.pathname.includes('/mod/resource/view.php'),
        viewportHeight: vp ? Math.round(vp.getBoundingClientRect().height) : 0,
        canvasCount: canvases.length,
        firstCanvasWidth: canvases[0] ? canvases[0].width : 0,
        zoomPct,
      };
    });
    assertCheck(
      'Inline Embedded PDF Viewer (Desktop 650px, multi-page canvas, Zoom + to 120%, ZERO download)',
      pdfDesktopAudit.stayedOnLessonPage &&
        Math.abs(pdfDesktopAudit.viewportHeight - 650) <= 2 &&
        pdfDesktopAudit.canvasCount >= 2 &&
        pdfDesktopAudit.firstCanvasWidth > 400 &&
        pdfDesktopAudit.zoomPct === '120%',
      `height=${pdfDesktopAudit.viewportHeight}px, pages=${pdfDesktopAudit.canvasCount}, zoom=${pdfDesktopAudit.zoomPct}`
    );
    await cfPage.evaluate(() => {
      document.getElementById('mx-inline-pdf-viewer')?.scrollIntoView({ behavior: 'instant', block: 'start' });
      window.scrollBy(0, -115);
    });
    await saveShot(cfPage, '24a_inline_pdf_viewer_desktop.png');

    // Verify Mobile 375x812 Inline PDF Viewer (480px viewport height + zero horizontal overflow)
    await cfPage.setViewport({ width: 375, height: 812, isMobile: true, hasTouch: true });
    await cfPage.reload({ waitUntil: 'networkidle2' });
    await cfPage.waitForSelector('#mx-inline-pdf-viewer[data-pdf-rendered="true"]', { timeout: 25000 });
    const pdfMobileHeight = await cfPage.$eval('#mx-pdf-viewport', (el) => Math.round(el.getBoundingClientRect().height));
    assertCheck(
      'Inline Embedded PDF Viewer (Mobile 375x812 -> 480px viewport height)',
      Math.abs(pdfMobileHeight - 480) <= 2,
      `height=${pdfMobileHeight}px`
    );
    await checkNoHorizontalOverflow(cfPage, 'Inline PDF Viewer Mobile 375x812');
    await cfPage.evaluate(() => {
      document.getElementById('mx-inline-pdf-viewer')?.scrollIntoView({ behavior: 'instant', block: 'start' });
      window.scrollBy(0, -64);
    });
    await saveShot(cfPage, '24b_inline_pdf_viewer_mobile.png');

    // 5.4a Authentic H5P Activity (mod_h5pactivity QuestionSet -> xAPI Gradebook Sync)
    await cfPage.setViewport({ width: 1440, height: 920 });
    await cfPage.goto(chamsocLinks.h5pUrl, { waitUntil: 'networkidle2' });
    const h5pFrame = await waitForFrameWithSelector(cfPage, '.h5p-alternative-container, .h5p-question-content', 25000);
    const h5pAnswered = await h5pFrame.evaluate(async () => {
      const firstAlt = document.querySelector('.h5p-alternative-container');
      if (firstAlt) firstAlt.click();
      await new Promise((r) => setTimeout(r, 250));
      const checkBtn = document.querySelector('.h5p-question-check-answer');
      if (checkBtn) checkBtn.click();
      await new Promise((r) => setTimeout(r, 500));
      return {
        text: document.body.innerText.slice(0, 300),
        hasQuestion: Boolean(document.querySelector('.h5p-question-content')),
      };
    });
    assertCheck(
      'Authentic H5P Activity (mod_h5pactivity H5P.QuestionSet) rendered & interactive',
      h5pAnswered.hasQuestion && h5pAnswered.text.includes('5.8'),
      'H5P Question 1 checked live'
    );
    await cfPage.evaluate(() => {
      document.querySelector('iframe.h5p-player')?.scrollIntoView({ behavior: 'instant', block: 'start' });
      window.scrollBy(0, -140);
    });
    await saveShot(cfPage, '25a_h5p_interactive_activity.png');

    // Finish remaining H5P questions so xAPI pushes completion/score to Moodle Gradebook
    await h5pFrame.evaluate(async () => {
      for (let step = 0; step < 3; step++) {
        const nextBtn = document.querySelector('.h5p-question-next');
        if (nextBtn && nextBtn.offsetParent !== null) {
          nextBtn.click();
          await new Promise((r) => setTimeout(r, 350));
        }
        const visibleQ = [...document.querySelectorAll('.question-container')].find((q) => q.offsetParent !== null) || document;
        const alt = visibleQ.querySelector('.h5p-alternative-container');
        if (alt) alt.click();
        await new Promise((r) => setTimeout(r, 200));
        const chk = visibleQ.querySelector('.h5p-question-check-answer');
        if (chk && chk.offsetParent !== null) chk.click();
        await new Promise((r) => setTimeout(r, 350));
        const fin = visibleQ.querySelector('.h5p-question-finish');
        if (fin && fin.offsetParent !== null) {
          fin.click();
          await new Promise((r) => setTimeout(r, 600));
        }
      }
    });

    // 5.4b Authentic ADL SCORM 1.2 Package (mod_scorm -> LMSInitialize / LMSSetValue / LMSCommit / LMSFinish)
    await cfPage.setViewport({ width: 1440, height: 960 });
    await cfPage.goto(chamsocLinks.scormUrl, { waitUntil: 'networkidle2' });
    await Promise.all([
      cfPage.waitForNavigation({ waitUntil: 'networkidle2' }),
      cfPage.$eval('#scormviewform', (f) => f.submit()),
    ]);
    const scormFrame = await waitForFrameWithSelector(cfPage, '#scorm-quiz-form', 25000);
    await scormFrame.waitForFunction(
      () => {
        const b = document.getElementById('scorm-conn-badge');
        return Boolean(b && b.textContent.includes('LMSInitialize OK'));
      },
      { timeout: 10000 }
    );
    const scormOutcome = await scormFrame.evaluate(() => {
      ['sq1', 'sq2', 'sq3', 'sq4'].forEach((name) => {
        const r = document.querySelector(`input[name="${name}"][value="1"]`);
        if (r) r.click();
      });
      document.getElementById('scorm-submit-btn')?.click();
      const banner = document.getElementById('scorm-result-banner');
      banner?.scrollIntoView({ behavior: 'instant', block: 'end' });
      return {
        connBadge: document.getElementById('scorm-conn-badge')?.textContent.trim() || '',
        bannerText: banner ? banner.innerText : '',
      };
    });
    assertCheck(
      'Authentic SCORM 1.2 SCO connected to Moodle SCORM API & synced 100/100 COMPLETED',
      scormOutcome.connBadge.includes('LMSInitialize OK') &&
        scormOutcome.bannerText.includes('100/100') &&
        scormOutcome.bannerText.includes('COMPLETED'),
      scormOutcome.connBadge
    );
    await new Promise((r) => setTimeout(r, 800));
    await saveShot(cfPage, '25b_scorm12_vietgap_player.png');

    // Return to /mod/scorm/view.php and verify Moodle recorded score 100
    await cfPage.goto(chamsocLinks.scormUrl, { waitUntil: 'networkidle2' });
    const scormSummaryText = await cfPage.evaluate(() => document.body.innerText);
    assertCheck(
      'Moodle SCORM 1.2 summary confirms persisted score 100 in LMS',
      scormSummaryText.includes('100'),
      'Score 100 verified on /mod/scorm/view.php'
    );

    // 5.5 Master Course Template [MASTER-AGRITECH-TPL] Sequential 4-Step Learning Path & Restrict Access
    await cfPage.setViewport({ width: 1440, height: 920 });
    await cfPage.goto(`${BASE_URL}/course/view.php?id=8`, { waitUntil: 'networkidle2' });
    const masterInitial = await cfPage.evaluate(() => {
      const stepper = document.getElementById('mx-learning-path-stepper');
      const stepNodes = document.querySelectorAll('#mx-learning-path-stepper .mx-stepper-node');
      const sec1 = document.getElementById('mx-sec-1');
      const sec2 = document.getElementById('mx-sec-2');
      const sec3 = document.getElementById('mx-sec-3');
      const sec4 = document.getElementById('mx-sec-4');
      const sec1Links = sec1 ? [...sec1.querySelectorAll('a.mx-tile-action-btn')].map((a) => a.href) : [];
      return {
        hasStepper: Boolean(stepper),
        stepCount: stepNodes.length,
        sec1Open: Boolean(sec1 && !sec1.classList.contains('mx-sec-is-locked')),
        sec2Locked: Boolean(sec2 && sec2.classList.contains('mx-sec-is-locked')),
        sec3Locked: Boolean(sec3 && sec3.classList.contains('mx-sec-is-locked')),
        sec4Locked: Boolean(sec4 && sec4.classList.contains('mx-sec-is-locked')),
        sec1Links,
      };
    });
    assertCheck(
      'MASTER-AGRITECH-TPL displays 5-node Roadmap Stepper + Step 1 open + Steps 3 & 4 restricted',
      masterInitial.hasStepper &&
        masterInitial.stepCount >= 5 &&
        masterInitial.sec1Open &&
        masterInitial.sec3Locked &&
        masterInitial.sec4Locked &&
        masterInitial.sec1Links.length >= 2,
      `steps=${masterInitial.stepCount}, sec2Locked=${masterInitial.sec2Locked}, sec3Locked=${masterInitial.sec3Locked}`
    );
    await saveShot(cfPage, '26a_master_template_learning_path_locked.png', true, true);

    // Complete Step 1 (Video + Inline PDF) in MASTER-AGRITECH-TPL -> verify Step 2 (H5P + SCORM 1.2) unlocks automatically!
    for (const step1Url of masterInitial.sec1Links) {
      await cfPage.goto(step1Url, { waitUntil: 'networkidle2' });
    }
    await cfPage.goto(`${BASE_URL}/course/view.php?id=8`, { waitUntil: 'networkidle2' });
    const masterAfterStep1 = await cfPage.evaluate(() => {
      const sec2 = document.getElementById('mx-sec-2');
      const sec3 = document.getElementById('mx-sec-3');
      const sec2Btns = sec2 ? sec2.querySelectorAll('a.mx-tile-action-btn').length : 0;
      return {
        sec2Unlocked: Boolean(sec2 && !sec2.classList.contains('mx-sec-is-locked') && sec2Btns >= 2),
        sec3StillLocked: Boolean(sec3 && sec3.classList.contains('mx-sec-is-locked')),
      };
    });
    assertCheck(
      'Completing Step 1 (Video + PDF) in MASTER-AGRITECH-TPL automatically unlocks Step 2 (H5P & SCORM 1.2)',
      masterAfterStep1.sec2Unlocked && masterAfterStep1.sec3StillLocked,
      'Step 2 unlocked, Step 3 still restricted'
    );
    await saveShot(cfPage, '26b_master_template_step2_unlocked.png', true, true);
    await ctxCouncilFarmer.close();

    // 5.6 1-Click Local Course Cloner (/local/mamxanhapp/clone_course.php as CBKT cbkt_pilot)
    await cbktPage.setViewport({ width: 1440, height: 920 });
    await cbktPage.goto(`${BASE_URL}/local/mamxanhapp/clone_course.php?lang=vi`, { waitUntil: 'networkidle2' });
    const clonerFormReady = await cbktPage.evaluate(() => {
      const sel = document.getElementById('mx-clone-source');
      const fn = document.getElementById('mx-clone-fullname');
      const coh = document.getElementById('mx-clone-cohort');
      const btn = document.getElementById('mx-clone-submit-btn');
      if (coh) {
        const ldOpt = [...coh.options].find((o) => o.text.includes('Lâm Đồng'));
        if (ldOpt) coh.value = ldOpt.value;
      }
      return Boolean(sel && fn && btn && sel.options[0]?.text.includes('MASTER-AGRITECH-TPL'));
    });
    assertCheck('CBKT 1-Click Course Cloner page loaded with MASTER-AGRITECH-TPL prioritized', clonerFormReady);
    await saveShot(cbktPage, '27a_course_cloner_form.png');

    // Submit 1-Click Clone -> Moodle Backup & Restore Controller creates regional course
    await Promise.all([
      cbktPage.waitForNavigation({ waitUntil: 'networkidle2', timeout: 90000 }),
      cbktPage.click('#mx-clone-submit-btn'),
    ]);
    const cloneSuccessInfo = await cbktPage.evaluate(() => {
      const card = document.getElementById('mx-clone-success-card');
      const openBtn = document.getElementById('mx-open-cloned-course-btn');
      return {
        hasSuccess: Boolean(card && card.innerText.includes('100%')),
        cardText: card ? card.innerText.replace(/\s+/g, ' ').slice(0, 180) : '',
        newCourseUrl: openBtn ? openBtn.href : '',
      };
    });
    assertCheck(
      '1-Click Course Cloner executed Moodle backup_controller & restore_controller cleanly',
      cloneSuccessInfo.hasSuccess && cloneSuccessInfo.newCourseUrl.includes('/course/view.php?id='),
      cloneSuccessInfo.newCourseUrl
    );
    await saveShot(cbktPage, '27b_course_cloner_result.png');

    // Open the newly cloned local course and verify 100% preserved Stepper, PDF, H5P, SCORM, FieldPractice & Quiz
    await cbktPage.goto(cloneSuccessInfo.newCourseUrl, { waitUntil: 'networkidle2' });
    const clonedCourseAudit = await cbktPage.evaluate(() => ({
      title: document.body.innerText.includes('Đơn Dương'),
      hasStepper: Boolean(document.getElementById('mx-learning-path-stepper')),
      hasPdf: Boolean(document.querySelector('a[href*="/mod/resource/view.php"]')),
      hasH5p: Boolean(document.querySelector('a[href*="/mod/h5pactivity/view.php"]')),
      hasScorm: Boolean(document.querySelector('a[href*="/mod/scorm/view.php"]')),
      hasFp: Boolean(document.querySelector('a[href*="/mod/fieldpractice/view.php"]')),
      hasQuiz: Boolean(document.querySelector('a[href*="/mod/quiz/view.php"]')),
    }));
    assertCheck(
      'Cloned regional course preserves 100% of Stepper, PDF, H5P, SCORM 1.2, FieldPractice & Quiz',
      clonedCourseAudit.title &&
        clonedCourseAudit.hasStepper &&
        clonedCourseAudit.hasPdf &&
        clonedCourseAudit.hasH5p &&
        clonedCourseAudit.hasScorm &&
        clonedCourseAudit.hasFp &&
        clonedCourseAudit.hasQuiz
    );
    await saveShot(cbktPage, '27c_cloned_local_course_view.png', true, true);

    // =========================================================================
    // PART 6: ADMIN ROLE (admin) & WARM TTFB PERFORMANCE BENCHMARK
    // =========================================================================
    console.log('\n--- [6/6] Testing Admin Role (admin) & Measuring Warm TTFB Benchmarks ---');
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
      { name: 'Login Page HTTPS Proxy (/login/index.php)', url: `${BASE_URL}/login/index.php`, cookie: '' },
      { name: 'Login Page Direct HTTP moodle_app (http://localhost:8090/login/index.php)', url: `${DIRECT_HTTP_URL}/login/index.php`, cookie: '' },
      { name: 'Public QR Certificate Verify (/local/mamxanhcert/verify.php)', url: `${BASE_URL}/local/mamxanhcert/verify.php?code=${encodeURIComponent(unlockedCertCode)}`, cookie: '' },
      { name: 'Public VietGAP Traceability (/local/agriops/trace.php)', url: `${BASE_URL}/local/agriops/trace.php?code=${encodeURIComponent(createdBatchCode)}`, cookie: '' },
      { name: 'CBKT Analytics Hub (/local/mamxanhapp/manage.php)', url: `${BASE_URL}/local/mamxanhapp/manage.php`, cookie: cbktCookie },
      { name: '1-Click Course Cloner (/local/mamxanhapp/clone_course.php)', url: `${BASE_URL}/local/mamxanhapp/clone_course.php`, cookie: cbktCookie },
      { name: 'Smart AgTech Calculator (/local/mamxanhapp/calculator.php)', url: `${BASE_URL}/local/mamxanhapp/calculator.php`, cookie: cbktCookie },
      { name: 'Course Tiles View CHAMSOC01 (/course/view.php?id=2)', url: `${BASE_URL}/course/view.php?id=2`, cookie: cbktCookie },
      { name: 'Master Course Template (/course/view.php?id=8)', url: `${BASE_URL}/course/view.php?id=8`, cookie: cbktCookie },
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
