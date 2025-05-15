<?php 
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.id as cart_id, c.quantity, p.*, p.price * c.quantity as subtotal, 
           p.stock_quantity as available_stock
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Check if cart is empty
if (count($cart_items) === 0) {
    redirect('cart.php');
}

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['subtotal'];
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = clean_input($_POST['shipping_address']);
    $payment_method = clean_input($_POST['payment_method']);
    
    if (empty($shipping_address)) {
        $error = 'Please enter a shipping address';
    } else if (empty($payment_method)) {
        $error = 'Please select a payment method';
    } else {
        // Check stock availability
        $stock_error = false;
        foreach ($cart_items as $item) {
            if ($item['quantity'] > $item['available_stock']) {
                $error = 'Some items in your cart are out of stock or have insufficient quantity';
                $stock_error = true;
                break;
            }
        }
        
        if (!$stock_error) {
            // Create order using the checkout function
            $order_id = create_order($user_id, $cart_items, $shipping_address, $payment_method);
            
            if ($order_id) {
                // Send order confirmation email
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                $order_items_for_email = get_order_items($order_id);
                send_order_confirmation_email(['id' => $order_id, 'created_at' => date('Y-m-d H:i:s'), 'status' => 'pending', 'payment_method' => $payment_method, 'shipping_address' => $shipping_address, 'total_amount' => $total], $order_items_for_email, $user);
                
                // Redirect to payment page
                redirect(get_correct_path("payment.php") . "?order_id=$order_id");
            } else {
                $error = 'An error occurred while processing your order. Please try again.';
            }
        }
    }
}

// Get user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-green-700 mb-6">Checkout</h1>
    
    <?php if ($error): ?>
        <?= display_error($error) ?>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Order Summary -->
        <div>
            <h2 class="text-xl font-semibold text-green-600 mb-4">Order Summary</h2>
            
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left pb-2">Product</th>
                            <th class="text-right pb-2">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr class="border-t">
                                <td class="py-2">
                                    <?= htmlspecialchars($item['name']) ?> 
                                    <span class="text-gray-600">x <?= $item['quantity'] ?></span>
                                </td>
                                <td class="py-2 text-right">$<?= number_format($item['subtotal'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="border-t">
                            <td class="py-2 font-bold">Total</td>
                            <td class="py-2 text-right font-bold">$<?= number_format($total, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <a href="<?= get_correct_path('cart.php') ?>" class="text-green-600 hover:underline">← Back to Cart</a>
        </div>
        
        <!-- Checkout Form -->
        <div>
            <h2 class="text-xl font-semibold text-green-600 mb-4">Shipping & Payment</h2>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 mb-2">Full Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['username']) ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="shipping_address" class="block text-gray-700 mb-2">Shipping Address</label>
                    <textarea id="shipping_address" name="shipping_address" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required></textarea>
                </div>
                
                <div class="mb-6">
                    <label for="payment_method" class="block text-gray-700 mb-2">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                        <option value="">Select Payment Method</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="paypal">PayPal</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash_on_delivery">Cash on Delivery</option>
                    </select>
                </div>
                
                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Continue to Payment</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>