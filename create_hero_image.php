<?php
// Create a fallback hero image
$width = 1920;
$height = 1080;
$image = imagecreatetruecolor($width, $height);

// Green color (similar to Tailwind's green-600)
$green = imagecolorallocate($image, 34, 197, 94);
imagefill($image, 0, 0, $green);

// Add some text
$white = imagecolorallocate($image, 255, 255, 255);
$text = "FreshLink - Farm Fresh Products";
$font_size = 5;
$text_width = imagefontwidth($font_size) * strlen($text);
$text_x = ($width - $text_width) / 2;
$text_y = $height / 2;
imagestring($image, $font_size, $text_x, $text_y, $text, $white);

// Save the image
$image_path = __DIR__ . '/assets/images/hero/farm-hero-fallback.jpg';
imagejpeg($image, $image_path, 90);
imagedestroy($image);

echo "Hero image created at: $image_path";
?>