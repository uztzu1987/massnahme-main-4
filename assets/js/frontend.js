/**
 * Gift Card Frontend JS
 */
(function($) {
    'use strict';

    // Balance checker
    $(document).on('click', '.mgc-check-balance', function(e) {
        e.preventDefault();

        var code = $('#mgc_code').val();
        var $result = $('.mgc-balance-result');
        var $button = $(this);

        if (!code) {
            $result.removeClass('success').addClass('error').text('Please enter a gift card code').show();
            return;
        }

        $button.prop('disabled', true).text('Checking...');

        $.ajax({
            url: mgc_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'mgc_validate_code',
                code: code,
                nonce: mgc_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.removeClass('error').addClass('success')
                        .html(response.data.message + '<br>Expires: ' + response.data.expires)
                        .show();
                } else {
                    $result.removeClass('success').addClass('error')
                        .text(response.data)
                        .show();
                }
            },
            complete: function() {
                $button.prop('disabled', false).text('Check Balance');
            }
        });
    });

    // Delivery date picker enhancement
    if ($('#mgc_delivery_date').length) {
        $('#mgc_delivery_date').attr('min', new Date().toISOString().split('T')[0]);
    }

    // Delivery method handling
    function initDeliveryMethods() {
        var $deliveryOptions = $('input[name="mgc_delivery_method"]');

        if ($deliveryOptions.length === 0) {
            return;
        }

        // Handle delivery method change
        $deliveryOptions.on('change', function() {
            updateDeliveryUI($(this).val());
            updateShippingFee($(this).val());
        });

        // Initialize with current selection
        var initialMethod = $deliveryOptions.filter(':checked').val() || 'digital';
        updateDeliveryUI(initialMethod);
    }

    function updateDeliveryUI(method) {
        // Update option styling
        $('.mgc-delivery-option').removeClass('active');
        $('.mgc-delivery-option[data-method="' + method + '"]').addClass('active');

        // Hide all conditional sections first
        $('#mgc-digital-section').hide();
        $('#mgc-pickup-section').hide();
        $('#mgc-recipient-section').hide();
        $('#mgc-shipping-notice').hide();
        $('#mgc-pickup-notice').hide();

        switch (method) {
            case 'digital':
                $('#mgc-digital-section').slideDown(200);
                break;

            case 'pickup':
                $('#mgc-pickup-section').slideDown(200);
                $('#mgc-recipient-section').slideDown(200);
                $('#mgc-pickup-notice').slideDown(200);
                break;

            case 'shipping':
                $('#mgc-recipient-section').slideDown(200);
                $('#mgc-shipping-notice').slideDown(200);
                break;
        }
    }

    function updateShippingFee(method) {
        // Only proceed if shipping data is available
        if (typeof mgc_ajax === 'undefined' || !mgc_ajax.shipping_cost) {
            return;
        }

        // Trigger cart update if shipping method changes
        if (method === 'shipping') {
            // Add shipping fee to session
            $.ajax({
                url: mgc_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'mgc_set_delivery_method',
                    method: method,
                    nonce: mgc_ajax.nonce
                },
                success: function() {
                    // Trigger WooCommerce to update cart totals
                    $(document.body).trigger('update_checkout');
                }
            });
        } else {
            // Remove shipping fee from session
            $.ajax({
                url: mgc_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'mgc_set_delivery_method',
                    method: method,
                    nonce: mgc_ajax.nonce
                },
                success: function() {
                    $(document.body).trigger('update_checkout');
                }
            });
        }
    }

    // Store location selection styling
    $(document).on('change', 'input[name="mgc_pickup_location"]', function() {
        $('.mgc-store-location-option').removeClass('selected');
        $(this).closest('.mgc-store-location-option').addClass('selected');
    });

    // Initialize on document ready
    $(document).ready(function() {
        initDeliveryMethods();

        // Also initialize after checkout updates
        $(document.body).on('updated_checkout', function() {
            initDeliveryMethods();
        });

        // Mark initially selected store location
        $('input[name="mgc_pickup_location"]:checked').closest('.mgc-store-location-option').addClass('selected');
    });

})(jQuery);
