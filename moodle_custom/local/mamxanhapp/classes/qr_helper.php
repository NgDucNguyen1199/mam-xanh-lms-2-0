<?php
namespace local_mamxanhapp;

defined('MOODLE_INTERNAL') || die();

/**
 * Generates standards-compliant ISO/IEC 18004 SVG QR codes locally using Moodle's built-in TCPDF QR engine.
 */
class qr_helper {
    /**
     * Render a crisp, responsive inline SVG QR code for any URL or verification code.
     *
     * @param string $payload Data/URL to encode
     * @param int $size Pixel width/height
     * @param string $color Hex color for dark modules
     * @return string Inline <svg> markup
     */
    public static function render_svg(string $payload, int $size = 180, string $color = '#1B4332'): string {
        global $CFG;
        if (class_exists('\core_qrcode')) {
            $qrcode = new \core_qrcode($payload);
        } else {
            require_once($CFG->libdir . '/tcpdf/include/barcodes/qrcode.php');
            $qrcode = new \QRcode($payload, 'M');
        }
        $arr = $qrcode->getBarcodeArray();
        if (empty($arr['num_rows']) || empty($arr['bcode'])) {
            return '';
        }

        $rows = (int)$arr['num_rows'];
        $cols = (int)$arr['num_cols'];
        $quiet = 2;
        $totalw = $cols + ($quiet * 2);
        $totalh = $rows + ($quiet * 2);

        $path = '';
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                if (!empty($arr['bcode'][$r][$c])) {
                    $x = $c + $quiet;
                    $y = $r + $quiet;
                    $path .= "M{$x},{$y}h1v1h-1z";
                }
            }
        }

        $escapedpayload = s($payload);
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $totalw . ' ' . $totalh . '" width="' . $size . '" height="' . $size . '" role="img" aria-label="QR: ' . $escapedpayload . '" shape-rendering="crispEdges" style="background:#FFFFFF;border-radius:12px;padding:6px;border:2px solid #D8F3DC;max-width:100%;height:auto;">'
            . '<rect width="' . $totalw . '" height="' . $totalh . '" fill="#FFFFFF"/>'
            . '<path d="' . $path . '" fill="' . $color . '"/>'
            . '</svg>';
    }
}
