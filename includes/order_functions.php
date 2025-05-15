<?php
/**
 * Order management functions for FreshLink Management System
 */

/**
 * Get order status label with appropriate color class
 * 
 * @param string $status The order status
 * @return array Array with label and color class
 */
function get_order_status_label($status) {
    switch ($status) {
        case 'pending':
            return [
                'label' => 'Pending',
                'class' => 'bg-yellow-100 text-yellow-800'
            ];
        case 'processing':
            return [
                'label' => 'Processing',
                'class' => 'bg-blue-100 text-blue-800'
            ];
        case 'shipped':
            return [
                'label' => 'Shipped',
                'class' => 'bg-indigo-100 text-indigo-800'
            ];
        case 'delivered':
            return [
                'label' => 'Delivered',
                'class' => 'bg-green-100 text-green-800'
            ];
        case 'cancelled':
            return [
                'label' => 'Cancelled',
                'class' => 'bg-red-100 text-red-800'
            ];
        default:
            return [
                'label' => ucfirst($status),
                'class' => 'bg-gray-100 text-gray-800'
            ];
    }
}

/**
 * Get payment method label
 * 
 * @param string $method The payment method
 * @return string Formatted payment method label
 */
function get_payment_method_label($method) {
    switch ($method) {
        case 'credit_card':
            return 'Credit Card';
        case 'paypal':
            return 'PayPal';
        case 'bank_transfer':
            return 'Bank Transfer';
        case 'cash_on_delivery':
            return 'Cash on Delivery';
        default:
            return ucwords(str_replace('_', ' ', $method));
    }
}

/**
 * Get user's orders
 * 
 * @param int $user_id The user ID
 * @param string $role The user role
 * @return array Array of orders
 */
function get_user_orders($user_id, $role) {
    global $pdo;
    
    if ($role === 'customer') {
        // For customers: Get their orders
        $stmt = $pdo->prepare("
            SELECT o.*, COUNT(oi.id) as item_count 
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.customer_id = ? 
            GROUP BY o.id 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$user_id]);
    } else if ($role === 'seller') {
        // For sellers: Get orders containing their products
        $stmt = $pdo->prepare("
            SELECT o.*, COUNT(DISTINCT oi.id) as item_count, u.username as customer_name
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id 
            JOIN products p ON oi.product_id = p.id 
            JOIN users u ON o.customer_id = u.id
            WHERE p.seller_id = ? 
            GROUP BY o.id 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$user_id]);
    } else if ($role === 'admin') {
        // For admins: Get all orders
        $stmt = $pdo->query("
            SELECT o.*, COUNT(oi.id) as item_count, u.username as customer_name
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            JOIN users u ON o.customer_id = u.id
            GROUP BY o.id 
            ORDER BY o.created_at DESC
        ");
    }
    
    return $stmt->fetchAll();
}

/**
 * Get order details
 * 
 * @param int $order_id The order ID
 * @param int $user_id The user ID
 * @return array|bool Order details or false if not found
 */
function get_order_details($order_id, $user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.customer_id = u.id 
        WHERE o.id = ? AND o.customer_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    return $stmt->fetch();
}

/**
 * Get order items
 * 
 * @param int $order_id The order ID
 * @return array Array of order items
 */
function get_order_items($order_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name, p.category, p.image_url 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}
?>