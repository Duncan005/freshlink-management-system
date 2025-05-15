<?php
// Check products in the database
require_once __DIR__ . '/config/database.php';

// Get products
$stmt = $pdo->query("SELECT id, name, category, price, image_url FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Products in the database:\n\n";
foreach ($products as $product) {
    echo "ID: " . $product['id'] . "\n";
    echo "Name: " . $product['name'] . "\n";
    echo "Category: " . $product['category'] . "\n";
    echo "Price: $" . $product['price'] . "\n";
    echo "Image URL: " . $product['image_url'] . "\n";
    echo "------------------------------\n";
}

echo "\nTotal products: " . count($products) . "\n";
?>