<?php
/**
 * Frontend Admin Dashboard Template
 * Shortcode: [massnahme_admin_dashboard]
 * Allows shop admins to view, create, and manage gift cards
 */

defined('ABSPATH') || exit;

$currency_symbol = get_woocommerce_currency_symbol();

// Get statistics
global $wpdb;
$table = $wpdb->prefix . 'mgc_gift_cards';
$total_cards = $wpdb->get_var("SELECT COUNT(*) FROM $table");
$active_cards = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'active'");
$total_value = $wpdb->get_var("SELECT SUM(amount) FROM $table") ?: 0;
$remaining_value = $wpdb->get_var("SELECT SUM(balance) FROM $table WHERE status = 'active'") ?: 0;
?>

<div class="mgc-frontend-dashboard">
    <!-- Header -->
    <div class="mgc-fd-header">
        <h2><?php _e('Gift Card Management', 'massnahme-gift-cards'); ?></h2>
        <span class="mgc-fd-user"><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
    </div>

    <!-- COMMENTED OUT: Tab Navigation - Simplified to show only Redemption -->
    <!--
    <div class="mgc-fd-tabs">
        <button class="mgc-fd-tab active" data-tab="overview"><?php _e('Overview', 'massnahme-gift-cards'); ?></button>
        <button class="mgc-fd-tab" data-tab="all-cards"><?php _e('All Gift Cards', 'massnahme-gift-cards'); ?></button>
        <button class="mgc-fd-tab" data-tab="transactions"><?php _e('Transaction History', 'massnahme-gift-cards'); ?></button>
        <button class="mgc-fd-tab" data-tab="create"><?php _e('Create Gift Card', 'massnahme-gift-cards'); ?></button>
    </div>
    -->

    <!-- COMMENTED OUT: Overview Tab -->
    <!--
    <div class="mgc-fd-content mgc-fd-tab-content active" id="mgc-tab-overview">
        <div class="mgc-fd-stats">
            <div class="mgc-fd-stat-card">
                <span class="mgc-fd-stat-value"><?php echo number_format($total_cards); ?></span>
                <span class="mgc-fd-stat-label"><?php _e('Total Cards', 'massnahme-gift-cards'); ?></span>
            </div>
            <div class="mgc-fd-stat-card">
                <span class="mgc-fd-stat-value"><?php echo number_format($active_cards); ?></span>
                <span class="mgc-fd-stat-label"><?php _e('Active Cards', 'massnahme-gift-cards'); ?></span>
            </div>
            <div class="mgc-fd-stat-card">
                <span class="mgc-fd-stat-value"><?php echo esc_html(html_entity_decode(strip_tags(wc_price($total_value)), ENT_QUOTES, 'UTF-8')); ?></span>
                <span class="mgc-fd-stat-label"><?php _e('Total Value', 'massnahme-gift-cards'); ?></span>
            </div>
            <div class="mgc-fd-stat-card">
                <span class="mgc-fd-stat-value"><?php echo esc_html(html_entity_decode(strip_tags(wc_price($remaining_value)), ENT_QUOTES, 'UTF-8')); ?></span>
                <span class="mgc-fd-stat-label"><?php _e('Outstanding', 'massnahme-gift-cards'); ?></span>
            </div>
        </div>

        <div class="mgc-fd-quick-actions">
            <h3><?php _e('Quick Actions', 'massnahme-gift-cards'); ?></h3>
            <button class="mgc-fd-btn mgc-fd-btn-primary" data-goto="create">
                <?php _e('Create New Gift Card', 'massnahme-gift-cards'); ?>
            </button>
            <button class="mgc-fd-btn mgc-fd-btn-secondary" data-goto="all-cards">
                <?php _e('View All Cards', 'massnahme-gift-cards'); ?>
            </button>
        </div>
    </div>
    -->

    <!-- COMMENTED OUT: All Cards Tab -->
    <!--
    <div class="mgc-fd-content mgc-fd-tab-content" id="mgc-tab-all-cards">
        <div class="mgc-fd-cards-header">
            <h3><?php _e('All Gift Cards', 'massnahme-gift-cards'); ?></h3>
            <button class="mgc-fd-btn mgc-fd-btn-primary mgc-fd-btn-sm" id="mgc-fd-refresh">
                <?php _e('Refresh', 'massnahme-gift-cards'); ?>
            </button>
        </div>

        <div class="mgc-fd-filters">
            <div class="mgc-fd-search-wrap">
                <input type="text" id="mgc-fd-search" placeholder="<?php esc_attr_e('Search by code, recipient...', 'massnahme-gift-cards'); ?>" class="mgc-fd-search-input">
            </div>
            <div class="mgc-fd-filter-wrap">
                <select id="mgc-fd-filter-status" class="mgc-fd-filter-select">
                    <option value=""><?php _e('All Statuses', 'massnahme-gift-cards'); ?></option>
                    <option value="active"><?php _e('Active', 'massnahme-gift-cards'); ?></option>
                    <option value="used"><?php _e('Used', 'massnahme-gift-cards'); ?></option>
                </select>
                <select id="mgc-fd-filter-type" class="mgc-fd-filter-select">
                    <option value=""><?php _e('All Types', 'massnahme-gift-cards'); ?></option>
                    <option value="digital"><?php _e('Digital', 'massnahme-gift-cards'); ?></option>
                    <option value="physical"><?php _e('Physical', 'massnahme-gift-cards'); ?></option>
                    <option value="pickup"><?php _e('Pickup', 'massnahme-gift-cards'); ?></option>
                    <option value="shipping"><?php _e('Shipping', 'massnahme-gift-cards'); ?></option>
                </select>
            </div>
        </div>

        <div id="mgc-fd-cards-loading" class="mgc-fd-loading">
            <?php _e('Loading gift cards...', 'massnahme-gift-cards'); ?>
        </div>

        <div id="mgc-fd-cards-table-wrap" style="display: none;">
            <table class="mgc-fd-table" id="mgc-fd-cards-table">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'massnahme-gift-cards'); ?></th>
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
                <tbody id="mgc-fd-cards-tbody"></tbody>
            </table>

            <div id="mgc-fd-pagination" class="mgc-fd-pagination"></div>
        </div>

        <p id="mgc-fd-no-cards" style="display: none;"><?php _e('No gift cards found.', 'massnahme-gift-cards'); ?></p>
    </div>
    -->

    <!-- ============================================ -->
    <!-- GIFT CARD REDEMPTION - Main Active Section -->
    <!-- ============================================ -->

    <!-- Code Entry Section -->
    <div class="mgc-fd-redemption-section">
        <div class="mgc-fd-code-entry">
            <div class="mgc-fd-input-group">
                <input type="text"
                       id="mgc-redemption-code"
                       class="mgc-fd-code-input"
                       placeholder="<?php esc_attr_e('Enter gift card code...', 'massnahme-gift-cards'); ?>"
                       autocomplete="off">
                <button type="button" id="mgc-redemption-lookup" class="mgc-fd-btn mgc-fd-btn-primary">
                    <?php _e('Look Up', 'massnahme-gift-cards'); ?>
                </button>
            </div>
        </div>

        <!-- Card Display (hidden until lookup) -->
        <div id="mgc-redemption-card-display" style="display: none;">
            <!-- Status Banner -->
            <div id="mgc-redemption-status-banner" class="mgc-fd-status-banner">
                <span class="mgc-fd-status-icon"></span>
                <span class="mgc-fd-status-text"></span>
            </div>

            <!-- Balance Display -->
            <div class="mgc-fd-balance-box">
                <span class="mgc-fd-balance-label"><?php _e('Available Balance', 'massnahme-gift-cards'); ?></span>
                <span id="mgc-redemption-balance" class="mgc-fd-balance-value"></span>
            </div>

            <!-- Card Info Grid -->
            <div class="mgc-fd-card-info-grid">
                <div class="mgc-fd-info-item">
                    <span class="mgc-fd-info-label"><?php _e('Code', 'massnahme-gift-cards'); ?></span>
                    <span id="mgc-redemption-card-code" class="mgc-fd-info-value mgc-fd-code"></span>
                </div>
                <div class="mgc-fd-info-item">
                    <span class="mgc-fd-info-label"><?php _e('Original Amount', 'massnahme-gift-cards'); ?></span>
                    <span id="mgc-redemption-original" class="mgc-fd-info-value"></span>
                </div>
                <div class="mgc-fd-info-item">
                    <span class="mgc-fd-info-label"><?php _e('Recipient', 'massnahme-gift-cards'); ?></span>
                    <span id="mgc-redemption-recipient" class="mgc-fd-info-value"></span>
                </div>
                <div class="mgc-fd-info-item">
                    <span class="mgc-fd-info-label"><?php _e('Expires', 'massnahme-gift-cards'); ?></span>
                    <span id="mgc-redemption-expires" class="mgc-fd-info-value"></span>
                </div>
            </div>

            <!-- Redemption Amount Section -->
            <div id="mgc-redemption-amount-section" class="mgc-fd-redemption-amount">
                <h4><?php _e('Redeem Amount', 'massnahme-gift-cards'); ?></h4>

                <div class="mgc-fd-amount-input-wrap">
                    <span class="mgc-fd-currency-symbol"><?php echo esc_html($currency_symbol); ?></span>
                    <input type="number"
                           id="mgc-redemption-amount"
                           class="mgc-fd-amount-input-field"
                           step="0.01"
                           min="0.01"
                           placeholder="0.00">
                </div>

                <div class="mgc-fd-quick-amounts-grid">
                    <button type="button" class="mgc-fd-quick-amt" data-amount="10"><?php echo esc_html($currency_symbol); ?>10</button>
                    <button type="button" class="mgc-fd-quick-amt" data-amount="25"><?php echo esc_html($currency_symbol); ?>25</button>
                    <button type="button" class="mgc-fd-quick-amt" data-amount="50"><?php echo esc_html($currency_symbol); ?>50</button>
                    <button type="button" class="mgc-fd-quick-amt" data-amount="100"><?php echo esc_html($currency_symbol); ?>100</button>
                    <button type="button" class="mgc-fd-quick-amt mgc-fd-quick-full" data-amount="full"><?php _e('FULL', 'massnahme-gift-cards'); ?></button>
                </div>

                <!-- Preview -->
                <div id="mgc-redemption-preview" class="mgc-fd-preview" style="display: none;">
                    <div class="mgc-fd-preview-row">
                        <span><?php _e('Current:', 'massnahme-gift-cards'); ?></span>
                        <span id="mgc-preview-current"></span>
                    </div>
                    <div class="mgc-fd-preview-row mgc-fd-preview-deduct">
                        <span><?php _e('Redeem:', 'massnahme-gift-cards'); ?></span>
                        <span id="mgc-preview-deduct"></span>
                    </div>
                    <div class="mgc-fd-preview-row mgc-fd-preview-remaining">
                        <span><?php _e('Remaining:', 'massnahme-gift-cards'); ?></span>
                        <span id="mgc-preview-remaining"></span>
                    </div>
                </div>

                <button type="button" id="mgc-redemption-confirm" class="mgc-fd-btn mgc-fd-btn-success mgc-fd-btn-large" disabled>
                    <?php _e('Confirm Redemption', 'massnahme-gift-cards'); ?>
                </button>
            </div>

            <!-- Transaction History for this card -->
            <div class="mgc-fd-card-transactions">
                <h4><?php _e('Transaction History', 'massnahme-gift-cards'); ?></h4>
                <div id="mgc-redemption-history-loading" class="mgc-fd-loading"><?php _e('Loading...', 'massnahme-gift-cards'); ?></div>
                <table class="mgc-fd-table mgc-fd-history-table" id="mgc-redemption-history-table" style="display: none;">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Type', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Amount', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Remaining', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('User ID', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('User Name', 'massnahme-gift-cards'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="mgc-redemption-history-tbody"></tbody>
                </table>
                <p id="mgc-redemption-no-history" style="display: none;"><?php _e('No transactions yet.', 'massnahme-gift-cards'); ?></p>
            </div>

            <!-- Clear Button -->
            <button type="button" id="mgc-redemption-clear" class="mgc-fd-btn mgc-fd-btn-secondary mgc-fd-btn-large">
                <?php _e('Clear / New Lookup', 'massnahme-gift-cards'); ?>
            </button>
        </div>

        <!-- Error Display -->
        <div id="mgc-redemption-error" class="mgc-fd-error-display" style="display: none;">
            <div class="mgc-fd-error-icon">!</div>
            <div id="mgc-redemption-error-message" class="mgc-fd-error-text"></div>
            <button type="button" id="mgc-redemption-retry" class="mgc-fd-btn mgc-fd-btn-secondary">
                <?php _e('Try Again', 'massnahme-gift-cards'); ?>
            </button>
        </div>
    </div>

    <!-- Success Overlay -->
    <div id="mgc-redemption-success" class="mgc-fd-success-overlay" style="display: none;">
        <div class="mgc-fd-success-content">
            <div class="mgc-fd-success-icon">✓</div>
            <div class="mgc-fd-success-title"><?php _e('Redemption Complete!', 'massnahme-gift-cards'); ?></div>
            <div id="mgc-redemption-success-amount" class="mgc-fd-success-amount"></div>
            <div id="mgc-redemption-success-remaining" class="mgc-fd-success-remaining"></div>
            <button type="button" id="mgc-redemption-success-close" class="mgc-fd-btn mgc-fd-btn-primary">
                <?php _e('Done', 'massnahme-gift-cards'); ?>
            </button>
        </div>
    </div>

    <!-- COMMENTED OUT: Transaction History Tab (standalone - now integrated above) -->
    <!--
    <div class="mgc-fd-content mgc-fd-tab-content" id="mgc-tab-transactions">
        <div class="mgc-fd-cards-header">
            <h3><?php _e('Transaction History', 'massnahme-gift-cards'); ?></h3>
            <button class="mgc-fd-btn mgc-fd-btn-primary mgc-fd-btn-sm" id="mgc-fd-refresh-transactions">
                <?php _e('Refresh', 'massnahme-gift-cards'); ?>
            </button>
        </div>

        <div class="mgc-fd-filters">
            <div class="mgc-fd-search-wrap">
                <input type="text" id="mgc-fd-transaction-search" placeholder="<?php esc_attr_e('Search by gift card code...', 'massnahme-gift-cards'); ?>" class="mgc-fd-search-input">
            </div>
            <div class="mgc-fd-filter-wrap">
                <select id="mgc-fd-filter-user" class="mgc-fd-filter-select">
                    <option value=""><?php _e('All Users', 'massnahme-gift-cards'); ?></option>
                </select>
            </div>
        </div>

        <div id="mgc-fd-transactions-loading" class="mgc-fd-loading">
            <?php _e('Loading transactions...', 'massnahme-gift-cards'); ?>
        </div>

        <div id="mgc-fd-transactions-table-wrap" style="display: none;">
            <table class="mgc-fd-table" id="mgc-fd-transactions-table">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'massnahme-gift-cards'); ?></th>
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
                <tbody id="mgc-fd-transactions-tbody"></tbody>
            </table>

            <div id="mgc-fd-transactions-pagination" class="mgc-fd-pagination"></div>
        </div>

        <p id="mgc-fd-no-transactions" style="display: none;"><?php _e('No transactions found.', 'massnahme-gift-cards'); ?></p>
    </div>
    -->

    <!-- COMMENTED OUT: Create Card Tab -->
    <!--
    <div class="mgc-fd-content mgc-fd-tab-content" id="mgc-tab-create">
        <div class="mgc-fd-create-form">
            <h3><?php _e('Create New Gift Card', 'massnahme-gift-cards'); ?></h3>

            <div class="mgc-fd-form-notice" id="mgc-fd-create-notice" style="display: none;"></div>

            <form id="mgc-fd-create-form">
                <!-- Card Type Toggle -->
                <div class="mgc-fd-card-type-toggle">
                    <label class="mgc-fd-toggle-option">
                        <input type="radio" name="card_type" value="digital" checked>
                        <span class="mgc-fd-toggle-label">
                            <span class="mgc-fd-toggle-icon">&#128231;</span>
                            <?php _e('Digital Card', 'massnahme-gift-cards'); ?>
                        </span>
                    </label>
                    <label class="mgc-fd-toggle-option">
                        <input type="radio" name="card_type" value="physical">
                        <span class="mgc-fd-toggle-label">
                            <span class="mgc-fd-toggle-icon">&#127873;</span>
                            <?php _e('Physical Card', 'massnahme-gift-cards'); ?>
                        </span>
                    </label>
                </div>

                <!-- Amount -->
                <div class="mgc-fd-form-group">
                    <label for="mgc-create-amount"><?php _e('Amount', 'massnahme-gift-cards'); ?> *</label>
                    <div class="mgc-fd-amount-input">
                        <span class="mgc-fd-currency"><?php echo esc_html($currency_symbol); ?></span>
                        <input type="number" id="mgc-create-amount" name="amount" step="0.01" min="1" required>
                    </div>
                    <div class="mgc-fd-quick-amounts">
                        <button type="button" class="mgc-fd-quick-amount" data-amount="50"><?php echo esc_html($currency_symbol); ?>50</button>
                        <button type="button" class="mgc-fd-quick-amount" data-amount="100"><?php echo esc_html($currency_symbol); ?>100</button>
                        <button type="button" class="mgc-fd-quick-amount" data-amount="200"><?php echo esc_html($currency_symbol); ?>200</button>
                        <button type="button" class="mgc-fd-quick-amount" data-amount="500"><?php echo esc_html($currency_symbol); ?>500</button>
                    </div>
                </div>

                <!-- Custom Code (shown for physical cards) -->
                <div class="mgc-fd-form-group mgc-fd-physical-only" style="display: none;">
                    <label for="mgc-create-code">
                        <?php _e('Card Code', 'massnahme-gift-cards'); ?>
                        <span class="mgc-fd-label-hint"><?php _e('(printed on physical card)', 'massnahme-gift-cards'); ?></span>
                    </label>
                    <input type="text" id="mgc-create-code" name="custom_code" placeholder="<?php esc_attr_e('e.g., PHYS-2025-ABC123', 'massnahme-gift-cards'); ?>" maxlength="50" pattern="[A-Za-z0-9\-]{4,50}">
                    <p class="mgc-fd-field-hint"><?php _e('Enter the code printed on the physical card. Use letters, numbers, and dashes only.', 'massnahme-gift-cards'); ?></p>
                </div>

                <!-- Auto-generate notice (shown for digital cards) -->
                <div class="mgc-fd-form-group mgc-fd-digital-only">
                    <div class="mgc-fd-info-box">
                        <span class="mgc-fd-info-icon">&#9432;</span>
                        <?php _e('A unique code will be automatically generated for this digital gift card.', 'massnahme-gift-cards'); ?>
                    </div>
                </div>

                <!-- Recipient Name -->
                <div class="mgc-fd-form-group">
                    <label for="mgc-create-recipient-name"><?php _e('Recipient Name', 'massnahme-gift-cards'); ?></label>
                    <input type="text" id="mgc-create-recipient-name" name="recipient_name" placeholder="<?php esc_attr_e('Optional', 'massnahme-gift-cards'); ?>">
                </div>

                <!-- Recipient Email -->
                <div class="mgc-fd-form-group">
                    <label for="mgc-create-recipient-email"><?php _e('Recipient Email', 'massnahme-gift-cards'); ?></label>
                    <input type="email" id="mgc-create-recipient-email" name="recipient_email" placeholder="<?php esc_attr_e('Optional', 'massnahme-gift-cards'); ?>">
                </div>

                <!-- Message -->
                <div class="mgc-fd-form-group">
                    <label for="mgc-create-message"><?php _e('Personal Message', 'massnahme-gift-cards'); ?></label>
                    <textarea id="mgc-create-message" name="message" rows="3" placeholder="<?php esc_attr_e('Optional personal message', 'massnahme-gift-cards'); ?>"></textarea>
                </div>

                <button type="submit" class="mgc-fd-btn mgc-fd-btn-primary mgc-fd-btn-large" id="mgc-fd-create-btn">
                    <?php _e('Create Gift Card', 'massnahme-gift-cards'); ?>
                </button>
            </form>
        </div>
    </div>
    -->

    <!-- COMMENTED OUT: Card Detail Modal (now using inline display in redemption section) -->
    <!--
    <div id="mgc-fd-detail-modal" class="mgc-fd-modal" style="display: none;">
        <div class="mgc-fd-modal-content">
            <div class="mgc-fd-modal-header">
                <h3><?php _e('Gift Card Details', 'massnahme-gift-cards'); ?></h3>
                <button type="button" class="mgc-fd-modal-close">&times;</button>
            </div>
            <div class="mgc-fd-modal-body">
                <div class="mgc-fd-detail-grid">
                    <div class="mgc-fd-detail-item">
                        <span class="mgc-fd-detail-label"><?php _e('ID', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-fd-detail-value" id="mgc-detail-id"></span>
                    </div>
                    <div class="mgc-fd-detail-item">
                        <span class="mgc-fd-detail-label"><?php _e('Code', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-fd-detail-value mgc-fd-code" id="mgc-detail-code"></span>
                    </div>
                    <div class="mgc-fd-detail-item">
                        <span class="mgc-fd-detail-label"><?php _e('Original Amount', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-fd-detail-value" id="mgc-detail-amount"></span>
                    </div>
                    <div class="mgc-fd-detail-item">
                        <span class="mgc-fd-detail-label"><?php _e('Current Balance', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-fd-detail-value mgc-fd-balance" id="mgc-detail-balance"></span>
                    </div>
                    <div class="mgc-fd-detail-item">
                        <span class="mgc-fd-detail-label"><?php _e('Recipient', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-fd-detail-value" id="mgc-detail-recipient"></span>
                    </div>
                    <div class="mgc-fd-detail-item">
                        <span class="mgc-fd-detail-label"><?php _e('Status', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-fd-detail-value" id="mgc-detail-status"></span>
                    </div>
                    <div class="mgc-fd-detail-item">
                        <span class="mgc-fd-detail-label"><?php _e('Created', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-fd-detail-value" id="mgc-detail-created"></span>
                    </div>
                    <div class="mgc-fd-detail-item">
                        <span class="mgc-fd-detail-label"><?php _e('Expires', 'massnahme-gift-cards'); ?></span>
                        <span class="mgc-fd-detail-value" id="mgc-detail-expires"></span>
                    </div>
                </div>

                <h4><?php _e('Transaction History', 'massnahme-gift-cards'); ?></h4>
                <div id="mgc-detail-history-loading"><?php _e('Loading...', 'massnahme-gift-cards'); ?></div>
                <table class="mgc-fd-table mgc-fd-history-table" id="mgc-detail-history-table" style="display: none;">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Amount', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('Remaining', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('User ID', 'massnahme-gift-cards'); ?></th>
                            <th><?php _e('User Name', 'massnahme-gift-cards'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="mgc-detail-history-tbody"></tbody>
                </table>
                <p id="mgc-detail-no-history" style="display: none;"><?php _e('No transactions yet.', 'massnahme-gift-cards'); ?></p>
            </div>
            <div class="mgc-fd-modal-footer">
                <button type="button" class="mgc-fd-btn mgc-fd-btn-secondary mgc-fd-modal-close-btn"><?php _e('Close', 'massnahme-gift-cards'); ?></button>
            </div>
        </div>
    </div>
    -->

    <!-- COMMENTED OUT: Success Modal (old tabbed interface) -->
    <!--
    <div id="mgc-fd-success-modal" class="mgc-fd-modal" style="display: none;">
        <div class="mgc-fd-modal-content mgc-fd-success-content">
            <div class="mgc-fd-success-icon">&#10003;</div>
            <h3><?php _e('Gift Card Created!', 'massnahme-gift-cards'); ?></h3>
            <div class="mgc-fd-success-details">
                <p class="mgc-fd-success-code" id="mgc-success-code"></p>
                <p class="mgc-fd-success-amount" id="mgc-success-amount"></p>
            </div>
            <button type="button" class="mgc-fd-btn mgc-fd-btn-primary" id="mgc-fd-success-close"><?php _e('Done', 'massnahme-gift-cards'); ?></button>
        </div>
    </div>
    -->
</div>

<style>
/* Frontend Dashboard Container */
.mgc-frontend-dashboard {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* Header */
.mgc-fd-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #eee;
}

.mgc-fd-header h2 {
    margin: 0;
    font-size: 24px;
    color: #1a1a1a;
}

.mgc-fd-user {
    font-size: 14px;
    color: #666;
    background: #f0f0f0;
    padding: 6px 14px;
    border-radius: 20px;
}

/* Tabs */
.mgc-fd-tabs {
    display: flex;
    gap: 5px;
    margin-bottom: 20px;
    border-bottom: 2px solid #eee;
    padding-bottom: 0;
}

.mgc-fd-tab {
    padding: 12px 24px;
    border: none;
    background: none;
    font-size: 14px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s;
}

.mgc-fd-tab:hover {
    color: #1a1a1a;
}

.mgc-fd-tab.active {
    color: #2271b1;
    border-bottom-color: #2271b1;
}

/* Tab Content */
.mgc-fd-tab-content {
    display: none;
}

.mgc-fd-tab-content.active {
    display: block;
}

/* Statistics */
.mgc-fd-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.mgc-fd-stat-card {
    background: linear-gradient(135deg, #1e3a5f, #2271b1);
    color: #fff;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
}

.mgc-fd-stat-value {
    display: block;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 5px;
}

.mgc-fd-stat-label {
    font-size: 13px;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Quick Actions */
.mgc-fd-quick-actions {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 12px;
}

.mgc-fd-quick-actions h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
}

/* Buttons */
.mgc-fd-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    margin-right: 10px;
}

.mgc-fd-btn-primary {
    background: #2271b1;
    color: #fff;
}

.mgc-fd-btn-primary:hover {
    background: #135e96;
}

.mgc-fd-btn-secondary {
    background: #6c757d;
    color: #fff;
}

.mgc-fd-btn-sm {
    padding: 8px 16px;
    font-size: 13px;
}

.mgc-fd-btn-large {
    width: 100%;
    padding: 16px;
    font-size: 16px;
    margin-top: 10px;
}

.mgc-fd-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Table */
.mgc-fd-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.mgc-fd-table th,
.mgc-fd-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.mgc-fd-table th {
    background: #f8f9fa;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    color: #666;
}

.mgc-fd-table tbody tr:hover {
    background: #f8f9fa;
}

/* Status Badges */
.mgc-fd-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.mgc-fd-status-active {
    background: #d4edda;
    color: #155724;
}

.mgc-fd-status-used {
    background: #f8d7da;
    color: #721c24;
}

.mgc-fd-type {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    text-transform: uppercase;
}

.mgc-fd-type-digital {
    background: #e3f2fd;
    color: #1565c0;
}

.mgc-fd-type-physical {
    background: #fff3e0;
    color: #e65100;
}

/* Form */
.mgc-fd-create-form {
    max-width: 600px;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.mgc-fd-create-form h3 {
    margin: 0 0 25px 0;
    font-size: 20px;
}

.mgc-fd-form-group {
    margin-bottom: 20px;
}

.mgc-fd-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.mgc-fd-label-hint {
    font-weight: 400;
    color: #888;
    font-size: 13px;
}

.mgc-fd-form-group input[type="text"],
.mgc-fd-form-group input[type="email"],
.mgc-fd-form-group input[type="number"],
.mgc-fd-form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 15px;
    transition: border-color 0.2s;
    box-sizing: border-box;
}

.mgc-fd-form-group input:focus,
.mgc-fd-form-group textarea:focus {
    border-color: #2271b1;
    outline: none;
}

.mgc-fd-amount-input {
    display: flex;
    align-items: center;
    border: 2px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.mgc-fd-currency {
    padding: 12px 15px;
    background: #f8f9fa;
    font-size: 18px;
    font-weight: 600;
    color: #666;
}

.mgc-fd-amount-input input {
    flex: 1;
    border: none !important;
    font-size: 20px;
    text-align: center;
}

.mgc-fd-quick-amounts {
    display: flex;
    gap: 8px;
    margin-top: 10px;
}

.mgc-fd-quick-amount {
    flex: 1;
    padding: 10px;
    border: 2px solid #ddd;
    background: #fff;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.mgc-fd-quick-amount:hover {
    border-color: #2271b1;
    color: #2271b1;
}

.mgc-fd-field-hint {
    margin: 8px 0 0 0;
    font-size: 13px;
    color: #888;
}

/* Card Type Toggle */
.mgc-fd-card-type-toggle {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.mgc-fd-toggle-option {
    flex: 1;
    cursor: pointer;
}

.mgc-fd-toggle-option input {
    display: none;
}

.mgc-fd-toggle-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    border: 2px solid #ddd;
    border-radius: 10px;
    transition: all 0.2s;
}

.mgc-fd-toggle-option input:checked + .mgc-fd-toggle-label {
    border-color: #2271b1;
    background: #f0f7fc;
}

.mgc-fd-toggle-icon {
    font-size: 28px;
    margin-bottom: 8px;
}

/* Info Box */
.mgc-fd-info-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    background: #e8f4fc;
    border-radius: 8px;
    font-size: 14px;
    color: #1565c0;
}

.mgc-fd-info-icon {
    font-size: 18px;
}

/* Form Notice */
.mgc-fd-form-notice {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
}

.mgc-fd-form-notice.success {
    background: #d4edda;
    color: #155724;
}

.mgc-fd-form-notice.error {
    background: #f8d7da;
    color: #721c24;
}

/* Modal */
.mgc-fd-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.mgc-fd-modal-content {
    background: #fff;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.mgc-fd-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.mgc-fd-modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.mgc-fd-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #999;
    line-height: 1;
}

.mgc-fd-modal-body {
    padding: 20px;
}

.mgc-fd-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #eee;
    text-align: right;
}

/* Detail Grid */
.mgc-fd-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.mgc-fd-detail-item {
    background: #f8f9fa;
    padding: 12px 15px;
    border-radius: 8px;
}

.mgc-fd-detail-label {
    display: block;
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.mgc-fd-detail-value {
    font-size: 15px;
    font-weight: 600;
    color: #333;
}

.mgc-fd-detail-value.mgc-fd-code {
    font-family: monospace;
    letter-spacing: 1px;
}

.mgc-fd-detail-value.mgc-fd-balance {
    color: #2271b1;
    font-size: 18px;
}

/* Success Modal */
.mgc-fd-success-content {
    text-align: center;
    padding: 40px;
}

.mgc-fd-success-icon {
    width: 70px;
    height: 70px;
    background: #28a745;
    color: #fff;
    font-size: 36px;
    line-height: 70px;
    border-radius: 50%;
    margin: 0 auto 20px;
}

.mgc-fd-success-code {
    font-size: 24px;
    font-weight: 700;
    font-family: monospace;
    letter-spacing: 2px;
    color: #333;
    margin: 15px 0;
}

.mgc-fd-success-amount {
    font-size: 20px;
    color: #2271b1;
    font-weight: 600;
}

/* Loading */
.mgc-fd-loading {
    text-align: center;
    padding: 40px;
    color: #888;
}

/* Pagination */
.mgc-fd-pagination {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 20px;
}

.mgc-fd-page-btn {
    padding: 8px 12px;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.mgc-fd-page-btn:hover {
    background: #f8f9fa;
}

.mgc-fd-page-btn.active {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}

/* Cards Header */
.mgc-fd-cards-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.mgc-fd-cards-header h3 {
    margin: 0;
}

/* Filters */
.mgc-fd-filters {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.mgc-fd-search-wrap {
    flex: 1;
    min-width: 200px;
}

.mgc-fd-search-input {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.mgc-fd-search-input:focus {
    border-color: #2271b1;
    outline: none;
}

.mgc-fd-filter-wrap {
    display: flex;
    gap: 10px;
}

.mgc-fd-filter-select {
    padding: 10px 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
    cursor: pointer;
    min-width: 140px;
}

.mgc-fd-filter-select:focus {
    border-color: #2271b1;
    outline: none;
}

/* Transaction type badges */
.mgc-fd-tx-type {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 600;
}

.mgc-fd-tx-type-redemption {
    background: #fff3cd;
    color: #856404;
}

.mgc-fd-tx-type-creation {
    background: #d4edda;
    color: #155724;
}

.mgc-fd-tx-type-adjustment {
    background: #d1ecf1;
    color: #0c5460;
}

/* User ID badge */
.mgc-fd-user-id {
    display: inline-block;
    padding: 2px 8px;
    background: #e9ecef;
    border-radius: 4px;
    font-family: monospace;
    font-size: 12px;
    color: #495057;
}

/* History table user columns */
.mgc-fd-history-table th,
.mgc-fd-history-table td {
    font-size: 13px;
    padding: 10px 12px;
}

/* ============================================ */
/* REDEMPTION SECTION STYLES */
/* ============================================ */

.mgc-fd-redemption-section {
    max-width: 700px;
    margin: 0 auto;
}

.mgc-fd-code-entry {
    margin-bottom: 25px;
}

.mgc-fd-input-group {
    display: flex;
    gap: 10px;
}

.mgc-fd-code-input {
    flex: 1;
    padding: 16px 20px;
    font-size: 18px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 2px;
    border: 2px solid #ddd;
    border-radius: 10px;
    outline: none;
    transition: border-color 0.2s;
}

.mgc-fd-code-input:focus {
    border-color: #2271b1;
}

/* Status Banner */
.mgc-fd-status-banner {
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

.mgc-fd-status-banner.status-active {
    background: #d4edda;
    color: #155724;
}

.mgc-fd-status-banner.status-used {
    background: #f8d7da;
    color: #721c24;
}

.mgc-fd-status-banner.status-expired {
    background: #fff3cd;
    color: #856404;
}

/* Balance Box */
.mgc-fd-balance-box {
    text-align: center;
    padding: 30px 20px;
    background: linear-gradient(135deg, #1e3a5f, #2271b1);
    border-radius: 15px;
    margin-bottom: 20px;
}

.mgc-fd-balance-label {
    display: block;
    color: rgba(255,255,255,0.8);
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}

.mgc-fd-balance-value {
    display: block;
    color: #fff;
    font-size: 42px;
    font-weight: 700;
}

/* Card Info Grid */
.mgc-fd-card-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 25px;
}

.mgc-fd-info-item {
    background: #f8f9fa;
    padding: 12px 15px;
    border-radius: 8px;
}

.mgc-fd-info-label {
    display: block;
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.mgc-fd-info-value {
    font-size: 15px;
    font-weight: 600;
    color: #333;
}

.mgc-fd-info-value.mgc-fd-code {
    font-family: monospace;
    letter-spacing: 1px;
}

/* Redemption Amount Section */
.mgc-fd-redemption-amount {
    background: #fff;
    border: 2px solid #eee;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
}

.mgc-fd-redemption-amount h4 {
    margin: 0 0 20px 0;
    text-align: center;
    color: #1a1a1a;
    font-size: 18px;
}

.mgc-fd-amount-input-wrap {
    display: flex;
    align-items: center;
    border: 2px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 15px;
}

.mgc-fd-currency-symbol {
    padding: 15px 18px;
    background: #f8f9fa;
    font-size: 22px;
    font-weight: 600;
    color: #666;
}

.mgc-fd-amount-input-field {
    flex: 1;
    border: none;
    padding: 18px 15px;
    font-size: 26px;
    text-align: center;
    outline: none;
}

/* Quick Amount Grid */
.mgc-fd-quick-amounts-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
    margin-bottom: 15px;
}

.mgc-fd-quick-amt {
    padding: 12px 8px;
    border: 2px solid #ddd;
    background: #fff;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.mgc-fd-quick-amt:hover {
    border-color: #2271b1;
    color: #2271b1;
}

.mgc-fd-quick-amt.selected {
    background: #2271b1;
    border-color: #2271b1;
    color: #fff;
}

.mgc-fd-quick-full {
    background: #e8f4fc;
}

/* Preview */
.mgc-fd-preview {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
}

.mgc-fd-preview-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 15px;
}

.mgc-fd-preview-deduct {
    color: #dc3545;
}

.mgc-fd-preview-remaining {
    font-weight: 700;
    font-size: 17px;
    color: #28a745;
    border-top: 2px solid #ddd;
    padding-top: 10px;
    margin-top: 5px;
}

/* Success Button */
.mgc-fd-btn-success {
    background: #28a745;
    color: #fff;
}

.mgc-fd-btn-success:hover:not(:disabled) {
    background: #218838;
}

/* Card Transactions */
.mgc-fd-card-transactions {
    background: #fff;
    border: 2px solid #eee;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
}

.mgc-fd-card-transactions h4 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #1a1a1a;
}

/* Error Display */
.mgc-fd-error-display {
    text-align: center;
    padding: 40px 20px;
    background: #fff;
    border-radius: 15px;
    border: 2px solid #f8d7da;
}

.mgc-fd-error-icon {
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

.mgc-fd-error-text {
    font-size: 18px;
    color: #721c24;
    margin-bottom: 20px;
}

/* Success Overlay */
.mgc-fd-success-overlay {
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

.mgc-fd-success-content {
    background: #fff;
    border-radius: 20px;
    padding: 40px;
    text-align: center;
    max-width: 90%;
    width: 400px;
}

.mgc-fd-success-icon {
    width: 80px;
    height: 80px;
    background: #28a745;
    color: #fff;
    font-size: 48px;
    line-height: 80px;
    border-radius: 50%;
    margin: 0 auto 20px;
}

.mgc-fd-success-title {
    font-size: 24px;
    font-weight: 700;
    color: #28a745;
    margin-bottom: 15px;
}

.mgc-fd-success-amount {
    font-size: 36px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 10px;
}

.mgc-fd-success-remaining {
    font-size: 16px;
    color: #666;
    margin-bottom: 25px;
}

/* Responsive */
@media (max-width: 768px) {
    .mgc-fd-tabs {
        flex-wrap: wrap;
    }

    .mgc-fd-tab {
        flex: 1;
        text-align: center;
        padding: 10px 15px;
    }

    .mgc-fd-stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .mgc-fd-detail-grid {
        grid-template-columns: 1fr;
    }

    .mgc-fd-table {
        font-size: 13px;
    }

    .mgc-fd-table th,
    .mgc-fd-table td {
        padding: 8px 10px;
    }

    .mgc-fd-filters {
        flex-direction: column;
    }

    .mgc-fd-filter-wrap {
        flex-wrap: wrap;
    }

    .mgc-fd-filter-select {
        flex: 1;
        min-width: 120px;
    }

    .mgc-fd-table-responsive {
        overflow-x: auto;
    }

    /* Redemption section responsive */
    .mgc-fd-input-group {
        flex-direction: column;
    }

    .mgc-fd-card-info-grid {
        grid-template-columns: 1fr;
    }

    .mgc-fd-quick-amounts-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .mgc-fd-balance-value {
        font-size: 32px;
    }
}
</style>

<script>
(function($) {
    'use strict';

    var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
    var nonce = '<?php echo wp_create_nonce('mgc_frontend_nonce'); ?>';
    var currencySymbol = '<?php echo esc_js($currency_symbol); ?>';
    var currentPage = 1;
    var currentTransactionPage = 1;
    var searchTimeout = null;
    var transactionSearchTimeout = null;

    function formatCurrency(amount) {
        // Decode HTML entity (e.g., &euro; -> €) for proper display
        var decodedSymbol = $('<textarea />').html(currencySymbol).text();
        return decodedSymbol + parseFloat(amount).toFixed(2).replace('.', ',');
    }

    // Tab switching
    $('.mgc-fd-tab').on('click', function() {
        var tab = $(this).data('tab');
        $('.mgc-fd-tab').removeClass('active');
        $(this).addClass('active');
        $('.mgc-fd-tab-content').removeClass('active');
        $('#mgc-tab-' + tab).addClass('active');

        if (tab === 'all-cards') {
            loadCards(1);
        } else if (tab === 'transactions') {
            loadTransactions(1);
        }
    });

    // Quick action buttons
    $('[data-goto]').on('click', function() {
        var tab = $(this).data('goto');
        $('.mgc-fd-tab[data-tab="' + tab + '"]').click();
    });

    // Card type toggle
    $('input[name="card_type"]').on('change', function() {
        if ($(this).val() === 'physical') {
            $('.mgc-fd-physical-only').show();
            $('.mgc-fd-digital-only').hide();
        } else {
            $('.mgc-fd-physical-only').hide();
            $('.mgc-fd-digital-only').show();
        }
    });

    // Quick amount buttons
    $('.mgc-fd-quick-amount').on('click', function() {
        $('#mgc-create-amount').val($(this).data('amount'));
    });

    // Create form submission
    $('#mgc-fd-create-form').on('submit', function(e) {
        e.preventDefault();

        var $btn = $('#mgc-fd-create-btn');
        var $notice = $('#mgc-fd-create-notice');
        var isPhysical = $('input[name="card_type"]:checked').val() === 'physical';

        $btn.prop('disabled', true).text('<?php _e('Creating...', 'massnahme-gift-cards'); ?>');
        $notice.hide();

        $.post(ajaxUrl, {
            action: 'mgc_frontend_create_card',
            nonce: nonce,
            amount: $('#mgc-create-amount').val(),
            custom_code: isPhysical ? $('#mgc-create-code').val() : '',
            recipient_name: $('#mgc-create-recipient-name').val(),
            recipient_email: $('#mgc-create-recipient-email').val(),
            message: $('#mgc-create-message').val(),
            is_physical: isPhysical ? 1 : 0
        }, function(response) {
            if (response.success) {
                // Show success modal
                $('#mgc-success-code').text(response.data.code);
                $('#mgc-success-amount').text(response.data.formatted_amount);
                $('#mgc-fd-success-modal').show();

                // Reset form
                $('#mgc-fd-create-form')[0].reset();
                $('input[name="card_type"][value="digital"]').prop('checked', true).trigger('change');
            } else {
                $notice.removeClass('success').addClass('error').text(response.data).show();
            }
        }).fail(function() {
            $notice.removeClass('success').addClass('error').text('<?php _e('An error occurred. Please try again.', 'massnahme-gift-cards'); ?>').show();
        }).always(function() {
            $btn.prop('disabled', false).text('<?php _e('Create Gift Card', 'massnahme-gift-cards'); ?>');
        });
    });

    // Success modal close
    $('#mgc-fd-success-close').on('click', function() {
        $('#mgc-fd-success-modal').hide();
    });

    // Load cards with filters
    function loadCards(page) {
        currentPage = page;
        $('#mgc-fd-cards-loading').show();
        $('#mgc-fd-cards-table-wrap').hide();
        $('#mgc-fd-no-cards').hide();

        var search = $('#mgc-fd-search').val();
        var filterStatus = $('#mgc-fd-filter-status').val();
        var filterType = $('#mgc-fd-filter-type').val();

        $.post(ajaxUrl, {
            action: 'mgc_frontend_list_cards',
            nonce: nonce,
            page: page,
            search: search,
            status: filterStatus,
            delivery_method: filterType
        }, function(response) {
            $('#mgc-fd-cards-loading').hide();

            if (response.success && response.data.cards.length > 0) {
                var $tbody = $('#mgc-fd-cards-tbody');
                $tbody.empty();

                $.each(response.data.cards, function(i, card) {
                    var typeClass = card.delivery_method === 'physical' ? 'physical' : 'digital';
                    var typeLabel = getTypeLabel(card.delivery_method);
                    var statusClass = card.status;
                    var recipient = card.recipient_name || card.recipient_email || '-';

                    $tbody.append(
                        '<tr>' +
                        '<td><strong>#' + card.id + '</strong></td>' +
                        '<td><code>' + card.code + '</code></td>' +
                        '<td>' + card.formatted_amount + '</td>' +
                        '<td>' + card.formatted_balance + '</td>' +
                        '<td>' + escapeHtml(recipient) + '</td>' +
                        '<td><span class="mgc-fd-type mgc-fd-type-' + typeClass + '">' + typeLabel + '</span></td>' +
                        '<td><span class="mgc-fd-status mgc-fd-status-' + statusClass + '">' + card.status.charAt(0).toUpperCase() + card.status.slice(1) + '</span></td>' +
                        '<td>' + card.created_at + '</td>' +
                        '<td><button class="mgc-fd-btn mgc-fd-btn-sm mgc-fd-btn-secondary mgc-fd-view-detail" data-code="' + card.code + '"><?php _e('Details', 'massnahme-gift-cards'); ?></button></td>' +
                        '</tr>'
                    );
                });

                // Pagination
                var $pagination = $('#mgc-fd-pagination');
                $pagination.empty();
                for (var i = 1; i <= response.data.total_pages; i++) {
                    $pagination.append('<button class="mgc-fd-page-btn' + (i === page ? ' active' : '') + '" data-page="' + i + '">' + i + '</button>');
                }

                $('#mgc-fd-cards-table-wrap').show();
            } else {
                $('#mgc-fd-no-cards').show();
            }
        });
    }

    function getTypeLabel(type) {
        var labels = {
            'digital': '<?php _e('Digital', 'massnahme-gift-cards'); ?>',
            'physical': '<?php _e('Physical', 'massnahme-gift-cards'); ?>',
            'pickup': '<?php _e('Pickup', 'massnahme-gift-cards'); ?>',
            'shipping': '<?php _e('Shipping', 'massnahme-gift-cards'); ?>'
        };
        return labels[type] || type;
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Search with debounce
    $('#mgc-fd-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadCards(1);
        }, 300);
    });

    // Filter changes
    $('#mgc-fd-filter-status, #mgc-fd-filter-type').on('change', function() {
        loadCards(1);
    });

    // Load transactions
    function loadTransactions(page) {
        currentTransactionPage = page;
        $('#mgc-fd-transactions-loading').show();
        $('#mgc-fd-transactions-table-wrap').hide();
        $('#mgc-fd-no-transactions').hide();

        var search = $('#mgc-fd-transaction-search').val();
        var filterUser = $('#mgc-fd-filter-user').val();

        $.post(ajaxUrl, {
            action: 'mgc_frontend_list_transactions',
            nonce: nonce,
            page: page,
            search: search,
            user_id: filterUser
        }, function(response) {
            $('#mgc-fd-transactions-loading').hide();

            if (response.success && response.data.transactions.length > 0) {
                var $tbody = $('#mgc-fd-transactions-tbody');
                $tbody.empty();

                // Populate user filter dropdown if not already done
                if (response.data.users && response.data.users.length > 0) {
                    var $userFilter = $('#mgc-fd-filter-user');
                    if ($userFilter.find('option').length <= 1) {
                        $.each(response.data.users, function(i, user) {
                            $userFilter.append('<option value="' + user.id + '">' + escapeHtml(user.name) + ' (#' + user.id + ')</option>');
                        });
                    }
                }

                $.each(response.data.transactions, function(i, tx) {
                    var txTypeClass = tx.type;
                    var txTypeLabel = getTransactionTypeLabel(tx.type);
                    var userIdDisplay = tx.updated_by ? '<span class="mgc-fd-user-id">#' + tx.updated_by + '</span>' : '-';
                    var userNameDisplay = tx.updated_by_name || '-';
                    var orderDisplay = tx.order_id > 0 ? '<a href="#" class="mgc-fd-order-link" data-order="' + tx.order_id + '">#' + tx.order_id + '</a>' : '<?php _e('Manual', 'massnahme-gift-cards'); ?>';

                    $tbody.append(
                        '<tr>' +
                        '<td><strong>#' + tx.id + '</strong></td>' +
                        '<td>' + tx.date + '</td>' +
                        '<td><code>' + tx.gift_card_code + '</code></td>' +
                        '<td><span class="mgc-fd-tx-type mgc-fd-tx-type-' + txTypeClass + '">' + txTypeLabel + '</span></td>' +
                        '<td>' + formatCurrency(tx.amount_used) + '</td>' +
                        '<td>' + formatCurrency(tx.remaining_balance) + '</td>' +
                        '<td>' + userIdDisplay + '</td>' +
                        '<td>' + escapeHtml(userNameDisplay) + '</td>' +
                        '<td>' + orderDisplay + '</td>' +
                        '</tr>'
                    );
                });

                // Pagination
                var $pagination = $('#mgc-fd-transactions-pagination');
                $pagination.empty();
                for (var i = 1; i <= response.data.total_pages; i++) {
                    $pagination.append('<button class="mgc-fd-page-btn mgc-fd-tx-page-btn' + (i === page ? ' active' : '') + '" data-page="' + i + '">' + i + '</button>');
                }

                $('#mgc-fd-transactions-table-wrap').show();
            } else {
                $('#mgc-fd-no-transactions').show();
            }
        });
    }

    function getTransactionTypeLabel(type) {
        var labels = {
            'redemption': '<?php _e('Redemption', 'massnahme-gift-cards'); ?>',
            'creation': '<?php _e('Creation', 'massnahme-gift-cards'); ?>',
            'adjustment': '<?php _e('Adjustment', 'massnahme-gift-cards'); ?>'
        };
        return labels[type] || type;
    }

    // Transaction search with debounce
    $('#mgc-fd-transaction-search').on('input', function() {
        clearTimeout(transactionSearchTimeout);
        transactionSearchTimeout = setTimeout(function() {
            loadTransactions(1);
        }, 300);
    });

    // Transaction filter changes
    $('#mgc-fd-filter-user').on('change', function() {
        loadTransactions(1);
    });

    // Transaction pagination click
    $(document).on('click', '.mgc-fd-tx-page-btn', function() {
        loadTransactions($(this).data('page'));
    });

    // Refresh transactions button
    $('#mgc-fd-refresh-transactions').on('click', function() {
        loadTransactions(currentTransactionPage);
    });

    // Pagination click
    $(document).on('click', '.mgc-fd-page-btn', function() {
        loadCards($(this).data('page'));
    });

    // Refresh button
    $('#mgc-fd-refresh').on('click', function() {
        loadCards(currentPage);
    });

    // View detail
    $(document).on('click', '.mgc-fd-view-detail', function() {
        var code = $(this).data('code');
        showCardDetail(code);
    });

    function showCardDetail(code) {
        $('#mgc-detail-history-loading').show();
        $('#mgc-detail-history-table').hide();
        $('#mgc-detail-no-history').hide();
        $('#mgc-fd-detail-modal').show();

        $.post(ajaxUrl, {
            action: 'mgc_frontend_staff_lookup',
            nonce: nonce,
            code: code
        }, function(response) {
            $('#mgc-detail-history-loading').hide();

            if (response.success) {
                var data = response.data;

                $('#mgc-detail-id').text('#' + (data.id || '-'));
                $('#mgc-detail-code').text(data.code);
                $('#mgc-detail-amount').text(formatCurrency(data.amount));
                $('#mgc-detail-balance').text(formatCurrency(data.balance));
                $('#mgc-detail-recipient').text(data.recipient_name || data.recipient_email || '-');
                $('#mgc-detail-status').text(data.status.charAt(0).toUpperCase() + data.status.slice(1));
                $('#mgc-detail-created').text(data.created_at);
                $('#mgc-detail-expires').text(data.expires_at);

                if (data.history && data.history.length > 0) {
                    var $tbody = $('#mgc-detail-history-tbody');
                    $tbody.empty();

                    $.each(data.history, function(i, item) {
                        var userIdDisplay = item.updated_by ? '<span class="mgc-fd-user-id">#' + item.updated_by + '</span>' : '-';
                        var userNameDisplay = item.updated_by_name || '-';
                        $tbody.append(
                            '<tr>' +
                            '<td>' + item.date + '</td>' +
                            '<td>' + formatCurrency(item.amount) + '</td>' +
                            '<td>' + formatCurrency(item.remaining) + '</td>' +
                            '<td>' + userIdDisplay + '</td>' +
                            '<td>' + escapeHtml(userNameDisplay) + '</td>' +
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

    // Close modals
    $(document).on('click', '.mgc-fd-modal-close, .mgc-fd-modal-close-btn', function() {
        $(this).closest('.mgc-fd-modal').hide();
    });

    $('.mgc-fd-modal').on('click', function(e) {
        if ($(e.target).is('.mgc-fd-modal')) {
            $(this).hide();
        }
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.mgc-fd-modal').hide();
            $('#mgc-redemption-success').hide();
        }
    });

    // ============================================
    // REDEMPTION SECTION - New Simplified Interface
    // ============================================

    var currentRedemptionCard = null;
    var currentRedemptionBalance = 0;

    // Lookup gift card for redemption
    function lookupRedemptionCard() {
        var code = $('#mgc-redemption-code').val().trim().toUpperCase();
        if (!code) {
            showRedemptionError('<?php _e('Please enter a gift card code', 'massnahme-gift-cards'); ?>');
            return;
        }

        $('#mgc-redemption-lookup').prop('disabled', true).text('<?php _e('Looking up...', 'massnahme-gift-cards'); ?>');

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
                    displayRedemptionCard(response.data);
                } else {
                    showRedemptionError(response.data || '<?php _e('Gift card not found', 'massnahme-gift-cards'); ?>');
                }
            },
            error: function() {
                showRedemptionError('<?php _e('Connection error. Please try again.', 'massnahme-gift-cards'); ?>');
            },
            complete: function() {
                $('#mgc-redemption-lookup').prop('disabled', false).text('<?php _e('Look Up', 'massnahme-gift-cards'); ?>');
            }
        });
    }

    // Display card info for redemption
    function displayRedemptionCard(card) {
        currentRedemptionCard = card;
        currentRedemptionBalance = parseFloat(card.balance);

        $('#mgc-redemption-error').hide();
        $('#mgc-redemption-card-display').show();

        // Status banner
        var banner = $('#mgc-redemption-status-banner');
        banner.removeClass('status-active status-used status-expired').addClass('status-' + card.status);

        var statusText = {
            'active': '<?php _e('VALID', 'massnahme-gift-cards'); ?>',
            'used': '<?php _e('FULLY USED', 'massnahme-gift-cards'); ?>',
            'expired': '<?php _e('EXPIRED', 'massnahme-gift-cards'); ?>'
        };
        var statusIcon = { 'active': '✓', 'used': '✗', 'expired': '!' };

        banner.find('.mgc-fd-status-icon').text(statusIcon[card.status] || '?');
        banner.find('.mgc-fd-status-text').text(statusText[card.status] || card.status.toUpperCase());

        // Card details
        $('#mgc-redemption-balance').html(formatCurrency(card.balance));
        $('#mgc-redemption-card-code').text(card.code);
        $('#mgc-redemption-original').html(formatCurrency(card.amount));
        $('#mgc-redemption-recipient').text(card.recipient_name || card.recipient_email || '-');
        $('#mgc-redemption-expires').text(card.expires_at || '-');

        // Show/hide redemption section
        if (card.status === 'active' && currentRedemptionBalance > 0) {
            $('#mgc-redemption-amount-section').show();
            $('#mgc-redemption-amount').attr('max', currentRedemptionBalance);
        } else {
            $('#mgc-redemption-amount-section').hide();
        }

        // Reset redemption form
        $('#mgc-redemption-amount').val('');
        $('#mgc-redemption-preview').hide();
        $('#mgc-redemption-confirm').prop('disabled', true);
        $('.mgc-fd-quick-amt').removeClass('selected');

        // Load transaction history
        loadRedemptionHistory(card);
    }

    // Load transaction history for the card
    function loadRedemptionHistory(card) {
        $('#mgc-redemption-history-loading').show();
        $('#mgc-redemption-history-table').hide();
        $('#mgc-redemption-no-history').hide();

        if (card.history && card.history.length > 0) {
            var $tbody = $('#mgc-redemption-history-tbody');
            $tbody.empty();

            $.each(card.history, function(i, item) {
                var typeLabel = getTransactionTypeLabel(item.type || 'adjustment');
                var userIdDisplay = item.updated_by ? '<span class="mgc-fd-user-id">#' + item.updated_by + '</span>' : '-';
                var userNameDisplay = item.updated_by_name || '-';
                $tbody.append(
                    '<tr>' +
                    '<td>' + item.date + '</td>' +
                    '<td><span class="mgc-fd-tx-type mgc-fd-tx-type-' + (item.type || 'adjustment') + '">' + typeLabel + '</span></td>' +
                    '<td style="color: #dc3545; font-weight: 600;">-' + formatCurrency(Math.abs(item.amount)) + '</td>' +
                    '<td>' + formatCurrency(item.remaining) + '</td>' +
                    '<td>' + userIdDisplay + '</td>' +
                    '<td>' + escapeHtml(userNameDisplay) + '</td>' +
                    '</tr>'
                );
            });

            $('#mgc-redemption-history-loading').hide();
            $('#mgc-redemption-history-table').show();
        } else {
            $('#mgc-redemption-history-loading').hide();
            $('#mgc-redemption-no-history').show();
        }
    }

    // Show error for redemption
    function showRedemptionError(message) {
        $('#mgc-redemption-card-display').hide();
        $('#mgc-redemption-error').show();
        $('#mgc-redemption-error-message').text(message);
    }

    // Clear redemption form
    function clearRedemption() {
        currentRedemptionCard = null;
        currentRedemptionBalance = 0;
        $('#mgc-redemption-code').val('').focus();
        $('#mgc-redemption-card-display').hide();
        $('#mgc-redemption-error').hide();
    }

    // Update redemption preview
    function updateRedemptionPreview() {
        var amount = parseFloat($('#mgc-redemption-amount').val()) || 0;

        if (amount > 0 && amount <= currentRedemptionBalance) {
            var remaining = currentRedemptionBalance - amount;
            $('#mgc-preview-current').html(formatCurrency(currentRedemptionBalance));
            $('#mgc-preview-deduct').html('-' + formatCurrency(amount));
            $('#mgc-preview-remaining').html(formatCurrency(remaining));
            $('#mgc-redemption-preview').show();
            $('#mgc-redemption-confirm').prop('disabled', false);
        } else {
            $('#mgc-redemption-preview').hide();
            $('#mgc-redemption-confirm').prop('disabled', true);
        }
    }

    // Confirm redemption
    function confirmRedemption() {
        var amount = parseFloat($('#mgc-redemption-amount').val());

        if (!amount || amount <= 0 || amount > currentRedemptionBalance) {
            alert('<?php _e('Invalid amount', 'massnahme-gift-cards'); ?>');
            return;
        }

        $('#mgc-redemption-confirm').prop('disabled', true).text('<?php _e('Processing...', 'massnahme-gift-cards'); ?>');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'mgc_frontend_redeem',
                nonce: nonce,
                code: currentRedemptionCard.code,
                amount: amount
            },
            success: function(response) {
                if (response.success) {
                    showRedemptionSuccess(amount, response.data.new_balance);
                } else {
                    alert('<?php _e('Error:', 'massnahme-gift-cards'); ?> ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('Connection error. Please try again.', 'massnahme-gift-cards'); ?>');
            },
            complete: function() {
                $('#mgc-redemption-confirm').prop('disabled', false).text('<?php _e('Confirm Redemption', 'massnahme-gift-cards'); ?>');
            }
        });
    }

    // Show success overlay
    function showRedemptionSuccess(amount, remaining) {
        $('#mgc-redemption-success-amount').html('-' + formatCurrency(amount));
        $('#mgc-redemption-success-remaining').html('<?php _e('Remaining balance:', 'massnahme-gift-cards'); ?> ' + formatCurrency(remaining));
        $('#mgc-redemption-success').show();
    }

    // Event handlers for redemption section
    $('#mgc-redemption-lookup').on('click', lookupRedemptionCard);
    $('#mgc-redemption-code').on('keypress', function(e) {
        if (e.which === 13) lookupRedemptionCard();
    });

    $('#mgc-redemption-clear, #mgc-redemption-retry').on('click', clearRedemption);

    $('#mgc-redemption-amount').on('input', function() {
        $('.mgc-fd-quick-amt').removeClass('selected');
        updateRedemptionPreview();
    });

    $('.mgc-fd-quick-amt').on('click', function() {
        var amount = $(this).data('amount');
        $('.mgc-fd-quick-amt').removeClass('selected');
        $(this).addClass('selected');
        $('#mgc-redemption-amount').val(amount === 'full' ? currentRedemptionBalance : amount);
        updateRedemptionPreview();
    });

    $('#mgc-redemption-confirm').on('click', confirmRedemption);

    $('#mgc-redemption-success-close').on('click', function() {
        $('#mgc-redemption-success').hide();
        clearRedemption();
    });

})(jQuery);
</script>
