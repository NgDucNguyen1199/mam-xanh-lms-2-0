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

function buildWelcomeEmailHtml({ full_name, username, email, auth_method } = {}) {
  const safeName = escapeHtml(full_name || username || 'Học viên Mầm Xanh');
  const safeUsername = escapeHtml(username || '');
  const safeEmail = escapeHtml(email || '');
  const safeMethod = escapeHtml(auth_method || 'Form Đăng ký Trực tuyến');
  const portalUrl = escapeHtml(String(process.env.APP_BASE_URL || DEFAULT_PORTAL_URL).trim());

  const coursesListHtml = STARTER_COURSE_SUMMARIES.map(
    c =>
      `<li style="margin:0 0 8px 0;color:#33403C;font-size:14px;line-height:1.6;">` +
      `<strong style="color:#234E3E;">[${escapeHtml(c.code)}]</strong> ${escapeHtml(c.title)}` +
      `</li>`
  ).join('');

  return assertCleanUtf8(`<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Chào mừng đến với Mầm Xanh LMS 2.0</title>
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

              <div style="margin:0 0 22px 0;padding:14px 16px;border-left:3px solid #234E3E;background-color:#F9FAF9;">
                <div style="font-size:13px;font-weight:700;color:#1C2522;margin-bottom:6px;">
                  Gợi ý trải nghiệm các phân hệ cốt lõi:
                </div>
                <p style="margin:0 0 6px 0;font-size:13px;line-height:1.6;color:#33403C;">
                  1. <strong>Tra cứu Chứng nhận số QR (HMAC-SHA256):</strong> Kiểm chứng chữ ký mật mã học và thử kịch bản phát hiện giả mạo điểm số (Tamper Test) ngay trên trang chủ.
                </p>
                <p style="margin:0;font-size:13px;line-height:1.6;color:#33403C;">
                  2. <strong>Sổ Nhật ký Canh tác VietGAP &amp; Hồ sơ:</strong> Ghi chép nhật ký pH/EC đồng ruộng, xuất Hộ chiếu nông sản QR và bổ sung thông tin trang trại của bạn trong tab <em>Hồ sơ &amp; Đổi mật khẩu</em>.
                </p>
              </div>

              <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto;">
                <tr>
                  <td align="center" style="border-radius:8px;background-color:#234E3E;">
                    <a href="${portalUrl}" target="_blank" rel="noopener" style="display:inline-block;padding:13px 24px;font-size:14px;font-weight:700;color:#FFFFFF;text-decoration:none;border-radius:8px;">
                      Truy cập Bảng Điều khiển Học tập →
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
      `Bạn có thể tra cứu Chứng nhận số QR (HMAC-SHA256), ghi Sổ Nhật ký VietGAP và cập nhật thông tin trang trại trong tab Hồ sơ tại:\n${portalUrl}\n`
  );
}

async function sendWelcomeEmail({ full_name, username, email, auth_method } = {}) {
  const cleanEmail = assertCleanUtf8(String(email || '').trim().toLowerCase());
  const cleanName = assertCleanUtf8(String(full_name || username || '').trim());
  const cleanUsername = assertCleanUtf8(String(username || '').trim());
  const cleanMethod = assertCleanUtf8(String(auth_method || 'Form Đăng ký Trực tuyến').trim());

  const apiKey = String(process.env.RESEND_API_KEY || '').trim();
  if (!apiKey) {
    return { sent: false, provider: 'resend', reason: 'MISSING_RESEND_API_KEY' };
  }

  if (!cleanEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cleanEmail) || cleanEmail.endsWith('.local')) {
    return { sent: false, provider: 'resend', reason: 'UNROUTABLE_EMAIL' };
  }

  const from = assertCleanUtf8(String(process.env.MAIL_FROM || DEFAULT_MAIL_FROM).trim());
  const resendUrl = String(process.env.RESEND_API_URL || DEFAULT_RESEND_URL).trim();
  const subject = assertCleanUtf8(`Chào mừng ${cleanName || cleanUsername} đến với Mầm Xanh LMS 2.0`);
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
        subject,
        html,
        text,
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

module.exports = {
  buildWelcomeEmailHtml,
  buildWelcomeEmailText,
  sendWelcomeEmail,
};
