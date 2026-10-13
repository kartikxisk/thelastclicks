#!/usr/bin/env bash
# Prepare a folder of camera masters for upload: cap the long edge and burn in
# the studio lockup.
#
#   scripts/watermark-photos.sh <src-dir> <out-dir>
#
# The stills arrive as 5-7MB A7III/5D frames. Nothing on the site renders wider
# than the lightbox, so they are capped at 2560px on the long edge — and the
# watermark is burned here rather than in the app because the object behind
# CloudFront is public and anonymous: whatever is uploaded is what anyone can
# right-click. works:import-photos uploads verbatim and never processes.
#
# Logo is public/logo.png (the white lockup) at 12% of the frame width, 55%
# opacity, bottom-right on a 4% margin — readable on stage lighting without
# sitting on the performer.
set -euo pipefail

src="${1:?usage: watermark-photos.sh <src-dir> <out-dir>}"
out="${2:?usage: watermark-photos.sh <src-dir> <out-dir>}"
logo="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/public/logo.png"

mkdir -p "$out"

shopt -s nullglob nocaseglob
for f in "$src"/*.jpg "$src"/*.jpeg "$src"/*.png; do
  base="$(basename "${f%.*}").jpg"
  ffmpeg -y -v error -i "$f" -i "$logo" -filter_complex "\
[0:v]scale='if(gt(iw,ih),2560,-2)':'if(gt(iw,ih),-2,2560)'[bg];\
[1:v]format=rgba,colorchannelmixer=aa=0.55[lg];\
[lg][bg]scale2ref=w=iw*0.12:h=ow/mdar[wm][base];\
[base][wm]overlay=W-w-W*0.04:H-h-W*0.04,format=yuvj420p" \
    -q:v 3 "$out/$base"
  echo "watermarked $base"
done
