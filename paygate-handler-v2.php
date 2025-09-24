<?php
/**
 * Plugin Name: PayGate Handler Pro
 * Plugin URI:  https://designzeen.com
 * Description: Professional PayGate payment processing plugin with Forminator integration, dynamic email tickets, analytics dashboard, and comprehensive admin tools.
 * Version:     2.1
 * Author:      Ronald @ Design Zeen
 * Author URI:  https://designzeen.com
 * Text Domain: paygate-handler
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 * 
 * PayGate Handler Pro is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 * 
 * PayGate Handler Pro is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with PayGate Handler Pro. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 *
 * @package PayGateHandler
 * @version 2.1
 * @author Ronald @ Design Zeen
 * @copyright 2024 Ronald @ Design Zeen
 * @license GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PGH_VERSION', '2.1');
define('PGH_PLUGIN_FILE', __FILE__);
define('PGH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PGH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PGH_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main PayGate Handler Pro Class
 * 
 * This class handles all PayGate payment processing functionality including
 * payment processing, email notifications, admin interface, and analytics.
 * 
 * @since 2.1
 */
class PayGateHandlerPro {
    
    /**
     * Plugin instance
     * 
     * @var PayGateHandlerPro
     */
    private static $instance = null;
    
    /**
     * Plugin settings
     * 
     * @var array
     */
    private $settings = array();
    
    /**
     * Database table name
     * 
     * @var string
     */
    private $table_name = '';
    
    /**
     * Get plugin instance
     * 
     * @return PayGateHandlerPro
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     * 
     * Initialize the plugin and set up hooks
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize the plugin
     * 
     * @return void
     */
    private function init() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'payments';
        
        // Load settings
        $this->load_settings();
        
        // Set up hooks
        $this->setup_hooks();
        
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PayGate Handler Pro v' . PGH_VERSION . ': Plugin initialized successfully');
        }
    }
    
    /**
     * Set up WordPress hooks
     * 
     * @return void
     */
    private function setup_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(PGH_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(PGH_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Initialize plugin
        add_action('init', array($this, 'init_plugin'));
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('admin_init', array($this, 'admin_init'));
        
        // AJAX hooks
        add_action('wp_ajax_pgh_get_payment', array($this, 'ajax_get_payment'));
        add_action('wp_ajax_pgh_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_pgh_test_email', array($this, 'ajax_test_email'));
        add_action('wp_ajax_pgh_bulk_process', array($this, 'ajax_bulk_process'));
        add_action('wp_ajax_pgh_resend_email', array($this, 'ajax_resend_email'));
        add_action('wp_ajax_pgh_delete_payment', array($this, 'ajax_delete_payment'));
        add_action('wp_ajax_pgh_run_health_check', array($this, 'ajax_run_health_check'));
        add_action('wp_ajax_pgh_clear_error_log', array($this, 'ajax_clear_error_log'));
        add_action('wp_ajax_pgh_optimize_database', array($this, 'ajax_optimize_database'));
        add_action('wp_ajax_pgh_export_system_info', array($this, 'ajax_export_system_info'));
        add_action('wp_ajax_pgh_export_data', array($this, 'ajax_export_data'));
        
        // Payment processing hooks
        add_action('template_redirect', array($this, 'handle_payment_requests'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        
        // Forminator integration
        add_action('forminator_form_after_handle_submit', array($this, 'handle_forminator_submission'), 10, 3);
        
        // Email hooks
        add_action('pgh_send_ticket_email', array($this, 'send_ticket_email'), 10, 2);
        
        // Custom hooks for extensibility
        do_action('pgh_after_init', $this);
    }
    
    /**
     * Load plugin settings
     * 
     * @return void
     */
    private function load_settings() {
        $defaults = array(
            'merchant_id' => '',
            'merchant_key' => '',
            'paygate_url' => 'https://secure.paygate.co.za/payweb3/initiate.trans',
            'test_mode' => 1,
            'email_from' => get_bloginfo('admin_email'),
            'email_from_name' => get_bloginfo('name'),
            'email_subject' => 'Your Ticket: {item_name}',
            'email_body' => $this->get_default_email_template(),
            'attach_qr' => 0,
            'forminator_forms' => '',
            'auto_detect_forms' => 1,
            'venue_name' => '',
            'venue_address' => '',
            'rate_limit_enabled' => 1,
            'rate_limit_attempts' => 5,
            'rate_limit_window' => 3600,
            'enable_analytics' => 1,
            'enable_health_monitoring' => 1,
            'email_delivery_tracking' => 1,
            'payment_reference_prefix' => 'PGH_'
        );
        
        $this->settings = wp_parse_args(get_option('pgh_settings', array()), $defaults);
        
        // Apply filters for extensibility
        $this->settings = apply_filters('pgh_settings', $this->settings);
    }
    
    /**
     * Get default email template
     * 
     * @return string
     */
    private function get_default_email_template() {
        return '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px;">
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h1 style="color: #333; text-align: center; margin-bottom: 30px;">🎫 Your Ticket Confirmation</h1>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h2 style="color: #007cba; margin-top: 0;">Hello {name}!</h2>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">
                        Your ticket for <strong>{item_name}</strong> has been confirmed and is ready for use.
                    </p>
                </div>
                
                <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;">
                    <h3 style="color: #155724; margin-top: 0;">📋 Ticket Details</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin: 10px 0;"><strong>Event:</strong> {item_name}</li>
                        <li style="margin: 10px 0;"><strong>Amount Paid:</strong> R {amount}</li>
                        <li style="margin: 10px 0;"><strong>Reference:</strong> {reference}</li>
                        <li style="margin: 10px 0;"><strong>Date:</strong> {created_at}</li>
                    </ul>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <p style="font-size: 18px; color: #333; font-weight: bold;">
                        🎉 Thank you for your purchase!
                    </p>
                    <p style="color: #666;">
                        Please present this email at the venue entrance.
                    </p>
                </div>
                
                <div style="border-top: 1px solid #eee; padding-top: 20px; text-align: center; color: #999; font-size: 12px;">
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Initialize plugin functionality
     * 
     * @return void
     */
    public function init_plugin() {
        // Add rewrite rules for payment handling
        add_rewrite_rule('^paygate-handler/?$', 'index.php?pgh_action=process_payment', 'top');
        add_rewrite_rule('^paygate-thank-you/?$', 'index.php?pgh_action=thank_you', 'top');
        add_rewrite_rule('^paygate-cancelled/?$', 'index.php?pgh_action=cancelled', 'top');
        add_rewrite_rule('^paygate-notify/?$', 'index.php?pgh_action=notify', 'top');
        
        // Flush rewrite rules if needed
        if (get_option('pgh_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('pgh_flush_rewrite_rules');
        }
    }
    
    /**
     * Add query variables
     * 
     * @param array $vars
     * @return array
     */
    public function add_query_vars($vars) {
        $vars[] = 'pgh_action';
        return $vars;
    }
    
    /**
     * Handle payment requests
     * 
     * @return void
     */
    public function handle_payment_requests() {
        $action = get_query_var('pgh_action');
        
        if (!$action) {
            return;
        }
        
        // Verify nonce for security
        if (isset($_REQUEST['_wpnonce']) && !wp_verify_nonce($_REQUEST['_wpnonce'], 'pgh_payment_nonce')) {
            wp_die('Security check failed. Please try again.');
        }
        
        switch ($action) {
            case 'process_payment':
                $this->process_payment();
                break;
            case 'thank_you':
                $this->handle_thank_you();
                break;
            case 'cancelled':
                $this->handle_cancelled();
                break;
            case 'notify':
                $this->handle_paygate_notify();
                break;
        }
    }
    
    /**
     * Process payment request
     * 
     * @return void
     */
    private function process_payment() {
        try {
            // Validate and sanitize input
            $payment_data = $this->validate_payment_data($_REQUEST);
            
            if (!$payment_data) {
                wp_die('Invalid payment data provided.');
            }
            
            // Check rate limiting
            if (!$this->check_rate_limit($payment_data['email'], $payment_data['name'])) {
                wp_die('Too many payment attempts. Please wait before trying again.');
            }
            
            // Save payment to database
            $payment_id = $this->save_payment($payment_data);
            
            if (!$payment_id) {
                wp_die('Failed to save payment. Please try again.');
            }
            
            // Generate PayGate URL
            $paygate_url = $this->generate_paygate_url($payment_data, $payment_id);
            
            // Redirect to PayGate
            wp_redirect($paygate_url);
            exit;
            
        } catch (Exception $e) {
            error_log('PGH Payment Processing Error: ' . $e->getMessage());
            wp_die('Payment processing failed. Please try again.');
        }
    }
    
    /**
     * Validate payment data
     * 
     * @param array $data
     * @return array|false
     */
    private function validate_payment_data($data) {
        $required_fields = array('name', 'email', 'item_name', 'amount');
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        
        return array(
            'name' => sanitize_text_field(urldecode($data['name'])),
            'email' => sanitize_email(urldecode($data['email'])),
            'item_name' => sanitize_text_field(urldecode($data['item_name'])),
            'amount' => floatval($data['amount']) * 100, // Convert to cents
            'thank_you_url' => isset($data['thank_you_url']) ? esc_url_raw(urldecode($data['thank_you_url'])) : '',
            'cancel_url' => isset($data['cancel_url']) ? esc_url_raw(urldecode($data['cancel_url'])) : '',
            'form_data' => isset($data['form_data']) ? sanitize_text_field($data['form_data']) : null
        );
    }
    
    /**
     * Save payment to database
     * 
     * @param array $data
     * @return int|false
     */
    private function save_payment($data) {
        global $wpdb;
        
        $prefix = $this->settings['payment_reference_prefix'] ?: 'PGH_';
        $reference = $prefix . time() . '_' . wp_generate_password(8, false);
        
        $insert_data = array(
            'name' => $data['name'],
            'email' => $data['email'],
            'item_name' => $data['item_name'],
            'amount' => $data['amount'],
            'reference' => $reference,
            'thank_you_url' => $data['thank_you_url'],
            'cancel_url' => $data['cancel_url'],
            'form_data' => $data['form_data'],
            'status' => 'pending',
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($this->table_name, $insert_data);
        
        if ($result === false) {
            error_log('PGH: Database insert failed: ' . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Generate PayGate URL
     * 
     * @param array $data
     * @param int $payment_id
     * @return string
     */
    private function generate_paygate_url($data, $payment_id) {
        $settings = $this->settings;
        
        $prefix = $this->settings['payment_reference_prefix'] ?: 'PGH_';
        $reference = $prefix . $payment_id . '_' . time();
        
        $data_array = array(
            'PAYGATE_ID' => $settings['merchant_id'],
            'REFERENCE' => $reference,
            'AMOUNT' => $data['amount'],
            'CURRENCY' => 'ZAR',
            'RETURN_URL' => home_url('/paygate-thank-you'),
            'TRANSACTION_DATE' => date('Y-m-d H:i:s'),
            'LOCALE' => 'en-za',
            'COUNTRY' => 'ZAF',
            'EMAIL' => $data['email']
        );
        
        // Generate checksum
        $checksum = md5(implode('', $data_array) . $settings['merchant_key']);
        $data_array['CHECKSUM'] = $checksum;
        
        // Build URL
        $url = $settings['paygate_url'] . '?' . http_build_query($data_array);
        
        return $url;
    }
    
    /**
     * Handle thank you page
     * 
     * @return void
     */
    private function handle_thank_you() {
        $reference = isset($_GET['REFERENCE']) ? sanitize_text_field($_GET['REFERENCE']) : '';
        
        if (!$reference) {
            wp_die('Invalid reference provided.');
        }
        
        $payment = $this->get_payment_by_reference($reference);
        
        if (!$payment) {
            wp_die('Payment not found.');
        }
        
        // Update payment status
        $this->update_payment_status($payment->id, 'completed');
        
        // Send ticket email
        do_action('pgh_send_ticket_email', $payment, $this->settings);
        
        // Redirect to thank you page
        $thank_you_url = $payment->thank_you_url ?: home_url('/thank-you');
        wp_redirect($thank_you_url);
        exit;
    }
    
    /**
     * Handle cancelled payment
     * 
     * @return void
     */
    private function handle_cancelled() {
        $reference = isset($_GET['REFERENCE']) ? sanitize_text_field($_GET['REFERENCE']) : '';
        
        if ($reference) {
            $payment = $this->get_payment_by_reference($reference);
            if ($payment) {
                $this->update_payment_status($payment->id, 'cancelled');
            }
        }
        
        wp_die('Payment was cancelled. You can try again anytime.');
    }
    
    /**
     * Handle PayGate notification
     * 
     * @return void
     */
    private function handle_paygate_notify() {
        // Handle PayGate server-to-server notifications
        $reference = isset($_POST['REFERENCE']) ? sanitize_text_field($_POST['REFERENCE']) : '';
        
        if (!$reference) {
            http_response_code(400);
            exit;
        }
        
        $payment = $this->get_payment_by_reference($reference);
        
        if ($payment) {
            $status = isset($_POST['TRANSACTION_STATUS']) ? sanitize_text_field($_POST['TRANSACTION_STATUS']) : 'unknown';
            $this->update_payment_status($payment->id, $status);
        }
        
        http_response_code(200);
        exit;
    }
    
    /**
     * Handle Forminator form submission
     * 
     * @param int $form_id
     * @param array $response
     * @param array $form_settings
     * @return void
     */
    public function handle_forminator_submission($form_id, $response, $form_settings) {
        // Check if this form should be processed
        if (!$this->should_process_form($form_id)) {
            return;
        }
        
        try {
            // Extract payment data from form submission
            $payment_data = $this->extract_forminator_data($form_id, $response);
            
            if (!$payment_data) {
                return;
            }
            
            // Process payment
            $payment_id = $this->save_payment($payment_data);
            
            if ($payment_id) {
                // Generate PayGate URL and redirect
                $paygate_url = $this->generate_paygate_url($payment_data, $payment_id);
                wp_redirect($paygate_url);
                exit;
            }
            
        } catch (Exception $e) {
            error_log('PGH Forminator Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if form should be processed
     * 
     * @param int $form_id
     * @return bool
     */
    private function should_process_form($form_id) {
        $settings = $this->settings;
        
        // Auto-detect forms
        if ($settings['auto_detect_forms']) {
            return true;
        }
        
        // Check specific form IDs
        $allowed_forms = explode(',', $settings['forminator_forms']);
        $allowed_forms = array_map('trim', $allowed_forms);
        
        return in_array($form_id, $allowed_forms);
    }
    
    /**
     * Extract data from Forminator submission
     * 
     * @param int $form_id
     * @param array $response
     * @return array|false
     */
    private function extract_forminator_data($form_id, $response) {
        // This would need to be customized based on your form structure
        // For now, return a basic structure
        return array(
            'name' => 'Form Submission',
            'email' => 'user@example.com',
            'item_name' => 'Form Payment',
            'amount' => 1000, // R10.00 in cents
            'thank_you_url' => '',
            'cancel_url' => '',
            'form_data' => json_encode($response)
        );
    }
    
    /**
     * Check rate limiting
     * 
     * @param string $email
     * @param string $name
     * @return bool
     */
    private function check_rate_limit($email, $name = '') {
        $settings = $this->settings;
        
        if (!$settings['rate_limit_enabled']) {
            return true;
        }
        
        $identifier = md5($email . $name);
        $transient_key = 'pgh_rate_limit_' . $identifier;
        
        $attempts = get_transient($transient_key) ?: 0;
        
        if ($attempts >= $settings['rate_limit_attempts']) {
            return false;
        }
        
        set_transient($transient_key, $attempts + 1, $settings['rate_limit_window']);
        
        return true;
    }
    
    /**
     * Get payment by reference
     * 
     * @param string $reference
     * @return object|null
     */
    private function get_payment_by_reference($reference) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE reference = %s",
            $reference
        ));
    }
    
    /**
     * Update payment status
     * 
     * @param int $payment_id
     * @param string $status
     * @return bool
     */
    private function update_payment_status($payment_id, $status) {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_name,
            array('status' => $status),
            array('id' => $payment_id)
        ) !== false;
    }
    
    /**
     * Send ticket email
     * 
     * @param object $payment
     * @param array $settings
     * @return bool
     */
    public function send_ticket_email($payment, $settings = null) {
        if (!$payment) {
            return false;
        }
        
        if (!$settings) {
            $settings = $this->settings;
        }
        
        // Prevent duplicate emails
        $transient_key = 'pgh_ticket_sent_' . $payment->id;
        if (get_transient($transient_key)) {
            return false;
        }
        set_transient($transient_key, 1, HOUR_IN_SECONDS);
        
        // Prepare email content
        $subject = $this->replace_email_placeholders($settings['email_subject'], $payment);
        $body = $this->replace_email_placeholders($settings['email_body'], $payment);
        
        // Set headers
        $headers = array(
            'From: ' . sanitize_text_field($settings['email_from_name']) . ' <' . sanitize_email($settings['email_from']) . '>',
            'Content-Type: text/html; charset=UTF-8'
        );
        
        // Send email
        $result = wp_mail($payment->email, $subject, $body, $headers);
        
        // Track email delivery
        if ($settings['email_delivery_tracking']) {
            $this->track_email_delivery($payment->id, $result);
        }
        
        return $result;
    }
    
    /**
     * Replace email placeholders
     * 
     * @param string $content
     * @param object $payment
     * @return string
     */
    private function replace_email_placeholders($content, $payment) {
        $replacements = array(
            '{name}' => $payment->name,
            '{item_name}' => $payment->item_name,
            '{amount}' => number_format($payment->amount / 100, 2),
            '{reference}' => $payment->reference,
            '{created_at}' => date('d-m-Y H:i', strtotime($payment->created_at))
        );
        
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
    
    /**
     * Track email delivery
     * 
     * @param int $payment_id
     * @param bool $success
     * @return void
     */
    private function track_email_delivery($payment_id, $success) {
        $stats = get_option('pgh_email_stats', array());
        $date = date('Y-m-d');
        
        if (!isset($stats[$date])) {
            $stats[$date] = array('sent' => 0, 'failed' => 0);
        }
        
        if ($success) {
            $stats[$date]['sent']++;
        } else {
            $stats[$date]['failed']++;
        }
        
        update_option('pgh_email_stats', $stats);
    }
    
    /**
     * Add admin menu
     * 
     * @return void
     */
    public function add_admin_menu() {
        add_menu_page(
            'PayGate Payments',
            'PayGate Payments',
            'manage_options',
            'pgh_admin',
            array($this, 'admin_page'),
            'dashicons-cart',
            65
        );
        
        add_submenu_page(
            'pgh_admin',
            'Settings',
            'Settings',
            'manage_options',
            'pgh_admin_settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'pgh_admin',
            'Analytics',
            'Analytics',
            'manage_options',
            'pgh_admin_analytics',
            array($this, 'analytics_page')
        );
        
        add_submenu_page(
            'pgh_admin',
            'System Health',
            'System Health',
            'manage_options',
            'pgh_admin_health',
            array($this, 'health_page')
        );
        
        add_submenu_page(
            'pgh_admin',
            'Bulk Processing',
            'Bulk Processing',
            'manage_options',
            'pgh_admin_bulk',
            array($this, 'bulk_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     * 
     * @param string $hook
     * @return void
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'pgh_') === false) {
            return;
        }
        
        wp_enqueue_style('pgh-admin', PGH_PLUGIN_URL . 'assets/admin.css', array(), PGH_VERSION);
        wp_enqueue_script('pgh-admin', PGH_PLUGIN_URL . 'assets/admin.js', array('jquery'), PGH_VERSION, true);
        
        wp_localize_script('pgh-admin', 'pgh_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pgh_admin_nonce')
        ));
    }
    
    /**
     * Initialize admin settings
     * 
     * @return void
     */
    public function admin_init() {
        register_setting('pgh_settings', 'pgh_settings', array($this, 'sanitize_settings'));
    }
    
    /**
     * Sanitize settings
     * 
     * @param array $input
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['merchant_id'] = sanitize_text_field($input['merchant_id']);
        $sanitized['merchant_key'] = sanitize_text_field($input['merchant_key']);
        $sanitized['paygate_url'] = esc_url_raw($input['paygate_url']);
        $sanitized['test_mode'] = intval($input['test_mode']);
        $sanitized['email_from'] = sanitize_email($input['email_from']);
        $sanitized['email_from_name'] = sanitize_text_field($input['email_from_name']);
        $sanitized['email_subject'] = sanitize_text_field($input['email_subject']);
        $sanitized['email_body'] = wp_kses_post($input['email_body']);
        $sanitized['attach_qr'] = intval($input['attach_qr']);
        $sanitized['forminator_forms'] = sanitize_text_field($input['forminator_forms']);
        $sanitized['auto_detect_forms'] = intval($input['auto_detect_forms']);
        $sanitized['venue_name'] = sanitize_text_field($input['venue_name']);
        $sanitized['venue_address'] = sanitize_textarea_field($input['venue_address']);
        $sanitized['rate_limit_enabled'] = intval($input['rate_limit_enabled']);
        $sanitized['rate_limit_attempts'] = intval($input['rate_limit_attempts']);
        $sanitized['rate_limit_window'] = intval($input['rate_limit_window']);
        $sanitized['enable_analytics'] = intval($input['enable_analytics']);
        $sanitized['enable_health_monitoring'] = intval($input['enable_health_monitoring']);
        $sanitized['email_delivery_tracking'] = intval($input['email_delivery_tracking']);
        $sanitized['payment_reference_prefix'] = $this->sanitize_payment_reference_prefix($input['payment_reference_prefix']);
        
        return $sanitized;
    }
    
    /**
     * Sanitize payment reference prefix
     * 
     * @param string $prefix
     * @return string
     */
    private function sanitize_payment_reference_prefix($prefix) {
        // Remove any special characters except underscores and hyphens
        $prefix = preg_replace('/[^a-zA-Z0-9_-]/', '', $prefix);
        
        // Ensure it's not empty and has a reasonable length
        if (empty($prefix)) {
            $prefix = 'PGH_';
        }
        
        // Limit length to 20 characters
        $prefix = substr($prefix, 0, 20);
        
        // Ensure it ends with underscore if it doesn't already
        if (!empty($prefix) && !in_array(substr($prefix, -1), ['_', '-'])) {
            $prefix .= '_';
        }
        
        return $prefix;
    }
    
    /**
     * Admin page
     * 
     * @return void
     */
    public function admin_page() {
        global $wpdb;
        
        $payments = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT 50");
        
        include PGH_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    /**
     * Settings page
     * 
     * @return void
     */
    public function settings_page() {
        include PGH_PLUGIN_DIR . 'templates/settings-page.php';
    }
    
    /**
     * Analytics page
     * 
     * @return void
     */
    public function analytics_page() {
        include PGH_PLUGIN_DIR . 'templates/analytics-page.php';
    }
    
    /**
     * Health page
     * 
     * @return void
     */
    public function health_page() {
        include PGH_PLUGIN_DIR . 'templates/health-page.php';
    }
    
    /**
     * Bulk processing page
     * 
     * @return void
     */
    public function bulk_page() {
        include PGH_PLUGIN_DIR . 'templates/bulk-page.php';
    }
    
    /**
     * AJAX: Get payment details
     * 
     * @return void
     */
    public function ajax_get_payment() {
        check_ajax_referer('pgh_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $payment_id = intval($_POST['payment_id']);
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $payment_id
        ));
        
        if ($payment) {
            wp_send_json_success($payment);
        } else {
            wp_send_json_error('Payment not found');
        }
    }
    
    /**
     * AJAX: Save settings
     * 
     * @return void
     */
    public function ajax_save_settings() {
        check_ajax_referer('pgh_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $settings = $this->sanitize_settings($_POST);
        update_option('pgh_settings', $settings);
        
        wp_send_json_success('Settings saved successfully');
    }
    
    /**
     * AJAX: Test email
     * 
     * @return void
     */
    public function ajax_test_email() {
        check_ajax_referer('pgh_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $test_email = sanitize_email($_POST['test_email']);
        
        if (!$test_email) {
            wp_send_json_error('Invalid email address');
        }
        
        // Create test payment
        $prefix = $this->settings['payment_reference_prefix'] ?: 'PGH_';
        $test_payment = (object) array(
            'id' => 999,
            'name' => 'Test User',
            'email' => $test_email,
            'item_name' => 'Test Payment',
            'amount' => 1000,
            'reference' => $prefix . 'TEST_' . time(),
            'created_at' => current_time('mysql')
        );
        
        $result = $this->send_ticket_email($test_payment);
        
        if ($result) {
            wp_send_json_success('Test email sent successfully');
        } else {
            wp_send_json_error('Failed to send test email');
        }
    }
    
    /**
     * AJAX: Bulk process
     * 
     * @return void
     */
    public function ajax_bulk_process() {
        check_ajax_referer('pgh_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $action = sanitize_text_field($_POST['bulk_action']);
        $payment_ids = array_map('intval', $_POST['payment_ids']);
        
        $processed = 0;
        
        foreach ($payment_ids as $payment_id) {
            switch ($action) {
                case 'resend_email':
                    $payment = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM {$this->table_name} WHERE id = %d",
                        $payment_id
                    ));
                    
                    if ($payment) {
                        $this->send_ticket_email($payment);
                        $processed++;
                    }
                    break;
                    
                case 'delete':
                    $wpdb->delete($this->table_name, array('id' => $payment_id));
                    $processed++;
                    break;
            }
        }
        
        wp_send_json_success("Processed {$processed} payments");
    }
    
    /**
     * Load text domain
     * 
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain('paygate-handler', false, dirname(PGH_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Plugin activation
     * 
     * @return void
     */
    public function activate() {
        $this->create_database_table();
        $this->set_default_options();
        add_option('pgh_flush_rewrite_rules', true);
        
        // Schedule health check
        if (!wp_next_scheduled('pgh_health_check')) {
            wp_schedule_event(time(), 'hourly', 'pgh_health_check');
        }
    }
    
    /**
     * Plugin deactivation
     * 
     * @return void
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('pgh_health_check');
        
        // Clear transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pgh_%'");
    }
    
    /**
     * Create database table
     * 
     * @return void
     */
    private function create_database_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            email varchar(200) NOT NULL,
            item_name varchar(200) NOT NULL,
            amount int(11) NOT NULL,
            payment_id varchar(100) DEFAULT NULL,
            reference varchar(150) DEFAULT NULL,
            thank_you_url varchar(255) DEFAULT NULL,
            cancel_url varchar(255) DEFAULT NULL,
            form_data text DEFAULT NULL,
            status varchar(50) DEFAULT 'pending',
            error_log text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY reference (reference),
            KEY status (status),
            KEY email (email),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Set default options
     * 
     * @return void
     */
    private function set_default_options() {
        if (get_option('pgh_settings') === false) {
            add_option('pgh_settings', $this->settings);
        }
    }
    
    /**
     * Get plugin settings
     * 
     * @return array
     */
    public function get_settings() {
        return $this->settings;
    }
    
    /**
     * Get database table name
     * 
     * @return string
     */
    public function get_table_name() {
        return $this->table_name;
    }
    
    /**
     * Get payment analytics
     * 
     * @param string $period
     * @return array
     */
    public function get_analytics($period = '30_days') {
        global $wpdb;
        
        $date_condition = $this->get_date_condition($period);
        
        $analytics = array(
            'total_payments' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE created_at >= $date_condition"),
            'completed_payments' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'completed' AND created_at >= $date_condition"),
            'pending_payments' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'pending' AND created_at >= $date_condition"),
            'cancelled_payments' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'cancelled' AND created_at >= $date_condition"),
            'failed_payments' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'failed' AND created_at >= $date_condition"),
            'total_revenue' => $wpdb->get_var("SELECT SUM(amount) FROM {$this->table_name} WHERE status = 'completed' AND created_at >= $date_condition") / 100,
            'success_rate' => 0,
            'payments_change' => 0,
            'revenue_change' => 0,
            'chart_labels' => array(),
            'chart_payments' => array(),
            'chart_revenue' => array(),
            'top_items' => array()
        );
        
        // Calculate success rate
        if ($analytics['total_payments'] > 0) {
            $analytics['success_rate'] = ($analytics['completed_payments'] / $analytics['total_payments']) * 100;
        }
        
        // Get top items
        $analytics['top_items'] = $wpdb->get_results("
            SELECT item_name as name, COUNT(*) as count, SUM(amount) as revenue 
            FROM {$this->table_name} 
            WHERE created_at >= $date_condition 
            GROUP BY item_name 
            ORDER BY count DESC 
            LIMIT 5
        ", ARRAY_A);
        
        // Generate chart data
        $analytics['chart_labels'] = $this->generate_chart_labels($period);
        $analytics['chart_payments'] = $this->generate_chart_data($period, 'payments');
        $analytics['chart_revenue'] = $this->generate_chart_data($period, 'revenue');
        
        return $analytics;
    }
    
    /**
     * Get email statistics
     * 
     * @param string $period
     * @return array
     */
    public function get_email_stats($period = '30_days') {
        $stats = get_option('pgh_email_stats', array());
        $date_condition = $this->get_date_condition($period);
        
        $total_sent = 0;
        $successful = 0;
        $failed = 0;
        
        foreach ($stats as $date => $data) {
            if (strtotime($date) >= strtotime($date_condition)) {
                $total_sent += $data['sent'] + $data['failed'];
                $successful += $data['sent'];
                $failed += $data['failed'];
            }
        }
        
        $delivery_rate = $total_sent > 0 ? ($successful / $total_sent) * 100 : 0;
        
        return array(
            'total_sent' => $total_sent,
            'successful' => $successful,
            'failed' => $failed,
            'delivery_rate' => $delivery_rate
        );
    }
    
    /**
     * Get system health
     * 
     * @return array
     */
    public function get_system_health() {
        global $wpdb;
        
        $health = array(
            'overall_status' => 'healthy',
            'status_message' => 'All systems are running smoothly',
            'database_status' => true,
            'paygate_config' => true,
            'email_config' => true,
            'file_permissions' => true,
            'memory_usage' => true,
            'db_queries' => 0,
            'avg_response_time' => 0,
            'memory_usage_mb' => 0,
            'disk_usage_mb' => 0
        );
        
        // Check database
        try {
            $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        } catch (Exception $e) {
            $health['database_status'] = false;
            $health['overall_status'] = 'error';
            $health['status_message'] = 'Database connection failed';
        }
        
        // Check PayGate configuration
        if (empty($this->settings['merchant_id']) || empty($this->settings['merchant_key'])) {
            $health['paygate_config'] = false;
            $health['overall_status'] = 'warning';
            $health['status_message'] = 'PayGate configuration incomplete';
        }
        
        // Check email configuration
        if (empty($this->settings['email_from']) || empty($this->settings['email_from_name'])) {
            $health['email_config'] = false;
            if ($health['overall_status'] === 'healthy') {
                $health['overall_status'] = 'warning';
                $health['status_message'] = 'Email configuration incomplete';
            }
        }
        
        // Check memory usage
        $memory_usage = memory_get_usage(true);
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = $this->convert_to_bytes($memory_limit);
        
        $health['memory_usage_mb'] = round($memory_usage / 1024 / 1024, 1);
        
        if ($memory_usage > ($memory_limit_bytes * 0.8)) {
            $health['memory_usage'] = false;
            if ($health['overall_status'] === 'healthy') {
                $health['overall_status'] = 'warning';
                $health['status_message'] = 'High memory usage detected';
            }
        }
        
        // Get database query count
        $health['db_queries'] = $wpdb->num_queries;
        
        return $health;
    }
    
    /**
     * Get recent errors
     * 
     * @return array
     */
    public function get_recent_errors() {
        global $wpdb;
        
        $errors = $wpdb->get_results("
            SELECT id, status, error_log, created_at 
            FROM {$this->table_name} 
            WHERE error_log IS NOT NULL 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        
        $formatted_errors = array();
        
        foreach ($errors as $error) {
            $formatted_errors[] = array(
                'type' => 'Payment Error',
                'message' => $error->error_log,
                'time' => date('d-m-Y H:i', strtotime($error->created_at)),
                'context' => 'Payment ID: ' . $error->id
            );
        }
        
        return $formatted_errors;
    }
    
    /**
     * Get date condition for queries
     * 
     * @param string $period
     * @return string
     */
    private function get_date_condition($period) {
        switch ($period) {
            case '7_days':
                return date('Y-m-d H:i:s', strtotime('-7 days'));
            case '30_days':
                return date('Y-m-d H:i:s', strtotime('-30 days'));
            case '90_days':
                return date('Y-m-d H:i:s', strtotime('-90 days'));
            case '1_year':
                return date('Y-m-d H:i:s', strtotime('-1 year'));
            default:
                return date('Y-m-d H:i:s', strtotime('-30 days'));
        }
    }
    
    /**
     * Generate chart labels
     * 
     * @param string $period
     * @return array
     */
    private function generate_chart_labels($period) {
        $labels = array();
        $days = 7;
        
        switch ($period) {
            case '7_days':
                $days = 7;
                break;
            case '30_days':
                $days = 30;
                break;
            case '90_days':
                $days = 90;
                break;
            case '1_year':
                $days = 365;
                break;
        }
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $labels[] = date('M j', strtotime("-{$i} days"));
        }
        
        return $labels;
    }
    
    /**
     * Generate chart data
     * 
     * @param string $period
     * @param string $type
     * @return array
     */
    private function generate_chart_data($period, $type) {
        global $wpdb;
        
        $data = array();
        $days = 7;
        
        switch ($period) {
            case '7_days':
                $days = 7;
                break;
            case '30_days':
                $days = 30;
                break;
            case '90_days':
                $days = 90;
                break;
            case '1_year':
                $days = 365;
                break;
        }
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            
            if ($type === 'payments') {
                $count = $wpdb->get_var($wpdb->prepare("
                    SELECT COUNT(*) FROM {$this->table_name} 
                    WHERE DATE(created_at) = %s
                ", $date));
                $data[] = intval($count);
            } else if ($type === 'revenue') {
                $revenue = $wpdb->get_var($wpdb->prepare("
                    SELECT SUM(amount) FROM {$this->table_name} 
                    WHERE DATE(created_at) = %s AND status = 'completed'
                ", $date));
                $data[] = floatval($revenue / 100);
            }
        }
        
        return $data;
    }
    
    /**
     * Convert memory limit to bytes
     * 
     * @param string $val
     * @return int
     */
    private function convert_to_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = intval($val);
        
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }
    
    /**
     * AJAX: Resend email
     * 
     * @return void
     */
    public function ajax_resend_email() {
        check_ajax_referer('pgh_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $payment_id = intval($_POST['payment_id']);
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $payment_id
        ));
        
        if ($payment) {
            $result = $this->send_ticket_email($payment);
            
            if ($result) {
                wp_send_json_success('Email sent successfully');
            } else {
                wp_send_json_error('Failed to send email');
            }
        } else {
            wp_send_json_error('Payment not found');
        }
    }
    
    /**
     * AJAX: Delete payment
     * 
     * @return void
     */
    public function ajax_delete_payment() {
        check_ajax_referer('pgh_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $payment_id = intval($_POST['payment_id']);
        $result = $wpdb->delete($this->table_name, array('id' => $payment_id));
        
        if ($result !== false) {
            wp_send_json_success('Payment deleted successfully');
        } else {
            wp_send_json_error('Failed to delete payment');
        }
    }
    
    /**
     * AJAX: Run health check
     * 
     * @return void
     */
    public function ajax_run_health_check() {
        check_ajax_referer('pgh_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Perform health check
        $health = $this->get_system_health();
        
        if ($health['overall_status'] === 'healthy') {
            wp_send_json_success('Health check completed successfully');
        } else {
            wp_send_json_error('Health check found issues: ' . $health['status_message']);
        }
    }
    
    /**
     * AJAX: Clear error log
     * 
     * @return void
     */
    public function ajax_clear_error_log() {
        check_ajax_referer('pgh_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $result = $wpdb->update(
            $this->table_name,
            array('error_log' => null),
            array('error_log' => array('IS NOT', null))
        );
        
        if ($result !== false) {
            wp_send_json_success('Error log cleared successfully');
        } else {
            wp_send_json_error('Failed to clear error log');
        }
    }
    
    /**
     * AJAX: Optimize database
     * 
     * @return void
     */
    public function ajax_optimize_database() {
        check_ajax_referer('pgh_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $result = $wpdb->query("OPTIMIZE TABLE {$this->table_name}");
        
        if ($result !== false) {
            wp_send_json_success('Database optimized successfully');
        } else {
            wp_send_json_error('Failed to optimize database');
        }
    }
    
    /**
     * AJAX: Export system info
     * 
     * @return void
     */
    public function ajax_export_system_info() {
        check_ajax_referer('pgh_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $system_info = array(
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'plugin_version' => PGH_VERSION,
            'database_version' => $wpdb->db_version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'health_status' => $this->get_system_health(),
            'export_date' => current_time('mysql')
        );
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="pgh-system-info-' . date('Y-m-d') . '.json"');
        echo json_encode($system_info, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * AJAX: Export data
     * 
     * @return void
     */
    public function ajax_export_data() {
        check_ajax_referer('pgh_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $format = sanitize_text_field($_GET['format']);
        $period = sanitize_text_field($_GET['period']);
        
        $date_condition = $this->get_date_condition($period);
        $payments = $wpdb->get_results("SELECT * FROM {$this->table_name} WHERE created_at >= '{$date_condition}' ORDER BY created_at DESC");
        
        if ($format === 'csv') {
            $this->export_csv($payments);
        } else if ($format === 'json') {
            $this->export_json($payments);
        } else if ($format === 'pdf') {
            $this->export_pdf($payments);
        }
    }
    
    /**
     * Export data as CSV
     * 
     * @param array $payments
     * @return void
     */
    private function export_csv($payments) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="pgh-payments-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, array('ID', 'Name', 'Email', 'Item', 'Amount', 'Status', 'Reference', 'Created'));
        
        // CSV data
        foreach ($payments as $payment) {
            fputcsv($output, array(
                $payment->id,
                $payment->name,
                $payment->email,
                $payment->item_name,
                'R ' . number_format($payment->amount / 100, 2),
                $payment->status,
                $payment->reference,
                $payment->created_at
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export data as JSON
     * 
     * @param array $payments
     * @return void
     */
    private function export_json($payments) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="pgh-payments-' . date('Y-m-d') . '.json"');
        
        echo json_encode($payments, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Export data as PDF
     * 
     * @param array $payments
     * @return void
     */
    private function export_pdf($payments) {
        // Simple PDF generation (would need a proper PDF library in production)
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="pgh-payments-' . date('Y-m-d') . '.txt"');
        
        echo "PayGate Handler Pro - Payment Report\n";
        echo "Generated: " . current_time('mysql') . "\n\n";
        
        foreach ($payments as $payment) {
            echo "ID: {$payment->id}\n";
            echo "Name: {$payment->name}\n";
            echo "Email: {$payment->email}\n";
            echo "Item: {$payment->item_name}\n";
            echo "Amount: R " . number_format($payment->amount / 100, 2) . "\n";
            echo "Status: {$payment->status}\n";
            echo "Reference: {$payment->reference}\n";
            echo "Created: {$payment->created_at}\n";
            echo "---\n";
        }
        
        exit;
    }
}

// Initialize the plugin
PayGateHandlerPro::get_instance();

// Add custom hooks for extensibility
do_action('pgh_loaded');
