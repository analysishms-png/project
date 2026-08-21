<?php
/**
 * Generate PWA icons from SVG
 * Run: php .ai/generate_pwa_icons.php
 */

$svgPath = __DIR__ . '/../public/admin/images/pwa-icon.svg';
$output192 = __DIR__ . '/../public/admin/images/pwa-192.png';
$output512 = __DIR__ . '/../public/admin/images/pwa-512.png';

if (!file_exists($svgPath)) {
    echo "SVG not found at: $svgPath\n";
    exit(1);
}

// Check if Imagick is available
if (class_exists('Imagick')) {
    echo "Using Imagick...\n";
    
    $imagick = new Imagick();
    $imagick->readImage($svgPath);
    
    // Generate 192x192
    $imagick192 = clone $imagick;
    $imagick192->resizeImage(192, 192, Imagick::FILTER_LANCZOS, 1);
    $imagick192->writeImage($output192);
    echo "Created: $output192\n";
    
    // Generate 512x512
    $imagick512 = clone $imagick;
    $imagick512->resizeImage(512, 512, Imagick::FILTER_LANCZOS, 1);
    $imagick512->writeImage($output512);
    echo "Created: $output512\n";
    
    $imagick->destroy();
} else {
    echo "Imagick not available. Creating fallback PNGs using GD...\n";
    
    // Create gradient PNG as fallback
    foreach ([192, 512] as $size) {
        $img = imagecreatetruecolor($size, $size);
        
        // Gradient from #1e3a5f to #2563eb
        for ($y = 0; $y < $size; $y++) {
            $ratio = $y / $size;
            $r = (int)(30 + ($ratio * 7));   // 30 -> 37
            $g = (int)(58 + ($ratio * 41));  // 58 -> 99
            $b = (int)(95 + ($ratio * 138)); // 95 -> 233
            $color = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $size, $y, $color);
        }
        
        // Draw hotel shape
        $white = imagecolorallocatealpha($img, 255, 255, 255, 40);
        $brightWhite = imagecolorallocatealpha($img, 255, 255, 255, 20);
        $gold = imagecolorallocate($img, 251, 191, 36);
        $whiteSolid = imagecolorallocate($img, 255, 255, 255);
        $darkBlue = imagecolorallocate($img, 30, 58, 95);
        
        $cx = $size / 2;
        $scale = $size / 512;
        
        // Building
        $bx = $cx - 100 * $scale;
        $by = 140 * $scale;
        $bw = 200 * $scale;
        $bh = 260 * $scale;
        imagefilledrectangle($img, $bx, $by, $bx + $bw, $by + $bh, $white);
        
        // Windows (3x3 grid)
        $winSize = 36 * $scale;
        $winGap = 14 * $scale;
        $startX = $cx - 64 * $scale;
        $startY = 160 * $scale;
        for ($row = 0; $row < 3; $row++) {
            for ($col = 0; $col < 3; $col++) {
                $wx = $startX + $col * ($winSize + $winGap);
                $wy = $startY + $row * ($winSize + $winGap);
                $alpha = ($row == 1 && $col == 1) ? 50 : 25;
                $winColor = imagecolorallocatealpha($img, 255, 255, 255, $alpha);
                imagefilledrectangle($img, $wx, $wy, $wx + $winSize, $wy + $winSize, $winColor);
            }
        }
        
        // Door
        $dw = 52 * $scale;
        $dh = 60 * $scale;
        $dx = $cx - $dw / 2;
        $dy = 320 * $scale;
        imagefilledrectangle($img, $dx, $dy, $dx + $dw, $dy + $dh, $whiteSolid);
        imagefilledellipse($img, $dx + $dw - 12 * $scale, $dy + $dh / 2, 8 * $scale, 8 * $scale, $darkBlue);
        
        // Star
        $starY = 108 * $scale;
        $starSize = 28 * $scale;
        drawStar($img, $cx, $starY, 5, $starSize, $starSize / 2, $gold);
        
        // HMS text
        $fontSize = (int)(36 * $scale);
        $font = 5; // Bold
        $text = 'HMS';
        $tw = imagefontwidth($font) * strlen($text);
        $th = imagefontheight($font);
        $tx = $cx - $tw / 2;
        $ty = 440 * $scale - $th / 2;
        imagestring($img, $font, $tx, $ty, $text, $whiteSolid);
        
        // Rounded corners (approximate)
        $cornerRadius = 108 * $scale;
        $rounded = applyRoundedCorners($img, $size, $cornerRadius);
        
        $output = $size == 192 ? $output192 : $output512;
        imagepng($rounded, $output);
        imagedestroy($img);
        imagedestroy($rounded);
        echo "Created: $output ($size x $size)\n";
    }
}

echo "Done!\n";

// Helper: Draw a star
function drawStar($img, $cx, $cy, $points, $outerR, $innerR, $color) {
    $vertices = [];
    for ($i = 0; $i < $points * 2; $i++) {
        $angle = ($i * M_PI / $points) - M_PI / 2;
        $r = ($i % 2 == 0) ? $outerR : $innerR;
        $vertices[] = $cx + $r * cos($angle);
        $vertices[] = $cy + $r * sin($angle);
    }
    $n = count($vertices) / 2;
    imagefilledpolygon($img, $vertices, $n, $color);
}

// Helper: Apply rounded corners
function applyRoundedCorners($img, $size, $radius) {
    $rounded = imagecreatetruecolor($size, $size);
    imagecopy($rounded, $img, 0, 0, 0, 0, $size, $size);
    
    // Simple corner masking
    $bg = imagecolorallocatealpha($rounded, 0, 0, 0, 127);
    imagesavealpha($rounded, true);
    
    // Top-left corner
    for ($x = 0; $x < $radius; $x++) {
        for ($y = 0; $y < $radius; $y++) {
            $dist = sqrt(($x - $radius) ** 2 + ($y - $radius) ** 2);
            if ($dist > $radius) {
                imagesetpixel($rounded, $x, $y, $bg);
            }
        }
    }
    // Top-right corner
    for ($x = $size - $radius; $x < $size; $x++) {
        for ($y = 0; $y < $radius; $y++) {
            $dist = sqrt(($x - ($size - $radius)) ** 2 + ($y - $radius) ** 2);
            if ($dist > $radius) {
                imagesetpixel($rounded, $x, $y, $bg);
            }
        }
    }
    // Bottom-left corner
    for ($x = 0; $x < $radius; $x++) {
        for ($y = $size - $radius; $y < $size; $y++) {
            $dist = sqrt(($x - $radius) ** 2 + ($y - ($size - $radius)) ** 2);
            if ($dist > $radius) {
                imagesetpixel($rounded, $x, $y, $bg);
            }
        }
    }
    // Bottom-right corner
    for ($x = $size - $radius; $x < $size; $x++) {
        for ($y = $size - $radius; $y < $size; $y++) {
            $dist = sqrt(($x - ($size - $radius)) ** 2 + ($y - ($size - $radius)) ** 2);
            if ($dist > $radius) {
                imagesetpixel($rounded, $x, $y, $bg);
            }
        }
    }
    
    return $rounded;
}
