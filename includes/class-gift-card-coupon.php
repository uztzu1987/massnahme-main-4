<?php
/**
 * Coupon management for gift cards
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MGC_Coupon {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Hook into coupon validation
        add_filter('woocommerce_coupon_is_valid', [$this, 'validate_gift_coupon'], 10, 2);
        add_filter('woocommerce_coupon_error', [$this, 'custom_coupon_messages'], 10, 3);

        // Track usage after order is placed
        add_action('woocommerce_checkout_order_processed', [$this, 'process_order_coupons'], 10, 1);

        // Partial redemption support
        add_filter('woocommerce_coupon_get_discount_amount', [$this, 'calculate_discount'], 10, 5);
    }
    
    /**
     * Create WooCommerce coupon for gift card
     */
    public function create_coupon($code, $amount, $order_id) {
        $coupon = new WC_Coupon();
        
        $coupon->set_code($code);
        $coupon->set_amount($amount);
        $coupon->set_discount_type('fixed_cart');
        $coupon->set_description(
            sprintf(__('Gift Card - Order #%s', 'massnahme-gift-cards'), $order_id)
        );
        
        // Set restrictions
        $coupon->set_individual_use(true);
        $coupon->set_usage_limit(0); // Unlimited for partial redemption
        $coupon->set_usage_limit_per_user(0);
        $coupon->set_limit_usage_to_x_items(null);
        
        // Set expiry
        $settings = get_option('mgc_settings');
        $expiry_days = isset($settings['expiry_days']) ? $settings['expiry_days'] : 730;
        $coupon->set_date_expires(strtotime('+' . $expiry_days . ' days'));
        
        // Add metadata
        $coupon->add_meta_data('_mgc_gift_card', 'yes', true);
        $coupon->add_meta_data('_mgc_original_amount', $amount, true);
        $coupon->add_meta_data('_mgc_balance', $amount, true);
        $coupon->add_meta_data('_mgc_order_id', $order_id, true);
        
        $coupon->save();
        
        return $coupon->get_id();
    }
    
    /**
     * Validate gift card coupon
     */
    public function validate_gift_coupon($valid, $coupon) {
        // Skip if not a gift card
        if ($coupon->get_meta('_mgc_gift_card') !== 'yes') {
            return $valid;
        }
        
        // Check balance
        $balance = floatval($coupon->get_meta('_mgc_balance'));
        if ($balance <= 0) {
            throw new Exception(__('This gift card has no remaining balance', 'massnahme-gift-cards'));
        }
        
        // Check in database
        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';
        $gift_card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE code = %s",
            $coupon->get_code()
        ));
        
        if (!$gift_card) {
            throw new Exception(__('Invalid gift card', 'massnahme-gift-cards'));
        }
        
        if ($gift_card->status !== 'active') {
            throw new Exception(__('This gift card is no longer active', 'massnahme-gift-cards'));
        }
        
        if (strtotime($gift_card->expires_at) < current_time('timestamp')) {
            throw new Exception(__('This gift card has expired', 'massnahme-gift-cards'));
        }
        
        return $valid;
    }
    
    /**
     * Calculate discount amount for partial redemption
     */
    public function calculate_discount($discount, $discounting_amount, $cart_item, $single, $coupon) {
        // Skip if not a gift card
        if ($coupon->get_meta('_mgc_gift_card') !== 'yes') {
            return $discount;
        }
        
        $balance = floatval($coupon->get_meta('_mgc_balance'));
        $cart_total = WC()->cart->get_subtotal();
        
        // If balance is less than cart total, use balance
        if ($balance < $cart_total) {
            return $balance;
        }
        
        // Otherwise use cart total
        return $cart_total;
    }
    
    /**
     * Process all gift card coupons in an order
     */
    public function process_order_coupons($order_id) {
        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        // Process each coupon used in the order
        foreach ($order->get_coupon_codes() as $coupon_code) {
            $coupon = new WC_Coupon($coupon_code);

            // Check if this is a gift card coupon
            if ($coupon->get_meta('_mgc_gift_card') === 'yes') {
                $this->update_gift_card_balance($coupon, $order_id);
            }
        }
    }
    
    /**
     * Update gift card balance after use
     * Uses atomic SQL to prevent race conditions
     */
    public function update_gift_card_balance($coupon, $order_id) {
        $order = wc_get_order($order_id);
        $code = $coupon->get_code();

        // Find how much was used from this gift card
        $used_amount = 0;
        foreach ($order->get_coupon_codes() as $applied_code) {
            if ($applied_code === $code) {
                foreach ($order->get_items('coupon') as $item) {
                    if ($item->get_code() === $code) {
                        $used_amount = floatval($item->get_discount());
                        break;
                    }
                }
            }
        }

        if ($used_amount <= 0) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';

        // Use atomic SQL update to prevent race conditions
        // This ensures the balance is calculated in the database, not PHP
        $wpdb->query('START TRANSACTION');

        try {
            // Lock the row and get current balance atomically
            $gift_card = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE code = %s FOR UPDATE",
                $code
            ));

            if (!$gift_card) {
                $wpdb->query('ROLLBACK');
                return;
            }

            $current_balance = floatval($gift_card->balance);

            // Prevent over-spending: only deduct what's available
            $actual_deduction = min($used_amount, $current_balance);
            $new_balance = max(0, $current_balance - $actual_deduction);

            // Update database atomically
            $wpdb->update(
                $table,
                [
                    'balance' => $new_balance,
                    'status' => $new_balance <= 0 ? 'used' : 'active'
                ],
                ['code' => $code],
                ['%f', '%s'],
                ['%s']
            );

            // Update coupon meta
            $coupon->update_meta_data('_mgc_balance', $new_balance);
            $coupon->save();

            // Log usage in database
            $wpdb->insert(
                $wpdb->prefix . 'mgc_gift_card_usage',
                [
                    'gift_card_code' => $code,
                    'order_id' => $order_id,
                    'amount_used' => $actual_deduction,
                    'remaining_balance' => $new_balance,
                    'used_at' => current_time('mysql')
                ]
            );

            $wpdb->query('COMMIT');

            // Add order note
            $order->add_order_note(sprintf(
                __('Gift card %s used. Amount: %s, Remaining balance: %s', 'massnahme-gift-cards'),
                $code,
                wc_price($actual_deduction),
                wc_price($new_balance)
            ));

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('MGC Balance Update Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Custom error messages for gift cards
     */
    public function custom_coupon_messages($err_msg, $err_code, $coupon) {
        if ($coupon->get_meta('_mgc_gift_card') !== 'yes') {
            return $err_msg;
        }
        
        switch ($err_code) {
            case WC_Coupon::E_WC_COUPON_EXPIRED:
                return __('This gift card has expired.', 'massnahme-gift-cards');
            case WC_Coupon::E_WC_COUPON_INVALID_REMOVED:
                return __('Gift card removed from cart.', 'massnahme-gift-cards');
            case WC_Coupon::E_WC_COUPON_NOT_EXIST:
                return __('Invalid gift card code.', 'massnahme-gift-cards');
            default:
                return $err_msg;
        }
    }
    
    /**
     * Get gift card balance
     */
    public static function get_balance($code) {
        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';
        
        $balance = $wpdb->get_var($wpdb->prepare(
            "SELECT balance FROM $table WHERE code = %s AND status = 'active'",
            $code
        ));
        
        return $balance ? floatval($balance) : 0;
    }
    
    /**
     * Check if code is valid gift card
     */
    public static function is_gift_card($code) {
        $coupon = new WC_Coupon($code);
        return $coupon->get_meta('_mgc_gift_card') === 'yes';
    }
}