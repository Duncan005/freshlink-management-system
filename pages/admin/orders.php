<?php 
require_once __DIR__ . '/../../includes/admin_header.php';

// Filter by status
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;

// Get orders
$stmt = $pdo->prepare("
    SELECT o.*, u.username as customer_name 
    FROM orders o 
    JOIN users u ON o.customer_id = u.id 
    " . ($status_filter ? "WHERE o.status = ? " : "") . "
    ORDER BY o.created_at DESC 
    LIMIT ? OFFSET ?
");

$offset = ($page - 1) * $limit;
if ($status_filter) {
    $stmt->bindValue(1, $status_filter, PDO::PARAM_STR);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
} else {
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
}

$stmt->execute();
$orders = $stmt->fetchAll();

// Get total orders for pagination
$total_orders = $status_filter ? get_orders_count($status_filter) : get_orders_count();
$total_pages = ceil($total_orders / $limit);
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-green-700">Manage Orders</h1>
    </div>
    
    <!-- Filter -->
    <div class="mb-6">
        <form method="GET" action="" class="flex items-center space-x-2">
            <label for="status" class="text-gray-700">Filter by Status:</label>
            <select name="status" id="status" class="border rounded px-3 py-2">
                <option value="">All Orders</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Filter</button>
            <?php if ($status_filter): ?>
                <a href="<?= get_correct_path('admin/orders.php') ?>" class="text-gray-600 hover:underline">Clear Filter</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Customer</th>
                    <th class="py-2 px-4 text-left">Date</th>
                    <th class="py-2 px-4 text-left">Total</th>
                    <th class="py-2 px-4 text-left">Payment Method</th>
                    <th class="py-2 px-4 text-left">Status</th>
                    <th class="py-2 px-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr class="border-t">
                        <td class="py-2 px-4">#<?= $order['id'] ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td class="py-2 px-4"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        <td class="py-2 px-4">$<?= number_format($order['total_amount'], 2) ?></td>
                        <td class="py-2 px-4"><?= get_payment_method_label($order['payment_method']) ?></td>
                        <td class="py-2 px-4">
                            <?php $status = get_order_status_label($order['status']); ?>
                            <span class="px-2 py-1 rounded text-xs <?= $status['class'] ?>">
                                <?= $status['label'] ?>
                            </span>
                        </td>
                        <td class="py-2 px-4">
                            <div class="flex space-x-2">
                                <a href="<?= get_correct_path('admin/update_order.php') ?>?order_id=<?= $order['id'] ?>" class="text-blue-600 hover:underline">Update</a>
                                <a href="<?= get_correct_path('admin/update_order.php') ?>?order_id=<?= $order['id'] ?>" class="text-green-600 hover:underline">View</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if (count($orders) === 0): ?>
        <div class="text-center py-4">
            <p class="text-gray-500">No orders found.</p>
        </div>
    <?php endif; ?>
    
    <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex justify-center">
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $status_filter ? '&status=' . $status_filter : '' ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $status_filter ? '&status=' . $status_filter : '' ?>" class="px-3 py-1 <?= $i === $page ? 'bg-green-500 text-white' : 'bg-gray-200 hover:bg-gray-300' ?> rounded"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $status_filter ? '&status=' . $status_filter : '' ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>