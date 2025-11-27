<?php
/**
 * Shipping Confirmation Email Template
 *
 * Sent to purchaser when gift card shipping is ordered
 */

defined('ABSPATH') || exit;

$store_name = get_bloginfo('name');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Gift Card Shipping Confirmation', 'massnahme-gift-cards'); ?></title>
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
                                <?php _e('LUXURY GIFT CARD', 'massnahme-gift-cards'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Icon & Message -->
                    <tr>
                        <td style="padding: 40px 30px 20px; text-align: center;">
                            <!-- Shipping Icon -->
                            <div style="margin-bottom: 20px;">
                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="1.5" style="margin: 0 auto;">
                                    <rect x="1" y="3" width="15" height="13"/>
                                    <polygon points="16,8 20,8 23,11 23,16 16,16 16,8"/>
                                    <circle cx="5.5" cy="18.5" r="2.5"/>
                                    <circle cx="18.5" cy="18.5" r="2.5"/>
                                </svg>
                            </div>
                            <h2 style="margin: 0 0 15px; color: #1a1a1a; font-size: 24px; font-weight: 400;">
                                <?php _e('Your Gift Card is On Its Way', 'massnahme-gift-cards'); ?>
                            </h2>
                            <p style="margin: 0; color: #666; font-size: 16px; line-height: 1.6;">
                                <?php _e('Thank you for choosing our luxury shipping option. Your gift card is being prepared with our signature premium presentation and will be shipped shortly.', 'massnahme-gift-cards'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Gift Card Preview -->
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
                                        <?php if (!empty($recipient_name)) : ?>
                                        <p style="margin: 15px 0 0; color: #999; font-size: 14px;">
                                            <?php _e('For:', 'massnahme-gift-cards'); ?> <?php echo esc_html($recipient_name); ?>
                                        </p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Shipping Details -->
                    <tr>
                        <td style="padding: 20px 30px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td width="50%" style="padding-right: 15px; vertical-align: top;">
                                        <h3 style="margin: 0 0 10px; color: #1a1a1a; font-size: 14px; font-weight: 600;">
                                            <?php _e('Shipping To', 'massnahme-gift-cards'); ?>
                                        </h3>
                                        <div style="background-color: #f8f9fa; border-radius: 8px; padding: 15px;">
                                            <p style="margin: 0; color: #666; font-size: 14px; line-height: 1.6;">
                                                <?php echo wp_kses_post($shipping_address); ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td width="50%" style="padding-left: 15px; vertical-align: top;">
                                        <h3 style="margin: 0 0 10px; color: #1a1a1a; font-size: 14px; font-weight: 600;">
                                            <?php _e('Estimated Delivery', 'massnahme-gift-cards'); ?>
                                        </h3>
                                        <div style="background-color: #f8f9fa; border-radius: 8px; padding: 15px;">
                                            <p style="margin: 0; color: #d4af37; font-size: 18px; font-weight: 600;">
                                                <?php echo esc_html($shipping_time); ?>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Premium Packaging Info -->
                    <tr>
                        <td style="padding: 20px 30px 40px;">
                            <div style="background: linear-gradient(135deg, #f8f4e8 0%, #fff8e1 100%); border-radius: 12px; padding: 25px; border: 1px solid #d4af37;">
                                <h3 style="margin: 0 0 15px; color: #1a1a1a; font-size: 16px; font-weight: 600; text-align: center;">
                                    <?php _e('Luxury Presentation Includes', 'massnahme-gift-cards'); ?>
                                </h3>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding: 10px 0; text-align: center;">
                                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                                                <tr>
                                                    <td style="padding: 0 20px;">
                                                        <p style="margin: 0 0 5px; color: #d4af37; font-size: 24px;">&#10022;</p>
                                                        <p style="margin: 0; color: #666; font-size: 12px;"><?php _e('Premium Gift Box', 'massnahme-gift-cards'); ?></p>
                                                    </td>
                                                    <td style="padding: 0 20px;">
                                                        <p style="margin: 0 0 5px; color: #d4af37; font-size: 24px;">&#10022;</p>
                                                        <p style="margin: 0; color: #666; font-size: 12px;"><?php _e('Elegant Envelope', 'massnahme-gift-cards'); ?></p>
                                                    </td>
                                                    <td style="padding: 0 20px;">
                                                        <p style="margin: 0 0 5px; color: #d4af37; font-size: 24px;">&#10022;</p>
                                                        <p style="margin: 0; color: #666; font-size: 12px;"><?php _e('Personal Message Card', 'massnahme-gift-cards'); ?></p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #eee;">
                            <p style="margin: 0 0 10px; color: #666; font-size: 14px;">
                                <?php _e('You will receive a shipping notification with tracking information once your gift card has been dispatched.', 'massnahme-gift-cards'); ?>
                            </p>
                            <p style="margin: 15px 0 0; color: #666; font-size: 12px;">
                                <?php _e('Questions? Contact us at', 'massnahme-gift-cards'); ?>
                                <a href="mailto:<?php echo esc_attr(get_option('woocommerce_email_from_address')); ?>" style="color: #d4af37;"><?php echo esc_html(get_option('woocommerce_email_from_address')); ?></a>
                            </p>
                            <p style="margin: 10px 0 0; color: #999; font-size: 11px;">
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
