<?php 
require_once __DIR__ . '/../includes/header.php';

// Require seller access
require_seller();

$user_id = $_SESSION['user_id'];
$error = '';

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    redirect('seller_orders.php');
}

$order_id = (int) $_GET['order_id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username as customer_name, u.email as customer_email
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

// If order not found
if (!$order) {
    redirect('seller_orders.php');
}

// Get seller's items in this order
$order_items = get_seller_order_items($order_id, $user_id);

// Check if seller has items in this order
if (count($order_items) === 0) {
    redirect('seller_orders.php');
}

// Calculate seller's total for this order
$seller_total = 0;
foreach ($order_items as $item) {
    $seller_total += $item['price'] * $item['quantity'];
}
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-green-700">Order #<?= $order_id ?> Details</h1>
        <a href="<?= get_correct_path('seller_orders.php') ?>" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to Orders</a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Order Details -->
        <div>
            <h2 class="text-xl font-semibold text-green-600 mb-4">Order Information</h2>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <p><strong>Order Number:</strong> #<?= $order_id ?></p>
                <p><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
                <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                <p><strong>Status:</strong> 
                    <?php $status = get_order_status_label($order['status']); ?>
                    <span class="px-2 py-1 rounded text-xs <?= $status['class'] ?>">
                        <?= $status['label'] ?>
                    </span>
                </p>
                <p><strong>Payment Method:</strong> <?= get_payment_method_label($order['payment_method']) ?></p>
            </div>
        </div>
        
        <!-- Shipping Details -->
        <div>
            <h2 class="text-xl font-semibold text-green-600 mb-4">Shipping Information</h2>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <p><strong>Shipping Address:</strong></p>
                <p class="whitespace-pre-line"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
            </div>
        </div>
    </div>
    
    <!-- Order Items -->
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-green-600 mb-4">Your Products in This Order</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left">Product</th>
                        <th class="py-2 px-4 text-left">Category</th>
                        <th class="py-2 px-4 text-left">Price</th>
                        <th class="py-2 px-4 text-left">Quantity</th>
                        <th class="py-2 px-4 text-left">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr class="border-t">
                            <td class="py-2 px-4"><?= htmlspecialchars($item['product_name']) ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($item['category']) ?></td>
                            <td class="py-2 px-4">$<?= number_format($item['price'], 2) ?></td>
                            <td class="py-2 px-4"><?= $item['quantity'] ?></td>
                            <td class="py-2 px-4">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="py-2 px-4 text-right font-bold">Your Total:</td>
                        <td class="py-2 px-4 font-bold">$<?= number_format($seller_total, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="bg-yellow-50 p-4 rounded-lg mb-6">
        <p class="text-yellow-800">
            <strong>Note:</strong> This order may contain products from other sellers. You only see your products in this order.
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>