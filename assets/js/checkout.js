// Checkout and payment processing scripts

document.addEventListener('DOMContentLoaded', function() {
    // Credit card form formatting
    const cardNumberInput = document.getElementById('card_number');
    const cardExpiryInput = document.getElementById('card_expiry');
    const cardCvvInput = document.getElementById('card_cvv');
    
    if (cardNumberInput) {
        // Format credit card number with spaces
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = '';
            
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            
            e.target.value = formattedValue;
        });
    }
    
    if (cardExpiryInput) {
        // Format expiry date as MM/YY
        cardExpiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            
            if (value.length > 2) {
                value = value.substr(0, 2) + '/' + value.substr(2, 2);
            }
            
            e.target.value = value;
        });
    }
    
    // Form validation
    const paymentForm = document.getElementById('payment-form');
    
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate credit card fields if they exist
            if (cardNumberInput && cardExpiryInput && cardCvvInput) {
                const cardNumber = cardNumberInput.value.replace(/\s+/g, '');
                const cardExpiry = cardExpiryInput.value;
                const cardCvv = cardCvvInput.value;
                
                if (cardNumber.length !== 16) {
                    isValid = false;
                    showError(cardNumberInput, 'Please enter a valid 16-digit card number');
                } else {
                    clearError(cardNumberInput);
                }
                
                if (!cardExpiry.match(/^\d{2}\/\d{2}$/)) {
                    isValid = false;
                    showError(cardExpiryInput, 'Please enter a valid expiry date (MM/YY)');
                } else {
                    clearError(cardExpiryInput);
                }
                
                if (cardCvv.length < 3 || cardCvv.length > 4) {
                    isValid = false;
                    showError(cardCvvInput, 'Please enter a valid CVV code');
                } else {
                    clearError(cardCvvInput);
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Helper functions for form validation
    function showError(input, message) {
        const formGroup = input.parentElement;
        let errorElement = formGroup.querySelector('.error-message');
        
        if (!errorElement) {
            errorElement = document.createElement('p');
            errorElement.className = 'error-message text-red-500 text-sm mt-1';
            formGroup.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        input.classList.add('border-red-500');
    }
    
    function clearError(input) {
        const formGroup = input.parentElement;
        const errorElement = formGroup.querySelector('.error-message');
        
        if (errorElement) {
            errorElement.remove();
        }
        
        input.classList.remove('border-red-500');
    }
});