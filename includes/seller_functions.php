<?php
/**
 * Seller functions for FreshLink Management System
 */

/**
 * Check if user is seller
 * 
 * @return bool True if user is seller, false otherwise
 */
function is_seller() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'seller';
}

/**
 * Require seller access
 * 
 * Redirects to login page if user is not logged in or not a seller
 */
function require_seller() {
    if (!is_logged_in() || !is_seller()) {
        redirect('login.php');
    }
}

/**
 * Get seller orders with pagination
 * 
 * @param int $seller_id Seller ID
 * @param int $page Page number
 * @param int $limit Items per page
 * @return array Array of orders
 */
function get_seller_orders($seller_id, $page = 1, $limit = 10) {
    global $pdo;
    
    $offset = ($page - 1) * $limit;
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT o.id, o.customer_id, o.total_amount, o.status, o.created_at, u.username as customer_name
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN users u ON o.customer_id = u.id
        WHERE oi.seller_id = ?
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bindValue(1, $seller_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Get seller order items
 * 
 * @param int $order_id Order ID
 * @param int $seller_id Seller ID
 * @return array Array of order items
 */
function get_seller_order_items($order_id, $seller_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.category
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ? AND oi.seller_id = ?
    ");
    
    $stmt->execute([$order_id, $seller_id]);
    return $stmt->fetchAll();
}

/**
 * Count total seller orders
 * 
 * @param int $seller_id Seller ID
 * @return int Number of orders
 */
function count_seller_orders($seller_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT o.id) as count
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE oi.seller_id = ?
    ");
    
    $stmt->execute([$seller_id]);
    return $stmt->fetch()['count'];
}

/**
 * Ensure order_items has seller_id column
 */
function ensure_order_items_seller_id() {
    global $pdo;
    
    try {
        // Check if column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM order_items LIKE 'seller_id'");
        if ($stmt->rowCount() === 0) {
            // Add column if it doesn't exist
            $pdo->exec("ALTER TABLE order_items ADD COLUMN seller_id INT NOT NULL AFTER product_id");
            $pdo->exec("ALTER TABLE order_items ADD FOREIGN KEY (seller_id) REFERENCES users(id)");
            
            // Update existing order_items with seller_id from products
            $pdo->exec("
                UPDATE order_items oi
                JOIN products p ON oi.product_id = p.id
                SET oi.seller_id = p.seller_id
            ");
        }
    } catch (PDOException $e) {
        // Ignore errors
    }
}
?>