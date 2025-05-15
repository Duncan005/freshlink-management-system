<?php
// Create placeholder images for products
$image_dir = __DIR__ . '/assets/images/products/';

// Create directory if it doesn't exist
if (!file_exists($image_dir)) {
    mkdir($image_dir, 0777, true);
}

// List of product images
$images = [
    'tomatoes.jpg',
    'apples.jpg',
    'eggs.jpg',
    'spinach.jpg',
    'honey.jpg',
    'carrots.jpg',
    'strawberries.jpg',
    'goat_cheese.jpg',
    'green_beans.jpg',
    'blueberries.jpg'
];

// Create placeholder images
foreach ($images as $image) {
    $img = imagecreatetruecolor(500, 500);
    
    // Set background color based on product
    switch ($image) {
        case 'tomatoes.jpg':
            $bg_color = imagecolorallocate($img, 255, 99, 71); // Tomato red
            break;
        case 'apples.jpg':
            $bg_color = imagecolorallocate($img, 255, 0, 0); // Red
            break;
        case 'eggs.jpg':
            $bg_color = imagecolorallocate($img, 255, 248, 220); // Cream
            break;
        case 'spinach.jpg':
            $bg_color = imagecolorallocate($img, 0, 128, 0); // Green
            break;
        case 'honey.jpg':
            $bg_color = imagecolorallocate($img, 218, 165, 32); // Golden
            break;
        case 'carrots.jpg':
            $bg_color = imagecolorallocate($img, 255, 140, 0); // Orange
            break;
        case 'strawberries.jpg':
            $bg_color = imagecolorallocate($img, 220, 20, 60); // Crimson
            break;
        case 'goat_cheese.jpg':
            $bg_color = imagecolorallocate($img, 245, 245, 245); // White
            break;
        case 'green_beans.jpg':
            $bg_color = imagecolorallocate($img, 50, 205, 50); // Lime green
            break;
        case 'blueberries.jpg':
            $bg_color = imagecolorallocate($img, 65, 105, 225); // Blue
            break;
        default:
            $bg_color = imagecolorallocate($img, 200, 200, 200); // Gray
    }
    
    // Fill background
    imagefill($img, 0, 0, $bg_color);
    
    // Add text
    $text_color = imagecolorallocate($img, 255, 255, 255);
    $product_name = pathinfo($image, PATHINFO_FILENAME);
    $product_name = ucwords(str_replace('_', ' ', $product_name));
    
    // Center text
    $font_size = 5;
    $text_width = imagefontwidth($font_size) * strlen($product_name);
    $text_height = imagefontheight($font_size);
    $x = (500 - $text_width) / 2;
    $y = (500 - $text_height) / 2;
    
    imagestring($img, $font_size, $x, $y, $product_name, $text_color);
    
    // Save image
    imagejpeg($img, $image_dir . $image);
    imagedestroy($img);
    
    echo "Created placeholder image: $image\n";
}

echo "All placeholder images created successfully!\n";
?>