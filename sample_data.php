<?php
// Sample data script for FreshLink Management System
require_once __DIR__ . '/config/database.php';

// Check if we have sellers
$stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'seller' LIMIT 2");
$sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no sellers, create some
if (count($sellers) < 2) {
    // Create two seller accounts
    $password_hash = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'seller')");
    
    // First seller
    if (!$stmt->execute(['FarmFresh', 'farmfresh@example.com', $password_hash])) {
        echo "Error creating seller 1\n";
    } else {
        $sellers[] = ['id' => $pdo->lastInsertId(), 'username' => 'FarmFresh'];
        echo "Created seller: FarmFresh\n";
    }
    
    // Second seller
    if (!$stmt->execute(['OrganicHarvest', 'organic@example.com', $password_hash])) {
        echo "Error creating seller 2\n";
    } else {
        $sellers[] = ['id' => $pdo->lastInsertId(), 'username' => 'OrganicHarvest'];
        echo "Created seller: OrganicHarvest\n";
    }
}

// Sample products data
$products = [
    [
        'name' => 'Fresh Organic Tomatoes',
        'description' => 'Vine-ripened organic tomatoes grown without pesticides. Perfect for salads, sandwiches, or cooking.',
        'category' => 'Vegetables',
        'price' => 3.99,
        'stock_quantity' => 50,
        'image_url' => '/assets/images/products/tomatoes.jpg'
    ],
    [
        'name' => 'Crisp Red Apples',
        'description' => 'Sweet and crisp red apples freshly picked from our orchard. Rich in flavor and nutrients.',
        'category' => 'Fruits',
        'price' => 4.50,
        'stock_quantity' => 75,
        'image_url' => '/assets/images/products/apples.jpg'
    ],
    [
        'name' => 'Farm Fresh Eggs',
        'description' => 'Free-range eggs from pasture-raised chickens. Higher in omega-3 fatty acids and vitamin E.',
        'category' => 'Dairy & Eggs',
        'price' => 5.99,
        'stock_quantity' => 30,
        'image_url' => '/assets/images/products/eggs.jpg'
    ],
    [
        'name' => 'Organic Baby Spinach',
        'description' => 'Tender baby spinach leaves, perfect for salads or cooking. Packed with iron and vitamins.',
        'category' => 'Vegetables',
        'price' => 3.49,
        'stock_quantity' => 40,
        'image_url' => '/assets/images/products/spinach.jpg'
    ],
    [
        'name' => 'Raw Wildflower Honey',
        'description' => 'Pure, unfiltered wildflower honey collected from local beehives. Natural sweetener with health benefits.',
        'category' => 'Honey & Preserves',
        'price' => 8.99,
        'stock_quantity' => 25,
        'image_url' => '/assets/images/products/honey.jpg'
    ],
    [
        'name' => 'Organic Carrots',
        'description' => 'Sweet and crunchy organic carrots, freshly harvested. Great for snacking, cooking, or juicing.',
        'category' => 'Vegetables',
        'price' => 2.99,
        'stock_quantity' => 60,
        'image_url' => '/assets/images/products/carrots.jpg'
    ],
    [
        'name' => 'Fresh Strawberries',
        'description' => 'Juicy, sweet strawberries picked at peak ripeness. Perfect for desserts or eating fresh.',
        'category' => 'Fruits',
        'price' => 4.99,
        'stock_quantity' => 35,
        'image_url' => '/assets/images/products/strawberries.jpg'
    ],
    [
        'name' => 'Artisanal Goat Cheese',
        'description' => 'Creamy, tangy goat cheese made in small batches from our own goat milk. Great for salads or cheese boards.',
        'category' => 'Dairy & Eggs',
        'price' => 6.99,
        'stock_quantity' => 20,
        'image_url' => '/assets/images/products/goat_cheese.jpg'
    ],
    [
        'name' => 'Fresh Green Beans',
        'description' => 'Crisp, tender green beans harvested daily. Versatile for many recipes or steamed as a side dish.',
        'category' => 'Vegetables',
        'price' => 3.29,
        'stock_quantity' => 45,
        'image_url' => '/assets/images/products/green_beans.jpg'
    ],
    [
        'name' => 'Organic Blueberries',
        'description' => 'Sweet, plump blueberries grown organically. Packed with antioxidants and perfect for snacking.',
        'category' => 'Fruits',
        'price' => 5.49,
        'stock_quantity' => 30,
        'image_url' => '/assets/images/products/blueberries.jpg'
    ]
];

// Insert products
$stmt = $pdo->prepare("
    INSERT INTO products (seller_id, name, description, category, price, stock_quantity, image_url) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

// Check if products already exist
$check = $pdo->query("SELECT COUNT(*) as count FROM products");
$product_count = $check->fetch(PDO::FETCH_ASSOC)['count'];

if ($product_count < 10) {
    foreach ($products as $index => $product) {
        // Alternate between sellers
        $seller_id = $sellers[$index % count($sellers)]['id'];
        
        if (!$stmt->execute([
            $seller_id,
            $product['name'],
            $product['description'],
            $product['category'],
            $product['price'],
            $product['stock_quantity'],
            $product['image_url']
        ])) {
            echo "Error adding product: {$product['name']}\n";
        } else {
            echo "Added product: {$product['name']}\n";
        }
    }
    
    echo "Sample products added successfully!\n";
} else {
    echo "Products already exist in the database. Skipping sample data insertion.\n";
}

echo "Done!\n";
?>