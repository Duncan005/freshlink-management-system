<?php 
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    redirect('dashboard.php');
}

$order_id = (int) $_GET['order_id'];

// Get order details
$order = get_order_details($order_id, $user_id);

// If order not found or doesn't belong to user
if (!$order) {
    redirect('dashboard.php');
}

// Check if payment was successful
$payment_status = isset($_GET['payment']) ? $_GET['payment'] : '';
if ($payment_status === 'success') {
    $success = 'Payment processed successfully! Your order is now being prepared.';
} else if ($payment_status === 'pending') {
    $success = 'Your order has been placed! Please complete the bank transfer to process your order.';
} else if ($payment_status === 'cod') {
    $success = 'Your order has been placed! Payment will be collected upon delivery.';
}

// Get order items
$order_items = get_order_items($order_id);
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="text-center mb-8">
        <div class="inline-block p-4 rounded-full bg-green-100 text-green-600 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-green-700">Order Placed Successfully!</h1>
        <p class="text-gray-600">Thank you for your purchase. Your order has been received.</p>
        
        <?php if ($success): ?>
            <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <?= $success ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-green-600 mb-4">Order Details</h2>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <p><strong>Order Number:</strong> #<?= $order_id ?></p>
                    <p><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
                    <p><strong>Status:</strong> 
                        <?php $status = get_order_status_label($order['status']); ?>
                        <span class="px-2 py-1 rounded text-xs <?= $status['class'] ?>">
                            <?= $status['label'] ?>
                        </span>
                    </p>
                </div>
                <div>
                    <p><strong>Total Amount:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
                    <p><strong>Payment Method:</strong> <?= get_payment_method_label($order['payment_method']) ?></p>
                </div>
            </div>
            
            <div class="mb-4">
                <p><strong>Shipping Address:</strong></p>
                <p class="whitespace-pre-line"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
            </div>
        </div>
    </div>
    
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-green-600 mb-4">Order Items</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left">Product</th>
                        <th class="py-2 px-4 text-left">Price</th>
                        <th class="py-2 px-4 text-left">Quantity</th>
                        <th class="py-2 px-4 text-left">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr class="border-t">
                            <td class="py-2 px-4">
                                <div class="flex items-center">
                                    <?php if ($item['image_url']): ?>
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="h-10 w-10 object-cover mr-2">
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-medium"><?= htmlspecialchars($item['name']) ?></div>
                                        <div class="text-sm text-gray-600">Category: <?= htmlspecialchars($item['category']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2 px-4">$<?= number_format($item['price'], 2) ?></td>
                            <td class="py-2 px-4"><?= $item['quantity'] ?></td>
                            <td class="py-2 px-4">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="py-2 px-4 text-right font-bold">Total:</td>
                        <td class="py-2 px-4 font-bold">$<?= number_format($order['total_amount'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="flex justify-between">
        <a href="<?= get_correct_path('dashboard.php') ?>" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Go to Dashboard</a>
        <a href="<?= get_correct_path('products.php') ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Continue Shopping</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>