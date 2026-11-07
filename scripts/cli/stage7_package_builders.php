<?php
// ===================================================================
// Mầm Xanh LMS 2.0 - Stage 7 Package & Technical Document Builders
// (TCPDF Illustrated Vietnamese PDFs + Authentic H5P + ADL SCORM 1.2)
// ===================================================================
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/pdflib.php');
require_once($CFG->libdir . '/filelib.php');

/**
 * Generate 2 richly formatted multi-page Vietnamese Technical PDF Handbooks via TCPDF.
 */
function mamxanh_stage7_generate_technical_pdfs(string $outdir): array {
    if (!is_dir($outdir)) {
        mkdir($outdir, 0775, true);
    }

    $pdf1path = $outdir . '/So_Tay_Canh_Tac_Nong_Nghiep_CNC_VietGAP.pdf';
    $pdf2path = $outdir . '/Quy_Trinh_Bao_Quan_Che_Bien_Sau_Thu_Hoach.pdf';

    // ---------------------------------------------------------------
    // PDF 1: Sổ tay Kỹ thuật Canh tác Nông nghiệp Công nghệ cao & VietGAP
    // ---------------------------------------------------------------
    $pdf1 = new pdf('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf1->SetCreator('Mầm Xanh LMS 2.0');
    $pdf1->SetAuthor('Hội đồng Chuyên môn Mầm Xanh AgTech');
    $pdf1->SetTitle('Sổ tay Kỹ thuật Canh tác Nông nghiệp Công nghệ cao & VietGAP');
    $pdf1->setPrintHeader(false);
    $pdf1->setPrintFooter(false);
    $pdf1->SetMargins(15, 15, 15);
    $pdf1->SetAutoPageBreak(true, 16);
    $pdf1->SetFont('freesans', '', 11);

    // Page 1: Cover & Seedling + Substrate Standards
    $pdf1->AddPage();
    $pdf1->writeHTML('
        <div style="background-color:#1B4332;color:#FFFFFF;padding:18px;border-radius:8px;">
            <div style="font-size:10pt;color:#B7E4C7;font-weight:bold;">TÀI LIỆU KỸ THUẬT CHÍNH THỨC • MẦM XANH LMS 2.0 (CHAMSOC01)</div>
            <h1 style="font-size:18pt;color:#FFFFFF;margin:6px 0;">SỔ TAY KỸ THUẬT CANH TÁC NÔNG NGHIỆP CÔNG NGHỆ CAO &amp; TIÊU CHUẨN VIETGAP</h1>
            <div style="font-size:10pt;color:#D8F3DC;">Áp dụng cho vùng chuyên canh Lâm Đồng • Đắk Lắk • Đồng Nai | Xem trực tiếp trên Web</div>
        </div>
        <br>
        <h2 style="color:#1B4332;font-size:14pt;border-bottom:2px solid #2E7D32;">PHẦN 1. TIÊU CHUẨN CÂY GIỐNG F1 &amp; XỬ LÝ GIÁ THỂ SẠCH BỆNH</h2>
        <p style="line-height:1.6;">Cây giống khỏe mạnh quyết định <b>50% năng suất vụ mùa</b> trong nhà màng công nghệ cao. Trước khi xuống giống vào bầu giá thể xơ dừa hoặc máng thủy canh, nông hộ bắt buộc kiểm tra 4 chỉ tiêu kỹ thuật:</p>
        <table border="1" cellpadding="7" cellspacing="0" style="border-color:#A5D6A7;width:100%;">
            <tr style="background-color:#E8F5E9;color:#1B4332;font-weight:bold;">
                <th width="28%">Chỉ tiêu đánh giá</th>
                <th width="42%">Ngưỡng chuẩn xuất vườn</th>
                <th width="30%">Biện pháp loại trừ rủi ro</th>
            </tr>
            <tr>
                <td><b>Hình thái thân lá</b></td>
                <td>Cao 12–15 cm, thân mập thẳng, đốt ngắn, 4–5 lá thật xanh bóng</td>
                <td>Loại bỏ 100% cây dị dạng, xoăn ngọn nghi nhiễm virus</td>
            </tr>
            <tr>
                <td><b>Hệ rễ bầu ươm</b></td>
                <td>Rễ tơ trắng ngà ôm kín giá thể, không thối nâu cổ rễ</td>
                <td>Tưới phòng nấm đối kháng <i>Trichoderma</i> 48 giờ trước trồng</td>
            </tr>
            <tr>
                <td><b>Giá thể xơ dừa</b></td>
                <td>Đã xả chát Tanin &amp; Lignin; EC giá thể &lt; 0.5 mS/cm; pH 5.8–6.3</td>
                <td>Ngâm xả nước sạch và đệm Canxi Nitrat trước khi nạp bầu</td>
            </tr>
            <tr>
                <td><b>Huấn luyện cây con</b></td>
                <td>Giảm che nắng 3–5 ngày trước trồng để làm dày lớp biểu bì lá</td>
                <td>Treo bẫy dính vàng/xanh kiểm soát sạch bọ trĩ, bọ phấn</td>
            </tr>
        </table>
        <br>
        <h2 style="color:#1B4332;font-size:14pt;border-bottom:2px solid #2E7D32;">PHẦN 2. THANG ĐO DINH DƯỠNG pH / EC &amp; NGUYÊN TẮC PHA PHÂN A–B</h2>
        <p style="line-height:1.6;">Hệ thống tưới nhỏ giọt châm phân tự động (<i>Fertigation</i>) cung cấp chính xác nước và 12 nguyên tố khoáng thiết yếu tới từng gốc cây thông qua dây nhỏ giọt bù áp 2.0 L/giờ:</p>
        <table border="1" cellpadding="7" cellspacing="0" style="border-color:#A5D6A7;width:100%;">
            <tr style="background-color:#1B4332;color:#FFFFFF;font-weight:bold;">
                <th width="34%">Nhóm cây trồng chủ lực</th>
                <th width="22%">Ngưỡng pH tối ưu</th>
                <th width="24%">Ngưỡng EC (mS/cm)</th>
                <th width="20%">Tỷ lệ rỉ (Drainage)</th>
            </tr>
            <tr>
                <td>Rau ăn lá thủy canh NFT (Xà lách, Cải)</td>
                <td align="center"><b>5.8 – 6.2</b></td>
                <td align="center"><b>1.2 – 1.8</b></td>
                <td align="center">Tuần hoàn kín</td>
            </tr>
            <tr>
                <td>Dưa lưới / Cà chua nhà màng (Cây con)</td>
                <td align="center"><b>5.8 – 6.2</b></td>
                <td align="center"><b>1.5 – 2.0</b></td>
                <td align="center">15% – 20%</td>
            </tr>
            <tr>
                <td>Dưa lưới / Cà chua (Nuôi trái – Tạo ngọt)</td>
                <td align="center"><b>6.0 – 6.4</b></td>
                <td align="center"><b>2.2 – 2.8</b></td>
                <td align="center">20% – 30%</td>
            </tr>
            <tr>
                <td>Sầu riêng / Cây ăn quả kinh doanh</td>
                <td align="center"><b>5.8 – 6.5</b></td>
                <td align="center"><b>1.4 – 2.0</b></td>
                <td align="center">Độ ẩm đất 65–75%</td>
            </tr>
        </table>
    ', true, false, true, false, '');

    // Page 2: Stock A-B Mixing & Biological IPM + VietGAP Checklist
    $pdf1->AddPage();
    $pdf1->writeHTML('
        <div style="background-color:#FFF8E1;border:2px solid #F9A825;padding:12px;border-radius:6px;">
            <b style="color:#E65100;font-size:12pt;">CẢNH BÁO AN TOÀN KỸ THUẬT KHI PHA DUNG DỊCH MẸ STOCK A – STOCK B:</b><br>
            • <b>Bồn A:</b> Chứa Calcium Nitrate Ca(NO3)2, Potassium Nitrate và Sắt Chelate (Fe-EDDHA).<br>
            • <b>Bồn B:</b> Chứa Mono Potassium Phosphate (MKP), Magnesium Sulfate (MgSO4) và Vi lượng.<br>
            • <b>Tuyệt đối KHÔNG đổ chung phân đậm đặc A và B trực tiếp</b> vì Ca2+ gặp gốc PO4(3-) và SO4(2-) sẽ tạo kết tủa thạch cao CaSO4 / Ca3(PO4)2 làm tắc hoàn toàn đầu nhỏ giọt bù áp!
        </div>
        <br>
        <h2 style="color:#1B4332;font-size:14pt;border-bottom:2px solid #2E7D32;">PHẦN 3. PHÁC ĐỒ PHÒNG TRỪ TỔNG HỢP SÂU BỆNH SINH HỌC (IPM)</h2>
        <table border="1" cellpadding="7" cellspacing="0" style="border-color:#A5D6A7;width:100%;">
            <tr style="background-color:#E8F5E9;color:#1B4332;font-weight:bold;">
                <th width="24%">Đối tượng dịch hại</th>
                <th width="34%">Triệu chứng nhận diện sớm</th>
                <th width="42%">Phác đồ xử lý sinh học (PHI = 0–3 ngày)</th>
            </tr>
            <tr>
                <td><b>Bọ trĩ (Thrips palmi)</b></td>
                <td>Đọt non xoăn tít, mặt dưới lá vệt ánh bạc màu đồng, truyền virus khảm</td>
                <td>Treo 40 bẫy dính xanh/1.000m²; phun nấm <i>Beauveria + Metarhizium</i> kết hợp dầu Neem chiều mát</td>
            </tr>
            <tr>
                <td><b>Rệp sáp phấn trắng</b></td>
                <td>Bám cụm phủ sáp trắng ở cuống trái, cổ rễ; kéo theo kiến đen &amp; bồ hóng</td>
                <td>Băng keo chặn kiến quanh gốc; phun dầu khoáng sinh học 0.3% làm tan sáp + nấm trắng ký sinh</td>
            </tr>
            <tr>
                <td><b>Nhện đỏ hai chấm</b></td>
                <td>Lá lấm tấm chấm vàng trắng li ti, mặt dưới chăng tơ mịn khi trời khô nóng</td>
                <td>Tăng ẩm vi khí hậu, thả nhện bắt mồi <i>Phytoseiulus</i>, phun Matrine / Abamectin sinh học mặt dưới lá</td>
            </tr>
            <tr>
                <td><b>Bệnh Gỉ sắt &amp; Thối rễ</b></td>
                <td>Ổ bào tử nâu cam mặt dưới lá già; thối nhũn cổ rễ khi úng nước</td>
                <td>Tỉa lá gốc thông thoáng; phun <i>Bacillus subtilis</i> trên tán và tưới <i>Trichoderma</i> vùng rễ</td>
            </tr>
        </table>
        <br>
        <h2 style="color:#1B4332;font-size:14pt;border-bottom:2px solid #2E7D32;">PHẦN 4. QUY TẮC 5 ĐÚNG &amp; GHI NHẬT KÝ ĐIỆN TỬ TRUY XUẤT QR VIETGAP</h2>
        <ol style="line-height:1.7;">
            <li><b>Đúng mã lô canh tác:</b> Khởi tạo mã lô VietGAP (VD: <i>VG-LD-202611-0001</i>) ngay khi xuống giống.</li>
            <li><b>Đúng danh mục sinh học:</b> Chỉ sử dụng phân bón và chế phẩm sinh học trong danh mục được phép lưu hành tại Việt Nam.</li>
            <li><b>Đúng nồng độ &amp; thông số đo:</b> Ghi nhận đầy đủ chỉ số pH, EC dung dịch tưới và nước thoát hàng ngày.</li>
            <li><b>Đúng thời điểm vi khí hậu:</b> Phun chế phẩm vi sinh/thảo mộc vào chiều mát (16:00–18:00) để tránh tia UV phân hủy bào tử.</li>
            <li><b>Đúng thời gian cách ly (PHI):</b> Kiểm tra tự động đủ số ngày cách ly trước khi thu hoạch và dán tem QR hộ chiếu nông sản.</li>
        </ol>
        <div style="text-align:center;color:#2E7D32;font-size:9.5pt;margin-top:12px;">
            — Tài liệu chuẩn hóa thuộc Hệ thống Đào tạo Nông nghiệp Công nghệ cao Mầm Xanh LMS 2.0 —
        </div>
    ', true, false, true, false, '');
    $pdf1->Output($pdf1path, 'F');

    // ---------------------------------------------------------------
    // PDF 2: Quy trình Bảo quản Lạnh & Chế biến Sâu Nông sản Sau Thu hoạch
    // ---------------------------------------------------------------
    $pdf2 = new pdf('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf2->SetCreator('Mầm Xanh LMS 2.0');
    $pdf2->SetAuthor('Hội đồng Chuyên môn Mầm Xanh AgTech');
    $pdf2->SetTitle('Quy trình Bảo quản Lạnh & Chế biến Sâu Nông sản Sau Thu hoạch');
    $pdf2->setPrintHeader(false);
    $pdf2->setPrintFooter(false);
    $pdf2->SetMargins(15, 15, 15);
    $pdf2->SetAutoPageBreak(true, 16);
    $pdf2->SetFont('freesans', '', 11);

    // Page 1
    $pdf2->AddPage();
    $pdf2->writeHTML('
        <div style="background-color:#0D47A1;color:#FFFFFF;padding:18px;border-radius:8px;">
            <div style="font-size:10pt;color:#BBDEFB;font-weight:bold;">TÀI LIỆU KỸ THUẬT CHÍNH THỨC • MẦM XANH LMS 2.0 (CHEBIEN01)</div>
            <h1 style="font-size:18pt;color:#FFFFFF;margin:6px 0;">QUY TRÌNH BẢO QUẢN CHUỖI LẠNH, SẤY LẠNH &amp; CHẾ BIẾN SÂU SAU THU HOẠCH</h1>
            <div style="font-size:10pt;color:#E3F2FD;">Giảm thất thoát sau thu hoạch từ 25% xuống dưới 5% • Chuẩn Xuất khẩu &amp; Siêu thị</div>
        </div>
        <br>
        <h2 style="color:#1B4332;font-size:14pt;border-bottom:2px solid #2E7D32;">PHẦN 1. LÀM MÁT SƠ BỘ (PRE-COOLING) TRONG 2 GIỜ VÀNG ĐẦU TIÊN</h2>
        <p style="line-height:1.6;">Sau khi cắt rời khỏi cây mẹ, nông sản vẫn là thực thể sống tiếp tục hô hấp mạnh và sinh nhiệt. Cứ tăng thêm <b>10°C nhiệt độ đồng ruộng</b>, tốc độ hô hấp phân hủy đường và vitamin tăng gấp <b>2 đến 3 lần</b> (hệ số Q10). Do đó phải hạ nhiệt sơ bộ trong vòng <b>2 giờ đầu</b>:</p>
        <table border="1" cellpadding="7" cellspacing="0" style="border-color:#90CAF9;width:100%;">
            <tr style="background-color:#E3F2FD;color:#0D47A1;font-weight:bold;">
                <th width="28%">Phương pháp Pre-cooling</th>
                <th width="36%">Nhóm nông sản phù hợp</th>
                <th width="36%">Thông số vận hành chuẩn</th>
            </tr>
            <tr>
                <td><b>Cưỡng bức gió lạnh (Forced-Air)</b></td>
                <td>Dâu tây, Dưa lưới, Cà chua, Trái cây ăn tươi</td>
                <td>Quạt hút tạo chênh áp qua khe thùng, hạ tâm quả về 4–10°C trong 45–90 phút</td>
            </tr>
            <tr>
                <td><b>Chân không (Vacuum Cooling)</b></td>
                <td>Xà lách thủy canh, Cải bó xôi, Rau ăn lá cao cấp</td>
                <td>Áp suất 4.6 mmHg làm bay hơi 1–2% ẩm bề mặt, hạ đều 2–4°C chỉ trong 25 phút</td>
            </tr>
            <tr>
                <td><b>Nước lạnh sạch (Hydro-Cooling)</b></td>
                <td>Măng tây, Cà rốt, Bắp ngọt, Trái vỏ cứng</td>
                <td>Nước lạnh 1–3°C khử trùng Chlorine thực phẩm 50–100 ppm, pH 6.5–7.0</td>
            </tr>
        </table>
        <br>
        <h2 style="color:#1B4332;font-size:14pt;border-bottom:2px solid #2E7D32;">PHẦN 2. CÔNG NGHỆ SẤY LẠNH BƠM NHIỆT &amp; CẤP ĐÔNG NHANH IQF</h2>
        <table border="1" cellpadding="7" cellspacing="0" style="border-color:#A5D6A7;width:100%;">
            <tr style="background-color:#1B4332;color:#FFFFFF;font-weight:bold;">
                <th width="30%">Công nghệ chế biến sâu</th>
                <th width="26%">Dải nhiệt độ chuẩn</th>
                <th width="44%">Ưu điểm vượt trội về chất lượng thương phẩm</th>
            </tr>
            <tr>
                <td><b>Sấy lạnh bơm nhiệt (Heat-Pump)</b></td>
                <td align="center"><b>25°C – 40°C</b><br>(Ẩm buồng sấy 10–15%)</td>
                <td>Giữ nguyên màu sắc tự nhiên, tinh dầu thơm và &gt;92% hàm lượng Vitamin C, Polyphenol (không caramel hóa như sấy nhiệt 70°C)</td>
            </tr>
            <tr>
                <td><b>Sấy thăng hoa (Freeze Drying)</b></td>
                <td align="center"><b>-35°C &amp; Chân không</b></td>
                <td>Nước đá thăng hoa trực tiếp sang hơi; cấu trúc giòn xốp nguyên bản cho Sầu riêng sấy giòn, Dâu tây, Đông trùng hạ thảo</td>
            </tr>
            <tr>
                <td><b>Cấp đông siêu tốc IQF</b></td>
                <td align="center"><b>-35°C đến -40°C</b><br>(Trữ đông -18°C)</td>
                <td>Tinh thể băng siêu mịn không phá vỡ màng tế bào; rã đông không chảy dịch (Múi sầu riêng xuất khẩu chính ngạch)</td>
            </tr>
        </table>
    ', true, false, true, false, '');

    // Page 2
    $pdf2->AddPage();
    $pdf2->writeHTML('
        <h2 style="color:#1B4332;font-size:14pt;border-bottom:2px solid #2E7D32;">PHẦN 3. BAO BÌ KHÍ QUYỂN BIẾN ĐỔI (MAP) &amp; VẬN HÀNH KHO LẠNH CA</h2>
        <p style="line-height:1.6;">Kết hợp kiểm soát nhiệt độ lạnh với điều chỉnh thành phần khí quyển (giảm <b>O2 xuống 3%–5%</b>, duy trì <b>CO2 ở mức 5%–8%</b> và hấp thụ khí chín <b>Ethylene C2H4</b> bằng gói KMnO4) giúp kéo dài tuổi thọ thương phẩm gấp 2–4 lần:</p>
        <table border="1" cellpadding="7" cellspacing="0" style="border-color:#A5D6A7;width:100%;">
            <tr style="background-color:#E8F5E9;color:#1B4332;font-weight:bold;">
                <th width="28%">Mặt hàng nông sản</th>
                <th width="20%">Nhiệt độ kho</th>
                <th width="18%">Độ ẩm RH</th>
                <th width="34%">Lưu ý chống tổn thương lạnh (Chilling Injury)</th>
            </tr>
            <tr>
                <td>Rau ăn lá VietGAP / Thủy canh</td>
                <td align="center">2°C – 4°C</td>
                <td align="center">92% – 95%</td>
                <td>Tránh đóng băng &lt; 0°C; dùng màng vi đục lỗ MAP chống đọng sương</td>
            </tr>
            <tr>
                <td>Ớt chuông / Dưa lưới nhà màng</td>
                <td align="center">7°C – 10°C</td>
                <td align="center">90% – 95%</td>
                <td>Dưới 6°C vỏ trái bị lõm nước và thối nhũn nhanh khi đưa ra quầy</td>
            </tr>
            <tr>
                <td>Sầu riêng / Xoài / Chuối tươi</td>
                <td align="center">13°C – 15°C</td>
                <td align="center">85% – 90%</td>
                <td>Cây nhiệt đới nhạy cảm lạnh: tuyệt đối không bảo quản trái tươi &lt; 12°C</td>
            </tr>
        </table>
        <br>
        <h2 style="color:#1B4332;font-size:14pt;border-bottom:2px solid #2E7D32;">PHẦN 4. ĐÓNG GÓI TRUY XUẤT NGUỒN GỐC MÃ QR &amp; GIÁM SÁT DATALOGGER</h2>
        <ul style="line-height:1.7;">
            <li><b>Thiết bị ghi nhiệt tự động (DataLogger):</b> Đặt tối thiểu 2 thiết bị ở vị trí giữa thùng và gần cửa container lạnh để ghi nhận chuỗi nhiệt độ liên tục mỗi 15 phút.</li>
            <li><b>Liên thông Sổ Nhật ký VietGAP Điện tử:</b> Mỗi lô thành phẩm xuất kho trên Mầm Xanh LMS được gắn một mã định danh duy nhất (VD: <i>VG-LD-202611-0016</i>).</li>
            <li><b>Minh bạch với Đơn vị Thu mua &amp; Người tiêu dùng:</b> Quét mã QR trên bao bì mở trực tiếp Hộ chiếu Nông sản công khai hiển thị tọa độ GPS nông trại, lịch sử dinh dưỡng pH/EC, ngày thu hoạch và chứng nhận an toàn.</li>
        </ul>
        <div style="background-color:#E8F5E9;border:1.5px solid #2E7D32;padding:10px;border-radius:6px;text-align:center;color:#1B4332;font-weight:bold;">
            TÀI LIỆU ĐƯỢC XEM TRỰC TIẾP TRÊN TRÌNH DUYỆT MẦM XANH LMS 2.0 — KHÔNG CẦN TẢI VỀ THIẾT BỊ
        </div>
    ', true, false, true, false, '');
    $pdf2->Output($pdf2path, 'F');

    return [
        'chamsoc_pdf' => $pdf1path,
        'chebien_pdf' => $pdf2path,
    ];
}

/**
 * Install base H5P libraries into mdl_h5p_libraries and build 3 authentic Vietnamese .h5p packages.
 */
function mamxanh_stage7_build_h5p_packages(string $baseh5pdir, string $outpkgdir): array {
    global $USER;
    if (!is_dir($outpkgdir)) {
        mkdir($outpkgdir, 0775, true);
    }

    // 1. Ensure upstream H5P libraries from qset.h5p, cp.h5p, ipsums.h5p are installed in mdl_h5p_libraries
    $factory = new \core_h5p\factory();
    $fs = get_file_storage();
    $userctx = context_user::instance($USER->id);
    $config = (object)['frame' => 1, 'export' => 0, 'embed' => 0, 'copyright' => 0];

    foreach (['qset.h5p', 'cp.h5p', 'ipsums.h5p'] as $seedfile) {
        $fullseed = $baseh5pdir . '/' . $seedfile;
        if (!file_exists($fullseed)) {
            continue;
        }
        $draftid = file_get_unused_draft_itemid();
        $finfo = [
            'contextid' => $userctx->id,
            'component' => 'user',
            'filearea'  => 'draft',
            'itemid'    => $draftid,
            'filepath'  => '/',
            'filename'  => $seedfile,
        ];
        $stored = $fs->create_file_from_pathname($finfo, $fullseed);
        \core_h5p\helper::save_h5p($factory, $stored, $config, true, true);
        $stored->delete();
    }

    // Helper: Build a standalone .h5p file from a base .h5p (preserving its bundled libraries) with new h5p.json + content/content.json
    $repackH5p = static function(string $srcH5p, string $destH5p, array $h5pJsonOverride, array $contentData): string {
        copy($srcH5p, $destH5p);
        $za = new ZipArchive();
        if ($za->open($destH5p) !== true) {
            throw new RuntimeException("Cannot open H5P archive: $destH5p");
        }
        $origMeta = json_decode((string)$za->getFromName('h5p.json'), true) ?: [];
        $newMeta = array_merge($origMeta, $h5pJsonOverride);

        // Remove any sample image files inside content/images if present so package is clean
        $toDelete = [];
        for ($i = 0; $i < $za->numFiles; $i++) {
            $stat = $za->statIndex($i);
            if ($stat && strpos($stat['name'], 'content/') === 0 && $stat['name'] !== 'content/' && $stat['name'] !== 'content/content.json') {
                $toDelete[] = $stat['name'];
            }
        }
        foreach ($toDelete as $delName) {
            $za->deleteName($delName);
        }

        $za->addFromString('h5p.json', json_encode($newMeta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $za->addFromString('content/content.json', json_encode($contentData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $za->close();
        return $destH5p;
    };

    // Helper for MultiChoice subcontent inside QuestionSet & CoursePresentation
    $makeMcQuestion = static function(string $subId, string $title, string $questionHtml, array $options, int $correctIdx): array {
        $answers = [];
        foreach ($options as $idx => [$text, $feedback]) {
            $isRight = ($idx === $correctIdx);
            $answers[] = [
                'correct' => $isRight,
                'text'    => '<div>' . $text . '</div>',
                'tipsAndFeedback' => [
                    'tip'               => '',
                    'chosenFeedback'    => '<div>' . $feedback . '</div>',
                    'notChosenFeedback' => '',
                ],
            ];
        }
        return [
            'library'      => 'H5P.MultiChoice 1.16',
            'subContentId' => $subId,
            'metadata'     => [
                'contentType' => 'Multiple Choice',
                'license'     => 'U',
                'title'       => $title,
            ],
            'params' => [
                'question' => '<p><strong>' . $questionHtml . '</strong></p>',
                'answers'  => $answers,
                'overallFeedback' => [
                    ['from' => 0, 'to' => 69, 'feedback' => 'Xem lại sổ tay kỹ thuật pH/EC & IPM.'],
                    ['from' => 70, 'to' => 100, 'feedback' => 'Chính xác chuẩn kỹ thuật VietGAP!'],
                ],
                'behaviour' => [
                    'enableRetry'               => true,
                    'enableSolutionsButton'     => true,
                    'enableCheckButton'         => true,
                    'type'                      => 'single',
                    'singlePoint'               => false,
                    'randomAnswers'             => false,
                    'showSolutionsRequiresInput'=> true,
                    'confirmCheckDialog'        => false,
                    'confirmRetryDialog'        => false,
                    'autoCheck'                 => false,
                    'passPercentage'            => 70,
                    'showScorePoints'           => true,
                ],
                'UI' => [
                    'checkAnswerButton' => 'Kiểm tra đáp án',
                    'submitAnswerButton'=> 'Nộp đáp án',
                    'showSolutionButton'=> 'Xem đáp án đúng',
                    'tryAgainButton'    => 'Làm lại',
                    'tipsLabel'         => 'Gợi ý kỹ thuật',
                    'scoreBarLabel'     => 'Đạt :num / :total điểm',
                    'tipAvailable'      => 'Có gợi ý',
                    'feedbackAvailable' => 'Có giải thích',
                    'readFeedback'      => 'Đọc giải thích',
                    'wrongAnswer'       => 'Chưa chính xác',
                    'correctAnswer'     => 'Đáp án đúng',
                    'shouldCheck'       => 'Cần chọn đáp án này',
                    'shouldNotCheck'    => 'Không nên chọn đáp án này',
                    'noInput'           => 'Vui lòng chọn phương án trả lời trước.',
                    'a11yCheck'         => 'Kiểm tra câu trả lời',
                    'a11yShowSolution'  => 'Hiển thị lời giải',
                    'a11yRetry'         => 'Thực hiện lại câu hỏi',
                ],
                'confirmCheck' => ['header' => 'Xác nhận?', 'body' => 'Kiểm tra câu trả lời này?', 'cancelLabel' => 'Hủy', 'confirmLabel' => 'Đồng ý'],
                'confirmRetry' => ['header' => 'Làm lại?', 'body' => 'Bạn muốn làm lại câu hỏi?', 'cancelLabel' => 'Hủy', 'confirmLabel' => 'Làm lại'],
            ],
        ];
    };

    // ---------------------------------------------------------------
    // H5P #1: H5P.QuestionSet 1.20 (CHAMSOC01 & MASTER-AGRITECH-TPL)
    // ---------------------------------------------------------------
    $qsetContent = [
        'introPage' => [
            'showIntroPage'   => false,
            'startButtonText' => 'Bắt đầu Tương tác H5P',
            'introduction'    => '',
        ],
        'progressType'               => 'dots',
        'passPercentage'             => 70,
        'disableBackwardsNavigation' => false,
        'randomQuestions'            => false,
        'questions' => [
            $makeMcQuestion(
                'a1111111-2222-4333-8444-555555555501',
                'Kiểm soát pH vùng rễ nhà màng',
                'Câu 1/3: Ngưỡng pH dung dịch tưới nhỏ giọt tối ưu giúp cây rau ăn lá và dưa lưới hấp thu trọn vẹn 12 nguyên tố khoáng (không kết tủa Sắt/Lân) là bao nhiêu?',
                [
                    ['pH từ 5.8 đến 6.2', 'Chính xác! Ở pH 5.8–6.2, cả đa lượng (N, P, K, Ca, Mg) và vi lượng (Fe, Mn, Zn, B) đều ở dạng ion hòa tan dễ hấp thu nhất.'],
                    ['pH từ 3.5 đến 4.2 (quá chua)', 'Chưa đúng: pH < 4.5 gây cháy rễ tơ và ngộ độc Mangan/Nhôm.'],
                    ['pH từ 7.8 đến 8.5 (kiềm cao)', 'Chưa đúng: pH kiềm làm kết tủa hoàn toàn Sắt (Fe), Lân (P) và Kẽm (Zn).'],
                ],
                0
            ),
            $makeMcQuestion(
                'a1111111-2222-4333-8444-555555555502',
                'Quy tắc an toàn pha bồn phân Stock A - B',
                'Câu 2/3: Vì sao trong hệ thống châm phân tự động Fertigation phải tách riêng Bồn A (Calcium Nitrate + Fe) và Bồn B (MKP + MgSO4)?',
                [
                    ['Để ngăn phản ứng kết tủa CaSO4 (thạch cao) và Ca3(PO4)2 gây tắc nghẽn đầu tưới nhỏ giọt bù áp', 'Hoàn toàn chính xác! Canxi đậm đặc gặp gốc Sulfate và Phosphate tạo kết tủa rắn không tan làm hỏng béc nhỏ giọt.'],
                    ['Chỉ để phân biệt màu sắc hai bồn chứa cho đẹp mắt', 'Chưa đúng: đây là nguyên tắc hóa học bắt buộc để tránh kết tủa khoáng.'],
                    ['Để tăng pH nước tưới lên mức 9.0', 'Chưa đúng: pH 9.0 sẽ làm cây ngừng hút dinh dưỡng.'],
                ],
                0
            ),
            $makeMcQuestion(
                'a1111111-2222-4333-8444-555555555503',
                'Phác đồ sinh học IPM trị Bọ trĩ hại đọt non',
                'Câu 3/3: Khi phát hiện mật độ Bọ trĩ (Thrips palmi) tăng trên ngọn dưa lưới/ớt chuông canh tác chuẩn VietGAP, phác đồ IPM nào an toàn và hiệu quả nhất?',
                [
                    ['Treo bẫy dính xanh/vàng (40 bẫy/1000m²), thả nhện bắt mồi và phun nấm sinh học Beauveria + tinh dầu Neem vào chiều mát', 'Xuất sắc! Giải pháp sinh học tổng hợp vừa diệt bọ trĩ hiệu quả vừa đảm bảo thời gian cách ly PHI an toàn tuyệt đối.'],
                    ['Pha trộn 3 loại thuốc hóa học độc tính cao phun ngay trước ngày thu hoạch', 'Sai nghiêm trọng: vi phạm quy định an toàn thực phẩm và tồn dư BVTV.'],
                    ['Đóng kín toàn bộ quạt thông gió để nhiệt độ nhà màng vượt 45°C', 'Chưa đúng: nhiệt độ >45°C gây rụng hoa và khô cháy lá cây trồng.'],
                ],
                0
            ),
        ],
        'endGame' => [
            'showResultPage'     => true,
            'showSolutionButton' => true,
            'showRetryButton'    => true,
            'noResultMessage'    => 'Đã hoàn thành bài tương tác H5P',
            'message'            => 'Kết quả Tương tác H5P của bạn (Đã tự động đồng bộ vào Sổ điểm Moodle Gradebook):',
            'overallFeedback'    => [
                ['from' => 0, 'to' => 69, 'feedback' => 'Hãy ôn lại Sổ tay PDF trực tuyến và thử lại để đạt >= 70%!'],
                ['from' => 70, 'to' => 100, 'feedback' => 'Tuyệt vời! Điểm H5P đã được lưu vào Gradebook và mở khóa lộ trình kế tiếp!'],
            ],
            'solutionButtonText' => 'Xem giải thích chi tiết',
            'retryButtonText'    => 'Thực hành lại',
            'finishButtonText'   => 'Hoàn thành & Lưu điểm',
            'submitButtonText'   => 'Nộp kết quả H5P',
            'scoreBarLabel'      => 'Bạn đạt @finals / @totals điểm kỹ thuật',
            'showAnimations'     => false,
            'skippable'          => false,
            'skipButtonText'     => 'Bỏ qua',
        ],
        'texts' => [
            'prevButton'          => '← Câu trước',
            'nextButton'          => 'Câu tiếp theo →',
            'finishButton'        => 'Hoàn thành & Lưu điểm',
            'submitButton'        => 'Nộp kết quả H5P',
            'textualProgress'     => 'Câu hỏi @current / @total',
            'jumpToQuestion'      => 'Câu %d / %total',
            'questionLabel'       => 'Câu hỏi',
            'readSpeakerProgress' => 'Câu hỏi @current trên @total',
            'unansweredText'      => 'Chưa trả lời',
            'answeredText'        => 'Đã trả lời',
            'currentQuestionText' => 'Câu hiện tại',
            'navigationLabel'     => 'Danh sách câu hỏi',
        ],
        'override' => [
            'checkButton' => true,
        ],
    ];

    $h5pQsetPath = $repackH5p(
        $baseh5pdir . '/qset.h5p',
        $outpkgdir . '/H5P_BoCauHoi_DinhDuong_IPM_VietGAP.h5p',
        [
            'title'           => 'H5P Question Set: Chẩn đoán Dinh dưỡng pH/EC & Sâu bệnh Sinh học IPM',
            'language'        => 'vi',
            'defaultLanguage' => 'vi',
            'mainLibrary'     => 'H5P.QuestionSet',
        ],
        $qsetContent
    );

    // ---------------------------------------------------------------
    // H5P #2: H5P.CoursePresentation 1.26 (CHEBIEN01)
    // ---------------------------------------------------------------
    $zaCp = new ZipArchive();
    $zaCp->open($baseh5pdir . '/cp.h5p');
    $cpTemplate = json_decode((string)$zaCp->getFromName('content/content.json'), true);
    $zaCp->close();

    $cpSlides = [
        // Slide 1: Pre-cooling
        [
            'slideBackgroundSelector' => ['fillSlideBackground' => '#E8F5E9'],
            'keywords' => [['main' => '1. Làm mát sơ bộ Pre-cooling (< 2 giờ)']],
            'elements' => [
                [
                    'x' => 4, 'y' => 6, 'width' => 92, 'height' => 86,
                    'alwaysDisplayComments' => false, 'backgroundOpacity' => 100, 'displayAsButton' => false,
                    'buttonSize' => 'big', 'goToSlideType' => 'specified', 'invisible' => false, 'solution' => '',
                    'action' => [
                        'library' => 'H5P.AdvancedText 1.1',
                        'subContentId' => 'b2222222-1111-4222-8333-000000000001',
                        'metadata' => ['contentType' => 'Text', 'license' => 'U', 'title' => 'Pre-cooling'],
                        'params' => [
                            'text' => '<h2>❄️ Slide 1/3: Nguyên lý Hạ nhiệt Đồng ruộng (Pre-cooling &lt; 2 giờ)</h2>'
                                . '<p>Sau thu hoạch, cứ tăng <strong>10°C</strong> thì cường độ hô hấp phân hủy đường &amp; Vitamin tăng gấp <strong>2–3 lần</strong>.</p>'
                                . '<ul>'
                                . '<li><strong>Rau thủy canh / ăn lá:</strong> Làm mát chân không (Vacuum Cooling) về <code>2–4°C</code> trong 25 phút.</li>'
                                . '<li><strong>Dưa lưới / Dâu tây:</strong> Cưỡng bức gió lạnh (Forced-Air) hạ tâm quả về <code>4–8°C</code>.</li>'
                                . '<li><strong>Sầu riêng tươi xuất khẩu:</strong> Bảo quản mát ở <code>13–15°C</code> (tránh tổn thương lạnh dưới 12°C).</li>'
                                . '</ul>'
                                . '<p><em>👉 Bấm mũi tên sang Slide 2 để xem Công nghệ Sấy lạnh &amp; làm câu hỏi tương tác chấm điểm!</em></p>',
                        ],
                    ],
                ],
            ],
        ],
        // Slide 2: Heat-Pump Freeze Drying & MAP/CA
        [
            'slideBackgroundSelector' => ['fillSlideBackground' => '#E3F2FD'],
            'keywords' => [['main' => '2. Sấy lạnh Bơm nhiệt & Bao bì MAP/CA']],
            'elements' => [
                [
                    'x' => 4, 'y' => 6, 'width' => 92, 'height' => 86,
                    'alwaysDisplayComments' => false, 'backgroundOpacity' => 100, 'displayAsButton' => false,
                    'buttonSize' => 'big', 'goToSlideType' => 'specified', 'invisible' => false, 'solution' => '',
                    'action' => [
                        'library' => 'H5P.AdvancedText 1.1',
                        'subContentId' => 'b2222222-1111-4222-8333-000000000002',
                        'metadata' => ['contentType' => 'Text', 'license' => 'U', 'title' => 'MAP CA'],
                        'params' => [
                            'text' => '<h2>🌬️ Slide 2/3: Sấy Lạnh Bơm Nhiệt (25–40°C) &amp; Khí Quyển Biến Đổi MAP</h2>'
                                . '<ul>'
                                . '<li><strong>Sấy lạnh bơm nhiệt 25–40°C:</strong> Tách ẩm ở nhiệt độ thấp giúp giữ nguyên màu sắc tự nhiên và <strong>&gt;92% Vitamin C, Polyphenol</strong> (vượt trội sấy nhiệt truyền thống &gt;70°C).</li>'
                                . '<li><strong>Bao bì MAP (Modified Atmosphere Packaging):</strong> Duy trì <code>O2: 3–5%</code> và <code>CO2: 5–8%</code> kết hợp gói hút khí chín Ethylene <code>KMnO4</code> giúp kéo dài hạn bảo quản gấp 3 lần.</li>'
                                . '</ul>'
                                . '<p><em>👉 Chuyển sang Slide 3 để trả lời câu hỏi kiểm tra &amp; lưu điểm vào Sổ điểm Moodle!</em></p>',
                        ],
                    ],
                ],
            ],
        ],
        // Slide 3: Embedded Graded MultiChoice Interaction
        [
            'slideBackgroundSelector' => ['fillSlideBackground' => '#FFFDE7'],
            'keywords' => [['main' => '3. Câu hỏi Tình huống Chấm điểm Chuỗi lạnh']],
            'elements' => [
                [
                    'x' => 4, 'y' => 5, 'width' => 92, 'height' => 88,
                    'alwaysDisplayComments' => false, 'backgroundOpacity' => 100, 'displayAsButton' => false,
                    'buttonSize' => 'big', 'goToSlideType' => 'specified', 'invisible' => false, 'solution' => '',
                    'action' => $makeMcQuestion(
                        'b2222222-1111-4222-8333-000000000003',
                        'Kiểm tra Chuỗi lạnh & Sấy lạnh',
                        'Tình huống: Để chế biến trái cây/dược liệu sấy dẻo giữ nguyên màu sắc tươi sáng và hơn 90% hàm lượng Vitamin tự nhiên, Cán bộ kỹ thuật cần cài đặt chế độ sấy nào?',
                        [
                            ['Sấy lạnh bơm nhiệt tách ẩm ở dải nhiệt độ thấp 25°C – 40°C', 'Chính xác 100%! Nhiệt độ thấp 25–40°C ngăn phản ứng sẫm màu caramel hóa và bảo toàn dinh dưỡng.'],
                            ['Sấy lò than nhiệt độ cao 95°C – 110°C liên tục', 'Chưa đúng: nhiệt cao làm cháy đường, mất màu và phân hủy vitamin.'],
                            ['Phơi trực tiếp ngoài mưa nắng không che chắn bụi và côn trùng', 'Chưa đúng: mất an toàn vệ sinh thực phẩm VietGAP/HACCP.'],
                        ],
                        0
                    ),
                ],
            ],
        ],
    ];

    $cpContent = [
        'presentation' => [
            'slides'                   => $cpSlides,
            'keywordListEnabled'       => true,
            'globalBackgroundSelector' => [],
            'keywordListAlwaysShow'    => false,
            'keywordListAutoHide'      => false,
            'keywordListOpacity'       => 90,
        ],
        'override' => $cpTemplate['override'] ?? [
            'activeSurface'              => false,
            'hideSummarySlide'           => false,
            'summarySlideSolutionButton' => true,
            'summarySlideRetryButton'    => true,
            'enablePrintButton'          => false,
        ],
        'l10n' => array_merge($cpTemplate['l10n'] ?? [], [
            'slide'           => 'Trang',
            'score'           => 'Điểm số',
            'yourScore'       => 'Điểm của bạn',
            'maxScore'        => 'Điểm tối đa',
            'total'           => 'Tổng cộng',
            'totalScore'      => 'Tổng điểm đạt được',
            'showSolutions'   => 'Xem đáp án',
            'retry'           => 'Làm lại',
            'prevSlide'       => 'Trang trước',
            'nextSlide'       => 'Trang tiếp theo',
            'summary'         => 'Tổng kết điểm H5P',
            'scoreMessage'    => 'Kết quả bài học H5P (Đã lưu vào Gradebook):',
        ]),
    ];

    $h5pCpPath = $repackH5p(
        $baseh5pdir . '/cp.h5p',
        $outpkgdir . '/H5P_BaiGiang_ChuoiLanh_MAP_CA.h5p',
        [
            'title'           => 'H5P Course Presentation: Công nghệ Pre-cooling, Sấy lạnh & Bảo quản MAP/CA',
            'language'        => 'vi',
            'defaultLanguage' => 'vi',
            'mainLibrary'     => 'H5P.CoursePresentation',
        ],
        $cpContent
    );

    // ---------------------------------------------------------------
    // H5P #3: H5P.Accordion 1.0 (Compact Vietnamese Bio-Safety Guide)
    // ---------------------------------------------------------------
    $accContent = [
        'hTag' => 'h2',
        'panels' => [
            [
                'title' => '1. Quy trình Xử lý Nước tưới Nhỏ giọt Đạt Chuẩn Vi sinh VietGAP',
                'content' => [
                    'library' => 'H5P.AdvancedText 1.1',
                    'subContentId' => 'c3333333-1111-4222-8333-000000000001',
                    'metadata' => ['contentType' => 'Text', 'license' => 'U', 'title' => 'Water'],
                    'params' => ['text' => '<p>Nguồn nước tưới đi qua hệ thống lọc đĩa <strong>120–150 Mesh</strong>, kiểm soát EC nền &lt; 0.3 mS/cm và xét nghiệm định kỳ không nhiễm vi khuẩn <em>E. coli / Salmonella</em>.</p>'],
                ],
            ],
            [
                'title' => '2. Nguyên tắc Phối hợp Nấm đối kháng Trichoderma & Vi sinh Bản địa',
                'content' => [
                    'library' => 'H5P.AdvancedText 1.1',
                    'subContentId' => 'c3333333-1111-4222-8333-000000000002',
                    'metadata' => ['contentType' => 'Text', 'license' => 'U', 'title' => 'Trichoderma'],
                    'params' => ['text' => '<p>Ủ hoạt hóa 1kg <em>Trichoderma</em> với 500kg phân hữu cơ hoai mục + dịch Humic trong 20 ngày. Không pha chung với thuốc trừ nấm hóa học gốc đồng hoặc vôi bột trong vòng 10 ngày.</p>'],
                ],
            ],
            [
                'title' => '3. Quy định Ghi Nhật ký Điện tử & Tem QR Truy xuất Nguồn gốc',
                'content' => [
                    'library' => 'H5P.AdvancedText 1.1',
                    'subContentId' => 'c3333333-1111-4222-8333-000000000003',
                    'metadata' => ['contentType' => 'Text', 'license' => 'U', 'title' => 'VietGAP QR'],
                    'params' => ['text' => '<p>Ghi nhận bằng giọng nói ngay tại vườn trên Mầm Xanh LMS đủ 5 thông tin: Mã lô — Ngày giờ — Tác vụ — Chỉ số pH/EC — Thời gian cách ly PHI trước khi xuất bán.</p>'],
                ],
            ],
        ],
    ];

    $h5pAccPath = $repackH5p(
        $baseh5pdir . '/ipsums.h5p',
        $outpkgdir . '/H5P_Accordion_SoTay_AnToanSinhHoc.h5p',
        [
            'title'           => 'H5P Accordion: Cẩm nang An toàn Sinh học & Truy xuất VietGAP',
            'language'        => 'vi',
            'defaultLanguage' => 'vi',
            'mainLibrary'     => 'H5P.Accordion',
        ],
        $accContent
    );

    return [
        'qset_h5p' => $h5pQsetPath,
        'cp_h5p'   => $h5pCpPath,
        'acc_h5p'  => $h5pAccPath,
    ];
}

/**
 * Build an authentic ADL SCORM 1.2 (.zip) interactive e-learning package:
 * "Quy trình Thực hành Nông nghiệp Tốt VietGAP & Kiểm soát Mối nguy"
 */
function mamxanh_stage7_build_scorm12_package(string $outpkgdir): string {
    if (!is_dir($outpkgdir)) {
        mkdir($outpkgdir, 0775, true);
    }
    $zippath = $outpkgdir . '/SCORM12_VietGAP_KiemSoat_MoiNguy.zip';
    if (file_exists($zippath)) {
        unlink($zippath);
    }

    $imsmanifest = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<manifest identifier="vn.mamxanh.scorm12.vietgap.hazards" version="1.0"
          xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2"
          xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2"
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:schemaLocation="http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd
                              http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd">
  <metadata>
    <schema>ADL SCORM</schema>
    <schemaversion>1.2</schemaversion>
  </metadata>
  <organizations default="MAMXANH_VIETGAP_ORG">
    <organization identifier="MAMXANH_VIETGAP_ORG">
      <title>Quy trình Thực hành Nông nghiệp Tốt VietGAP &amp; Kiểm soát Mối nguy</title>
      <item identifier="ITEM_SCO_VIETGAP_01" identifierref="RES_SCO_VIETGAP_01" isvisible="true">
        <title>Bài giảng Tương tác SCORM 1.2: Kiểm soát 3 Nhóm Mối nguy &amp; An toàn VietGAP</title>
        <adlcp:masteryscore>70</adlcp:masteryscore>
      </item>
    </organization>
  </organizations>
  <resources>
    <resource identifier="RES_SCO_VIETGAP_01" type="webcontent" adlcp:scormtype="sco" href="index.html">
      <file href="index.html"/>
      <file href="scorm_api.js"/>
    </resource>
  </resources>
</manifest>
XML;

    $scormjs = <<<'JS'
// ===================================================================
// Official ADL SCORM 1.2 Runtime API Adapter (Mầm Xanh LMS 2.0)
// ===================================================================
(function(window) {
    var apiHandle = null;
    var initialized = false;
    var finished = false;
    var startTime = Date.now();

    function findAPI(win) {
        var attempts = 0;
        while ((win.API == null) && (win.parent != null) && (win.parent != win)) {
            attempts++;
            if (attempts > 10) return null;
            win = win.parent;
        }
        return win.API || null;
    }

    function getAPI() {
        if (apiHandle) return apiHandle;
        apiHandle = findAPI(window);
        if (!apiHandle && window.opener != null && typeof window.opener !== 'undefined') {
            apiHandle = findAPI(window.opener);
        }
        return apiHandle;
    }

    function formatScormTime(ms) {
        var totalSec = Math.max(1, Math.floor(ms / 1000));
        var h = String(Math.floor(totalSec / 3600)).padStart(4, '0');
        var m = String(Math.floor((totalSec % 3600) / 60)).padStart(2, '0');
        var s = String(totalSec % 60).padStart(2, '0');
        return h + ':' + m + ':' + s;
    }

    window.MXScorm12 = {
        init: function() {
            var api = getAPI();
            var badge = document.getElementById('scorm-conn-badge');
            if (api) {
                var res = api.LMSInitialize('');
                initialized = (res === 'true' || res === true);
                var curStatus = api.LMSGetValue('cmi.core.lesson_status');
                if (!curStatus || curStatus === 'not attempted') {
                    api.LMSSetValue('cmi.core.lesson_status', 'incomplete');
                    api.LMSSetValue('cmi.core.score.min', '0');
                    api.LMSSetValue('cmi.core.score.max', '100');
                    api.LMSCommit('');
                }
                if (badge) {
                    badge.textContent = '🟢 Đã kết nối Moodle SCORM 1.2 API (LMSInitialize OK)';
                    badge.style.background = '#D8F3DC';
                    badge.style.color = '#1B4332';
                }
            } else if (badge) {
                badge.textContent = '🟡 Chế độ Xem Độc lập (Standalone SCORM Preview)';
            }
            return initialized;
        },
        completeWithScore: function(rawScore) {
            var score = Math.max(0, Math.min(100, Math.round(rawScore)));
            var status = (score >= 70) ? 'completed' : 'incomplete';
            var api = getAPI();
            var logLines = [];

            if (api) {
                if (!initialized) {
                    api.LMSInitialize('');
                    initialized = true;
                }
                api.LMSSetValue('cmi.core.score.min', '0');
                api.LMSSetValue('cmi.core.score.max', '100');
                api.LMSSetValue('cmi.core.score.raw', String(score));
                api.LMSSetValue('cmi.core.lesson_status', status);
                api.LMSSetValue('cmi.core.session_time', formatScormTime(Date.now() - startTime));
                api.LMSCommit('');
                if (status === 'completed' && !finished) {
                    api.LMSFinish('');
                    finished = true;
                }
                logLines.push('LMSInitialize("") -> true');
                logLines.push('LMSSetValue("cmi.core.score.raw", ' + score + ') -> true');
                logLines.push('LMSSetValue("cmi.core.lesson_status", "' + status + '") -> true');
                logLines.push('LMSCommit("") & LMSFinish("") -> Đã đồng bộ về Moodle Gradebook!');
            } else {
                logLines.push('Mô phỏng SCORM 1.2: score.raw=' + score + ', lesson_status=' + status);
            }
            return { score: score, status: status, logs: logLines };
        }
    };
})(window);
JS;

    $indexhtml = <<<'HTML'
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SCORM 1.2: Quy trình Thực hành Nông nghiệp Tốt VietGAP & Kiểm soát Mối nguy</title>
<style>
  * { box-sizing: border-box; }
  body {
    margin: 0; padding: 16px;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    background: #F4FBF6; color: #14281D; line-height: 1.55;
  }
  .scorm-shell {
    max-width: 920px; margin: 0 auto; background: #FFFFFF;
    border: 2.5px solid #1B4332; border-radius: 22px; overflow: hidden;
    box-shadow: 0 10px 28px rgba(27,67,50,0.12);
  }
  .scorm-header {
    background: linear-gradient(135deg, #1B4332, #2E7D32);
    color: #FFFFFF; padding: 18px 22px;
  }
  .scorm-pill {
    display: inline-block; padding: 4px 12px; border-radius: 999px;
    font-size: 0.78rem; font-weight: 800; background: #FFF8E1; color: #E65100;
  }
  .scorm-body { padding: 20px 22px; }
  .hazard-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 12px; margin-bottom: 20px;
  }
  .hazard-card {
    background: #F8FCF9; border: 2px solid #C8E6C9; border-radius: 16px; padding: 14px;
  }
  .hazard-card h4 { margin: 0 0 6px; color: #1B4332; font-size: 1rem; }
  .q-box {
    background: #FFFFFF; border: 2px solid #DCEEE0; border-radius: 16px;
    padding: 14px 16px; margin-bottom: 12px;
  }
  .q-title { font-weight: 800; color: #1B4332; margin-bottom: 8px; }
  .q-opt {
    display: flex; align-items: center; gap: 8px; padding: 9px 12px;
    border: 1.5px solid #C8E6C9; border-radius: 10px; margin-bottom: 6px;
    cursor: pointer; font-weight: 600; background: #FAFDFA; min-height: 44px;
  }
  .q-opt:hover { background: #E8F5E9; border-color: #2E7D32; }
  .btn-submit-scorm {
    width: 100%; min-height: 52px; border-radius: 14px; border: none;
    background: #1B4332; color: #FFFFFF; font-size: 1.02rem; font-weight: 900;
    cursor: pointer; margin-top: 8px;
  }
  .btn-submit-scorm:hover { background: #2E7D32; }
  #scorm-result-banner {
    display: none; margin-top: 16px; padding: 16px; border-radius: 16px;
    border: 2px solid #2E7D32; background: #E8F5E9;
  }
</style>
<script src="scorm_api.js"></script>
</head>
<body onload="MXScorm12.init()">
<div class="scorm-shell">
  <div class="scorm-header">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px;">
      <span class="scorm-pill">📦 CHUẨN QUỐC TẾ ADL SCORM 1.2 SCO</span>
      <span id="scorm-conn-badge" class="scorm-pill">⏳ Đang kết nối SCORM API...</span>
    </div>
    <h2 style="margin:0 0 4px;font-size:1.35rem;">Quy trình Thực hành Nông nghiệp Tốt VietGAP &amp; Kiểm soát Mối nguy</h2>
    <div style="font-size:0.88rem;color:#D8F3DC;">Nghiên cứu 3 nhóm mối nguy an toàn thực phẩm bên dưới và hoàn thành 4 câu hỏi để đồng bộ điểm số về Moodle LMS</div>
  </div>

  <div class="scorm-body">
    <div class="hazard-grid">
      <div class="hazard-card">
        <h4>🦠 1. Mối nguy Sinh học</h4>
        <div style="font-size:0.9rem;">Vi khuẩn <em>E. coli, Salmonella</em> từ nước tưới ô nhiễm, phân chuồng tươi chưa ủ hoai nhiệt hiếu khí 55–65°C hoặc dụng cụ thu hoạch bẩn.</div>
      </div>
      <div class="hazard-card">
        <h4>🧪 2. Mối nguy Hóa học</h4>
        <div style="font-size:0.9rem;">Tồn dư thuốc BVTV ngoài danh mục, thu hoạch khi chưa đủ ngày cách ly <strong>PHI</strong>, dư lượng Nitrat hoặc kim loại nặng (Pb, Cd, As, Hg).</div>
      </div>
      <div class="hazard-card">
        <h4>🔩 3. Mối nguy Vật lý</h4>
        <div style="font-size:0.9rem;">Mảnh thủy tinh, kim loại, đá sỏi, dị vật nhựa lẫn vào khay nông sản trong khâu thu hái, sơ chế rửa và đóng gói kho lạnh.</div>
      </div>
    </div>

    <h3 style="color:#1B4332;margin:0 0 12px;font-size:1.12rem;">📝 Bài tập Tương tác Kiểm soát Mối nguy VietGAP (Đạt &ge; 70% hoàn thành SCORM)</h3>

    <form id="scorm-quiz-form" onsubmit="event.preventDefault(); submitScormQuiz();">
      <div class="q-box">
        <div class="q-title">Câu 1: Theo tiêu chuẩn VietGAP, phân hữu cơ chuồng trại bắt buộc xử lý như thế nào để triệt tiêu mối nguy sinh học (E. coli, Salmonella)?</div>
        <label class="q-opt"><input type="radio" name="sq1" value="1" required> Ủ hoai mục hiếu khí đạt nhiệt độ 55–65°C hoàn toàn trước khi bón và ghi chép sổ nhật ký lô</label>
        <label class="q-opt"><input type="radio" name="sq1" value="0"> Bón trực tiếp phân tươi chưa ủ lên rau ăn lá sát ngày thu hoạch</label>
      </div>

      <div class="q-box">
        <div class="q-title">Câu 2: Chỉ số PHI (Pre-Harvest Interval) trên nhãn chế phẩm bảo vệ thực vật có ý nghĩa bắt buộc gì?</div>
        <label class="q-opt"><input type="radio" name="sq2" value="1" required> Số ngày cách ly tối thiểu từ lần phun cuối cùng đến khi được phép thu hoạch nông sản an toàn</label>
        <label class="q-opt"><input type="radio" name="sq2" value="0"> Số lít nước cần pha cho mỗi bình phun điện</label>
      </div>

      <div class="q-box">
        <div class="q-title">Câu 3: Để phòng ngừa mối nguy vật lý trong nhà sơ chế đóng gói VietGAP, trang bị nào là bắt buộc đối với bóng đèn chiếu sáng phía trên băng chuyền?</div>
        <label class="q-opt"><input type="radio" name="sq3" value="1" required> Bọc chụp bảo vệ chống vỡ (shatterproof cover) để mảnh kính không thể rơi vào nông sản</label>
        <label class="q-opt"><input type="radio" name="sq3" value="0"> Treo bóng đèn thủy tinh trần sát mặt rổ rau không cần che chắn</label>
      </div>

      <div class="q-box">
        <div class="q-title">Câu 4: Khi phát hiện một lô nông sản chưa đủ thời gian cách ly PHI, Cán bộ kỹ thuật và Nông hộ phải xử lý thế nào?</div>
        <label class="q-opt"><input type="radio" name="sq4" value="1" required> Tuyệt đối tạm dừng thu hoạch, treo biển cảnh báo lô cách ly và chờ đủ ngày an toàn mới xuất bán kèm mã QR</label>
        <label class="q-opt"><input type="radio" name="sq4" value="0"> Vẫn thu hoạch trộn lẫn với các lô khác để bán ra chợ</label>
      </div>

      <button type="submit" id="scorm-submit-btn" class="btn-submit-scorm">
        ✅ Hoàn thành Bài học &amp; Đồng bộ Điểm SCORM 1.2 về Moodle (100 điểm)
      </button>
    </form>

    <div id="scorm-result-banner">
      <div id="scorm-result-title" style="font-weight:900;font-size:1.1rem;color:#1B4332;margin-bottom:6px;"></div>
      <div id="scorm-api-logs" style="font-family:monospace;font-size:0.84rem;background:#FFFFFF;padding:10px;border-radius:10px;border:1px solid #A5D6A7;color:#1B4332;"></div>
    </div>
  </div>
</div>

<script>
function submitScormQuiz() {
    var correct = 0;
    ['sq1', 'sq2', 'sq3', 'sq4'].forEach(function(name) {
        var el = document.querySelector('input[name="' + name + '"]:checked');
        if (el && el.value === '1') correct++;
    });
    var pct = Math.round((correct / 4) * 100);
    var res = MXScorm12.completeWithScore(pct);

    var banner = document.getElementById('scorm-result-banner');
    var title = document.getElementById('scorm-result-title');
    var logs = document.getElementById('scorm-api-logs');
    banner.style.display = 'block';
    title.innerHTML = (res.status === 'completed' ? '🏅 HOÀN THÀNH XUẤT SẮC: ' : '⚠️ CHƯA ĐẠT: ')
        + res.score + '/100 điểm • Trạng thái SCORM: <code>' + res.status.toUpperCase() + '</code>';
    logs.innerHTML = res.logs.join('<br>');
}
</script>
</body>
</html>
HTML;

    $za = new ZipArchive();
    if ($za->open($zippath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException("Cannot create SCORM 1.2 zip at $zippath");
    }
    $za->addFromString('imsmanifest.xml', $imsmanifest);
    $za->addFromString('scorm_api.js', $scormjs);
    $za->addFromString('index.html', $indexhtml);
    $za->close();

    return $zippath;
}
