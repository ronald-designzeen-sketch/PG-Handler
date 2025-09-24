<?php
/**
 * Settings Page Template
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

// Handle form submission
if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'pgh_settings')) {
    $new_settings = array();
    
    // PayGate Settings
    $new_settings['merchant_id'] = sanitize_text_field($_POST['merchant_id']);
    $new_settings['merchant_key'] = sanitize_text_field($_POST['merchant_key']);
    $new_settings['paygate_url'] = esc_url_raw($_POST['paygate_url']);
    $new_settings['test_mode'] = intval($_POST['test_mode']);
    
    // Email Settings
    $new_settings['email_from'] = sanitize_email($_POST['email_from']);
    $new_settings['email_from_name'] = sanitize_text_field($_POST['email_from_name']);
    $new_settings['email_subject'] = sanitize_text_field($_POST['email_subject']);
    $new_settings['email_body'] = wp_kses_post($_POST['email_body']);
    $new_settings['attach_qr'] = intval($_POST['attach_qr']);
    
    // Form Settings
    $new_settings['forminator_forms'] = sanitize_text_field($_POST['forminator_forms']);
    $new_settings['auto_detect_forms'] = intval($_POST['auto_detect_forms']);
    
    // Venue Settings
    $new_settings['venue_name'] = sanitize_text_field($_POST['venue_name']);
    $new_settings['venue_address'] = sanitize_textarea_field($_POST['venue_address']);
    
    // Payment Reference Settings
    $new_settings['payment_reference_prefix'] = sanitize_text_field($_POST['payment_reference_prefix']);
    
    // Rate Limiting
    $new_settings['rate_limit_enabled'] = intval($_POST['rate_limit_enabled']);
    $new_settings['rate_limit_attempts'] = intval($_POST['rate_limit_attempts']);
    $new_settings['rate_limit_window'] = intval($_POST['rate_limit_window']);
    
    // Features
    $new_settings['enable_analytics'] = intval($_POST['enable_analytics']);
    $new_settings['enable_health_monitoring'] = intval($_POST['enable_health_monitoring']);
    $new_settings['email_delivery_tracking'] = intval($_POST['email_delivery_tracking']);
    
    update_option('pgh_settings', $new_settings);
    $settings = $new_settings;
    
    echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
}
?>

<div class="wrap">
    <h1>PayGate Handler Pro Settings</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('pgh_settings'); ?>
        
        <div class="pgh-settings-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#paygate-settings" class="nav-tab nav-tab-active">PayGate Configuration</a>
                <a href="#email-settings" class="nav-tab">Email Settings</a>
                <a href="#form-settings" class="nav-tab">Form Integration</a>
                <a href="#payment-settings" class="nav-tab">Payment Settings</a>
                <a href="#security-settings" class="nav-tab">Security & Rate Limiting</a>
                <a href="#advanced-settings" class="nav-tab">Advanced</a>
            </nav>
            
            <!-- PayGate Settings Tab -->
            <div id="paygate-settings" class="pgh-tab-content active">
                <table class="form-table">
                    <tr>
                        <th scope="row">Merchant ID</th>
                        <td>
                            <input type="text" name="merchant_id" value="<?php echo esc_attr($settings['merchant_id']); ?>" class="regular-text" required>
                            <p class="description">Your PayGate Merchant ID</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Merchant Key</th>
                        <td>
                            <input type="password" name="merchant_key" value="<?php echo esc_attr($settings['merchant_key']); ?>" class="regular-text" required>
                            <p class="description">Your PayGate Merchant Key (keep this secure)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">PayGate URL</th>
                        <td>
                            <input type="url" name="paygate_url" value="<?php echo esc_attr($settings['paygate_url']); ?>" class="regular-text" required>
                            <p class="description">PayGate initiate URL</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Test Mode</th>
                        <td>
                            <label>
                                <input type="checkbox" name="test_mode" value="1" <?php checked($settings['test_mode'], 1); ?>>
                                Enable test mode (recommended for development)
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Email Settings Tab -->
            <div id="email-settings" class="pgh-tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">From Email</th>
                        <td>
                            <input type="email" name="email_from" value="<?php echo esc_attr($settings['email_from']); ?>" class="regular-text" required>
                            <p class="description">Email address to send tickets from</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">From Name</th>
                        <td>
                            <input type="text" name="email_from_name" value="<?php echo esc_attr($settings['email_from_name']); ?>" class="regular-text" required>
                            <p class="description">Name to send emails from</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Email Subject</th>
                        <td>
                            <input type="text" name="email_subject" value="<?php echo esc_attr($settings['email_subject']); ?>" class="regular-text" required>
                            <p class="description">Available placeholders: {name}, {item_name}, {amount}, {reference}</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Email Template</th>
                        <td>
                            <textarea name="email_body" rows="15" cols="80" class="large-text"><?php echo esc_textarea($settings['email_body']); ?></textarea>
                            <p class="description">HTML email template. Available placeholders: {name}, {item_name}, {amount}, {reference}, {created_at}</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Attach QR Code</th>
                        <td>
                            <label>
                                <input type="checkbox" name="attach_qr" value="1" <?php checked($settings['attach_qr'], 1); ?>>
                                Attach QR code to email tickets
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Test Email</th>
                        <td>
                            <input type="email" id="test-email" placeholder="Enter email to test" class="regular-text">
                            <button type="button" id="send-test-email" class="button">Send Test Email</button>
                            <p class="description">Send a test email to verify your template</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Form Settings Tab -->
            <div id="form-settings" class="pgh-tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">Auto-detect Forms</th>
                        <td>
                            <label>
                                <input type="checkbox" name="auto_detect_forms" value="1" <?php checked($settings['auto_detect_forms'], 1); ?>>
                                Automatically process all Forminator form submissions
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Specific Form IDs</th>
                        <td>
                            <input type="text" name="forminator_forms" value="<?php echo esc_attr($settings['forminator_forms']); ?>" class="regular-text">
                            <p class="description">Comma-separated list of Forminator form IDs to process (if auto-detect is disabled)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Venue Name</th>
                        <td>
                            <input type="text" name="venue_name" value="<?php echo esc_attr($settings['venue_name']); ?>" class="regular-text">
                            <p class="description">Default venue name for tickets</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Venue Address</th>
                        <td>
                            <textarea name="venue_address" rows="3" cols="50" class="large-text"><?php echo esc_textarea($settings['venue_address']); ?></textarea>
                            <p class="description">Default venue address for tickets</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Payment Settings Tab -->
            <div id="payment-settings" class="pgh-tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">Payment Reference Prefix</th>
                        <td>
                            <input type="text" name="payment_reference_prefix" value="<?php echo esc_attr($settings['payment_reference_prefix']); ?>" class="regular-text" placeholder="PGH_" required maxlength="20">
                            <p class="description">
                                <strong>Customize the prefix for payment references:</strong><br>
                                • Examples: <code>PGH_</code>, <code>Registration_</code>, <code>EVENT_</code>, <code>ORDER_</code>, <code>BOOKING_</code><br>
                                • Only letters, numbers, underscores, and hyphens allowed<br>
                                • Maximum 20 characters<br>
                                • Will automatically add underscore if not present<br><br>
                                
                                <strong>Current format:</strong> <code><?php echo esc_html($settings['payment_reference_prefix']); ?>TIMESTAMP_RANDOM</code><br>
                                <strong>Live example:</strong> <code><?php echo esc_html($settings['payment_reference_prefix']); ?><?php echo time(); ?>_<?php echo wp_generate_password(8, false); ?></code>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Security Settings Tab -->
            <div id="security-settings" class="pgh-tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Rate Limiting</th>
                        <td>
                            <label>
                                <input type="checkbox" name="rate_limit_enabled" value="1" <?php checked($settings['rate_limit_enabled'], 1); ?>>
                                Enable rate limiting to prevent abuse
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Max Attempts</th>
                        <td>
                            <input type="number" name="rate_limit_attempts" value="<?php echo esc_attr($settings['rate_limit_attempts']); ?>" min="1" max="100" class="small-text">
                            <p class="description">Maximum payment attempts per time window</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Time Window (seconds)</th>
                        <td>
                            <input type="number" name="rate_limit_window" value="<?php echo esc_attr($settings['rate_limit_window']); ?>" min="60" max="86400" class="small-text">
                            <p class="description">Time window for rate limiting (3600 = 1 hour)</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Advanced Settings Tab -->
            <div id="advanced-settings" class="pgh-tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Analytics</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_analytics" value="1" <?php checked($settings['enable_analytics'], 1); ?>>
                                Enable payment analytics and reporting
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Health Monitoring</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_health_monitoring" value="1" <?php checked($settings['enable_health_monitoring'], 1); ?>>
                                Enable system health monitoring
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Email Delivery Tracking</th>
                        <td>
                            <label>
                                <input type="checkbox" name="email_delivery_tracking" value="1" <?php checked($settings['email_delivery_tracking'], 1); ?>>
                                Track email delivery success/failure rates
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php submit_button('Save Settings'); ?>
    </form>
</div>

<style>
.pgh-settings-tabs {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.pgh-tab-content {
    display: none;
    padding: 20px;
}

.pgh-tab-content.active {
    display: block;
}

.nav-tab-wrapper {
    margin: 0;
    border-bottom: 1px solid #ccd0d4;
}

.nav-tab {
    background: #f1f1f1;
    border: 1px solid #ccd0d4;
    border-bottom: none;
    margin-right: 5px;
    padding: 10px 15px;
    text-decoration: none;
    color: #555;
}

.nav-tab:hover {
    background: #f9f9f9;
}

.nav-tab-active {
    background: #fff;
    border-bottom: 1px solid #fff;
    margin-bottom: -1px;
}

.form-table th {
    width: 200px;
    padding: 20px 10px 20px 0;
}

.form-table td {
    padding: 15px 10px;
}

.description {
    font-style: italic;
    color: #666;
    margin-top: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).attr('href');
        
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.pgh-tab-content').removeClass('active');
        $(target).addClass('active');
    });
    
    // Test email
    $('#send-test-email').on('click', function() {
        var email = $('#test-email').val();
        
        if (!email) {
            alert('Please enter an email address');
            return;
        }
        
        var button = $(this);
        button.prop('disabled', true).text('Sending...');
        
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
                    alert('Test email sent successfully!');
                } else {
                    alert('Failed to send test email: ' + response.data);
                }
            },
            complete: function() {
                button.prop('disabled', false).text('Send Test Email');
            }
        });
    });
    
    // Payment reference prefix validation
    $('input[name="payment_reference_prefix"]').on('input', function() {
        var prefix = $(this).val();
        var sanitized = prefix.replace(/[^a-zA-Z0-9_-]/g, '');
        
        if (sanitized !== prefix) {
            $(this).val(sanitized);
        }
        
        // Update live example
        updatePrefixExample();
    });
    
    function updatePrefixExample() {
        var prefix = $('input[name="payment_reference_prefix"]').val() || 'PGH_';
        var timestamp = Math.floor(Date.now() / 1000);
        var random = Math.random().toString(36).substring(2, 10);
        var example = prefix + timestamp + '_' + random;
        
        // Update the example in the description
        $('input[name="payment_reference_prefix"]').closest('td').find('.description').html(
            '<strong>Customize the prefix for payment references:</strong><br>' +
            '• Examples: <code>PGH_</code>, <code>Registration_</code>, <code>EVENT_</code>, <code>ORDER_</code>, <code>BOOKING_</code><br>' +
            '• Only letters, numbers, underscores, and hyphens allowed<br>' +
            '• Maximum 20 characters<br>' +
            '• Will automatically add underscore if not present<br><br>' +
            '<strong>Current format:</strong> <code>' + prefix + 'TIMESTAMP_RANDOM</code><br>' +
            '<strong>Live example:</strong> <code>' + example + '</code>'
        );
    }
    
    // Initialize prefix example
    updatePrefixExample();
});
</script>
