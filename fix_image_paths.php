<?php
// Fix image paths to use relative paths
require_once __DIR__ . '/config/database.php';

// Update product image paths to use relative paths
$stmt = $pdo->query("
    UPDATE products 
    SET image_url = CONCAT('/assets/images/products/', SUBSTRING_INDEX(image_url, '/', -1))
    WHERE image_url LIKE '%/assets/images/products/%'
");

echo "Image paths fixed to use relative paths!\n";
?>