#!/usr/bin/env bash
set -euo pipefail

MEDIA_DIR="/repo/moodle_custom/local/mamxanhapp/media/videos"
PDFJS_DIR="/repo/moodle_custom/local/mamxanhapp/assets/pdfjs"
mkdir -p "$MEDIA_DIR" "$PDFJS_DIR" /tmp/raw_vids

echo "=== [1/3] Downloading self-hosted PDF.js 3.11.174 UMD bundle ==="
curl -fsSL --retry 3 -o "$PDFJS_DIR/pdf.min.js" "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"
curl -fsSL --retry 3 -o "$PDFJS_DIR/pdf.worker.min.js" "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js"
ls -lh "$PDFJS_DIR"

echo "=== [2/3] Installing ffmpeg in ephemeral builder container ==="
apt-get update -qq && apt-get install -y --no-install-recommends ffmpeg >/dev/null

UA="MamXanhLMS/2.0 (https://moodle.lvh.me; educational-thesis)"

download_and_encode() {
    local name="$1"
    local url="$2"
    local start_sec="$3"
    local dur_sec="$4"

    echo " -> Fetching $name from Wikimedia Commons..."
    curl -fsSL --retry 3 -A "$UA" -o "/tmp/raw_vids/${name}.webm" "$url"

    echo " -> Encoding ${name}.mp4 (H.264 yuv420p +faststart) & ${name}.webm & ${name}.jpg..."
    ffmpeg -y -ss "$start_sec" -t "$dur_sec" -i "/tmp/raw_vids/${name}.webm" \
        -vf "scale='min(854,iw)':-2" \
        -c:v libx264 -preset fast -crf 25 -pix_fmt yuv420p -movflags +faststart \
        -an "$MEDIA_DIR/${name}.mp4" </dev/null >/dev/null 2>&1

    ffmpeg -y -ss "$start_sec" -t "$dur_sec" -i "/tmp/raw_vids/${name}.webm" \
        -vf "scale='min(854,iw)':-2" \
        -c:v libvpx-vp9 -crf 33 -b:v 0 -an "$MEDIA_DIR/${name}.webm" </dev/null >/dev/null 2>&1

    ffmpeg -y -ss 2 -i "$MEDIA_DIR/${name}.mp4" -frames:v 1 -q:v 3 "$MEDIA_DIR/${name}.jpg" </dev/null >/dev/null 2>&1
}

download_and_encode "chamsoc_fertigation" \
    "https://upload.wikimedia.org/wikipedia/commons/transcoded/7/70/Drip_irrigation.ogv/Drip_irrigation.ogv.480p.vp9.webm" \
    "0" "24"

download_and_encode "chebien_postharvest" \
    "https://upload.wikimedia.org/wikipedia/commons/transcoded/9/95/Harvest_%2820181008-FPAC-KLS-0002%29.webm/Harvest_%2820181008-FPAC-KLS-0002%29.webm.480p.vp9.webm" \
    "2" "24"

download_and_encode "nhamang_hydroponics" \
    "https://upload.wikimedia.org/wikipedia/commons/transcoded/7/70/Urban_hydroponic_farms_offer_sustainable_water_solutions.webm/Urban_hydroponic_farms_offer_sustainable_water_solutions.webm.480p.vp9.webm" \
    "4" "24"

download_and_encode "visinh_compost" \
    "https://upload.wikimedia.org/wikipedia/commons/transcoded/7/73/Steaming_compost_in_Nymans_Garden.webm/Steaming_compost_in_Nymans_Garden.webm.480p.vp9.webm" \
    "1" "24"

echo "=== [3/3] Media assets ready ==="
ls -lh "$MEDIA_DIR"
