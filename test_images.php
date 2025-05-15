<?php
// Simple script to test if images are accessible
$images = [
    'assets/images/hero/farm1.png',
    'assets/images/hero/Farm2.png',
    'assets/images/hero/Farm3.png',
    'assets/images/hero/farm-hero-fallback.jpg'
];

echo "<h1>Image Accessibility Test</h1>";

foreach ($images as $image) {
    $fullPath = __DIR__ . '/' . $image;
    $exists = file_exists($fullPath);
    $readable = is_readable($fullPath);
    $size = $exists ? filesize($fullPath) : 0;
    
    echo "<h2>$image</h2>";
    echo "<p>Full path: $fullPath</p>";
    echo "<p>Exists: " . ($exists ? "Yes" : "No") . "</p>";
    echo "<p>Readable: " . ($readable ? "Yes" : "No") . "</p>";
    echo "<p>Size: $size bytes</p>";
    
    if ($exists) {
        echo "<p><img src='$image' alt='Test image' style='max-width: 300px; border: 1px solid #ccc;'></p>";
    }
    
    echo "<hr>";
}
?>