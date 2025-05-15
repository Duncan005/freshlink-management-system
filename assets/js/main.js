// Main JavaScript for FreshLink Management System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize any components that need JavaScript functionality
    initializeComponents();
    
    // Set up event listeners
    setupEventListeners();
});

// Initialize UI components
function initializeComponents() {
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Initialize dropdowns
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const content = dropdown.querySelector('.dropdown-content');
        
        if (trigger && content) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                content.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                content.classList.add('hidden');
            });
        }
    });
}

// Set up event listeners for interactive elements
function setupEventListeners() {
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productPrice = this.dataset.productPrice;
            
            addToCart(productId, productName, productPrice);
            
            // Show confirmation
            showNotification(`Added ${productName} to cart!`, 'success');
            
            e.preventDefault();
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

// Add product to cart (using localStorage for simplicity)
function addToCart(productId, productName, productPrice) {
    let cart = JSON.parse(localStorage.getItem('freshlink_cart')) || [];
    
    // Check if product already in cart
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: productPrice,
            quantity: 1
        });
    }
    
    localStorage.setItem('freshlink_cart', JSON.stringify(cart));
    updateCartCount();
}

// Update cart item count in UI
function updateCartCount() {
    const cartCountElement = document.getElementById('cart-count');
    if (!cartCountElement) return;
    
    const cart = JSON.parse(localStorage.getItem('freshlink_cart')) || [];
    const itemCount = cart.reduce((total, item) => total + item.quantity, 0);
    
    cartCountElement.textContent = itemCount;
    
    if (itemCount > 0) {
        cartCountElement.classList.remove('hidden');
    } else {
        cartCountElement.classList.add('hidden');
    }
}

// Simple form validation
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('border-red-500');
            
            // Add error message if it doesn't exist
            let errorMessage = field.parentNode.querySelector('.error-message');
            if (!errorMessage) {
                errorMessage = document.createElement('p');
                errorMessage.className = 'text-red-500 text-xs mt-1 error-message';
                errorMessage.textContent = 'This field is required';
                field.parentNode.appendChild(errorMessage);
            }
        } else {
            field.classList.remove('border-red-500');
            const errorMessage = field.parentNode.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
        }
    });
    
    return isValid;
}

// Show notification message
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('opacity-0');
        notification.style.transition = 'opacity 0.5s ease-in-out';
        
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 3000);
}