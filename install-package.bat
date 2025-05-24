@echo off
REM Laravel Flutter Generator - Installation Script for Windows
REM Author: BasharShaeb

echo 🚀 Laravel Flutter Generator Installation Script
echo ================================================

REM Check if we're in a Laravel project
if not exist "artisan" (
    echo ❌ Error: This doesn't appear to be a Laravel project directory.
    echo    Please run this script from your Laravel project root.
    pause
    exit /b 1
)

echo ✅ Laravel project detected

REM Method 1: Try to install from Packagist
echo.
echo 📦 Attempting to install from Packagist...
composer require bashar-shaeb/laravel-flutter-generator --no-interaction >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Successfully installed from Packagist!
    set INSTALLED=true
) else (
    echo ⚠️  Package not found on Packagist ^(not published yet^)
    set INSTALLED=false
)

REM Method 2: Install from GitHub if Packagist failed
if "%INSTALLED%"=="false" (
    echo.
    echo 📦 Installing from GitHub repository...
    
    REM Add GitHub repository
    composer config repositories.bashar-shaeb-laravel-flutter-generator vcs https://github.com/BasharShaeb/laravel-flutter-generator
    
    REM Install with dev constraint
    composer require bashar-shaeb/laravel-flutter-generator:dev-main --no-interaction
    if %errorlevel% equ 0 (
        echo ✅ Successfully installed from GitHub!
        set INSTALLED=true
    ) else (
        echo ❌ Failed to install from GitHub
        set INSTALLED=false
    )
)

REM Method 3: Local installation instructions
if "%INSTALLED%"=="false" (
    echo.
    echo 📦 GitHub installation failed. Try local installation:
    echo.
    echo 1. Clone the repository:
    echo    git clone https://github.com/BasharShaeb/laravel-flutter-generator.git
    echo.
    echo 2. Add local repository:
    echo    composer config repositories.local path ../laravel-flutter-generator
    echo.
    echo 3. Install the package:
    echo    composer require bashar-shaeb/laravel-flutter-generator @dev
    echo.
    pause
    exit /b 1
)

REM Verify installation
echo.
echo 🔍 Verifying installation...
php artisan list | findstr "flutter:" >nul
if %errorlevel% equ 0 (
    echo ✅ Package installed successfully!
    echo.
    echo Available commands:
    php artisan list flutter
) else (
    echo ❌ Installation verification failed
    pause
    exit /b 1
)

REM Publish configuration
echo.
echo 📋 Publishing configuration...
php artisan vendor:publish --provider="BasharShaeb\LaravelFlutterGenerator\FlutterGeneratorServiceProvider" --tag="flutter-generator-config" --force
if %errorlevel% equ 0 (
    echo ✅ Configuration published successfully!
) else (
    echo ⚠️  Configuration publishing failed ^(this is optional^)
)

REM Create output directory
echo.
echo 📁 Creating output directory...
if not exist "flutter_output" mkdir flutter_output
if not exist "flutter_output\models" mkdir flutter_output\models
if not exist "flutter_output\services" mkdir flutter_output\services
if not exist "flutter_output\widgets" mkdir flutter_output\widgets
if not exist "flutter_output\screens" mkdir flutter_output\screens
echo ✅ Output directories created!

REM Final instructions
echo.
echo 🎉 Installation Complete!
echo ========================
echo.
echo Quick start:
echo 1. Create a model: php artisan make:model TestModel -m
echo 2. Generate Flutter code: php artisan flutter:generate-model TestModel
echo 3. Check output: dir flutter_output\models\
echo.
echo For more information, see README.md
echo.
echo Happy coding! 🚀
echo.
pause
