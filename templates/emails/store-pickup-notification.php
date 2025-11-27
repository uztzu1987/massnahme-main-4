<?php
/**
 * Store Pickup Notification Email Template
 *
 * Sent to store staff when a gift card is ordered for pickup
 */

defined('ABSPATH') || exit;

$store_name = get_bloginfo('name');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Gift Card Pickup Order', 'massnahme-gift-cards'); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #d4a373; padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">
                                <?php _e('Gift Card Pickup Order', 'massnahme-gift-cards'); ?>
                            </h1>
                        </td>
                    </tr>

                    <!-- Alert Banner -->
                    <tr>
                        <td style="background-color: #fef3cd; padding: 15px 30px; border-bottom: 1px solid #ffc107;">
                            <p style="margin: 0; color: #856404; font-size: 14px; text-align: center;">
                                <strong><?php _e('Action Required:', 'massnahme-gift-cards'); ?></strong>
                                <?php _e('Please prepare this gift card for customer pickup.', 'massnahme-gift-cards'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Order Details -->
                    <tr>
                        <td style="padding: 30px;">
                            <h2 style="margin: 0 0 20px; color: #1a1a1a; font-size: 18px;">
                                <?php printf(__('Order #%s', 'massnahme-gift-cards'), esc_html($order_id)); ?>
                            </h2>

                            <!-- Gift Card Info -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="padding-bottom: 15px;">
                                                    <span style="color: #666; font-size: 12px; text-transform: uppercase;"><?php _e('Gift Card Value', 'massnahme-gift-cards'); ?></span><br>
                                                    <strong style="color: #d4a373; font-size: 28px;"><?php echo wc_price($amount); ?></strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 15px;">
                                                    <span style="color: #666; font-size: 12px; text-transform: uppercase;"><?php _e('Gift Card Code', 'massnahme-gift-cards'); ?></span><br>
                                                    <strong style="color: #1a1a1a; font-size: 18px; font-family: monospace;"><?php echo esc_html($code); ?></strong>
                                                </td>
                                            </tr>
                                            <?php if (!empty($recipient_name)) : ?>
                                            <tr>
                                                <td>
                                                    <span style="color: #666; font-size: 12px; text-transform: uppercase;"><?php _e('Recipient Name (for card)', 'massnahme-gift-cards'); ?></span><br>
                                                    <strong style="color: #1a1a1a; font-size: 16px;"><?php echo esc_html($recipient_name); ?></strong>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <?php if (!empty($message)) : ?>
                            <!-- Personal Message -->
                            <div style="background-color: #fff8e1; padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #d4a373;">
                                <p style="margin: 0 0 5px; color: #666; font-size: 12px; text-transform: uppercase;"><?php _e('Personal Message to Include', 'massnahme-gift-cards'); ?></p>
                                <p style="margin: 0; color: #1a1a1a; font-size: 14px; font-style: italic;">"<?php echo esc_html($message); ?>"</p>
                            </div>
                            <?php endif; ?>

                            <!-- Customer Details -->
                            <h3 style="margin: 0 0 15px; color: #1a1a1a; font-size: 16px;"><?php _e('Customer Details', 'massnahme-gift-cards'); ?></h3>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">
                                        <span style="color: #666;"><?php _e('Name:', 'massnahme-gift-cards'); ?></span>
                                        <strong style="float: right; color: #1a1a1a;"><?php echo esc_html($customer_name); ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">
                                        <span style="color: #666;"><?php _e('Email:', 'massnahme-gift-cards'); ?></span>
                                        <strong style="float: right; color: #1a1a1a;"><?php echo esc_html($customer_email); ?></strong>
                                    </td>
                                </tr>
                                <?php if (!empty($customer_phone)) : ?>
                                <tr>
                                    <td style="padding: 8px 0;">
                                        <span style="color: #666;"><?php _e('Phone:', 'massnahme-gift-cards'); ?></span>
                                        <strong style="float: right; color: #1a1a1a;"><?php echo esc_html($customer_phone); ?></strong>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>

                            <!-- Instructions -->
                            <div style="background-color: #e8f5e9; padding: 20px; border-radius: 8px;">
                                <h4 style="margin: 0 0 10px; color: #2e7d32; font-size: 14px;"><?php _e('Preparation Checklist', 'massnahme-gift-cards'); ?></h4>
                                <ul style="margin: 0; padding-left: 20px; color: #1a1a1a; font-size: 14px; line-height: 1.8;">
                                    <li><?php _e('Print the gift card with the code above', 'massnahme-gift-cards'); ?></li>
                                    <li><?php _e('Prepare premium packaging', 'massnahme-gift-cards'); ?></li>
                                    <?php if (!empty($message)) : ?>
                                    <li><?php _e('Include the personal message card', 'massnahme-gift-cards'); ?></li>
                                    <?php endif; ?>
                                    <li><?php _e('Notify customer when ready for pickup', 'massnahme-gift-cards'); ?></li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #eee;">
                            <p style="margin: 0; color: #666; font-size: 12px;">
                                <?php echo esc_html($store_name); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
