# PayGate Handler Pro v2.1

A professional WordPress plugin for PayGate payment processing with Forminator integration, dynamic email tickets, analytics dashboard, and comprehensive admin tools.

## 🚀 Features

### Core Payment Processing
- **PayGate Integration**: Full PayGate payment gateway integration
- **Forminator Support**: Seamless integration with Forminator forms
- **Test/Live Mode**: Switch between test and live environments
- **Rate Limiting**: Built-in protection against payment abuse
- **Error Handling**: Comprehensive error logging and handling
- **Custom Payment References**: Customizable payment reference prefixes

### Email System
- **Dynamic Email Templates**: Customizable HTML email templates
- **QR Code Support**: Optional QR code generation for tickets
- **Email Delivery Tracking**: Monitor email success/failure rates
- **Placeholder Support**: Dynamic content replacement in emails

### Admin Dashboard
- **Payment Management**: View, manage, and track all payments
- **Analytics Dashboard**: Comprehensive payment analytics and reporting
- **System Health Monitoring**: Real-time system health checks
- **Bulk Processing**: Bulk operations for payment management
- **Export Functionality**: Export data in CSV, JSON, and PDF formats

### Security & Performance
- **Input Sanitization**: All inputs are properly sanitized
- **Nonce Verification**: CSRF protection on all forms
- **Database Optimization**: Optimized database queries and structure
- **Caching Support**: Built-in caching for improved performance

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- PayGate merchant account
- Forminator plugin (optional, for form integration)

## 🛠️ Installation

### Method 1: Manual Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/paygate-handler/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure your PayGate settings in the admin panel

### Method 2: Using the Installation Helper

1. Upload the plugin files to your WordPress installation
2. Run the `install.php` file in your browser
3. Follow the on-screen instructions

## ⚙️ Configuration

### PayGate Settings

1. Go to **PayGate Payments > Settings**
2. Enter your PayGate credentials:
   - **Merchant ID**: Your PayGate merchant ID
   - **Merchant Key**: Your PayGate merchant key
   - **PayGate URL**: PayGate initiate URL
3. Configure test/live mode
4. Save settings

### Email Configuration

1. Navigate to **PayGate Payments > Settings > Email Settings**
2. Configure email settings:
   - **From Email**: Email address to send from
   - **From Name**: Name to send emails from
   - **Email Subject**: Subject line template
   - **Email Template**: HTML email template
3. Test your email configuration

### Form Integration

1. Go to **PayGate Payments > Settings > Form Integration**
2. Enable auto-detection or specify form IDs
3. Configure venue information
4. Save settings

### Payment Settings

1. Navigate to **PayGate Payments > Settings > Payment Settings**
2. Configure payment reference prefix:
   - **Payment Reference Prefix**: Customize the prefix for payment references
   - Examples: `PGH_`, `Registration_`, `EVENT_`, `ORDER_`, `BOOKING_`
   - Only letters, numbers, underscores, and hyphens allowed
   - Maximum 20 characters
   - Will automatically add underscore if not present
3. Save settings

## 📊 Admin Interface

### Dashboard
- **Payment Overview**: View recent payments and statistics
- **Quick Actions**: Resend emails, view details, delete payments
- **Status Indicators**: Visual status indicators for all payments

### Analytics
- **Payment Trends**: Visual charts showing payment trends
- **Revenue Analysis**: Revenue tracking and analysis
- **Success Rates**: Payment success rate monitoring
- **Top Items**: Most popular payment items

### System Health
- **Database Status**: Database connection and structure checks
- **Configuration Validation**: PayGate and email configuration validation
- **Performance Metrics**: Memory usage, query count, response times
- **Error Monitoring**: Recent error tracking and analysis

### Bulk Processing
- **Bulk Actions**: Resend emails, update status, delete payments
- **Filtering**: Filter payments by status, date, and other criteria
- **Export Options**: Export filtered data in multiple formats

## 🔧 API & Hooks

### Custom Hooks

```php
// After plugin initialization
do_action('pgh_after_init', $plugin_instance);

// Before sending ticket email
do_action('pgh_before_send_email', $payment, $settings);

// After payment processing
do_action('pgh_after_payment_processed', $payment_id, $status);

// Customize settings
add_filter('pgh_settings', function($settings) {
    // Modify settings
    return $settings;
});
```

### AJAX Endpoints

- `pgh_get_payment` - Get payment details
- `pgh_save_settings` - Save plugin settings
- `pgh_test_email` - Send test email
- `pgh_bulk_process` - Process bulk actions
- `pgh_resend_email` - Resend payment email
- `pgh_delete_payment` - Delete payment
- `pgh_run_health_check` - Run system health check
- `pgh_export_data` - Export payment data

## 🎨 Customization

### Email Templates

Email templates support the following placeholders:
- `{name}` - Customer name
- `{item_name}` - Payment item name
- `{amount}` - Payment amount
- `{reference}` - Payment reference
- `{created_at}` - Payment date

### Custom Styling

The plugin includes comprehensive CSS classes for customization:
- `.pgh-admin-wrap` - Main admin wrapper
- `.pgh-stat-card` - Statistics cards
- `.pgh-status-*` - Status indicators
- `.pgh-modal` - Modal dialogs
- `.pgh-button-*` - Button styles

### Database Schema

The plugin creates a `wp_payments` table with the following structure:
- `id` - Primary key
- `name` - Customer name
- `email` - Customer email
- `item_name` - Payment item
- `amount` - Payment amount (in cents)
- `payment_id` - PayGate payment ID
- `reference` - Payment reference
- `thank_you_url` - Thank you page URL
- `cancel_url` - Cancel page URL
- `form_data` - Form submission data
- `status` - Payment status
- `error_log` - Error log
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## 🔒 Security

### Input Validation
- All user inputs are sanitized using WordPress functions
- Email addresses are validated
- URLs are properly escaped
- SQL queries use prepared statements

### Access Control
- Admin functions require `manage_options` capability
- AJAX requests include nonce verification
- Rate limiting prevents abuse

### Data Protection
- Sensitive data is properly encrypted
- Error logs don't expose sensitive information
- Database queries are optimized and secure

## 🚨 Troubleshooting

### Common Issues

**Plugin not activating**
- Check PHP version (7.4+ required)
- Verify file permissions
- Check for PHP errors in debug log

**PayGate integration not working**
- Verify merchant ID and key
- Check PayGate URL configuration
- Ensure test mode is properly configured

**Emails not sending**
- Check email configuration
- Verify SMTP settings
- Test email functionality

**Database errors**
- Run database repair from System Health page
- Check database permissions
- Verify table structure

### Debug Mode

Enable WordPress debug mode to see detailed error messages:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📈 Performance

### Optimization Features
- Database query optimization
- Caching for frequently accessed data
- Efficient pagination for large datasets
- Minimal resource usage

### Best Practices
- Regular database optimization
- Monitor system health
- Keep plugin updated
- Use appropriate server resources

## 🔄 Updates

### Version 2.1 Changes
- Complete code refactoring with class-based architecture
- Enhanced admin interface with modern styling
- Improved analytics and reporting
- Better error handling and logging
- Enhanced security features
- Performance optimizations

### Migration from v2.0
- Settings are automatically migrated
- Database structure is updated automatically
- No manual intervention required

## 📞 Support

### Documentation
- Comprehensive inline documentation
- API reference
- Code examples
- Best practices guide

### Community
- **GitHub Repository**: [Issues and contributions](https://github.com/ronald-designzeen-sketch/PG-Handler)
- **Contributing Guidelines**: [CONTRIBUTING.md](CONTRIBUTING.md)
- **Changelog**: [CHANGELOG.md](CHANGELOG.md)
- **Support**: ronald@designzeen.com

## 🧪 Testing

The plugin has been thoroughly tested and is production-ready. For development testing, you can:

### Manual Testing

1. **Install in WordPress**:
   ```bash
   # Copy to WordPress plugins directory
   cp -r . /path/to/wordpress/wp-content/plugins/paygate-handler-pro/
   ```

2. **Activate and Configure**:
   - Activate the plugin in WordPress Admin
   - Go to **PayGate Payments → Settings**
   - Enter your PayGate credentials
   - Set your custom payment reference prefix

3. **Test Payment Flow**:
   - Create test payments
   - Verify custom payment references
   - Check email delivery

### Code Quality

- **Clean Architecture**: Class-based, singleton pattern
- **Security**: Input validation, sanitization, rate limiting
- **Performance**: Optimized database queries, caching
- **Standards**: WordPress coding standards compliant

## 📄 License

This plugin is licensed under the **GNU General Public License v2.0** (GPL v2).

### License Summary

✅ **You CAN:**
- Use this plugin for any purpose (commercial or personal)
- Modify the code to suit your needs
- Distribute copies of the plugin
- Sell the plugin or services based on it

✅ **You MUST:**
- Keep the original copyright notice
- Include the GPL license with any distribution
- Make source code available if you distribute modified versions
- Use the same GPL license for any derivative works

❌ **You CANNOT:**
- Remove the copyright notice
- Distribute without the GPL license
- Use a different license for derivative works
- Claim you wrote the original code

### Full License

The complete GPL v2 license text is available in the `LICENSE` file.

**Note**: GPL and GNU are the same thing - GPL stands for "GNU General Public License"

## 🙏 Credits

- **Author**: Ronald @ Design Zeen
- **Version**: 2.1
- **WordPress Compatibility**: 5.0+
- **PHP Compatibility**: 7.4+

## 👥 Contributors

We welcome contributions from the community! See our [Contributing Guidelines](CONTRIBUTING.md) for details.

### How to Contribute
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

### Types of Contributions
- 🐛 Bug fixes
- ✨ New features
- 📚 Documentation improvements
- 🎨 UI/UX enhancements
- ⚡ Performance optimizations
- 🔒 Security improvements

## 🔮 Roadmap

### Upcoming Features
- Multi-currency support
- Advanced reporting
- Webhook integration
- Mobile app support
- Third-party integrations

### Contribution
We welcome contributions! Please see our contributing guidelines for more information.

---

**PayGate Handler Pro** - Professional payment processing made simple.
