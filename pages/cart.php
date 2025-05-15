<?php 
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Update quantity
        if ($_POST['action'] === 'update') {
            $cart_id = (int) $_POST['cart_id'];
            $quantity = (int) $_POST['quantity'];
            
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or less
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->execute([$cart_id, $user_id]);
            } else {
                // Update quantity
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$quantity, $cart_id, $user_id]);
            }
            
            $success = 'Cart updated successfully';
        }
        
        // Remove item
        else if ($_POST['action'] === 'remove') {
            $cart_id = (int) $_POST['cart_id'];
            
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user_id]);
            
            $success = 'Item removed from cart';
        }
        
        // Clear cart
        else if ($_POST['action'] === 'clear') {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $success = 'Cart cleared successfully';
        }
    }
}

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.id as cart_id, c.quantity, p.*, p.price * c.quantity as subtotal 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['subtotal'];
}
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-green-700 mb-6">Your Shopping Cart</h1>
    
    <?php if ($error): ?>
        <?= display_error($error) ?>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <?= display_success($success) ?>
    <?php endif; ?>
    
    <?php if (count($cart_items) > 0): ?>
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left">Product</th>
                        <th class="py-2 px-4 text-left">Price</th>
                        <th class="py-2 px-4 text-left">Quantity</th>
                        <th class="py-2 px-4 text-left">Subtotal</th>
                        <th class="py-2 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
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
                            <td class="py-2 px-4">
                                <form method="POST" action="" class="flex items-center">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0" max="<?= $item['stock_quantity'] ?>" class="w-16 px-2 py-1 border rounded mr-2">
                                    <button type="submit" class="text-xs bg-blue-500 text-white px-2 py-1 rounded">Update</button>
                                </form>
                            </td>
                            <td class="py-2 px-4">$<?= number_format($item['subtotal'], 2) ?></td>
                            <td class="py-2 px-4">
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                    <button type="submit" class="text-xs bg-red-500 text-white px-2 py-1 rounded">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="py-2 px-4 text-right font-bold">Total:</td>
                        <td class="py-2 px-4 font-bold">$<?= number_format($total, 2) ?></td>
                        <td class="py-2 px-4">
                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to clear your cart?');">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="text-xs bg-gray-500 text-white px-2 py-1 rounded">Clear Cart</button>
                            </form>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="flex justify-between items-center">
            <a href="<?= get_correct_path('products.php') ?>" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Continue Shopping</a>
            <a href="<?= get_correct_path('checkout.php') ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Proceed to Checkout</a>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <p class="text-gray-500 mb-4">Your cart is empty.</p>
            <a href="<?= get_correct_path('products.php') ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>