<?php
/**
 * Purchase Confirmation Email Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get WooCommerce email header
do_action('woocommerce_email_header', __('Gift Card Purchase Confirmation', 'massnahme-gift-cards'), null);
?>

<p><?php printf(__('Hello %s,', 'massnahme-gift-cards'), $order->get_billing_first_name()); ?></p>

<p><?php _e('Thank you for your gift card purchase!', 'massnahme-gift-cards'); ?></p>

<h2><?php _e('Gift Card Details', 'massnahme-gift-cards'); ?></h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
    <tbody>
        <tr>
            <th scope="row" style="text-align: left; border: 1px solid #eee;">
                <?php _e('Gift Card Code:', 'massnahme-gift-cards'); ?>
            </th>
            <td style="text-align: left; border: 1px solid #eee;">
                <strong><?php echo esc_html($code); ?></strong>
            </td>
        </tr>
        <tr>
            <th scope="row" style="text-align: left; border: 1px solid #eee;">
                <?php _e('Amount:', 'massnahme-gift-cards'); ?>
            </th>
            <td style="text-align: left; border: 1px solid #eee;">
                <?php echo wc_price($amount); ?>
            </td>
        </tr>
        <tr>
            <th scope="row" style="text-align: left; border: 1px solid #eee;">
                <?php _e('Recipient:', 'massnahme-gift-cards'); ?>
            </th>
            <td style="text-align: left; border: 1px solid #eee;">
                <?php echo esc_html($recipient_email); ?>
            </td>
        </tr>
        <?php if (!empty($scheduled_date)): ?>
        <tr>
            <th scope="row" style="text-align: left; border: 1px solid #eee;">
                <?php _e('Delivery Date:', 'massnahme-gift-cards'); ?>
            </th>
            <td style="text-align: left; border: 1px solid #eee;">
                <?php echo date_i18n(get_option('date_format'), strtotime($scheduled_date)); ?>
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($scheduled_date)): ?>
    <p>
        <strong><?php _e('Note:', 'massnahme-gift-cards'); ?></strong>
        <?php printf(
            __('The gift card will be sent to the recipient on %s.', 'massnahme-gift-cards'),
            date_i18n(get_option('date_format'), strtotime($scheduled_date))
        ); ?>
    </p>
<?php else: ?>
    <p><?php _e('The gift card has been sent to the recipient via email.', 'massnahme-gift-cards'); ?></p>
<?php endif; ?>

<h3><?php _e('Order Details', 'massnahme-gift-cards'); ?></h3>

<p>
    <?php printf(__('Order number: %s', 'massnahme-gift-cards'), $order->get_order_number()); ?><br>
    <?php printf(__('Order date: %s', 'massnahme-gift-cards'), date_i18n(get_option('date_format'), strtotime($order->get_date_created()))); ?>
</p>

<p>
    <?php _e('You can view your order details by clicking the link below:', 'massnahme-gift-cards'); ?>
</p>

<p>
    <a href="<?php echo esc_url($order->get_view_order_url()); ?>" style="display: inline-block; padding: 10px 20px; background: #2271b1; color: #fff; text-decoration: none; border-radius: 4px;">
        <?php _e('View Order', 'massnahme-gift-cards'); ?>
    </a>
</p>

<p><?php _e('Thank you for shopping with us!', 'massnahme-gift-cards'); ?></p>

<?php
// Get WooCommerce email footer
do_action('woocommerce_email_footer', null);
?>
