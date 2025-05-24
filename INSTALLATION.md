# Installation Guide

## 🚀 **Quick Installation**

### **Option 1: Install from Packagist (Recommended)**

Once the package is published to Packagist:

```bash
composer require bashar-shaeb/laravel-flutter-generator
```

### **Option 2: Install from GitHub (Development)**

If the package is not yet on Packagist, install directly from GitHub:

```bash
# Add the GitHub repository
composer config repositories.bashar-shaeb-laravel-flutter-generator vcs https://github.com/BasharShaeb/laravel-flutter-generator

# Install the package
composer require bashar-shaeb/laravel-flutter-generator:dev-main
```

### **Option 3: Local Development Installation**

For local development and testing:

```bash
# Clone the repository
git clone https://github.com/BasharShaeb/laravel-flutter-generator.git

# In your Laravel project, add local repository
composer config repositories.local path ../laravel-flutter-generator

# Install the package
composer require bashar-shaeb/laravel-flutter-generator @dev
```

## 🔧 **Troubleshooting Installation Issues**

### **Issue: "Could not find a version matching your minimum-stability"**

**Solution 1: Allow dev versions**
```bash
composer require bashar-shaeb/laravel-flutter-generator @dev
```

**Solution 2: Update composer.json**
```json
{
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

**Solution 3: Explicit version constraint**
```bash
composer require bashar-shaeb/laravel-flutter-generator:dev-main
```

### **Issue: Package not found**

**Solution: Add repository manually**
```bash
composer config repositories.bashar-shaeb-generator vcs https://github.com/BasharShaeb/laravel-flutter-generator
composer require bashar-shaeb/laravel-flutter-generator:dev-main
```

## 📋 **Post-Installation Setup**

### **1. Publish Configuration**
```bash
php artisan vendor:publish --provider="BasharShaeb\LaravelFlutterGenerator\FlutterGeneratorServiceProvider" --tag="flutter-generator-config"
```

### **2. Publish Templates (Optional)**
```bash
php artisan vendor:publish --provider="BasharShaeb\LaravelFlutterGenerator\FlutterGeneratorServiceProvider" --tag="flutter-generator-templates"
```

### **3. Verify Installation**
```bash
php artisan list flutter
```

You should see:
```
flutter:generate-all       Generate complete Flutter application code from all Laravel models
flutter:generate-feature   Generate complete Flutter features (models, services, widgets, screens) from Laravel models
flutter:generate-model     Generate Flutter Dart models from Laravel Eloquent models
flutter:generate-service   Generate Flutter API service classes from Laravel models and routes
```

## 🎯 **Laravel Version Compatibility**

| Laravel Version | PHP Version | Package Version |
|----------------|-------------|-----------------|
| 10.x           | 8.1+        | ✅ Supported    |
| 11.x           | 8.2+        | ✅ Supported    |
| 12.x           | 8.2+        | ✅ Supported    |

## 🚀 **Quick Test**

After installation, test the package:

```bash
# Create a test model
php artisan make:model TestModel -m

# Generate Flutter code
php artisan flutter:generate-model TestModel

# Check generated files
ls flutter_output/models/
```

## 📦 **Publishing to Packagist**

To make the package available via `composer require`:

### **1. Push to GitHub**
```bash
git add .
git commit -m "Initial release"
git tag v1.0.0
git push origin main --tags
```

### **2. Submit to Packagist**
1. Go to [packagist.org](https://packagist.org)
2. Click "Submit"
3. Enter: `https://github.com/BasharShaeb/laravel-flutter-generator`
4. Click "Check"
5. Click "Submit"

### **3. Enable Auto-Update**
1. Go to your package page on Packagist
2. Click "Settings"
3. Set up GitHub webhook for auto-updates

## 🔄 **Update Package**

### **From Packagist:**
```bash
composer update bashar-shaeb/laravel-flutter-generator
```

### **From GitHub:**
```bash
composer update bashar-shaeb/laravel-flutter-generator:dev-main
```

## 🆘 **Support**

If you encounter issues:

1. **Check Requirements**: Ensure PHP 8.1+ and Laravel 10+
2. **Clear Cache**: `composer clear-cache`
3. **Update Composer**: `composer self-update`
4. **Check GitHub Issues**: [Report issues here](https://github.com/BasharShaeb/laravel-flutter-generator/issues)

## 📝 **Development Installation**

For contributing to the package:

```bash
# Fork and clone
git clone https://github.com/YourUsername/laravel-flutter-generator.git
cd laravel-flutter-generator

# Install dependencies
composer install

# Run tests
composer test

# Run quick test
composer quick-test
```

---

**Need help?** Open an issue on [GitHub](https://github.com/BasharShaeb/laravel-flutter-generator/issues) or contact BasharShaeb.
