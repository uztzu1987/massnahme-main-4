<?php
/**
 * Checkout gift card fields template
 */

$settings = get_option('mgc_settings', []);
$store_locations = $settings['store_locations'] ?? [];

// Check which delivery methods are enabled
$digital_enabled = $settings['enable_digital'] ?? true;
$pickup_enabled = !empty($settings['enable_pickup']) && !empty($store_locations);
$shipping_enabled = !empty($settings['enable_shipping']);

// Count enabled methods
$enabled_count = ($digital_enabled ? 1 : 0) + ($pickup_enabled ? 1 : 0) + ($shipping_enabled ? 1 : 0);

// Default to digital if nothing is enabled
if ($enabled_count === 0) {
    $digital_enabled = true;
    $enabled_count = 1;
}
?>
<div class="mgc-checkout-fields">
    <h3><?php _e('Gift Card Details', 'massnahme-gift-cards'); ?></h3>

    <?php if ($enabled_count > 1) : ?>
    <div class="mgc-delivery-method-section">
        <label class="mgc-section-label"><?php _e('Delivery Method', 'massnahme-gift-cards'); ?></label>

        <div class="mgc-delivery-options">
            <?php if ($digital_enabled) : ?>
            <label class="mgc-delivery-option" data-method="digital">
                <input type="radio" name="mgc_delivery_method" value="digital" checked>
                <div class="mgc-option-content">
                    <span class="mgc-option-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/>
                        </svg>
                    </span>
                    <span class="mgc-option-title"><?php _e('Digital Delivery', 'massnahme-gift-cards'); ?></span>
                    <span class="mgc-option-desc"><?php _e('Instant email delivery', 'massnahme-gift-cards'); ?></span>
                    <span class="mgc-option-price"><?php _e('Free', 'massnahme-gift-cards'); ?></span>
                </div>
            </label>
            <?php endif; ?>

            <?php if ($pickup_enabled) : ?>
            <label class="mgc-delivery-option" data-method="pickup">
                <input type="radio" name="mgc_delivery_method" value="pickup" <?php echo !$digital_enabled ? 'checked' : ''; ?>>
                <div class="mgc-option-content">
                    <span class="mgc-option-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/>
                        </svg>
                    </span>
                    <span class="mgc-option-title"><?php _e('Store Pickup', 'massnahme-gift-cards'); ?></span>
                    <span class="mgc-option-desc"><?php _e('Premium packaging, ready in 24h', 'massnahme-gift-cards'); ?></span>
                    <span class="mgc-option-price"><?php _e('Free', 'massnahme-gift-cards'); ?></span>
                </div>
            </label>
            <?php endif; ?>

            <?php if ($shipping_enabled) : ?>
            <label class="mgc-delivery-option" data-method="shipping">
                <input type="radio" name="mgc_delivery_method" value="shipping" <?php echo !$digital_enabled && !$pickup_enabled ? 'checked' : ''; ?>>
                <div class="mgc-option-content">
                    <span class="mgc-option-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="3" width="15" height="13"/><polygon points="16,8 20,8 23,11 23,16 16,16 16,8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </span>
                    <span class="mgc-option-title"><?php _e('Luxury Shipping', 'massnahme-gift-cards'); ?></span>
                    <span class="mgc-option-desc"><?php echo esc_html($settings['shipping_time'] ?? '3-5 business days'); ?></span>
                    <span class="mgc-option-price"><?php echo wc_price($settings['shipping_cost'] ?? 9.95); ?></span>
                </div>
            </label>
            <?php endif; ?>
        </div>
    </div>
    <?php else : ?>
        <input type="hidden" name="mgc_delivery_method" value="<?php echo $digital_enabled ? 'digital' : ($pickup_enabled ? 'pickup' : 'shipping'); ?>">
    <?php endif; ?>

    <!-- Store Pickup Location (shown when pickup is selected) -->
    <?php if ($pickup_enabled) : ?>
    <div class="mgc-pickup-section mgc-conditional-section" id="mgc-pickup-section" style="display: none;">
        <label class="mgc-section-label"><?php _e('Select Pickup Location', 'massnahme-gift-cards'); ?> <abbr class="required" title="required">*</abbr></label>
        <div class="mgc-store-locations">
            <?php foreach ($store_locations as $index => $location) : ?>
            <label class="mgc-store-location-option">
                <input type="radio" name="mgc_pickup_location" value="<?php echo esc_attr($index); ?>" <?php echo $index === array_key_first($store_locations) ? 'checked' : ''; ?>>
                <div class="mgc-store-info">
                    <strong class="mgc-store-name"><?php echo esc_html($location['name']); ?></strong>
                    <?php if (!empty($location['address'])) : ?>
                    <span class="mgc-store-address"><?php echo esc_html($location['address']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($location['hours'])) : ?>
                    <span class="mgc-store-hours"><?php echo esc_html($location['hours']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($location['phone'])) : ?>
                    <span class="mgc-store-phone"><?php echo esc_html($location['phone']); ?></span>
                    <?php endif; ?>
                </div>
            </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Digital Delivery Fields -->
    <div class="mgc-digital-section mgc-conditional-section" id="mgc-digital-section">
        <p class="form-row form-row-wide">
            <label for="mgc_recipient_email">
                <?php _e('Recipient Email (optional)', 'massnahme-gift-cards'); ?>
            </label>
            <input type="email" class="input-text" name="mgc_recipient_email" id="mgc_recipient_email"
                   placeholder="<?php esc_attr_e('Send directly to recipient...', 'massnahme-gift-cards'); ?>">
            <span class="description"><?php _e('Leave blank to send to your own email', 'massnahme-gift-cards'); ?></span>
        </p>

        <p class="form-row form-row-wide">
            <label for="mgc_delivery_date">
                <?php _e('Delivery Date (optional)', 'massnahme-gift-cards'); ?>
            </label>
            <input type="date" class="input-text" name="mgc_delivery_date" id="mgc_delivery_date">
            <span class="description">
                <?php _e('Schedule email delivery for a future date', 'massnahme-gift-cards'); ?>
            </span>
        </p>
    </div>

    <!-- Personal Message (shown for all methods) -->
    <div class="mgc-message-section">
        <p class="form-row form-row-wide">
            <label for="mgc_message">
                <?php _e('Personal Message', 'massnahme-gift-cards'); ?>
            </label>
            <textarea name="mgc_message" class="input-text" id="mgc_message"
                      placeholder="<?php esc_attr_e('Add a personal message to your gift...', 'massnahme-gift-cards'); ?>"
                      rows="4"></textarea>
        </p>
    </div>

    <!-- Recipient Name (for pickup/shipping) -->
    <div class="mgc-recipient-section mgc-conditional-section" id="mgc-recipient-section" style="display: none;">
        <p class="form-row form-row-wide">
            <label for="mgc_recipient_name">
                <?php _e('Recipient Name', 'massnahme-gift-cards'); ?>
            </label>
            <input type="text" class="input-text" name="mgc_recipient_name" id="mgc_recipient_name"
                   placeholder="<?php esc_attr_e('Name to appear on the gift card', 'massnahme-gift-cards'); ?>">
        </p>
    </div>

    <!-- Shipping Notice -->
    <?php if ($shipping_enabled) : ?>
    <div class="mgc-shipping-notice mgc-conditional-section" id="mgc-shipping-notice" style="display: none;">
        <div class="mgc-notice mgc-notice-info">
            <strong><?php _e('Luxury Presentation', 'massnahme-gift-cards'); ?></strong>
            <p><?php _e('Your gift card will be beautifully presented in our signature packaging, including a premium envelope and personalized gift card.', 'massnahme-gift-cards'); ?></p>
            <p><small><?php printf(__('Shipping: %s | Estimated delivery: %s', 'massnahme-gift-cards'), wc_price($settings['shipping_cost'] ?? 9.95), esc_html($settings['shipping_time'] ?? '3-5 business days')); ?></small></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pickup Notice -->
    <?php if ($pickup_enabled) : ?>
    <div class="mgc-pickup-notice mgc-conditional-section" id="mgc-pickup-notice" style="display: none;">
        <div class="mgc-notice mgc-notice-info">
            <strong><?php _e('Premium In-Store Pickup', 'massnahme-gift-cards'); ?></strong>
            <p><?php _e('Your gift card will be prepared with our premium packaging. We will notify you when it\'s ready for pickup (usually within 24 hours).', 'massnahme-gift-cards'); ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>
