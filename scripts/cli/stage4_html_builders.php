<?php
// ===================================================================
// Mầm Xanh LMS 2.0 - Stage 4 HTML5 Interactive Learning Object Builders
// (Zero Static PDFs: HTML5 Video Player + Infographics + 7-Slide Deck + IPM Flashcards)
// ===================================================================
defined('MOODLE_INTERNAL') || die();

/**
 * Section 1 Builder: Interactive HTML5 Video Guide + Bootstrap Visual Infographic.
 */
function mamxanh_build_video_infographic_html(array $cfg): string {
    $uid = preg_replace('/[^a-z0-9]/i', '', $cfg['code']);
    $chaptersJson = json_encode($cfg['chapters'], JSON_UNESCAPED_UNICODE);

    // Map course code to locally hosted real agricultural MP4 + WebM + Poster + Vietnamese WebVTT
    $codeupper = strtoupper($cfg['code']);
    if (strpos($codeupper, 'CHEBIEN') !== false || strpos($codeupper, 'SAURIENG') !== false) {
        $vidslug = 'chebien_postharvest';
    } else if (strpos($codeupper, 'IOT') !== false || strpos($codeupper, 'THUYCANH') !== false) {
        $vidslug = 'nhamang_hydroponics';
    } else if (strpos($codeupper, 'VISINH') !== false) {
        $vidslug = 'visinh_compost';
    } else {
        $vidslug = 'chamsoc_fertigation';
    }
    $mp4url    = "/local/mamxanhapp/media/videos/{$vidslug}.mp4";
    $webmurl   = "/local/mamxanhapp/media/videos/{$vidslug}.webm";
    $posterurl = "/local/mamxanhapp/media/videos/{$vidslug}.jpg";
    $vtturl    = "/local/mamxanhapp/media/videos/{$vidslug}.vtt";

    $cardsHtml = '';
    foreach ($cfg['infographic_cards'] as $card) {
        $cardsHtml .= '<div class="col-12 col-md-4 mb-3">'
            . '<div style="background:#FFFFFF;border:2px solid #C8E6C9;border-radius:18px;padding:18px;height:100%;box-shadow:0 4px 12px rgba(27,67,50,0.05);">'
            . '<div style="font-size:2rem;margin-bottom:8px;">' . $card['icon'] . '</div>'
            . '<h4 style="color:#1B4332;font-weight:800;font-size:1.08rem;margin-bottom:8px;">' . htmlspecialchars($card['title']) . '</h4>'
            . '<div style="font-size:0.94rem;color:#263238;line-height:1.6;">' . $card['body'] . '</div>'
            . '</div>'
            . '</div>';
    }

    $scaleHtml = '';
    foreach ($cfg['scale_bars'] as $bar) {
        $scaleHtml .= '<div style="margin-bottom:12px;">'
            . '<div class="d-flex justify-content-between" style="font-weight:700;font-size:0.92rem;margin-bottom:4px;">'
            . '<span>' . htmlspecialchars($bar['label']) . '</span>'
            . '<span style="color:#1B4332;font-weight:800;">' . htmlspecialchars($bar['range']) . '</span>'
            . '</div>'
            . '<div style="height:14px;background:#E8F5E9;border-radius:999px;overflow:hidden;">'
            . '<div style="width:' . (int)$bar['pct'] . '%;height:100%;background:' . $bar['color'] . ';border-radius:999px;"></div>'
            . '</div>'
            . '</div>';
    }

    return <<<HTML
<div class="mx-digital-lesson" id="mx-vid-{$uid}">
    <!-- Real Local HTML5 Video Player (MP4 + WebM + Vietnamese WebVTT Subtitles — Zero YouTube Block) -->
    <div style="background:linear-gradient(145deg,#102A1E,#1B4332);color:#FFFFFF;border-radius:24px;padding:22px;margin-bottom:24px;box-shadow:0 14px 32px rgba(20,40,29,0.22);">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:8px;">
            <span style="background:#2E7D32;color:#FFF;padding:5px 14px;border-radius:999px;font-weight:800;font-size:0.84rem;">
                🎬 VIDEO THỰC TẾ NÔNG NGHIỆP CNC (HTML5 MP4 + PHỤ ĐỀ VIỆT) • {$cfg['duration']}
            </span>
            <span style="color:#B7E4C7;font-size:0.86rem;font-weight:700;">Phát Trực tiếp Mượt mà 100% (Không phụ thuộc YouTube)</span>
        </div>

        <h3 style="color:#FFFFFF;font-weight:800;font-size:1.35rem;margin-bottom:14px;">{$cfg['video_title']}</h3>

        <!-- Native HTML5 Video Element with MP4, WebM & Default Vietnamese Subtitles -->
        <div style="border-radius:18px;overflow:hidden;border:2px solid #52B788;background:#000000;margin-bottom:14px;">
            <video id="mx-player-{$uid}" class="mx-real-html5-video" controls playsinline preload="metadata" poster="{$posterurl}" style="width:100%;max-height:460px;display:block;margin:0 auto;background:#000;">
                <source src="{$mp4url}" type="video/mp4">
                <source src="{$webmurl}" type="video/webm">
                <track kind="subtitles" src="{$vtturl}" srclang="vi" label="Phụ đề Kỹ thuật Tiếng Việt" default>
                Trình duyệt của bạn không hỗ trợ thẻ video HTML5.
            </video>
        </div>

        <!-- Synchronized Technical Chapter Summary Below Video -->
        <div id="mx-stage-{$uid}" style="background:#0B1F16;border:2px solid #40916C;border-radius:18px;padding:18px;margin-bottom:14px;">
            <div class="d-flex align-items-center" style="gap:14px;">
                <div id="mx-vicon-{$uid}" style="font-size:2.4rem;line-height:1;">{$cfg['chapters'][0]['icon']}</div>
                <div>
                    <div id="mx-vchap-{$uid}" style="color:#95D5B2;font-weight:800;font-size:0.86rem;text-transform:uppercase;">📝 Tóm tắt Kỹ thuật Phần 1 / 3 • {$cfg['chapters'][0]['time']}</div>
                    <h4 id="mx-vhead-{$uid}" style="color:#FFFFFF;font-weight:800;font-size:1.15rem;margin:4px 0;">{$cfg['chapters'][0]['title']}</h4>
                </div>
            </div>
            <p id="mx-vdesc-{$uid}" style="color:#E8F5E9;font-size:0.98rem;line-height:1.6;margin:10px 0;">{$cfg['chapters'][0]['desc']}</p>
            <div style="height:8px;background:rgba(255,255,255,0.15);border-radius:999px;overflow:hidden;">
                <div id="mx-vbar-{$uid}" style="width:33%;height:100%;background:#52B788;transition:width 0.4s ease;"></div>
            </div>
        </div>

        <!-- Video Chapter Seek Controls (>=48px WCAG AAA) -->
        <div class="d-flex flex-wrap" style="gap:10px;">
            <button type="button" class="btn btn-success" style="min-height:48px;border-radius:12px;font-weight:800;background:#2E7D32;" onclick="mxPlayVideoChapter_{$uid}(0, 0)">▶ Phần 1: {$cfg['chapters'][0]['short']}</button>
            <button type="button" class="btn btn-outline-light" style="min-height:48px;border-radius:12px;font-weight:700;" onclick="mxPlayVideoChapter_{$uid}(1, 8)">▶ Phần 2: {$cfg['chapters'][1]['short']}</button>
            <button type="button" class="btn btn-outline-light" style="min-height:48px;border-radius:12px;font-weight:700;" onclick="mxPlayVideoChapter_{$uid}(2, 16)">▶ Phần 3: {$cfg['chapters'][2]['short']}</button>
        </div>
    </div>

    <!-- HTML5 / Bootstrap Infographic Cards -->
    <div style="background:#F4FBF6;border:2px solid #B7E4C7;border-radius:24px;padding:22px;margin-bottom:22px;">
        <h3 style="color:#1B4332;font-weight:800;font-size:1.28rem;margin-bottom:16px;">
            📊 Infographic Kỹ thuật Trọng tâm: {$cfg['infographic_title']}
        </h3>
        <div class="row">
            {$cardsHtml}
        </div>

        <div style="background:#FFFFFF;border-radius:18px;padding:18px;border:1.5px solid #C8E6C9;margin-top:8px;">
            <h4 style="color:#1B4332;font-weight:800;font-size:1.08rem;margin-bottom:12px;">📏 Thang Đo Thông Số Chuẩn Thực Địa</h4>
            {$scaleHtml}
        </div>
    </div>
</div>

<script>
function mxPlayVideoChapter_{$uid}(idx, seekSec) {
    var chaps = {$chaptersJson};
    if (!chaps[idx]) return;
    var c = chaps[idx];
    document.getElementById('mx-vicon-{$uid}').textContent = c.icon;
    document.getElementById('mx-vchap-{$uid}').textContent = '📝 Tóm tắt Kỹ thuật Phần ' + (idx + 1) + ' / ' + chaps.length + ' • ' + c.time;
    document.getElementById('mx-vhead-{$uid}').textContent = c.title;
    document.getElementById('mx-vdesc-{$uid}').textContent = c.desc;
    document.getElementById('mx-vbar-{$uid}').style.width = Math.round(((idx + 1) / chaps.length) * 100) + '%';
    var root = document.getElementById('mx-vid-{$uid}');
    var vid = (root && (root.querySelector('video.vjs-tech') || root.querySelector('video'))) || document.querySelector('#region-main video');
    if (vid) {
        try {
            vid.currentTime = seekSec || 0;
            var p = vid.play();
            if (p && typeof p.catch === 'function') p.catch(function(){});
        } catch (e) {}
    }
}
</script>
HTML;
}

/**
 * Section 2 Builder: 7-Slide Interactive Slide Deck with SVG Schematics & Instant Scenario Quiz.
 */
function mamxanh_build_slidedeck_html(string $code, string $decktitle, array $slides): string {
    $uid = preg_replace('/[^a-z0-9]/i', '', $code);
    $total = count($slides);
    $slidesHtml = '';

    foreach ($slides as $i => $s) {
        $display = ($i === 0) ? 'block' : 'none';
        $num = $i + 1;
        $diagram = $s['svg'] ?? '';

        $quizHtml = '';
        if (!empty($s['quiz'])) {
            $q = $s['quiz'];
            $qid = "{$uid}_sq_{$i}";
            $optsHtml = '';
            foreach ($q['options'] as $oi => $opt) {
                $isRight = ((int)$oi === (int)$q['answer']) ? '1' : '0';
                $exp = htmlspecialchars($q['explain'], ENT_QUOTES);
                $optsHtml .= '<button type="button" class="btn btn-outline-success w-100 text-start mb-2" '
                    . 'style="min-height:48px;border-radius:12px;font-weight:700;border-width:2px;" '
                    . 'onclick="mxCheckSlideScenario(\'' . $qid . '\',' . $isRight . ',\'' . $exp . '\', this)">'
                    . htmlspecialchars($opt)
                    . '</button>';
            }
            $quizHtml = '<div style="background:#F1F8E9;border:2px solid #81C784;border-radius:16px;padding:16px;margin-top:16px;">'
                . '<div style="font-weight:800;color:#1B4332;margin-bottom:10px;">💡 Câu hỏi Tình huống Thực tế: ' . htmlspecialchars($q['question']) . '</div>'
                . $optsHtml
                . '<div id="' . $qid . '_fb" style="display:none;margin-top:8px;padding:10px 14px;border-radius:12px;font-weight:700;"></div>'
                . '</div>';
        }

        $slidesHtml .= '<div class="mx-slide-item-' . $uid . '" data-idx="' . $i . '" style="display:' . $display . ';">'
            . '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">'
            . '<span style="background:#D8F3DC;color:#1B4332;padding:5px 14px;border-radius:999px;font-weight:800;font-size:0.85rem;">Slide ' . $num . ' / ' . $total . '</span>'
            . '<span style="font-size:0.85rem;color:#2E7D32;font-weight:700;">' . htmlspecialchars($s['tag'] ?? 'Kỹ thuật chuẩn') . '</span>'
            . '</div>'
            . '<h3 style="color:#1B4332;font-weight:800;font-size:1.35rem;margin-bottom:12px;">' . htmlspecialchars($s['title']) . '</h3>'
            . '<div style="font-size:1.02rem;line-height:1.7;color:#14281D;">' . $s['body'] . '</div>'
            . $diagram
            . $quizHtml
            . '</div>';
    }

    return <<<HTML
<div class="mx-interactive-deck" style="background:#FFFFFF;border:2.5px solid #1B4332;border-radius:24px;padding:24px;box-shadow:0 12px 28px rgba(27,67,50,0.10);">
    <div style="border-bottom:2px solid #E8F5E9;padding-bottom:12px;margin-bottom:18px;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;">
        <strong style="color:#1B4332;font-size:1.05rem;">📑 {$decktitle}</strong>
        <div style="height:10px;width:180px;background:#E8F5E9;border-radius:999px;overflow:hidden;">
            <div id="mx-sprog-{$uid}" style="width:14%;height:100%;background:#2E7D32;transition:width 0.25s ease;"></div>
        </div>
    </div>

    {$slidesHtml}

    <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top:2px solid #E8F5E9;gap:12px;">
        <button type="button" id="mx-sprev-{$uid}" class="btn btn-outline-success" style="min-height:50px;border-radius:14px;font-weight:800;padding:0 20px;border-width:2px;" onclick="mxMoveSlide_{$uid}(-1)">
            ← Slide trước
        </button>
        <span id="mx-sindicator-{$uid}" style="font-weight:800;color:#1B4332;">1 / {$total}</span>
        <button type="button" id="mx-snext-{$uid}" class="btn btn-success" style="min-height:50px;border-radius:14px;font-weight:800;padding:0 20px;background:#1B4332;" onclick="mxMoveSlide_{$uid}(1)">
            Slide tiếp theo →
        </button>
    </div>
</div>

<script>
(function() {
    var cur_{$uid} = 0;
    var total_{$uid} = {$total};
    window.mxMoveSlide_{$uid} = function(delta) {
        var next = cur_{$uid} + delta;
        if (next < 0 || next >= total_{$uid}) return;
        cur_{$uid} = next;
        var items = document.querySelectorAll('.mx-slide-item-{$uid}');
        items.forEach(function(el, idx) {
            el.style.display = (idx === cur_{$uid}) ? 'block' : 'none';
        });
        document.getElementById('mx-sindicator-{$uid}').textContent = (cur_{$uid} + 1) + ' / ' + total_{$uid};
        document.getElementById('mx-sprog-{$uid}').style.width = Math.round(((cur_{$uid} + 1) / total_{$uid}) * 100) + '%';
    };
})();
if (!window.mxCheckSlideScenario) {
    window.mxCheckSlideScenario = function(qid, isRight, explain, btn) {
        var fb = document.getElementById(qid + '_fb');
        if (!fb) return;
        fb.style.display = 'block';
        if (isRight === 1) {
            fb.style.background = '#D8F3DC';
            fb.style.color = '#1B4332';
            fb.innerHTML = '✅ Chính xác! ' + explain;
            btn.style.background = '#1B4332';
            btn.style.color = '#FFFFFF';
        } else {
            fb.style.background = '#FFEBEE';
            fb.style.color = '#B71C1C';
            fb.innerHTML = '❌ Chưa đúng — Gợi ý: ' + explain;
        }
    };
}
</script>
HTML;
}

/**
 * Section 3 Builder: Interactive Click-to-Flip IPM Pest Flashcards + VietGAP QR Traceability Accordion.
 */
function mamxanh_build_flashcards_accordion_html(string $code, array $cards, array $accordions): string {
    $uid = preg_replace('/[^a-z0-9]/i', '', $code);
    $cardsHtml = '';

    foreach ($cards as $idx => $c) {
        $cid = "{$uid}_fc_{$idx}";
        $cardsHtml .= '<div class="col-12 col-md-6 mb-3">'
            . '<div id="' . $cid . '" style="background:#FFFFFF;border:2px solid #2E7D32;border-radius:20px;padding:20px;min-height:220px;display:flex;flex-direction:column;justify-content:space-between;cursor:pointer;box-shadow:0 6px 18px rgba(27,67,50,0.07);" onclick="mxFlipCard(\'' . $cid . '\')">'
            . '<div class="mx-fc-front">'
            . '<span style="background:#FFF8E1;color:#E65100;padding:4px 12px;border-radius:999px;font-weight:800;font-size:0.8rem;">🔍 MẶT TRƯỚC: NHẬN DIỆN TRIỆU CHỨNG</span>'
            . '<h4 style="color:#1B4332;font-weight:800;margin:12px 0 8px;">' . $c['icon'] . ' ' . htmlspecialchars($c['front_title']) . '</h4>'
            . '<p style="color:#37474F;line-height:1.6;margin:0;">' . htmlspecialchars($c['front_symptom']) . '</p>'
            . '</div>'
            . '<div class="mx-fc-back" style="display:none;background:#F1F8E9;border-radius:14px;padding:14px;">'
            . '<span style="background:#1B4332;color:#FFF;padding:4px 12px;border-radius:999px;font-weight:800;font-size:0.8rem;">🛡️ MẶT SAU: PHÁC ĐỒ SINH HỌC IPM</span>'
            . '<h4 style="color:#1B4332;font-weight:800;margin:10px 0 6px;">' . htmlspecialchars($c['front_title']) . '</h4>'
            . '<div style="color:#14281D;line-height:1.6;font-size:0.95rem;">' . $c['back_protocol'] . '</div>'
            . '</div>'
            . '<div class="mt-3 text-end">'
            . '<span class="btn btn-sm btn-outline-success" style="min-height:40px;display:inline-flex;align-items:center;border-radius:10px;font-weight:800;">🔄 Chạm để lật thẻ xem phác đồ</span>'
            . '</div>'
            . '</div>'
            . '</div>';
    }

    $accHtml = '';
    foreach ($accordions as $ai => $acc) {
        $aid = "{$uid}_acc_{$ai}";
        $open = ($ai === 0) ? 'block' : 'none';
        $accHtml .= '<div style="border:2px solid #C8E6C9;border-radius:16px;margin-bottom:10px;overflow:hidden;background:#FFFFFF;">'
            . '<button type="button" onclick="mxToggleAcc(\'' . $aid . '\')" style="width:100%;min-height:52px;background:#E8F5E9;border:none;padding:12px 18px;text-align:left;font-weight:800;color:#1B4332;display:flex;justify-content:space-between;align-items:center;">'
            . '<span>' . htmlspecialchars($acc['title']) . '</span>'
            . '<span>▾</span>'
            . '</button>'
            . '<div id="' . $aid . '" style="display:' . $open . ';padding:16px 18px;line-height:1.65;color:#263238;">'
            . $acc['body']
            . '</div>'
            . '</div>';
    }

    return <<<HTML
<div class="mx-flashcards-module">
    <div style="margin-bottom:24px;">
        <h3 style="color:#1B4332;font-weight:800;font-size:1.3rem;margin-bottom:8px;">
            🃏 Thẻ Lật Tương Tác (Flashcards): Nhận diện Sâu bệnh & Phác đồ Sinh học IPM
        </h3>
        <p style="color:#4E6358;margin-bottom:16px;">Chạm vào từng thẻ bệnh hại bên dưới để lật sang mặt sau xem phác đồ xử lý sinh học chuẩn VietGAP:</p>
        <div class="row">
            {$cardsHtml}
        </div>
    </div>

    <div style="background:#F4FBF6;border:2px solid #B7E4C7;border-radius:22px;padding:20px;">
        <h3 style="color:#1B4332;font-weight:800;font-size:1.25rem;margin-bottom:14px;">
            📲 Cẩm nang Mở rộng (Accordion): Thực hành Truy xuất Nguồn gốc Mã QR VietGAP
        </h3>
        {$accHtml}
        <div class="mt-3">
            <a href="/local/agriops/index.php" class="btn btn-success" style="min-height:48px;display:inline-flex;align-items:center;border-radius:14px;font-weight:800;background:#1B4332;">
                📒 Mở Sổ Nhật ký Điện tử & Tạo Mã QR VietGAP Thực tế →
            </a>
        </div>
    </div>
</div>

<script>
if (!window.mxFlipCard) {
    window.mxFlipCard = function(id) {
        var box = document.getElementById(id);
        if (!box) return;
        var f = box.querySelector('.mx-fc-front');
        var b = box.querySelector('.mx-fc-back');
        if (b.style.display === 'none') {
            f.style.display = 'none';
            b.style.display = 'block';
        } else {
            b.style.display = 'none';
            f.style.display = 'block';
        }
    };
}
if (!window.mxToggleAcc) {
    window.mxToggleAcc = function(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = (el.style.display === 'none') ? 'block' : 'none';
    };
}
</script>
HTML;
}
