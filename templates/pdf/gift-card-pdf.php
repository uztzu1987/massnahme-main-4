<?php
/**
 * PDF Template for Gift Card
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('mgc_settings');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 40px;
            color: #333;
        }
        .gift-card-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px;
            border: 3px solid #2271b1;
            border-radius: 10px;
            text-align: center;
            background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #2271b1;
            margin-bottom: 20px;
        }
        h1 {
            color: #2271b1;
            margin: 20px 0;
            font-size: 36px;
        }
        .amount {
            font-size: 48px;
            font-weight: bold;
            color: #155724;
            margin: 30px 0;
        }
        .code-container {
            background: #fff;
            padding: 20px;
            border: 2px dashed #ccc;
            border-radius: 8px;
            margin: 30px 0;
        }
        .code-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .code {
            font-size: 28px;
            font-weight: bold;
            color: #2271b1;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }
        .details {
            margin: 30px 0;
            text-align: left;
            font-size: 14px;
            line-height: 1.8;
        }
        .details-row {
            margin: 10px 0;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .details-label {
            font-weight: bold;
            color: #555;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .message {
            background: #fff;
            padding: 20px;
            border-left: 4px solid #2271b1;
            margin: 20px 0;
            text-align: left;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="gift-card-container">
        <div class="logo">
            <?php echo esc_html(get_bloginfo('name')); ?>
        </div>

        <h1><?php _e('Gift Card', 'massnahme-gift-cards'); ?></h1>

        <div class="amount">
            <?php echo wc_price($gift_card->amount); ?>
        </div>

        <?php if (!empty($gift_card->message)): ?>
            <div class="message">
                <?php echo esc_html($gift_card->message); ?>
            </div>
        <?php endif; ?>

        <div class="code-container">
            <div class="code-label">
                <?php _e('Gift Card Code:', 'massnahme-gift-cards'); ?>
            </div>
            <div class="code">
                <?php echo esc_html($gift_card->code); ?>
            </div>
        </div>

        <div class="details">
            <div class="details-row">
                <span class="details-label"><?php _e('Recipient:', 'massnahme-gift-cards'); ?></span>
                <?php echo esc_html($gift_card->recipient_email); ?>
            </div>

            <div class="details-row">
                <span class="details-label"><?php _e('Issued:', 'massnahme-gift-cards'); ?></span>
                <?php echo date_i18n(get_option('date_format'), strtotime($gift_card->created_at)); ?>
            </div>

            <div class="details-row">
                <span class="details-label"><?php _e('Expires:', 'massnahme-gift-cards'); ?></span>
                <?php echo date_i18n(get_option('date_format'), strtotime($gift_card->expires_at)); ?>
            </div>

            <div class="details-row">
                <span class="details-label"><?php _e('Balance:', 'massnahme-gift-cards'); ?></span>
                <?php echo wc_price($gift_card->balance); ?>
            </div>
        </div>

        <?php if (!empty($settings['enable_qr'])): ?>
            <div class="qr-code">
                <!-- QR Code can be added here if needed -->
            </div>
        <?php endif; ?>

        <div class="footer">
            <p><?php _e('To redeem this gift card, use the code above at checkout.', 'massnahme-gift-cards'); ?></p>
            <p><?php echo esc_html(get_bloginfo('name')); ?> - <?php echo esc_url(home_url()); ?></p>
        </div>
    </div>
</body>
</html>
