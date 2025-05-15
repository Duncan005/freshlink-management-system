<?php
// Common functions for the application

// Clean input data
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check user role
function has_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect to a specific page
function redirect($page) {
    // Fix any paths with duplicate admin directories
    if (strpos($page, 'admin/admin/') !== false) {
        $page = str_replace('admin/admin/', 'admin/', $page);
    }
    
    // Use the path helper function if it exists
    if (function_exists('get_correct_path')) {
        $page = get_correct_path($page);
    }
    
    header("Location: $page");
    exit;
}

// Display error message
function display_error($message) {
    return "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>$message</div>";
}

// Display success message
function display_success($message) {
    return "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>$message</div>";
}

// Get cart count for a user
function get_cart_count($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch()['count'];
}

// Get cart total for a user
function get_cart_total($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT SUM(p.price * c.quantity) as total 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['total'] ?: 0;
}
?>