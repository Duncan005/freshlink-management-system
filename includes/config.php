<?php
// Currency settings
define('CURRENCY_SYMBOL', 'KSh');
define('CURRENCY_CODE', 'KES');

// Function to format price with currency symbol
function format_price($price) {
    return CURRENCY_SYMBOL . ' ' . number_format($price, 2);
}
?>
