<?php
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php?redirect=products.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Process add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];
    
    if ($quantity <= 0) {
        $error = 'Quantity must be greater than 0';
    } else {
        try {
            // Check if product exists and has enough stock
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND stock_quantity >= ?");
            $stmt->execute([$product_id, $quantity]);
            $product = $stmt->fetch();
            
            if (!$product) {
                $error = 'Product not available or insufficient stock';
            } else {
                // Check if product already in cart
                $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
                $cart_item = $stmt->fetch();
                
                if ($cart_item) {
                    // Update quantity
                    $new_quantity = $cart_item['quantity'] + $quantity;
                    
                    // Check if new quantity exceeds stock
                    if ($new_quantity > $product['stock_quantity']) {
                        $error = 'Cannot add more of this item (exceeds available stock)';
                    } else {
                        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                        $stmt->execute([$new_quantity, $cart_item['id']]);
                        $success = 'Cart updated successfully';
                    }
                } else {
                    // Add new item to cart
                    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->execute([$user_id, $product_id, $quantity]);
                    $success = 'Product added to cart';
                }
            }
        } catch (PDOException $e) {
            $error = 'Failed to add product to cart';
        }
    }
}

// Redirect back to products page or cart
if ($error) {
    $_SESSION['error'] = $error;
} else if ($success) {
    $_SESSION['success'] = $success;
}

// Redirect to cart if coming from cart page, otherwise back to products
$redirect = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'cart.php') !== false ? 'cart.php' : 'products.php';
redirect($redirect);
?>