const { assertCleanUtf8 } = require('./_supabase');

const DEFAULT_RESEND_URL = 'https://api.resend.com/emails';
const DEFAULT_MAIL_FROM = 'Mầm Xanh LMS 2.0 <onboarding@resend.dev>';
const DEFAULT_PORTAL_URL = 'https://mam-xanh-lms-2-0.vercel.app';
const MAIL_TIMEOUT_MS = 4000;

const STARTER_COURSE_SUMMARIES = [
  {
    code: 'CHAMSOC01',
    title: 'Kỹ thuật Canh tác Nông nghiệp Công nghệ cao & Dinh dưỡng Tự động',
  },
  {
    code: 'IOTNHAMANG01',
    title: 'Vận hành Nhà màng Thông minh & Trạm Cảm biến Vi khí hậu IoT',
  },
  {
    code: 'DAUTAY-CNC-01',
    title: 'Kỹ thuật Canh tác Dâu tây & Hoa Cúc Công nghệ cao trong Nhà màng Đà Lạt',
  },
];

function escapeHtml(val) {
  return assertCleanUtf8(String(val ?? ''))
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function wrapBotanicalEmailShell({ preheaderTitle, bodyHtml, ctaLabel, ctaUrl }) {
  return assertCleanUtf8(`<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>${escapeHtml(preheaderTitle)}</title>
</head>
<body style="margin:0;padding:0;background-color:#F9FAF9;font-family:'Be Vietnam Pro',Arial,Helvetica,sans-serif;color:#1C2522;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F9FAF9;padding:28px 14px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;background-color:#FFFFFF;border:1px solid #E6ECE9;border-radius:14px;overflow:hidden;">
          <tr>
            <td style="background-color:#234E3E;padding:22px 28px;">
              <div style="font-size:12px;letter-spacing:0.06em;text-transform:uppercase;color:#CFE2DA;font-weight:700;margin-bottom:4px;">
                KHOA CNTT • TRƯỜNG ĐẠI HỌC ĐÀ LẠT
              </div>
              <div style="font-size:20px;font-weight:700;color:#FFFFFF;line-height:1.35;">
                Mầm Xanh LMS 2.0 — Nền tảng Tập huấn Nông nghiệp Công nghệ cao
              </div>
            </td>
          </tr>
          <tr>
            <td style="padding:26px 28px;">
              ${bodyHtml}
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:22px auto 0;">
                <tr>
                  <td align="center" style="border-radius:8px;background-color:#234E3E;">
                    <a href="${escapeHtml(ctaUrl)}" target="_blank" rel="noopener" style="display:inline-block;padding:13px 24px;font-size:14px;font-weight:700;color:#FFFFFF;text-decoration:none;border-radius:8px;">
                      ${escapeHtml(ctaLabel)}
                    </a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:16px 28px;border-top:1px solid #E6ECE9;background-color:#F9FAF9;font-size:12px;line-height:1.6;color:#5C6864;text-align:center;">
              Mầm Xanh LMS 2.0 • Đồ án Tốt nghiệp Khoa Công nghệ Thông tin, Trường Đại học Đà Lạt (2026)<br />
              Email được gửi tự động từ Serverless Mailer. Vui lòng không trả lời thư này.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>`);
}

function buildWelcomeEmailHtml({ full_name, username, email, auth_method } = {}) {
  const safeName = escapeHtml(full_name || username || 'Học viên Mầm Xanh');
  const safeUsername = escapeHtml(username || '');
  const safeEmail = escapeHtml(email || '');
  const safeMethod = escapeHtml(auth_method || 'Form Đăng ký Trực tuyến');
  const portalUrl = String(process.env.APP_BASE_URL || DEFAULT_PORTAL_URL).trim();

  const coursesListHtml = STARTER_COURSE_SUMMARIES.map(
    c =>
      `<li style="margin:0 0 8px 0;color:#33403C;font-size:14px;line-height:1.6;">` +
      `<strong style="color:#234E3E;">[${escapeHtml(c.code)}]</strong> ${escapeHtml(c.title)}` +
      `</li>`
  ).join('');

  const bodyHtml = `
    <p style="margin:0 0 14px 0;font-size:16px;font-weight:700;color:#1C2522;">
      Xin chào ${safeName},
    </p>
    <p style="margin:0 0 16px 0;font-size:14px;line-height:1.65;color:#33403C;">
      Chúc mừng bạn đã đăng ký thành công tài khoản trên hệ thống <strong>Mầm Xanh LMS 2.0 (Cloud Edition)</strong>. Tài khoản học viên của bạn đã được kích hoạt trên cơ sở dữ liệu Supabase PostgreSQL và sẵn sàng học tập ngay.
    </p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F9FAF9;border:1px solid #E6ECE9;border-radius:10px;margin:0 0 18px 0;">
      <tr>
        <td style="padding:14px 18px;">
          <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#234E3E;margin-bottom:8px;">
            Thông tin Tài khoản đã kích hoạt
          </div>
          <p style="margin:0 0 6px 0;font-size:14px;color:#33403C;"><strong>Tên đăng nhập:</strong> <code>${safeUsername}</code></p>
          <p style="margin:0 0 6px 0;font-size:14px;color:#33403C;"><strong>Email:</strong> ${safeEmail}</p>
          <p style="margin:0 0 6px 0;font-size:14px;color:#33403C;"><strong>Phương thức đăng ký:</strong> ${safeMethod}</p>
          <p style="margin:0;font-size:14px;color:#33403C;"><strong>Vai trò:</strong> Học viên / Nông hộ (Student)</p>
        </td>
      </tr>
    </table>
    <div style="background-color:#EAF2EE;border:1px solid #CFE2DA;border-radius:10px;padding:16px 18px;margin:0 0 18px 0;">
      <div style="font-size:13px;font-weight:700;color:#234E3E;margin-bottom:8px;">
        03 Học phần Nền tảng đã tự động ghi danh sẵn cho bạn:
      </div>
      <ul style="margin:0;padding-left:18px;">
        ${coursesListHtml}
      </ul>
    </div>
    <div style="margin:0;padding:14px 16px;border-left:3px solid #234E3E;background-color:#F9FAF9;">
      <div style="font-size:13px;font-weight:700;color:#1C2522;margin-bottom:6px;">
        Gợi ý trải nghiệm các phân hệ cốt lõi:
      </div>
      <p style="margin:0 0 6px 0;font-size:13px;line-height:1.6;color:#33403C;">
        1. <strong>Tra cứu Chứng nhận số QR (HMAC-SHA256):</strong> Kiểm chứng chữ ký mật mã học và thử kịch bản phát hiện giả mạo điểm số (Tamper Test) ngay trên trang chủ.
      </p>
      <p style="margin:0;font-size:13px;line-height:1.6;color:#33403C;">
        2. <strong>Sổ Nhật ký Canh tác VietGAP &amp; Hồ sơ:</strong> Ghi chép nhật ký pH/EC đồng ruộng, theo dõi thời gian cách ly PHI và cập nhật thông tin trang trại của bạn trong tab <em>Hồ sơ &amp; Đổi mật khẩu</em>.
      </p>
    </div>
  `;

  return wrapBotanicalEmailShell({
    preheaderTitle: 'Chào mừng đến với Mầm Xanh LMS 2.0',
    bodyHtml,
    ctaLabel: 'Truy cập Bảng Điều khiển Học tập →',
    ctaUrl: portalUrl,
  });
}

function buildWelcomeEmailText({ full_name, username, email, auth_method } = {}) {
  const name = assertCleanUtf8(String(full_name || username || 'Học viên Mầm Xanh'));
  const portalUrl = String(process.env.APP_BASE_URL || DEFAULT_PORTAL_URL).trim();
  const courses = STARTER_COURSE_SUMMARIES.map(c => `- [${c.code}] ${c.title}`).join('\n');
  return assertCleanUtf8(
    `Xin chào ${name},\n\n` +
      `Chúc mừng bạn đã đăng ký thành công tài khoản trên Mầm Xanh LMS 2.0.\n` +
      `- Tên đăng nhập: ${username || ''}\n` +
      `- Email: ${email || ''}\n` +
      `- Phương thức: ${auth_method || 'Form Đăng ký Trực tuyến'}\n\n` +
      `03 Học phần Nền tảng đã tự động ghi danh:\n${courses}\n\n` +
      `Truy cập Bảng Điều khiển Học tập tại:\n${portalUrl}\n`
  );
}

function buildCertIssuedEmailHtml({
  full_name,
  username,
  course_code,
  course_name,
  cert_code,
  final_score,
  hmac_signature,
} = {}) {
  const safeName = escapeHtml(full_name || username || 'Học viên Mầm Xanh');
  const safeCourse = escapeHtml(`[${course_code || ''}] ${course_name || ''}`);
  const safeCert = escapeHtml(cert_code || '');
  const safeScore = escapeHtml(Number(final_score || 0).toFixed(2));
  const safeHmac = escapeHtml(hmac_signature || '');
  const basePortal = String(process.env.APP_BASE_URL || DEFAULT_PORTAL_URL).trim().replace(/\/+$/, '');
  const verifyUrl = `${basePortal}/?verify=${encodeURIComponent(String(cert_code || ''))}`;

  const bodyHtml = `
    <p style="margin:0 0 14px 0;font-size:16px;font-weight:700;color:#1C2522;">
      Xin chào ${safeName},
    </p>
    <p style="margin:0 0 16px 0;font-size:14px;line-height:1.65;color:#33403C;">
      Chúc mừng bạn đã hoàn thành xuất sắc chương trình tập huấn <strong>${safeCourse}</strong> (đạt chuẩn cả bài thi Trắc nghiệm Quiz lẫn Thực hành hiện trường Rubric 4 tiêu chí) và được cấp <strong>Chứng nhận Số QR HMAC-SHA256</strong> chính thức.
    </p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#EAF2EE;border:1px solid #CFE2DA;border-radius:10px;margin:0 0 18px 0;">
      <tr>
        <td style="padding:16px 18px;">
          <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#234E3E;margin-bottom:8px;">
            Chi tiết Chứng nhận Điện tử Đã Ký số
          </div>
          <p style="margin:0 0 6px 0;font-size:14px;color:#1C2522;"><strong>Mã chứng nhận:</strong> <code>${safeCert}</code></p>
          <p style="margin:0 0 6px 0;font-size:14px;color:#1C2522;"><strong>Học phần:</strong> ${safeCourse}</p>
          <p style="margin:0 0 6px 0;font-size:14px;color:#1C2522;"><strong>Điểm tổng kết:</strong> <strong>${safeScore}%</strong></p>
          <p style="margin:0;font-size:12px;color:#33403C;word-break:break-all;"><strong>Chữ ký số HMAC-SHA256:</strong> <code>${safeHmac}</code></p>
        </td>
      </tr>
    </table>
    <p style="margin:0;font-size:13px;line-height:1.6;color:#33403C;">
      Doanh nghiệp bao tiêu nông sản, Hợp tác xã hoặc cơ quan kiểm định có thể bấm trực tiếp nút bên dưới hoặc quét mã QR trên phôi chứng nhận để đối chiếu chữ ký mật mã học thời gian thực.
    </p>
  `;

  return wrapBotanicalEmailShell({
    preheaderTitle: `Cấp Chứng nhận Số ${cert_code || ''} — Mầm Xanh LMS 2.0`,
    bodyHtml,
    ctaLabel: 'Kiểm tra Chứng nhận QR Trực tuyến →',
    ctaUrl: verifyUrl,
  });
}

function buildCertIssuedEmailText({ full_name, course_code, course_name, cert_code, final_score } = {}) {
  const name = assertCleanUtf8(String(full_name || 'Học viên Mầm Xanh'));
  const basePortal = String(process.env.APP_BASE_URL || DEFAULT_PORTAL_URL).trim().replace(/\/+$/, '');
  const verifyUrl = `${basePortal}/?verify=${encodeURIComponent(String(cert_code || ''))}`;
  return assertCleanUtf8(
    `Xin chào ${name},\n\n` +
      `Chúc mừng bạn đã được cấp Chứng nhận Số QR HMAC-SHA256 cho học phần [${course_code || ''}] ${course_name || ''}.\n` +
      `- Mã chứng nhận: ${cert_code || ''}\n` +
      `- Điểm tổng kết: ${Number(final_score || 0).toFixed(2)}%\n\n` +
      `Link tra cứu xác thực công khai:\n${verifyUrl}\n`
  );
}

async function sendResendEmail({ to, subject, html, text }) {
  const cleanEmail = assertCleanUtf8(String(to || '').trim().toLowerCase());
  const apiKey = String(process.env.RESEND_API_KEY || '').trim();
  if (!apiKey) {
    return { sent: false, provider: 'resend', reason: 'MISSING_RESEND_API_KEY' };
  }

  if (!cleanEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cleanEmail) || cleanEmail.endsWith('.local')) {
    return { sent: false, provider: 'resend', reason: 'UNROUTABLE_EMAIL' };
  }

  const from = assertCleanUtf8(String(process.env.MAIL_FROM || DEFAULT_MAIL_FROM).trim());
  const resendUrl = String(process.env.RESEND_API_URL || DEFAULT_RESEND_URL).trim();

  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), MAIL_TIMEOUT_MS);

  try {
    const res = await fetch(resendUrl, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${apiKey}`,
        'Content-Type': 'application/json; charset=utf-8',
        Accept: 'application/json; charset=utf-8',
      },
      body: JSON.stringify({
        from,
        to: [cleanEmail],
        subject: assertCleanUtf8(subject),
        html: assertCleanUtf8(html),
        text: assertCleanUtf8(text),
      }),
      signal: controller.signal,
    });

    const rawText = await res.text();
    let json = null;
    try {
      json = rawText ? JSON.parse(rawText) : null;
    } catch {
      json = null;
    }

    if (!res.ok) {
      const msg = String((json && json.message) || rawText || '');
      if (res.status === 403 && /testing emails|verify a domain|own email/i.test(msg)) {
        return {
          sent: false,
          provider: 'resend',
          reason: 'RESEND_SANDBOX_DOMAIN_REQUIRED',
          detail_vi: 'Resend đang chạy chế độ sandbox (onboarding@resend.dev), chỉ gửi được tới email chủ tài khoản Resend cho đến khi xác minh domain riêng cho MAIL_FROM.',
        };
      }
      return {
        sent: false,
        provider: 'resend',
        reason: `RESEND_HTTP_${res.status}`,
      };
    }

    return {
      sent: true,
      provider: 'resend',
      id: json && json.id ? String(json.id) : undefined,
    };
  } catch (err) {
    return {
      sent: false,
      provider: 'resend',
      reason: err && err.name === 'AbortError' ? 'TIMEOUT_4000MS' : 'NETWORK_ERROR',
    };
  } finally {
    clearTimeout(timer);
  }
}

async function sendWelcomeEmail({ full_name, username, email, auth_method } = {}) {
  const cleanName = assertCleanUtf8(String(full_name || username || '').trim());
  const cleanUsername = assertCleanUtf8(String(username || '').trim());
  const cleanEmail = assertCleanUtf8(String(email || '').trim().toLowerCase());
  const cleanMethod = assertCleanUtf8(String(auth_method || 'Form Đăng ký Trực tuyến').trim());

  const subject = `Chào mừng ${cleanName || cleanUsername} đến với Mầm Xanh LMS 2.0`;
  const html = buildWelcomeEmailHtml({
    full_name: cleanName,
    username: cleanUsername,
    email: cleanEmail,
    auth_method: cleanMethod,
  });
  const text = buildWelcomeEmailText({
    full_name: cleanName,
    username: cleanUsername,
    email: cleanEmail,
    auth_method: cleanMethod,
  });
  return sendResendEmail({ to: cleanEmail, subject, html, text });
}

async function sendCertIssuedEmail({
  full_name,
  username,
  email,
  course_code,
  course_name,
  cert_code,
  final_score,
  hmac_signature,
} = {}) {
  const cleanEmail = assertCleanUtf8(String(email || '').trim().toLowerCase());
  const subject = `Chứng nhận Số QR [${course_code || ''}] (${cert_code || ''}) — Mầm Xanh LMS 2.0`;
  const html = buildCertIssuedEmailHtml({
    full_name,
    username,
    course_code,
    course_name,
    cert_code,
    final_score,
    hmac_signature,
  });
  const text = buildCertIssuedEmailText({
    full_name,
    course_code,
    course_name,
    cert_code,
    final_score,
  });
  return sendResendEmail({ to: cleanEmail, subject, html, text });
}

module.exports = {
  buildWelcomeEmailHtml,
  buildWelcomeEmailText,
  buildCertIssuedEmailHtml,
  buildCertIssuedEmailText,
  sendWelcomeEmail,
  sendCertIssuedEmail,
};
