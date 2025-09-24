<?php
/**
 * Admin Page Template
 * 
 * @package PayGateHandler
 * @since 2.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin = PayGateHandlerPro::get_instance();
$payments = $wpdb->get_results("SELECT * FROM {$plugin->get_table_name()} ORDER BY created_at DESC LIMIT 50");
$total_payments = $wpdb->get_var("SELECT COUNT(*) FROM {$plugin->get_table_name()}");
$pending_payments = $wpdb->get_var("SELECT COUNT(*) FROM {$plugin->get_table_name()} WHERE status = 'pending'");
$completed_payments = $wpdb->get_var("SELECT COUNT(*) FROM {$plugin->get_table_name()} WHERE status = 'completed'");
?>

<div class="wrap">
    <h1 class="wp-heading-inline">PayGate Payments Dashboard</h1>
    <a href="<?php echo admin_url('admin.php?page=pgh_admin_settings'); ?>" class="page-title-action">Settings</a>
    
    <hr class="wp-header-end">
    
    <!-- Search and Filter Bar -->
    <div class="pgh-search-filter-bar">
        <div class="pgh-search-box">
            <input type="text" id="payment-search" placeholder="Search payments by name, email, or reference..." class="regular-text">
            <button type="button" id="search-payments" class="button">Search</button>
        </div>
        
        <div class="pgh-filter-box">
            <select id="status-filter" class="regular-text">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="failed">Failed</option>
            </select>
            
            <select id="date-filter" class="regular-text">
                <option value="">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
            </select>
            
            <button type="button" id="clear-filters" class="button">Clear</button>
        </div>
    </div>
    
    <!-- Dashboard Stats -->
    <div class="pgh-dashboard-stats">
        <div class="pgh-stat-card">
            <div class="pgh-stat-icon">📊</div>
            <div class="pgh-stat-content">
                <h3><?php echo number_format($total_payments); ?></h3>
                <p>Total Payments</p>
            </div>
        </div>
        
        <div class="pgh-stat-card">
            <div class="pgh-stat-icon">⏳</div>
            <div class="pgh-stat-content">
                <h3><?php echo number_format($pending_payments); ?></h3>
                <p>Pending</p>
            </div>
        </div>
        
        <div class="pgh-stat-card">
            <div class="pgh-stat-icon">✅</div>
            <div class="pgh-stat-content">
                <h3><?php echo number_format($completed_payments); ?></h3>
                <p>Completed</p>
            </div>
        </div>
        
        <div class="pgh-stat-card">
            <div class="pgh-stat-icon">💰</div>
            <div class="pgh-stat-content">
                <h3>R <?php echo number_format($wpdb->get_var("SELECT SUM(amount) FROM {$plugin->get_table_name()} WHERE status = 'completed'") / 100, 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
    </div>
    
    <!-- Recent Payments Table -->
    <div class="pgh-payments-table">
        <h2>Recent Payments</h2>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Item</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">
                        No payments found. <a href="<?php echo admin_url('admin.php?page=pgh_admin_settings'); ?>">Configure your settings</a> to start processing payments.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo esc_html($payment->id); ?></td>
                        <td><?php echo esc_html($payment->name); ?></td>
                        <td><?php echo esc_html($payment->email); ?></td>
                        <td><?php echo esc_html($payment->item_name); ?></td>
                        <td>R <?php echo number_format($payment->amount / 100, 2); ?></td>
                        <td>
                            <span class="pgh-status pgh-status-<?php echo esc_attr($payment->status); ?>">
                                <?php echo esc_html(ucfirst($payment->status)); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date('d-m-Y H:i', strtotime($payment->created_at))); ?></td>
                        <td>
                            <button class="button button-small pgh-view-details" data-payment-id="<?php echo esc_attr($payment->id); ?>">
                                View Details
                            </button>
                            <?php if ($payment->status === 'completed'): ?>
                            <button class="button button-small pgh-resend-email" data-payment-id="<?php echo esc_attr($payment->id); ?>">
                                Resend Email
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Payment Details Modal -->
    <div id="pgh-payment-modal" class="pgh-modal" style="display: none;">
        <div class="pgh-modal-content">
            <div class="pgh-modal-header">
                <h2>Payment Details</h2>
                <span class="pgh-modal-close">&times;</span>
            </div>
            <div class="pgh-modal-body">
                <div id="pgh-payment-details"></div>
            </div>
        </div>
    </div>
</div>

<style>
.pgh-dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.pgh-stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.pgh-stat-icon {
    font-size: 24px;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
    border-radius: 50%;
}

.pgh-stat-content h3 {
    margin: 0;
    font-size: 24px;
    color: #333;
}

.pgh-stat-content p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

.pgh-payments-table {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 20px;
}

.pgh-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.pgh-status-pending {
    background: #fff3cd;
    color: #856404;
}

.pgh-status-completed {
    background: #d4edda;
    color: #155724;
}

.pgh-status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.pgh-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pgh-modal-content {
    background: #fff;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.pgh-modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pgh-modal-close {
    font-size: 24px;
    cursor: pointer;
    color: #999;
}

.pgh-modal-body {
    padding: 20px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // View payment details
    $('.pgh-view-details').on('click', function() {
        var paymentId = $(this).data('payment-id');
        
        $.ajax({
            url: pgh_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pgh_get_payment',
                payment_id: paymentId,
                nonce: pgh_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var payment = response.data;
                    var html = '<ul>';
                    for (var key in payment) {
                        html += '<li><strong>' + key + ':</strong> ' + payment[key] + '</li>';
                    }
                    html += '</ul>';
                    
                    $('#pgh-payment-details').html(html);
                    $('#pgh-payment-modal').show();
                }
            }
        });
    });
    
    // Resend email
    $('.pgh-resend-email').on('click', function() {
        var paymentId = $(this).data('payment-id');
        var button = $(this);
        
        button.prop('disabled', true).text('Sending...');
        
        $.ajax({
            url: pgh_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pgh_resend_email',
                payment_id: paymentId,
                nonce: pgh_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Email sent successfully!');
                } else {
                    alert('Failed to send email: ' + response.data);
                }
            },
            complete: function() {
                button.prop('disabled', false).text('Resend Email');
            }
        });
    });
    
    // Close modal
    $('.pgh-modal-close, .pgh-modal').on('click', function(e) {
        if (e.target === this) {
            $('#pgh-payment-modal').hide();
        }
    });
});
</script>
