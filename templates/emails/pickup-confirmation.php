<?php
/**
 * Pickup Confirmation Email Template
 *
 * Sent to purchaser when gift card pickup is ordered
 */

defined('ABSPATH') || exit;

$store_name = get_bloginfo('name');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Gift Card Pickup Confirmation', 'massnahme-gift-cards'); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #1a1a1a; padding: 40px; text-align: center;">
                            <h1 style="margin: 0 0 10px; color: #d4af37; font-size: 28px; font-weight: 300; letter-spacing: 2px;">
                                <?php echo esc_html($store_name); ?>
                            </h1>
                            <p style="margin: 0; color: #ffffff; font-size: 14px; letter-spacing: 1px;">
                                <?php _e('GIFT CARD', 'massnahme-gift-cards'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Thank You Message -->
                    <tr>
                        <td style="padding: 40px 30px 20px; text-align: center;">
                            <h2 style="margin: 0 0 15px; color: #1a1a1a; font-size: 24px; font-weight: 400;">
                                <?php _e('Your Gift Card is Being Prepared', 'massnahme-gift-cards'); ?>
                            </h2>
                            <p style="margin: 0; color: #666; font-size: 16px; line-height: 1.6;">
                                <?php _e('Thank you for your purchase. We are preparing your gift card with our premium packaging. You will receive a notification when it is ready for pickup.', 'massnahme-gift-cards'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Gift Card Amount -->
                    <tr>
                        <td style="padding: 20px 30px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: linear-gradient(135deg, #1a1a1a 0%, #333333 100%); border-radius: 12px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 30px; text-align: center;">
                                        <p style="margin: 0 0 10px; color: #d4af37; font-size: 12px; text-transform: uppercase; letter-spacing: 2px;">
                                            <?php _e('Gift Card Value', 'massnahme-gift-cards'); ?>
                                        </p>
                                        <p style="margin: 0; color: #ffffff; font-size: 42px; font-weight: 300;">
                                            <?php echo wc_price($amount); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <?php if (!empty($store)) : ?>
                    <!-- Pickup Location -->
                    <tr>
                        <td style="padding: 20px 30px;">
                            <h3 style="margin: 0 0 15px; color: #1a1a1a; font-size: 16px; font-weight: 600;">
                                <?php _e('Pickup Location', 'massnahme-gift-cards'); ?>
                            </h3>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 8px; color: #1a1a1a; font-size: 16px; font-weight: 600;">
                                            <?php echo esc_html($store['name']); ?>
                                        </p>
                                        <?php if (!empty($store['address'])) : ?>
                                        <p style="margin: 0 0 8px; color: #666; font-size: 14px;">
                                            <?php echo nl2br(esc_html($store['address'])); ?>
                                        </p>
                                        <?php endif; ?>
                                        <?php if (!empty($store['hours'])) : ?>
                                        <p style="margin: 0 0 8px; color: #666; font-size: 14px;">
                                            <strong><?php _e('Hours:', 'massnahme-gift-cards'); ?></strong> <?php echo esc_html($store['hours']); ?>
                                        </p>
                                        <?php endif; ?>
                                        <?php if (!empty($store['phone'])) : ?>
                                        <p style="margin: 0; color: #666; font-size: 14px;">
                                            <strong><?php _e('Phone:', 'massnahme-gift-cards'); ?></strong> <?php echo esc_html($store['phone']); ?>
                                        </p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <!-- What to Expect -->
                    <tr>
                        <td style="padding: 20px 30px 40px;">
                            <h3 style="margin: 0 0 15px; color: #1a1a1a; font-size: 16px; font-weight: 600;">
                                <?php _e('What to Expect', 'massnahme-gift-cards'); ?>
                            </h3>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding: 15px 0; border-bottom: 1px solid #eee;">
                                        <table role="presentation" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="width: 40px; vertical-align: top;">
                                                    <span style="display: inline-block; width: 24px; height: 24px; background-color: #d4af37; color: #1a1a1a; border-radius: 50%; text-align: center; line-height: 24px; font-size: 12px; font-weight: 600;">1</span>
                                                </td>
                                                <td style="color: #666; font-size: 14px;">
                                                    <?php _e('Your gift card will be prepared with our signature premium packaging within 24 hours.', 'massnahme-gift-cards'); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px 0; border-bottom: 1px solid #eee;">
                                        <table role="presentation" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="width: 40px; vertical-align: top;">
                                                    <span style="display: inline-block; width: 24px; height: 24px; background-color: #d4af37; color: #1a1a1a; border-radius: 50%; text-align: center; line-height: 24px; font-size: 12px; font-weight: 600;">2</span>
                                                </td>
                                                <td style="color: #666; font-size: 14px;">
                                                    <?php _e('You will receive an email notification when your gift card is ready for pickup.', 'massnahme-gift-cards'); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px 0;">
                                        <table role="presentation" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="width: 40px; vertical-align: top;">
                                                    <span style="display: inline-block; width: 24px; height: 24px; background-color: #d4af37; color: #1a1a1a; border-radius: 50%; text-align: center; line-height: 24px; font-size: 12px; font-weight: 600;">3</span>
                                                </td>
                                                <td style="color: #666; font-size: 14px;">
                                                    <?php _e('Visit the store during opening hours to collect your beautifully packaged gift card.', 'massnahme-gift-cards'); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #eee;">
                            <p style="margin: 0 0 10px; color: #666; font-size: 12px;">
                                <?php _e('Questions? Contact us at', 'massnahme-gift-cards'); ?>
                                <a href="mailto:<?php echo esc_attr(get_option('woocommerce_email_from_address')); ?>" style="color: #d4af37;"><?php echo esc_html(get_option('woocommerce_email_from_address')); ?></a>
                            </p>
                            <p style="margin: 0; color: #999; font-size: 11px;">
                                <?php echo esc_html($store_name); ?> | <?php echo esc_html(home_url()); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
