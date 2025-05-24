#!/bin/bash

# Laravel Flutter Generator - Installation Script
# Author: BasharShaeb

echo "🚀 Laravel Flutter Generator Installation Script"
echo "================================================"

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Error: This doesn't appear to be a Laravel project directory."
    echo "   Please run this script from your Laravel project root."
    exit 1
fi

echo "✅ Laravel project detected"

# Method 1: Try to install from Packagist (if published)
echo ""
echo "📦 Attempting to install from Packagist..."
if composer require bashar-shaeb/laravel-flutter-generator --no-interaction 2>/dev/null; then
    echo "✅ Successfully installed from Packagist!"
    INSTALLED=true
else
    echo "⚠️  Package not found on Packagist (not published yet)"
    INSTALLED=false
fi

# Method 2: Install from GitHub if Packagist failed
if [ "$INSTALLED" = false ]; then
    echo ""
    echo "📦 Installing from GitHub repository..."
    
    # Add GitHub repository
    composer config repositories.bashar-shaeb-laravel-flutter-generator vcs https://github.com/BasharShaeb/laravel-flutter-generator
    
    # Install with dev constraint
    if composer require bashar-shaeb/laravel-flutter-generator:dev-main --no-interaction; then
        echo "✅ Successfully installed from GitHub!"
        INSTALLED=true
    else
        echo "❌ Failed to install from GitHub"
        INSTALLED=false
    fi
fi

# Method 3: Local installation instructions
if [ "$INSTALLED" = false ]; then
    echo ""
    echo "📦 GitHub installation failed. Try local installation:"
    echo ""
    echo "1. Clone the repository:"
    echo "   git clone https://github.com/BasharShaeb/laravel-flutter-generator.git"
    echo ""
    echo "2. Add local repository:"
    echo "   composer config repositories.local path ../laravel-flutter-generator"
    echo ""
    echo "3. Install the package:"
    echo "   composer require bashar-shaeb/laravel-flutter-generator @dev"
    echo ""
    exit 1
fi

# Verify installation
echo ""
echo "🔍 Verifying installation..."
if php artisan list | grep -q "flutter:"; then
    echo "✅ Package installed successfully!"
    echo ""
    echo "Available commands:"
    php artisan list flutter
else
    echo "❌ Installation verification failed"
    exit 1
fi

# Publish configuration
echo ""
echo "📋 Publishing configuration..."
if php artisan vendor:publish --provider="BasharShaeb\LaravelFlutterGenerator\FlutterGeneratorServiceProvider" --tag="flutter-generator-config" --force; then
    echo "✅ Configuration published successfully!"
else
    echo "⚠️  Configuration publishing failed (this is optional)"
fi

# Create output directory
echo ""
echo "📁 Creating output directory..."
mkdir -p flutter_output/{models,services,widgets,screens}
echo "✅ Output directories created!"

# Final instructions
echo ""
echo "🎉 Installation Complete!"
echo "========================"
echo ""
echo "Quick start:"
echo "1. Create a model: php artisan make:model TestModel -m"
echo "2. Generate Flutter code: php artisan flutter:generate-model TestModel"
echo "3. Check output: ls flutter_output/models/"
echo ""
echo "For more information, see README.md"
echo ""
echo "Happy coding! 🚀"
