<?php
/**
 * Physical Card Created Email Template
 *
 * Sent to customer and staff when a physical gift card is created
 */

defined('ABSPATH') || exit;

$store_name = get_bloginfo('name');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Gift Card Created', 'massnahme-gift-cards'); ?></title>
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
                                <?php _e('PHYSICAL GIFT CARD', 'massnahme-gift-cards'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Icon & Message -->
                    <tr>
                        <td style="padding: 40px 30px 20px; text-align: center;">
                            <!-- Gift Card Icon -->
                            <div style="margin-bottom: 20px;">
                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="1.5" style="margin: 0 auto;">
                                    <rect x="3" y="8" width="18" height="13" rx="2" ry="2"/>
                                    <path d="M12 8V21"/>
                                    <path d="M19 12H5"/>
                                    <path d="M12 8c-2-3-6-3-6 0s4 5 6 5 6-2 6-5-4-3-6 0"/>
                                </svg>
                            </div>
                            <h2 style="margin: 0 0 15px; color: #1a1a1a; font-size: 24px; font-weight: 400;">
                                <?php _e('Physical Gift Card Created', 'massnahme-gift-cards'); ?>
                            </h2>
                            <p style="margin: 0; color: #666; font-size: 16px; line-height: 1.6;">
                                <?php _e('A new physical gift card has been created. Please find the details below.', 'massnahme-gift-cards'); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- Gift Card Details -->
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

                    <!-- Card Code Section -->
                    <tr>
                        <td style="padding: 20px 30px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 12px; border: 2px dashed #d4af37;">
                                <tr>
                                    <td style="padding: 25px; text-align: center;">
                                        <p style="margin: 0 0 10px; color: #666; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                                            <?php _e('Card Code', 'massnahme-gift-cards'); ?>
                                        </p>
                                        <p style="margin: 0; color: #1a1a1a; font-size: 28px; font-weight: 600; font-family: 'Courier New', monospace; letter-spacing: 2px;">
                                            <?php echo esc_html($code); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Details Grid -->
                    <tr>
                        <td style="padding: 20px 30px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td width="50%" style="padding-right: 10px; vertical-align: top;">
                                        <div style="background-color: #f8f9fa; border-radius: 8px; padding: 20px; height: 100%;">
                                            <p style="margin: 0 0 5px; color: #999; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                                                <?php _e('Created On', 'massnahme-gift-cards'); ?>
                                            </p>
                                            <p style="margin: 0; color: #1a1a1a; font-size: 16px; font-weight: 500;">
                                                <?php echo esc_html($created_at); ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td width="50%" style="padding-left: 10px; vertical-align: top;">
                                        <div style="background-color: #f8f9fa; border-radius: 8px; padding: 20px; height: 100%;">
                                            <p style="margin: 0 0 5px; color: #999; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                                                <?php _e('Expires On', 'massnahme-gift-cards'); ?>
                                            </p>
                                            <p style="margin: 0; color: #1a1a1a; font-size: 16px; font-weight: 500;">
                                                <?php echo esc_html($expires_at); ?>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <?php if (!empty($recipient_name) || !empty($recipient_email)) : ?>
                    <!-- Recipient Info -->
                    <tr>
                        <td style="padding: 10px 30px 20px;">
                            <div style="background-color: #f8f9fa; border-radius: 8px; padding: 20px;">
                                <p style="margin: 0 0 5px; color: #999; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                                    <?php _e('Recipient', 'massnahme-gift-cards'); ?>
                                </p>
                                <?php if (!empty($recipient_name)) : ?>
                                <p style="margin: 0; color: #1a1a1a; font-size: 16px; font-weight: 500;">
                                    <?php echo esc_html($recipient_name); ?>
                                </p>
                                <?php endif; ?>
                                <?php if (!empty($recipient_email)) : ?>
                                <p style="margin: 5px 0 0; color: #666; font-size: 14px;">
                                    <?php echo esc_html($recipient_email); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <?php if (!empty($created_by_name)) : ?>
                    <!-- Created By Info -->
                    <tr>
                        <td style="padding: 0 30px 20px;">
                            <div style="background-color: #fff8e1; border-radius: 8px; padding: 20px; border: 1px solid #d4af37;">
                                <p style="margin: 0 0 5px; color: #999; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                                    <?php _e('Created By', 'massnahme-gift-cards'); ?>
                                </p>
                                <p style="margin: 0; color: #1a1a1a; font-size: 16px; font-weight: 500;">
                                    <?php echo esc_html($created_by_name); ?>
                                </p>
                                <?php if (!empty($created_by_email)) : ?>
                                <p style="margin: 5px 0 0; color: #666; font-size: 14px;">
                                    <?php echo esc_html($created_by_email); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #eee;">
                            <p style="margin: 0 0 10px; color: #666; font-size: 14px;">
                                <?php _e('This gift card can be redeemed at checkout by entering the code above.', 'massnahme-gift-cards'); ?>
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
