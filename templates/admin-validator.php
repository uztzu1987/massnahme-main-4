<?php
/**
 * Staff Validation & Redemption Page
 * Mobile-friendly interface for in-store use
 */
defined('ABSPATH') || exit;

if (!current_user_can('manage_woocommerce')) {
    return;
}
?>
<div class="wrap mgc-staff-validator">
    <h1><?php _e('Gift Card Validator', 'massnahme-gift-cards'); ?></h1>

    <!-- Quick Code Entry -->
    <div class="mgc-validator-container">
        <div class="mgc-code-entry">
            <label for="mgc_code" class="screen-reader-text"><?php _e('Gift Card Code', 'massnahme-gift-cards'); ?></label>
            <input type="text"
                   id="mgc_code"
                   class="mgc-code-input"
                   placeholder="<?php esc_attr_e('Enter or scan gift card code...', 'massnahme-gift-cards'); ?>"
                   autocomplete="off"
                   autofocus>
            <button type="button" id="mgc-lookup-btn" class="button button-primary button-hero">
                <?php _e('Look Up', 'massnahme-gift-cards'); ?>
            </button>
        </div>

        <!-- Result Display -->
        <div id="mgc-card-result" class="mgc-card-result" style="display: none;">
            <!-- Card Status Banner -->
            <div class="mgc-status-banner" id="mgc-status-banner">
                <span class="mgc-status-icon"></span>
                <span class="mgc-status-text"></span>
            </div>

            <!-- Card Details -->
            <div class="mgc-card-details">
                <div class="mgc-balance-display">
                    <span class="mgc-balance-label"><?php _e('Available Balance', 'massnahme-gift-cards'); ?></span>
                    <span class="mgc-balance-amount" id="mgc-balance-amount"></span>
                </div>

                <div class="mgc-card-info-grid">
                    <div class="mgc-info-item">
                        <span class="mgc-info-label"><?php _e('Code', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-info-value" id="mgc-card-code"></span>
                    </div>
                    <div class="mgc-info-item">
                        <span class="mgc-info-label"><?php _e('Original Amount', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-info-value" id="mgc-original-amount"></span>
                    </div>
                    <div class="mgc-info-item">
                        <span class="mgc-info-label"><?php _e('Recipient', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-info-value" id="mgc-recipient"></span>
                    </div>
                    <div class="mgc-info-item">
                        <span class="mgc-info-label"><?php _e('Expires', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-info-value" id="mgc-expires"></span>
                    </div>
                    <div class="mgc-info-item">
                        <span class="mgc-info-label"><?php _e('Delivery Method', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-info-value" id="mgc-delivery-method"></span>
                    </div>
                    <div class="mgc-info-item" id="mgc-pickup-status-container" style="display: none;">
                        <span class="mgc-info-label"><?php _e('Pickup Status', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-info-value" id="mgc-pickup-status"></span>
                    </div>
                </div>
            </div>

            <!-- Partial Redemption Section -->
            <div class="mgc-redemption-section" id="mgc-redemption-section">
                <h3><?php _e('Redeem Amount', 'massnahme-gift-cards'); ?></h3>

                <div class="mgc-redemption-input-group">
                    <span class="mgc-currency-symbol"><?php echo get_woocommerce_currency_symbol(); ?></span>
                    <input type="number"
                           id="mgc-redeem-amount"
                           class="mgc-redeem-input"
                           step="0.01"
                           min="0.01"
                           placeholder="0.00">
                </div>

                <div class="mgc-quick-amounts">
                    <button type="button" class="mgc-quick-amount" data-amount="10">€10</button>
                    <button type="button" class="mgc-quick-amount" data-amount="25">€25</button>
                    <button type="button" class="mgc-quick-amount" data-amount="50">€50</button>
                    <button type="button" class="mgc-quick-amount" data-amount="100">€100</button>
                    <button type="button" class="mgc-quick-amount mgc-full-amount" data-amount="full"><?php _e('Full Balance', 'massnahme-gift-cards'); ?></button>
                </div>

                <div class="mgc-redemption-preview" id="mgc-redemption-preview" style="display: none;">
                    <div class="mgc-preview-row">
                        <span><?php _e('Current Balance:', 'massnahme-gift-cards'); ?></span>
                        <span id="mgc-preview-current"></span>
                    </div>
                    <div class="mgc-preview-row mgc-preview-deduct">
                        <span><?php _e('Amount to Redeem:', 'massnahme-gift-cards'); ?></span>
                        <span id="mgc-preview-deduct"></span>
                    </div>
                    <div class="mgc-preview-row mgc-preview-remaining">
                        <span><?php _e('Remaining Balance:', 'massnahme-gift-cards'); ?></span>
                        <span id="mgc-preview-remaining"></span>
                    </div>
                </div>

                <button type="button" id="mgc-confirm-redemption" class="button button-primary button-hero mgc-redeem-btn" disabled>
                    <?php _e('Confirm Redemption', 'massnahme-gift-cards'); ?>
                </button>
            </div>

            <!-- Pickup Status Update (for pickup orders) -->
            <div class="mgc-pickup-actions" id="mgc-pickup-actions" style="display: none;">
                <h3><?php _e('Update Pickup Status', 'massnahme-gift-cards'); ?></h3>
                <div class="mgc-pickup-status-buttons">
                    <button type="button" class="mgc-status-btn" data-status="ordered">
                        <span class="dashicons dashicons-clock"></span>
                        <?php _e('Ordered', 'massnahme-gift-cards'); ?>
                    </button>
                    <button type="button" class="mgc-status-btn" data-status="preparing">
                        <span class="dashicons dashicons-hammer"></span>
                        <?php _e('Preparing', 'massnahme-gift-cards'); ?>
                    </button>
                    <button type="button" class="mgc-status-btn" data-status="ready">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php _e('Ready for Pickup', 'massnahme-gift-cards'); ?>
                    </button>
                    <button type="button" class="mgc-status-btn" data-status="collected">
                        <span class="dashicons dashicons-saved"></span>
                        <?php _e('Collected', 'massnahme-gift-cards'); ?>
                    </button>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="mgc-transaction-history" id="mgc-transaction-history">
                <h3><?php _e('Transaction History', 'massnahme-gift-cards'); ?></h3>
                <div class="mgc-history-list" id="mgc-history-list"></div>
            </div>

            <!-- Clear / New Lookup -->
            <button type="button" id="mgc-clear-btn" class="button mgc-clear-btn">
                <?php _e('Clear / New Lookup', 'massnahme-gift-cards'); ?>
            </button>
        </div>

        <!-- Error Display -->
        <div id="mgc-error-result" class="mgc-error-result" style="display: none;">
            <div class="mgc-error-icon">
                <span class="dashicons dashicons-warning"></span>
            </div>
            <div class="mgc-error-message" id="mgc-error-message"></div>
            <button type="button" id="mgc-try-again-btn" class="button">
                <?php _e('Try Again', 'massnahme-gift-cards'); ?>
            </button>
        </div>
    </div>
</div>

<style>
/* Staff Validator Styles - Mobile-first approach */
.mgc-staff-validator {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.mgc-staff-validator h1 {
    text-align: center;
    margin-bottom: 30px;
}

.mgc-validator-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 30px;
}

/* Code Entry */
.mgc-code-entry {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
}

.mgc-code-input {
    font-size: 24px !important;
    padding: 15px 20px !important;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 2px;
    border: 2px solid #ddd !important;
    border-radius: 8px !important;
    transition: border-color 0.3s;
}

.mgc-code-input:focus {
    border-color: #2271b1 !important;
    outline: none;
    box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.2);
}

#mgc-lookup-btn {
    padding: 15px 30px !important;
    font-size: 18px !important;
}

/* Status Banner */
.mgc-status-banner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 25px;
    font-size: 18px;
    font-weight: 600;
}

.mgc-status-banner.status-active {
    background: #d4edda;
    color: #155724;
}

.mgc-status-banner.status-used {
    background: #f8d7da;
    color: #721c24;
}

.mgc-status-banner.status-expired {
    background: #fff3cd;
    color: #856404;
}

.mgc-status-icon {
    font-size: 24px;
}

/* Balance Display */
.mgc-balance-display {
    text-align: center;
    padding: 30px;
    background: linear-gradient(135deg, #1e3a5f 0%, #2271b1 100%);
    border-radius: 12px;
    margin-bottom: 25px;
}

.mgc-balance-label {
    display: block;
    color: rgba(255,255,255,0.8);
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
}

.mgc-balance-amount {
    display: block;
    color: #fff;
    font-size: 48px;
    font-weight: 700;
}

/* Card Info Grid */
.mgc-card-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 30px;
}

.mgc-info-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.mgc-info-label {
    display: block;
    color: #666;
    font-size: 12px;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.mgc-info-value {
    display: block;
    font-size: 16px;
    font-weight: 600;
    color: #1d2327;
    word-break: break-word;
}

/* Redemption Section */
.mgc-redemption-section {
    border-top: 1px solid #eee;
    padding-top: 25px;
    margin-bottom: 25px;
}

.mgc-redemption-section h3 {
    margin: 0 0 20px 0;
    text-align: center;
}

.mgc-redemption-input-group {
    display: flex;
    align-items: center;
    max-width: 300px;
    margin: 0 auto 20px;
    border: 2px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.mgc-currency-symbol {
    padding: 15px;
    background: #f8f9fa;
    font-size: 24px;
    font-weight: 600;
    color: #666;
}

.mgc-redeem-input {
    flex: 1;
    border: none !important;
    padding: 15px !important;
    font-size: 24px !important;
    text-align: center;
    box-shadow: none !important;
}

.mgc-redeem-input:focus {
    outline: none;
}

/* Quick Amount Buttons */
.mgc-quick-amounts {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-bottom: 20px;
}

.mgc-quick-amount {
    padding: 10px 20px;
    border: 2px solid #ddd;
    background: #fff;
    border-radius: 25px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
}

.mgc-quick-amount:hover {
    border-color: #2271b1;
    color: #2271b1;
}

.mgc-quick-amount.selected {
    background: #2271b1;
    border-color: #2271b1;
    color: #fff;
}

.mgc-full-amount {
    background: #f0f7ff;
}

/* Redemption Preview */
.mgc-redemption-preview {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.mgc-preview-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.mgc-preview-row:last-child {
    border-bottom: none;
}

.mgc-preview-deduct {
    color: #dc3545;
}

.mgc-preview-remaining {
    font-weight: 700;
    font-size: 18px;
    color: #155724;
}

.mgc-redeem-btn {
    display: block;
    width: 100%;
    padding: 15px !important;
    font-size: 18px !important;
}

.mgc-redeem-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Pickup Status Actions */
.mgc-pickup-actions {
    border-top: 1px solid #eee;
    padding-top: 25px;
    margin-bottom: 25px;
}

.mgc-pickup-actions h3 {
    margin: 0 0 20px 0;
    text-align: center;
}

.mgc-pickup-status-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.mgc-status-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 20px 15px;
    border: 2px solid #ddd;
    background: #fff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.mgc-status-btn:hover {
    border-color: #2271b1;
    background: #f0f7ff;
}

.mgc-status-btn.active {
    border-color: #2271b1;
    background: #2271b1;
    color: #fff;
}

.mgc-status-btn .dashicons {
    font-size: 28px;
    width: 28px;
    height: 28px;
}

/* Transaction History */
.mgc-transaction-history {
    border-top: 1px solid #eee;
    padding-top: 25px;
    margin-bottom: 25px;
}

.mgc-transaction-history h3 {
    margin: 0 0 15px 0;
}

.mgc-history-list {
    max-height: 200px;
    overflow-y: auto;
}

.mgc-history-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid #eee;
}

.mgc-history-item:last-child {
    border-bottom: none;
}

.mgc-history-date {
    color: #666;
    font-size: 13px;
}

.mgc-history-amount {
    font-weight: 600;
}

.mgc-history-amount.debit {
    color: #dc3545;
}

.mgc-history-order {
    font-size: 13px;
    color: #2271b1;
}

/* Clear Button */
.mgc-clear-btn {
    display: block;
    width: 100%;
    text-align: center;
    padding: 12px !important;
}

/* Error Display */
.mgc-error-result {
    text-align: center;
    padding: 40px 20px;
}

.mgc-error-icon {
    color: #dc3545;
    margin-bottom: 20px;
}

.mgc-error-icon .dashicons {
    font-size: 64px;
    width: 64px;
    height: 64px;
}

.mgc-error-message {
    font-size: 18px;
    color: #721c24;
    margin-bottom: 20px;
}

/* Responsive */
@media (max-width: 600px) {
    .mgc-staff-validator {
        padding: 10px;
    }

    .mgc-validator-container {
        padding: 20px 15px;
    }

    .mgc-code-input {
        font-size: 18px !important;
        letter-spacing: 1px;
    }

    .mgc-balance-amount {
        font-size: 36px;
    }

    .mgc-card-info-grid {
        grid-template-columns: 1fr;
    }

    .mgc-pickup-status-buttons {
        grid-template-columns: 1fr;
    }
}

/* Loading State */
.mgc-loading {
    position: relative;
    pointer-events: none;
}

.mgc-loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script>
jQuery(document).ready(function($) {
    var currentCard = null;
    var currentBalance = 0;

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: mgc_admin.currency || 'EUR'
        }).format(amount);
    }

    // Lookup gift card
    function lookupCard() {
        var code = $('#mgc_code').val().trim().toUpperCase();
        if (!code) {
            showError('<?php _e('Please enter a gift card code', 'massnahme-gift-cards'); ?>');
            return;
        }

        $('#mgc-lookup-btn').prop('disabled', true).text('<?php _e('Looking up...', 'massnahme-gift-cards'); ?>');

        $.ajax({
            url: mgc_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'mgc_staff_lookup',
                nonce: mgc_admin.nonce,
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
                showError('<?php _e('Connection error. Please try again.', 'massnahme-gift-cards'); ?>');
            },
            complete: function() {
                $('#mgc-lookup-btn').prop('disabled', false).text('<?php _e('Look Up', 'massnahme-gift-cards'); ?>');
            }
        });
    }

    // Display card details
    function displayCard(card) {
        currentCard = card;
        currentBalance = parseFloat(card.balance);

        // Hide error, show result
        $('#mgc-error-result').hide();
        $('#mgc-card-result').show();

        // Status banner
        var statusBanner = $('#mgc-status-banner');
        statusBanner.removeClass('status-active status-used status-expired');
        statusBanner.addClass('status-' + card.status);

        var statusText = {
            'active': '<?php _e('VALID - Ready to Use', 'massnahme-gift-cards'); ?>',
            'used': '<?php _e('FULLY REDEEMED', 'massnahme-gift-cards'); ?>',
            'expired': '<?php _e('EXPIRED', 'massnahme-gift-cards'); ?>'
        };

        var statusIcon = {
            'active': '✓',
            'used': '✗',
            'expired': '⚠'
        };

        statusBanner.find('.mgc-status-icon').text(statusIcon[card.status] || '?');
        statusBanner.find('.mgc-status-text').text(statusText[card.status] || card.status);

        // Card details
        $('#mgc-balance-amount').text(formatCurrency(card.balance));
        $('#mgc-card-code').text(card.code);
        $('#mgc-original-amount').text(formatCurrency(card.amount));
        $('#mgc-recipient').text(card.recipient_name || card.recipient_email);
        $('#mgc-expires').text(card.expires_at);

        // Delivery method
        var deliveryLabels = {
            'digital': '<?php _e('Digital Email', 'massnahme-gift-cards'); ?>',
            'pickup': '<?php _e('Store Pickup', 'massnahme-gift-cards'); ?>',
            'shipping': '<?php _e('Shipping', 'massnahme-gift-cards'); ?>'
        };
        $('#mgc-delivery-method').text(deliveryLabels[card.delivery_method] || card.delivery_method);

        // Show/hide redemption section based on status
        if (card.status === 'active' && currentBalance > 0) {
            $('#mgc-redemption-section').show();
            $('#mgc-redeem-amount').attr('max', currentBalance);
        } else {
            $('#mgc-redemption-section').hide();
        }

        // Pickup status section
        if (card.delivery_method === 'pickup') {
            $('#mgc-pickup-status-container').show();
            $('#mgc-pickup-status').text(card.pickup_status || '<?php _e('Ordered', 'massnahme-gift-cards'); ?>');
            $('#mgc-pickup-actions').show();

            // Highlight current status
            $('.mgc-status-btn').removeClass('active');
            $('.mgc-status-btn[data-status="' + (card.pickup_status || 'ordered') + '"]').addClass('active');
        } else {
            $('#mgc-pickup-status-container').hide();
            $('#mgc-pickup-actions').hide();
        }

        // Transaction history
        displayHistory(card.history || []);

        // Reset redemption inputs
        $('#mgc-redeem-amount').val('');
        $('#mgc-redemption-preview').hide();
        $('#mgc-confirm-redemption').prop('disabled', true);
        $('.mgc-quick-amount').removeClass('selected');
    }

    // Display transaction history
    function displayHistory(history) {
        var html = '';
        if (history.length === 0) {
            html = '<p style="color:#666;text-align:center;"><?php _e('No transactions yet', 'massnahme-gift-cards'); ?></p>';
        } else {
            history.forEach(function(item) {
                html += '<div class="mgc-history-item">';
                html += '<div>';
                html += '<span class="mgc-history-date">' + item.date + '</span>';
                if (item.order_id > 0) {
                    html += '<br><span class="mgc-history-order"><?php _e('Order', 'massnahme-gift-cards'); ?> #' + item.order_id + '</span>';
                } else {
                    html += '<br><span class="mgc-history-order"><?php _e('Manual adjustment', 'massnahme-gift-cards'); ?></span>';
                }
                html += '</div>';
                html += '<span class="mgc-history-amount debit">-' + formatCurrency(item.amount) + '</span>';
                html += '</div>';
            });
        }
        $('#mgc-history-list').html(html);
    }

    // Show error
    function showError(message) {
        $('#mgc-card-result').hide();
        $('#mgc-error-result').show();
        $('#mgc-error-message').text(message);
    }

    // Clear/reset
    function clearLookup() {
        currentCard = null;
        currentBalance = 0;
        $('#mgc_code').val('').focus();
        $('#mgc-card-result').hide();
        $('#mgc-error-result').hide();
    }

    // Update redemption preview
    function updateRedemptionPreview() {
        var amount = parseFloat($('#mgc-redeem-amount').val()) || 0;

        if (amount > 0 && amount <= currentBalance) {
            var remaining = currentBalance - amount;

            $('#mgc-preview-current').text(formatCurrency(currentBalance));
            $('#mgc-preview-deduct').text('-' + formatCurrency(amount));
            $('#mgc-preview-remaining').text(formatCurrency(remaining));

            $('#mgc-redemption-preview').show();
            $('#mgc-confirm-redemption').prop('disabled', false);
        } else {
            $('#mgc-redemption-preview').hide();
            $('#mgc-confirm-redemption').prop('disabled', true);
        }
    }

    // Confirm redemption
    function confirmRedemption() {
        var amount = parseFloat($('#mgc-redeem-amount').val());

        if (!amount || amount <= 0 || amount > currentBalance) {
            alert('<?php _e('Please enter a valid amount', 'massnahme-gift-cards'); ?>');
            return;
        }

        if (!confirm('<?php _e('Are you sure you want to redeem this amount?', 'massnahme-gift-cards'); ?>\n\n' + formatCurrency(amount))) {
            return;
        }

        var newBalance = currentBalance - amount;

        $('#mgc-confirm-redemption').prop('disabled', true).text('<?php _e('Processing...', 'massnahme-gift-cards'); ?>');

        $.ajax({
            url: mgc_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'mgc_update_balance',
                nonce: mgc_admin.nonce,
                code: currentCard.code,
                balance: newBalance
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Redemption successful!', 'massnahme-gift-cards'); ?>\n\n<?php _e('New balance:', 'massnahme-gift-cards'); ?> ' + formatCurrency(newBalance));
                    // Refresh card display
                    lookupCard();
                } else {
                    alert('<?php _e('Error:', 'massnahme-gift-cards'); ?> ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('Connection error. Please try again.', 'massnahme-gift-cards'); ?>');
            },
            complete: function() {
                $('#mgc-confirm-redemption').prop('disabled', false).text('<?php _e('Confirm Redemption', 'massnahme-gift-cards'); ?>');
            }
        });
    }

    // Update pickup status
    function updatePickupStatus(status) {
        if (!currentCard) return;

        $.ajax({
            url: mgc_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'mgc_update_pickup_status',
                nonce: mgc_admin.nonce,
                code: currentCard.code,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    $('.mgc-status-btn').removeClass('active');
                    $('.mgc-status-btn[data-status="' + status + '"]').addClass('active');
                    $('#mgc-pickup-status').text(response.data.status_label);
                    currentCard.pickup_status = status;
                } else {
                    alert('<?php _e('Error:', 'massnahme-gift-cards'); ?> ' + response.data);
                }
            }
        });
    }

    // Event handlers
    $('#mgc-lookup-btn').on('click', lookupCard);

    $('#mgc_code').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            lookupCard();
        }
    });

    $('#mgc-clear-btn, #mgc-try-again-btn').on('click', clearLookup);

    $('#mgc-redeem-amount').on('input', function() {
        $('.mgc-quick-amount').removeClass('selected');
        updateRedemptionPreview();
    });

    $('.mgc-quick-amount').on('click', function() {
        var amount = $(this).data('amount');

        $('.mgc-quick-amount').removeClass('selected');
        $(this).addClass('selected');

        if (amount === 'full') {
            amount = currentBalance;
        }

        $('#mgc-redeem-amount').val(amount);
        updateRedemptionPreview();
    });

    $('#mgc-confirm-redemption').on('click', confirmRedemption);

    $('.mgc-status-btn').on('click', function() {
        updatePickupStatus($(this).data('status'));
    });
});
</script>
