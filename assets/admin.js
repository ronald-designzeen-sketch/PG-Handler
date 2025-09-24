/**
 * PayGate Handler Pro - Admin JavaScript
 * 
 * @package PayGateHandler
 * @since 2.1
 */

(function($) {
    'use strict';

    // Main admin object
    var PayGateAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.bindEvents();
            this.initComponents();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Payment details modal
            $(document).on('click', '.pgh-view-details', this.showPaymentDetails);
            
            // Resend email
            $(document).on('click', '.pgh-resend-email', this.resendEmail);
            
            // Delete payment
            $(document).on('click', '.pgh-delete-payment', this.deletePayment);
            
            // Modal close
            $(document).on('click', '.pgh-modal-close, .pgh-modal', this.closeModal);
            
            // Tab switching
            $(document).on('click', '.nav-tab', this.switchTab);
            
            // Test email
            $(document).on('click', '#send-test-email', this.sendTestEmail);
            
            // Bulk actions
            $(document).on('change', '.payment-checkbox, #select-all-payments, #select-all-header', this.updateBulkSelection);
            $(document).on('change', '#bulk-action-select', this.updateBulkAction);
            $(document).on('click', '#execute-bulk-action', this.executeBulkAction);
            
            // Status update form
            $(document).on('submit', '#status-update-form', this.updatePaymentStatus);
            
            // Table filters
            $(document).on('click', '#apply-filters', this.applyFilters);
            $(document).on('click', '#clear-filters', this.clearFilters);
            
            // Health check actions
            $(document).on('click', 'button[onclick="runHealthCheck()"]', this.runHealthCheck);
            $(document).on('click', 'button[onclick="clearErrorLog()"]', this.clearErrorLog);
            $(document).on('click', 'button[onclick="optimizeDatabase()"]', this.optimizeDatabase);
            $(document).on('click', 'button[onclick="exportSystemInfo()"]', this.exportSystemInfo);
        },
        
        /**
         * Initialize components
         */
        initComponents: function() {
            this.initCharts();
            this.initTooltips();
            this.initDatePickers();
        },
        
        /**
         * Show payment details modal
         */
        showPaymentDetails: function(e) {
            e.preventDefault();
            
            var paymentId = $(this).data('payment-id');
            var $modal = $('#pgh-payment-modal');
            var $content = $('#pgh-payment-details');
            
            // Show loading state
            $content.html('<div class="pgh-loading">Loading payment details...</div>');
            $modal.show();
            
            // Fetch payment details
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
                        var html = '<div class="pgh-payment-details">';
                        
                        // Basic info
                        html += '<div class="pgh-detail-section">';
                        html += '<h4>Payment Information</h4>';
                        html += '<div class="pgh-detail-grid">';
                        html += '<div class="pgh-detail-item"><strong>ID:</strong> ' + payment.id + '</div>';
                        html += '<div class="pgh-detail-item"><strong>Name:</strong> ' + payment.name + '</div>';
                        html += '<div class="pgh-detail-item"><strong>Email:</strong> ' + payment.email + '</div>';
                        html += '<div class="pgh-detail-item"><strong>Item:</strong> ' + payment.item_name + '</div>';
                        html += '<div class="pgh-detail-item"><strong>Amount:</strong> R ' + (payment.amount / 100).toFixed(2) + '</div>';
                        html += '<div class="pgh-detail-item"><strong>Status:</strong> <span class="pgh-status pgh-status-' + payment.status + '">' + payment.status + '</span></div>';
                        html += '<div class="pgh-detail-item"><strong>Reference:</strong> ' + payment.reference + '</div>';
                        html += '<div class="pgh-detail-item"><strong>Created:</strong> ' + payment.created_at + '</div>';
                        html += '</div>';
                        html += '</div>';
                        
                        // Form data if available
                        if (payment.form_data) {
                            html += '<div class="pgh-detail-section">';
                            html += '<h4>Form Data</h4>';
                            html += '<pre class="pgh-form-data">' + JSON.stringify(JSON.parse(payment.form_data), null, 2) + '</pre>';
                            html += '</div>';
                        }
                        
                        // Error log if available
                        if (payment.error_log) {
                            html += '<div class="pgh-detail-section">';
                            html += '<h4>Error Log</h4>';
                            html += '<pre class="pgh-error-log">' + payment.error_log + '</pre>';
                            html += '</div>';
                        }
                        
                        html += '</div>';
                        $content.html(html);
                    } else {
                        $content.html('<div class="pgh-error">Error loading payment details: ' + response.data + '</div>');
                    }
                },
                error: function() {
                    $content.html('<div class="pgh-error">Failed to load payment details. Please try again.</div>');
                }
            });
        },
        
        /**
         * Resend email
         */
        resendEmail: function(e) {
            e.preventDefault();
            
            var paymentId = $(this).data('payment-id');
            var $button = $(this);
            
            if (!confirm('Are you sure you want to resend the email for this payment?')) {
                return;
            }
            
            $button.prop('disabled', true).text('Sending...');
            
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
                        PayGateAdmin.showNotice('Email sent successfully!', 'success');
                    } else {
                        PayGateAdmin.showNotice('Failed to send email: ' + response.data, 'error');
                    }
                },
                error: function() {
                    PayGateAdmin.showNotice('An error occurred while sending the email.', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Resend');
                }
            });
        },
        
        /**
         * Delete payment
         */
        deletePayment: function(e) {
            e.preventDefault();
            
            var paymentId = $(this).data('payment-id');
            
            if (!confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
                return;
            }
            
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
                        PayGateAdmin.showNotice('Payment deleted successfully!', 'success');
                        location.reload();
                    } else {
                        PayGateAdmin.showNotice('Failed to delete payment: ' + response.data, 'error');
                    }
                },
                error: function() {
                    PayGateAdmin.showNotice('An error occurred while deleting the payment.', 'error');
                }
            });
        },
        
        /**
         * Close modal
         */
        closeModal: function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        },
        
        /**
         * Switch tabs
         */
        switchTab: function(e) {
            e.preventDefault();
            
            var target = $(this).attr('href');
            
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            $('.pgh-tab-content').removeClass('active');
            $(target).addClass('active');
        },
        
        /**
         * Send test email
         */
        sendTestEmail: function(e) {
            e.preventDefault();
            
            var email = $('#test-email').val();
            
            if (!email) {
                PayGateAdmin.showNotice('Please enter an email address', 'error');
                return;
            }
            
            var $button = $(this);
            $button.prop('disabled', true).text('Sending...');
            
            $.ajax({
                url: pgh_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pgh_test_email',
                    test_email: email,
                    nonce: pgh_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        PayGateAdmin.showNotice('Test email sent successfully!', 'success');
                    } else {
                        PayGateAdmin.showNotice('Failed to send test email: ' + response.data, 'error');
                    }
                },
                error: function() {
                    PayGateAdmin.showNotice('An error occurred while sending the test email.', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Send Test Email');
                }
            });
        },
        
        /**
         * Update bulk selection
         */
        updateBulkSelection: function() {
            var selectedPayments = $('.payment-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            
            $('.pgh-selected-count').text(selectedPayments.length + ' payments selected');
            
            // Update select all checkbox
            var totalCheckboxes = $('.payment-checkbox').length;
            var checkedCheckboxes = $('.payment-checkbox:checked').length;
            
            $('#select-all-payments, #select-all-header').prop('checked', totalCheckboxes === checkedCheckboxes);
            
            // Enable/disable bulk action button
            $('#execute-bulk-action').prop('disabled', selectedPayments.length === 0 || $('#bulk-action-select').val() === '');
        },
        
        /**
         * Update bulk action
         */
        updateBulkAction: function() {
            var selectedPayments = $('.payment-checkbox:checked').length;
            $('#execute-bulk-action').prop('disabled', selectedPayments === 0 || $(this).val() === '');
        },
        
        /**
         * Execute bulk action
         */
        executeBulkAction: function(e) {
            e.preventDefault();
            
            var selectedPayments = $('.payment-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            
            var action = $('#bulk-action-select').val();
            
            if (selectedPayments.length === 0 || !action) {
                PayGateAdmin.showNotice('Please select payments and choose an action.', 'error');
                return;
            }
            
            if (action === 'update_status') {
                $('#status-update-modal').show();
            } else {
                PayGateAdmin.executeBulkAction(action, selectedPayments);
            }
        },
        
        /**
         * Execute bulk action
         */
        executeBulkAction: function(action, paymentIds) {
            PayGateAdmin.showProcessingStatus('Processing ' + action + '...');
            
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
                    PayGateAdmin.hideProcessingStatus();
                    if (response.success) {
                        PayGateAdmin.showNotice('Bulk action completed successfully!', 'success');
                        location.reload();
                    } else {
                        PayGateAdmin.showNotice('Bulk action failed: ' + response.data, 'error');
                    }
                },
                error: function() {
                    PayGateAdmin.hideProcessingStatus();
                    PayGateAdmin.showNotice('An error occurred while processing the bulk action.', 'error');
                }
            });
        },
        
        /**
         * Update payment status
         */
        updatePaymentStatus: function(e) {
            e.preventDefault();
            
            var newStatus = $('#new-status').val();
            var reason = $('#status-reason').val();
            var selectedPayments = $('.payment-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (!newStatus) {
                PayGateAdmin.showNotice('Please select a status.', 'error');
                return;
            }
            
            $('#status-update-modal').hide();
            
            PayGateAdmin.showProcessingStatus('Updating payment status...');
            
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
                    PayGateAdmin.hideProcessingStatus();
                    if (response.success) {
                        PayGateAdmin.showNotice('Status updated successfully!', 'success');
                        location.reload();
                    } else {
                        PayGateAdmin.showNotice('Status update failed: ' + response.data, 'error');
                    }
                },
                error: function() {
                    PayGateAdmin.hideProcessingStatus();
                    PayGateAdmin.showNotice('An error occurred while updating the status.', 'error');
                }
            });
        },
        
        /**
         * Apply table filters
         */
        applyFilters: function() {
            var status = $('#status-filter').val();
            var dateFrom = $('#date-from').val();
            var dateTo = $('#date-to').val();
            
            PayGateAdmin.filterTable(status, dateFrom, dateTo);
        },
        
        /**
         * Clear table filters
         */
        clearFilters: function() {
            $('#status-filter').val('');
            $('#date-from').val('');
            $('#date-to').val('');
            PayGateAdmin.filterTable('', '', '');
        },
        
        /**
         * Filter table
         */
        filterTable: function(status, dateFrom, dateTo) {
            $('#payments-table tbody tr').each(function() {
                var $row = $(this);
                var rowStatus = $row.data('status');
                var rowDate = $row.find('td:nth-child(8)').text();
                
                var showRow = true;
                
                if (status && rowStatus !== status) {
                    showRow = false;
                }
                
                if (dateFrom || dateTo) {
                    // Implement date filtering logic
                    // This would need to be customized based on your date format
                }
                
                $row.toggle(showRow);
            });
        },
        
        /**
         * Run health check
         */
        runHealthCheck: function() {
            var $button = $('button[onclick="runHealthCheck()"]');
            $button.prop('disabled', true).text('Running...');
            
            $.ajax({
                url: pgh_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pgh_run_health_check',
                    nonce: pgh_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        PayGateAdmin.showNotice('Health check completed successfully!', 'success');
                        location.reload();
                    } else {
                        PayGateAdmin.showNotice('Health check failed: ' + response.data, 'error');
                    }
                },
                error: function() {
                    PayGateAdmin.showNotice('An error occurred while running the health check.', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Run Health Check');
                }
            });
        },
        
        /**
         * Clear error log
         */
        clearErrorLog: function() {
            if (confirm('Are you sure you want to clear the error log?')) {
                $.ajax({
                    url: pgh_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pgh_clear_error_log',
                        nonce: pgh_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            PayGateAdmin.showNotice('Error log cleared successfully!', 'success');
                            location.reload();
                        } else {
                            PayGateAdmin.showNotice('Failed to clear error log: ' + response.data, 'error');
                        }
                    },
                    error: function() {
                        PayGateAdmin.showNotice('An error occurred while clearing the error log.', 'error');
                    }
                });
            }
        },
        
        /**
         * Optimize database
         */
        optimizeDatabase: function() {
            if (confirm('Are you sure you want to optimize the database? This may take a few moments.')) {
                var $button = $('button[onclick="optimizeDatabase()"]');
                $button.prop('disabled', true).text('Optimizing...');
                
                $.ajax({
                    url: pgh_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pgh_optimize_database',
                        nonce: pgh_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            PayGateAdmin.showNotice('Database optimized successfully!', 'success');
                        } else {
                            PayGateAdmin.showNotice('Database optimization failed: ' + response.data, 'error');
                        }
                    },
                    error: function() {
                        PayGateAdmin.showNotice('An error occurred while optimizing the database.', 'error');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('Optimize Database');
                    }
                });
            }
        },
        
        /**
         * Export system info
         */
        exportSystemInfo: function() {
            var url = pgh_ajax.ajax_url + '?action=pgh_export_system_info&nonce=' + pgh_ajax.nonce;
            window.open(url, '_blank');
        },
        
        /**
         * Initialize charts
         */
        initCharts: function() {
            if (typeof Chart === 'undefined') {
                return;
            }
            
            // Payment trends chart
            var paymentsCtx = document.getElementById('payments-chart');
            if (paymentsCtx) {
                new Chart(paymentsCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: window.pgh_chart_labels || [],
                        datasets: [{
                            label: 'Payments',
                            data: window.pgh_chart_payments || [],
                            borderColor: '#007cba',
                            backgroundColor: 'rgba(0, 124, 186, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // Revenue trends chart
            var revenueCtx = document.getElementById('revenue-chart');
            if (revenueCtx) {
                new Chart(revenueCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: window.pgh_chart_labels || [],
                        datasets: [{
                            label: 'Revenue (R)',
                            data: window.pgh_chart_revenue || [],
                            backgroundColor: '#28a745',
                            borderColor: '#1e7e34',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        },
        
        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            $('[data-tooltip]').each(function() {
                var $this = $(this);
                var tooltip = $this.data('tooltip');
                
                $this.attr('title', tooltip);
            });
        },
        
        /**
         * Initialize date pickers
         */
        initDatePickers: function() {
            $('.pgh-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true
            });
        },
        
        /**
         * Show processing status
         */
        showProcessingStatus: function(message) {
            $('#processing-message').text(message);
            $('#bulk-processing-status').show();
        },
        
        /**
         * Hide processing status
         */
        hideProcessingStatus: function() {
            $('#bulk-processing-status').hide();
        },
        
        /**
         * Show notice
         */
        showNotice: function(message, type) {
            type = type || 'info';
            
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            
            $('.wrap h1').after($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut();
            }, 5000);
        },
        
        /**
         * Export data
         */
        exportData: function(format) {
            var url = pgh_ajax.ajax_url + '?action=pgh_export_data&format=' + format + '&nonce=' + pgh_ajax.nonce;
            window.open(url, '_blank');
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        PayGateAdmin.init();
    });
    
    // Make functions globally available
    window.runHealthCheck = PayGateAdmin.runHealthCheck;
    window.clearErrorLog = PayGateAdmin.clearErrorLog;
    window.optimizeDatabase = PayGateAdmin.optimizeDatabase;
    window.exportSystemInfo = PayGateAdmin.exportSystemInfo;
    window.exportData = PayGateAdmin.exportData;
    
})(jQuery);
