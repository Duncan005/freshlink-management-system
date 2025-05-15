<?php
/**
 * Admin functions for FreshLink Management System
 */

/**
 * Check if user is admin
 * 
 * @return bool True if user is admin, false otherwise
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require admin access
 * 
 * Redirects to login page if user is not logged in or not an admin
 */
function require_admin() {
    if (!is_logged_in() || !is_admin()) {
        redirect('login.php');
    }
}

/**
 * Get total users count
 * 
 * @param string $role Optional role filter
 * @return int Number of users
 */
function get_users_count($role = null) {
    global $pdo;
    
    $sql = "SELECT COUNT(*) as count FROM users";
    $params = [];
    
    if ($role) {
        $sql .= " WHERE role = ?";
        $params[] = $role;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch()['count'];
}

/**
 * Get total products count
 * 
 * @param int $seller_id Optional seller ID filter
 * @return int Number of products
 */
function get_products_count($seller_id = null) {
    global $pdo;
    
    $sql = "SELECT COUNT(*) as count FROM products";
    $params = [];
    
    if ($seller_id) {
        $sql .= " WHERE seller_id = ?";
        $params[] = $seller_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch()['count'];
}

/**
 * Get total orders count
 * 
 * @param string $status Optional status filter
 * @return int Number of orders
 */
function get_orders_count($status = null) {
    global $pdo;
    
    $sql = "SELECT COUNT(*) as count FROM orders";
    $params = [];
    
    if ($status) {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch()['count'];
}

/**
 * Get total sales amount
 * 
 * @return float Total sales amount
 */
function get_total_sales() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
    $result = $stmt->fetch();
    return $result['total'] ?: 0;
}

/**
 * Get all users with pagination
 * 
 * @param int $page Page number
 * @param int $limit Items per page
 * @return array Array of users
 */
function get_all_users($page = 1, $limit = 10) {
    global $pdo;
    
    $offset = ($page - 1) * $limit;
    
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Get all products with pagination
 * 
 * @param int $page Page number
 * @param int $limit Items per page
 * @return array Array of products
 */
function get_all_products($page = 1, $limit = 10) {
    global $pdo;
    
    $offset = ($page - 1) * $limit;
    
    $stmt = $pdo->prepare("
        SELECT p.*, u.username as seller_name 
        FROM products p 
        JOIN users u ON p.seller_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Update user role
 * 
 * @param int $user_id User ID
 * @param string $role New role
 * @return bool True if successful, false otherwise
 */
function update_user_role($user_id, $role) {
    global $pdo;
    
    $valid_roles = ['customer', 'seller', 'admin'];
    if (!in_array($role, $valid_roles)) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $user_id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Add product approval column if it doesn't exist
 */
function ensure_product_approval_column() {
    global $pdo;
    
    try {
        // Check if column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'approved'");
        if ($stmt->rowCount() === 0) {
            // Add column if it doesn't exist
            $pdo->exec("ALTER TABLE products ADD COLUMN approved TINYINT(1) NOT NULL DEFAULT 1");
        }
    } catch (PDOException $e) {
        // Ignore errors
    }
}

/**
 * Update product approval status
 * 
 * @param int $product_id Product ID
 * @param bool $approved Approval status
 * @return bool True if successful, false otherwise
 */
function update_product_approval($product_id, $approved) {
    global $pdo;
    
    ensure_product_approval_column();
    
    try {
        $stmt = $pdo->prepare("UPDATE products SET approved = ? WHERE id = ?");
        $stmt->execute([$approved ? 1 : 0, $product_id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
?>