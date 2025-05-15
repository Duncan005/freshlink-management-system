<?php
/**
 * Email functions for FreshLink Management System
 */

/**
 * Send an email
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email message (HTML)
 * @param string $from From email address (optional)
 * @return bool Whether the email was sent successfully
 */
function send_email($to, $subject, $message, $from = 'noreply@freshlink.com') {
    // In a production environment, you would use a proper email sending library
    // like PHPMailer or the mail() function
    
    // For this demo, we'll just log the email
    $log_file = __DIR__ . '/../logs/email.log';
    
    // Create logs directory if it doesn't exist
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0777, true);
    }
    
    $log_message = "==========\n";
    $log_message .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $log_message .= "To: $to\n";
    $log_message .= "From: $from\n";
    $log_message .= "Subject: $subject\n";
    $log_message .= "Message:\n$message\n";
    $log_message .= "==========\n\n";
    
    file_put_contents($log_file, $log_message, FILE_APPEND);
    
    return true;
}

/**
 * Send order confirmation email
 * 
 * @param array $order Order details
 * @param array $order_items Order items
 * @param array $user User details
 * @return bool Whether the email was sent successfully
 */
function send_order_confirmation_email($order, $order_items, $user) {
    $subject = "FreshLink - Order Confirmation #" . $order['id'];
    
    // Build email message
    $message = "<html><body>";
    $message .= "<h1>Order Confirmation</h1>";
    $message .= "<p>Dear " . htmlspecialchars($user['username']) . ",</p>";
    $message .= "<p>Thank you for your order. We are pleased to confirm that your order has been received and is being processed.</p>";
    
    $message .= "<h2>Order Details</h2>";
    $message .= "<p><strong>Order Number:</strong> #" . $order['id'] . "</p>";
    $message .= "<p><strong>Order Date:</strong> " . date('F j, Y, g:i a', strtotime($order['created_at'])) . "</p>";
    $message .= "<p><strong>Payment Method:</strong> " . get_payment_method_label($order['payment_method']) . "</p>";
    $message .= "<p><strong>Order Status:</strong> " . ucfirst($order['status']) . "</p>";
    
    $message .= "<h2>Shipping Address</h2>";
    $message .= "<p>" . nl2br(htmlspecialchars($order['shipping_address'])) . "</p>";
    
    $message .= "<h2>Order Summary</h2>";
    $message .= "<table border='1' cellpadding='5' cellspacing='0' width='100%'>";
    $message .= "<tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr>";
    
    $total = 0;
    foreach ($order_items as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $total += $subtotal;
        
        $message .= "<tr>";
        $message .= "<td>" . htmlspecialchars($item['name']) . "</td>";
        $message .= "<td>" . $item['quantity'] . "</td>";
        $message .= "<td>$" . number_format($item['price'], 2) . "</td>";
        $message .= "<td>$" . number_format($subtotal, 2) . "</td>";
        $message .= "</tr>";
    }
    
    $message .= "<tr><td colspan='3' align='right'><strong>Total:</strong></td><td>$" . number_format($total, 2) . "</td></tr>";
    $message .= "</table>";
    
    $message .= "<p>If you have any questions about your order, please contact our customer service.</p>";
    $message .= "<p>Thank you for shopping with FreshLink!</p>";
    $message .= "</body></html>";
    
    return send_email($user['email'], $subject, $message);
}

/**
 * Send payment confirmation email
 * 
 * @param array $order Order details
 * @param array $user User details
 * @param string $payment_status Payment status
 * @return bool Whether the email was sent successfully
 */
function send_payment_confirmation_email($order, $user, $payment_status) {
    $subject = "FreshLink - Payment Confirmation for Order #" . $order['id'];
    
    // Build email message
    $message = "<html><body>";
    $message .= "<h1>Payment Confirmation</h1>";
    $message .= "<p>Dear " . htmlspecialchars($user['username']) . ",</p>";
    
    if ($payment_status === 'success') {
        $message .= "<p>Thank you for your payment. We are pleased to confirm that your payment for order #" . $order['id'] . " has been successfully processed.</p>";
        $message .= "<p>Your order is now being prepared for shipping.</p>";
    } else if ($payment_status === 'pending') {
        $message .= "<p>Thank you for your order #" . $order['id'] . ". We are waiting to receive your bank transfer payment.</p>";
        $message .= "<p>Please transfer the total amount of $" . number_format($order['total_amount'], 2) . " to the following bank account:</p>";
        $message .= "<p><strong>Bank:</strong> FreshLink Bank<br>";
        $message .= "<strong>Account Name:</strong> FreshLink Inc.<br>";
        $message .= "<strong>Account Number:</strong> 1234567890<br>";
        $message .= "<strong>Reference:</strong> Order #" . $order['id'] . "</p>";
        $message .= "<p>Your order will be processed once we receive your payment.</p>";
    } else if ($payment_status === 'cod') {
        $message .= "<p>Thank you for your order #" . $order['id'] . ". Your order has been confirmed for Cash on Delivery.</p>";
        $message .= "<p>Please have the exact amount of $" . number_format($order['total_amount'], 2) . " ready when your order is delivered.</p>";
    }
    
    $message .= "<p>If you have any questions about your payment, please contact our customer service.</p>";
    $message .= "<p>Thank you for shopping with FreshLink!</p>";
    $message .= "</body></html>";
    
    return send_email($user['email'], $subject, $message);
}

/**
 * Send shipping confirmation email
 * 
 * @param array $order Order details
 * @param array $user User details
 * @param string $tracking_number Tracking number
 * @return bool Whether the email was sent successfully
 */
function send_shipping_confirmation_email($order, $user, $tracking_number) {
    $subject = "FreshLink - Your Order #" . $order['id'] . " Has Been Shipped";
    
    // Build email message
    $message = "<html><body>";
    $message .= "<h1>Shipping Confirmation</h1>";
    $message .= "<p>Dear " . htmlspecialchars($user['username']) . ",</p>";
    $message .= "<p>Good news! Your order #" . $order['id'] . " has been shipped and is on its way to you.</p>";
    
    $message .= "<h2>Tracking Information</h2>";
    $message .= "<p><strong>Tracking Number:</strong> " . $tracking_number . "</p>";
    $message .= "<p><strong>Carrier:</strong> FreshLink Express</p>";
    $message .= "<p>You can track your order by entering your tracking number on our website.</p>";
    
    $message .= "<h2>Shipping Address</h2>";
    $message .= "<p>" . nl2br(htmlspecialchars($order['shipping_address'])) . "</p>";
    
    $message .= "<p>If you have any questions about your shipment, please contact our customer service.</p>";
    $message .= "<p>Thank you for shopping with FreshLink!</p>";
    $message .= "</body></html>";
    
    return send_email($user['email'], $subject, $message);
}
?>