<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Start session for authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get cart items
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT c.id as cart_id, c.quantity, p.*, p.price * c.quantity as subtotal 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll();
        
        // Calculate total
        $total = 0;
        foreach ($cart_items as $item) {
            $total += $item['subtotal'];
        }
        
        echo json_encode([
            'status' => 'success', 
            'data' => [
                'items' => $cart_items,
                'total' => $total,
                'count' => count($cart_items)
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch cart items']);
    }
}

// Add item to cart
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['product_id']) || !isset($data['quantity'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }
    
    $product_id = (int) $data['product_id'];
    $quantity = (int) $data['quantity'];
    
    if ($quantity <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Quantity must be greater than 0']);
        exit;
    }
    
    try {
        // Check if product exists and has enough stock
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND stock_quantity >= ?");
        $stmt->execute([$product_id, $quantity]);
        $product = $stmt->fetch();
        
        if (!$product) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Product not available or insufficient stock']);
            exit;
        }
        
        // Check if product already in cart
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $cart_item = $stmt->fetch();
        
        if ($cart_item) {
            // Update quantity
            $new_quantity = $cart_item['quantity'] + $quantity;
            
            // Check if new quantity exceeds stock
            if ($new_quantity > $product['stock_quantity']) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Cannot add more of this item (exceeds available stock)']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $cart_item['id']]);
        } else {
            // Add new item to cart
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }
        
        // Get updated cart count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $count = $stmt->fetch()['count'];
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Product added to cart',
            'cart_count' => $count
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to add product to cart']);
    }
}

// Update cart item
else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['cart_id']) || !isset($data['quantity'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }
    
    $cart_id = (int) $data['cart_id'];
    $quantity = (int) $data['quantity'];
    
    try {
        if ($quantity <= 0) {
            // Remove item if quantity is 0 or less
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user_id]);
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Item removed from cart'
            ]);
        } else {
            // Get product info to check stock
            $stmt = $pdo->prepare("
                SELECT p.stock_quantity, c.product_id 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.id = ? AND c.user_id = ?
            ");
            $stmt->execute([$cart_id, $user_id]);
            $item = $stmt->fetch();
            
            if (!$item) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Cart item not found']);
                exit;
            }
            
            // Check if quantity exceeds stock
            if ($quantity > $item['stock_quantity']) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Cannot add more of this item (exceeds available stock)']);
                exit;
            }
            
            // Update quantity
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$quantity, $cart_id, $user_id]);
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Cart updated successfully'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update cart']);
    }
}

// Delete cart item or clear cart
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Get cart item ID from URL
    $cart_id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    
    try {
        if ($cart_id) {
            // Remove specific item
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user_id]);
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Item removed from cart'
            ]);
        } else {
            // Clear entire cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Cart cleared successfully'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove item from cart']);
    }
}

// Invalid request method
else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>