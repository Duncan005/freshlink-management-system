<?php
require_once __DIR__ . '/../includes/header.php';

// Require admin access
require_admin();
?>

<div class="bg-gray-800 text-white p-4 mb-6">
    <div class="container mx-auto flex justify-between items-center">
        <div class="text-xl font-bold">FreshLink Admin</div>
        <div class="space-x-4">
            <a href="<?= get_correct_path('admin/dashboard.php') ?>" class="hover:underline <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'text-green-400' : '' ?>">Dashboard</a>
            <a href="<?= get_correct_path('admin/users.php') ?>" class="hover:underline <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'text-green-400' : '' ?>">Users</a>
            <a href="<?= get_correct_path('admin/products.php') ?>" class="hover:underline <?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'text-green-400' : '' ?>">Products</a>
            <a href="<?= get_correct_path('admin/orders.php') ?>" class="hover:underline <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'text-green-400' : '' ?>">Orders</a>
            <a href="<?= get_correct_path('index.php') ?>" class="hover:underline">Back to Site</a>
            <a href="<?= get_correct_path('admin/logout.php') ?>" class="hover:underline text-red-300">Logout</a>
        </div>
    </div>
</div>