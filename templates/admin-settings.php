<?php
defined('ABSPATH') || exit;

if (!current_user_can('manage_woocommerce')) {
    return;
}

$settings = get_option('mgc_settings', []);
$store_locations = $settings['store_locations'] ?? [];
?>
<div class="wrap">
    <h1><?php _e('Massnahme Gift Cards Settings', 'massnahme-gift-cards'); ?></h1>

    <form method="post" action="">
        <?php wp_nonce_field('mgc_settings_nonce'); ?>

        <h2><?php _e('General Settings', 'massnahme-gift-cards'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="expiry_days"><?php _e('Expiry Days', 'massnahme-gift-cards'); ?></label></th>
                <td>
                    <input type="number" name="expiry_days" id="expiry_days" value="<?php echo esc_attr($settings['expiry_days'] ?? 730); ?>" class="small-text">
                </td>
            </tr>

            <tr>
                <th><label for="code_prefix"><?php _e('Code Prefix', 'massnahme-gift-cards'); ?></label></th>
                <td>
                    <input type="text" name="code_prefix" id="code_prefix" value="<?php echo esc_attr($settings['code_prefix'] ?? 'MASS'); ?>">
                </td>
            </tr>

            <tr>
                <th><?php _e('Enable PDF Attachment', 'massnahme-gift-cards'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_pdf" value="1" <?php checked(!empty($settings['enable_pdf'])); ?>>
                        <?php _e('Attach PDF to gift card emails', 'massnahme-gift-cards'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th><?php _e('Enable QR', 'massnahme-gift-cards'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_qr" value="1" <?php checked(!empty($settings['enable_qr'])); ?>>
                        <?php _e('Include QR code in PDFs/emails', 'massnahme-gift-cards'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <hr>
        <h2><?php _e('Custom Amount Gift Card', 'massnahme-gift-cards'); ?></h2>
        <p class="description"><?php _e('Configure the custom amount gift card that allows customers to choose their own value.', 'massnahme-gift-cards'); ?></p>

        <table class="form-table">
            <tr>
                <th><?php _e('Enable Custom Amount', 'massnahme-gift-cards'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_custom_amount" value="1" <?php checked($settings['enable_custom_amount'] ?? true); ?>>
                        <?php _e('Allow customers to choose their own gift card amount', 'massnahme-gift-cards'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th><label for="custom_min_amount"><?php _e('Minimum Amount', 'massnahme-gift-cards'); ?></label></th>
                <td>
                    <input type="number" name="custom_min_amount" id="custom_min_amount" value="<?php echo esc_attr($settings['custom_min_amount'] ?? 50); ?>" class="small-text" step="1" min="1">
                    <span class="description"><?php echo get_woocommerce_currency_symbol(); ?></span>
                </td>
            </tr>

            <tr>
                <th><label for="custom_max_amount"><?php _e('Maximum Amount', 'massnahme-gift-cards'); ?></label></th>
                <td>
                    <input type="number" name="custom_max_amount" id="custom_max_amount" value="<?php echo esc_attr($settings['custom_max_amount'] ?? 300); ?>" class="small-text" step="1" min="1">
                    <span class="description"><?php echo get_woocommerce_currency_symbol(); ?></span>
                </td>
            </tr>
        </table>

        <hr>
        <h2><?php _e('Delivery Options', 'massnahme-gift-cards'); ?></h2>

        <table class="form-table">
            <tr>
                <th><?php _e('Enable Digital Delivery', 'massnahme-gift-cards'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_digital" value="1" <?php checked($settings['enable_digital'] ?? true); ?>>
                        <?php _e('Email delivery (instant)', 'massnahme-gift-cards'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th><?php _e('Enable Store Pickup', 'massnahme-gift-cards'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_pickup" value="1" <?php checked(!empty($settings['enable_pickup'])); ?>>
                        <?php _e('Allow customers to pick up gift cards in store with premium packaging', 'massnahme-gift-cards'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th><?php _e('Enable Shipping', 'massnahme-gift-cards'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_shipping" value="1" <?php checked(!empty($settings['enable_shipping'])); ?>>
                        <?php _e('Mail physical gift cards with luxury presentation', 'massnahme-gift-cards'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th><label for="shipping_cost"><?php _e('Shipping Cost', 'massnahme-gift-cards'); ?></label></th>
                <td>
                    <input type="number" step="0.01" name="shipping_cost" id="shipping_cost" value="<?php echo esc_attr($settings['shipping_cost'] ?? '9.95'); ?>" class="small-text">
                    <span class="description"><?php echo get_woocommerce_currency_symbol(); ?></span>
                </td>
            </tr>

            <tr>
                <th><label for="shipping_time"><?php _e('Estimated Shipping Time', 'massnahme-gift-cards'); ?></label></th>
                <td>
                    <input type="text" name="shipping_time" id="shipping_time" value="<?php echo esc_attr($settings['shipping_time'] ?? '3-5 business days'); ?>" class="regular-text">
                </td>
            </tr>
        </table>

        <hr>
        <h2><?php _e('Store Locations', 'massnahme-gift-cards'); ?></h2>
        <p class="description"><?php _e('Configure store locations for gift card pickup. Customers will be able to select from these locations at checkout.', 'massnahme-gift-cards'); ?></p>

        <div id="mgc-store-locations">
            <?php if (!empty($store_locations)) : ?>
                <?php foreach ($store_locations as $index => $location) : ?>
                <div class="mgc-store-location" data-index="<?php echo $index; ?>">
                    <table class="form-table">
                        <tr>
                            <th><label><?php _e('Store Name', 'massnahme-gift-cards'); ?></label></th>
                            <td>
                                <input type="text" name="store_locations[<?php echo $index; ?>][name]" value="<?php echo esc_attr($location['name'] ?? ''); ?>" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Address', 'massnahme-gift-cards'); ?></label></th>
                            <td>
                                <textarea name="store_locations[<?php echo $index; ?>][address]" class="regular-text" rows="2"><?php echo esc_textarea($location['address'] ?? ''); ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Notification Email', 'massnahme-gift-cards'); ?></label></th>
                            <td>
                                <input type="email" name="store_locations[<?php echo $index; ?>][email]" value="<?php echo esc_attr($location['email'] ?? ''); ?>" class="regular-text">
                                <p class="description"><?php _e('Email address to notify when a pickup order is placed', 'massnahme-gift-cards'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Phone', 'massnahme-gift-cards'); ?></label></th>
                            <td>
                                <input type="text" name="store_locations[<?php echo $index; ?>][phone]" value="<?php echo esc_attr($location['phone'] ?? ''); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Hours', 'massnahme-gift-cards'); ?></label></th>
                            <td>
                                <input type="text" name="store_locations[<?php echo $index; ?>][hours]" value="<?php echo esc_attr($location['hours'] ?? ''); ?>" class="regular-text" placeholder="<?php esc_attr_e('Mon-Fri 10:00-18:00, Sat 10:00-16:00', 'massnahme-gift-cards'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <button type="button" class="button mgc-remove-location"><?php _e('Remove Location', 'massnahme-gift-cards'); ?></button>
                            </td>
                        </tr>
                    </table>
                    <hr>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <p>
            <button type="button" id="mgc-add-location" class="button"><?php _e('Add Store Location', 'massnahme-gift-cards'); ?></button>
        </p>

        <p class="submit">
            <button type="submit" name="mgc_save_settings" class="button button-primary"><?php _e('Save Settings', 'massnahme-gift-cards'); ?></button>
        </p>
    </form>
</div>

<script type="text/template" id="mgc-location-template">
    <div class="mgc-store-location" data-index="{{INDEX}}">
        <table class="form-table">
            <tr>
                <th><label><?php _e('Store Name', 'massnahme-gift-cards'); ?></label></th>
                <td>
                    <input type="text" name="store_locations[{{INDEX}}][name]" value="" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th><label><?php _e('Address', 'massnahme-gift-cards'); ?></label></th>
                <td>
                    <textarea name="store_locations[{{INDEX}}][address]" class="regular-text" rows="2"></textarea>
                </td>
            </tr>
            <tr>
                <th><label><?php _e('Notification Email', 'massnahme-gift-cards'); ?></label></th>
                <td>
                    <input type="email" name="store_locations[{{INDEX}}][email]" value="" class="regular-text">
                    <p class="description"><?php _e('Email address to notify when a pickup order is placed', 'massnahme-gift-cards'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label><?php _e('Phone', 'massnahme-gift-cards'); ?></label></th>
                <td>
                    <input type="text" name="store_locations[{{INDEX}}][phone]" value="" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label><?php _e('Hours', 'massnahme-gift-cards'); ?></label></th>
                <td>
                    <input type="text" name="store_locations[{{INDEX}}][hours]" value="" class="regular-text" placeholder="<?php esc_attr_e('Mon-Fri 10:00-18:00, Sat 10:00-16:00', 'massnahme-gift-cards'); ?>">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button type="button" class="button mgc-remove-location"><?php _e('Remove Location', 'massnahme-gift-cards'); ?></button>
                </td>
            </tr>
        </table>
        <hr>
    </div>
</script>

<script>
jQuery(function($) {
    var locationIndex = <?php echo !empty($store_locations) ? max(array_keys($store_locations)) + 1 : 0; ?>;

    $('#mgc-add-location').on('click', function() {
        var template = $('#mgc-location-template').html();
        template = template.replace(/\{\{INDEX\}\}/g, locationIndex);
        $('#mgc-store-locations').append(template);
        locationIndex++;
    });

    $(document).on('click', '.mgc-remove-location', function() {
        $(this).closest('.mgc-store-location').remove();
    });
});
</script>
