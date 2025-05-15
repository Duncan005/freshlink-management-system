<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Get all products
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT p.*, u.username as seller_name FROM products p JOIN users u ON p.seller_id = u.id ORDER BY p.created_at DESC");
        $products = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $products]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch products']);
    }
}

// Add a new product (requires authentication)
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'seller' && $_SESSION['role'] !== 'admin')) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['name']) || !isset($data['category']) || !isset($data['price'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (seller_id, name, description, category, price, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $data['name'],
            $data['description'] ?? '',
            $data['category'],
            (float) $data['price'],
            (int) ($data['stock_quantity'] ?? 0),
            $data['image_url'] ?? null
        ]);
        
        $product_id = $pdo->lastInsertId();
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Product added successfully',
            'product_id' => $product_id
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to add product']);
    }
}

// Update a product (requires authentication)
else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    session_start();
    
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'seller' && $_SESSION['role'] !== 'admin')) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing product ID']);
        exit;
    }
    
    try {
        // Verify the product belongs to this seller
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$data['id'], $_SESSION['user_id']]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission to update this product']);
            exit;
        }
        
        // Build update query based on provided fields
        $updateFields = [];
        $params = [];
        
        if (isset($data['name'])) {
            $updateFields[] = "name = ?";
            $params[] = $data['name'];
        }
        
        if (isset($data['description'])) {
            $updateFields[] = "description = ?";
            $params[] = $data['description'];
        }
        
        if (isset($data['category'])) {
            $updateFields[] = "category = ?";
            $params[] = $data['category'];
        }
        
        if (isset($data['price'])) {
            $updateFields[] = "price = ?";
            $params[] = (float) $data['price'];
        }
        
        if (isset($data['stock_quantity'])) {
            $updateFields[] = "stock_quantity = ?";
            $params[] = (int) $data['stock_quantity'];
        }
        
        if (isset($data['image_url'])) {
            $updateFields[] = "image_url = ?";
            $params[] = $data['image_url'];
        }
        
        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
            exit;
        }
        
        // Add product ID to params
        $params[] = $data['id'];
        
        $stmt = $pdo->prepare("UPDATE products SET " . implode(", ", $updateFields) . " WHERE id = ?");
        $stmt->execute($params);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Product updated successfully'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update product']);
    }
}

// Delete a product (requires authentication)
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    session_start();
    
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'seller' && $_SESSION['role'] !== 'admin')) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    
    // Get product ID from URL
    $product_id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    
    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing product ID']);
        exit;
    }
    
    try {
        // Verify the product belongs to this seller
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$product_id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission to delete this product']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Product deleted successfully'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete product']);
    }
}

// Invalid request method
else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>