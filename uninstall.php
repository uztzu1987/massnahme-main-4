<?php
/**
 * Uninstall script
 */

// Exit if not called by WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Only remove data if option is set
if (get_option('mgc_remove_data_on_uninstall') === 'yes') {
    global $wpdb;
    
    // Drop custom table
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mgc_gift_cards");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mgc_gift_card_usage");
    
    // Delete options
    delete_option('mgc_version');
    delete_option('mgc_settings');
    delete_option('mgc_products_created');
    
    // Delete gift card products
    $products = wc_get_products([
        'meta_key' => '_mgc_gift_card',
        'meta_value' => 'yes',
        'limit' => -1
    ]);
    
    foreach ($products as $product) {
        $product->delete(true);
    }
}