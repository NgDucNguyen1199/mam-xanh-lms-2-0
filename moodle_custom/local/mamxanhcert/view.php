<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/local/mamxanhapp/lib.php');

local_mamxanhapp_handle_lang_switch();
require_login();

$code = required_param('code', PARAM_RAW_TRIMMED);
$download = optional_param('download', '', PARAM_ALPHA);

$cert = $DB->get_record('local_mamxanhcert', ['cert_code' => $code], '*', MUST_EXIST);
$syscontext = context_system::instance();

if ((int)$cert->userid !== (int)$USER->id && !is_siteadmin() && !has_capability('local/mamxanhcert:manage', $syscontext)) {
    local_mamxanhapp_send_403_and_exit();
}

$learner = $DB->get_record('user', ['id' => $cert->userid], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cert->courseid], '*', MUST_EXIST);
$verifyurl = (new moodle_url('/local/mamxanhcert/verify.php', ['code' => $cert->cert_code]))->out(false);
$isvalid = local_mamxanhcert_verify_record($cert);

// ===================================================================
// REAL BINARY PDF EXPORT VIA MOODLE TCPDF (freesans Vietnamese Unicode)
// ===================================================================
if ($download === 'pdf') {
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    require_once($CFG->libdir . '/pdflib.php');
    $pdf = new pdf('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(false, 10);
    $pdf->AddPage();

    // Decorative green border
    $pdf->SetLineStyle(['width' => 2.2, 'color' => [27, 67, 50]]);
    $pdf->Rect(10, 10, 277, 190);
    $pdf->SetLineStyle(['width' => 0.6, 'color' => [46, 125, 50]]);
    $pdf->Rect(13, 13, 271, 184);

    $pdf->SetFont('freesans', 'B', 14);
    $pdf->SetTextColor(46, 125, 50);
    $pdf->SetXY(15, 24);
    $pdf->Cell(267, 8, 'HỆ THỐNG ĐÀO TẠO NÔNG NGHIỆP CÔNG NGHỆ CAO - MẦM XANH LMS 2.0', 0, 1, 'C');

    $pdf->SetFont('freesans', 'B', 26);
    $pdf->SetTextColor(27, 67, 50);
    $pdf->Cell(267, 15, 'CHỨNG NHẬN HOÀN THÀNH KHÓA HỌC', 0, 1, 'C');

    $pdf->SetFont('freesans', '', 12);
    $pdf->SetTextColor(70, 90, 80);
    $pdf->Cell(267, 8, 'CERTIFICATE OF AGRICULTURAL EXCELLENCE (VIETGAP DIGITAL STANDARD)', 0, 1, 'C');

    $pdf->Ln(5);
    $pdf->SetFont('freesans', '', 13);
    $pdf->Cell(267, 8, 'Trân trọng chứng nhận học viên / This is to certify that:', 0, 1, 'C');

    $pdf->SetFont('freesans', 'B', 22);
    $pdf->SetTextColor(20, 40, 29);
    $pdf->Cell(267, 13, fullname($learner), 0, 1, 'C');

    $pdf->SetFont('freesans', '', 13);
    $pdf->SetTextColor(60, 80, 70);
    $pdf->Cell(267, 8, 'Đã hoàn thành xuất sắc chương trình tập huấn kỹ thuật số hóa:', 0, 1, 'C');

    $pdf->SetFont('freesans', 'B', 16);
    $pdf->SetTextColor(46, 125, 50);
    $pdf->MultiCell(240, 9, format_string($course->fullname), 0, 'C', false, 1, 28);

    $pdf->Ln(3);
    $pdf->SetFont('freesans', 'B', 13);
    $pdf->SetTextColor(27, 67, 50);
    $pdf->Cell(267, 8, 'Kết quả đánh giá: ' . number_format((float)$cert->final_score, 1) . '%  |  Mã số: ' . $cert->cert_code, 0, 1, 'C');

    $pdf->SetFont('freesans', '', 9);
    $pdf->SetTextColor(90, 105, 95);
    $pdf->SetXY(22, 154);
    $pdf->MultiCell(195, 5, "Ngày cấp: " . date('d/m/Y H:i', (int)$cert->timecreated) . "\nChữ ký số HMAC-SHA256: " . $cert->hmac_signature . "\nXác thực trực tuyến: " . $verifyurl, 0, 'L');

    // Native 2D QR Barcode
    $style = ['border' => 0, 'padding' => 1, 'fgcolor' => [27, 67, 50], 'bgcolor' => [255, 255, 255]];
    $pdf->write2DBarcode($verifyurl, 'QRCODE,M', 235, 142, 38, 38, $style, 'N');

    $pdf->Output($cert->cert_code . '.pdf', 'D');
    exit;
}

$PAGE->set_context($syscontext);
$PAGE->set_url(new moodle_url('/local/mamxanhcert/view.php', ['code' => $cert->cert_code]));
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Chứng nhận ' . $cert->cert_code);
$PAGE->set_heading('📜 ' . $cert->cert_code);

$qrsvg = \local_mamxanhapp\qr_helper::render_svg($verifyurl, 150, '#1B4332');

echo $OUTPUT->header();
?>

<div style="max-width:920px;margin:0 auto;">
    <div class="d-flex flex-wrap justify-content-between mb-3" style="gap:10px;">
        <a href="<?php echo (new moodle_url('/local/mamxanhcert/index.php'))->out(false); ?>" class="btn btn-outline-success" style="min-height:48px;display:inline-flex;align-items:center;border-radius:14px;font-weight:700;">
            ← <?php echo s(local_mamxanhapp_t('Danh sách Chứng nhận', 'All Certificates')); ?>
        </a>
        <div class="d-flex flex-wrap" style="gap:10px;">
            <a href="<?php echo (new moodle_url('/local/mamxanhcert/view.php', ['code' => $cert->cert_code, 'download' => 'pdf']))->out(false); ?>" class="btn btn-success" style="min-height:48px;display:inline-flex;align-items:center;border-radius:14px;font-weight:800;background:#1B4332;">
                ⬇️ <?php echo s(local_mamxanhapp_t('Tải Bản PDF Ký Số', 'Download Signed PDF')); ?>
            </a>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary" style="min-height:48px;border-radius:14px;font-weight:700;">
                🖨️ <?php echo s(local_mamxanhapp_t('In Chứng nhận', 'Print')); ?>
            </button>
        </div>
    </div>

    <article style="background:linear-gradient(180deg,#FFFFFF 0%,#F7FCF8 100%);border:6px double #1B4332;border-radius:24px;padding:36px 28px;text-align:center;box-shadow:0 16px 40px rgba(27,67,50,0.14);">
        <div style="color:#2E7D32;font-weight:800;letter-spacing:0.08em;font-size:0.92rem;margin-bottom:6px;">
            🌱 HỆ THỐNG TẬP HUẤN NÔNG NGHIỆP CÔNG NGHỆ CAO • MẦM XANH LMS 2.0
        </div>
        <h2 style="color:#1B4332;font-weight:900;font-size:2rem;margin-bottom:6px;">
            <?php echo s(local_mamxanhapp_t('CHỨNG NHẬN HOÀN THÀNH KHÓA HỌC', 'CERTIFICATE OF COMPLETION')); ?>
        </h2>
        <p style="color:#546E7A;margin-bottom:20px;"><?php echo s(local_mamxanhapp_t('Trân trọng trao tặng học viên nông nghiệp số:', 'Proudly presented to digital agriculture practitioner:')); ?></p>

        <div style="font-size:1.95rem;font-weight:900;color:#14281D;border-bottom:2px solid #A5D6A7;display:inline-block;padding:0 28px 6px;margin-bottom:18px;">
            <?php echo s(fullname($learner)); ?>
        </div>

        <p style="color:#455A64;margin-bottom:6px;"><?php echo s(local_mamxanhapp_t('Đã hoàn thành xuất sắc chương trình tập huấn kỹ thuật:', 'For successfully completing the interactive technical training course:')); ?></p>
        <h3 style="color:#2E7D32;font-weight:800;font-size:1.4rem;margin-bottom:16px;">
            <?php echo format_string($course->fullname); ?>
        </h3>

        <div style="display:inline-flex;flex-wrap:wrap;justify-content:center;gap:14px;background:#E8F5E9;padding:10px 22px;border-radius:999px;font-weight:800;color:#1B4332;margin-bottom:24px;">
            <span>🎯 <?php echo s(local_mamxanhapp_t('Điểm tổng kết: ', 'Final Score: ') . number_format((float)$cert->final_score, 1) . '%'); ?></span>
            <span>•</span>
            <span>📅 <?php echo date('d/m/Y', (int)$cert->timecreated); ?></span>
            <span>•</span>
            <span>🆔 <?php echo s($cert->cert_code); ?></span>
        </div>

        <div class="row align-items-center text-start" style="background:#FFF;border:1.5px solid #C8E6C9;border-radius:18px;padding:16px;margin:0;">
            <div class="col-md-8 mb-3 mb-md-0">
                <div style="font-weight:800;color:#1B4332;margin-bottom:4px;">
                    <?php echo $isvalid ? '✅ CHỮ KÝ SỐ HỢP LỆ (HMAC-SHA256 VERIFIED)' : '⚠️ INVALID SIGNATURE'; ?>
                </div>
                <div style="font-size:0.8rem;word-break:break-all;color:#455A64;">
                    <strong>HMAC-SHA256 Digest:</strong> <code><?php echo s($cert->hmac_signature); ?></code>
                </div>
                <div style="font-size:0.84rem;margin-top:6px;">
                    <a href="<?php echo $verifyurl; ?>" style="color:#2E7D32;font-weight:700;"><?php echo s($verifyurl); ?></a>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <?php echo $qrsvg; ?>
            </div>
        </div>
    </article>
</div>

<?php
echo $OUTPUT->footer();
