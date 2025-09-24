<?php
/**
 * Notifications Page Template
 * 
 * @package PayGateHandler
 * @since 2.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin = PayGateHandlerPro::get_instance();
$settings = $plugin->get_settings();

// Handle notification settings save
if (isset($_POST['save_notifications']) && wp_verify_nonce($_POST['pgh_notifications_nonce'], 'pgh_notifications')) {
    $notification_settings = array(
        'email_alerts' => intval($_POST['email_alerts']),
        'failed_payment_alerts' => intval($_POST['failed_payment_alerts']),
        'high_value_alerts' => intval($_POST['high_value_alerts']),
        'high_value_threshold' => intval($_POST['high_value_threshold']),
        'daily_summary' => intval($_POST['daily_summary']),
        'weekly_summary' => intval($_POST['weekly_summary']),
        'admin_email' => sanitize_email($_POST['admin_email']),
        'system_health_alerts' => intval($_POST['system_health_alerts']),
        'rate_limit_alerts' => intval($_POST['rate_limit_alerts']),
        'email_delivery_alerts' => intval($_POST['email_delivery_alerts'])
    );
    
    $current_settings = $plugin->get_settings();
    $current_settings['notifications'] = $notification_settings;
    update_option('pgh_settings', $current_settings);
    
    echo '<div class="notice notice-success"><p>Notification settings saved successfully!</p></div>';
}

$notification_settings = $settings['notifications'] ?? array();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">🔔 Smart Notifications</h1>
    <a href="<?php echo admin_url('admin.php?page=pgh_admin'); ?>" class="page-title-action">← Back to Dashboard</a>
    
    <hr class="wp-header-end">
    
    <form method="post" action="">
        <?php wp_nonce_field('pgh_notifications', 'pgh_notifications_nonce'); ?>
        
        <div class="pgh-notifications-container">
            <!-- Email Alerts Section -->
            <div class="pgh-notification-section">
                <h2>📧 Email Alerts</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Email Alerts</th>
                        <td>
                            <label>
                                <input type="checkbox" name="email_alerts" value="1" <?php checked($notification_settings['email_alerts'] ?? 1); ?>>
                                Send email notifications for important events
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Admin Email</th>
                        <td>
                            <input type="email" name="admin_email" value="<?php echo esc_attr($notification_settings['admin_email'] ?? get_bloginfo('admin_email')); ?>" class="regular-text" required>
                            <p class="description">Email address to receive notifications</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Payment Alerts -->
            <div class="pgh-notification-section">
                <h2>💳 Payment Alerts</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Failed Payment Alerts</th>
                        <td>
                            <label>
                                <input type="checkbox" name="failed_payment_alerts" value="1" <?php checked($notification_settings['failed_payment_alerts'] ?? 1); ?>>
                                Get notified when payments fail
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">High Value Transaction Alerts</th>
                        <td>
                            <label>
                                <input type="checkbox" name="high_value_alerts" value="1" <?php checked($notification_settings['high_value_alerts'] ?? 1); ?>>
                                Get notified for high-value transactions
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">High Value Threshold</th>
                        <td>
                            <input type="number" name="high_value_threshold" value="<?php echo esc_attr($notification_settings['high_value_threshold'] ?? 10000); ?>" class="small-text" min="0">
                            <span class="description">Amount in cents (e.g., 10000 = R100.00)</span>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Summary Reports -->
            <div class="pgh-notification-section">
                <h2>📊 Summary Reports</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Daily Summary</th>
                        <td>
                            <label>
                                <input type="checkbox" name="daily_summary" value="1" <?php checked($notification_settings['daily_summary'] ?? 0); ?>>
                                Receive daily payment summary emails
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Weekly Summary</th>
                        <td>
                            <label>
                                <input type="checkbox" name="weekly_summary" value="1" <?php checked($notification_settings['weekly_summary'] ?? 1); ?>>
                                Receive weekly payment summary emails
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- System Alerts -->
            <div class="pgh-notification-section">
                <h2>⚡ System Alerts</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">System Health Alerts</th>
                        <td>
                            <label>
                                <input type="checkbox" name="system_health_alerts" value="1" <?php checked($notification_settings['system_health_alerts'] ?? 1); ?>>
                                Get notified of system health issues
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Rate Limit Alerts</th>
                        <td>
                            <label>
                                <input type="checkbox" name="rate_limit_alerts" value="1" <?php checked($notification_settings['rate_limit_alerts'] ?? 1); ?>>
                                Get notified when rate limits are exceeded
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Email Delivery Alerts</th>
                        <td>
                            <label>
                                <input type="checkbox" name="email_delivery_alerts" value="1" <?php checked($notification_settings['email_delivery_alerts'] ?? 1); ?>>
                                Get notified of email delivery issues
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Test Notifications -->
            <div class="pgh-notification-section">
                <h2>🧪 Test Notifications</h2>
                
                <div class="pgh-test-notifications">
                    <button type="button" id="test-email-alert" class="button">Test Email Alert</button>
                    <button type="button" id="test-payment-alert" class="button">Test Payment Alert</button>
                    <button type="button" id="test-system-alert" class="button">Test System Alert</button>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="save_notifications" class="button-primary" value="Save Notification Settings">
        </p>
    </form>
    
    <!-- Recent Notifications -->
    <div class="pgh-recent-notifications">
        <h2>📋 Recent Notifications</h2>
        <div id="notifications-list">
            <!-- Notifications will be loaded via AJAX -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test notification buttons
    document.getElementById('test-email-alert').addEventListener('click', function() {
        if (confirm('Send test email alert?')) {
            // AJAX call to send test notification
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=pgh_test_notification&type=email&nonce=' + pgh_ajax.nonce
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test email alert sent!');
                } else {
                    alert('Failed to send test alert: ' + data.data);
                }
            });
        }
    });
    
    document.getElementById('test-payment-alert').addEventListener('click', function() {
        if (confirm('Send test payment alert?')) {
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=pgh_test_notification&type=payment&nonce=' + pgh_ajax.nonce
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test payment alert sent!');
                } else {
                    alert('Failed to send test alert: ' + data.data);
                }
            });
        }
    });
    
    document.getElementById('test-system-alert').addEventListener('click', function() {
        if (confirm('Send test system alert?')) {
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=pgh_test_notification&type=system&nonce=' + pgh_ajax.nonce
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test system alert sent!');
                } else {
                    alert('Failed to send test alert: ' + data.data);
                }
            });
        }
    });
    
    // Load recent notifications
    loadRecentNotifications();
    
    function loadRecentNotifications() {
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=pgh_get_notifications&nonce=' + pgh_ajax.nonce
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('notifications-list').innerHTML = data.data;
            }
        });
    }
});
</script>
