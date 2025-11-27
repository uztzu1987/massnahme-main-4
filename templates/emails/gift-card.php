<?php
/**
 * Gift Card Email Template
 * 
 * Variables available:
 * $code, $amount, $balance, $message, $expires_at, $purchaser_name, $order
 */

defined('ABSPATH') || exit;
// Ensure settings are available in this template
$settings = get_option('mgc_settings', []);
?>
<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sprintf(__('Gift Card - %s', 'massnahme-gift-cards'), esc_html($code)); ?></title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;600&display=swap');
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: 'Inter', Arial, sans-serif;">
    
    <!-- Email Container -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                
                <!-- Main Card -->
                <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); padding: 50px; text-align: center;">
                            <h1 style="color: #ffffff; font-family: 'Playfair Display', serif; font-size: 42px; margin: 0; letter-spacing: 4px; font-weight: 400;">
                                MASSNAHME
                            </h1>
                            <p style="color: #c9a961; font-size: 14px; letter-spacing: 3px; margin: 15px 0 0 0; text-transform: uppercase;">
                                Premium Tailoring Since 1985
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Gift Message -->
                    <?php if (!empty($purchaser_name)) : ?>
                    <tr>
                        <td style="padding: 40px 50px 20px; text-align: center;">
                            <p style="color: #666; font-size: 16px; margin: 0; line-height: 1.6;">
                                <?php echo sprintf(__('A gift from %s', 'massnahme-gift-cards'), esc_html($purchaser_name)); ?>
                            </p>
                        </td>
                    </tr>
                    <?php endif; ?>
                    
                    <!-- Title -->
                    <tr>
                        <td style="padding: 20px 50px; text-align: center;">
                            <h2 style="font-family: 'Playfair Display', serif; color: #1a1a1a; font-size: 36px; margin: 0; font-weight: 400;">
                                <?php _e('Gift Card', 'massnahme-gift-cards'); ?>
                            </h2>
                        </td>
                    </tr>
                    
                    <!-- Amount and Code -->
                    <tr>
                        <td style="padding: 30px 50px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background: linear-gradient(135deg, #fafafa 0%, #ffffff 100%); border: 2px solid #c9a961; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 40px; text-align: center;">
                                        <div style="font-size: 48px; color: #1a1a1a; font-family: 'Playfair Display', serif; margin-bottom: 20px;">
                                            <?php echo wc_price($amount); ?>
                                        </div>
                                        <div style="background: #1a1a1a; color: #ffffff; padding: 15px 30px; display: inline-block; letter-spacing: 3px; font-size: 18px; font-weight: 600; border-radius: 4px;">
                                            <?php echo esc_html($code); ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Personal Message -->
                    <?php if (!empty($message)) : ?>
                    <tr>
                        <td style="padding: 0 50px 30px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color: #fafafa; border-left: 4px solid #c9a961; border-radius: 4px;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <p style="color: #666; font-style: italic; margin: 0; line-height: 1.8; font-size: 15px;">
                                            "<?php echo nl2br(esc_html($message)); ?>"
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <?php endif; ?>
                    
                    <!-- Instructions -->
                    <tr>
                        <td style="padding: 30px 50px;">
                            <h3 style="color: #1a1a1a; font-size: 20px; margin: 0 0 20px 0;">
                                <?php _e('How to Redeem', 'massnahme-gift-cards'); ?>
                            </h3>
                            <ol style="color: #666; line-height: 1.8; padding-left: 20px;">
                                <li style="margin-bottom: 10px;">
                                    <?php _e('Visit our online store or boutique', 'massnahme-gift-cards'); ?>
                                </li>
                                <li style="margin-bottom: 10px;">
                                    <?php _e('Select your desired items', 'massnahme-gift-cards'); ?>
                                </li>
                                <li style="margin-bottom: 10px;">
                                    <?php _e('Enter your gift card code at checkout', 'massnahme-gift-cards'); ?>
                                </li>
                                <li>
                                    <?php _e('Enjoy your premium tailoring experience', 'massnahme-gift-cards'); ?>
                                </li>
                            </ol>
                        </td>
                    </tr>
                    
                    <!-- Details -->
                    <tr>
                        <td style="padding: 20px 50px 30px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="color: #999; font-size: 14px; padding: 5px 0;">
                                        <strong><?php _e('Valid Until:', 'massnahme-gift-cards'); ?></strong>
                                        <?php echo esc_html( date_i18n(get_option('date_format'), strtotime($expires_at)) ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: #999; font-size: 14px; padding: 5px 0;">
                                        <strong><?php _e('Balance:', 'massnahme-gift-cards'); ?></strong>
                                        <?php echo wc_price($balance); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- CTA Button -->
                    <tr>
                        <td style="padding: 30px 50px 40px; text-align: center;">
                            <a href="<?php echo esc_url( home_url('/shop') ); ?>" style="background-color: #1a1a1a; color: #ffffff; padding: 18px 50px; text-decoration: none; display: inline-block; letter-spacing: 2px; font-weight: 600; font-size: 14px; text-transform: uppercase; border-radius: 4px;">
                                <?php _e('Shop Now', 'massnahme-gift-cards'); ?>
                            </a>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #1a1a1a; padding: 40px; text-align: center;">
                            <p style="color: #c9a961; margin: 0; font-size: 14px; letter-spacing: 1px;">
                                MASSNAHME
                            </p>
                            <p style="color: #888; margin: 10px 0 0 0; font-size: 13px; line-height: 1.6;">
                                Königsallee 27 | 40212 Düsseldorf<br>
                                Tel: +49 211 12345678 | info@massnahme.de
                            </p>
                            
                            <?php if (!empty($settings['enable_balance_check'])) : ?>
                            <p style="margin-top: 20px;">
                                <a href="<?php echo home_url('/gift-card-balance'); ?>" style="color: #c9a961; text-decoration: none; font-size: 13px;">
                                    <?php _e('Check Gift Card Balance', 'massnahme-gift-cards'); ?>
                                </a>
                            </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>
    
</body>
</html>