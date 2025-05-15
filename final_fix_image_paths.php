<?php
// Final fix for image paths
require_once __DIR__ . '/config/database.php';

// Update product image paths to use correct relative paths
$stmt = $pdo->query("
    UPDATE products 
    SET image_url = CONCAT('/assets/images/products/', 
        CASE 
            WHEN image_url LIKE '%tomatoes.jpg' THEN 'tomatoes.jpg'
            WHEN image_url LIKE '%apples.jpg' THEN 'apples.jpg'
            WHEN image_url LIKE '%eggs.jpg' THEN 'eggs.jpg'
            WHEN image_url LIKE '%spinach.jpg' THEN 'spinach.jpg'
            WHEN image_url LIKE '%honey.jpg' THEN 'honey.jpg'
            WHEN image_url LIKE '%carrots.jpg' THEN 'carrots.jpg'
            WHEN image_url LIKE '%strawberries.jpg' THEN 'strawberries.jpg'
            WHEN image_url LIKE '%goat_cheese.jpg' THEN 'goat_cheese.jpg'
            WHEN image_url LIKE '%green_beans.jpg' THEN 'green_beans.jpg'
            WHEN image_url LIKE '%blueberries.jpg' THEN 'blueberries.jpg'
            ELSE SUBSTRING_INDEX(image_url, '/', -1)
        END
    )
");

echo "Image paths fixed correctly!\n";
?>