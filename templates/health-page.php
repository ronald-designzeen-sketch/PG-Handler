<?php
/**
 * System Health Page Template
 * 
 * @package PayGateHandler
 * @since 2.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin = PayGateHandlerPro::get_instance();
$health_data = $plugin->get_system_health();
$errors = $plugin->get_recent_errors();
?>

<div class="wrap">
    <h1>System Health Monitor</h1>
    
    <!-- Health Status Overview -->
    <div class="pgh-health-overview">
        <div class="pgh-health-status <?php echo $health_data['overall_status']; ?>">
            <div class="pgh-health-icon">
                <?php if ($health_data['overall_status'] === 'healthy'): ?>
                    ✅
                <?php elseif ($health_data['overall_status'] === 'warning'): ?>
                    ⚠️
                <?php else: ?>
                    ❌
                <?php endif; ?>
            </div>
            <div class="pgh-health-content">
                <h2>System Status: <?php echo ucfirst($health_data['overall_status']); ?></h2>
                <p><?php echo esc_html($health_data['status_message']); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Health Checks -->
    <div class="pgh-health-checks">
        <h3>System Checks</h3>
        
        <div class="pgh-check-item">
            <div class="pgh-check-icon <?php echo $health_data['database_status'] ? 'success' : 'error'; ?>">
                <?php echo $health_data['database_status'] ? '✅' : '❌'; ?>
            </div>
            <div class="pgh-check-content">
                <h4>Database Connection</h4>
                <p><?php echo $health_data['database_status'] ? 'Database is accessible and table structure is correct' : 'Database connection failed or table structure is incorrect'; ?></p>
            </div>
        </div>
        
        <div class="pgh-check-item">
            <div class="pgh-check-icon <?php echo $health_data['paygate_config'] ? 'success' : 'error'; ?>">
                <?php echo $health_data['paygate_config'] ? '✅' : '❌'; ?>
            </div>
            <div class="pgh-check-content">
                <h4>PayGate Configuration</h4>
                <p><?php echo $health_data['paygate_config'] ? 'PayGate settings are properly configured' : 'PayGate settings are missing or invalid'; ?></p>
            </div>
        </div>
        
        <div class="pgh-check-item">
            <div class="pgh-check-icon <?php echo $health_data['email_config'] ? 'success' : 'error'; ?>">
                <?php echo $health_data['email_config'] ? '✅' : '❌'; ?>
            </div>
            <div class="pgh-check-content">
                <h4>Email Configuration</h4>
                <p><?php echo $health_data['email_config'] ? 'Email settings are properly configured' : 'Email settings are missing or invalid'; ?></p>
            </div>
        </div>
        
        <div class="pgh-check-item">
            <div class="pgh-check-icon <?php echo $health_data['file_permissions'] ? 'success' : 'error'; ?>">
                <?php echo $health_data['file_permissions'] ? '✅' : '❌'; ?>
            </div>
            <div class="pgh-check-content">
                <h4>File Permissions</h4>
                <p><?php echo $health_data['file_permissions'] ? 'File permissions are correct' : 'File permissions need to be adjusted'; ?></p>
            </div>
        </div>
        
        <div class="pgh-check-item">
            <div class="pgh-check-icon <?php echo $health_data['memory_usage'] ? 'success' : 'warning'; ?>">
                <?php echo $health_data['memory_usage'] ? '✅' : '⚠️'; ?>
            </div>
            <div class="pgh-check-content">
                <h4>Memory Usage</h4>
                <p><?php echo $health_data['memory_usage'] ? 'Memory usage is within acceptable limits' : 'Memory usage is high, consider optimizing'; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Performance Metrics -->
    <div class="pgh-performance-metrics">
        <h3>Performance Metrics</h3>
        
        <div class="pgh-metrics-grid">
            <div class="pgh-metric-item">
                <div class="pgh-metric-label">Database Queries</div>
                <div class="pgh-metric-value"><?php echo number_format($health_data['db_queries']); ?></div>
                <div class="pgh-metric-description">Queries executed in last hour</div>
            </div>
            
            <div class="pgh-metric-item">
                <div class="pgh-metric-label">Average Response Time</div>
                <div class="pgh-metric-value"><?php echo number_format($health_data['avg_response_time'], 2); ?>ms</div>
                <div class="pgh-metric-description">Average payment processing time</div>
            </div>
            
            <div class="pgh-metric-item">
                <div class="pgh-metric-label">Memory Usage</div>
                <div class="pgh-metric-value"><?php echo number_format($health_data['memory_usage_mb'], 1); ?>MB</div>
                <div class="pgh-metric-description">Current memory consumption</div>
            </div>
            
            <div class="pgh-metric-item">
                <div class="pgh-metric-label">Disk Usage</div>
                <div class="pgh-metric-value"><?php echo number_format($health_data['disk_usage_mb'], 1); ?>MB</div>
                <div class="pgh-metric-description">Plugin data storage</div>
            </div>
        </div>
    </div>
    
    <!-- Recent Errors -->
    <div class="pgh-recent-errors">
        <h3>Recent Errors</h3>
        
        <?php if (empty($errors)): ?>
        <div class="pgh-no-errors">
            <p>🎉 No recent errors found! Your system is running smoothly.</p>
        </div>
        <?php else: ?>
        <div class="pgh-errors-list">
            <?php foreach ($errors as $error): ?>
            <div class="pgh-error-item">
                <div class="pgh-error-header">
                    <span class="pgh-error-type"><?php echo esc_html($error['type']); ?></span>
                    <span class="pgh-error-time"><?php echo esc_html($error['time']); ?></span>
                </div>
                <div class="pgh-error-message"><?php echo esc_html($error['message']); ?></div>
                <?php if (!empty($error['context'])): ?>
                <div class="pgh-error-context">
                    <strong>Context:</strong> <?php echo esc_html($error['context']); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- System Information -->
    <div class="pgh-system-info">
        <h3>System Information</h3>
        
        <div class="pgh-info-grid">
            <div class="pgh-info-item">
                <strong>WordPress Version:</strong> <?php echo get_bloginfo('version'); ?>
            </div>
            <div class="pgh-info-item">
                <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
            </div>
            <div class="pgh-info-item">
                <strong>Plugin Version:</strong> <?php echo PGH_VERSION; ?>
            </div>
            <div class="pgh-info-item">
                <strong>Database Version:</strong> <?php echo $wpdb->db_version(); ?>
            </div>
            <div class="pgh-info-item">
                <strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
            </div>
            <div class="pgh-info-item">
                <strong>Max Execution Time:</strong> <?php echo ini_get('max_execution_time'); ?>s
            </div>
            <div class="pgh-info-item">
                <strong>Memory Limit:</strong> <?php echo ini_get('memory_limit'); ?>
            </div>
            <div class="pgh-info-item">
                <strong>Upload Max Size:</strong> <?php echo ini_get('upload_max_filesize'); ?>
            </div>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="pgh-health-actions">
        <h3>System Actions</h3>
        
        <div class="pgh-action-buttons">
            <button class="button button-primary" onclick="runHealthCheck()">Run Health Check</button>
            <button class="button" onclick="clearErrorLog()">Clear Error Log</button>
            <button class="button" onclick="optimizeDatabase()">Optimize Database</button>
            <button class="button" onclick="exportSystemInfo()">Export System Info</button>
        </div>
    </div>
</div>

<style>
.pgh-health-overview {
    margin: 20px 0;
}

.pgh-health-status {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pgh-health-status.healthy {
    background: #d4edda;
    border-left: 4px solid #28a745;
}

.pgh-health-status.warning {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
}

.pgh-health-status.error {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
}

.pgh-health-icon {
    font-size: 32px;
}

.pgh-health-content h2 {
    margin: 0 0 5px 0;
    color: #333;
}

.pgh-health-content p {
    margin: 0;
    color: #666;
}

.pgh-health-checks {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.pgh-check-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.pgh-check-item:last-child {
    border-bottom: none;
}

.pgh-check-icon {
    font-size: 20px;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.pgh-check-icon.success {
    background: #d4edda;
    color: #155724;
}

.pgh-check-icon.error {
    background: #f8d7da;
    color: #721c24;
}

.pgh-check-icon.warning {
    background: #fff3cd;
    color: #856404;
}

.pgh-check-content h4 {
    margin: 0 0 5px 0;
    color: #333;
}

.pgh-check-content p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.pgh-performance-metrics {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.pgh-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.pgh-metric-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.pgh-metric-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.pgh-metric-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.pgh-metric-description {
    font-size: 11px;
    color: #999;
}

.pgh-recent-errors {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.pgh-no-errors {
    text-align: center;
    padding: 40px;
    color: #28a745;
    font-size: 16px;
}

.pgh-errors-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.pgh-error-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 4px solid #dc3545;
}

.pgh-error-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.pgh-error-type {
    background: #dc3545;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.pgh-error-time {
    font-size: 12px;
    color: #666;
}

.pgh-error-message {
    color: #333;
    margin-bottom: 5px;
}

.pgh-error-context {
    font-size: 12px;
    color: #666;
    font-style: italic;
}

.pgh-system-info {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.pgh-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.pgh-info-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 14px;
}

.pgh-health-actions {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.pgh-action-buttons {
    margin-top: 15px;
}

.pgh-action-buttons .button {
    margin-right: 10px;
    margin-bottom: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Health check functions
    window.runHealthCheck = function() {
        var button = $('button[onclick="runHealthCheck()"]');
        button.prop('disabled', true).text('Running...');
        
        $.ajax({
            url: pgh_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pgh_run_health_check',
                nonce: pgh_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Health check completed successfully!');
                    location.reload();
                } else {
                    alert('Health check failed: ' + response.data);
                }
            },
            complete: function() {
                button.prop('disabled', false).text('Run Health Check');
            }
        });
    };
    
    window.clearErrorLog = function() {
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
                        alert('Error log cleared successfully!');
                        location.reload();
                    } else {
                        alert('Failed to clear error log: ' + response.data);
                    }
                }
            });
        }
    };
    
    window.optimizeDatabase = function() {
        if (confirm('Are you sure you want to optimize the database? This may take a few moments.')) {
            var button = $('button[onclick="optimizeDatabase()"]');
            button.prop('disabled', true).text('Optimizing...');
            
            $.ajax({
                url: pgh_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pgh_optimize_database',
                    nonce: pgh_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Database optimized successfully!');
                    } else {
                        alert('Database optimization failed: ' + response.data);
                    }
                },
                complete: function() {
                    button.prop('disabled', false).text('Optimize Database');
                }
            });
        }
    };
    
    window.exportSystemInfo = function() {
        var url = pgh_ajax.ajax_url + '?action=pgh_export_system_info&nonce=' + pgh_ajax.nonce;
        window.open(url, '_blank');
    };
});
</script>
