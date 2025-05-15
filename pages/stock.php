<?php 
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in and is a seller or admin
if (!is_logged_in() || (!has_role('seller') && !has_role('admin'))) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle form submission for adding/updating product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add new product
        if ($_POST['action'] === 'add') {
            $name = clean_input($_POST['name']);
            $description = clean_input($_POST['description']);
            $category = clean_input($_POST['category']);
            $price = (float) $_POST['price'];
            $stock = (int) $_POST['stock'];
            $image_url = clean_input($_POST['image_url']);
            
            if (empty($name) || empty($category) || $price <= 0) {
                $error = 'Please fill in all required fields';
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (seller_id, name, description, category, price, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$user_id, $name, $description, $category, $price, $stock, $image_url])) {
                    $success = 'Product added successfully';
                } else {
                    $error = 'Failed to add product';
                }
            }
        }
        
        // Update existing product
        else if ($_POST['action'] === 'update' && isset($_POST['product_id'])) {
            $product_id = (int) $_POST['product_id'];
            $stock = (int) $_POST['stock'];
            $price = (float) $_POST['price'];
            
            // Verify the product belongs to this seller
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
            $stmt->execute([$product_id, $user_id]);
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = ?, price = ? WHERE id = ?");
                if ($stmt->execute([$stock, $price, $product_id])) {
                    $success = 'Product updated successfully';
                } else {
                    $error = 'Failed to update product';
                }
            } else {
                $error = 'Invalid product';
            }
        }
        
        // Delete product
        else if ($_POST['action'] === 'delete' && isset($_POST['product_id'])) {
            $product_id = (int) $_POST['product_id'];
            
            // Verify the product belongs to this seller
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
            $stmt->execute([$product_id, $user_id]);
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                if ($stmt->execute([$product_id])) {
                    $success = 'Product deleted successfully';
                } else {
                    $error = 'Failed to delete product';
                }
            } else {
                $error = 'Invalid product';
            }
        }
    }
}

// Get seller's products
$stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$products = $stmt->fetchAll();
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-green-700 mb-6">Manage Stock</h1>
    
    <?php if ($error): ?>
        <?= display_error($error) ?>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <?= display_success($success) ?>
    <?php endif; ?>
    
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-green-600 mb-4">Add New Product</h2>
        
        <form method="POST" action="" class="bg-gray-50 p-4 rounded-lg">
            <input type="hidden" name="action" value="add">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="name" class="block text-gray-700 mb-2">Product Name *</label>
                    <input type="text" id="name" name="name" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                </div>
                
                <div>
                    <label for="category" class="block text-gray-700 mb-2">Category *</label>
                    <input type="text" id="category" name="category" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                </div>
                
                <div>
                    <label for="price" class="block text-gray-700 mb-2">Price ($) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                </div>
                
                <div>
                    <label for="stock" class="block text-gray-700 mb-2">Stock Quantity *</label>
                    <input type="number" id="stock" name="stock" min="0" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                </div>
                
                <div>
                    <label for="image_url" class="block text-gray-700 mb-2">Image URL</label>
                    <input type="text" id="image_url" name="image_url" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="description" class="block text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500"></textarea>
            </div>
            
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Add Product</button>
        </form>
    </div>
    
    <div>
        <h2 class="text-xl font-semibold text-green-600 mb-4">Your Products</h2>
        
        <?php if (count($products) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left">Product</th>
                            <th class="py-2 px-4 text-left">Category</th>
                            <th class="py-2 px-4 text-left">Price</th>
                            <th class="py-2 px-4 text-left">Stock</th>
                            <th class="py-2 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr class="border-t">
                                <td class="py-2 px-4">
                                    <div class="flex items-center">
                                        <?php if ($product['image_url']): ?>
                                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="h-10 w-10 object-cover mr-2">
                                        <?php endif; ?>
                                        <?= htmlspecialchars($product['name']) ?>
                                    </div>
                                </td>
                                <td class="py-2 px-4"><?= htmlspecialchars($product['category']) ?></td>
                                <td class="py-2 px-4">
                                    <form method="POST" action="" class="flex items-center">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="number" name="price" value="<?= $product['price'] ?>" step="0.01" min="0" class="w-20 px-2 py-1 border rounded mr-2">
                                        <button type="submit" class="text-xs bg-blue-500 text-white px-2 py-1 rounded">Update</button>
                                    </form>
                                </td>
                                <td class="py-2 px-4">
                                    <form method="POST" action="" class="flex items-center">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="price" value="<?= $product['price'] ?>">
                                        <input type="number" name="stock" value="<?= $product['stock_quantity'] ?>" min="0" class="w-20 px-2 py-1 border rounded mr-2">
                                        <button type="submit" class="text-xs bg-blue-500 text-white px-2 py-1 rounded">Update</button>
                                    </form>
                                </td>
                                <td class="py-2 px-4">
                                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="text-xs bg-red-500 text-white px-2 py-1 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">You haven't added any products yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>