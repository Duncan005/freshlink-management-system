<?php 
require_once __DIR__ . '/../includes/header.php';

// Require seller access
require_seller();

// Ensure order_items has seller_id column
ensure_order_items_seller_id();

$user_id = $_SESSION['user_id'];

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;

// Get seller orders
$orders = get_seller_orders($user_id, $page, $limit);

// Get total orders for pagination
$total_orders = count_seller_orders($user_id);
$total_pages = ceil($total_orders / $limit);
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-green-700 mb-6">My Orders</h1>
    
    <?php if (count($orders) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left">Order ID</th>
                        <th class="py-2 px-4 text-left">Customer</th>
                        <th class="py-2 px-4 text-left">Date</th>
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
                            <td class="py-2 px-4">
                                <?php $status = get_order_status_label($order['status']); ?>
                                <span class="px-2 py-1 rounded text-xs <?= $status['class'] ?>">
                                    <?= $status['label'] ?>
                                </span>
                            </td>
                            <td class="py-2 px-4">
                                <a href="<?= get_correct_path('seller_order_details.php') ?>?order_id=<?= $order['id'] ?>" class="text-blue-600 hover:underline">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="mt-6 flex justify-center">
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="px-3 py-1 <?= $i === $page ? 'bg-green-500 text-white' : 'bg-gray-200 hover:bg-gray-300' ?> rounded"><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-8">
            <p class="text-gray-500">You don't have any orders yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>