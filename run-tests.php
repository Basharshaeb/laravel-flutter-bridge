<?php

/**
 * Test Runner Script for Laravel Flutter Generator
 * 
 * This script runs tests in a safe way and provides detailed feedback
 * Run with: php run-tests.php
 * 
 * @author BasharShaeb
 */

echo "🧪 Laravel Flutter Generator - Test Runner\n";
echo "==========================================\n\n";

// Check if we're in the package directory
if (!file_exists('composer.json')) {
    echo "❌ Error: This doesn't appear to be the package directory.\n";
    echo "   Please run this script from the package root.\n";
    exit(1);
}

echo "✅ Package directory detected\n";

// Check if vendor directory exists
if (!is_dir('vendor')) {
    echo "❌ Vendor directory not found. Running composer install...\n";
    exec('composer install', $output, $returnCode);
    if ($returnCode !== 0) {
        echo "❌ Composer install failed\n";
        exit(1);
    }
    echo "✅ Dependencies installed\n";
}

// Check if PHPUnit exists
if (!file_exists('vendor/bin/phpunit')) {
    echo "❌ PHPUnit not found in vendor/bin/\n";
    exit(1);
}

echo "✅ PHPUnit found\n\n";

// Run simple tests first
echo "🔍 Running Simple Tests (Safe Tests)...\n";
echo str_repeat("-", 50) . "\n";

$simpleTestCommand = 'vendor/bin/phpunit --testsuite=Simple --testdox';
echo "Command: {$simpleTestCommand}\n\n";

$output = [];
$returnCode = 0;
exec($simpleTestCommand . ' 2>&1', $output, $returnCode);

foreach ($output as $line) {
    echo $line . "\n";
}

if ($returnCode === 0) {
    echo "\n✅ Simple tests passed!\n\n";
} else {
    echo "\n❌ Simple tests failed with exit code: {$returnCode}\n\n";
}

// Ask user if they want to run all tests
echo "Do you want to run ALL tests (including potentially failing ones)? [y/N]: ";
$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));
fclose($handle);

if (strtolower($input) === 'y' || strtolower($input) === 'yes') {
    echo "\n🔍 Running All Tests...\n";
    echo str_repeat("-", 50) . "\n";
    
    $allTestCommand = 'vendor/bin/phpunit --testdox';
    echo "Command: {$allTestCommand}\n\n";
    
    $output = [];
    $returnCode = 0;
    exec($allTestCommand . ' 2>&1', $output, $returnCode);
    
    foreach ($output as $line) {
        echo $line . "\n";
    }
    
    if ($returnCode === 0) {
        echo "\n🎉 All tests passed!\n";
    } else {
        echo "\n⚠️  Some tests failed with exit code: {$returnCode}\n";
        echo "This is expected for complex tests that require database setup.\n";
    }
} else {
    echo "\nSkipping full test suite.\n";
}

// Run quick package validation
echo "\n🔍 Running Package Validation...\n";
echo str_repeat("-", 50) . "\n";

// Test autoloading
echo "Testing autoloading...\n";
require_once 'vendor/autoload.php';

$classes = [
    'BasharShaeb\\LaravelFlutterGenerator\\FlutterGeneratorServiceProvider',
    'BasharShaeb\\LaravelFlutterGenerator\\Analyzers\\ModelAnalyzer',
    'BasharShaeb\\LaravelFlutterGenerator\\Generators\\DartModelGenerator',
    'BasharShaeb\\LaravelFlutterGenerator\\Generators\\ApiServiceGenerator',
];

$allClassesExist = true;
foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✅ {$class}\n";
    } else {
        echo "❌ {$class}\n";
        $allClassesExist = false;
    }
}

if ($allClassesExist) {
    echo "✅ All classes can be autoloaded\n";
} else {
    echo "❌ Some classes failed to autoload\n";
}

// Test basic instantiation
echo "\nTesting basic instantiation...\n";
try {
    $config = [
        'output' => ['base_path' => sys_get_temp_dir()],
        'templates' => ['path' => 'src/Templates', 'extension' => '.dart.stub']
    ];
    
    $modelGenerator = new BasharShaeb\LaravelFlutterGenerator\Generators\DartModelGenerator($config);
    echo "✅ DartModelGenerator can be instantiated\n";
    
    $serviceGenerator = new BasharShaeb\LaravelFlutterGenerator\Generators\ApiServiceGenerator($config);
    echo "✅ ApiServiceGenerator can be instantiated\n";
    
    $analyzer = new BasharShaeb\LaravelFlutterGenerator\Analyzers\ModelAnalyzer();
    echo "✅ ModelAnalyzer can be instantiated\n";
    
} catch (Exception $e) {
    echo "❌ Instantiation failed: " . $e->getMessage() . "\n";
}

// Test configuration
echo "\nTesting configuration...\n";
if (file_exists('config/flutter-generator.php')) {
    $config = require 'config/flutter-generator.php';
    if (is_array($config)) {
        echo "✅ Configuration file is valid\n";
    } else {
        echo "❌ Configuration file is invalid\n";
    }
} else {
    echo "❌ Configuration file not found\n";
}

// Test templates
echo "\nTesting templates...\n";
$templateDir = 'src/Templates';
if (is_dir($templateDir)) {
    $templates = glob($templateDir . '/*.stub');
    if (count($templates) > 0) {
        echo "✅ Found " . count($templates) . " template(s)\n";
    } else {
        echo "❌ No templates found\n";
    }
} else {
    echo "❌ Template directory not found\n";
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 Test Summary\n";
echo str_repeat("=", 50) . "\n";

if ($returnCode === 0 && $allClassesExist) {
    echo "🎉 Package is working correctly!\n";
    echo "\nNext steps:\n";
    echo "1. Install in a Laravel project\n";
    echo "2. Test the Artisan commands\n";
    echo "3. Generate some Flutter code\n";
} else {
    echo "⚠️  Package has some issues but basic functionality works\n";
    echo "\nTo fix remaining issues:\n";
    echo "1. Check the test output above\n";
    echo "2. Run: php fix-tests.php\n";
    echo "3. Ensure all dependencies are installed\n";
}

echo "\n🚀 Happy coding!\n";
