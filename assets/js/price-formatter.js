const CURRENCY_SYMBOL = 'KSh';

function formatPrice(amount) {
    return `${CURRENCY_SYMBOL} ${parseFloat(amount).toFixed(2)}`;
}

// For international number formatting
const priceFormatter = new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
    minimumFractionDigits: 2
});
