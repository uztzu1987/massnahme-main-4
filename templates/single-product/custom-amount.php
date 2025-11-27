<?php
/**
 * Custom Amount Gift Card Input Template
 *
 * @var float $min_amount
 * @var float $max_amount
 * @var string $currency_symbol
 * @var float $default_amount
 */

defined('ABSPATH') || exit;
?>

<div class="mgc-custom-amount-wrapper">
    <div class="mgc-custom-amount-label">
        <label for="mgc_custom_amount"><?php _e('Choose Your Amount', 'massnahme-gift-cards'); ?></label>
        <span class="mgc-amount-range">
            <?php printf(
                __('%s - %s', 'massnahme-gift-cards'),
                wc_price($min_amount),
                wc_price($max_amount)
            ); ?>
        </span>
    </div>

    <div class="mgc-custom-amount-input-wrapper">
        <span class="mgc-currency-symbol"><?php echo esc_html($currency_symbol); ?></span>
        <input
            type="number"
            id="mgc_custom_amount"
            name="mgc_custom_amount"
            class="mgc-custom-amount-input"
            value="<?php echo esc_attr($default_amount); ?>"
            min="<?php echo esc_attr($min_amount); ?>"
            max="<?php echo esc_attr($max_amount); ?>"
            step="1"
            required
            data-min="<?php echo esc_attr($min_amount); ?>"
            data-max="<?php echo esc_attr($max_amount); ?>"
        >
    </div>

    <div class="mgc-quick-amounts">
        <?php
        // Generate quick select amounts within range
        $quick_amounts = [50, 75, 100, 150, 200, 250, 300];
        $available_amounts = array_filter($quick_amounts, function($amount) use ($min_amount, $max_amount) {
            return $amount >= $min_amount && $amount <= $max_amount;
        });

        if (!empty($available_amounts)) :
        ?>
        <span class="mgc-quick-label"><?php _e('Quick select:', 'massnahme-gift-cards'); ?></span>
        <div class="mgc-quick-buttons">
            <?php foreach ($available_amounts as $amount) : ?>
            <button type="button" class="mgc-quick-amount" data-amount="<?php echo esc_attr($amount); ?>">
                <?php echo wc_price($amount); ?>
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="mgc-amount-validation" style="display: none;">
        <span class="mgc-validation-message"></span>
    </div>

    <div class="mgc-custom-amount-preview">
        <span class="mgc-preview-label"><?php _e('Gift Card Value:', 'massnahme-gift-cards'); ?></span>
        <span class="mgc-preview-amount"><?php echo wc_price($default_amount); ?></span>
    </div>
</div>

<style>
.mgc-custom-amount-wrapper {
    margin: 20px 0 30px;
    padding: 25px;
    background: linear-gradient(135deg, #f8f8f8 0%, #ffffff 100%);
    border: 1px solid #e5e5e5;
    border-radius: 12px;
}

.mgc-custom-amount-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.mgc-custom-amount-label label {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

.mgc-amount-range {
    font-size: 13px;
    color: #666;
    background: #f0f0f0;
    padding: 4px 12px;
    border-radius: 20px;
}

.mgc-custom-amount-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.mgc-currency-symbol {
    position: absolute;
    left: 16px;
    font-size: 24px;
    font-weight: 300;
    color: #1a1a1a;
    z-index: 1;
}

.mgc-custom-amount-input {
    width: 100%;
    padding: 18px 20px 18px 50px;
    font-size: 28px;
    font-weight: 600;
    border: 2px solid #ddd;
    border-radius: 8px;
    text-align: left;
    transition: all 0.2s ease;
    -moz-appearance: textfield;
}

.mgc-custom-amount-input::-webkit-outer-spin-button,
.mgc-custom-amount-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.mgc-custom-amount-input:focus {
    outline: none;
    border-color: #1a1a1a;
    box-shadow: 0 0 0 3px rgba(26, 26, 26, 0.1);
}

.mgc-custom-amount-input.invalid {
    border-color: #dc3545;
}

.mgc-custom-amount-input.valid {
    border-color: #28a745;
}

.mgc-quick-amounts {
    margin-bottom: 20px;
}

.mgc-quick-label {
    display: block;
    font-size: 12px;
    color: #888;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.mgc-quick-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.mgc-quick-amount {
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 500;
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.mgc-quick-amount:hover {
    border-color: #1a1a1a;
    background: #f8f8f8;
}

.mgc-quick-amount.active {
    background: #1a1a1a;
    border-color: #1a1a1a;
    color: #ffffff;
}

.mgc-amount-validation {
    padding: 10px 15px;
    margin-bottom: 15px;
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 6px;
    color: #856404;
    font-size: 13px;
}

.mgc-amount-validation.error {
    background: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.mgc-custom-amount-preview {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: linear-gradient(135deg, #1a1a1a 0%, #333333 100%);
    border-radius: 8px;
    color: #ffffff;
}

.mgc-preview-label {
    font-size: 14px;
    color: #aaa;
}

.mgc-preview-amount {
    font-size: 24px;
    font-weight: 600;
    color: #d4af37;
}

@media (max-width: 480px) {
    .mgc-custom-amount-wrapper {
        padding: 20px 15px;
    }

    .mgc-custom-amount-label {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .mgc-custom-amount-input {
        font-size: 24px;
        padding: 15px 15px 15px 45px;
    }

    .mgc-currency-symbol {
        font-size: 20px;
        left: 14px;
    }

    .mgc-quick-buttons {
        justify-content: center;
    }

    .mgc-preview-amount {
        font-size: 20px;
    }
}
</style>

<script>
jQuery(function($) {
    var $input = $('#mgc_custom_amount');
    var $preview = $('.mgc-preview-amount');
    var $validation = $('.mgc-amount-validation');
    var $validationMsg = $('.mgc-validation-message');
    var $quickButtons = $('.mgc-quick-amount');

    var minAmount = parseFloat($input.data('min'));
    var maxAmount = parseFloat($input.data('max'));

    function formatPrice(amount) {
        return '<?php echo esc_js($currency_symbol); ?>' + amount.toFixed(0);
    }

    function validateAmount(amount) {
        $quickButtons.removeClass('active');

        if (amount < minAmount) {
            $input.removeClass('valid').addClass('invalid');
            $validationMsg.text('<?php echo esc_js(sprintf(__('Minimum amount is %s', 'massnahme-gift-cards'), wc_price($min_amount))); ?>');
            $validation.addClass('error').show();
            return false;
        }

        if (amount > maxAmount) {
            $input.removeClass('valid').addClass('invalid');
            $validationMsg.text('<?php echo esc_js(sprintf(__('Maximum amount is %s', 'massnahme-gift-cards'), wc_price($max_amount))); ?>');
            $validation.addClass('error').show();
            return false;
        }

        $input.removeClass('invalid').addClass('valid');
        $validation.hide();

        // Highlight matching quick button
        $quickButtons.each(function() {
            if (parseFloat($(this).data('amount')) === amount) {
                $(this).addClass('active');
            }
        });

        return true;
    }

    function updatePreview(amount) {
        if (validateAmount(amount)) {
            $preview.text(formatPrice(amount));
        }
    }

    // Input change handler
    $input.on('input change', function() {
        var amount = parseFloat($(this).val()) || 0;
        updatePreview(amount);
    });

    // Quick amount buttons
    $quickButtons.on('click', function(e) {
        e.preventDefault();
        var amount = parseFloat($(this).data('amount'));
        $input.val(amount).trigger('change');
    });

    // Initialize
    updatePreview(parseFloat($input.val()) || minAmount);

    // Prevent form submission with invalid amount
    $input.closest('form').on('submit', function(e) {
        var amount = parseFloat($input.val()) || 0;
        if (!validateAmount(amount)) {
            e.preventDefault();
            $input.focus();
            return false;
        }
    });
});
</script>
