<?php
/**
 * API & Webhooks Page Template
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

// Handle API settings save
if (isset($_POST['save_api']) && wp_verify_nonce($_POST['pgh_api_nonce'], 'pgh_api')) {
    $api_settings = array(
        'api_enabled' => intval($_POST['api_enabled']),
        'api_key' => sanitize_text_field($_POST['api_key']),
        'webhook_url' => esc_url_raw($_POST['webhook_url']),
        'webhook_secret' => sanitize_text_field($_POST['webhook_secret']),
        'webhook_events' => array_map('sanitize_text_field', $_POST['webhook_events'] ?? array()),
        'rate_limit' => intval($_POST['rate_limit']),
        'cors_enabled' => intval($_POST['cors_enabled']),
        'cors_origins' => sanitize_textarea_field($_POST['cors_origins'])
    );
    
    $current_settings = $plugin->get_settings();
    $current_settings['api'] = $api_settings;
    update_option('pgh_settings', $current_settings);
    
    echo '<div class="notice notice-success"><p>API settings saved successfully!</p></div>';
}

$api_settings = $settings['api'] ?? array();

// Generate API key if not exists
if (empty($api_settings['api_key'])) {
    $api_settings['api_key'] = wp_generate_password(32, false);
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">🔌 API & Webhooks</h1>
    <a href="<?php echo admin_url('admin.php?page=pgh_admin'); ?>" class="page-title-action">← Back to Dashboard</a>
    
    <hr class="wp-header-end">
    
    <form method="post" action="">
        <?php wp_nonce_field('pgh_api', 'pgh_api_nonce'); ?>
        
        <div class="pgh-api-container">
            <!-- API Configuration -->
            <div class="pgh-api-section">
                <h2>🔑 API Configuration</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable API</th>
                        <td>
                            <label>
                                <input type="checkbox" name="api_enabled" value="1" <?php checked($api_settings['api_enabled'] ?? 0); ?>>
                                Enable REST API endpoints
                            </label>
                            <p class="description">Allows external applications to interact with the plugin</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">API Key</th>
                        <td>
                            <input type="text" name="api_key" value="<?php echo esc_attr($api_settings['api_key']); ?>" class="regular-text" readonly>
                            <button type="button" id="generate-api-key" class="button">Generate New Key</button>
                            <p class="description">Use this key to authenticate API requests</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Rate Limit (requests per minute)</th>
                        <td>
                            <input type="number" name="rate_limit" value="<?php echo esc_attr($api_settings['rate_limit'] ?? 100); ?>" class="small-text" min="1" max="1000">
                            <p class="description">Maximum API requests per minute per IP</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- CORS Configuration -->
            <div class="pgh-api-section">
                <h2>🌐 CORS Configuration</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable CORS</th>
                        <td>
                            <label>
                                <input type="checkbox" name="cors_enabled" value="1" <?php checked($api_settings['cors_enabled'] ?? 0); ?>>
                                Enable Cross-Origin Resource Sharing
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Allowed Origins</th>
                        <td>
                            <textarea name="cors_origins" rows="3" cols="50" class="large-text" placeholder="https://example.com&#10;https://app.example.com"><?php echo esc_textarea($api_settings['cors_origins'] ?? ''); ?></textarea>
                            <p class="description">One domain per line. Leave empty to allow all origins.</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Webhook Configuration -->
            <div class="pgh-api-section">
                <h2>🔗 Webhook Configuration</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Webhook URL</th>
                        <td>
                            <input type="url" name="webhook_url" value="<?php echo esc_attr($api_settings['webhook_url'] ?? ''); ?>" class="regular-text" placeholder="https://your-app.com/webhook">
                            <p class="description">URL to receive webhook notifications</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Webhook Secret</th>
                        <td>
                            <input type="text" name="webhook_secret" value="<?php echo esc_attr($api_settings['webhook_secret'] ?? ''); ?>" class="regular-text">
                            <button type="button" id="generate-webhook-secret" class="button">Generate Secret</button>
                            <p class="description">Secret key to verify webhook authenticity</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Webhook Events</th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="webhook_events[]" value="payment.created" <?php checked(in_array('payment.created', $api_settings['webhook_events'] ?? [])); ?>>
                                    Payment Created
                                </label><br>
                                <label>
                                    <input type="checkbox" name="webhook_events[]" value="payment.completed" <?php checked(in_array('payment.completed', $api_settings['webhook_events'] ?? [])); ?>>
                                    Payment Completed
                                </label><br>
                                <label>
                                    <input type="checkbox" name="webhook_events[]" value="payment.failed" <?php checked(in_array('payment.failed', $api_settings['webhook_events'] ?? [])); ?>>
                                    Payment Failed
                                </label><br>
                                <label>
                                    <input type="checkbox" name="webhook_events[]" value="payment.cancelled" <?php checked(in_array('payment.cancelled', $api_settings['webhook_events'] ?? [])); ?>>
                                    Payment Cancelled
                                </label><br>
                                <label>
                                    <input type="checkbox" name="webhook_events[]" value="email.sent" <?php checked(in_array('email.sent', $api_settings['webhook_events'] ?? [])); ?>>
                                    Email Sent
                                </label><br>
                                <label>
                                    <input type="checkbox" name="webhook_events[]" value="email.failed" <?php checked(in_array('email.failed', $api_settings['webhook_events'] ?? [])); ?>>
                                    Email Failed
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="save_api" class="button-primary" value="Save API Settings">
        </p>
    </form>
    
    <!-- API Documentation -->
    <div class="pgh-api-documentation">
        <h2>📚 API Documentation</h2>
        
        <div class="pgh-api-endpoints">
            <h3>Available Endpoints</h3>
            
            <div class="pgh-endpoint">
                <h4>GET /wp-json/paygate/v1/payments</h4>
                <p>Retrieve payments list</p>
                <div class="pgh-endpoint-details">
                    <strong>Parameters:</strong>
                    <ul>
                        <li><code>page</code> - Page number (default: 1)</li>
                        <li><code>per_page</code> - Items per page (default: 10, max: 100)</li>
                        <li><code>status</code> - Filter by status (pending, completed, failed, cancelled)</li>
                        <li><code>date_from</code> - Filter from date (YYYY-MM-DD)</li>
                        <li><code>date_to</code> - Filter to date (YYYY-MM-DD)</li>
                    </ul>
                    <strong>Headers:</strong>
                    <ul>
                        <li><code>Authorization: Bearer YOUR_API_KEY</code></li>
                    </ul>
                </div>
            </div>
            
            <div class="pgh-endpoint">
                <h4>GET /wp-json/paygate/v1/payments/{id}</h4>
                <p>Retrieve specific payment</p>
                <div class="pgh-endpoint-details">
                    <strong>Headers:</strong>
                    <ul>
                        <li><code>Authorization: Bearer YOUR_API_KEY</code></li>
                    </ul>
                </div>
            </div>
            
            <div class="pgh-endpoint">
                <h4>POST /wp-json/paygate/v1/payments</h4>
                <p>Create new payment</p>
                <div class="pgh-endpoint-details">
                    <strong>Body:</strong>
                    <pre><code>{
  "name": "John Doe",
  "email": "john@example.com",
  "item_name": "Test Payment",
  "amount": 10000
}</code></pre>
                    <strong>Headers:</strong>
                    <ul>
                        <li><code>Authorization: Bearer YOUR_API_KEY</code></li>
                        <li><code>Content-Type: application/json</code></li>
                    </ul>
                </div>
            </div>
            
            <div class="pgh-endpoint">
                <h4>GET /wp-json/paygate/v1/analytics</h4>
                <p>Get analytics data</p>
                <div class="pgh-endpoint-details">
                    <strong>Headers:</strong>
                    <ul>
                        <li><code>Authorization: Bearer YOUR_API_KEY</code></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="pgh-api-examples">
            <h3>Code Examples</h3>
            
            <div class="pgh-code-example">
                <h4>JavaScript (Fetch API)</h4>
                <pre><code>fetch('/wp-json/paygate/v1/payments', {
  headers: {
    'Authorization': 'Bearer YOUR_API_KEY',
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));</code></pre>
            </div>
            
            <div class="pgh-code-example">
                <h4>PHP (cURL)</h4>
                <pre><code>$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://yoursite.com/wp-json/paygate/v1/payments');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer YOUR_API_KEY',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);</code></pre>
            </div>
        </div>
    </div>
    
    <!-- Webhook Testing -->
    <div class="pgh-webhook-testing">
        <h2>🧪 Webhook Testing</h2>
        <div class="pgh-test-webhook">
            <button type="button" id="test-webhook" class="button button-primary">Test Webhook</button>
            <div id="webhook-test-result"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate API key
    document.getElementById('generate-api-key').addEventListener('click', function() {
        if (confirm('Generate a new API key? The old key will become invalid.')) {
            const newKey = generateRandomString(32);
            document.querySelector('input[name="api_key"]').value = newKey;
        }
    });
    
    // Generate webhook secret
    document.getElementById('generate-webhook-secret').addEventListener('click', function() {
        const newSecret = generateRandomString(32);
        document.querySelector('input[name="webhook_secret"]').value = newSecret;
    });
    
    // Test webhook
    document.getElementById('test-webhook').addEventListener('click', function() {
        const button = this;
        const resultDiv = document.getElementById('webhook-test-result');
        
        button.disabled = true;
        button.textContent = 'Testing...';
        resultDiv.innerHTML = '<p>Testing webhook...</p>';
        
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=pgh_test_webhook&nonce=' + pgh_ajax.nonce
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="notice notice-success"><p>Webhook test successful!</p></div>';
            } else {
                resultDiv.innerHTML = '<div class="notice notice-error"><p>Webhook test failed: ' + data.data + '</p></div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<div class="notice notice-error"><p>Webhook test error: ' + error.message + '</p></div>';
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = 'Test Webhook';
        });
    });
    
    function generateRandomString(length) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }
});
</script>
