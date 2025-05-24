# Quick Installation Guide

## 🚨 **Current Issue**
The package `bashar-shaeb/laravel-flutter-generator` is not yet published to Packagist, so normal `composer require` won't work.

## ✅ **Working Solutions**

### **Option 1: Automated Installation (Recommended)**

**For Linux/Mac:**
```bash
# Download and run the installation script
curl -O https://raw.githubusercontent.com/BasharShaeb/laravel-flutter-generator/main/install-package.sh
chmod +x install-package.sh
./install-package.sh
```

**For Windows:**
```cmd
# Download and run the installation script
curl -O https://raw.githubusercontent.com/BasharShaeb/laravel-flutter-generator/main/install-package.bat
install-package.bat
```

### **Option 2: Manual Installation from GitHub**

```bash
# Step 1: Add GitHub repository
composer config repositories.bashar-shaeb-generator vcs https://github.com/BasharShaeb/laravel-flutter-generator

# Step 2: Install the package
composer require bashar-shaeb/laravel-flutter-generator:dev-main

# Step 3: Publish configuration
php artisan vendor:publish --provider="BasharShaeb\LaravelFlutterGenerator\FlutterGeneratorServiceProvider" --tag="flutter-generator-config"

# Step 4: Verify installation
php artisan list flutter
```

### **Option 3: Local Development Installation**

```bash
# Step 1: Clone the repository
git clone https://github.com/BasharShaeb/laravel-flutter-generator.git

# Step 2: Add local repository to your Laravel project
cd /path/to/your/laravel/project
composer config repositories.local path ../laravel-flutter-generator

# Step 3: Install the package
composer require bashar-shaeb/laravel-flutter-generator @dev

# Step 4: Publish configuration
php artisan vendor:publish --provider="BasharShaeb\LaravelFlutterGenerator\FlutterGeneratorServiceProvider" --tag="flutter-generator-config"
```

### **Option 4: Copy Files Manually**

If Composer installation fails:

```bash
# Step 1: Download the package
git clone https://github.com/BasharShaeb/laravel-flutter-generator.git

# Step 2: Copy to your Laravel project
cp -r laravel-flutter-generator/src app/Packages/LaravelFlutterGenerator

# Step 3: Add to composer.json autoload
```

Add to your Laravel project's `composer.json`:
```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "BasharShaeb\\LaravelFlutterGenerator\\": "app/Packages/LaravelFlutterGenerator/"
        }
    }
}
```

```bash
# Step 4: Update autoloader
composer dump-autoload

# Step 5: Register service provider manually in config/app.php
```

Add to `config/app.php`:
```php
'providers' => [
    // Other providers...
    BasharShaeb\LaravelFlutterGenerator\FlutterGeneratorServiceProvider::class,
],
```

## 🔍 **Verify Installation**

After installation, verify it works:

```bash
# Check available commands
php artisan list flutter

# You should see:
# flutter:generate-all
# flutter:generate-feature  
# flutter:generate-model
# flutter:generate-service
```

## 🚀 **Quick Test**

```bash
# Create a test model
php artisan make:model TestUser -m

# Generate Flutter code
php artisan flutter:generate-model TestUser

# Check generated files
ls flutter_output/models/
```

## 📦 **Publishing to Packagist**

To make normal `composer require` work, the package needs to be published:

### **For Package Author (BasharShaeb):**

1. **Push to GitHub:**
```bash
git add .
git commit -m "Initial release v1.0.0"
git tag v1.0.0
git push origin main --tags
```

2. **Submit to Packagist:**
- Go to [packagist.org](https://packagist.org)
- Click "Submit"
- Enter: `https://github.com/BasharShaeb/laravel-flutter-generator`
- Click "Submit"

3. **After publishing, normal installation will work:**
```bash
composer require bashar-shaeb/laravel-flutter-generator
```

## 🆘 **Troubleshooting**

### **Error: "Could not find package"**
- Use Option 2 or 3 above
- Ensure GitHub repository exists and is public

### **Error: "Class not found"**
- Run `composer dump-autoload`
- Check service provider is registered

### **Error: "Command not found"**
- Clear Laravel cache: `php artisan cache:clear`
- Check if service provider is loaded: `php artisan list`

### **Error: "Permission denied"**
- Make scripts executable: `chmod +x install-package.sh`
- Run with sudo if needed: `sudo ./install-package.sh`

## 📞 **Support**

If you still have issues:
1. Check [GitHub Issues](https://github.com/BasharShaeb/laravel-flutter-generator/issues)
2. Create a new issue with your error message
3. Contact BasharShaeb directly

---

**Once the package is published to Packagist, installation will be as simple as:**
```bash
composer require bashar-shaeb/laravel-flutter-generator
```
