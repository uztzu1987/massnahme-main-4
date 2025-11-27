<?php
/**
 * Core functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MGC_Core {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Product creation
        add_action('init', [$this, 'create_gift_products']);

        // Order processing
        add_action('woocommerce_order_status_processing', [$this, 'process_gift_card_order']);
        add_action('woocommerce_order_status_completed', [$this, 'process_gift_card_order']);

        // Checkout fields
        add_action('woocommerce_after_order_notes', [$this, 'add_checkout_fields']);
        add_action('woocommerce_checkout_create_order', [$this, 'save_checkout_fields'], 10, 2);

        // Enqueue scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);

        // AJAX handlers
        add_action('wp_ajax_mgc_validate_code', [$this, 'ajax_validate_code']);
        add_action('wp_ajax_nopriv_mgc_validate_code', [$this, 'ajax_validate_code']);
        add_action('wp_ajax_mgc_set_delivery_method', [$this, 'ajax_set_delivery_method']);
        add_action('wp_ajax_nopriv_mgc_set_delivery_method', [$this, 'ajax_set_delivery_method']);

        // Shortcodes
        add_shortcode('massnahme_gift_balance', [$this, 'balance_checker_shortcode']);
        add_shortcode('massnahme_staff_redemption', [$this, 'staff_redemption_shortcode']);

        // Frontend staff AJAX handlers (for logged-in staff)
        add_action('wp_ajax_mgc_frontend_staff_lookup', [$this, 'ajax_frontend_staff_lookup']);
        add_action('wp_ajax_mgc_frontend_redeem', [$this, 'ajax_frontend_redeem']);
        add_action('wp_ajax_mgc_frontend_update_pickup_status', [$this, 'ajax_frontend_update_pickup_status']);

        // Add shipping fee for gift card delivery
        add_action('woocommerce_cart_calculate_fees', [$this, 'add_gift_card_shipping_fee']);

        // Start session for delivery method tracking
        add_action('init', [$this, 'start_session'], 1);

        // Custom amount product hooks
        add_action('woocommerce_before_add_to_cart_button', [$this, 'display_custom_amount_field']);
        add_filter('woocommerce_add_cart_item_data', [$this, 'add_custom_amount_to_cart'], 10, 3);
        add_action('woocommerce_before_calculate_totals', [$this, 'set_custom_cart_item_price'], 20, 1);
        add_filter('woocommerce_get_item_data', [$this, 'display_custom_amount_in_cart'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'save_custom_amount_to_order'], 10, 4);
        add_filter('woocommerce_add_to_cart_validation', [$this, 'validate_custom_amount'], 10, 3);

        // Ensure custom amount product exists when enabled
        add_action('init', [$this, 'maybe_create_custom_amount_product'], 20);
    }

    /**
     * Create custom amount product if enabled and doesn't exist
     */
    public function maybe_create_custom_amount_product() {
        $settings = get_option('mgc_settings', []);

        // Only create if feature is enabled
        if (empty($settings['enable_custom_amount'])) {
            return;
        }

        // Check if product already exists
        $existing = wc_get_products([
            'sku' => 'MGC-CUSTOM',
            'limit' => 1
        ]);

        if (!empty($existing)) {
            return;
        }

        // Create the product
        $this->create_custom_amount_product();
    }

    public function start_session() {
        if (!session_id() && !headers_sent()) {
            session_start();
        }
    }
    
    public function create_gift_products() {
        // Only run once
        if (get_option('mgc_products_created') === 'yes') {
            return;
        }

        // Premium fixed-tier products
        $products = [
            500 => __('Gift Card €500', 'massnahme-gift-cards'),
            1000 => __('Gift Card €1,000', 'massnahme-gift-cards'),
            1500 => __('Gift Card €1,500', 'massnahme-gift-cards'),
            2000 => __('Gift Card €2,000', 'massnahme-gift-cards'),
            3000 => __('Gift Card €3,000', 'massnahme-gift-cards')
        ];

        foreach ($products as $amount => $title) {
            $this->create_single_product($amount, $title);
        }

        // Create custom amount product
        $this->create_custom_amount_product();

        update_option('mgc_products_created', 'yes');
    }

    /**
     * Create the custom amount gift card product
     */
    private function create_custom_amount_product() {
        // Check if exists
        $existing = wc_get_products([
            'sku' => 'MGC-CUSTOM',
            'limit' => 1
        ]);

        if (!empty($existing)) {
            return;
        }

        $settings = get_option('mgc_settings', []);
        $min_amount = $settings['custom_min_amount'] ?? 50;

        $product = new WC_Product_Simple();
        $product->set_name(__('Custom Gift Card', 'massnahme-gift-cards'));
        $product->set_sku('MGC-CUSTOM');
        $product->set_regular_price($min_amount); // Default price, will be overridden
        $product->set_tax_status('taxable');
        $product->set_tax_class('standard');
        $product->set_catalog_visibility('visible');
        $product->set_virtual(true);
        $product->set_sold_individually(false);
        $product->set_manage_stock(false);
        $product->set_stock_status('instock');
        $product->set_description(__('Choose your own gift card amount. Perfect for any occasion.', 'massnahme-gift-cards'));
        $product->set_short_description(__('Create a personalized gift card with your chosen amount.', 'massnahme-gift-cards'));

        // Add meta to identify as custom amount gift card
        $product->add_meta_data('_mgc_gift_card', 'yes', true);
        $product->add_meta_data('_mgc_custom_amount', 'yes', true);

        $product->save();
    }
    
    private function create_single_product($amount, $title) {
        // Check if exists
        $existing = wc_get_products([
            'sku' => 'MGC-' . $amount,
            'limit' => 1
        ]);

        if (!empty($existing)) {
            return;
        }

        $product = new WC_Product_Simple();
        $product->set_name($title);
        $product->set_sku('MGC-' . $amount);
        $product->set_regular_price($amount);
        $product->set_tax_status('taxable');
        $product->set_tax_class('standard');
        $product->set_catalog_visibility('visible');
        $product->set_virtual(true);
        $product->set_sold_individually(false);
        $product->set_manage_stock(false);
        $product->set_stock_status('instock');
        
        // Add meta to identify as gift card
        $product->add_meta_data('_mgc_gift_card', 'yes', true);
        $product->add_meta_data('_mgc_amount', $amount, true);
        
        $product->save();
    }
    
    public function process_gift_card_order($order_id) {
        $order = wc_get_order($order_id);
        
        // Check if already processed
        if ($order->get_meta('_mgc_processed') === 'yes') {
            return;
        }
        
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            
            if (!$product || $product->get_meta('_mgc_gift_card') !== 'yes') {
                continue;
            }
            
            $this->create_gift_card($order, $item);
        }
        
        $order->update_meta_data('_mgc_processed', 'yes');
        $order->save();
    }
    
    private function create_gift_card($order, $item) {
        global $wpdb;

        $code = $this->generate_unique_code();
        $amount = $item->get_total();
        $settings = get_option('mgc_settings');

        // Get delivery method and related data
        $delivery_method = $order->get_meta('_mgc_delivery_method') ?: 'digital';
        $pickup_location = $order->get_meta('_mgc_pickup_location');
        $recipient_name = $order->get_meta('_mgc_recipient_name');

        // Insert into database
        $wpdb->insert(
            $wpdb->prefix . 'mgc_gift_cards',
            [
                'code' => $code,
                'amount' => $amount,
                'balance' => $amount,
                'order_id' => $order->get_id(),
                'purchaser_email' => $order->get_billing_email(),
                'recipient_email' => $order->get_meta('_mgc_recipient_email') ?: $order->get_billing_email(),
                'recipient_name' => $recipient_name,
                'message' => $order->get_meta('_mgc_message'),
                'delivery_method' => $delivery_method,
                'pickup_location' => $pickup_location,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+' . $settings['expiry_days'] . ' days')),
                'status' => 'active'
            ]
        );

        // Create WooCommerce coupon
        MGC_Coupon::get_instance()->create_coupon($code, $amount, $order->get_id());

        // Handle based on delivery method
        switch ($delivery_method) {
            case 'digital':
                // Send email immediately or schedule
                MGC_Email::get_instance()->send_gift_card($code, $order);
                break;

            case 'pickup':
                // Send store notification
                MGC_Email::get_instance()->send_store_pickup_notification($code, $order);
                // Send confirmation to purchaser
                MGC_Email::get_instance()->send_pickup_confirmation($code, $order);
                break;

            case 'shipping':
                // Send shipping confirmation to purchaser
                MGC_Email::get_instance()->send_shipping_confirmation($code, $order);
                break;
        }

        // Log the creation with delivery method
        $delivery_labels = [
            'digital' => __('Digital', 'massnahme-gift-cards'),
            'pickup' => __('Store Pickup', 'massnahme-gift-cards'),
            'shipping' => __('Shipping', 'massnahme-gift-cards')
        ];

        $order->add_order_note(
            sprintf(__('Gift card created: %s (Amount: %s, Delivery: %s)', 'massnahme-gift-cards'),
                $code,
                wc_price($amount),
                $delivery_labels[$delivery_method] ?? $delivery_method
            )
        );
    }
    
    private function generate_unique_code() {
        $settings = get_option('mgc_settings');
        $prefix = $settings['code_prefix'] ?: 'MASS';
        
        do {
            $code = sprintf(
                '%s-%d-%s',
                $prefix,
                date('Y'),
                strtoupper(wp_generate_password(6, false))
            );
        } while ($this->code_exists($code));
        
        return $code;
    }
    
    private function code_exists($code) {
        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE code = %s",
            $code
        ));
        return $exists > 0;
    }
    
    public function add_checkout_fields($checkout) {
        // Check if cart contains gift card
        if (!$this->cart_has_gift_card()) {
            return;
        }
        
        wc_get_template('checkout-gift-fields.php', [], '', MGC_PLUGIN_DIR . 'templates/');
    }
    
    public function save_checkout_fields($order, $data) {
        // Delivery method
        if (isset($_POST['mgc_delivery_method'])) {
            $order->update_meta_data('_mgc_delivery_method', sanitize_text_field($_POST['mgc_delivery_method']));
        }

        // Digital delivery fields
        if (isset($_POST['mgc_recipient_email'])) {
            $order->update_meta_data('_mgc_recipient_email', sanitize_email($_POST['mgc_recipient_email']));
        }
        if (isset($_POST['mgc_message'])) {
            $order->update_meta_data('_mgc_message', sanitize_textarea_field($_POST['mgc_message']));
        }
        if (isset($_POST['mgc_delivery_date'])) {
            $order->update_meta_data('_mgc_delivery_date', sanitize_text_field($_POST['mgc_delivery_date']));
        }

        // Pickup fields
        if (isset($_POST['mgc_pickup_location'])) {
            $order->update_meta_data('_mgc_pickup_location', sanitize_text_field($_POST['mgc_pickup_location']));
        }

        // Recipient name (for pickup/shipping)
        if (isset($_POST['mgc_recipient_name'])) {
            $order->update_meta_data('_mgc_recipient_name', sanitize_text_field($_POST['mgc_recipient_name']));
        }

        // Clear the session delivery method
        if (isset($_SESSION['mgc_delivery_method'])) {
            unset($_SESSION['mgc_delivery_method']);
        }
    }
    
    private function cart_has_gift_card() {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if ($product->get_meta('_mgc_gift_card') === 'yes') {
                return true;
            }
        }
        return false;
    }
    
    public function enqueue_frontend_scripts() {
        $post = get_post();
        $should_enqueue = is_checkout() || ($post && has_shortcode($post->post_content, 'massnahme_gift_balance'));

        if ($should_enqueue) {
            wp_enqueue_style(
                'mgc-frontend',
                MGC_PLUGIN_URL . 'assets/css/frontend.css',
                [],
                MGC_VERSION
            );

            wp_enqueue_script(
                'mgc-frontend',
                MGC_PLUGIN_URL . 'assets/js/frontend.js',
                ['jquery'],
                MGC_VERSION,
                true
            );

            $settings = get_option('mgc_settings', []);
            wp_localize_script('mgc-frontend', 'mgc_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mgc_nonce'),
                'shipping_cost' => floatval($settings['shipping_cost'] ?? 9.95),
                'shipping_enabled' => !empty($settings['enable_shipping'])
            ]);
        }
    }

    /**
     * AJAX handler for setting delivery method in session
     */
    public function ajax_set_delivery_method() {
        check_ajax_referer('mgc_nonce', 'nonce');

        $method = sanitize_text_field($_POST['method'] ?? 'digital');

        if (in_array($method, ['digital', 'pickup', 'shipping'])) {
            $_SESSION['mgc_delivery_method'] = $method;
            wp_send_json_success(['method' => $method]);
        } else {
            wp_send_json_error(__('Invalid delivery method', 'massnahme-gift-cards'));
        }
    }

    /**
     * Add shipping fee for gift card when shipping delivery is selected
     */
    public function add_gift_card_shipping_fee($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        // Check if cart has gift card
        if (!$this->cart_has_gift_card()) {
            return;
        }

        // Check if shipping method is selected
        $delivery_method = $_SESSION['mgc_delivery_method'] ?? 'digital';

        if ($delivery_method !== 'shipping') {
            return;
        }

        $settings = get_option('mgc_settings', []);
        $shipping_cost = floatval($settings['shipping_cost'] ?? 9.95);

        if ($shipping_cost > 0) {
            $cart->add_fee(
                __('Gift Card Luxury Shipping', 'massnahme-gift-cards'),
                $shipping_cost,
                true // taxable
            );
        }
    }
    
    public function ajax_validate_code() {
        check_ajax_referer('mgc_nonce', 'nonce');

        // Rate limiting to prevent brute force attacks
        $ip_address = $this->get_client_ip();
        $rate_limit_key = 'mgc_rate_limit_' . md5($ip_address);
        $attempts = get_transient($rate_limit_key);

        // Allow 10 attempts per 5 minutes
        $max_attempts = 10;
        $lockout_duration = 5 * MINUTE_IN_SECONDS;

        if ($attempts !== false && $attempts >= $max_attempts) {
            wp_send_json_error(__('Too many attempts. Please try again later.', 'massnahme-gift-cards'));
        }

        $code = sanitize_text_field($_POST['code'] ?? '');

        if (empty($code)) {
            wp_send_json_error(__('Please enter a gift card code', 'massnahme-gift-cards'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';

        $gift_card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE code = %s",
            $code
        ));

        if (!$gift_card) {
            // Increment failed attempts
            $this->increment_rate_limit($rate_limit_key, $lockout_duration);
            wp_send_json_error(__('Invalid gift card code', 'massnahme-gift-cards'));
        }

        if ($gift_card->status !== 'active') {
            wp_send_json_error(__('This gift card has been used', 'massnahme-gift-cards'));
        }

        if (strtotime($gift_card->expires_at) < time()) {
            wp_send_json_error(__('This gift card has expired', 'massnahme-gift-cards'));
        }

        // Successful validation - reset rate limit for this IP
        delete_transient($rate_limit_key);

        wp_send_json_success([
            'balance' => $gift_card->balance,
            'expires' => date_i18n(get_option('date_format'), strtotime($gift_card->expires_at)),
            'message' => sprintf(__('Balance: %s', 'massnahme-gift-cards'), wc_price($gift_card->balance))
        ]);
    }

    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Increment rate limit counter
     */
    private function increment_rate_limit($key, $duration) {
        $attempts = get_transient($key);

        if ($attempts === false) {
            set_transient($key, 1, $duration);
        } else {
            set_transient($key, $attempts + 1, $duration);
        }
    }
    
    public function balance_checker_shortcode() {
        ob_start();
        wc_get_template('balance-checker.php', [], '', MGC_PLUGIN_DIR . 'templates/');
        return ob_get_clean();
    }

    /**
     * Check if product is a custom amount gift card
     */
    private function is_custom_amount_product($product) {
        if (!$product) {
            return false;
        }
        return $product->get_meta('_mgc_custom_amount') === 'yes';
    }

    /**
     * Display custom amount input field on product page
     */
    public function display_custom_amount_field() {
        global $product;

        if (!$this->is_custom_amount_product($product)) {
            return;
        }

        $settings = get_option('mgc_settings', []);
        $min_amount = floatval($settings['custom_min_amount'] ?? 50);
        $max_amount = floatval($settings['custom_max_amount'] ?? 300);
        $currency_symbol = get_woocommerce_currency_symbol();

        wc_get_template(
            'single-product/custom-amount.php',
            [
                'min_amount' => $min_amount,
                'max_amount' => $max_amount,
                'currency_symbol' => $currency_symbol,
                'default_amount' => $min_amount
            ],
            '',
            MGC_PLUGIN_DIR . 'templates/'
        );
    }

    /**
     * Validate custom amount before adding to cart
     */
    public function validate_custom_amount($passed, $product_id, $quantity) {
        $product = wc_get_product($product_id);

        if (!$this->is_custom_amount_product($product)) {
            return $passed;
        }

        $custom_amount = isset($_POST['mgc_custom_amount']) ? floatval($_POST['mgc_custom_amount']) : 0;

        $settings = get_option('mgc_settings', []);
        $min_amount = floatval($settings['custom_min_amount'] ?? 50);
        $max_amount = floatval($settings['custom_max_amount'] ?? 300);

        if ($custom_amount < $min_amount) {
            wc_add_notice(
                sprintf(__('Minimum gift card amount is %s', 'massnahme-gift-cards'), wc_price($min_amount)),
                'error'
            );
            return false;
        }

        if ($custom_amount > $max_amount) {
            wc_add_notice(
                sprintf(__('Maximum gift card amount is %s', 'massnahme-gift-cards'), wc_price($max_amount)),
                'error'
            );
            return false;
        }

        return $passed;
    }

    /**
     * Add custom amount to cart item data
     */
    public function add_custom_amount_to_cart($cart_item_data, $product_id, $variation_id) {
        $product = wc_get_product($product_id);

        if (!$this->is_custom_amount_product($product)) {
            return $cart_item_data;
        }

        if (isset($_POST['mgc_custom_amount']) && !empty($_POST['mgc_custom_amount'])) {
            $custom_amount = floatval($_POST['mgc_custom_amount']);
            $cart_item_data['mgc_custom_amount'] = $custom_amount;
            // Create unique cart item key to allow multiple custom amounts
            $cart_item_data['unique_key'] = md5(microtime() . rand());
        }

        return $cart_item_data;
    }

    /**
     * Set custom price for cart item
     */
    public function set_custom_cart_item_price($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (did_action('woocommerce_before_calculate_totals') >= 2) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item) {
            if (isset($cart_item['mgc_custom_amount'])) {
                $cart_item['data']->set_price($cart_item['mgc_custom_amount']);
            }
        }
    }

    /**
     * Display custom amount in cart and checkout
     */
    public function display_custom_amount_in_cart($item_data, $cart_item) {
        if (isset($cart_item['mgc_custom_amount'])) {
            $item_data[] = [
                'key' => __('Gift Card Amount', 'massnahme-gift-cards'),
                'value' => wc_price($cart_item['mgc_custom_amount'])
            ];
        }
        return $item_data;
    }

    /**
     * Save custom amount to order item meta
     */
    public function save_custom_amount_to_order($item, $cart_item_key, $values, $order) {
        if (isset($values['mgc_custom_amount'])) {
            $item->add_meta_data('_mgc_custom_amount', $values['mgc_custom_amount'], true);
        }
    }

    /**
     * Staff redemption shortcode for frontend POS
     */
    public function staff_redemption_shortcode($atts) {
        // Check if user is logged in and has permission
        if (!is_user_logged_in()) {
            return '<div class="mgc-staff-login-required">' .
                   '<p>' . __('Please log in to access the staff redemption system.', 'massnahme-gift-cards') . '</p>' .
                   '<a href="' . esc_url(wp_login_url(get_permalink())) . '" class="button">' . __('Log In', 'massnahme-gift-cards') . '</a>' .
                   '</div>';
        }

        if (!current_user_can('manage_woocommerce')) {
            return '<div class="mgc-staff-no-permission">' .
                   '<p>' . __('You do not have permission to access this page.', 'massnahme-gift-cards') . '</p>' .
                   '</div>';
        }

        // Enqueue the staff redemption scripts
        wp_enqueue_script('mgc-staff-redemption');

        ob_start();
        include MGC_PLUGIN_DIR . 'templates/frontend-staff-redemption.php';
        return ob_get_clean();
    }

    /**
     * AJAX: Frontend staff lookup
     */
    public function ajax_frontend_staff_lookup() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mgc_frontend_nonce')) {
            wp_send_json_error(__('Security check failed', 'massnahme-gift-cards'));
        }

        // Check permission
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Permission denied', 'massnahme-gift-cards'));
        }

        $code = isset($_POST['code']) ? sanitize_text_field(strtoupper($_POST['code'])) : '';

        if (empty($code)) {
            wp_send_json_error(__('Please enter a gift card code', 'massnahme-gift-cards'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';
        $usage_table = $wpdb->prefix . 'mgc_gift_card_usage';

        $gift_card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE code = %s",
            $code
        ));

        if (!$gift_card) {
            wp_send_json_error(__('Gift card not found', 'massnahme-gift-cards'));
        }

        // Get transaction history
        $history = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $usage_table WHERE gift_card_code = %s ORDER BY used_at DESC LIMIT 10",
            $code
        ));

        $history_data = [];
        foreach ($history as $item) {
            $history_data[] = [
                'date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->used_at)),
                'amount' => floatval($item->amount_used),
                'order_id' => intval($item->order_id),
                'remaining' => floatval($item->remaining_balance)
            ];
        }

        // Get store name if pickup
        $store_name = '';
        if ($gift_card->delivery_method === 'pickup' && $gift_card->pickup_location !== null) {
            $settings = get_option('mgc_settings', []);
            $store_locations = $settings['store_locations'] ?? [];
            $store = $store_locations[$gift_card->pickup_location] ?? null;
            $store_name = $store['name'] ?? '';
        }

        wp_send_json_success([
            'code' => $gift_card->code,
            'amount' => floatval($gift_card->amount),
            'balance' => floatval($gift_card->balance),
            'status' => $gift_card->status,
            'recipient_email' => $gift_card->recipient_email,
            'recipient_name' => $gift_card->recipient_name ?? '',
            'delivery_method' => $gift_card->delivery_method ?? 'digital',
            'pickup_location' => $gift_card->pickup_location ?? '',
            'pickup_status' => $gift_card->pickup_status ?? 'ordered',
            'store_name' => $store_name,
            'expires_at' => date_i18n(get_option('date_format'), strtotime($gift_card->expires_at)),
            'created_at' => date_i18n(get_option('date_format'), strtotime($gift_card->created_at)),
            'history' => $history_data
        ]);
    }

    /**
     * AJAX: Frontend staff redeem
     */
    public function ajax_frontend_redeem() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mgc_frontend_nonce')) {
            wp_send_json_error(__('Security check failed', 'massnahme-gift-cards'));
        }

        // Check permission
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Permission denied', 'massnahme-gift-cards'));
        }

        $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        $redeem_amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

        if (empty($code)) {
            wp_send_json_error(__('Invalid gift card code', 'massnahme-gift-cards'));
        }

        if ($redeem_amount <= 0) {
            wp_send_json_error(__('Invalid redemption amount', 'massnahme-gift-cards'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';

        // Use transaction for safety
        $wpdb->query('START TRANSACTION');

        try {
            $gift_card = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE code = %s FOR UPDATE",
                $code
            ));

            if (!$gift_card) {
                $wpdb->query('ROLLBACK');
                wp_send_json_error(__('Gift card not found', 'massnahme-gift-cards'));
            }

            $current_balance = floatval($gift_card->balance);

            if ($redeem_amount > $current_balance) {
                $wpdb->query('ROLLBACK');
                wp_send_json_error(__('Insufficient balance', 'massnahme-gift-cards'));
            }

            $new_balance = $current_balance - $redeem_amount;
            $new_status = $new_balance > 0 ? 'active' : 'used';

            // Update balance
            $wpdb->update(
                $table,
                [
                    'balance' => $new_balance,
                    'status' => $new_status
                ],
                ['code' => $code],
                ['%f', '%s'],
                ['%s']
            );

            // Log the redemption
            $wpdb->insert(
                $wpdb->prefix . 'mgc_gift_card_usage',
                [
                    'gift_card_code' => $code,
                    'order_id' => 0, // 0 = manual/POS redemption
                    'amount_used' => $redeem_amount,
                    'remaining_balance' => $new_balance,
                    'used_at' => current_time('mysql')
                ]
            );

            // Update WooCommerce coupon if exists
            $coupon = new WC_Coupon($code);
            if ($coupon->get_id()) {
                $coupon->update_meta_data('_mgc_balance', $new_balance);
                $coupon->save();
            }

            $wpdb->query('COMMIT');

            wp_send_json_success([
                'message' => __('Redemption successful', 'massnahme-gift-cards'),
                'redeemed' => $redeem_amount,
                'new_balance' => $new_balance,
                'new_status' => $new_status,
                'formatted_balance' => strip_tags(wc_price($new_balance))
            ]);

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(__('Error processing redemption', 'massnahme-gift-cards'));
        }
    }

    /**
     * AJAX: Frontend update pickup status
     */
    public function ajax_frontend_update_pickup_status() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mgc_frontend_nonce')) {
            wp_send_json_error(__('Security check failed', 'massnahme-gift-cards'));
        }

        // Check permission
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Permission denied', 'massnahme-gift-cards'));
        }

        $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        $valid_statuses = ['ordered', 'preparing', 'ready', 'collected'];
        if (!in_array($status, $valid_statuses)) {
            wp_send_json_error(__('Invalid status', 'massnahme-gift-cards'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';

        $gift_card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE code = %s",
            $code
        ));

        if (!$gift_card) {
            wp_send_json_error(__('Gift card not found', 'massnahme-gift-cards'));
        }

        $old_status = $gift_card->pickup_status;

        $updated = $wpdb->update(
            $table,
            ['pickup_status' => $status],
            ['code' => $code],
            ['%s'],
            ['%s']
        );

        if ($updated === false) {
            wp_send_json_error(__('Failed to update status', 'massnahme-gift-cards'));
        }

        // Send notification when marked as ready
        if ($status === 'ready' && $old_status !== 'ready') {
            $this->send_ready_for_pickup_email($gift_card);
        }

        $status_labels = [
            'ordered' => __('Ordered', 'massnahme-gift-cards'),
            'preparing' => __('Preparing', 'massnahme-gift-cards'),
            'ready' => __('Ready for Pickup', 'massnahme-gift-cards'),
            'collected' => __('Collected', 'massnahme-gift-cards')
        ];

        wp_send_json_success([
            'status' => $status,
            'status_label' => $status_labels[$status]
        ]);
    }

    /**
     * Send ready for pickup email notification
     */
    private function send_ready_for_pickup_email($gift_card) {
        $settings = get_option('mgc_settings', []);
        $store_locations = $settings['store_locations'] ?? [];
        $store = $store_locations[$gift_card->pickup_location] ?? null;

        $to = $gift_card->purchaser_email;
        $subject = sprintf(
            __('Your Gift Card is Ready for Pickup - %s', 'massnahme-gift-cards'),
            get_bloginfo('name')
        );

        $store_info = '';
        if ($store) {
            $store_info = sprintf(
                "\n\n%s\n%s\n%s",
                $store['name'] ?? '',
                $store['address'] ?? '',
                $store['hours'] ?? ''
            );
        }

        $message = sprintf(
            __("Great news! Your gift card (Code: %s) is now ready for pickup.%s\n\nPlease bring a valid ID when collecting your gift card.\n\nThank you for shopping with %s!", 'massnahme-gift-cards'),
            $gift_card->code,
            $store_info,
            get_bloginfo('name')
        );

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('woocommerce_email_from_address') . '>'
        ];

        wp_mail($to, $subject, $message, $headers);
    }
}