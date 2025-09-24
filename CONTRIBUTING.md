# Contributing to PayGate Handler Pro

Thank you for your interest in contributing to PayGate Handler Pro! This document provides guidelines and information for contributors.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Contributing Guidelines](#contributing-guidelines)
- [Code Standards](#code-standards)
- [Testing](#testing)
- [Pull Request Process](#pull-request-process)
- [Issue Reporting](#issue-reporting)
- [Feature Requests](#feature-requests)
- [Documentation](#documentation)
- [Release Process](#release-process)

## 🤝 Code of Conduct

This project adheres to a code of conduct that we expect all contributors to follow:

- **Be respectful** and inclusive
- **Be constructive** in feedback and discussions
- **Be patient** with newcomers and questions
- **Be collaborative** and help others learn
- **Be professional** in all interactions

## 🚀 Getting Started

### Prerequisites

- **PHP 7.4+** with WordPress development environment
- **WordPress 5.0+** for testing
- **Git** for version control
- **Basic knowledge** of WordPress plugin development

### Fork and Clone

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/YOUR-USERNAME/PG-Handler.git
   cd PG-Handler
   ```
3. **Add upstream remote**:
   ```bash
   git remote add upstream https://github.com/ronald-designzeen-sketch/PG-Handler.git
   ```

## 🛠️ Development Setup

### Local WordPress Environment

1. **Set up WordPress** locally (XAMPP, WAMP, or Docker)
2. **Install the plugin** in your WordPress installation
3. **Activate the plugin** and configure test settings
4. **Set up PayGate test credentials** for testing

### Development Tools

Recommended tools for development:

- **IDE**: VS Code, PhpStorm, or similar
- **PHP Linting**: PHP_CodeSniffer with WordPress standards
- **Version Control**: Git with meaningful commit messages
- **Testing**: Manual testing in WordPress environment

## 📝 Contributing Guidelines

### Types of Contributions

We welcome several types of contributions:

- **🐛 Bug Fixes**: Fix issues and improve stability
- **✨ New Features**: Add new functionality
- **📚 Documentation**: Improve README, code comments, guides
- **🎨 UI/UX Improvements**: Enhance user interface and experience
- **⚡ Performance**: Optimize code and database queries
- **🔒 Security**: Improve security measures
- **🧪 Testing**: Add tests and improve test coverage

### Contribution Process

1. **Check existing issues** and pull requests
2. **Create an issue** to discuss your contribution
3. **Fork and create a branch** for your feature/fix
4. **Make your changes** following our coding standards
5. **Test your changes** thoroughly
6. **Submit a pull request** with a clear description

## 📏 Code Standards

### PHP Coding Standards

We follow **WordPress PHP Coding Standards**:

```php
// ✅ Good: Proper indentation and spacing
class PayGateHandlerPro {
    
    /**
     * Process payment data
     * 
     * @param array $data Payment data
     * @return bool|int Payment ID or false on failure
     */
    public function process_payment($data) {
        if (empty($data['amount'])) {
            return false;
        }
        
        return $this->save_payment($data);
    }
}

// ❌ Bad: Inconsistent formatting
class PayGateHandlerPro{
public function process_payment($data){
if(empty($data['amount']))return false;
return $this->save_payment($data);}
}
```

### File Organization

```
paygate-handler-pro/
├── paygate-handler-pro.php    # Main plugin file
├── assets/                    # CSS, JS, images
│   ├── admin.css
│   └── admin.js
├── templates/                 # Admin page templates
│   ├── admin-page.php
│   ├── settings-page.php
│   └── ...
├── includes/                  # Additional PHP files (if needed)
├── languages/                 # Translation files (if needed)
├── README.md                  # Documentation
├── LICENSE                    # GPL v2.0 license
└── CONTRIBUTING.md            # This file
```

### Naming Conventions

- **Classes**: `PascalCase` (e.g., `PayGateHandlerPro`)
- **Functions**: `snake_case` (e.g., `process_payment`)
- **Variables**: `snake_case` (e.g., `$payment_data`)
- **Constants**: `UPPER_SNAKE_CASE` (e.g., `PGH_VERSION`)
- **Files**: `kebab-case` (e.g., `admin-page.php`)

### Documentation Standards

All functions and classes must have proper PHPDoc:

```php
/**
 * Process payment and redirect to PayGate
 * 
 * @since 2.1
 * @param array $data Payment data including name, email, amount
 * @return string|false PayGate URL or false on failure
 * @throws Exception If payment data is invalid
 */
public function process_payment($data) {
    // Implementation
}
```

## 🧪 Testing

### Manual Testing

Before submitting changes, please test:

1. **Plugin Activation/Deactivation**
2. **Payment Processing Flow**
3. **Admin Interface Functionality**
4. **Email Sending**
5. **Settings Configuration**
6. **Error Handling**

### Test Scenarios

- **Valid Payment Data**: Test with complete, valid payment information
- **Invalid Payment Data**: Test with missing or invalid data
- **Edge Cases**: Test with empty values, special characters, large amounts
- **Error Conditions**: Test network failures, database errors, etc.

### Browser Testing

Test the admin interface in:
- **Chrome** (latest)
- **Firefox** (latest)
- **Safari** (latest)
- **Edge** (latest)
- **Mobile browsers** (responsive design)

## 🔄 Pull Request Process

### Before Submitting

1. **Update your branch** with the latest changes:
   ```bash
   git fetch upstream
   git checkout main
   git merge upstream/main
   git checkout your-feature-branch
   git rebase main
   ```

2. **Test your changes** thoroughly
3. **Update documentation** if needed
4. **Check code formatting** and standards

### Pull Request Template

When creating a pull request, please include:

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Documentation update
- [ ] Performance improvement
- [ ] Security enhancement

## Testing
- [ ] Manual testing completed
- [ ] Browser testing completed
- [ ] No console errors
- [ ] No PHP errors

## Screenshots (if applicable)
Add screenshots for UI changes

## Checklist
- [ ] Code follows WordPress standards
- [ ] Documentation updated
- [ ] No breaking changes
- [ ] Backward compatibility maintained
```

### Review Process

1. **Automated checks** will run on your PR
2. **Maintainers will review** your code
3. **Feedback will be provided** for improvements
4. **Changes may be requested** before merging
5. **PR will be merged** once approved

## 🐛 Issue Reporting

### Bug Reports

When reporting bugs, please include:

```markdown
**Bug Description**
Clear description of the bug

**Steps to Reproduce**
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected Behavior**
What you expected to happen

**Actual Behavior**
What actually happened

**Environment**
- WordPress Version: X.X.X
- PHP Version: X.X.X
- Plugin Version: X.X.X
- Browser: Chrome/Firefox/etc

**Screenshots**
If applicable, add screenshots

**Additional Context**
Any other context about the problem
```

### Security Issues

For security vulnerabilities, please:

1. **Do NOT** create a public issue
2. **Email directly** to ronald@designzeen.com
3. **Include details** about the vulnerability
4. **Wait for response** before public disclosure

## 💡 Feature Requests

When requesting features:

```markdown
**Feature Description**
Clear description of the feature

**Use Case**
Why is this feature needed?

**Proposed Solution**
How should this feature work?

**Alternatives Considered**
Other solutions you've considered

**Additional Context**
Any other context or screenshots
```

## 📚 Documentation

### Code Documentation

- **PHPDoc comments** for all functions and classes
- **Inline comments** for complex logic
- **README updates** for new features
- **Code examples** in documentation

### User Documentation

- **Installation instructions**
- **Configuration guides**
- **Troubleshooting tips**
- **FAQ updates**

## 🚀 Release Process

### Version Numbering

We follow **Semantic Versioning** (SemVer):

- **MAJOR** (X.0.0): Breaking changes
- **MINOR** (X.Y.0): New features, backward compatible
- **PATCH** (X.Y.Z): Bug fixes, backward compatible

### Release Checklist

- [ ] All tests passing
- [ ] Documentation updated
- [ ] Version number updated
- [ ] Changelog updated
- [ ] Release notes prepared
- [ ] GitHub release created

## 🤔 Questions?

If you have questions about contributing:

1. **Check existing issues** for similar questions
2. **Create a new issue** with the "question" label
3. **Email** ronald@designzeen.com for direct contact
4. **Join discussions** in GitHub issues

## 🙏 Recognition

Contributors will be recognized in:

- **README.md** contributors section
- **Release notes** for significant contributions
- **GitHub contributors** page
- **Plugin credits** (for major contributions)

## 📄 License

By contributing to PayGate Handler Pro, you agree that your contributions will be licensed under the **GNU General Public License v2.0**.

---

**Thank you for contributing to PayGate Handler Pro!** 🎉

Your contributions help make this plugin better for everyone. We appreciate your time and effort in improving the project.
