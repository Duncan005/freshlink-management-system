<?php 
require_once __DIR__ . '/../includes/header.php';

// Require seller access
require_seller();

$user_id = $_SESSION['user_id'];

// Get seller's products
$stmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE seller_id = ? 
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$products = $stmt->fetchAll();

// Get seller's recent orders
$stmt = $pdo->prepare("
    SELECT DISTINCT o.id, o.customer_id, o.total_amount, o.status, o.created_at, u.username as customer_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN users u ON o.customer_id = u.id
    WHERE oi.seller_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Get seller's sales statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT o.id) as total_orders,
        SUM(oi.price * oi.quantity) as total_sales,
        COUNT(DISTINCT o.customer_id) as total_customers
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE oi.seller_id = ? AND o.status != 'cancelled'
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Get product count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE seller_id = ?");
$stmt->execute([$user_id]);
$product_count = $stmt->fetch()['count'];
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-green-700 mb-6">Seller Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="font-semibold text-blue-700">Total Products</h3>
            <p class="text-2xl font-bold"><?= $product_count ?></p>
        </div>
        
        <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="font-semibold text-green-700">Total Orders</h3>
            <p class="text-2xl font-bold"><?= $stats['total_orders'] ?: 0 ?></p>
        </div>
        
        <div class="bg-purple-50 p-4 rounded-lg">
            <h3 class="font-semibold text-purple-700">Total Sales</h3>
            <p class="text-2xl font-bold">$<?= number_format($stats['total_sales'] ?: 0, 2) ?></p>
        </div>
        
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="font-semibold text-yellow-700">Total Customers</h3>
            <p class="text-2xl font-bold"><?= $stats['total_customers'] ?: 0 ?></p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-green-600">Recent Orders</h2>
                <a href="<?= get_correct_path('seller_orders.php') ?>" class="text-blue-600 hover:underline">View All</a>
            </div>
            
            <?php if (count($orders) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 text-left">Order ID</th>
                                <th class="py-2 px-4 text-left">Customer</th>
                                <th class="py-2 px-4 text-left">Date</th>
                                <th class="py-2 px-4 text-left">Status</th>
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">You don't have any orders yet.</p>
            <?php endif; ?>
        </div>
        
        <!-- Products -->
        <div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-green-600">Your Products</h2>
                <a href="<?= get_correct_path('stock.php') ?>" class="text-blue-600 hover:underline">Manage Products</a>
            </div>
            
            <?php if (count($products) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 text-left">Name</th>
                                <th class="py-2 px-4 text-left">Price</th>
                                <th class="py-2 px-4 text-left">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr class="border-t">
                                    <td class="py-2 px-4"><?= htmlspecialchars($product['name']) ?></td>
                                    <td class="py-2 px-4">$<?= number_format($product['price'], 2) ?></td>
                                    <td class="py-2 px-4"><?= $product['stock_quantity'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">You haven't added any products yet.</p>
                <a href="<?= get_correct_path('stock.php') ?>" class="mt-2 inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Add Products</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>