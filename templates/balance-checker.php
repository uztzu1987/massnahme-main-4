<?php
/**
 * Balance Checker Template
 * Shortcode: [massnahme_gift_balance]
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="mgc-balance-checker">
    <h3><?php _e('Check Your Gift Card Balance', 'massnahme-gift-cards'); ?></h3>

    <form id="mgc-balance-form" class="mgc-form">
        <div class="mgc-form-group">
            <label for="mgc-code-input">
                <?php _e('Enter your gift card code:', 'massnahme-gift-cards'); ?>
            </label>
            <input
                type="text"
                id="mgc-code-input"
                name="gift_card_code"
                placeholder="<?php esc_attr_e('e.g., MASS-2025-ABC123', 'massnahme-gift-cards'); ?>"
                required
            />
        </div>

        <button type="submit" class="button">
            <?php _e('Check Balance', 'massnahme-gift-cards'); ?>
        </button>
    </form>

    <div id="mgc-balance-result" class="mgc-result" style="display: none;">
        <div class="mgc-result-content"></div>
    </div>
</div>

<style>
.mgc-balance-checker {
    max-width: 500px;
    margin: 20px auto;
    padding: 30px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.mgc-balance-checker h3 {
    margin-top: 0;
    text-align: center;
}

.mgc-form-group {
    margin-bottom: 20px;
}

.mgc-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

.mgc-form-group input[type="text"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
    box-sizing: border-box;
}

.mgc-balance-checker .button {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.mgc-result {
    margin-top: 20px;
    padding: 20px;
    border-radius: 4px;
}

.mgc-result.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.mgc-result.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.mgc-result-content {
    font-size: 16px;
}

.mgc-result-content strong {
    display: block;
    font-size: 24px;
    margin-top: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#mgc-balance-form').on('submit', function(e) {
        e.preventDefault();

        var code = $('#mgc-code-input').val().trim();
        var $result = $('#mgc-balance-result');

        if (!code) {
            return;
        }

        // Show loading
        $result.removeClass('success error').show();
        $result.find('.mgc-result-content').html('<?php _e('Checking...', 'massnahme-gift-cards'); ?>');

        $.ajax({
            url: mgc_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mgc_validate_code',
                code: code,
                nonce: mgc_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.addClass('success');
                    var html = '<div>';
                    html += '<p><?php _e('Valid gift card!', 'massnahme-gift-cards'); ?></p>';
                    html += '<strong>' + response.data.message + '</strong>';
                    html += '<p><?php _e('Expires:', 'massnahme-gift-cards'); ?> ' + response.data.expires + '</p>';
                    html += '</div>';
                    $result.find('.mgc-result-content').html(html);
                } else {
                    $result.addClass('error');
                    $result.find('.mgc-result-content').html(response.data);
                }
            },
            error: function() {
                $result.addClass('error');
                $result.find('.mgc-result-content').html('<?php _e('An error occurred. Please try again.', 'massnahme-gift-cards'); ?>');
            }
        });
    });
});
</script>
