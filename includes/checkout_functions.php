<?php
/**
 * Checkout functions for FreshLink Management System
 */

/**
 * Create order and order items
 * 
 * @param int $user_id User ID
 * @param array $cart_items Cart items
 * @param string $shipping_address Shipping address
 * @param string $payment_method Payment method
 * @return int|bool Order ID if successful, false otherwise
 */
function create_order($user_id, $cart_items, $shipping_address, $payment_method) {
    global $pdo;
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Calculate total
        $total = 0;
        foreach ($cart_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders (customer_id, total_amount, shipping_address, payment_method, status) 
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$user_id, $total, $shipping_address, $payment_method]);
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($cart_items as $item) {
            // Get seller_id from product
            $product_stmt = $pdo->prepare("SELECT seller_id FROM products WHERE id = ?");
            $product_stmt->execute([$item['id']]);
            $seller_id = $product_stmt->fetch()['seller_id'];
            
            // Add to order items
            $stmt->execute([$order_id, $item['id'], $seller_id, $item['quantity'], $item['price']]);
            
            // Update product stock
            $new_stock = $item['available_stock'] - $item['quantity'];
            $update_stmt = $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
            $update_stmt->execute([$new_stock, $item['id']]);
        }
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Commit transaction
        $pdo->commit();
        
        return $order_id;
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        return false;
    }
}
?>