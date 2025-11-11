<?php
/**
 * Generate PWA Icons
 * Run this script once to generate all required PWA icons
 * Usage: php generate-icons.php
 */

// Check if GD extension is available
if (!extension_loaded('gd')) {
    die("GD extension is required. Please install php-gd extension.\n");
}

// Create images directory if it doesn't exist
$imagesDir = __DIR__ . '/images';
if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0755, true);
}

// Icon sizes required
$sizes = [192, 512];

// Theme color (orange)
$backgroundColor = [249, 115, 22]; // #f97316
$textColor = [255, 255, 255]; // White

foreach ($sizes as $size) {
    // Create image
    $image = imagecreatetruecolor($size, $size);
    
    // Allocate colors
    $bg = imagecolorallocate($image, $backgroundColor[0], $backgroundColor[1], $backgroundColor[2]);
    $text = imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);
    
    // Fill background
    imagefilledrectangle($image, 0, 0, $size, $size, $bg);
    
    // Draw text "DTS"
    $fontSize = $size / 3;
    $font = 5; // Use built-in font (you can use imageloadfont for custom fonts)
    
    // Calculate text position (center)
    $textWidth = imagefontwidth($font) * strlen('DTS');
    $textHeight = imagefontheight($font);
    $x = ($size - $textWidth) / 2;
    $y = ($size - $textHeight) / 2;
    
    // Draw text
    imagestring($image, $font, $x, $y, 'DTS', $text);
    
    // Save image
    $filename = $imagesDir . "/icon-{$size}x{$size}.png";
    imagepng($image, $filename);
    imagedestroy($image);
    
    echo "Generated: {$filename}\n";
}

echo "All icons generated successfully!\n";
echo "You can now delete this file (generate-icons.php) if you want.\n";

