<?php 
require_once __DIR__ . '/../../includes/admin_header.php';

$error = '';
$success = '';

// Check if user ID is provided
if (!isset($_GET['user_id'])) {
    redirect('users.php');
}

$user_id = (int) $_GET['user_id'];

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// If user not found
if (!$user) {
    redirect('users.php');
}

// Process role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    $role = clean_input($_POST['role']);
    
    if (update_user_role($user_id, $role)) {
        $success = 'User role updated successfully';
        
        // Refresh user details
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } else {
        $error = 'Failed to update user role';
    }
}

// Get user's orders
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.customer_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Get user's products if seller
$products = [];
if ($user['role'] === 'seller') {
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE seller_id = ? 
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $products = $stmt->fetchAll();
}
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-green-700">User Details</h1>
    </div>
    
    <?php if ($error): ?>
        <?= display_error($error) ?>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <?= display_success($success) ?>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- User Details -->
        <div>
            <h2 class="text-xl font-semibold text-green-600 mb-4">User Information</h2>
            
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <p><strong>ID:</strong> <?= $user['id'] ?></p>
                <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Role:</strong> 
                    <span class="px-2 py-1 rounded text-xs 
                        <?php 
                        switch ($user['role']) {
                            case 'admin': echo 'bg-red-100 text-red-800'; break;
                            case 'seller': echo 'bg-blue-100 text-blue-800'; break;
                            default: echo 'bg-gray-100 text-gray-800';
                        }
                        ?>">
                        <?= ucfirst($user['role']) ?>
                    </span>
                </p>
                <p><strong>Created:</strong> <?= date('F j, Y, g:i a', strtotime($user['created_at'])) ?></p>
            </div>
            
            <form method="POST" action="" class="mb-6">
                <div class="mb-4">
                    <label for="role" class="block text-gray-700 mb-2">Update Role</label>
                    <select id="role" name="role" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500">
                        <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="seller" <?= $user['role'] === 'seller' ? 'selected' : '' ?>>Seller</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update Role</button>
            </form>
        </div>
        
        <!-- User Activity -->
        <div>
            <h2 class="text-xl font-semibold text-green-600 mb-4">Recent Orders</h2>
            
            <?php if (count($orders) > 0): ?>
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 text-left">ID</th>
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
                
                <div class="mb-6">
                    <a href="<?= get_correct_path('admin/orders.php') ?>?customer_id=<?= $user_id ?>" class="text-blue-600 hover:underline">View All Orders</a>
                </div>
            <?php else: ?>
                <p class="text-gray-500 mb-6">No orders found for this user.</p>
            <?php endif; ?>
            
            <?php if ($user['role'] === 'seller'): ?>
                <h2 class="text-xl font-semibold text-green-600 mb-4">Products</h2>
                
                <?php if (count($products) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 text-left">ID</th>
                                    <th class="py-2 px-4 text-left">Name</th>
                                    <th class="py-2 px-4 text-left">Price</th>
                                    <th class="py-2 px-4 text-left">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr class="border-t">
                                        <td class="py-2 px-4"><?= $product['id'] ?></td>
                                        <td class="py-2 px-4"><?= htmlspecialchars($product['name']) ?></td>
                                        <td class="py-2 px-4">$<?= number_format($product['price'], 2) ?></td>
                                        <td class="py-2 px-4"><?= $product['stock_quantity'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        <a href="<?= get_correct_path('admin/products.php') ?>?seller_id=<?= $user_id ?>" class="text-blue-600 hover:underline">View All Products</a>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No products found for this seller.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>