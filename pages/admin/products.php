<?php 
require_once __DIR__ . '/../../includes/admin_header.php';

// Ensure product approval column exists
ensure_product_approval_column();

// Process approval update
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['approved'])) {
    $product_id = (int) $_POST['product_id'];
    $approved = (int) $_POST['approved'] === 1;
    
    if (update_product_approval($product_id, $approved)) {
        $success = 'Product approval status updated successfully';
    } else {
        $error = 'Failed to update product approval status';
    }
}

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$products = get_all_products($page, $limit);

// Get total products for pagination
$total_products = get_products_count();
$total_pages = ceil($total_products / $limit);
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-green-700">Manage Products</h1>
    </div>
    
    <?php if ($success): ?>
        <?= display_success($success) ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <?= display_error($error) ?>
    <?php endif; ?>
    
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-4 text-left">ID</th>
                    <th class="py-2 px-4 text-left">Name</th>
                    <th class="py-2 px-4 text-left">Category</th>
                    <th class="py-2 px-4 text-left">Price</th>
                    <th class="py-2 px-4 text-left">Stock</th>
                    <th class="py-2 px-4 text-left">Seller</th>
                    <th class="py-2 px-4 text-left">Status</th>
                    <th class="py-2 px-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr class="border-t">
                        <td class="py-2 px-4"><?= $product['id'] ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($product['name']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($product['category']) ?></td>
                        <td class="py-2 px-4">$<?= number_format($product['price'], 2) ?></td>
                        <td class="py-2 px-4"><?= $product['stock_quantity'] ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($product['seller_name']) ?></td>
                        <td class="py-2 px-4">
                            <?php if (isset($product['approved'])): ?>
                                <span class="px-2 py-1 rounded text-xs <?= $product['approved'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $product['approved'] ? 'Approved' : 'Not Approved' ?>
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">Approved</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2 px-4">
                            <form method="POST" action="" class="flex items-center space-x-2">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <select name="approved" class="border rounded px-2 py-1 text-sm">
                                    <option value="1" <?= (!isset($product['approved']) || $product['approved']) ? 'selected' : '' ?>>Approved</option>
                                    <option value="0" <?= (isset($product['approved']) && !$product['approved']) ? 'selected' : '' ?>>Not Approved</option>
                                </select>
                                <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded text-sm">Update</button>
                            </form>
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
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>