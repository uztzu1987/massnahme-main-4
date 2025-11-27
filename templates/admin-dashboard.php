<?php
/**
 * Admin Dashboard Template
 * Enhanced version with tabs for Overview, All Gift Cards, and Transaction History
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'mgc_gift_cards';
$usage_table = $wpdb->prefix . 'mgc_gift_card_usage';

// Get statistics
$total_cards = $wpdb->get_var("SELECT COUNT(*) FROM $table");
$active_cards = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'active'");
$total_value = $wpdb->get_var("SELECT SUM(amount) FROM $table") ?: 0;
$remaining_value = $wpdb->get_var("SELECT SUM(balance) FROM $table WHERE status = 'active'") ?: 0;
$total_transactions = $wpdb->get_var("SELECT COUNT(*) FROM $usage_table");

// Get store locations for display
$settings = get_option('mgc_settings', []);
$store_locations = $settings['store_locations'] ?? [];
$delivery_labels = [
    'digital' => __('Digital', 'massnahme-gift-cards'),
    'pickup' => __('Store Pickup', 'massnahme-gift-cards'),
    'shipping' => __('Shipping', 'massnahme-gift-cards'),
    'physical' => __('Physical', 'massnahme-gift-cards')
];

// Get users who have made transactions (for filter dropdown)
$transaction_users = $wpdb->get_results(
    "SELECT DISTINCT u.updated_by, wu.display_name
     FROM $usage_table u
     LEFT JOIN {$wpdb->users} wu ON u.updated_by = wu.ID
     WHERE u.updated_by IS NOT NULL AND u.updated_by > 0
     ORDER BY wu.display_name ASC"
);
?>

<div class="wrap mgc-admin-dashboard">
    <h1><?php _e('Gift Cards Dashboard', 'massnahme-gift-cards'); ?></h1>

    <!-- Tab Navigation -->
    <nav class="nav-tab-wrapper mgc-nav-tabs">
        <a href="#overview" class="nav-tab nav-tab-active" data-tab="overview">
            <?php _e('Overview', 'massnahme-gift-cards'); ?>
        </a>
        <a href="#all-cards" class="nav-tab" data-tab="all-cards">
            <?php _e('All Gift Cards', 'massnahme-gift-cards'); ?>
            <span class="mgc-tab-count"><?php echo number_format($total_cards); ?></span>
        </a>
        <a href="#transactions" class="nav-tab" data-tab="transactions">
            <?php _e('Transaction History', 'massnahme-gift-cards'); ?>
            <span class="mgc-tab-count"><?php echo number_format($total_transactions); ?></span>
        </a>
    </nav>

    <!-- Overview Tab -->
    <div class="mgc-tab-content active" id="tab-overview">
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

        <!-- Recent Gift Cards -->
        <div class="mgc-section">
            <h2><?php _e('Recent Gift Cards', 'massnahme-gift-cards'); ?></h2>
            <?php
            $recent_cards = $wpdb->get_results(
                "SELECT * FROM $table ORDER BY created_at DESC LIMIT 10"
            );
            ?>
            <?php if ($recent_cards): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;"><?php _e('ID', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Code', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Amount', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Balance', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Recipient', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Type', 'massnahme-gift-cards'); ?></th>
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
                                <td><strong>#<?php echo esc_html($card->id); ?></strong></td>
                                <td><code><?php echo esc_html($card->code); ?></code></td>
                                <td><?php echo esc_html(html_entity_decode(strip_tags(wc_price($card->amount)), ENT_QUOTES, 'UTF-8')); ?></td>
                                <td class="mgc-balance-cell"><?php echo esc_html(html_entity_decode(strip_tags(wc_price($card->balance)), ENT_QUOTES, 'UTF-8')); ?></td>
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
                                </td>
                                <td class="mgc-status-cell">
                                    <span class="mgc-status mgc-status-<?php echo esc_attr($card->status); ?>">
                                        <?php echo esc_html(ucfirst($card->status)); ?>
                                    </span>
                                </td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($card->created_at)); ?></td>
                                <td>
                                    <button type="button" class="button button-small mgc-view-history"
                                        data-code="<?php echo esc_attr($card->code); ?>"
                                        data-id="<?php echo esc_attr($card->id); ?>">
                                        <?php _e('Details', 'massnahme-gift-cards'); ?>
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

        <!-- Recent Transactions -->
        <div class="mgc-section">
            <h2><?php _e('Recent Transactions', 'massnahme-gift-cards'); ?></h2>
            <?php
            $recent_transactions = $wpdb->get_results(
                "SELECT t.*, u.display_name as user_name
                 FROM $usage_table t
                 LEFT JOIN {$wpdb->users} u ON t.updated_by = u.ID
                 ORDER BY t.used_at DESC LIMIT 10"
            );
            ?>
            <?php if ($recent_transactions): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;"><?php _e('ID', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Date', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Gift Card', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Type', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Amount', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Balance After', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('User ID', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('User Name', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Order', 'massnahme-gift-cards'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_transactions as $tx): ?>
                            <?php
                            // Determine transaction type
                            $tx_type = 'redemption';
                            $tx_type_label = __('Redemption', 'massnahme-gift-cards');
                            $tx_type_class = 'redemption';
                            if (floatval($tx->amount_used) == 0) {
                                $tx_type = 'creation';
                                $tx_type_label = __('Creation', 'massnahme-gift-cards');
                                $tx_type_class = 'creation';
                            } elseif (floatval($tx->amount_used) < 0) {
                                $tx_type = 'adjustment';
                                $tx_type_label = __('Adjustment', 'massnahme-gift-cards');
                                $tx_type_class = 'adjustment';
                            }
                            ?>
                            <tr>
                                <td><strong>#<?php echo esc_html($tx->id); ?></strong></td>
                                <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($tx->used_at)); ?></td>
                                <td><code><?php echo esc_html($tx->gift_card_code); ?></code></td>
                                <td><span class="mgc-tx-type mgc-tx-type-<?php echo esc_attr($tx_type_class); ?>"><?php echo esc_html($tx_type_label); ?></span></td>
                                <td><?php echo esc_html(html_entity_decode(strip_tags(wc_price($tx->amount_used)), ENT_QUOTES, 'UTF-8')); ?></td>
                                <td><?php echo esc_html(html_entity_decode(strip_tags(wc_price($tx->remaining_balance)), ENT_QUOTES, 'UTF-8')); ?></td>
                                <td>
                                    <?php if (!empty($tx->updated_by)): ?>
                                        <span class="mgc-user-id">#<?php echo esc_html($tx->updated_by); ?></span>
                                    <?php else: ?>
                                        <span class="mgc-system">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($tx->user_name)): ?>
                                        <?php echo esc_html($tx->user_name); ?>
                                    <?php elseif (!empty($tx->updated_by)): ?>
                                        <em><?php _e('Unknown', 'massnahme-gift-cards'); ?></em>
                                    <?php else: ?>
                                        <em><?php _e('System', 'massnahme-gift-cards'); ?></em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($tx->order_id > 0): ?>
                                        <a href="<?php echo admin_url('post.php?post=' . $tx->order_id . '&action=edit'); ?>" class="mgc-order-link">
                                            #<?php echo esc_html($tx->order_id); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="mgc-manual-badge"><?php _e('Manual', 'massnahme-gift-cards'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No transactions found.', 'massnahme-gift-cards'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- All Gift Cards Tab -->
    <div class="mgc-tab-content" id="tab-all-cards" style="display: none;">
        <div class="mgc-section">
            <div class="mgc-section-header">
                <h2><?php _e('All Gift Cards', 'massnahme-gift-cards'); ?></h2>
                <button type="button" class="button" id="mgc-refresh-cards">
                    <span class="dashicons dashicons-update"></span> <?php _e('Refresh', 'massnahme-gift-cards'); ?>
                </button>
            </div>

            <!-- Filters -->
            <div class="mgc-filters">
                <div class="mgc-filter-group">
                    <label for="mgc-search-cards"><?php _e('Search', 'massnahme-gift-cards'); ?></label>
                    <input type="text" id="mgc-search-cards" placeholder="<?php esc_attr_e('Code, recipient, email...', 'massnahme-gift-cards'); ?>" class="regular-text">
                </div>
                <div class="mgc-filter-group">
                    <label for="mgc-filter-status"><?php _e('Status', 'massnahme-gift-cards'); ?></label>
                    <select id="mgc-filter-status">
                        <option value=""><?php _e('All Statuses', 'massnahme-gift-cards'); ?></option>
                        <option value="active"><?php _e('Active', 'massnahme-gift-cards'); ?></option>
                        <option value="used"><?php _e('Used', 'massnahme-gift-cards'); ?></option>
                    </select>
                </div>
                <div class="mgc-filter-group">
                    <label for="mgc-filter-type"><?php _e('Type', 'massnahme-gift-cards'); ?></label>
                    <select id="mgc-filter-type">
                        <option value=""><?php _e('All Types', 'massnahme-gift-cards'); ?></option>
                        <option value="digital"><?php _e('Digital', 'massnahme-gift-cards'); ?></option>
                        <option value="physical"><?php _e('Physical', 'massnahme-gift-cards'); ?></option>
                        <option value="pickup"><?php _e('Pickup', 'massnahme-gift-cards'); ?></option>
                        <option value="shipping"><?php _e('Shipping', 'massnahme-gift-cards'); ?></option>
                    </select>
                </div>
            </div>

            <!-- Cards Table -->
            <div id="mgc-cards-loading" class="mgc-loading"><?php _e('Loading gift cards...', 'massnahme-gift-cards'); ?></div>
            <div id="mgc-cards-table-container" style="display: none;">
                <table class="wp-list-table widefat fixed striped" id="mgc-all-cards-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;"><?php _e('ID', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Code', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Amount', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Balance', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Recipient', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Type', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Status', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Order', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Created', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Actions', 'massnahme-gift-cards'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="mgc-cards-tbody"></tbody>
                </table>
                <div id="mgc-cards-pagination" class="mgc-pagination"></div>
            </div>
            <p id="mgc-no-cards" style="display: none;"><?php _e('No gift cards found.', 'massnahme-gift-cards'); ?></p>
        </div>
    </div>

    <!-- Transaction History Tab -->
    <div class="mgc-tab-content" id="tab-transactions" style="display: none;">
        <div class="mgc-section">
            <div class="mgc-section-header">
                <h2><?php _e('Transaction History', 'massnahme-gift-cards'); ?></h2>
                <button type="button" class="button" id="mgc-refresh-transactions">
                    <span class="dashicons dashicons-update"></span> <?php _e('Refresh', 'massnahme-gift-cards'); ?>
                </button>
            </div>

            <!-- Filters -->
            <div class="mgc-filters">
                <div class="mgc-filter-group">
                    <label for="mgc-search-transactions"><?php _e('Search by Code', 'massnahme-gift-cards'); ?></label>
                    <input type="text" id="mgc-search-transactions" placeholder="<?php esc_attr_e('Gift card code...', 'massnahme-gift-cards'); ?>" class="regular-text">
                </div>
                <div class="mgc-filter-group">
                    <label for="mgc-filter-user"><?php _e('User', 'massnahme-gift-cards'); ?></label>
                    <select id="mgc-filter-user">
                        <option value=""><?php _e('All Users', 'massnahme-gift-cards'); ?></option>
                        <?php foreach ($transaction_users as $user): ?>
                            <option value="<?php echo esc_attr($user->updated_by); ?>">
                                <?php echo esc_html($user->display_name ?: 'User #' . $user->updated_by); ?> (#<?php echo esc_html($user->updated_by); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mgc-filter-group">
                    <label for="mgc-filter-tx-type"><?php _e('Type', 'massnahme-gift-cards'); ?></label>
                    <select id="mgc-filter-tx-type">
                        <option value=""><?php _e('All Types', 'massnahme-gift-cards'); ?></option>
                        <option value="redemption"><?php _e('Redemption', 'massnahme-gift-cards'); ?></option>
                        <option value="creation"><?php _e('Creation', 'massnahme-gift-cards'); ?></option>
                        <option value="adjustment"><?php _e('Adjustment', 'massnahme-gift-cards'); ?></option>
                    </select>
                </div>
            </div>

            <!-- Transactions Table -->
            <div id="mgc-transactions-loading" class="mgc-loading"><?php _e('Loading transactions...', 'massnahme-gift-cards'); ?></div>
            <div id="mgc-transactions-table-container" style="display: none;">
                <table class="wp-list-table widefat fixed striped" id="mgc-all-transactions-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;"><?php _e('ID', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Date', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Gift Card', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Type', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Amount', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Balance After', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('User ID', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('User Name', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Order', 'massnahme-gift-cards'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="mgc-transactions-tbody"></tbody>
                </table>
                <div id="mgc-transactions-pagination" class="mgc-pagination"></div>
            </div>
            <p id="mgc-no-transactions" style="display: none;"><?php _e('No transactions found.', 'massnahme-gift-cards'); ?></p>
        </div>
    </div>
</div>

<!-- Gift Card Detail Modal -->
<div id="mgc-detail-modal" class="mgc-modal" style="display: none;">
    <div class="mgc-modal-content mgc-modal-wide">
        <div class="mgc-modal-header">
            <h3><?php _e('Gift Card Details', 'massnahme-gift-cards'); ?></h3>
            <button type="button" class="mgc-modal-close">&times;</button>
        </div>
        <div class="mgc-modal-body">
            <div class="mgc-detail-grid">
                <div class="mgc-detail-item">
                    <label><?php _e('ID', 'massnahme-gift-cards'); ?></label>
                    <span id="mgc-detail-id"></span>
                </div>
                <div class="mgc-detail-item">
                    <label><?php _e('Code', 'massnahme-gift-cards'); ?></label>
                    <span id="mgc-detail-code" class="mgc-code"></span>
                </div>
                <div class="mgc-detail-item">
                    <label><?php _e('Original Amount', 'massnahme-gift-cards'); ?></label>
                    <span id="mgc-detail-amount"></span>
                </div>
                <div class="mgc-detail-item">
                    <label><?php _e('Current Balance', 'massnahme-gift-cards'); ?></label>
                    <span id="mgc-detail-balance" class="mgc-balance-highlight"></span>
                </div>
                <div class="mgc-detail-item">
                    <label><?php _e('Recipient', 'massnahme-gift-cards'); ?></label>
                    <span id="mgc-detail-recipient"></span>
                </div>
                <div class="mgc-detail-item">
                    <label><?php _e('Email', 'massnahme-gift-cards'); ?></label>
                    <span id="mgc-detail-email"></span>
                </div>
                <div class="mgc-detail-item">
                    <label><?php _e('Status', 'massnahme-gift-cards'); ?></label>
                    <span id="mgc-detail-status"></span>
                </div>
                <div class="mgc-detail-item">
                    <label><?php _e('Type', 'massnahme-gift-cards'); ?></label>
                    <span id="mgc-detail-type"></span>
                </div>
                <div class="mgc-detail-item">
                    <label><?php _e('Created', 'massnahme-gift-cards'); ?></label>
                    <span id="mgc-detail-created"></span>
                </div>
                <div class="mgc-detail-item">
                    <label><?php _e('Expires', 'massnahme-gift-cards'); ?></label>
                    <span id="mgc-detail-expires"></span>
                </div>
            </div>

            <!-- Balance Edit Section -->
            <div class="mgc-balance-edit-section">
                <h4><?php _e('Update Balance', 'massnahme-gift-cards'); ?></h4>
                <div class="mgc-balance-edit-form">
                    <input type="number" id="mgc-new-balance" step="0.01" min="0" placeholder="<?php esc_attr_e('New balance', 'massnahme-gift-cards'); ?>">
                    <button type="button" class="button button-primary" id="mgc-save-balance"><?php _e('Update', 'massnahme-gift-cards'); ?></button>
                </div>
                <p class="description"><?php _e('Manually adjust the gift card balance. This will be logged with your user ID.', 'massnahme-gift-cards'); ?></p>
            </div>

            <!-- Transaction History -->
            <h4><?php _e('Transaction History', 'massnahme-gift-cards'); ?></h4>
            <div id="mgc-detail-history-loading"><?php _e('Loading...', 'massnahme-gift-cards'); ?></div>
            <table id="mgc-detail-history-table" class="wp-list-table widefat striped" style="display: none;">
                <thead>
                    <tr>
                        <th><?php _e('Date', 'massnahme-gift-cards'); ?></th>
                        <th><?php _e('Amount', 'massnahme-gift-cards'); ?></th>
                        <th><?php _e('Balance After', 'massnahme-gift-cards'); ?></th>
                        <th><?php _e('User ID', 'massnahme-gift-cards'); ?></th>
                        <th><?php _e('User Name', 'massnahme-gift-cards'); ?></th>
                        <th><?php _e('Order', 'massnahme-gift-cards'); ?></th>
                    </tr>
                </thead>
                <tbody id="mgc-detail-history-tbody"></tbody>
            </table>
            <p id="mgc-detail-no-history" style="display: none;"><?php _e('No transactions found for this gift card.', 'massnahme-gift-cards'); ?></p>
        </div>
        <div class="mgc-modal-footer">
            <button type="button" class="button mgc-modal-close-btn"><?php _e('Close', 'massnahme-gift-cards'); ?></button>
        </div>
    </div>
</div>

<style>
/* Dashboard Layout */
.mgc-admin-dashboard {
    max-width: 1400px;
}

/* Tab Navigation */
.mgc-nav-tabs {
    margin-bottom: 20px;
}

.mgc-nav-tabs .nav-tab {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.mgc-tab-count {
    background: #2271b1;
    color: #fff;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}

.nav-tab:not(.nav-tab-active) .mgc-tab-count {
    background: #787c82;
}

/* Tab Content */
.mgc-tab-content {
    background: #fff;
    padding: 20px;
    border: 1px solid #c3c4c7;
    border-top: none;
}

/* Statistics Grid */
.mgc-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.mgc-stat-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-left: 4px solid #2271b1;
    padding: 20px;
    text-align: center;
}

.mgc-stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 28px;
    color: #2271b1;
}

.mgc-stat-card p {
    margin: 0;
    color: #646970;
    font-size: 14px;
}

/* Sections */
.mgc-section {
    margin-bottom: 30px;
}

.mgc-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.mgc-section-header h2 {
    margin: 0;
}

/* Filters */
.mgc-filters {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    padding: 15px;
    background: #f6f7f7;
    border-radius: 4px;
    flex-wrap: wrap;
}

.mgc-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.mgc-filter-group label {
    font-weight: 600;
    font-size: 12px;
    color: #50575e;
}

.mgc-filter-group input,
.mgc-filter-group select {
    min-width: 180px;
}

/* Status badges */
.mgc-status {
    padding: 4px 10px;
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

/* Delivery type badges */
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

.mgc-delivery-physical {
    background: #fce4ec;
    color: #c2185b;
}

/* Transaction type badges */
.mgc-tx-type {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.mgc-tx-type-redemption {
    background: #fff3cd;
    color: #856404;
}

.mgc-tx-type-creation {
    background: #d4edda;
    color: #155724;
}

.mgc-tx-type-adjustment {
    background: #d1ecf1;
    color: #0c5460;
}

/* User ID badge */
.mgc-user-id {
    display: inline-block;
    padding: 2px 8px;
    background: #e9ecef;
    border-radius: 3px;
    font-family: monospace;
    font-size: 12px;
    color: #495057;
}

.mgc-system {
    color: #999;
    font-style: italic;
}

/* Order link */
.mgc-order-link {
    color: #2271b1;
    text-decoration: none;
}

.mgc-order-link:hover {
    text-decoration: underline;
}

.mgc-manual-badge {
    background: #fff3cd;
    color: #856404;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
}

.mgc-email {
    color: #666;
    font-size: 12px;
}

/* Loading */
.mgc-loading {
    text-align: center;
    padding: 40px;
    color: #666;
}

/* Pagination */
.mgc-pagination {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 20px;
}

.mgc-page-btn {
    padding: 8px 12px;
    border: 1px solid #c3c4c7;
    background: #fff;
    border-radius: 3px;
    cursor: pointer;
    font-size: 13px;
}

.mgc-page-btn:hover {
    background: #f6f7f7;
}

.mgc-page-btn.active {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}

/* Modal */
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
    max-width: 500px;
    margin: 20px;
    max-height: 90vh;
    overflow-y: auto;
}

.mgc-modal-wide {
    max-width: 800px;
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

.mgc-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 15px 20px;
    border-top: 1px solid #dcdcde;
    background: #f6f7f7;
}

/* Detail Grid */
.mgc-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.mgc-detail-item {
    background: #f6f7f7;
    padding: 12px;
    border-radius: 4px;
}

.mgc-detail-item label {
    display: block;
    font-size: 11px;
    color: #646970;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.mgc-detail-item span {
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
}

.mgc-code {
    font-family: monospace;
    letter-spacing: 1px;
}

.mgc-balance-highlight {
    color: #2271b1;
    font-size: 16px !important;
}

/* Balance Edit Section */
.mgc-balance-edit-section {
    background: #f0f7fc;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 25px;
    border-left: 4px solid #2271b1;
}

.mgc-balance-edit-section h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
}

.mgc-balance-edit-form {
    display: flex;
    gap: 10px;
    margin-bottom: 8px;
}

.mgc-balance-edit-form input {
    width: 150px;
}

/* Table improvements */
table code {
    background: #f0f0f1;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
}
</style>

<script>
(function($) {
    'use strict';

    var ajaxUrl = mgc_admin.ajax_url;
    var nonce = mgc_admin.nonce;
    var adminUrl = mgc_admin.admin_url;
    var currentCardCode = '';
    var currentCardAmount = 0;
    var cardsPage = 1;
    var transactionsPage = 1;
    var searchCardsTimeout = null;
    var searchTransactionsTimeout = null;

    // Tab switching
    $('.mgc-nav-tabs .nav-tab').on('click', function(e) {
        e.preventDefault();
        var tab = $(this).data('tab');

        // Update nav tabs
        $('.mgc-nav-tabs .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        // Update content
        $('.mgc-tab-content').hide();
        $('#tab-' + tab).show();

        // Load data when switching tabs
        if (tab === 'all-cards') {
            loadCards(1);
        } else if (tab === 'transactions') {
            loadTransactions(1);
        }
    });

    // Load All Gift Cards
    function loadCards(page) {
        cardsPage = page;
        $('#mgc-cards-loading').show();
        $('#mgc-cards-table-container').hide();
        $('#mgc-no-cards').hide();

        $.post(ajaxUrl, {
            action: 'mgc_admin_list_cards',
            nonce: nonce,
            page: page,
            search: $('#mgc-search-cards').val(),
            status: $('#mgc-filter-status').val(),
            delivery_method: $('#mgc-filter-type').val()
        }, function(response) {
            $('#mgc-cards-loading').hide();

            if (response.success && response.data.cards.length > 0) {
                var $tbody = $('#mgc-cards-tbody');
                $tbody.empty();

                $.each(response.data.cards, function(i, card) {
                    var typeClass = getTypeClass(card.delivery_method);
                    var typeLabel = getTypeLabel(card.delivery_method);
                    var recipient = card.recipient_name || card.recipient_email || '-';

                    $tbody.append(
                        '<tr>' +
                        '<td><strong>#' + card.id + '</strong></td>' +
                        '<td><code>' + card.code + '</code></td>' +
                        '<td>' + card.formatted_amount + '</td>' +
                        '<td>' + card.formatted_balance + '</td>' +
                        '<td>' + escapeHtml(recipient) + '</td>' +
                        '<td><span class="mgc-delivery mgc-delivery-' + typeClass + '">' + typeLabel + '</span></td>' +
                        '<td><span class="mgc-status mgc-status-' + card.status + '">' + capitalize(card.status) + '</span></td>' +
                        '<td>' + (card.order_id > 0 ? '<a href="' + adminUrl + 'post.php?post=' + card.order_id + '&action=edit" class="mgc-order-link">#' + card.order_id + '</a>' : '<span class="mgc-manual-badge">Manual</span>') + '</td>' +
                        '<td>' + card.created_at + '</td>' +
                        '<td><button class="button button-small mgc-view-history" data-code="' + card.code + '" data-id="' + card.id + '">Details</button></td>' +
                        '</tr>'
                    );
                });

                // Pagination
                renderPagination('#mgc-cards-pagination', response.data.total_pages, page, function(p) {
                    loadCards(p);
                });

                $('#mgc-cards-table-container').show();
            } else {
                $('#mgc-no-cards').show();
            }
        });
    }

    // Load All Transactions
    function loadTransactions(page) {
        transactionsPage = page;
        $('#mgc-transactions-loading').show();
        $('#mgc-transactions-table-container').hide();
        $('#mgc-no-transactions').hide();

        $.post(ajaxUrl, {
            action: 'mgc_admin_list_transactions',
            nonce: nonce,
            page: page,
            search: $('#mgc-search-transactions').val(),
            user_id: $('#mgc-filter-user').val(),
            tx_type: $('#mgc-filter-tx-type').val()
        }, function(response) {
            $('#mgc-transactions-loading').hide();

            if (response.success && response.data.transactions.length > 0) {
                var $tbody = $('#mgc-transactions-tbody');
                $tbody.empty();

                $.each(response.data.transactions, function(i, tx) {
                    var txTypeClass = tx.type;
                    var txTypeLabel = getTransactionTypeLabel(tx.type);
                    var userIdDisplay = tx.updated_by ? '<span class="mgc-user-id">#' + tx.updated_by + '</span>' : '<span class="mgc-system">-</span>';
                    var userNameDisplay = tx.updated_by_name || (tx.updated_by ? 'Unknown' : 'System');
                    var orderDisplay = tx.order_id > 0 ? '<a href="' + adminUrl + 'post.php?post=' + tx.order_id + '&action=edit" class="mgc-order-link">#' + tx.order_id + '</a>' : '<span class="mgc-manual-badge">Manual</span>';

                    $tbody.append(
                        '<tr>' +
                        '<td><strong>#' + tx.id + '</strong></td>' +
                        '<td>' + tx.date + '</td>' +
                        '<td><code>' + tx.gift_card_code + '</code></td>' +
                        '<td><span class="mgc-tx-type mgc-tx-type-' + txTypeClass + '">' + txTypeLabel + '</span></td>' +
                        '<td>' + tx.formatted_amount + '</td>' +
                        '<td>' + tx.formatted_balance + '</td>' +
                        '<td>' + userIdDisplay + '</td>' +
                        '<td>' + escapeHtml(userNameDisplay) + '</td>' +
                        '<td>' + orderDisplay + '</td>' +
                        '</tr>'
                    );
                });

                // Pagination
                renderPagination('#mgc-transactions-pagination', response.data.total_pages, page, function(p) {
                    loadTransactions(p);
                });

                $('#mgc-transactions-table-container').show();
            } else {
                $('#mgc-no-transactions').show();
            }
        });
    }

    function renderPagination(container, totalPages, currentPage, callback) {
        var $pagination = $(container);
        $pagination.empty();

        if (totalPages <= 1) return;

        for (var i = 1; i <= totalPages; i++) {
            var $btn = $('<button class="mgc-page-btn' + (i === currentPage ? ' active' : '') + '">' + i + '</button>');
            $btn.data('page', i);
            $btn.on('click', function() {
                callback($(this).data('page'));
            });
            $pagination.append($btn);
        }
    }

    function getTypeClass(type) {
        return type || 'digital';
    }

    function getTypeLabel(type) {
        var labels = {
            'digital': 'Digital',
            'physical': 'Physical',
            'pickup': 'Pickup',
            'shipping': 'Shipping'
        };
        return labels[type] || type || 'Digital';
    }

    function getTransactionTypeLabel(type) {
        var labels = {
            'redemption': 'Redemption',
            'creation': 'Creation',
            'adjustment': 'Adjustment'
        };
        return labels[type] || type;
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Search with debounce - Cards
    $('#mgc-search-cards').on('input', function() {
        clearTimeout(searchCardsTimeout);
        searchCardsTimeout = setTimeout(function() {
            loadCards(1);
        }, 300);
    });

    // Filter changes - Cards
    $('#mgc-filter-status, #mgc-filter-type').on('change', function() {
        loadCards(1);
    });

    // Search with debounce - Transactions
    $('#mgc-search-transactions').on('input', function() {
        clearTimeout(searchTransactionsTimeout);
        searchTransactionsTimeout = setTimeout(function() {
            loadTransactions(1);
        }, 300);
    });

    // Filter changes - Transactions
    $('#mgc-filter-user, #mgc-filter-tx-type').on('change', function() {
        loadTransactions(1);
    });

    // Refresh buttons
    $('#mgc-refresh-cards').on('click', function() {
        loadCards(cardsPage);
    });

    $('#mgc-refresh-transactions').on('click', function() {
        loadTransactions(transactionsPage);
    });

    // View card details
    $(document).on('click', '.mgc-view-history', function() {
        var code = $(this).data('code');
        showCardDetail(code);
    });

    function showCardDetail(code) {
        currentCardCode = code;
        $('#mgc-detail-history-loading').show();
        $('#mgc-detail-history-table').hide();
        $('#mgc-detail-no-history').hide();
        $('#mgc-detail-modal').show();

        $.post(ajaxUrl, {
            action: 'mgc_staff_lookup',
            nonce: nonce,
            code: code
        }, function(response) {
            $('#mgc-detail-history-loading').hide();

            if (response.success) {
                var data = response.data;
                currentCardAmount = data.amount;

                $('#mgc-detail-id').text('#' + (data.id || '-'));
                $('#mgc-detail-code').text(data.code);
                $('#mgc-detail-amount').text(formatCurrency(data.amount));
                $('#mgc-detail-balance').text(formatCurrency(data.balance));
                $('#mgc-detail-recipient').text(data.recipient_name || '-');
                $('#mgc-detail-email').text(data.recipient_email || '-');
                $('#mgc-detail-status').html('<span class="mgc-status mgc-status-' + data.status + '">' + capitalize(data.status) + '</span>');
                $('#mgc-detail-type').html('<span class="mgc-delivery mgc-delivery-' + data.delivery_method + '">' + getTypeLabel(data.delivery_method) + '</span>');
                $('#mgc-detail-created').text(data.created_at);
                $('#mgc-detail-expires').text(data.expires_at);

                // Set current balance in input
                $('#mgc-new-balance').val(data.balance).attr('max', data.amount);

                if (data.history && data.history.length > 0) {
                    var $tbody = $('#mgc-detail-history-tbody');
                    $tbody.empty();

                    $.each(data.history, function(i, item) {
                        var userIdDisplay = item.updated_by ? '<span class="mgc-user-id">#' + item.updated_by + '</span>' : '-';
                        var userNameDisplay = item.updated_by_name || (item.updated_by ? 'Unknown' : 'System');
                        var orderDisplay = item.order_id > 0 ? '<a href="' + adminUrl + 'post.php?post=' + item.order_id + '&action=edit" class="mgc-order-link">#' + item.order_id + '</a>' : '<span class="mgc-manual-badge">Manual</span>';

                        $tbody.append(
                            '<tr>' +
                            '<td>' + item.date + '</td>' +
                            '<td>' + formatCurrency(item.amount) + '</td>' +
                            '<td>' + formatCurrency(item.remaining) + '</td>' +
                            '<td>' + userIdDisplay + '</td>' +
                            '<td>' + escapeHtml(userNameDisplay) + '</td>' +
                            '<td>' + orderDisplay + '</td>' +
                            '</tr>'
                        );
                    });

                    $('#mgc-detail-history-table').show();
                } else {
                    $('#mgc-detail-no-history').show();
                }
            }
        });
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: mgc_admin.currency || 'EUR'
        }).format(parseFloat(amount));
    }

    // Save balance
    $('#mgc-save-balance').on('click', function() {
        var newBalance = parseFloat($('#mgc-new-balance').val());

        if (isNaN(newBalance) || newBalance < 0) {
            alert('Please enter a valid balance');
            return;
        }

        if (newBalance > currentCardAmount) {
            alert('Balance cannot exceed original amount');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');

        $.post(ajaxUrl, {
            action: 'mgc_update_balance',
            nonce: nonce,
            code: currentCardCode,
            balance: newBalance
        }, function(response) {
            if (response.success) {
                // Refresh the detail view
                showCardDetail(currentCardCode);
                // Refresh the table if visible
                if ($('#tab-all-cards').is(':visible')) {
                    loadCards(cardsPage);
                }
            } else {
                alert(response.data || 'Error updating balance');
            }
        }).always(function() {
            $btn.prop('disabled', false).text('Update');
        });
    });

    // Close modals
    $(document).on('click', '.mgc-modal-close, .mgc-modal-close-btn', function() {
        $(this).closest('.mgc-modal').hide();
    });

    $('.mgc-modal').on('click', function(e) {
        if ($(e.target).is('.mgc-modal')) {
            $(this).hide();
        }
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.mgc-modal').hide();
        }
    });

})(jQuery);
</script>
