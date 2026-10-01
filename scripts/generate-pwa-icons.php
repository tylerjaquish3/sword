<?php
// One-off script: generates public/images/icon-192.png and icon-512.png from the
// existing (non-square) logo.png by padding it onto a square navy canvas, rather
// than stretching it. Run once with: php scripts/generate-pwa-icons.php
$sizes = [192, 512];
$source = __DIR__ . '/../public/images/logo.png';
$bg = [0x0e, 0x16, 0x28]; // --sword-navy from resources/css/sword.css

$src = imagecreatefrompng($source);
imagesavealpha($src, true);
$srcW = imagesx($src);
$srcH = imagesy($src);

foreach ($sizes as $size) {
    $canvas = imagecreatetruecolor($size, $size);
    $bgColor = imagecolorallocate($canvas, $bg[0], $bg[1], $bg[2]);
    imagefill($canvas, 0, 0, $bgColor);

    $padding = 0.15; // 15% breathing room on each side
    $maxDim = $size * (1 - $padding * 2);
    $scale = min($maxDim / $srcW, $maxDim / $srcH);
    $destW = (int) round($srcW * $scale);
    $destH = (int) round($srcH * $scale);
    $destX = (int) round(($size - $destW) / 2);
    $destY = (int) round(($size - $destH) / 2);

    imagecopyresampled($canvas, $src, $destX, $destY, 0, 0, $destW, $destH, $srcW, $srcH);
    imagepng($canvas, __DIR__ . "/../public/images/icon-{$size}.png");
    imagedestroy($canvas);
}

imagedestroy($src);
echo "Generated icon-192.png and icon-512.png\n";
