<?php
/**
 * Security Page Template
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

// Handle security settings save
if (isset($_POST['save_security']) && wp_verify_nonce($_POST['pgh_security_nonce'], 'pgh_security')) {
    $security_settings = array(
        'two_factor_auth' => intval($_POST['two_factor_auth']),
        'ip_whitelist' => sanitize_textarea_field($_POST['ip_whitelist']),
        'admin_session_timeout' => intval($_POST['admin_session_timeout']),
        'audit_logging' => intval($_POST['audit_logging']),
        'encrypt_sensitive_data' => intval($_POST['encrypt_sensitive_data']),
        'strong_password_required' => intval($_POST['strong_password_required']),
        'login_attempts_limit' => intval($_POST['login_attempts_limit']),
        'lockout_duration' => intval($_POST['lockout_duration']),
        'security_headers' => intval($_POST['security_headers']),
        'file_upload_scan' => intval($_POST['file_upload_scan'])
    );
    
    $current_settings = $plugin->get_settings();
    $current_settings['security'] = $security_settings;
    update_option('pgh_settings', $current_settings);
    
    echo '<div class="notice notice-success"><p>Security settings saved successfully!</p></div>';
}

$security_settings = $settings['security'] ?? array();
$security_health = $plugin->get_security_health();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">🛡️ Security Center</h1>
    <a href="<?php echo admin_url('admin.php?page=pgh_admin'); ?>" class="page-title-action">← Back to Dashboard</a>
    
    <hr class="wp-header-end">
    
    <!-- Security Status Overview -->
    <div class="pgh-security-overview">
        <div class="pgh-security-status">
            <div class="pgh-security-score">
                <h2>Security Score: <?php echo $security_health['score']; ?>/100</h2>
                <div class="pgh-score-bar">
                    <div class="pgh-score-fill" style="width: <?php echo $security_health['score']; ?>%"></div>
                </div>
                <p class="pgh-score-description"><?php echo $security_health['description']; ?></p>
            </div>
        </div>
        
        <div class="pgh-security-alerts">
            <h3>🚨 Security Alerts</h3>
            <?php if (!empty($security_health['alerts'])): ?>
                <?php foreach ($security_health['alerts'] as $alert): ?>
                    <div class="pgh-alert pgh-alert-<?php echo $alert['level']; ?>">
                        <strong><?php echo esc_html($alert['title']); ?></strong>
                        <p><?php echo esc_html($alert['message']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="pgh-alert pgh-alert-success">
                    <strong>✅ All Good!</strong>
                    <p>No security issues detected.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <form method="post" action="">
        <?php wp_nonce_field('pgh_security', 'pgh_security_nonce'); ?>
        
        <div class="pgh-security-container">
            <!-- Authentication Security -->
            <div class="pgh-security-section">
                <h2>🔐 Authentication Security</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Two-Factor Authentication</th>
                        <td>
                            <label>
                                <input type="checkbox" name="two_factor_auth" value="1" <?php checked($security_settings['two_factor_auth'] ?? 0); ?>>
                                Enable 2FA for admin access
                            </label>
                            <p class="description">Requires additional verification for admin access</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Strong Password Required</th>
                        <td>
                            <label>
                                <input type="checkbox" name="strong_password_required" value="1" <?php checked($security_settings['strong_password_required'] ?? 1); ?>>
                                Enforce strong password requirements
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Login Attempts Limit</th>
                        <td>
                            <input type="number" name="login_attempts_limit" value="<?php echo esc_attr($security_settings['login_attempts_limit'] ?? 5); ?>" class="small-text" min="1" max="20">
                            <p class="description">Maximum failed login attempts before lockout</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Lockout Duration (minutes)</th>
                        <td>
                            <input type="number" name="lockout_duration" value="<?php echo esc_attr($security_settings['lockout_duration'] ?? 15); ?>" class="small-text" min="1" max="1440">
                            <p class="description">How long to lock out after failed attempts</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Admin Session Timeout (minutes)</th>
                        <td>
                            <input type="number" name="admin_session_timeout" value="<?php echo esc_attr($security_settings['admin_session_timeout'] ?? 30); ?>" class="small-text" min="5" max="480">
                            <p class="description">Auto-logout after inactivity</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Access Control -->
            <div class="pgh-security-section">
                <h2>🌐 Access Control</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">IP Whitelist</th>
                        <td>
                            <textarea name="ip_whitelist" rows="5" cols="50" class="large-text" placeholder="192.168.1.1&#10;10.0.0.0/8&#10;203.0.113.0/24"><?php echo esc_textarea($security_settings['ip_whitelist'] ?? ''); ?></textarea>
                            <p class="description">One IP address or CIDR range per line. Leave empty to allow all IPs.</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Data Protection -->
            <div class="pgh-security-section">
                <h2>🔒 Data Protection</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Encrypt Sensitive Data</th>
                        <td>
                            <label>
                                <input type="checkbox" name="encrypt_sensitive_data" value="1" <?php checked($security_settings['encrypt_sensitive_data'] ?? 1); ?>>
                                Encrypt sensitive payment data in database
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Audit Logging</th>
                        <td>
                            <label>
                                <input type="checkbox" name="audit_logging" value="1" <?php checked($security_settings['audit_logging'] ?? 1); ?>>
                                Log all admin actions and payment events
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Advanced Security -->
            <div class="pgh-security-section">
                <h2>⚡ Advanced Security</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Security Headers</th>
                        <td>
                            <label>
                                <input type="checkbox" name="security_headers" value="1" <?php checked($security_settings['security_headers'] ?? 1); ?>>
                                Add security headers to responses
                            </label>
                            <p class="description">Adds X-Frame-Options, X-Content-Type-Options, etc.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">File Upload Scanning</th>
                        <td>
                            <label>
                                <input type="checkbox" name="file_upload_scan" value="1" <?php checked($security_settings['file_upload_scan'] ?? 0); ?>>
                                Scan uploaded files for malware
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="save_security" class="button-primary" value="Save Security Settings">
        </p>
    </form>
    
    <!-- Security Logs -->
    <div class="pgh-security-logs">
        <h2>📋 Security Logs</h2>
        <div class="pgh-log-filters">
            <select id="log-level-filter">
                <option value="">All Levels</option>
                <option value="info">Info</option>
                <option value="warning">Warning</option>
                <option value="error">Error</option>
                <option value="critical">Critical</option>
            </select>
            
            <select id="log-type-filter">
                <option value="">All Types</option>
                <option value="login">Login Attempts</option>
                <option value="payment">Payment Events</option>
                <option value="admin">Admin Actions</option>
                <option value="security">Security Events</option>
            </select>
            
            <button type="button" id="refresh-logs" class="button">Refresh</button>
            <button type="button" id="clear-logs" class="button">Clear Logs</button>
        </div>
        
        <div id="security-logs-list">
            <!-- Logs will be loaded via AJAX -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load security logs
    loadSecurityLogs();
    
    // Log filters
    document.getElementById('log-level-filter').addEventListener('change', loadSecurityLogs);
    document.getElementById('log-type-filter').addEventListener('change', loadSecurityLogs);
    document.getElementById('refresh-logs').addEventListener('click', loadSecurityLogs);
    
    document.getElementById('clear-logs').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear all security logs?')) {
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=pgh_clear_security_logs&nonce=' + pgh_ajax.nonce
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadSecurityLogs();
                    alert('Security logs cleared successfully!');
                } else {
                    alert('Failed to clear logs: ' + data.data);
                }
            });
        }
    });
    
    function loadSecurityLogs() {
        const level = document.getElementById('log-level-filter').value;
        const type = document.getElementById('log-type-filter').value;
        
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=pgh_get_security_logs&level=' + level + '&type=' + type + '&nonce=' + pgh_ajax.nonce
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('security-logs-list').innerHTML = data.data;
            }
        });
    }
});
</script>
