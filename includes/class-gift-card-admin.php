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
        add_action('wp_ajax_mgc_admin_list_cards', [$this, 'ajax_admin_list_cards']);
        add_action('wp_ajax_mgc_admin_list_transactions', [$this, 'ajax_admin_list_transactions']);
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
            'admin_url' => admin_url(),
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

        // Log the manual balance change with user tracking
        $wpdb->insert(
            $wpdb->prefix . 'mgc_gift_card_usage',
            [
                'gift_card_code' => $code,
                'order_id' => 0, // 0 indicates manual adjustment
                'amount_used' => $old_balance - $new_balance,
                'remaining_balance' => $new_balance,
                'updated_by' => get_current_user_id(),
                'used_at' => current_time('mysql')
            ]
        );

        wp_send_json_success([
            'message' => __('Balance updated successfully', 'massnahme-gift-cards'),
            'new_balance' => $new_balance,
            'new_status' => $new_status,
            'formatted_balance' => html_entity_decode(strip_tags(wc_price($new_balance)), ENT_QUOTES, 'UTF-8')
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
            $user_name = '';
            if (!empty($item->updated_by)) {
                $user = get_user_by('id', $item->updated_by);
                $user_name = $user ? $user->display_name : __('Unknown User', 'massnahme-gift-cards');
            }
            $history_data[] = [
                'date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->used_at)),
                'amount' => floatval($item->amount_used),
                'order_id' => intval($item->order_id),
                'remaining' => floatval($item->remaining_balance),
                'updated_by' => intval($item->updated_by ?? 0),
                'updated_by_name' => $user_name
            ];
        }

        wp_send_json_success([
            'id' => intval($gift_card->id),
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

        // Email sending disabled
        // wp_mail($to, $subject, $message, $headers);
    }

    /**
     * AJAX handler for listing all gift cards with filtering and pagination
     */
    public function ajax_admin_list_cards() {
        check_ajax_referer('mgc_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Permission denied', 'massnahme-gift-cards'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';

        $page = max(1, intval($_POST['page'] ?? 1));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        // Build WHERE clause for filters
        $where = ['1=1'];
        $where_args = [];

        // Search filter
        $search = sanitize_text_field($_POST['search'] ?? '');
        if (!empty($search)) {
            $search_like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = "(code LIKE %s OR recipient_name LIKE %s OR recipient_email LIKE %s)";
            $where_args[] = $search_like;
            $where_args[] = $search_like;
            $where_args[] = $search_like;
        }

        // Status filter
        $status = sanitize_text_field($_POST['status'] ?? '');
        if (!empty($status) && in_array($status, ['active', 'used'])) {
            $where[] = "status = %s";
            $where_args[] = $status;
        }

        // Delivery method filter
        $delivery_method = sanitize_text_field($_POST['delivery_method'] ?? '');
        if (!empty($delivery_method) && in_array($delivery_method, ['digital', 'physical', 'pickup', 'shipping'])) {
            $where[] = "delivery_method = %s";
            $where_args[] = $delivery_method;
        }

        $where_clause = implode(' AND ', $where);

        // Get total count with filters
        $count_query = "SELECT COUNT(*) FROM $table WHERE $where_clause";
        if (!empty($where_args)) {
            $total = $wpdb->get_var($wpdb->prepare($count_query, $where_args));
        } else {
            $total = $wpdb->get_var($count_query);
        }

        // Get cards with filters
        $query = "SELECT * FROM $table WHERE $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $query_args = array_merge($where_args, [$per_page, $offset]);
        $cards = $wpdb->get_results($wpdb->prepare($query, $query_args));

        $cards_data = [];
        foreach ($cards as $card) {
            $cards_data[] = [
                'id' => intval($card->id),
                'code' => $card->code,
                'amount' => floatval($card->amount),
                'balance' => floatval($card->balance),
                'formatted_amount' => html_entity_decode(strip_tags(wc_price($card->amount)), ENT_QUOTES, 'UTF-8'),
                'formatted_balance' => html_entity_decode(strip_tags(wc_price($card->balance)), ENT_QUOTES, 'UTF-8'),
                'recipient_name' => $card->recipient_name ?? '',
                'recipient_email' => $card->recipient_email ?? '',
                'delivery_method' => $card->delivery_method ?? 'digital',
                'status' => $card->status,
                'created_at' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($card->created_at)),
                'expires_at' => date_i18n(get_option('date_format'), strtotime($card->expires_at)),
                'order_id' => intval($card->order_id)
            ];
        }

        wp_send_json_success([
            'cards' => $cards_data,
            'total' => intval($total),
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ]);
    }

    /**
     * AJAX handler for listing all transactions with filtering and pagination
     */
    public function ajax_admin_list_transactions() {
        check_ajax_referer('mgc_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Permission denied', 'massnahme-gift-cards'));
        }

        global $wpdb;
        $usage_table = $wpdb->prefix . 'mgc_gift_card_usage';

        $page = max(1, intval($_POST['page'] ?? 1));
        $per_page = 25;
        $offset = ($page - 1) * $per_page;

        // Build WHERE clause for filters
        $where = ['1=1'];
        $where_args = [];

        // Search by gift card code
        $search = sanitize_text_field($_POST['search'] ?? '');
        if (!empty($search)) {
            $search_like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = "gift_card_code LIKE %s";
            $where_args[] = $search_like;
        }

        // Filter by user
        $user_id = intval($_POST['user_id'] ?? 0);
        if ($user_id > 0) {
            $where[] = "updated_by = %d";
            $where_args[] = $user_id;
        }

        // Filter by transaction type
        $tx_type = sanitize_text_field($_POST['tx_type'] ?? '');
        if (!empty($tx_type)) {
            switch ($tx_type) {
                case 'creation':
                    $where[] = "amount_used = 0";
                    break;
                case 'redemption':
                    $where[] = "amount_used > 0";
                    break;
                case 'adjustment':
                    $where[] = "amount_used < 0";
                    break;
            }
        }

        $where_clause = implode(' AND ', $where);

        // Get total count with filters
        $count_query = "SELECT COUNT(*) FROM $usage_table WHERE $where_clause";
        if (!empty($where_args)) {
            $total = $wpdb->get_var($wpdb->prepare($count_query, $where_args));
        } else {
            $total = $wpdb->get_var($count_query);
        }

        // Get transactions with filters
        $query = "SELECT * FROM $usage_table WHERE $where_clause ORDER BY used_at DESC LIMIT %d OFFSET %d";
        $query_args = array_merge($where_args, [$per_page, $offset]);
        $transactions = $wpdb->get_results($wpdb->prepare($query, $query_args));

        $transactions_data = [];
        foreach ($transactions as $tx) {
            // Get user info
            $user_name = '';
            if (!empty($tx->updated_by)) {
                $user = get_user_by('id', $tx->updated_by);
                $user_name = $user ? $user->display_name : __('Unknown User', 'massnahme-gift-cards');
            }

            // Determine transaction type
            $tx_type = 'redemption';
            if (floatval($tx->amount_used) == 0) {
                $tx_type = 'creation';
            } elseif (floatval($tx->amount_used) < 0) {
                $tx_type = 'adjustment';
            }

            $transactions_data[] = [
                'id' => intval($tx->id),
                'gift_card_code' => $tx->gift_card_code,
                'order_id' => intval($tx->order_id),
                'amount_used' => floatval($tx->amount_used),
                'remaining_balance' => floatval($tx->remaining_balance),
                'formatted_amount' => html_entity_decode(strip_tags(wc_price($tx->amount_used)), ENT_QUOTES, 'UTF-8'),
                'formatted_balance' => html_entity_decode(strip_tags(wc_price($tx->remaining_balance)), ENT_QUOTES, 'UTF-8'),
                'updated_by' => intval($tx->updated_by ?? 0),
                'updated_by_name' => $user_name,
                'date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($tx->used_at)),
                'type' => $tx_type
            ];
        }

        wp_send_json_success([
            'transactions' => $transactions_data,
            'total' => intval($total),
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ]);
    }
}