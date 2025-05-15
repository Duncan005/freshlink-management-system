<?php
/**
 * Helper functions for path management in the FreshLink Management System
 */

/**
 * Get the correct relative path for links based on current location
 * 
 * @param string $path The target path
 * @return string The correct relative path
 */
function get_correct_path($path) {
    // Fix any paths with duplicate admin directories
    if (preg_match('/^admin\/admin\//', $path)) {
        $path = preg_replace('/^admin\/admin\//', 'admin/', $path);
    }
    
    // If path is explicitly for index.php in the root
    if ($path === 'index.php') {
        // If we're in the pages directory, we need to go up one level
        if (basename(dirname($_SERVER['PHP_SELF'])) == 'pages') {
            return '../index.php';
        }
        // Otherwise we're already in the root
        return 'index.php';
    }
    
    // If we're in the root directory
    if (dirname($_SERVER['PHP_SELF']) == '/' || dirname($_SERVER['PHP_SELF']) == '\\') {
        // If the path doesn't already have 'pages/' and it's not pointing to the root
        if (!preg_match('/^pages\//', $path) && !preg_match('/^index\.php/', $path)) {
            return 'pages/' . $path;
        }
        return $path;
    } 
    // If we're in the pages directory
    else if (basename(dirname($_SERVER['PHP_SELF'])) == 'pages') {
        // If the path is pointing to another page in pages directory
        if (!preg_match('/^\.\.\//', $path)) {
            return $path;
        }
        return $path;
    }
    // If we're in the admin directory
    else if (basename(dirname($_SERVER['PHP_SELF'])) == 'admin') {
        // Fix paths that might have duplicate admin references
        if (preg_match('/^admin\//', $path)) {
            $path = preg_replace('/^admin\//', '', $path);
        }
        return $path;
    }
    
    return $path;
}

/**
 * Get the base URL for the application
 * 
 * @return string The base URL
 */
function get_base_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = pathinfo($script, PATHINFO_DIRNAME);
    
    // If we're in the pages directory, go up one level
    if (basename($path) == 'pages') {
        $path = dirname($path);
    }
    
    return "$protocol://$host$path";
}
?>