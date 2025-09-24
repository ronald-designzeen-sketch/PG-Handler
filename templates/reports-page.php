<?php
/**
 * Reports Page Template
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

// Get analytics data
$analytics = $plugin->get_analytics();
$email_stats = $plugin->get_email_stats();
$health = $plugin->get_system_health();

// Date range for reports
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : date('Y-m-d');
?>

<div class="wrap">
    <h1 class="wp-heading-inline">📊 Payment Reports & Analytics</h1>
    <a href="<?php echo admin_url('admin.php?page=pgh_admin'); ?>" class="page-title-action">← Back to Dashboard</a>
    
    <hr class="wp-header-end">
    
    <!-- Date Range Filter -->
    <div class="pgh-reports-filter">
        <form method="get" action="">
            <input type="hidden" name="page" value="pgh_admin_reports">
            <div class="pgh-filter-row">
                <label for="date_from">From:</label>
                <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>" class="regular-text">
                
                <label for="date_to">To:</label>
                <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>" class="regular-text">
                
                <button type="submit" class="button button-primary">Generate Report</button>
                <button type="button" id="export-report" class="button">Export PDF</button>
            </div>
        </form>
    </div>
    
    <!-- Key Metrics -->
    <div class="pgh-reports-metrics">
        <div class="pgh-metric-card">
            <div class="pgh-metric-icon">💰</div>
            <div class="pgh-metric-content">
                <h3>R <?php echo number_format($analytics['total_revenue'] / 100, 2); ?></h3>
                <p>Total Revenue</p>
                <span class="pgh-metric-change positive">+<?php echo $analytics['revenue_growth']; ?>%</span>
            </div>
        </div>
        
        <div class="pgh-metric-card">
            <div class="pgh-metric-icon">📈</div>
            <div class="pgh-metric-content">
                <h3><?php echo number_format($analytics['total_payments']); ?></h3>
                <p>Total Payments</p>
                <span class="pgh-metric-change positive">+<?php echo $analytics['payment_growth']; ?>%</span>
            </div>
        </div>
        
        <div class="pgh-metric-card">
            <div class="pgh-metric-icon">✅</div>
            <div class="pgh-metric-content">
                <h3><?php echo $analytics['success_rate']; ?>%</h3>
                <p>Success Rate</p>
                <span class="pgh-metric-change <?php echo $analytics['success_rate'] >= 95 ? 'positive' : 'negative'; ?>">
                    <?php echo $analytics['success_rate'] >= 95 ? 'Excellent' : 'Needs Improvement'; ?>
                </span>
            </div>
        </div>
        
        <div class="pgh-metric-card">
            <div class="pgh-metric-icon">📧</div>
            <div class="pgh-metric-content">
                <h3><?php echo $email_stats['success_rate']; ?>%</h3>
                <p>Email Delivery</p>
                <span class="pgh-metric-change <?php echo $email_stats['success_rate'] >= 95 ? 'positive' : 'negative'; ?>">
                    <?php echo $email_stats['total_sent']; ?> sent
                </span>
            </div>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="pgh-reports-charts">
        <div class="pgh-chart-container">
            <h3>📈 Revenue Trend</h3>
            <canvas id="revenue-chart" width="400" height="200"></canvas>
        </div>
        
        <div class="pgh-chart-container">
            <h3>📊 Payment Status Distribution</h3>
            <canvas id="status-chart" width="400" height="200"></canvas>
        </div>
    </div>
    
    <!-- Detailed Reports -->
    <div class="pgh-reports-detailed">
        <div class="pgh-report-section">
            <h3>🏆 Top Payment Items</h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Total Sales</th>
                        <th>Revenue</th>
                        <th>Success Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analytics['top_items'] as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item['name']); ?></td>
                        <td><?php echo number_format($item['count']); ?></td>
                        <td>R <?php echo number_format($item['revenue'] / 100, 2); ?></td>
                        <td><?php echo $item['success_rate']; ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="pgh-report-section">
            <h3>⏰ Peak Payment Times</h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Hour</th>
                        <th>Payments</th>
                        <th>Revenue</th>
                        <th>Success Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($analytics['peak_hours'] as $hour => $data): ?>
                    <tr>
                        <td><?php echo $hour; ?>:00</td>
                        <td><?php echo number_format($data['count']); ?></td>
                        <td>R <?php echo number_format($data['revenue'] / 100, 2); ?></td>
                        <td><?php echo $data['success_rate']; ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- System Performance -->
    <div class="pgh-reports-performance">
        <h3>⚡ System Performance</h3>
        <div class="pgh-performance-grid">
            <div class="pgh-performance-item">
                <span class="pgh-performance-label">Database Response Time</span>
                <span class="pgh-performance-value <?php echo $health['db_response_time'] < 100 ? 'good' : 'warning'; ?>">
                    <?php echo $health['db_response_time']; ?>ms
                </span>
            </div>
            
            <div class="pgh-performance-item">
                <span class="pgh-performance-label">Email Delivery Time</span>
                <span class="pgh-performance-value <?php echo $health['email_delivery_time'] < 5 ? 'good' : 'warning'; ?>">
                    <?php echo $health['email_delivery_time']; ?>s
                </span>
            </div>
            
            <div class="pgh-performance-item">
                <span class="pgh-performance-label">Memory Usage</span>
                <span class="pgh-performance-value <?php echo $health['memory_usage'] < 80 ? 'good' : 'warning'; ?>">
                    <?php echo $health['memory_usage']; ?>%
                </span>
            </div>
            
            <div class="pgh-performance-item">
                <span class="pgh-performance-label">Error Rate</span>
                <span class="pgh-performance-value <?php echo $health['error_rate'] < 5 ? 'good' : 'danger'; ?>">
                    <?php echo $health['error_rate']; ?>%
                </span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenue-chart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($analytics['chart_labels']); ?>,
            datasets: [{
                label: 'Revenue (R)',
                data: <?php echo json_encode($analytics['revenue_data']); ?>,
                borderColor: '#0073aa',
                backgroundColor: 'rgba(0, 115, 170, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R ' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
    
    // Status Chart
    const statusCtx = document.getElementById('status-chart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending', 'Cancelled', 'Failed'],
            datasets: [{
                data: <?php echo json_encode($analytics['status_data']); ?>,
                backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Export Report
    document.getElementById('export-report').addEventListener('click', function() {
        window.print();
    });
});
</script>
