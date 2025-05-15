<?php
// Create placeholder hero images
$width = 1920;
$height = 1080;
$colors = [
    'farm1.png' => [76, 175, 80],    // Green
    'farm2.png' => [46, 125, 50],    // Dark Green
    'farm3.png' => [27, 94, 32]      // Darker Green
];

foreach ($colors as $filename => $rgb) {
    $image = imagecreatetruecolor($width, $height);
    $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
    imagefill($image, 0, 0, $color);
    
    // Add text
    $white = imagecolorallocate($image, 255, 255, 255);
    $text = "FreshLink - " . pathinfo($filename, PATHINFO_FILENAME);
    $font_size = 5;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_x = ($width - $text_width) / 2;
    $text_y = $height / 2;
    imagestring($image, $font_size, $text_x, $text_y, $text, $white);
    
    // Save the image
    $image_path = __DIR__ . '/assets/images/hero/' . $filename;
    imagepng($image, $image_path);
    imagedestroy($image);
    
    echo "Created hero image: $filename\n";
}

echo "All hero images created successfully!\n";
?>