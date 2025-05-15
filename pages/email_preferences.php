<?php 
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Check if email preferences table exists, if not create it
$stmt = $pdo->query("SHOW TABLES LIKE 'email_preferences'");
if ($stmt->rowCount() === 0) {
    $pdo->exec("
        CREATE TABLE email_preferences (
            user_id INT PRIMARY KEY,
            order_confirmations BOOLEAN NOT NULL DEFAULT 1,
            shipping_updates BOOLEAN NOT NULL DEFAULT 1,
            promotions BOOLEAN NOT NULL DEFAULT 1,
            newsletter BOOLEAN NOT NULL DEFAULT 1,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
}

// Get user's email preferences
$stmt = $pdo->prepare("SELECT * FROM email_preferences WHERE user_id = ?");
$stmt->execute([$user_id]);
$preferences = $stmt->fetch();

// If no preferences exist, create default ones
if (!$preferences) {
    $stmt = $pdo->prepare("INSERT INTO email_preferences (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    
    $stmt = $pdo->prepare("SELECT * FROM email_preferences WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $preferences = $stmt->fetch();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_confirmations = isset($_POST['order_confirmations']) ? 1 : 0;
    $shipping_updates = isset($_POST['shipping_updates']) ? 1 : 0;
    $promotions = isset($_POST['promotions']) ? 1 : 0;
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE email_preferences 
            SET order_confirmations = ?, shipping_updates = ?, promotions = ?, newsletter = ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$order_confirmations, $shipping_updates, $promotions, $newsletter, $user_id]);
        
        $success = 'Email preferences updated successfully';
        
        // Refresh preferences
        $stmt = $pdo->prepare("SELECT * FROM email_preferences WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $preferences = $stmt->fetch();
    } catch (PDOException $e) {
        $error = 'Failed to update email preferences';
    }
}
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-green-700 mb-6">Email Preferences</h1>
    
    <?php if ($error): ?>
        <?= display_error($error) ?>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <?= display_success($success) ?>
    <?php endif; ?>
    
    <div class="mb-6">
        <p class="text-gray-700">Manage the types of emails you receive from FreshLink. You can opt out of certain types of emails while still receiving important account-related notifications.</p>
    </div>
    
    <form method="POST" action="">
        <div class="space-y-4">
            <div class="flex items-center">
                <input type="checkbox" id="order_confirmations" name="order_confirmations" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" <?= $preferences['order_confirmations'] ? 'checked' : '' ?>>
                <label for="order_confirmations" class="ml-2 block text-gray-700">
                    <span class="font-medium">Order Confirmations</span>
                    <p class="text-sm text-gray-500">Receive emails when you place an order and when your order status changes.</p>
                </label>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="shipping_updates" name="shipping_updates" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" <?= $preferences['shipping_updates'] ? 'checked' : '' ?>>
                <label for="shipping_updates" class="ml-2 block text-gray-700">
                    <span class="font-medium">Shipping Updates</span>
                    <p class="text-sm text-gray-500">Receive emails when your order ships and delivery notifications.</p>
                </label>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="promotions" name="promotions" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" <?= $preferences['promotions'] ? 'checked' : '' ?>>
                <label for="promotions" class="ml-2 block text-gray-700">
                    <span class="font-medium">Promotions and Discounts</span>
                    <p class="text-sm text-gray-500">Receive emails about special offers, discounts, and promotions.</p>
                </label>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="newsletter" name="newsletter" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" <?= $preferences['newsletter'] ? 'checked' : '' ?>>
                <label for="newsletter" class="ml-2 block text-gray-700">
                    <span class="font-medium">Newsletter</span>
                    <p class="text-sm text-gray-500">Receive our monthly newsletter with farming tips, seasonal produce guides, and recipes.</p>
                </label>
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Save Preferences</button>
        </div>
    </form>
    
    <div class="mt-8 pt-6 border-t border-gray-200">
        <h2 class="text-lg font-semibold text-green-600 mb-4">Email History</h2>
        
        <p class="text-gray-700">You can view your recent email communications in the logs directory.</p>
        
        <div class="mt-4">
            <a href="<?= get_correct_path('dashboard.php') ?>" class="text-green-600 hover:underline">Back to Dashboard</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>