<?php
// Update image paths to use the correct base URL
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/path_helper.php';

// Get base URL
$base_url = get_base_url();

// Update product image paths
$stmt = $pdo->prepare("
    UPDATE products 
    SET image_url = CONCAT(?, SUBSTRING(image_url, 2)) 
    WHERE image_url LIKE '/%'
");

$stmt->execute([$base_url]);

echo "Image paths updated successfully!\n";
?>