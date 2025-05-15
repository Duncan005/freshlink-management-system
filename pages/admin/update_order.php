<?php 
require_once __DIR__ . '/../../includes/admin_header.php';

$error = '';
$success = '';

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    redirect('orders.php');
}

$order_id = (int) $_GET['order_id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.customer_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

// If order not found
if (!$order) {
    redirect('orders.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = clean_input($_POST['status']);
    $tracking_number = isset($_POST['tracking_number']) ? clean_input($_POST['tracking_number']) : '';
    
    // Validate status
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($new_status, $valid_statuses)) {
        $error = 'Invalid status';
    } else {
        try {
            // Update order status
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            
            // If status is shipped and tracking number provided, send shipping confirmation
            if ($new_status === 'shipped' && !empty($tracking_number)) {
                // In a real application, you would store the tracking number in the database
                // For this demo, we'll just send the email
                send_shipping_confirmation_email($order, $order['username'], $tracking_number);
            }
            
            $success = 'Order status updated successfully';
            
            // Refresh order details
            $stmt = $pdo->prepare("
                SELECT o.*, u.username, u.email 
                FROM orders o 
                JOIN users u ON o.customer_id = u.id 
                WHERE o.id = ?
            ");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Failed to update order status';
        }
    }
}

// Get order items
$order_items = get_order_items($order_id);
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-green-700">Update Order #<?= $order_id ?></h1>
    </div>
    
    <?php if ($error): ?>
        <?= display_error($error) ?>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <?= display_success($success) ?>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Order Details -->
        <div>
            <h2 class="text-xl font-semibold text-green-600 mb-4">Order Details</h2>
            
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <p><strong>Order Number:</strong> #<?= $order_id ?></p>
                <p><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
                <p><strong>Customer:</strong> <?= htmlspecialchars($order['username']) ?> (<?= htmlspecialchars($order['email']) ?>)</p>
                <p><strong>Total Amount:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
                <p><strong>Payment Method:</strong> <?= get_payment_method_label($order['payment_method']) ?></p>
                <p><strong>Current Status:</strong> 
                    <?php $status = get_order_status_label($order['status']); ?>
                    <span class="px-2 py-1 rounded text-xs <?= $status['class'] ?>">
                        <?= $status['label'] ?>
                    </span>
                </p>
            </div>
            
            <div class="mb-4">
                <p><strong>Shipping Address:</strong></p>
                <p class="whitespace-pre-line"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
            </div>
        </div>
        
        <!-- Update Form -->
        <div>
            <h2 class="text-xl font-semibold text-green-600 mb-4">Update Order Status</h2>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="status" class="block text-gray-700 mb-2">Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                
                <div id="tracking-section" class="mb-4 <?= $order['status'] === 'shipped' ? '' : 'hidden' ?>">
                    <label for="tracking_number" class="block text-gray-700 mb-2">Tracking Number</label>
                    <input type="text" id="tracking_number" name="tracking_number" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500">
                    <p class="text-sm text-gray-600 mt-1">Enter tracking number if status is "Shipped"</p>
                </div>
                
                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Update Order</button>
            </form>
        </div>
    </div>
    
    <div class="mt-8">
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const trackingSection = document.getElementById('tracking-section');
    
    statusSelect.addEventListener('change', function() {
        if (this.value === 'shipped') {
            trackingSection.classList.remove('hidden');
        } else {
            trackingSection.classList.add('hidden');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>