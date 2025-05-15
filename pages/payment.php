<?php 
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    redirect('cart.php');
}

$user_id = $_SESSION['user_id'];
$order_id = (int) $_GET['order_id'];
$error = '';
$success = '';

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.customer_id = u.id 
    WHERE o.id = ? AND o.customer_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

// If order not found or doesn't belong to user
if (!$order) {
    redirect('dashboard.php');
}

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $order['payment_method'];
    
    // Credit card payment
    if ($payment_method === 'credit_card') {
        $card_number = clean_input($_POST['card_number']);
        $card_name = clean_input($_POST['card_name']);
        $card_expiry = clean_input($_POST['card_expiry']);
        $card_cvv = clean_input($_POST['card_cvv']);
        
        // Basic validation
        if (empty($card_number) || empty($card_name) || empty($card_expiry) || empty($card_cvv)) {
            $error = 'Please fill in all credit card details';
        } else if (!preg_match('/^[0-9]{16}$/', str_replace(' ', '', $card_number))) {
            $error = 'Invalid credit card number';
        } else if (!preg_match('/^[0-9]{3,4}$/', $card_cvv)) {
            $error = 'Invalid CVV code';
        } else {
            // In a real application, you would process the payment through a payment gateway
            // For this demo, we'll just update the order status
            try {
                $stmt = $pdo->prepare("UPDATE orders SET status = 'processing' WHERE id = ?");
                $stmt->execute([$order_id]);
                
                // Send payment confirmation email
                send_payment_confirmation_email($order, $user, 'success');
                
                // Redirect to order confirmation
                redirect(get_correct_path("order_confirmation.php") . "?order_id=$order_id&payment=success");
            } catch (PDOException $e) {
                $error = 'Payment processing failed. Please try again.';
            }
        }
    }
    // PayPal payment
    else if ($payment_method === 'paypal') {
        // In a real application, you would redirect to PayPal
        // For this demo, we'll just update the order status
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'processing' WHERE id = ?");
            $stmt->execute([$order_id]);
            
            // Send payment confirmation email
            send_payment_confirmation_email($order, $user, 'success');
            
            // Redirect to order confirmation
            redirect(get_correct_path("order_confirmation.php") . "?order_id=$order_id&payment=success");
        } catch (PDOException $e) {
            $error = 'Payment processing failed. Please try again.';
        }
    }
    // Bank transfer
    else if ($payment_method === 'bank_transfer') {
        // In a real application, you would provide bank details
        // For this demo, we'll just update the order status
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'pending' WHERE id = ?");
            $stmt->execute([$order_id]);
            
            // Send payment confirmation email
            send_payment_confirmation_email($order, $user, 'pending');
            
            // Redirect to order confirmation
            redirect(get_correct_path("order_confirmation.php") . "?order_id=$order_id&payment=pending");
        } catch (PDOException $e) {
            $error = 'Payment processing failed. Please try again.';
        }
    }
    // Cash on delivery
    else if ($payment_method === 'cash_on_delivery') {
        // Update order status
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'processing' WHERE id = ?");
            $stmt->execute([$order_id]);
            
            // Send payment confirmation email
            send_payment_confirmation_email($order, $user, 'cod');
            
            // Redirect to order confirmation
            redirect(get_correct_path("order_confirmation.php") . "?order_id=$order_id&payment=cod");
        } catch (PDOException $e) {
            $error = 'Order processing failed. Please try again.';
        }
    }
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.category 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-green-700 mb-6">Complete Payment</h1>
    
    <?php if ($error): ?>
        <?= display_error($error) ?>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Order Summary -->
        <div>
            <h2 class="text-xl font-semibold text-green-600 mb-4">Order Summary</h2>
            
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <p><strong>Order #:</strong> <?= $order_id ?></p>
                <p><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
                <p><strong>Total:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
                <p><strong>Payment Method:</strong> <?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></p>
                
                <div class="mt-4">
                    <h3 class="font-semibold mb-2">Items:</h3>
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="text-left pb-2">Product</th>
                                <th class="text-right pb-2">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr class="border-t">
                                    <td class="py-2">
                                        <?= htmlspecialchars($item['name']) ?> 
                                        <span class="text-gray-600">x <?= $item['quantity'] ?></span>
                                    </td>
                                    <td class="py-2 text-right">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="border-t">
                                <td class="py-2 font-bold">Total</td>
                                <td class="py-2 text-right font-bold">$<?= number_format($order['total_amount'], 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Payment Form -->
        <div>
            <h2 class="text-xl font-semibold text-green-600 mb-4">Payment Details</h2>
            
            <form method="POST" action="" id="payment-form">
                <?php if ($order['payment_method'] === 'credit_card'): ?>
                    <div class="mb-4">
                        <label for="card_number" class="block text-gray-700 mb-2">Card Number</label>
                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="card_name" class="block text-gray-700 mb-2">Name on Card</label>
                        <input type="text" id="card_name" name="card_name" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="card_expiry" class="block text-gray-700 mb-2">Expiry Date</label>
                            <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                        </div>
                        <div>
                            <label for="card_cvv" class="block text-gray-700 mb-2">CVV</label>
                            <input type="text" id="card_cvv" name="card_cvv" placeholder="123" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                        </div>
                    </div>
                    
                <?php elseif ($order['payment_method'] === 'paypal'): ?>
                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <p class="text-center mb-4">You will be redirected to PayPal to complete your payment.</p>
                        <div class="flex justify-center">
                            <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg" alt="PayPal" class="h-12">
                        </div>
                    </div>
                    
                <?php elseif ($order['payment_method'] === 'bank_transfer'): ?>
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <p class="mb-4">Please transfer the total amount to the following bank account:</p>
                        <p><strong>Bank:</strong> FreshLink Bank</p>
                        <p><strong>Account Name:</strong> FreshLink Inc.</p>
                        <p><strong>Account Number:</strong> 1234567890</p>
                        <p><strong>Reference:</strong> Order #<?= $order_id ?></p>
                        <p class="mt-4 text-sm text-gray-600">Your order will be processed once we receive your payment.</p>
                    </div>
                    
                <?php elseif ($order['payment_method'] === 'cash_on_delivery'): ?>
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <p class="mb-4">You have selected Cash on Delivery as your payment method.</p>
                        <p>Please have the exact amount ready when your order is delivered.</p>
                        <p class="mt-4 text-sm text-gray-600">Total amount to pay: $<?= number_format($order['total_amount'], 2) ?></p>
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    <?php if ($order['payment_method'] === 'credit_card'): ?>
                        Pay Now
                    <?php elseif ($order['payment_method'] === 'paypal'): ?>
                        Continue to PayPal
                    <?php elseif ($order['payment_method'] === 'bank_transfer'): ?>
                        Confirm Bank Transfer
                    <?php elseif ($order['payment_method'] === 'cash_on_delivery'): ?>
                        Confirm Order
                    <?php endif; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="<?= get_base_url() ?>/assets/js/checkout.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>