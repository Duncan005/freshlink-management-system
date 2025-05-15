<?php 
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// For customers: Get their orders
$orders = [];
if ($role === 'customer') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
}

// For sellers: Get their products
$products = [];
if ($role === 'seller' || $role === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $products = $stmt->fetchAll();
}
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-green-700 mb-6">Dashboard</h1>
    
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-green-600 mb-4">Account Information</h2>
        <div class="bg-gray-50 p-4 rounded-lg">
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Role:</strong> <?= ucfirst(htmlspecialchars($user['role'])) ?></p>
            <p><strong>Member Since:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
        </div>
    </div>
    
    <?php if ($role === 'customer'): ?>
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-green-600 mb-4">Your Orders</h2>
            
            <?php if (count($orders) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 text-left">Order ID</th>
                                <th class="py-2 px-4 text-left">Date</th>
                                <th class="py-2 px-4 text-left">Total</th>
                                <th class="py-2 px-4 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr class="border-t">
                                    <td class="py-2 px-4">#<?= $order['id'] ?></td>
                                    <td class="py-2 px-4"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                    <td class="py-2 px-4">$<?= number_format($order['total_amount'], 2) ?></td>
                                    <td class="py-2 px-4">
                                        <span class="px-2 py-1 rounded text-xs 
                                            <?php 
                                            switch ($order['status']) {
                                                case 'delivered': echo 'bg-green-100 text-green-800'; break;
                                                case 'shipped': echo 'bg-blue-100 text-blue-800'; break;
                                                case 'processing': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">You haven't placed any orders yet.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($role === 'seller' || $role === 'admin'): ?>
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-green-600">Your Products</h2>
                <a href="<?= get_correct_path('stock.php') ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Manage Stock</a>
            </div>
            
            <?php if (count($products) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($products as $product): ?>
                        <div class="border rounded-lg overflow-hidden flex">
                            <div class="w-1/3 bg-gray-200 flex items-center justify-center">
                                <?php if ($product['image_url']): ?>
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="h-full w-full object-cover">
                                <?php else: ?>
                                    <div class="text-gray-500">No Image</div>
                                <?php endif; ?>
                            </div>
                            <div class="w-2/3 p-4">
                                <h3 class="font-semibold"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="text-sm text-gray-600">Category: <?= htmlspecialchars($product['category']) ?></p>
                                <p class="text-sm text-gray-600">Price: $<?= number_format($product['price'], 2) ?></p>
                                <p class="text-sm text-gray-600">Stock: <?= $product['stock_quantity'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">You haven't added any products yet.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>