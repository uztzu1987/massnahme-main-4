<?php
/**
 * Frontend Staff Redemption Template
 * Mobile-optimized POS interface for in-store gift card redemption
 */
defined('ABSPATH') || exit;

$currency_symbol = get_woocommerce_currency_symbol();
?>
<div class="mgc-pos-container">
    <!-- Header -->
    <div class="mgc-pos-header">
        <h2><?php _e('Gift Card Redemption', 'massnahme-gift-cards'); ?></h2>
        <span class="mgc-pos-user"><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
    </div>

    <!-- Code Entry -->
    <div class="mgc-pos-code-entry">
        <div class="mgc-pos-input-wrap">
            <input type="text"
                   id="mgc-pos-code"
                   class="mgc-pos-code-input"
                   placeholder="<?php esc_attr_e('Enter or scan code...', 'massnahme-gift-cards'); ?>"
                   autocomplete="off"
                   autofocus>
            <button type="button" id="mgc-pos-lookup" class="mgc-pos-btn mgc-pos-btn-primary">
                <span class="mgc-pos-btn-text"><?php _e('Look Up', 'massnahme-gift-cards'); ?></span>
                <span class="mgc-pos-btn-loading" style="display:none;"><?php _e('...', 'massnahme-gift-cards'); ?></span>
            </button>
        </div>
    </div>

    <!-- Card Display (hidden until lookup) -->
    <div id="mgc-pos-card-display" class="mgc-pos-card-display" style="display: none;">
        <!-- Status Banner -->
        <div id="mgc-pos-status-banner" class="mgc-pos-status-banner">
            <span class="mgc-pos-status-icon"></span>
            <span class="mgc-pos-status-text"></span>
        </div>

        <!-- Balance Display -->
        <div class="mgc-pos-balance-box">
            <span class="mgc-pos-balance-label"><?php _e('Available Balance', 'massnahme-gift-cards'); ?></span>
            <span id="mgc-pos-balance" class="mgc-pos-balance-value"></span>
        </div>

        <!-- Card Info -->
        <div class="mgc-pos-card-info">
            <div class="mgc-pos-info-row">
                <span class="mgc-pos-info-label"><?php _e('Code', 'massnahme-gift-cards'); ?></span>
                <span id="mgc-pos-card-code" class="mgc-pos-info-value"></span>
            </div>
            <div class="mgc-pos-info-row">
                <span class="mgc-pos-info-label"><?php _e('Original', 'massnahme-gift-cards'); ?></span>
                <span id="mgc-pos-original" class="mgc-pos-info-value"></span>
            </div>
            <div class="mgc-pos-info-row">
                <span class="mgc-pos-info-label"><?php _e('Recipient', 'massnahme-gift-cards'); ?></span>
                <span id="mgc-pos-recipient" class="mgc-pos-info-value"></span>
            </div>
            <div class="mgc-pos-info-row">
                <span class="mgc-pos-info-label"><?php _e('Expires', 'massnahme-gift-cards'); ?></span>
                <span id="mgc-pos-expires" class="mgc-pos-info-value"></span>
            </div>
            <div id="mgc-pos-pickup-info" class="mgc-pos-info-row" style="display: none;">
                <span class="mgc-pos-info-label"><?php _e('Pickup Status', 'massnahme-gift-cards'); ?></span>
                <span id="mgc-pos-pickup-status" class="mgc-pos-info-value"></span>
            </div>
        </div>

        <!-- Redemption Section -->
        <div id="mgc-pos-redemption" class="mgc-pos-redemption">
            <h3><?php _e('Redeem', 'massnahme-gift-cards'); ?></h3>

            <div class="mgc-pos-amount-input">
                <span class="mgc-pos-currency"><?php echo esc_html($currency_symbol); ?></span>
                <input type="number"
                       id="mgc-pos-amount"
                       class="mgc-pos-amount-field"
                       step="0.01"
                       min="0.01"
                       placeholder="0.00">
            </div>

            <div class="mgc-pos-quick-btns">
                <button type="button" class="mgc-pos-quick" data-amount="10"><?php echo esc_html($currency_symbol); ?>10</button>
                <button type="button" class="mgc-pos-quick" data-amount="25"><?php echo esc_html($currency_symbol); ?>25</button>
                <button type="button" class="mgc-pos-quick" data-amount="50"><?php echo esc_html($currency_symbol); ?>50</button>
                <button type="button" class="mgc-pos-quick" data-amount="100"><?php echo esc_html($currency_symbol); ?>100</button>
                <button type="button" class="mgc-pos-quick mgc-pos-quick-full" data-amount="full"><?php _e('FULL', 'massnahme-gift-cards'); ?></button>
            </div>

            <div id="mgc-pos-preview" class="mgc-pos-preview" style="display: none;">
                <div class="mgc-pos-preview-row">
                    <span><?php _e('Current:', 'massnahme-gift-cards'); ?></span>
                    <span id="mgc-pos-preview-current"></span>
                </div>
                <div class="mgc-pos-preview-row mgc-pos-preview-deduct">
                    <span><?php _e('Redeem:', 'massnahme-gift-cards'); ?></span>
                    <span id="mgc-pos-preview-deduct"></span>
                </div>
                <div class="mgc-pos-preview-row mgc-pos-preview-remaining">
                    <span><?php _e('Remaining:', 'massnahme-gift-cards'); ?></span>
                    <span id="mgc-pos-preview-remaining"></span>
                </div>
            </div>

            <button type="button" id="mgc-pos-confirm" class="mgc-pos-btn mgc-pos-btn-success mgc-pos-btn-large" disabled>
                <?php _e('Confirm Redemption', 'massnahme-gift-cards'); ?>
            </button>
        </div>

        <!-- Pickup Status Buttons (for pickup orders) -->
        <div id="mgc-pos-pickup-actions" class="mgc-pos-pickup-actions" style="display: none;">
            <h3><?php _e('Pickup Status', 'massnahme-gift-cards'); ?></h3>
            <div class="mgc-pos-pickup-btns">
                <button type="button" class="mgc-pos-pickup-btn" data-status="ordered"><?php _e('Ordered', 'massnahme-gift-cards'); ?></button>
                <button type="button" class="mgc-pos-pickup-btn" data-status="preparing"><?php _e('Preparing', 'massnahme-gift-cards'); ?></button>
                <button type="button" class="mgc-pos-pickup-btn" data-status="ready"><?php _e('Ready', 'massnahme-gift-cards'); ?></button>
                <button type="button" class="mgc-pos-pickup-btn" data-status="collected"><?php _e('Collected', 'massnahme-gift-cards'); ?></button>
            </div>
        </div>

        <!-- Clear Button -->
        <button type="button" id="mgc-pos-clear" class="mgc-pos-btn mgc-pos-btn-secondary">
            <?php _e('Clear / New Card', 'massnahme-gift-cards'); ?>
        </button>
    </div>

    <!-- Error Display -->
    <div id="mgc-pos-error" class="mgc-pos-error" style="display: none;">
        <div class="mgc-pos-error-icon">!</div>
        <div id="mgc-pos-error-message" class="mgc-pos-error-text"></div>
        <button type="button" id="mgc-pos-retry" class="mgc-pos-btn mgc-pos-btn-secondary">
            <?php _e('Try Again', 'massnahme-gift-cards'); ?>
        </button>
    </div>

    <!-- Success Overlay -->
    <div id="mgc-pos-success" class="mgc-pos-success-overlay" style="display: none;">
        <div class="mgc-pos-success-content">
            <div class="mgc-pos-success-icon">✓</div>
            <div class="mgc-pos-success-title"><?php _e('Redemption Complete!', 'massnahme-gift-cards'); ?></div>
            <div id="mgc-pos-success-amount" class="mgc-pos-success-amount"></div>
            <div id="mgc-pos-success-remaining" class="mgc-pos-success-remaining"></div>
            <button type="button" id="mgc-pos-success-close" class="mgc-pos-btn mgc-pos-btn-primary">
                <?php _e('Done', 'massnahme-gift-cards'); ?>
            </button>
        </div>
    </div>
</div>

<style>
/* POS Container */
.mgc-pos-container {
    max-width: 500px;
    margin: 0 auto;
    padding: 15px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    position: relative;
}

/* Header */
.mgc-pos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #eee;
}

.mgc-pos-header h2 {
    margin: 0;
    font-size: 22px;
    color: #1a1a1a;
}

.mgc-pos-user {
    font-size: 14px;
    color: #666;
    background: #f0f0f0;
    padding: 5px 12px;
    border-radius: 15px;
}

/* Code Entry */
.mgc-pos-code-entry {
    margin-bottom: 20px;
}

.mgc-pos-input-wrap {
    display: flex;
    gap: 10px;
}

.mgc-pos-code-input {
    flex: 1;
    padding: 18px 15px;
    font-size: 20px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 2px;
    border: 2px solid #ddd;
    border-radius: 10px;
    outline: none;
    transition: border-color 0.2s;
}

.mgc-pos-code-input:focus {
    border-color: #2271b1;
}

/* Buttons */
.mgc-pos-btn {
    padding: 15px 25px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.mgc-pos-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.mgc-pos-btn-primary {
    background: #2271b1;
    color: #fff;
}

.mgc-pos-btn-primary:hover:not(:disabled) {
    background: #135e96;
}

.mgc-pos-btn-success {
    background: #28a745;
    color: #fff;
}

.mgc-pos-btn-success:hover:not(:disabled) {
    background: #218838;
}

.mgc-pos-btn-secondary {
    background: #6c757d;
    color: #fff;
}

.mgc-pos-btn-large {
    width: 100%;
    padding: 18px;
    font-size: 18px;
}

/* Status Banner */
.mgc-pos-status-banner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 18px;
    font-weight: 700;
}

.mgc-pos-status-banner.status-active {
    background: #d4edda;
    color: #155724;
}

.mgc-pos-status-banner.status-used {
    background: #f8d7da;
    color: #721c24;
}

.mgc-pos-status-banner.status-expired {
    background: #fff3cd;
    color: #856404;
}

/* Balance Box */
.mgc-pos-balance-box {
    text-align: center;
    padding: 30px 20px;
    background: linear-gradient(135deg, #1e3a5f, #2271b1);
    border-radius: 15px;
    margin-bottom: 20px;
}

.mgc-pos-balance-label {
    display: block;
    color: rgba(255,255,255,0.8);
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}

.mgc-pos-balance-value {
    display: block;
    color: #fff;
    font-size: 48px;
    font-weight: 700;
}

/* Card Info */
.mgc-pos-card-info {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
}

.mgc-pos-info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.mgc-pos-info-row:last-child {
    border-bottom: none;
}

.mgc-pos-info-label {
    color: #666;
    font-size: 14px;
}

.mgc-pos-info-value {
    font-weight: 600;
    color: #1a1a1a;
}

/* Redemption Section */
.mgc-pos-redemption {
    background: #fff;
    border: 2px solid #eee;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
}

.mgc-pos-redemption h3 {
    margin: 0 0 20px 0;
    text-align: center;
    color: #1a1a1a;
}

.mgc-pos-amount-input {
    display: flex;
    align-items: center;
    border: 2px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 15px;
}

.mgc-pos-currency {
    padding: 15px;
    background: #f8f9fa;
    font-size: 24px;
    font-weight: 600;
    color: #666;
}

.mgc-pos-amount-field {
    flex: 1;
    border: none;
    padding: 18px 15px;
    font-size: 28px;
    text-align: center;
    outline: none;
}

/* Quick Amount Buttons */
.mgc-pos-quick-btns {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
    margin-bottom: 15px;
}

.mgc-pos-quick {
    padding: 12px 8px;
    border: 2px solid #ddd;
    background: #fff;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.mgc-pos-quick:hover {
    border-color: #2271b1;
    color: #2271b1;
}

.mgc-pos-quick.selected {
    background: #2271b1;
    border-color: #2271b1;
    color: #fff;
}

.mgc-pos-quick-full {
    background: #e8f4fc;
}

/* Preview */
.mgc-pos-preview {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
}

.mgc-pos-preview-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.mgc-pos-preview-deduct {
    color: #dc3545;
}

.mgc-pos-preview-remaining {
    font-weight: 700;
    font-size: 18px;
    color: #28a745;
    border-top: 2px solid #ddd;
    padding-top: 10px;
    margin-top: 5px;
}

/* Pickup Actions */
.mgc-pos-pickup-actions {
    margin-bottom: 20px;
}

.mgc-pos-pickup-actions h3 {
    margin: 0 0 15px 0;
    text-align: center;
}

.mgc-pos-pickup-btns {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.mgc-pos-pickup-btn {
    padding: 15px;
    border: 2px solid #ddd;
    background: #fff;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.mgc-pos-pickup-btn:hover {
    border-color: #2271b1;
}

.mgc-pos-pickup-btn.active {
    background: #2271b1;
    border-color: #2271b1;
    color: #fff;
}

/* Error Display */
.mgc-pos-error {
    text-align: center;
    padding: 40px 20px;
    background: #fff;
    border-radius: 15px;
    border: 2px solid #f8d7da;
}

.mgc-pos-error-icon {
    width: 60px;
    height: 60px;
    background: #dc3545;
    color: #fff;
    font-size: 36px;
    font-weight: 700;
    line-height: 60px;
    border-radius: 50%;
    margin: 0 auto 20px;
}

.mgc-pos-error-text {
    font-size: 18px;
    color: #721c24;
    margin-bottom: 20px;
}

/* Success Overlay */
.mgc-pos-success-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.mgc-pos-success-content {
    background: #fff;
    border-radius: 20px;
    padding: 40px;
    text-align: center;
    max-width: 90%;
    width: 400px;
}

.mgc-pos-success-icon {
    width: 80px;
    height: 80px;
    background: #28a745;
    color: #fff;
    font-size: 48px;
    line-height: 80px;
    border-radius: 50%;
    margin: 0 auto 20px;
}

.mgc-pos-success-title {
    font-size: 24px;
    font-weight: 700;
    color: #28a745;
    margin-bottom: 15px;
}

.mgc-pos-success-amount {
    font-size: 36px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 10px;
}

.mgc-pos-success-remaining {
    font-size: 16px;
    color: #666;
    margin-bottom: 25px;
}

/* Responsive */
@media (max-width: 480px) {
    .mgc-pos-container {
        padding: 10px;
    }

    .mgc-pos-header h2 {
        font-size: 18px;
    }

    .mgc-pos-code-input {
        font-size: 16px;
        padding: 15px 10px;
    }

    .mgc-pos-balance-value {
        font-size: 36px;
    }

    .mgc-pos-quick-btns {
        grid-template-columns: repeat(3, 1fr);
    }

    .mgc-pos-pickup-btns {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
(function($) {
    'use strict';

    var currentCard = null;
    var currentBalance = 0;
    var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
    var nonce = '<?php echo wp_create_nonce('mgc_frontend_nonce'); ?>';
    var currencySymbol = '<?php echo esc_js($currency_symbol); ?>';

    function formatCurrency(amount) {
        return currencySymbol + parseFloat(amount).toFixed(2).replace('.', ',');
    }

    function lookupCard() {
        var code = $('#mgc-pos-code').val().trim().toUpperCase();
        if (!code) {
            showError('<?php _e('Please enter a gift card code', 'massnahme-gift-cards'); ?>');
            return;
        }

        $('#mgc-pos-lookup .mgc-pos-btn-text').hide();
        $('#mgc-pos-lookup .mgc-pos-btn-loading').show();
        $('#mgc-pos-lookup').prop('disabled', true);

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'mgc_frontend_staff_lookup',
                nonce: nonce,
                code: code
            },
            success: function(response) {
                if (response.success) {
                    displayCard(response.data);
                } else {
                    showError(response.data || '<?php _e('Gift card not found', 'massnahme-gift-cards'); ?>');
                }
            },
            error: function() {
                showError('<?php _e('Connection error', 'massnahme-gift-cards'); ?>');
            },
            complete: function() {
                $('#mgc-pos-lookup .mgc-pos-btn-text').show();
                $('#mgc-pos-lookup .mgc-pos-btn-loading').hide();
                $('#mgc-pos-lookup').prop('disabled', false);
            }
        });
    }

    function displayCard(card) {
        currentCard = card;
        currentBalance = parseFloat(card.balance);

        $('#mgc-pos-error').hide();
        $('#mgc-pos-card-display').show();

        // Status banner
        var banner = $('#mgc-pos-status-banner');
        banner.removeClass('status-active status-used status-expired').addClass('status-' + card.status);

        var statusText = {
            'active': '<?php _e('VALID', 'massnahme-gift-cards'); ?>',
            'used': '<?php _e('FULLY USED', 'massnahme-gift-cards'); ?>',
            'expired': '<?php _e('EXPIRED', 'massnahme-gift-cards'); ?>'
        };
        var statusIcon = { 'active': '✓', 'used': '✗', 'expired': '!' };

        banner.find('.mgc-pos-status-icon').text(statusIcon[card.status] || '?');
        banner.find('.mgc-pos-status-text').text(statusText[card.status] || card.status);

        // Card details
        $('#mgc-pos-balance').text(formatCurrency(card.balance));
        $('#mgc-pos-card-code').text(card.code);
        $('#mgc-pos-original').text(formatCurrency(card.amount));
        $('#mgc-pos-recipient').text(card.recipient_name || card.recipient_email);
        $('#mgc-pos-expires').text(card.expires_at);

        // Show/hide redemption
        if (card.status === 'active' && currentBalance > 0) {
            $('#mgc-pos-redemption').show();
            $('#mgc-pos-amount').attr('max', currentBalance);
        } else {
            $('#mgc-pos-redemption').hide();
        }

        // Pickup status
        if (card.delivery_method === 'pickup') {
            $('#mgc-pos-pickup-info').show();
            var statusLabels = {
                'ordered': '<?php _e('Ordered', 'massnahme-gift-cards'); ?>',
                'preparing': '<?php _e('Preparing', 'massnahme-gift-cards'); ?>',
                'ready': '<?php _e('Ready', 'massnahme-gift-cards'); ?>',
                'collected': '<?php _e('Collected', 'massnahme-gift-cards'); ?>'
            };
            $('#mgc-pos-pickup-status').text(statusLabels[card.pickup_status] || card.pickup_status);
            $('#mgc-pos-pickup-actions').show();
            $('.mgc-pos-pickup-btn').removeClass('active');
            $('.mgc-pos-pickup-btn[data-status="' + (card.pickup_status || 'ordered') + '"]').addClass('active');
        } else {
            $('#mgc-pos-pickup-info').hide();
            $('#mgc-pos-pickup-actions').hide();
        }

        // Reset redemption
        $('#mgc-pos-amount').val('');
        $('#mgc-pos-preview').hide();
        $('#mgc-pos-confirm').prop('disabled', true);
        $('.mgc-pos-quick').removeClass('selected');
    }

    function showError(message) {
        $('#mgc-pos-card-display').hide();
        $('#mgc-pos-error').show();
        $('#mgc-pos-error-message').text(message);
    }

    function clearCard() {
        currentCard = null;
        currentBalance = 0;
        $('#mgc-pos-code').val('').focus();
        $('#mgc-pos-card-display').hide();
        $('#mgc-pos-error').hide();
    }

    function updatePreview() {
        var amount = parseFloat($('#mgc-pos-amount').val()) || 0;

        if (amount > 0 && amount <= currentBalance) {
            var remaining = currentBalance - amount;
            $('#mgc-pos-preview-current').text(formatCurrency(currentBalance));
            $('#mgc-pos-preview-deduct').text('-' + formatCurrency(amount));
            $('#mgc-pos-preview-remaining').text(formatCurrency(remaining));
            $('#mgc-pos-preview').show();
            $('#mgc-pos-confirm').prop('disabled', false);
        } else {
            $('#mgc-pos-preview').hide();
            $('#mgc-pos-confirm').prop('disabled', true);
        }
    }

    function confirmRedemption() {
        var amount = parseFloat($('#mgc-pos-amount').val());

        if (!amount || amount <= 0 || amount > currentBalance) {
            alert('<?php _e('Invalid amount', 'massnahme-gift-cards'); ?>');
            return;
        }

        $('#mgc-pos-confirm').prop('disabled', true).text('<?php _e('Processing...', 'massnahme-gift-cards'); ?>');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'mgc_frontend_redeem',
                nonce: nonce,
                code: currentCard.code,
                amount: amount
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(amount, response.data.new_balance);
                } else {
                    alert('<?php _e('Error:', 'massnahme-gift-cards'); ?> ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('Connection error', 'massnahme-gift-cards'); ?>');
            },
            complete: function() {
                $('#mgc-pos-confirm').prop('disabled', false).text('<?php _e('Confirm Redemption', 'massnahme-gift-cards'); ?>');
            }
        });
    }

    function showSuccess(amount, remaining) {
        $('#mgc-pos-success-amount').text('-' + formatCurrency(amount));
        $('#mgc-pos-success-remaining').text('<?php _e('Remaining balance:', 'massnahme-gift-cards'); ?> ' + formatCurrency(remaining));
        $('#mgc-pos-success').show();
    }

    function updatePickupStatus(status) {
        if (!currentCard) return;

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'mgc_frontend_update_pickup_status',
                nonce: nonce,
                code: currentCard.code,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    $('.mgc-pos-pickup-btn').removeClass('active');
                    $('.mgc-pos-pickup-btn[data-status="' + status + '"]').addClass('active');
                    $('#mgc-pos-pickup-status').text(response.data.status_label);
                    currentCard.pickup_status = status;
                }
            }
        });
    }

    // Event handlers
    $('#mgc-pos-lookup').on('click', lookupCard);
    $('#mgc-pos-code').on('keypress', function(e) {
        if (e.which === 13) lookupCard();
    });

    $('#mgc-pos-clear, #mgc-pos-retry').on('click', clearCard);

    $('#mgc-pos-amount').on('input', function() {
        $('.mgc-pos-quick').removeClass('selected');
        updatePreview();
    });

    $('.mgc-pos-quick').on('click', function() {
        var amount = $(this).data('amount');
        $('.mgc-pos-quick').removeClass('selected');
        $(this).addClass('selected');
        $('#mgc-pos-amount').val(amount === 'full' ? currentBalance : amount);
        updatePreview();
    });

    $('#mgc-pos-confirm').on('click', confirmRedemption);

    $('.mgc-pos-pickup-btn').on('click', function() {
        updatePickupStatus($(this).data('status'));
    });

    $('#mgc-pos-success-close').on('click', function() {
        $('#mgc-pos-success').hide();
        clearCard();
    });

})(jQuery);
</script>
