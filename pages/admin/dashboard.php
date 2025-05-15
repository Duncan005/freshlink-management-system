<?php 
require_once __DIR__ . '/../../includes/admin_header.php';

// Get statistics
$total_users = get_users_count();
$total_customers = get_users_count('customer');
$total_sellers = get_users_count('seller');
$total_admins = get_users_count('admin');

$total_products = get_products_count();
$total_orders = get_orders_count();
$total_pending_orders = get_orders_count('pending');
$total_processing_orders = get_orders_count('processing');
$total_shipped_orders = get_orders_count('shipped');
$total_delivered_orders = get_orders_count('delivered');
$total_cancelled_orders = get_orders_count('cancelled');

$total_sales = get_total_sales();
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-green-700">Admin Dashboard</h1>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Users Stats -->
        <div class="bg-blue-50 p-4 rounded-lg">
            <h2 class="text-xl font-semibold text-blue-700 mb-4">Users</h2>
            <div class="space-y-2">
                <p><span class="font-medium">Total Users:</span> <?= $total_users ?></p>
                <p><span class="font-medium">Customers:</span> <?= $total_customers ?></p>
                <p><span class="font-medium">Sellers:</span> <?= $total_sellers ?></p>
                <p><span class="font-medium">Admins:</span> <?= $total_admins ?></p>
            </div>
            <div class="mt-4">
                <a href="<?= get_correct_path('admin/users.php') ?>" class="text-blue-600 hover:underline">View All Users →</a>
            </div>
        </div>
        
        <!-- Products Stats -->
        <div class="bg-green-50 p-4 rounded-lg">
            <h2 class="text-xl font-semibold text-green-700 mb-4">Products</h2>
            <div class="space-y-2">
                <p><span class="font-medium">Total Products:</span> <?= $total_products ?></p>
            </div>
            <div class="mt-4">
                <a href="<?= get_correct_path('admin/products.php') ?>" class="text-green-600 hover:underline">View All Products →</a>
            </div>
        </div>
        
        <!-- Orders Stats -->
        <div class="bg-purple-50 p-4 rounded-lg">
            <h2 class="text-xl font-semibold text-purple-700 mb-4">Orders</h2>
            <div class="space-y-2">
                <p><span class="font-medium">Total Orders:</span> <?= $total_orders ?></p>
                <p><span class="font-medium">Pending:</span> <?= $total_pending_orders ?></p>
                <p><span class="font-medium">Processing:</span> <?= $total_processing_orders ?></p>
                <p><span class="font-medium">Shipped:</span> <?= $total_shipped_orders ?></p>
                <p><span class="font-medium">Delivered:</span> <?= $total_delivered_orders ?></p>
                <p><span class="font-medium">Cancelled:</span> <?= $total_cancelled_orders ?></p>
            </div>
            <div class="mt-4">
                <a href="<?= get_correct_path('admin/orders.php') ?>" class="text-purple-600 hover:underline">View All Orders →</a>
            </div>
        </div>
    </div>
    
    <div class="bg-yellow-50 p-4 rounded-lg mb-8">
        <h2 class="text-xl font-semibold text-yellow-700 mb-4">Sales Overview</h2>
        <div class="space-y-2">
            <p><span class="font-medium">Total Sales:</span> $<?= number_format($total_sales, 2) ?></p>
        </div>
    </div>
    

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>