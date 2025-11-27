/**
 * Admin JavaScript for Massnahme Gift Cards
 */

(function($) {
    'use strict';

    var currentEditCode = null;
    var currentEditAmount = 0;

    $(document).ready(function() {
        // Validate gift card form
        $('#mgc-validate-form').on('submit', function(e) {
            e.preventDefault();

            var code = $('#mgc_code').val();
            if (!code) {
                alert('Please enter a gift card code');
                return;
            }

            // Show loading
            var $result = $('#mgc-validate-result');
            $result.html('<p>Validating...</p>').show();

            // AJAX validation
            $.post(mgc_admin.ajax_url, {
                action: 'mgc_validate_code',
                code: code,
                nonce: mgc_admin.nonce
            }, function(response) {
                if (response.success) {
                    $result.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                } else {
                    $result.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            }).fail(function() {
                $result.html('<div class="notice notice-error"><p>Validation failed. Please try again.</p></div>');
            });
        });

        // Edit Balance Modal
        var $modal = $('#mgc-edit-balance-modal');

        // Open modal when clicking edit button
        $(document).on('click', '.mgc-edit-balance', function() {
            var $btn = $(this);
            currentEditCode = $btn.data('code');
            currentEditAmount = parseFloat($btn.data('amount'));
            var currentBalance = parseFloat($btn.data('balance'));

            // Populate modal
            $('#mgc-modal-code').text(currentEditCode);
            $('#mgc-modal-amount').text(formatCurrency(currentEditAmount));
            $('#mgc-new-balance').val(currentBalance).attr('max', currentEditAmount);

            // Show modal
            $modal.show();
        });

        // Close modal
        $(document).on('click', '.mgc-modal-close, .mgc-modal-cancel', function() {
            closeModal();
        });

        // Close modal on background click
        $modal.on('click', function(e) {
            if ($(e.target).is('.mgc-modal')) {
                closeModal();
            }
        });

        // Close modal on Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                closeModal();
            }
        });

        // Save balance
        $(document).on('click', '.mgc-modal-save', function() {
            var newBalance = parseFloat($('#mgc-new-balance').val());

            // Validation
            if (isNaN(newBalance) || newBalance < 0) {
                alert(mgc_admin.i18n ? mgc_admin.i18n.invalid_balance : 'Please enter a valid balance');
                return;
            }

            if (newBalance > currentEditAmount) {
                alert(mgc_admin.i18n ? mgc_admin.i18n.balance_exceeds : 'Balance cannot exceed original amount');
                return;
            }

            var $saveBtn = $(this);
            $saveBtn.prop('disabled', true).text(mgc_admin.i18n ? mgc_admin.i18n.saving : 'Saving...');

            // AJAX request
            $.post(mgc_admin.ajax_url, {
                action: 'mgc_update_balance',
                code: currentEditCode,
                balance: newBalance,
                nonce: mgc_admin.nonce
            }, function(response) {
                if (response.success) {
                    // Update the table row
                    var $row = $('tr[data-code="' + currentEditCode + '"]');
                    $row.find('.mgc-balance-cell').html(response.data.formatted_balance);
                    $row.find('.mgc-edit-balance').data('balance', newBalance);

                    // Update status badge
                    var statusClass = 'mgc-status-' + response.data.new_status;
                    var statusText = response.data.new_status.charAt(0).toUpperCase() + response.data.new_status.slice(1);
                    $row.find('.mgc-status-cell').html(
                        '<span class="mgc-status ' + statusClass + '">' + statusText + '</span>'
                    );

                    closeModal();
                } else {
                    alert(response.data || 'Update failed');
                }
            }).fail(function() {
                alert(mgc_admin.i18n ? mgc_admin.i18n.update_failed : 'Update failed. Please try again.');
            }).always(function() {
                $saveBtn.prop('disabled', false).text(mgc_admin.i18n ? mgc_admin.i18n.update_balance : 'Update Balance');
            });
        });

        function closeModal() {
            $modal.hide();
            currentEditCode = null;
            currentEditAmount = 0;
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat(undefined, {
                style: 'currency',
                currency: mgc_admin.currency || 'EUR'
            }).format(amount);
        }

        // History Modal
        var $historyModal = $('#mgc-history-modal');

        // Open history modal
        $(document).on('click', '.mgc-view-history', function() {
            var $btn = $(this);
            var code = $btn.data('code');
            var id = $btn.data('id');

            // Reset modal
            $('#mgc-history-id').text('#' + id);
            $('#mgc-history-code').text(code);
            $('#mgc-history-loading').show();
            $('#mgc-history-table').hide();
            $('#mgc-history-empty').hide();
            $('#mgc-history-tbody').empty();

            // Show modal
            $historyModal.show();

            // Fetch history via AJAX
            $.post(mgc_admin.ajax_url, {
                action: 'mgc_staff_lookup',
                code: code,
                nonce: mgc_admin.nonce
            }, function(response) {
                $('#mgc-history-loading').hide();

                if (response.success) {
                    var data = response.data;

                    // Populate card info
                    $('#mgc-history-amount').text(formatCurrency(data.amount));
                    $('#mgc-history-balance').text(formatCurrency(data.balance));
                    $('#mgc-history-status').text(data.status.charAt(0).toUpperCase() + data.status.slice(1));

                    // Populate history table
                    if (data.history && data.history.length > 0) {
                        var $tbody = $('#mgc-history-tbody');
                        $.each(data.history, function(i, item) {
                            var orderCell = item.order_id === 0
                                ? '<span class="mgc-history-manual">Manual</span>'
                                : '<a href="' + mgc_admin.admin_url + 'post.php?post=' + item.order_id + '&action=edit" class="mgc-history-order-link" target="_blank">#' + item.order_id + '</a>';

                            var userCell = '';
                            if (item.updated_by_name) {
                                userCell = '<span class="mgc-history-user">' + item.updated_by_name + '</span>';
                                if (item.updated_by) {
                                    userCell += '<br><span class="mgc-history-user-id">(ID: ' + item.updated_by + ')</span>';
                                }
                            } else if (item.updated_by) {
                                userCell = '<span class="mgc-history-user-id">User ID: ' + item.updated_by + '</span>';
                            } else {
                                userCell = '<span class="mgc-history-user-id">—</span>';
                            }

                            $tbody.append(
                                '<tr>' +
                                '<td>' + item.date + '</td>' +
                                '<td>' + formatCurrency(item.amount) + '</td>' +
                                '<td>' + formatCurrency(item.remaining) + '</td>' +
                                '<td>' + orderCell + '</td>' +
                                '<td>' + userCell + '</td>' +
                                '</tr>'
                            );
                        });
                        $('#mgc-history-table').show();
                    } else {
                        $('#mgc-history-empty').show();
                    }
                } else {
                    $('#mgc-history-empty').text(response.data || 'Failed to load history').show();
                }
            }).fail(function() {
                $('#mgc-history-loading').hide();
                $('#mgc-history-empty').text('Failed to load history. Please try again.').show();
            });
        });

        // Close history modal
        $(document).on('click', '#mgc-history-modal .mgc-modal-close, #mgc-history-modal .mgc-modal-close-btn', function() {
            $historyModal.hide();
        });

        // Close history modal on background click
        $historyModal.on('click', function(e) {
            if ($(e.target).is('.mgc-modal')) {
                $historyModal.hide();
            }
        });

        // Close history modal on Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $historyModal.is(':visible')) {
                $historyModal.hide();
            }
        });
    });

})(jQuery);
