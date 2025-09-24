<?php
/**
 * Analytics Page Template
 * 
 * @package PayGateHandler
 * @since 2.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin = PayGateHandlerPro::get_instance();
$period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : '30_days';

// Get analytics data
$analytics = $plugin->get_analytics($period);
$email_stats = $plugin->get_email_stats($period);
?>

<div class="wrap">
    <h1>PayGate Analytics Dashboard</h1>
    
    <!-- Period Selector -->
    <div class="pgh-period-selector">
        <label for="period-select">Time Period:</label>
        <select id="period-select" onchange="window.location.href='?page=pgh_admin_analytics&period='+this.value">
            <option value="7_days" <?php selected($period, '7_days'); ?>>Last 7 Days</option>
            <option value="30_days" <?php selected($period, '30_days'); ?>>Last 30 Days</option>
            <option value="90_days" <?php selected($period, '90_days'); ?>>Last 90 Days</option>
            <option value="1_year" <?php selected($period, '1_year'); ?>>Last Year</option>
        </select>
    </div>
    
    <!-- Key Metrics -->
    <div class="pgh-analytics-metrics">
        <div class="pgh-metric-card">
            <div class="pgh-metric-icon">📊</div>
            <div class="pgh-metric-content">
                <h3><?php echo number_format($analytics['total_payments']); ?></h3>
                <p>Total Payments</p>
                <span class="pgh-metric-change <?php echo $analytics['payments_change'] >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $analytics['payments_change'] >= 0 ? '+' : ''; ?><?php echo number_format($analytics['payments_change'], 1); ?>%
                </span>
            </div>
        </div>
        
        <div class="pgh-metric-card">
            <div class="pgh-metric-icon">💰</div>
            <div class="pgh-metric-content">
                <h3>R <?php echo number_format($analytics['total_revenue'], 2); ?></h3>
                <p>Total Revenue</p>
                <span class="pgh-metric-change <?php echo $analytics['revenue_change'] >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $analytics['revenue_change'] >= 0 ? '+' : ''; ?><?php echo number_format($analytics['revenue_change'], 1); ?>%
                </span>
            </div>
        </div>
        
        <div class="pgh-metric-card">
            <div class="pgh-metric-icon">✅</div>
            <div class="pgh-metric-content">
                <h3><?php echo number_format($analytics['success_rate'], 1); ?>%</h3>
                <p>Success Rate</p>
                <span class="pgh-metric-change <?php echo $analytics['success_rate'] >= 90 ? 'positive' : 'negative'; ?>">
                    <?php echo $analytics['success_rate'] >= 90 ? 'Excellent' : 'Needs Attention'; ?>
                </span>
            </div>
        </div>
        
        <div class="pgh-metric-card">
            <div class="pgh-metric-icon">📧</div>
            <div class="pgh-metric-content">
                <h3><?php echo number_format($email_stats['delivery_rate'], 1); ?>%</h3>
                <p>Email Delivery Rate</p>
                <span class="pgh-metric-change <?php echo $email_stats['delivery_rate'] >= 95 ? 'positive' : 'negative'; ?>">
                    <?php echo $email_stats['delivery_rate'] >= 95 ? 'Good' : 'Check Settings'; ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="pgh-charts-section">
        <div class="pgh-chart-container">
            <h3>Payment Trends</h3>
            <canvas id="payments-chart" width="400" height="200"></canvas>
        </div>
        
        <div class="pgh-chart-container">
            <h3>Revenue Trends</h3>
            <canvas id="revenue-chart" width="400" height="200"></canvas>
        </div>
    </div>
    
    <!-- Detailed Statistics -->
    <div class="pgh-detailed-stats">
        <div class="pgh-stat-section">
            <h3>Payment Status Breakdown</h3>
            <div class="pgh-status-breakdown">
                <div class="pgh-status-item">
                    <span class="pgh-status-dot completed"></span>
                    <span>Completed: <?php echo number_format($analytics['completed_payments']); ?></span>
                </div>
                <div class="pgh-status-item">
                    <span class="pgh-status-dot pending"></span>
                    <span>Pending: <?php echo number_format($analytics['pending_payments']); ?></span>
                </div>
                <div class="pgh-status-item">
                    <span class="pgh-status-dot cancelled"></span>
                    <span>Cancelled: <?php echo number_format($analytics['cancelled_payments']); ?></span>
                </div>
                <div class="pgh-status-item">
                    <span class="pgh-status-dot failed"></span>
                    <span>Failed: <?php echo number_format($analytics['failed_payments']); ?></span>
                </div>
            </div>
        </div>
        
        <div class="pgh-stat-section">
            <h3>Top Payment Items</h3>
            <div class="pgh-top-items">
                <?php foreach ($analytics['top_items'] as $item): ?>
                <div class="pgh-item-row">
                    <span class="pgh-item-name"><?php echo esc_html($item['name']); ?></span>
                    <span class="pgh-item-count"><?php echo number_format($item['count']); ?> payments</span>
                    <span class="pgh-item-revenue">R <?php echo number_format($item['revenue'], 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="pgh-stat-section">
            <h3>Email Statistics</h3>
            <div class="pgh-email-stats">
                <div class="pgh-email-stat">
                    <strong>Total Sent:</strong> <?php echo number_format($email_stats['total_sent']); ?>
                </div>
                <div class="pgh-email-stat">
                    <strong>Successful:</strong> <?php echo number_format($email_stats['successful']); ?>
                </div>
                <div class="pgh-email-stat">
                    <strong>Failed:</strong> <?php echo number_format($email_stats['failed']); ?>
                </div>
                <div class="pgh-email-stat">
                    <strong>Delivery Rate:</strong> <?php echo number_format($email_stats['delivery_rate'], 1); ?>%
                </div>
            </div>
        </div>
    </div>
    
    <!-- Export Options -->
    <div class="pgh-export-section">
        <h3>Export Data</h3>
        <p>Export your payment data for external analysis:</p>
        <div class="pgh-export-buttons">
            <button class="button button-primary" onclick="exportData('csv')">Export as CSV</button>
            <button class="button" onclick="exportData('json')">Export as JSON</button>
            <button class="button" onclick="exportData('pdf')">Export as PDF</button>
        </div>
    </div>
</div>

<style>
.pgh-period-selector {
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.pgh-period-selector label {
    font-weight: bold;
    margin-right: 10px;
}

.pgh-analytics-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.pgh-metric-card {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
}

.pgh-metric-icon {
    font-size: 32px;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
    border-radius: 50%;
}

.pgh-metric-content h3 {
    margin: 0;
    font-size: 28px;
    color: #333;
    font-weight: bold;
}

.pgh-metric-content p {
    margin: 5px 0;
    color: #666;
    font-size: 14px;
}

.pgh-metric-change {
    font-size: 12px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 4px;
}

.pgh-metric-change.positive {
    background: #d4edda;
    color: #155724;
}

.pgh-metric-change.negative {
    background: #f8d7da;
    color: #721c24;
}

.pgh-charts-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.pgh-chart-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pgh-chart-container h3 {
    margin-top: 0;
    color: #333;
}

.pgh-detailed-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.pgh-stat-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pgh-stat-section h3 {
    margin-top: 0;
    color: #333;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.pgh-status-breakdown {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pgh-status-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pgh-status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.pgh-status-dot.completed { background: #28a745; }
.pgh-status-dot.pending { background: #ffc107; }
.pgh-status-dot.cancelled { background: #dc3545; }
.pgh-status-dot.failed { background: #6c757d; }

.pgh-top-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.pgh-item-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.pgh-item-name {
    font-weight: bold;
    color: #333;
}

.pgh-item-count {
    color: #666;
    font-size: 14px;
}

.pgh-item-revenue {
    color: #28a745;
    font-weight: bold;
}

.pgh-email-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.pgh-email-stat {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.pgh-export-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.pgh-export-buttons {
    margin-top: 15px;
}

.pgh-export-buttons .button {
    margin-right: 10px;
}

@media (max-width: 768px) {
    .pgh-charts-section {
        grid-template-columns: 1fr;
    }
    
    .pgh-analytics-metrics {
        grid-template-columns: 1fr;
    }
    
    .pgh-detailed-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
jQuery(document).ready(function($) {
    // Initialize charts
    initCharts();
    
    function initCharts() {
        // Payment trends chart
        var paymentsCtx = document.getElementById('payments-chart').getContext('2d');
        new Chart(paymentsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($analytics['chart_labels']); ?>,
                datasets: [{
                    label: 'Payments',
                    data: <?php echo json_encode($analytics['chart_payments']); ?>,
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
        
        // Revenue trends chart
        var revenueCtx = document.getElementById('revenue-chart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($analytics['chart_labels']); ?>,
                datasets: [{
                    label: 'Revenue (R)',
                    data: <?php echo json_encode($analytics['chart_revenue']); ?>,
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
    
    // Export functions
    window.exportData = function(format) {
        var url = pgh_ajax.ajax_url + '?action=pgh_export_data&format=' + format + '&period=<?php echo esc_js($period); ?>&nonce=' + pgh_ajax.nonce;
        window.open(url, '_blank');
    };
});
</script>
