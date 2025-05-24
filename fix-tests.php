<?php

/**
 * Test Fix Script for Laravel Flutter Generator
 * 
 * This script fixes common test issues and validates the package
 * Run with: php fix-tests.php
 * 
 * @author BasharShaeb
 */

echo "🔧 Laravel Flutter Generator - Test Fix Script\n";
echo "==============================================\n\n";

// Check if we're in the package directory
if (!file_exists('composer.json')) {
    echo "❌ Error: This doesn't appear to be the package directory.\n";
    echo "   Please run this script from the package root.\n";
    exit(1);
}

echo "✅ Package directory detected\n";

// Test 1: Check Composer Dependencies
echo "\n1. Checking Composer Dependencies...\n";
try {
    $composerJson = json_decode(file_get_contents('composer.json'), true);
    
    if (!$composerJson) {
        throw new Exception("Invalid composer.json");
    }
    
    echo "✅ composer.json is valid\n";
    
    // Check for required dependencies
    $requiredDeps = [
        'php',
        'laravel/framework',
        'illuminate/support',
        'illuminate/console',
        'illuminate/database',
        'doctrine/dbal'
    ];
    
    foreach ($requiredDeps as $dep) {
        if (isset($composerJson['require'][$dep])) {
            echo "✅ {$dep}: {$composerJson['require'][$dep]}\n";
        } else {
            echo "❌ Missing dependency: {$dep}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Composer check failed: " . $e->getMessage() . "\n";
}

// Test 2: Check Autoloading
echo "\n2. Checking Autoloading...\n";
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    echo "✅ Autoloader loaded\n";
    
    // Check if main classes can be loaded
    $classes = [
        'BasharShaeb\\LaravelFlutterGenerator\\FlutterGeneratorServiceProvider',
        'BasharShaeb\\LaravelFlutterGenerator\\Analyzers\\ModelAnalyzer',
        'BasharShaeb\\LaravelFlutterGenerator\\Generators\\DartModelGenerator',
    ];
    
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "✅ Class exists: {$class}\n";
        } else {
            echo "❌ Class not found: {$class}\n";
        }
    }
} else {
    echo "❌ Autoloader not found. Run: composer install\n";
}

// Test 3: Check Configuration
echo "\n3. Checking Configuration...\n";
if (file_exists('config/flutter-generator.php')) {
    $config = require 'config/flutter-generator.php';
    if (is_array($config)) {
        echo "✅ Configuration file is valid\n";
        
        $requiredKeys = ['output', 'api', 'generation', 'naming', 'model_analysis'];
        foreach ($requiredKeys as $key) {
            if (isset($config[$key])) {
                echo "✅ Config section exists: {$key}\n";
            } else {
                echo "❌ Missing config section: {$key}\n";
            }
        }
    } else {
        echo "❌ Configuration file is invalid\n";
    }
} else {
    echo "❌ Configuration file not found\n";
}

// Test 4: Check Templates
echo "\n4. Checking Templates...\n";
$templateDir = 'src/Templates';
if (is_dir($templateDir)) {
    echo "✅ Template directory exists\n";
    
    $templates = glob($templateDir . '/*.stub');
    if (count($templates) > 0) {
        echo "✅ Found " . count($templates) . " template(s)\n";
        foreach ($templates as $template) {
            echo "  - " . basename($template) . "\n";
        }
    } else {
        echo "❌ No templates found\n";
    }
} else {
    echo "❌ Template directory not found\n";
}

// Test 5: Check Test Configuration
echo "\n5. Checking Test Configuration...\n";
if (file_exists('tests/config/flutter-generator.php')) {
    $testConfig = require 'tests/config/flutter-generator.php';
    if (is_array($testConfig)) {
        echo "✅ Test configuration is valid\n";
    } else {
        echo "❌ Test configuration is invalid\n";
    }
} else {
    echo "❌ Test configuration not found\n";
}

// Test 6: Run Basic Tests
echo "\n6. Running Basic Tests...\n";
if (file_exists('vendor/bin/phpunit')) {
    echo "Running PHPUnit tests...\n";
    $output = [];
    $returnCode = 0;
    exec('vendor/bin/phpunit --testdox 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✅ All tests passed!\n";
    } else {
        echo "❌ Some tests failed:\n";
        foreach ($output as $line) {
            echo "  {$line}\n";
        }
    }
} else {
    echo "❌ PHPUnit not found. Run: composer install\n";
}

// Test 7: Check Output Directory
echo "\n7. Checking Output Directory...\n";
$outputDir = 'flutter_output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "✅ Created output directory\n";
} else {
    echo "✅ Output directory exists\n";
}

// Create subdirectories
$subdirs = ['models', 'services', 'widgets', 'screens'];
foreach ($subdirs as $subdir) {
    $path = $outputDir . '/' . $subdir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "✅ Created {$subdir} directory\n";
    } else {
        echo "✅ {$subdir} directory exists\n";
    }
}

// Test 8: Quick Generator Test
echo "\n8. Quick Generator Test...\n";
try {
    if (class_exists('BasharShaeb\\LaravelFlutterGenerator\\Generators\\DartModelGenerator')) {
        $config = [
            'output' => ['base_path' => sys_get_temp_dir() . '/test_output'],
            'generation' => ['null_safety' => true],
            'templates' => ['path' => 'src/Templates', 'extension' => '.dart.stub']
        ];
        
        $generator = new BasharShaeb\LaravelFlutterGenerator\Generators\DartModelGenerator($config);
        echo "✅ Generator instantiated successfully\n";
        
        // Test basic generation
        $testData = [
            'class_name' => 'TestModel',
            'table_name' => 'test_models',
            'attributes' => [
                'id' => ['type' => 'int', 'nullable' => false],
                'name' => ['type' => 'string', 'nullable' => false],
            ]
        ];
        
        $result = $generator->generate($testData);
        if (!empty($result)) {
            echo "✅ Generator produces output\n";
        } else {
            echo "❌ Generator produces no output\n";
        }
        
    } else {
        echo "❌ Generator class not found\n";
    }
} catch (Exception $e) {
    echo "❌ Generator test failed: " . $e->getMessage() . "\n";
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 Test Fix Summary\n";
echo str_repeat("=", 50) . "\n";

echo "\nIf you see any ❌ errors above, here's how to fix them:\n\n";

echo "1. Missing dependencies:\n";
echo "   composer install\n\n";

echo "2. Class not found errors:\n";
echo "   composer dump-autoload\n\n";

echo "3. Test failures:\n";
echo "   Check the specific error messages above\n";
echo "   Ensure database is configured for testing\n\n";

echo "4. Configuration issues:\n";
echo "   Ensure config/flutter-generator.php exists\n";
echo "   Check tests/config/flutter-generator.php\n\n";

echo "5. Template issues:\n";
echo "   Ensure src/Templates/*.stub files exist\n\n";

echo "🚀 After fixing issues, run:\n";
echo "   composer test\n";
echo "   php artisan list flutter\n\n";

echo "Happy coding! 🎉\n";
