<?php 
include __DIR__ . '/includes/header.php';
define('CURRENCY_SYMBOL', 'KSh');

// Get featured products
$stmt = $pdo->query("
    SELECT p.*, u.username as seller_name 
    FROM products p 
    JOIN users u ON p.seller_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 6
");
$featured_products = $stmt->fetchAll();

// Hero images with correct case sensitivity and no leading slashes
$hero_images = [
    'assets/images/hero/farm1.png',
    'assets/images/hero/Farm2.png',
    'assets/images/hero/Farm3.png',
];

// Define fallback image
$fallback_image = 'assets/images/hero/farm-hero-fallback.jpg';

// Verify images exist and use fallback if needed
foreach ($hero_images as $key => $image) {
    if (!file_exists(__DIR__ . '/' . $image)) {
        $hero_images[$key] = $fallback_image;
    }
}
?>

<!-- Hero Section with Slider -->
<div class="relative">
    <!-- Hero Image Slider -->
    <div class="hero-slider w-full h-96 md:h-screen md:max-h-[700px] relative overflow-hidden bg-green-600">
        <?php foreach ($hero_images as $index => $image): ?>
            <div class="hero-slide absolute inset-0 transition-opacity duration-1000 <?= $index === 0 ? 'opacity-100' : 'opacity-0' ?>" 
                 style="background-image: url('<?= $image ?>'); background-size: cover; background-position: center;">
                <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            </div>
        <?php endforeach; ?>
        
        <!-- Hero Content -->
        <div class="container mx-auto px-4 h-full flex items-center justify-center relative z-10">
            <div class="text-center text-white">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Welcome to FreshLink</h1>
                <p class="text-xl md:text-2xl mb-8">Farm Fresh Products Delivered to Your Doorstep</p>
                <div class="flex flex-col md:flex-row justify-center gap-4">
                    <a href="<?= get_correct_path('products.php') ?>" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">Shop Now</a>
                    <a href="<?= get_correct_path('register.php') ?>" class="bg-white hover:bg-gray-100 text-green-700 font-bold py-3 px-6 rounded-lg transition duration-300">Join as Seller</a>
                </div>
            </div>
        </div>
        
        <!-- Slider Navigation -->
        <div class="absolute bottom-5 left-0 right-0 flex justify-center space-x-2 z-20">
            <?php foreach ($hero_images as $index => $image): ?>
                <button class="slider-dot w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity <?= $index === 0 ? 'opacity-100' : '' ?>" 
                        data-index="<?= $index ?>"></button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-green-700 mb-12">Why Choose FreshLink?</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="bg-green-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-green-700 mb-2">Fresh Produce</h3>
                <p class="text-gray-600">All our products are harvested daily from local farms, ensuring maximum freshness and quality.</p>
            </div>
            
            <!-- Feature 2 -->
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="bg-green-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-green-700 mb-2">Trusted Sellers</h3>
                <p class="text-gray-600">Our platform connects you with verified local farmers who follow sustainable farming practices.</p>
            </div>
            
            <!-- Feature 3 -->
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="bg-green-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-green-700 mb-2">Easy Checkout</h3>
                <p class="text-gray-600">Simple and secure ordering process with multiple payment options and fast delivery.</p>
            </div>
        </div>
    </div>
</div>

<!-- Featured Products Section -->
<div class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-green-700">Featured Products</h2>
            <a href="<?= get_correct_path('products.php') ?>" class="text-green-600 hover:text-green-800 font-semibold">View All →</a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6">
            <?php foreach ($featured_products as $product): ?>
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-md transition-transform hover:shadow-lg hover:-translate-y-1">
                    <div class="h-48 bg-gray-200">
                        <?php if ($product['image_url']): ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                            <div class="h-full flex items-center justify-center text-gray-500">No Image</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold text-green-700"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="text-sm text-gray-500 mb-2"><?= htmlspecialchars($product['category']) ?></p>
                            </div>
                            <span class="text-lg font-bold text-green-600"><?= CURRENCY_SYMBOL . ' ' . number_format($product['price'], 2) ?></span>
                        </div>
                        
                        <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...</p>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">By <?= htmlspecialchars($product['seller_name']) ?></span>
                            <form method="POST" action="<?= get_correct_path('add_to_cart.php') ?>">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Testimonials Section -->
<div class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-green-700 mb-12">What Our Customers Say</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Testimonial 1 -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="text-yellow-400 flex">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <p class="text-gray-600 italic mb-4">"The produce from FreshLink is amazing! Everything is so fresh and tastes better than what I find at the supermarket. I love supporting local farmers too."</p>
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full w-10 h-10 flex items-center justify-center mr-3">
                        <span class="text-green-700 font-bold">JD</span>
                    </div>
                    <div>
                        <h4 class="font-semibold">Jane Doe</h4>
                        <p class="text-sm text-gray-500">Loyal Customer</p>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 2 -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="text-yellow-400 flex">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <p class="text-gray-600 italic mb-4">"As a chef, I rely on quality ingredients. FreshLink delivers exceptional produce directly from farmers, and I can taste the difference in my dishes."</p>
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full w-10 h-10 flex items-center justify-center mr-3">
                        <span class="text-green-700 font-bold">MS</span>
                    </div>
                    <div>
                        <h4 class="font-semibold">Michael Smith</h4>
                        <p class="text-sm text-gray-500">Professional Chef</p>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial 3 -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="text-yellow-400 flex">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                </div>
                <p class="text-gray-600 italic mb-4">"I've been selling my organic vegetables through FreshLink for six months now. The platform is easy to use and has connected me with so many new customers!"</p>
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full w-10 h-10 flex items-center justify-center mr-3">
                        <span class="text-green-700 font-bold">AJ</span>
                    </div>
                    <div>
                        <h4 class="font-semibold">Amanda Johnson</h4>
                        <p class="text-sm text-gray-500">Organic Farmer</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Footer -->
<footer class="bg-green-800 text-white">
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- About -->
            <div>
                <h3 class="text-xl font-bold mb-4">About FreshLink</h3>
                <p class="text-green-100">FreshLink connects consumers directly with local farmers, ensuring you get the freshest agricultural products while supporting sustainable farming practices.</p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="<?= get_correct_path('products.php') ?>" class="text-green-100 hover:text-white">Products</a></li>
                    <li><a href="<?= get_correct_path('register.php') ?>" class="text-green-100 hover:text-white">Become a Seller</a></li>
                    <li><a href="#" class="text-green-100 hover:text-white">About Us</a></li>
                    <li><a href="#" class="text-green-100 hover:text-white">Contact</a></li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                <ul class="space-y-2">
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-green-100">123 Farm Road, Countryside</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="text-green-100">info@freshlink.com</span>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span class="text-green-100">(555) 123-4567</span>
                    </li>
                </ul>
            </div>
            
            <!-- Social Media -->
            <div>
                <h3 class="text-xl font-bold mb-4">Follow Us</h3>
                <div class="flex space-x-4">
                    <a href="#" class="bg-white p-2 rounded-full text-green-800 hover:bg-green-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/>
                        </svg>
                    </a>
                    <a href="#" class="bg-white p-2 rounded-full text-green-800 hover:bg-green-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                    <a href="#" class="bg-white p-2 rounded-full text-green-800 hover:bg-green-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="border-t border-green-700 mt-8 pt-8 text-center">
            <p class="text-green-100">&copy; <?php echo date('Y'); ?> FreshLink Management System. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Hero Slider JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.slider-dot');
    let currentSlide = 0;
    const slideCount = slides.length;
    
    // Function to show a specific slide
    function showSlide(index) {
        // Hide all slides
        slides.forEach(slide => {
            slide.classList.remove('opacity-100');
            slide.classList.add('opacity-0');
        });
        
        // Update dots
        dots.forEach(dot => {
            dot.classList.remove('opacity-100');
            dot.classList.add('opacity-50');
        });
        
        // Show the selected slide
        slides[index].classList.remove('opacity-0');
        slides[index].classList.add('opacity-100');
        
        // Update the active dot
        dots[index].classList.remove('opacity-50');
        dots[index].classList.add('opacity-100');
        
        currentSlide = index;
    }
    
    // Auto-advance slides
    function nextSlide() {
        showSlide((currentSlide + 1) % slideCount);
    }
    
    // Set up dot click handlers
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            clearInterval(slideInterval); // Reset the timer when manually changing slides
            slideInterval = setInterval(nextSlide, 5000);
        });
    });
    
    // Start the slideshow
    let slideInterval = setInterval(nextSlide, 5000);
});
</script>

</body>
</html>