<?php
/**
 * Admin Dashboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'mgc_gift_cards';

// Get statistics
$total_cards = $wpdb->get_var("SELECT COUNT(*) FROM $table");
$active_cards = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'active'");
$total_value = $wpdb->get_var("SELECT SUM(amount) FROM $table") ?: 0;
$remaining_value = $wpdb->get_var("SELECT SUM(balance) FROM $table WHERE status = 'active'") ?: 0;

// Get recent gift cards
$recent_cards = $wpdb->get_results(
    "SELECT * FROM $table ORDER BY created_at DESC LIMIT 20"
);
?>

<div class="wrap">
    <h1><?php _e('Gift Cards Dashboard', 'massnahme-gift-cards'); ?></h1>

    <div class="mgc-dashboard">
        <!-- Statistics Cards -->
        <div class="mgc-stats-grid">
            <div class="mgc-stat-card">
                <h3><?php echo number_format($total_cards); ?></h3>
                <p><?php _e('Total Gift Cards', 'massnahme-gift-cards'); ?></p>
            </div>

            <div class="mgc-stat-card">
                <h3><?php echo number_format($active_cards); ?></h3>
                <p><?php _e('Active Cards', 'massnahme-gift-cards'); ?></p>
            </div>

            <div class="mgc-stat-card">
                <h3><?php echo wc_price($total_value); ?></h3>
                <p><?php _e('Total Value Sold', 'massnahme-gift-cards'); ?></p>
            </div>

            <div class="mgc-stat-card">
                <h3><?php echo wc_price($remaining_value); ?></h3>
                <p><?php _e('Outstanding Balance', 'massnahme-gift-cards'); ?></p>
            </div>
        </div>

        <!-- Recent Gift Cards Table -->
        <div class="mgc-recent-cards">
            <h2><?php _e('Recent Gift Cards', 'massnahme-gift-cards'); ?></h2>

            <?php if ($recent_cards): ?>
                <?php
                $settings = get_option('mgc_settings', []);
                $store_locations = $settings['store_locations'] ?? [];
                $delivery_labels = [
                    'digital' => __('Digital', 'massnahme-gift-cards'),
                    'pickup' => __('Store Pickup', 'massnahme-gift-cards'),
                    'shipping' => __('Shipping', 'massnahme-gift-cards')
                ];
                ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Code', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Amount', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Balance', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Recipient', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Delivery', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Status', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Created', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Actions', 'massnahme-gift-cards'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_cards as $card): ?>
                            <?php
                            $delivery_method = $card->delivery_method ?? 'digital';
                            $pickup_location = '';
                            if ($delivery_method === 'pickup' && isset($card->pickup_location) && isset($store_locations[$card->pickup_location])) {
                                $pickup_location = $store_locations[$card->pickup_location]['name'] ?? '';
                            }
                            ?>
                            <tr data-code="<?php echo esc_attr($card->code); ?>">
                                <td><strong><?php echo esc_html($card->code); ?></strong></td>
                                <td><?php echo wc_price($card->amount); ?></td>
                                <td class="mgc-balance-cell"><?php echo wc_price($card->balance); ?></td>
                                <td>
                                    <?php if (!empty($card->recipient_name)) : ?>
                                        <strong><?php echo esc_html($card->recipient_name); ?></strong><br>
                                    <?php endif; ?>
                                    <span class="mgc-email"><?php echo esc_html($card->recipient_email); ?></span>
                                </td>
                                <td>
                                    <span class="mgc-delivery mgc-delivery-<?php echo esc_attr($delivery_method); ?>">
                                        <?php echo esc_html($delivery_labels[$delivery_method] ?? ucfirst($delivery_method)); ?>
                                    </span>
                                    <?php if ($pickup_location) : ?>
                                        <br><small class="mgc-pickup-location"><?php echo esc_html($pickup_location); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="mgc-status-cell">
                                    <span class="mgc-status mgc-status-<?php echo esc_attr($card->status); ?>">
                                        <?php echo esc_html(ucfirst($card->status)); ?>
                                    </span>
                                </td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($card->created_at)); ?></td>
                                <td>
                                    <button type="button" class="button button-small mgc-edit-balance"
                                        data-code="<?php echo esc_attr($card->code); ?>"
                                        data-balance="<?php echo esc_attr($card->balance); ?>"
                                        data-amount="<?php echo esc_attr($card->amount); ?>">
                                        <?php _e('Edit Balance', 'massnahme-gift-cards'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No gift cards found.', 'massnahme-gift-cards'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Balance Modal -->
<div id="mgc-edit-balance-modal" class="mgc-modal" style="display: none;">
    <div class="mgc-modal-content">
        <div class="mgc-modal-header">
            <h3><?php _e('Edit Gift Card Balance', 'massnahme-gift-cards'); ?></h3>
            <button type="button" class="mgc-modal-close">&times;</button>
        </div>
        <div class="mgc-modal-body">
            <p class="mgc-modal-info">
                <?php _e('Gift Card:', 'massnahme-gift-cards'); ?> <strong id="mgc-modal-code"></strong>
            </p>
            <p class="mgc-modal-info">
                <?php _e('Original Amount:', 'massnahme-gift-cards'); ?> <span id="mgc-modal-amount"></span>
            </p>
            <div class="mgc-form-group">
                <label for="mgc-new-balance"><?php _e('New Balance', 'massnahme-gift-cards'); ?></label>
                <input type="number" id="mgc-new-balance" name="new_balance" step="0.01" min="0" class="regular-text">
                <p class="description"><?php _e('Enter the remaining balance on this gift card', 'massnahme-gift-cards'); ?></p>
            </div>
        </div>
        <div class="mgc-modal-footer">
            <button type="button" class="button mgc-modal-cancel"><?php _e('Cancel', 'massnahme-gift-cards'); ?></button>
            <button type="button" class="button button-primary mgc-modal-save"><?php _e('Update Balance', 'massnahme-gift-cards'); ?></button>
        </div>
    </div>
</div>

<style>
.mgc-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.mgc-stat-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
}

.mgc-stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 32px;
    color: #2271b1;
}

.mgc-stat-card p {
    margin: 0;
    color: #646970;
}

.mgc-recent-cards {
    margin-top: 30px;
}

.mgc-status {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.mgc-status-active {
    background: #d4edda;
    color: #155724;
}

.mgc-status-used {
    background: #f8d7da;
    color: #721c24;
}

.mgc-status-expired {
    background: #fff3cd;
    color: #856404;
}

/* Delivery method badges */
.mgc-delivery {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.mgc-delivery-digital {
    background: #e3f2fd;
    color: #1565c0;
}

.mgc-delivery-pickup {
    background: #e8f5e9;
    color: #2e7d32;
}

.mgc-delivery-shipping {
    background: #fff3e0;
    color: #e65100;
}

.mgc-pickup-location {
    color: #666;
    font-style: italic;
}

.mgc-email {
    color: #666;
    font-size: 12px;
}

/* Modal Styles */
.mgc-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mgc-modal-content {
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    width: 100%;
    max-width: 450px;
    margin: 20px;
}

.mgc-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #dcdcde;
}

.mgc-modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.mgc-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #646970;
    padding: 0;
    line-height: 1;
}

.mgc-modal-close:hover {
    color: #1d2327;
}

.mgc-modal-body {
    padding: 20px;
}

.mgc-modal-info {
    margin: 0 0 15px 0;
    color: #50575e;
}

.mgc-form-group {
    margin-bottom: 15px;
}

.mgc-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.mgc-form-group input {
    width: 100%;
}

.mgc-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 15px 20px;
    border-top: 1px solid #dcdcde;
    background: #f6f7f7;
}

.mgc-edit-balance {
    white-space: nowrap;
}
</style>
