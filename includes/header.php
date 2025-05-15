<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/path_helper.php';
require_once __DIR__ . '/../includes/order_functions.php';
require_once __DIR__ . '/../includes/email_functions.php';
require_once __DIR__ . '/../includes/admin_functions.php';
require_once __DIR__ . '/../includes/seller_functions.php';
require_once __DIR__ . '/../includes/checkout_functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshLink - Farm Fresh Products Direct to You</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php $is_homepage = basename($_SERVER['PHP_SELF']) === 'index.php'; ?>
    
    <nav class="<?= $is_homepage ? 'absolute w-full z-50 bg-transparent' : 'bg-green-600' ?> text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="<?= get_correct_path('index.php') ?>" class="text-2xl font-bold flex items-center">
                <span class="mr-2">🌱</span>FreshLink
            </a>
            <div class="space-x-4 hidden md:block">
                <a href="<?= get_correct_path('index.php') ?>" class="hover:underline">Home</a>
                <a href="<?= get_correct_path('products.php') ?>" class="hover:underline">Products</a>
                <?php if (is_logged_in()): ?>
                    <?php if (has_role('seller') || has_role('admin')): ?>
                        <a href="<?= get_correct_path('stock.php') ?>" class="hover:underline">Manage Stock</a>
                    <?php endif; ?>
                    <a href="<?= get_correct_path('cart.php') ?>" class="hover:underline relative">
                        Cart
                        <?php
                        // Get cart count
                        $cart_count = get_cart_count($_SESSION['user_id']);
                        
                        if ($cart_count > 0):
                        ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= get_correct_path('orders.php') ?>" class="hover:underline">Orders</a>
                    <a href="<?= get_correct_path('dashboard.php') ?>" class="hover:underline">Dashboard</a>
                    <?php if (is_seller()): ?>
                        <a href="<?= get_correct_path('seller_dashboard.php') ?>" class="hover:underline">Seller Dashboard</a>
                    <?php endif; ?>
                    <?php if (is_admin()): ?>
                        <a href="<?= get_correct_path('admin/dashboard.php') ?>" class="hover:underline text-yellow-300">Admin Panel</a>
                    <?php endif; ?>
                    <a href="<?= get_correct_path('logout.php') ?>" class="hover:underline">Logout</a>
                <?php else: ?>
                    <a href="<?= get_correct_path('login.php') ?>" class="hover:underline">Login</a>
                    <a href="<?= get_correct_path('register.php') ?>" class="hover:underline">Register</a>
                <?php endif; ?>
            </div>
            
            <!-- Mobile menu button -->
            <button class="md:hidden text-white focus:outline-none" id="mobile-menu-button">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        
        <!-- Mobile menu -->
        <div class="md:hidden hidden bg-green-700 mt-2 p-4 rounded-lg" id="mobile-menu">
            <div class="flex flex-col space-y-3">
                <a href="<?= get_correct_path('index.php') ?>" class="hover:underline">Home</a>
                <a href="<?= get_correct_path('products.php') ?>" class="hover:underline">Products</a>
                <?php if (is_logged_in()): ?>
                    <?php if (has_role('seller') || has_role('admin')): ?>
                        <a href="<?= get_correct_path('stock.php') ?>" class="hover:underline">Manage Stock</a>
                    <?php endif; ?>
                    <a href="<?= get_correct_path('cart.php') ?>" class="hover:underline relative inline-block">
                        Cart
                        <?php if ($cart_count > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= get_correct_path('orders.php') ?>" class="hover:underline">Orders</a>
                    <a href="<?= get_correct_path('dashboard.php') ?>" class="hover:underline">Dashboard</a>
                    <?php if (is_seller()): ?>
                        <a href="<?= get_correct_path('seller_dashboard.php') ?>" class="hover:underline">Seller Dashboard</a>
                        <a href="<?= get_correct_path('seller_orders.php') ?>" class="hover:underline">My Orders</a>
                    <?php endif; ?>
                    <?php if (is_admin()): ?>
                        <a href="<?= get_correct_path('admin/dashboard.php') ?>" class="hover:underline text-yellow-300">Admin Panel</a>
                    <?php endif; ?>
                    <a href="<?= get_correct_path('logout.php') ?>" class="hover:underline">Logout</a>
                <?php else: ?>
                    <a href="<?= get_correct_path('login.php') ?>" class="hover:underline">Login</a>
                    <a href="<?= get_correct_path('register.php') ?>" class="hover:underline">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <?php if (!$is_homepage): ?>
    <div class="container mx-auto p-4">
    <?php endif; ?>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>