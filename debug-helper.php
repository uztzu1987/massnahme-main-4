<?php
/**
 * WordPress Debug Helper for Massnahme Gift Cards
 *
 * INSTRUCTIONS:
 * 1. Upload this file to your WordPress root directory (same folder as wp-config.php)
 * 2. Add the following line to your wp-config.php file BEFORE "That's all, stop editing!"
 *    require_once(ABSPATH . 'debug-helper.php');
 * 3. Try to load your WordPress site
 * 4. Check your error log or enable WP_DEBUG to see detailed errors
 * 5. REMOVE THIS FILE after troubleshooting is complete
 */

// Enable WordPress debugging
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}
if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', true);
}
if (!defined('WP_DEBUG_DISPLAY')) {
    define('WP_DEBUG_DISPLAY', true);
}

// Set error reporting to maximum
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Custom error handler to catch everything
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $error_message = "PHP Error [$errno]: $errstr in $errfile on line $errline";
    error_log($error_message);

    if (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) {
        echo "<div style='background:#f00;color:#fff;padding:10px;margin:10px;border:2px solid #000;'>";
        echo "<strong>DEBUG ERROR:</strong><br>";
        echo nl2br(esc_html($error_message));
        echo "</div>";
    }

    return false; // Let PHP's default error handler run as well
});

// Log plugin loading
add_action('plugins_loaded', function() {
    error_log('DEBUG: plugins_loaded action fired');
}, 1);

// Check if Massnahme Gift Cards plugin is active
add_action('init', function() {
    if (class_exists('MGC_Core')) {
        error_log('DEBUG: Massnahme Gift Cards plugin is loaded and MGC_Core class exists');
    } else {
        error_log('DEBUG: Massnahme Gift Cards plugin MGC_Core class NOT found');
    }
}, 999);

echo "<!-- Debug Helper Active -->\n";
