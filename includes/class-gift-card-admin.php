<?php
/**
 * Admin functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MGC_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_menu_pages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_filter('woocommerce_screen_ids', [$this, 'add_screen_ids']);

        // AJAX handlers
        add_action('wp_ajax_mgc_update_balance', [$this, 'ajax_update_balance']);
        add_action('wp_ajax_mgc_staff_lookup', [$this, 'ajax_staff_lookup']);
        add_action('wp_ajax_mgc_update_pickup_status', [$this, 'ajax_update_pickup_status']);
    }
    
    public function add_menu_pages() {
        add_menu_page(
            __('Gift Cards', 'massnahme-gift-cards'),
            __('Gift Cards', 'massnahme-gift-cards'),
            'manage_woocommerce',
            'mgc-gift-cards',
            [$this, 'dashboard_page'],
            'dashicons-tickets-alt',
            56
        );
        
        add_submenu_page(
            'mgc-gift-cards',
            __('All Gift Cards', 'massnahme-gift-cards'),
            __('All Gift Cards', 'massnahme-gift-cards'),
            'manage_woocommerce',
            'mgc-gift-cards',
            [$this, 'dashboard_page']
        );
        
        add_submenu_page(
            'mgc-gift-cards',
            __('Validate', 'massnahme-gift-cards'),
            __('Validate', 'massnahme-gift-cards'),
            'manage_woocommerce',
            'mgc-validate',
            [$this, 'validate_page']
        );
        
        add_submenu_page(
            'mgc-gift-cards',
            __('Pickup Orders', 'massnahme-gift-cards'),
            __('Pickup Orders', 'massnahme-gift-cards'),
            'manage_woocommerce',
            'mgc-pickup-orders',
            [$this, 'pickup_orders_page']
        );

        add_submenu_page(
            'mgc-gift-cards',
            __('Settings', 'massnahme-gift-cards'),
            __('Settings', 'massnahme-gift-cards'),
            'manage_woocommerce',
            'mgc-settings',
            [$this, 'settings_page']
        );
    }

    public function pickup_orders_page() {
        require_once MGC_PLUGIN_DIR . 'templates/admin-pickup-orders.php';
    }
    
    public function dashboard_page() {
        require_once MGC_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }
    
    public function validate_page() {
        require_once MGC_PLUGIN_DIR . 'templates/admin-validator.php';
    }
    
    public function settings_page() {
        // Handle form submission
        if (isset($_POST['mgc_save_settings'])) {
            check_admin_referer('mgc_settings_nonce');

            // Sanitize store locations
            $store_locations = [];
            if (!empty($_POST['store_locations']) && is_array($_POST['store_locations'])) {
                foreach ($_POST['store_locations'] as $index => $location) {
                    if (!empty($location['name'])) {
                        $store_locations[$index] = [
                            'name' => sanitize_text_field($location['name']),
                            'address' => sanitize_textarea_field($location['address'] ?? ''),
                            'email' => sanitize_email($location['email'] ?? ''),
                            'phone' => sanitize_text_field($location['phone'] ?? ''),
                            'hours' => sanitize_text_field($location['hours'] ?? '')
                        ];
                    }
                }
            }

            $settings = [
                'expiry_days' => intval($_POST['expiry_days']),
                'code_prefix' => sanitize_text_field($_POST['code_prefix']),
                'enable_pdf' => isset($_POST['enable_pdf']),
                'enable_qr' => isset($_POST['enable_qr']),
                // Custom amount settings
                'enable_custom_amount' => isset($_POST['enable_custom_amount']),
                'custom_min_amount' => max(1, intval($_POST['custom_min_amount'] ?? 50)),
                'custom_max_amount' => max(1, intval($_POST['custom_max_amount'] ?? 300)),
                // Delivery options
                'enable_digital' => isset($_POST['enable_digital']),
                'enable_pickup' => isset($_POST['enable_pickup']),
                'enable_shipping' => isset($_POST['enable_shipping']),
                'shipping_cost' => floatval($_POST['shipping_cost'] ?? 9.95),
                'shipping_time' => sanitize_text_field($_POST['shipping_time'] ?? '3-5 business days'),
                // Store locations
                'store_locations' => $store_locations
            ];

            update_option('mgc_settings', $settings);

            echo '<div class="notice notice-success"><p>' . __('Settings saved', 'massnahme-gift-cards') . '</p></div>';
        }

        require_once MGC_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'mgc-') === false) {
            return;
        }
        
        wp_enqueue_style(
            'mgc-admin',
            MGC_PLUGIN_URL . 'assets/css/admin.css',
            [],
            MGC_VERSION
        );
        
        wp_enqueue_script(
            'mgc-admin',
            MGC_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            MGC_VERSION,
            true
        );
        
        wp_localize_script('mgc-admin', 'mgc_admin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mgc_admin_nonce'),
            'currency' => get_woocommerce_currency()
        ]);
    }
    
    public function add_screen_ids($screen_ids) {
        $screen_ids[] = 'toplevel_page_mgc-gift-cards';
        $screen_ids[] = 'gift-cards_page_mgc-validate';
        $screen_ids[] = 'gift-cards_page_mgc-pickup-orders';
        $screen_ids[] = 'gift-cards_page_mgc-settings';
        return $screen_ids;
    }

    /**
     * AJAX handler for updating gift card balance
     */
    public function ajax_update_balance() {
        // Security checks
        check_ajax_referer('mgc_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Permission denied', 'massnahme-gift-cards'));
        }

        $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        $new_balance = isset($_POST['balance']) ? floatval($_POST['balance']) : -1;

        if (empty($code)) {
            wp_send_json_error(__('Invalid gift card code', 'massnahme-gift-cards'));
        }

        if ($new_balance < 0) {
            wp_send_json_error(__('Balance cannot be negative', 'massnahme-gift-cards'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';

        // Get current gift card data
        $gift_card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE code = %s",
            $code
        ));

        if (!$gift_card) {
            wp_send_json_error(__('Gift card not found', 'massnahme-gift-cards'));
        }

        // Balance cannot exceed original amount
        if ($new_balance > floatval($gift_card->amount)) {
            wp_send_json_error(__('Balance cannot exceed original amount', 'massnahme-gift-cards'));
        }

        $old_balance = floatval($gift_card->balance);

        // Determine new status based on balance
        $new_status = $new_balance > 0 ? 'active' : 'used';

        // Update database
        $updated = $wpdb->update(
            $table,
            [
                'balance' => $new_balance,
                'status' => $new_status
            ],
            ['code' => $code],
            ['%f', '%s'],
            ['%s']
        );

        if ($updated === false) {
            wp_send_json_error(__('Failed to update balance', 'massnahme-gift-cards'));
        }

        // Update WooCommerce coupon meta
        $coupon = new WC_Coupon($code);
        if ($coupon->get_id()) {
            $coupon->update_meta_data('_mgc_balance', $new_balance);
            $coupon->save();
        }

        // Log the manual balance change
        $wpdb->insert(
            $wpdb->prefix . 'mgc_gift_card_usage',
            [
                'gift_card_code' => $code,
                'order_id' => 0, // 0 indicates manual adjustment
                'amount_used' => $old_balance - $new_balance,
                'remaining_balance' => $new_balance,
                'used_at' => current_time('mysql')
            ]
        );

        wp_send_json_success([
            'message' => __('Balance updated successfully', 'massnahme-gift-cards'),
            'new_balance' => $new_balance,
            'new_status' => $new_status,
            'formatted_balance' => wc_price($new_balance)
        ]);
    }

    /**
     * AJAX handler for staff gift card lookup
     */
    public function ajax_staff_lookup() {
        check_ajax_referer('mgc_admin_nonce', 'nonce');

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
            'expires_at' => date_i18n(get_option('date_format'), strtotime($gift_card->expires_at)),
            'created_at' => date_i18n(get_option('date_format'), strtotime($gift_card->created_at)),
            'history' => $history_data
        ]);
    }

    /**
     * AJAX handler for updating pickup status
     */
    public function ajax_update_pickup_status() {
        check_ajax_referer('mgc_admin_nonce', 'nonce');

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

        // Send notification email when status changes to "ready"
        if ($status === 'ready' && $old_status !== 'ready') {
            $this->send_ready_for_pickup_notification($gift_card);
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
     * Send notification when gift card is ready for pickup
     */
    private function send_ready_for_pickup_notification($gift_card) {
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