<?php 
require_once __DIR__ . '/../includes/header.php';

// Get all products
$stmt = $pdo->query("SELECT p.*, u.username as seller_name FROM products p JOIN users u ON p.seller_id = u.id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();

// Get categories for filter
$stmt = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category");
$categories = $stmt->fetchAll();
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-green-700">Available Products</h1>
        
        <div class="flex space-x-2">
            <select id="category-filter" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['category']) ?>">
                        <?= htmlspecialchars($category['category']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <input type="text" id="search" placeholder="Search products..." class="px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-green-500">
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="products-container">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card border rounded-lg overflow-hidden" data-category="<?= htmlspecialchars($product['category']) ?>">
                    <div class="h-48 bg-gray-200 flex items-center justify-center">
                        <?php if ($product['image_url']): ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                            <div class="text-gray-500">No Image</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="text-xl font-semibold text-green-700"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="text-gray-600 text-sm mb-2">Category: <?= htmlspecialchars($product['category']) ?></p>
                        <p class="text-gray-600 text-sm mb-2">Seller: <?= htmlspecialchars($product['seller_name']) ?></p>
                        <p class="text-gray-700 mb-4"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-green-600">$<?= number_format($product['price'], 2) ?></span>
                            <form method="POST" action="<?= get_correct_path('add_to_cart.php') ?>" class="add-to-cart-form">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-3 text-center py-8">
                <p class="text-gray-500">No products available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('category-filter');
    const searchInput = document.getElementById('search');
    const productsContainer = document.getElementById('products-container');
    const productCards = document.querySelectorAll('.product-card');
    
    function filterProducts() {
        const selectedCategory = categoryFilter.value.toLowerCase();
        const searchTerm = searchInput.value.toLowerCase();
        
        productCards.forEach(card => {
            const category = card.dataset.category.toLowerCase();
            const productText = card.textContent.toLowerCase();
            
            const categoryMatch = !selectedCategory || category === selectedCategory;
            const searchMatch = !searchTerm || productText.includes(searchTerm);
            
            if (categoryMatch && searchMatch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    categoryFilter.addEventListener('change', filterProducts);
    searchInput.addEventListener('input', filterProducts);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>