<?php
/**
 * Plugin Name: Massnahme Gift Cards
 * Plugin URI: https://massnahme.de
 * Description: Professional gift card system for luxury retail
 * Version: 1.0.0
 * Author: Zeenko Development
 * Author URI: https://zeenko.de
 * License: GPL v2 or later
 * Text Domain: massnahme-gift-cards
 * Domain Path: /languages
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MGC_VERSION', '1.0.0');
define('MGC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MGC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MGC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Declare WooCommerce feature compatibility
 *
 * This must run before WooCommerce initializes to properly register compatibility.
 */
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        // Declare HPOS (High-Performance Order Storage / Custom Order Tables) compatibility
        // Plugin uses WC_Order methods for metadata, which are HPOS-compatible
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );

        // Declare Cart/Checkout Blocks compatibility
        // Currently incompatible: checkout fields use classic woocommerce_after_order_notes hook
        // Setting to false informs WooCommerce that classic checkout is required for full functionality
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks',
            __FILE__,
            false
        );
    }
});

/**
 * Check if WooCommerce is active
 */
function mgc_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p><?php _e('Massnahme Gift Cards requires WooCommerce to be installed and activated.', 'massnahme-gift-cards'); ?></p>
            </div>
            <?php
        });
        return false;
    }
    return true;
}

/**
 * Initialize the plugin
 */
function mgc_init() {
    try {
        if (!mgc_check_woocommerce()) {
            return;
        }

        // Load text domain
        load_plugin_textdomain('massnahme-gift-cards', false, dirname(MGC_PLUGIN_BASENAME) . '/languages');

        // Include required files
        $required_files = [
            'includes/class-gift-card-core.php',
            'includes/class-gift-card-admin.php',
            'includes/class-gift-card-email.php',
            'includes/class-gift-card-coupon.php'
        ];

        foreach ($required_files as $file) {
            $file_path = MGC_PLUGIN_DIR . $file;
            if (!file_exists($file_path)) {
                throw new Exception(sprintf('Required file missing: %s', $file));
            }
            require_once $file_path;
        }

        // Ensure necessary tables exist (covers upgrades / missed activations)
        global $wpdb;
        $expected_table = $wpdb->prefix . 'mgc_gift_cards';
        if ($wpdb->get_var("SHOW TABLES LIKE '" . esc_sql($expected_table) . "'") !== $expected_table) {
            mgc_create_tables();
        }

        // Initialize classes
        if (!class_exists('MGC_Core')) {
            throw new Exception('MGC_Core class not found');
        }
        if (!class_exists('MGC_Admin')) {
            throw new Exception('MGC_Admin class not found');
        }
        if (!class_exists('MGC_Email')) {
            throw new Exception('MGC_Email class not found');
        }
        if (!class_exists('MGC_Coupon')) {
            throw new Exception('MGC_Coupon class not found');
        }

        MGC_Core::get_instance();
        MGC_Admin::get_instance();
        MGC_Email::get_instance();
        MGC_Coupon::get_instance();

    } catch (Exception $e) {
        // Log the error
        error_log('Massnahme Gift Cards Error: ' . $e->getMessage());

        // Show admin notice
        add_action('admin_notices', function() use ($e) {
            ?>
            <div class="notice notice-error">
                <p><strong>Massnahme Gift Cards Error:</strong> <?php echo esc_html($e->getMessage()); ?></p>
                <p>Please check your error logs for more details.</p>
            </div>
            <?php
        });

        return;
    }
}
add_action('plugins_loaded', 'mgc_init', 11);

/**
 * Activation hook
 */
register_activation_hook(__FILE__, function() {
    // Include plugin.php to use is_plugin_active()
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    // Check if WooCommerce is active
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        wp_die(__('Please install and activate WooCommerce before activating this plugin.', 'massnahme-gift-cards'));
    }
    
    // Create database tables if needed
    mgc_create_tables();
    
    // Set default options
    update_option('mgc_version', MGC_VERSION);
    update_option('mgc_settings', [
        'expiry_days' => 730, // 2 years
        'code_prefix' => 'MASS',
        'enable_pdf' => true,
        'enable_qr' => false
    ]);
    
    // Flush rewrite rules
    flush_rewrite_rules();
});

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, function() {
    // Clean up scheduled events
    wp_clear_scheduled_hook('mgc_daily_cleanup');
    flush_rewrite_rules();
});

/**
 * Create custom tables
 */
function mgc_create_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Create main gift cards table
    $table_name = $wpdb->prefix . 'mgc_gift_cards';
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        code varchar(50) NOT NULL,
        amount decimal(10,2) NOT NULL,
        balance decimal(10,2) NOT NULL,
        order_id bigint(20) DEFAULT NULL,
        purchaser_email varchar(100) DEFAULT NULL,
        recipient_email varchar(100) DEFAULT NULL,
        recipient_name varchar(100) DEFAULT NULL,
        message text DEFAULT NULL,
        delivery_method varchar(20) DEFAULT 'digital',
        pickup_location varchar(100) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        expires_at datetime DEFAULT NULL,
        status varchar(20) DEFAULT 'active',
        PRIMARY KEY  (id),
        UNIQUE KEY code (code),
        KEY order_id (order_id),
        KEY status (status),
        KEY delivery_method (delivery_method)
    ) $charset_collate;";

    dbDelta($sql);

    // Create usage log table for tracking redemptions
    $usage_table = $wpdb->prefix . 'mgc_gift_card_usage';
    $sql2 = "CREATE TABLE $usage_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        gift_card_code varchar(50) NOT NULL,
        order_id bigint(20) NOT NULL,
        amount_used decimal(10,2) NOT NULL,
        remaining_balance decimal(10,2) NOT NULL,
        used_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY gift_card_code (gift_card_code),
        KEY order_id (order_id)
    ) $charset_collate;";

    dbDelta($sql2);

    // Run migration for existing installations
    mgc_migrate_delivery_columns();
}

/**
 * Add delivery columns to existing installations
 */
function mgc_migrate_delivery_columns() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mgc_gift_cards';

    // Check if delivery_method column exists
    $column_exists = $wpdb->get_results($wpdb->prepare(
        "SHOW COLUMNS FROM `$table_name` LIKE %s",
        'delivery_method'
    ));

    if (empty($column_exists)) {
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `delivery_method` varchar(20) DEFAULT 'digital' AFTER `message`");
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `pickup_location` varchar(100) DEFAULT NULL AFTER `delivery_method`");
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `recipient_name` varchar(100) DEFAULT NULL AFTER `recipient_email`");
        $wpdb->query("ALTER TABLE `$table_name` ADD INDEX `delivery_method` (`delivery_method`)");
    }

    // Check if pickup_status column exists (Phase 4 - Store Notification System)
    $pickup_status_exists = $wpdb->get_results($wpdb->prepare(
        "SHOW COLUMNS FROM `$table_name` LIKE %s",
        'pickup_status'
    ));

    if (empty($pickup_status_exists)) {
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `pickup_status` varchar(20) DEFAULT NULL AFTER `pickup_location`");
        $wpdb->query("ALTER TABLE `$table_name` ADD INDEX `pickup_status` (`pickup_status`)");
    }
}