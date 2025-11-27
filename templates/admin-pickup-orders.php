<?php
/**
 * Pickup Orders Management Page
 * Shows all pickup orders with status workflow
 */
defined('ABSPATH') || exit;

if (!current_user_can('manage_woocommerce')) {
    return;
}

global $wpdb;
$table = $wpdb->prefix . 'mgc_gift_cards';

// Get filter parameters
$status_filter = isset($_GET['pickup_status']) ? sanitize_text_field($_GET['pickup_status']) : '';
$store_filter = isset($_GET['store']) ? sanitize_text_field($_GET['store']) : '';

// Build query
$where = "WHERE delivery_method = 'pickup'";
if ($status_filter) {
    $where .= $wpdb->prepare(" AND pickup_status = %s", $status_filter);
}
if ($store_filter !== '') {
    $where .= $wpdb->prepare(" AND pickup_location = %s", $store_filter);
}

// Get pickup orders
$pickup_orders = $wpdb->get_results(
    "SELECT * FROM $table $where ORDER BY
        CASE pickup_status
            WHEN 'ordered' THEN 1
            WHEN 'preparing' THEN 2
            WHEN 'ready' THEN 3
            WHEN 'collected' THEN 4
            ELSE 5
        END,
        created_at DESC
    LIMIT 100"
);

// Get counts by status
$status_counts = $wpdb->get_results(
    "SELECT pickup_status, COUNT(*) as count FROM $table WHERE delivery_method = 'pickup' GROUP BY pickup_status"
);
$counts = [];
foreach ($status_counts as $sc) {
    $counts[$sc->pickup_status ?? 'ordered'] = $sc->count;
}

$settings = get_option('mgc_settings', []);
$store_locations = $settings['store_locations'] ?? [];

$status_labels = [
    'ordered' => __('Ordered', 'massnahme-gift-cards'),
    'preparing' => __('Preparing', 'massnahme-gift-cards'),
    'ready' => __('Ready for Pickup', 'massnahme-gift-cards'),
    'collected' => __('Collected', 'massnahme-gift-cards')
];

$status_colors = [
    'ordered' => '#6c757d',
    'preparing' => '#fd7e14',
    'ready' => '#28a745',
    'collected' => '#17a2b8'
];
?>

<div class="wrap mgc-pickup-orders">
    <h1><?php _e('Pickup Orders', 'massnahme-gift-cards'); ?></h1>

    <!-- Status Summary Cards -->
    <div class="mgc-status-summary">
        <a href="<?php echo admin_url('admin.php?page=mgc-pickup-orders'); ?>" class="mgc-summary-card <?php echo !$status_filter ? 'active' : ''; ?>">
            <span class="mgc-summary-count"><?php echo array_sum($counts); ?></span>
            <span class="mgc-summary-label"><?php _e('All', 'massnahme-gift-cards'); ?></span>
        </a>
        <?php foreach ($status_labels as $status => $label) : ?>
            <a href="<?php echo admin_url('admin.php?page=mgc-pickup-orders&pickup_status=' . $status); ?>"
               class="mgc-summary-card <?php echo $status_filter === $status ? 'active' : ''; ?>"
               style="--status-color: <?php echo $status_colors[$status]; ?>">
                <span class="mgc-summary-count"><?php echo $counts[$status] ?? 0; ?></span>
                <span class="mgc-summary-label"><?php echo esc_html($label); ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Store Filter -->
    <?php if (!empty($store_locations)) : ?>
    <div class="mgc-store-filter">
        <label for="mgc-store-filter"><?php _e('Filter by Store:', 'massnahme-gift-cards'); ?></label>
        <select id="mgc-store-filter" onchange="window.location.href=this.value">
            <option value="<?php echo admin_url('admin.php?page=mgc-pickup-orders' . ($status_filter ? '&pickup_status=' . $status_filter : '')); ?>">
                <?php _e('All Stores', 'massnahme-gift-cards'); ?>
            </option>
            <?php foreach ($store_locations as $index => $store) : ?>
                <option value="<?php echo admin_url('admin.php?page=mgc-pickup-orders&store=' . $index . ($status_filter ? '&pickup_status=' . $status_filter : '')); ?>"
                    <?php selected($store_filter, (string)$index); ?>>
                    <?php echo esc_html($store['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <!-- Orders Table -->
    <?php if ($pickup_orders) : ?>
    <table class="wp-list-table widefat fixed striped mgc-pickup-table">
        <thead>
            <tr>
                <th class="column-status"><?php _e('Status', 'massnahme-gift-cards'); ?></th>
                <th class="column-code"><?php _e('Code', 'massnahme-gift-cards'); ?></th>
                <th class="column-amount"><?php _e('Amount', 'massnahme-gift-cards'); ?></th>
                <th class="column-recipient"><?php _e('Recipient', 'massnahme-gift-cards'); ?></th>
                <th class="column-store"><?php _e('Store', 'massnahme-gift-cards'); ?></th>
                <th class="column-date"><?php _e('Ordered', 'massnahme-gift-cards'); ?></th>
                <th class="column-actions"><?php _e('Actions', 'massnahme-gift-cards'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pickup_orders as $order) :
                $current_status = $order->pickup_status ?? 'ordered';
                $store = $store_locations[$order->pickup_location] ?? null;
            ?>
            <tr data-code="<?php echo esc_attr($order->code); ?>" class="status-<?php echo esc_attr($current_status); ?>">
                <td class="column-status">
                    <span class="mgc-pickup-status mgc-pickup-status-<?php echo esc_attr($current_status); ?>">
                        <?php echo esc_html($status_labels[$current_status] ?? ucfirst($current_status)); ?>
                    </span>
                </td>
                <td class="column-code">
                    <strong><?php echo esc_html($order->code); ?></strong>
                </td>
                <td class="column-amount">
                    <?php echo wc_price($order->amount); ?>
                </td>
                <td class="column-recipient">
                    <?php if ($order->recipient_name) : ?>
                        <strong><?php echo esc_html($order->recipient_name); ?></strong><br>
                    <?php endif; ?>
                    <span class="mgc-email"><?php echo esc_html($order->recipient_email); ?></span>
                </td>
                <td class="column-store">
                    <?php if ($store) : ?>
                        <?php echo esc_html($store['name']); ?>
                    <?php else : ?>
                        <em><?php _e('Unknown', 'massnahme-gift-cards'); ?></em>
                    <?php endif; ?>
                </td>
                <td class="column-date">
                    <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->created_at)); ?>
                </td>
                <td class="column-actions">
                    <div class="mgc-action-buttons">
                        <?php if ($current_status === 'ordered') : ?>
                            <button type="button" class="button mgc-update-status" data-code="<?php echo esc_attr($order->code); ?>" data-status="preparing">
                                <?php _e('Start Preparing', 'massnahme-gift-cards'); ?>
                            </button>
                        <?php elseif ($current_status === 'preparing') : ?>
                            <button type="button" class="button button-primary mgc-update-status" data-code="<?php echo esc_attr($order->code); ?>" data-status="ready">
                                <?php _e('Mark Ready', 'massnahme-gift-cards'); ?>
                            </button>
                        <?php elseif ($current_status === 'ready') : ?>
                            <button type="button" class="button button-primary mgc-update-status" data-code="<?php echo esc_attr($order->code); ?>" data-status="collected">
                                <?php _e('Mark Collected', 'massnahme-gift-cards'); ?>
                            </button>
                        <?php else : ?>
                            <span class="mgc-completed-label"><?php _e('Completed', 'massnahme-gift-cards'); ?></span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
        <div class="mgc-no-orders">
            <p><?php _e('No pickup orders found.', 'massnahme-gift-cards'); ?></p>
        </div>
    <?php endif; ?>
</div>

<style>
.mgc-pickup-orders {
    max-width: 1400px;
}

/* Status Summary Cards */
.mgc-status-summary {
    display: flex;
    gap: 15px;
    margin: 20px 0;
    flex-wrap: wrap;
}

.mgc-summary-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 30px;
    background: #fff;
    border: 2px solid #ddd;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
    min-width: 120px;
}

.mgc-summary-card:hover {
    border-color: var(--status-color, #2271b1);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.mgc-summary-card.active {
    border-color: var(--status-color, #2271b1);
    background: var(--status-color, #2271b1);
}

.mgc-summary-card.active .mgc-summary-count,
.mgc-summary-card.active .mgc-summary-label {
    color: #fff;
}

.mgc-summary-count {
    font-size: 32px;
    font-weight: 700;
    color: var(--status-color, #2271b1);
    line-height: 1;
}

.mgc-summary-label {
    font-size: 13px;
    color: #666;
    margin-top: 5px;
}

/* Store Filter */
.mgc-store-filter {
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.mgc-store-filter select {
    min-width: 200px;
}

/* Pickup Table */
.mgc-pickup-table {
    margin-top: 20px;
}

.mgc-pickup-table th.column-status,
.mgc-pickup-table td.column-status {
    width: 140px;
}

.mgc-pickup-table th.column-code,
.mgc-pickup-table td.column-code {
    width: 150px;
}

.mgc-pickup-table th.column-amount,
.mgc-pickup-table td.column-amount {
    width: 100px;
}

.mgc-pickup-table th.column-actions,
.mgc-pickup-table td.column-actions {
    width: 150px;
}

/* Status Badges */
.mgc-pickup-status {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.mgc-pickup-status-ordered {
    background: #e9ecef;
    color: #6c757d;
}

.mgc-pickup-status-preparing {
    background: #fff3cd;
    color: #856404;
}

.mgc-pickup-status-ready {
    background: #d4edda;
    color: #155724;
}

.mgc-pickup-status-collected {
    background: #d1ecf1;
    color: #0c5460;
}

.mgc-email {
    color: #666;
    font-size: 13px;
}

.mgc-action-buttons {
    display: flex;
    gap: 5px;
}

.mgc-completed-label {
    color: #17a2b8;
    font-style: italic;
}

/* No Orders */
.mgc-no-orders {
    background: #fff;
    padding: 40px;
    text-align: center;
    border-radius: 8px;
    margin-top: 20px;
}

/* Row Highlighting */
tr.status-ordered {
    background: rgba(108, 117, 125, 0.05) !important;
}

tr.status-preparing {
    background: rgba(253, 126, 20, 0.08) !important;
}

tr.status-ready {
    background: rgba(40, 167, 69, 0.08) !important;
}

/* Responsive */
@media (max-width: 782px) {
    .mgc-status-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
    }

    .mgc-summary-card {
        padding: 15px 10px;
        min-width: auto;
    }

    .mgc-summary-count {
        font-size: 24px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Update status button click
    $('.mgc-update-status').on('click', function() {
        var $btn = $(this);
        var code = $btn.data('code');
        var newStatus = $btn.data('status');
        var $row = $btn.closest('tr');

        $btn.prop('disabled', true).text('<?php _e('Updating...', 'massnahme-gift-cards'); ?>');

        $.ajax({
            url: mgc_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'mgc_update_pickup_status',
                nonce: mgc_admin.nonce,
                code: code,
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    // Reload page to show updated status
                    location.reload();
                } else {
                    alert('<?php _e('Error:', 'massnahme-gift-cards'); ?> ' + response.data);
                    $btn.prop('disabled', false);
                }
            },
            error: function() {
                alert('<?php _e('Connection error. Please try again.', 'massnahme-gift-cards'); ?>');
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
