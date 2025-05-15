<?php 
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$order = null;
$order_items = [];
$tracking_info = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int) $_POST['order_id'];
    
    // Get order details
    $order = get_order_details($order_id, $user_id);
    
    if (!$order) {
        $error = 'Order not found. Please check the order number and try again.';
    } else {
        // Get order items
        $order_items = get_order_items($order_id);
        
        // In a real application, you would fetch tracking information from a shipping API
        // For this demo, we'll create some sample tracking data
        $tracking_info = [
            'number' => 'TRK' . str_pad($order_id, 8, '0', STR_PAD_LEFT),
            'carrier' => 'FreshLink Express',
            'status' => $order['status'],
            'estimated_delivery' => date('Y-m-d', strtotime('+3 days', strtotime($order['created_at']))),
            'events' => [
                [
                    'date' => date('Y-m-d H:i:s', strtotime($order['created_at'])),
                    'location' => 'Order Processing Center',
                    'description' => 'Order received'
                ]
            ]
        ];
        
        // Add more tracking events based on order status
        if ($order['status'] === 'processing' || $order['status'] === 'shipped' || $order['status'] === 'delivered') {
            $tracking_info['events'][] = [
                'date' => date('Y-m-d H:i:s', strtotime('+1 day', strtotime($order['created_at']))),
                'location' => 'Distribution Center',
                'description' => 'Order processed and ready for shipping'
            ];
        }
        
        if ($order['status'] === 'shipped' || $order['status'] === 'delivered') {
            $tracking_info['events'][] = [
                'date' => date('Y-m-d H:i:s', strtotime('+2 days', strtotime($order['created_at']))),
                'location' => 'In Transit',
                'description' => 'Order has been shipped and is on its way'
            ];
        }
        
        if ($order['status'] === 'delivered') {
            $tracking_info['events'][] = [
                'date' => date('Y-m-d H:i:s', strtotime('+3 days', strtotime($order['created_at']))),
                'location' => 'Delivery Address',
                'description' => 'Order has been delivered'
            ];
        }
    }
}
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-green-700 mb-6">Track Your Order</h1>
    
    <?php if ($error): ?>
        <?= display_error($error) ?>
    <?php endif; ?>
    
    <?php if (!$order): ?>
        <div class="max-w-md mx-auto">
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="order_id" class="block text-gray-700 mb-2">Enter Order Number</label>
                    <input type="text" id="order_id" name="order_id" placeholder="e.g. 12345" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500" required>
                </div>
                
                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Track Order</button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">Or view all your orders</p>
                <a href="<?= get_correct_path('orders.php') ?>" class="mt-2 inline-block text-green-600 hover:underline">View Orders</a>
            </div>
        </div>
    <?php else: ?>
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-green-600 mb-4">Order #<?= $order['id'] ?> Tracking Information</h2>
            
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p><strong>Order Date:</strong> <?= date('F j, Y', strtotime($order['created_at'])) ?></p>
                        <p><strong>Tracking Number:</strong> <?= $tracking_info['number'] ?></p>
                        <p><strong>Carrier:</strong> <?= $tracking_info['carrier'] ?></p>
                    </div>
                    <div>
                        <p><strong>Status:</strong> 
                            <?php $status = get_order_status_label($order['status']); ?>
                            <span class="px-2 py-1 rounded text-xs <?= $status['class'] ?>">
                                <?= $status['label'] ?>
                            </span>
                        </p>
                        <p><strong>Estimated Delivery:</strong> <?= date('F j, Y', strtotime($tracking_info['estimated_delivery'])) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="mb-6">
                <h3 class="font-semibold mb-2">Tracking History</h3>
                <div class="relative">
                    <div class="absolute left-4 top-0 h-full w-0.5 bg-gray-200"></div>
                    
                    <?php foreach ($tracking_info['events'] as $event): ?>
                        <div class="relative pl-10 pb-8">
                            <div class="absolute left-2 top-2 h-6 w-6 rounded-full border-4 border-white bg-green-500"></div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <p class="text-sm text-gray-500"><?= date('F j, Y, g:i a', strtotime($event['date'])) ?></p>
                                <p class="font-medium"><?= $event['description'] ?></p>
                                <p class="text-sm"><?= $event['location'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="mb-6">
                <h3 class="font-semibold mb-2">Order Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 text-left">Product</th>
                                <th class="py-2 px-4 text-left">Quantity</th>
                                <th class="py-2 px-4 text-left">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr class="border-t">
                                    <td class="py-2 px-4"><?= htmlspecialchars($item['name']) ?></td>
                                    <td class="py-2 px-4"><?= $item['quantity'] ?></td>
                                    <td class="py-2 px-4">$<?= number_format($item['price'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="flex justify-between">
                <a href="<?= get_correct_path('orders.php') ?>" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to Orders</a>
                <form method="POST" action="">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Refresh Tracking</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>