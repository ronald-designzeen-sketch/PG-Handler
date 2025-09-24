<?php
/**
 * Bulk Processing Page Template
 * 
 * @package PayGateHandler
 * @since 2.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin = PayGateHandlerPro::get_instance();
$payments = $wpdb->get_results("SELECT * FROM {$plugin->get_table_name()} ORDER BY created_at DESC");
?>

<div class="wrap">
    <h1>Bulk Processing</h1>
    
    <!-- Bulk Actions -->
    <div class="pgh-bulk-actions">
        <h3>Bulk Actions</h3>
        
        <div class="pgh-bulk-form">
            <div class="pgh-bulk-selection">
                <label>
                    <input type="checkbox" id="select-all-payments"> Select All Payments
                </label>
                <span class="pgh-selected-count">0 payments selected</span>
            </div>
            
            <div class="pgh-bulk-options">
                <select id="bulk-action-select">
                    <option value="">Choose an action...</option>
                    <option value="resend_email">Resend Email Tickets</option>
                    <option value="export_csv">Export as CSV</option>
                    <option value="export_json">Export as JSON</option>
                    <option value="update_status">Update Status</option>
                    <option value="delete">Delete Payments</option>
                </select>
                
                <button type="button" id="execute-bulk-action" class="button button-primary" disabled>
                    Execute Action
                </button>
            </div>
        </div>
    </div>
    
    <!-- Status Update Modal -->
    <div id="status-update-modal" class="pgh-modal" style="display: none;">
        <div class="pgh-modal-content">
            <div class="pgh-modal-header">
                <h2>Update Payment Status</h2>
                <span class="pgh-modal-close">&times;</span>
            </div>
            <div class="pgh-modal-body">
                <form id="status-update-form">
                    <div class="pgh-form-group">
                        <label for="new-status">New Status:</label>
                        <select id="new-status" name="new_status" required>
                            <option value="">Select Status...</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="pgh-form-group">
                        <label for="status-reason">Reason (optional):</label>
                        <textarea id="status-reason" name="status_reason" rows="3" placeholder="Enter reason for status change..."></textarea>
                    </div>
                    <div class="pgh-form-actions">
                        <button type="submit" class="button button-primary">Update Status</button>
                        <button type="button" class="button pgh-modal-close">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Payments Table -->
    <div class="pgh-payments-table">
        <h3>All Payments</h3>
        
        <div class="pgh-table-controls">
            <div class="pgh-table-filters">
                <select id="status-filter">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="failed">Failed</option>
                </select>
                
                <input type="date" id="date-from" placeholder="From Date">
                <input type="date" id="date-to" placeholder="To Date">
                
                <button type="button" id="apply-filters" class="button">Apply Filters</button>
                <button type="button" id="clear-filters" class="button">Clear Filters</button>
            </div>
            
            <div class="pgh-table-pagination">
                <span class="pgh-pagination-info">Showing 1-50 of <?php echo count($payments); ?> payments</span>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped" id="payments-table">
            <thead>
                <tr>
                    <th class="pgh-checkbox-column">
                        <input type="checkbox" id="select-all-header">
                    </th>
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
                <?php foreach ($payments as $payment): ?>
                <tr data-payment-id="<?php echo esc_attr($payment->id); ?>" data-status="<?php echo esc_attr($payment->status); ?>">
                    <td class="pgh-checkbox-column">
                        <input type="checkbox" class="payment-checkbox" value="<?php echo esc_attr($payment->id); ?>">
                    </td>
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
                            View
                        </button>
                        <button class="button button-small pgh-resend-email" data-payment-id="<?php echo esc_attr($payment->id); ?>">
                            Resend
                        </button>
                        <button class="button button-small pgh-delete-payment" data-payment-id="<?php echo esc_attr($payment->id); ?>">
                            Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Processing Status -->
    <div id="bulk-processing-status" class="pgh-processing-status" style="display: none;">
        <div class="pgh-processing-content">
            <div class="pgh-processing-spinner"></div>
            <div class="pgh-processing-text">
                <h3>Processing...</h3>
                <p id="processing-message">Please wait while we process your request.</p>
                <div class="pgh-processing-progress">
                    <div class="pgh-progress-bar">
                        <div class="pgh-progress-fill" style="width: 0%;"></div>
                    </div>
                    <span class="pgh-progress-text">0%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pgh-bulk-actions {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.pgh-bulk-form {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.pgh-bulk-selection {
    display: flex;
    align-items: center;
    gap: 15px;
}

.pgh-selected-count {
    color: #666;
    font-size: 14px;
}

.pgh-bulk-options {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pgh-bulk-options select {
    min-width: 200px;
}

.pgh-payments-table {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.pgh-table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.pgh-table-filters {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pgh-table-filters select,
.pgh-table-filters input {
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.pgh-table-pagination {
    color: #666;
    font-size: 14px;
}

.pgh-checkbox-column {
    width: 40px;
    text-align: center;
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

.pgh-status-failed {
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
    max-width: 500px;
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

.pgh-form-group {
    margin-bottom: 15px;
}

.pgh-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.pgh-form-group select,
.pgh-form-group textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.pgh-form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.pgh-processing-status {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pgh-processing-content {
    background: #fff;
    padding: 40px;
    border-radius: 8px;
    text-align: center;
    max-width: 400px;
    width: 90%;
}

.pgh-processing-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007cba;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.pgh-processing-text h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.pgh-processing-text p {
    margin: 0 0 20px 0;
    color: #666;
}

.pgh-processing-progress {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pgh-progress-bar {
    flex: 1;
    height: 20px;
    background: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
}

.pgh-progress-fill {
    height: 100%;
    background: #007cba;
    transition: width 0.3s ease;
}

.pgh-progress-text {
    font-size: 14px;
    color: #666;
    min-width: 40px;
}

@media (max-width: 768px) {
    .pgh-bulk-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .pgh-table-controls {
        flex-direction: column;
        gap: 15px;
    }
    
    .pgh-table-filters {
        flex-wrap: wrap;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    var selectedPayments = [];
    var currentBulkAction = '';
    
    // Select all functionality
    $('#select-all-payments, #select-all-header').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.payment-checkbox').prop('checked', isChecked);
        updateSelectedCount();
    });
    
    // Individual checkbox change
    $('.payment-checkbox').on('change', function() {
        updateSelectedCount();
        
        // Update select all checkbox
        var totalCheckboxes = $('.payment-checkbox').length;
        var checkedCheckboxes = $('.payment-checkbox:checked').length;
        
        $('#select-all-payments, #select-all-header').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // Update selected count
    function updateSelectedCount() {
        selectedPayments = $('.payment-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        $('.pgh-selected-count').text(selectedPayments.length + ' payments selected');
        
        // Enable/disable bulk action button
        $('#execute-bulk-action').prop('disabled', selectedPayments.length === 0 || $('#bulk-action-select').val() === '');
    }
    
    // Bulk action select change
    $('#bulk-action-select').on('change', function() {
        currentBulkAction = $(this).val();
        $('#execute-bulk-action').prop('disabled', selectedPayments.length === 0 || currentBulkAction === '');
    });
    
    // Execute bulk action
    $('#execute-bulk-action').on('click', function() {
        if (selectedPayments.length === 0 || !currentBulkAction) {
            alert('Please select payments and choose an action.');
            return;
        }
        
        if (currentBulkAction === 'update_status') {
            $('#status-update-modal').show();
        } else {
            executeBulkAction(currentBulkAction, selectedPayments);
        }
    });
    
    // Execute bulk action
    function executeBulkAction(action, paymentIds) {
        showProcessingStatus('Processing ' + action + '...');
        
        $.ajax({
            url: pgh_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pgh_bulk_process',
                bulk_action: action,
                payment_ids: paymentIds,
                nonce: pgh_ajax.nonce
            },
            success: function(response) {
                hideProcessingStatus();
                if (response.success) {
                    alert('Bulk action completed successfully!');
                    location.reload();
                } else {
                    alert('Bulk action failed: ' + response.data);
                }
            },
            error: function() {
                hideProcessingStatus();
                alert('An error occurred while processing the bulk action.');
            }
        });
    }
    
    // Status update form
    $('#status-update-form').on('submit', function(e) {
        e.preventDefault();
        
        var newStatus = $('#new-status').val();
        var reason = $('#status-reason').val();
        
        if (!newStatus) {
            alert('Please select a status.');
            return;
        }
        
        $('#status-update-modal').hide();
        
        showProcessingStatus('Updating payment status...');
        
        $.ajax({
            url: pgh_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pgh_bulk_process',
                bulk_action: 'update_status',
                payment_ids: selectedPayments,
                new_status: newStatus,
                status_reason: reason,
                nonce: pgh_ajax.nonce
            },
            success: function(response) {
                hideProcessingStatus();
                if (response.success) {
                    alert('Status updated successfully!');
                    location.reload();
                } else {
                    alert('Status update failed: ' + response.data);
                }
            },
            error: function() {
                hideProcessingStatus();
                alert('An error occurred while updating the status.');
            }
        });
    });
    
    // Show processing status
    function showProcessingStatus(message) {
        $('#processing-message').text(message);
        $('#bulk-processing-status').show();
    }
    
    // Hide processing status
    function hideProcessingStatus() {
        $('#bulk-processing-status').hide();
    }
    
    // Close modal
    $('.pgh-modal-close').on('click', function() {
        $(this).closest('.pgh-modal').hide();
    });
    
    // Click outside modal to close
    $('.pgh-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // Individual actions
    $('.pgh-view-details').on('click', function() {
        var paymentId = $(this).data('payment-id');
        // Implement view details functionality
        alert('View details for payment ID: ' + paymentId);
    });
    
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
                button.prop('disabled', false).text('Resend');
            }
        });
    });
    
    $('.pgh-delete-payment').on('click', function() {
        var paymentId = $(this).data('payment-id');
        
        if (confirm('Are you sure you want to delete this payment?')) {
            $.ajax({
                url: pgh_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pgh_delete_payment',
                    payment_id: paymentId,
                    nonce: pgh_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Payment deleted successfully!');
                        location.reload();
                    } else {
                        alert('Failed to delete payment: ' + response.data);
                    }
                }
            });
        }
    });
    
    // Table filters
    $('#apply-filters').on('click', function() {
        var status = $('#status-filter').val();
        var dateFrom = $('#date-from').val();
        var dateTo = $('#date-to').val();
        
        // Implement filtering logic
        filterTable(status, dateFrom, dateTo);
    });
    
    $('#clear-filters').on('click', function() {
        $('#status-filter').val('');
        $('#date-from').val('');
        $('#date-to').val('');
        filterTable('', '', '');
    });
    
    function filterTable(status, dateFrom, dateTo) {
        $('#payments-table tbody tr').each(function() {
            var row = $(this);
            var rowStatus = row.data('status');
            var rowDate = row.find('td:nth-child(8)').text();
            
            var showRow = true;
            
            if (status && rowStatus !== status) {
                showRow = false;
            }
            
            if (dateFrom || dateTo) {
                // Implement date filtering logic
                // This would need to be customized based on your date format
            }
            
            row.toggle(showRow);
        });
    }
});
</script>
