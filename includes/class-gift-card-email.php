<?php
/**
 * Email handling for gift cards
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MGC_Email {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Add custom email classes
        add_filter('woocommerce_email_classes', [$this, 'add_email_classes']);

        // Schedule delayed emails
        add_action('mgc_send_scheduled_gift_card', [$this, 'send_scheduled_gift_card'], 10, 2);

        // Email actions
        add_action('mgc_gift_card_created', [$this, 'trigger_gift_card_email'], 10, 2);
    }

    /**
     * Add custom email classes to WooCommerce
     */
    public function add_email_classes($emails) {
        // Return the emails array as-is for now
        // Custom email classes can be added here in the future if needed
        return $emails;
    }

    /**
     * Send gift card email
     */
    public function send_gift_card($code, $order) {
        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';
        
        // Get gift card details
        $gift_card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE code = %s",
            $code
        ));
        
        if (!$gift_card) {
            return false;
        }
        
        // Check for scheduled delivery
        $delivery_date = $order->get_meta('_mgc_delivery_date');
        if ($delivery_date && strtotime($delivery_date) > current_time('timestamp')) {
            // Schedule for later
            wp_schedule_single_event(
                strtotime($delivery_date),
                'mgc_send_scheduled_gift_card',
                [$code, $order->get_id()]
            );
            
            // Send confirmation to purchaser
            $this->send_purchase_confirmation($gift_card, $order, $delivery_date);
            return true;
        }
        
        // Send immediately
        return $this->send_gift_card_now($gift_card, $order);
    }
    
    /**
     * Send gift card email immediately
     */
    private function send_gift_card_now($gift_card, $order) {
        $recipient_email = $gift_card->recipient_email;
        $purchaser_email = $gift_card->purchaser_email;
        
        // Prepare email data
        $email_data = [
            'code' => $gift_card->code,
            'amount' => $gift_card->amount,
            'balance' => $gift_card->balance,
            'message' => $gift_card->message,
            'expires_at' => $gift_card->expires_at,
            'purchaser_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'order' => $order
        ];
        
        // Get email template
        $email_content = $this->get_email_template('gift-card', $email_data);
        
        // Email headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('woocommerce_email_from_address') . '>'
        ];
        
        // Send to recipient
        $sent = wp_mail(
            $recipient_email,
            $this->get_email_subject('gift-card', $email_data),
            $email_content,
            $headers,
            $this->get_attachments($gift_card)
        );
        
        // Send copy to purchaser if different
        if ($recipient_email !== $purchaser_email) {
            $this->send_purchase_confirmation($gift_card, $order);
        }
        
        // Log email sent
        if ($sent) {
            $order->add_order_note(sprintf(
                __('Gift card %s emailed to %s', 'massnahme-gift-cards'),
                $gift_card->code,
                $recipient_email
            ));
        }
        
        return $sent;
    }
    
    /**
     * Send purchase confirmation
     */
    private function send_purchase_confirmation($gift_card, $order, $scheduled_date = null) {
        $email_data = [
            'code' => $gift_card->code,
            'amount' => $gift_card->amount,
            'recipient_email' => $gift_card->recipient_email,
            'scheduled_date' => $scheduled_date,
            'order' => $order
        ];
        
        $email_content = $this->get_email_template('purchase-confirmation', $email_data);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('woocommerce_email_from_address') . '>'
        ];
        
        wp_mail(
            $gift_card->purchaser_email,
            $this->get_email_subject('purchase-confirmation', $email_data),
            $email_content,
            $headers
        );
    }
    
    /**
     * Send scheduled gift card
     */
    public function send_scheduled_gift_card($code, $order_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';
        
        $gift_card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE code = %s",
            $code
        ));
        
        if (!$gift_card) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $this->send_gift_card_now($gift_card, $order);
    }
    
    /**
     * Get email template
     */
    private function get_email_template($template_name, $data) {
        ob_start();
        
        // Look for template override in theme
        $template_path = locate_template(
            'massnahme-gift-cards/emails/' . $template_name . '.php'
        );
        
        if (!$template_path) {
            $template_path = MGC_PLUGIN_DIR . 'templates/emails/' . $template_name . '.php';
        }
        
        if (file_exists($template_path)) {
            extract($data);
            include $template_path;
        }
        
        return ob_get_clean();
    }
    
    /**
     * Get email subject
     */
    private function get_email_subject($template_name, $data) {
        $subjects = [
            'gift-card' => sprintf(
                __('Your %s Gift Card from %s', 'massnahme-gift-cards'),
                get_bloginfo('name'),
                $data['purchaser_name'] ?? get_bloginfo('name')
            ),
            'purchase-confirmation' => __('Gift Card Purchase Confirmation', 'massnahme-gift-cards'),
            'balance-update' => __('Gift Card Balance Update', 'massnahme-gift-cards'),
            'store-pickup-notification' => sprintf(
                __('[Action Required] Gift Card Pickup Order #%s', 'massnahme-gift-cards'),
                $data['order_id'] ?? ''
            ),
            'pickup-confirmation' => __('Your Gift Card is Being Prepared for Pickup', 'massnahme-gift-cards'),
            'shipping-confirmation' => __('Your Gift Card is Being Shipped', 'massnahme-gift-cards')
        ];

        return apply_filters('mgc_email_subject',
            $subjects[$template_name] ?? __('Gift Card', 'massnahme-gift-cards'),
            $template_name,
            $data
        );
    }

    /**
     * Send store pickup notification to store staff
     */
    public function send_store_pickup_notification($code, $order) {
        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';

        $gift_card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE code = %s",
            $code
        ));

        if (!$gift_card) {
            return false;
        }

        $settings = get_option('mgc_settings', []);
        $store_locations = $settings['store_locations'] ?? [];
        $pickup_location_id = $gift_card->pickup_location;

        // Get the store location details
        $store = $store_locations[$pickup_location_id] ?? null;

        if (!$store || empty($store['email'])) {
            // Fallback to admin email if no store email configured
            $store_email = get_option('admin_email');
        } else {
            $store_email = $store['email'];
        }

        $email_data = [
            'code' => $gift_card->code,
            'amount' => $gift_card->amount,
            'recipient_name' => $gift_card->recipient_name,
            'message' => $gift_card->message,
            'order_id' => $order->get_id(),
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'customer_email' => $order->get_billing_email(),
            'customer_phone' => $order->get_billing_phone(),
            'store' => $store,
            'order' => $order
        ];

        $email_content = $this->get_email_template('store-pickup-notification', $email_data);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('woocommerce_email_from_address') . '>'
        ];

        $sent = wp_mail(
            $store_email,
            $this->get_email_subject('store-pickup-notification', $email_data),
            $email_content,
            $headers
        );

        if ($sent) {
            $order->add_order_note(sprintf(
                __('Store pickup notification sent to %s for gift card %s', 'massnahme-gift-cards'),
                $store_email,
                $code
            ));
        }

        return $sent;
    }

    /**
     * Send pickup confirmation to purchaser
     */
    public function send_pickup_confirmation($code, $order) {
        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';

        $gift_card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE code = %s",
            $code
        ));

        if (!$gift_card) {
            return false;
        }

        $settings = get_option('mgc_settings', []);
        $store_locations = $settings['store_locations'] ?? [];
        $pickup_location_id = $gift_card->pickup_location;
        $store = $store_locations[$pickup_location_id] ?? null;

        $email_data = [
            'code' => $gift_card->code,
            'amount' => $gift_card->amount,
            'recipient_name' => $gift_card->recipient_name,
            'message' => $gift_card->message,
            'expires_at' => $gift_card->expires_at,
            'store' => $store,
            'order' => $order
        ];

        $email_content = $this->get_email_template('pickup-confirmation', $email_data);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('woocommerce_email_from_address') . '>'
        ];

        return wp_mail(
            $gift_card->purchaser_email,
            $this->get_email_subject('pickup-confirmation', $email_data),
            $email_content,
            $headers
        );
    }

    /**
     * Send shipping confirmation to purchaser
     */
    public function send_shipping_confirmation($code, $order) {
        global $wpdb;
        $table = $wpdb->prefix . 'mgc_gift_cards';

        $gift_card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE code = %s",
            $code
        ));

        if (!$gift_card) {
            return false;
        }

        $settings = get_option('mgc_settings', []);

        $email_data = [
            'code' => $gift_card->code,
            'amount' => $gift_card->amount,
            'recipient_name' => $gift_card->recipient_name,
            'message' => $gift_card->message,
            'expires_at' => $gift_card->expires_at,
            'shipping_time' => $settings['shipping_time'] ?? '3-5 business days',
            'shipping_address' => $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address(),
            'order' => $order
        ];

        $email_content = $this->get_email_template('shipping-confirmation', $email_data);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('woocommerce_email_from_address') . '>'
        ];

        return wp_mail(
            $gift_card->purchaser_email,
            $this->get_email_subject('shipping-confirmation', $email_data),
            $email_content,
            $headers
        );
    }
    
    /**
     * Get email attachments (PDF if enabled)
     */
    private function get_attachments($gift_card) {
        $attachments = [];
        
        $settings = get_option('mgc_settings');
        if (!empty($settings['enable_pdf'])) {
            $pdf_path = $this->generate_pdf($gift_card);
            if ($pdf_path && file_exists($pdf_path)) {
                $attachments[] = $pdf_path;
            }
        }
        
        return $attachments;
    }
    
    /**
     * Generate PDF for gift card
     */
    private function generate_pdf($gift_card) {
        // Load our simple PDF generator
        if (!class_exists('MGC_Simple_PDF')) {
            require_once MGC_PLUGIN_DIR . 'lib/pdf/class-simple-pdf.php';
        }

        // Create upload directory
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/mgc-gift-cards/';

        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }

        // Generate PDF using our simple PDF class
        $pdf = new MGC_Simple_PDF();
        $pdf->addPage();

        // Header background
        $pdf->setFillColor(0, 0, 0);
        $pdf->rect(0, 0, $pdf->getPageWidth(), 120, 'F');

        // Store name in header
        $pdf->setTextColor(212, 175, 55); // Gold color
        $pdf->setFontSize(28);
        $pdf->setY(40);
        $pdf->write(html_entity_decode(get_bloginfo('name')), 'C');

        // Gift Card title
        $pdf->setTextColor(255, 255, 255);
        $pdf->setFontSize(16);
        $pdf->ln(15);
        $pdf->write('GIFT CARD', 'C');

        // Amount section
        $pdf->setY(160);
        $pdf->setTextColor(212, 175, 55);
        $pdf->setFontSize(48);
        $pdf->write(html_entity_decode(strip_tags(wc_price($gift_card->amount))), 'C');

        // Personal message if exists
        if (!empty($gift_card->message)) {
            $pdf->setY(230);
            $pdf->setTextColor(100, 100, 100);
            $pdf->setFontSize(12);
            $pdf->write('"' . $gift_card->message . '"', 'C');
        }

        // Code section
        $pdf->setY(290);
        $pdf->setFillColor(245, 245, 245);
        $pdf->rect(50, 280, $pdf->getPageWidth() - 100, 80, 'F');

        $pdf->setTextColor(100, 100, 100);
        $pdf->setFontSize(12);
        $pdf->write('Your Gift Card Code:', 'C');

        $pdf->ln(25);
        $pdf->setTextColor(0, 0, 0);
        $pdf->setFontSize(24);
        $pdf->write($gift_card->code, 'C');

        // Details section
        $pdf->setY(400);
        $pdf->setTextColor(80, 80, 80);
        $pdf->setFontSize(11);

        $pdf->write('Recipient: ' . $gift_card->recipient_email, 'C');
        $pdf->ln(20);
        $pdf->write('Issued: ' . date_i18n(get_option('date_format'), strtotime($gift_card->created_at)), 'C');
        $pdf->ln(20);
        $pdf->write('Expires: ' . date_i18n(get_option('date_format'), strtotime($gift_card->expires_at)), 'C');
        $pdf->ln(20);
        $pdf->write('Balance: ' . html_entity_decode(strip_tags(wc_price($gift_card->balance))), 'C');

        // Footer
        $pdf->setY(550);
        $pdf->setDrawColor(200, 200, 200);
        $pdf->line(50, 540, $pdf->getPageWidth() - 50, 540);

        $pdf->setTextColor(120, 120, 120);
        $pdf->setFontSize(10);
        $pdf->write('To redeem this gift card, enter the code above at checkout.', 'C');
        $pdf->ln(18);
        $pdf->write(html_entity_decode(get_bloginfo('name')) . ' - ' . home_url(), 'C');

        // Save PDF
        $pdf_filename = sanitize_file_name('gift-card-' . $gift_card->code . '.pdf');
        $pdf_path = $pdf_dir . $pdf_filename;

        if ($pdf->output($pdf_path)) {
            return $pdf_path;
        }

        // Fallback to HTML if PDF generation fails
        return $this->generate_html_pdf($gift_card);
    }
    
    /**
     * Generate HTML-based PDF alternative
     */
    private function generate_html_pdf($gift_card) {
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/mgc-gift-cards/';
        
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }
        
        $html_content = $this->get_pdf_content($gift_card);
        $filename = sanitize_file_name('gift-card-' . $gift_card->code . '.html');
        $filepath = $pdf_dir . $filename;

        file_put_contents($filepath, $html_content);

        return $filepath;
    }
    
    /**
     * Get PDF content
     */
    private function get_pdf_content($gift_card) {
        ob_start();
        include MGC_PLUGIN_DIR . 'templates/pdf/gift-card-pdf.php';
        return ob_get_clean();
    }
}