<?php 
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get orders based on role
$orders = get_user_orders($user_id, $role);
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-green-700 mb-6">
        <?php if ($role === 'customer'): ?>
            Your Orders
        <?php elseif ($role === 'seller'): ?>
            Orders for Your Products
        <?php else: ?>
            All Orders
        <?php endif; ?>
    </h1>
    
    <?php if (count($orders) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left">Order ID</th>
                        <?php if ($role !== 'customer'): ?>
                            <th class="py-2 px-4 text-left">Customer</th>
                        <?php endif; ?>
                        <th class="py-2 px-4 text-left">Date</th>
                        <th class="py-2 px-4 text-left">Items</th>
                        <th class="py-2 px-4 text-left">Total</th>
                        <th class="py-2 px-4 text-left">Status</th>
                        <th class="py-2 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr class="border-t">
                            <td class="py-2 px-4">#<?= $order['id'] ?></td>
                            <?php if ($role !== 'customer'): ?>
                                <td class="py-2 px-4"><?= htmlspecialchars($order['customer_name']) ?></td>
                            <?php endif; ?>
                            <td class="py-2 px-4"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            <td class="py-2 px-4"><?= $order['item_count'] ?></td>
                            <td class="py-2 px-4">$<?= number_format($order['total_amount'], 2) ?></td>
                            <td class="py-2 px-4">
                                <?php $status = get_order_status_label($order['status']); ?>
                                <span class="px-2 py-1 rounded text-xs <?= $status['class'] ?>">
                                    <?= $status['label'] ?>
                                </span>
                            </td>
                            <td class="py-2 px-4">
                                <div class="flex space-x-2">
                                    <a href="<?= get_correct_path('order_confirmation.php') ?>?order_id=<?= $order['id'] ?>" class="text-green-600 hover:underline">View Details</a>
                                    <a href="<?= get_correct_path('track_order.php') ?>?order_id=<?= $order['id'] ?>" class="text-blue-600 hover:underline">Track Order</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <p class="text-gray-500">No orders found.</p>
            <?php if ($role === 'customer'): ?>
                <a href="<?= get_correct_path('products.php') ?>" class="mt-4 inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Browse Products</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>