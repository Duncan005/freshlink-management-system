<?php 
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    redirect('dashboard.php');
}

$user_id = $_SESSION['user_id'];
$order_id = (int) $_GET['order_id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.customer_id = u.id 
    WHERE o.id = ? AND o.customer_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

// If order not found or doesn't belong to user
if (!$order) {
    redirect('dashboard.php');
}
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="text-center mb-8">
        <div class="inline-block p-4 rounded-full bg-green-100 text-green-600 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-green-700">Payment Successful!</h1>
        <p class="text-gray-600">Thank you for your payment. Your order is now being processed.</p>
    </div>
    
    <div class="max-w-md mx-auto bg-gray-50 p-6 rounded-lg mb-8">
        <h2 class="text-xl font-semibold text-green-600 mb-4">Order Summary</h2>
        
        <div class="mb-4">
            <p><strong>Order Number:</strong> #<?= $order_id ?></p>
            <p><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
            <p><strong>Total Amount:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
            <p><strong>Payment Method:</strong> <?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></p>
            <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>
        </div>
        
        <div class="mb-4">
            <p><strong>Shipping Address:</strong></p>
            <p class="whitespace-pre-line"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
        </div>
    </div>
    
    <div class="text-center">
        <p class="mb-4">You will receive an email confirmation shortly.</p>
        <div class="flex justify-center space-x-4">
            <a href="<?= get_correct_path('orders.php') ?>" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">View Orders</a>
            <a href="<?= get_correct_path('products.php') ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Continue Shopping</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>