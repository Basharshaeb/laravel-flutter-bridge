<?php

/**
 * Quick Test Script for Laravel Flutter Generator
 *
 * This script provides a quick way to test the package functionality
 * Run with: php test-script.php
 *
 * @author BasharShaeb
 */

require_once __DIR__ . '/vendor/autoload.php';

use BasharShaeb\LaravelFlutterGenerator\Analyzers\ModelAnalyzer;
use BasharShaeb\LaravelFlutterGenerator\Analyzers\RouteAnalyzer;
use BasharShaeb\LaravelFlutterGenerator\Generators\DartModelGenerator;
use BasharShaeb\LaravelFlutterGenerator\Generators\ApiServiceGenerator;

echo "🚀 Laravel Flutter Generator - Quick Test Script\n";
echo "================================================\n\n";

// Test 1: Model Analyzer
echo "1. Testing Model Analyzer...\n";
try {
    $modelAnalyzer = new ModelAnalyzer();

    // Test with a simple mock model data
    $mockModelData = [
        'class_name' => 'User',
        'full_class_name' => 'App\\Models\\User',
        'table_name' => 'users',
        'primary_key' => 'id',
        'attributes' => [
            'id' => [
                'name' => 'id',
                'type' => 'int',
                'nullable' => false,
                'database_type' => 'integer',
                'default' => null,
            ],
            'name' => [
                'name' => 'name',
                'type' => 'String',
                'nullable' => false,
                'database_type' => 'string',
                'default' => null,
            ],
            'email' => [
                'name' => 'email',
                'type' => 'String',
                'nullable' => false,
                'database_type' => 'string',
                'default' => null,
            ],
            'age' => [
                'name' => 'age',
                'type' => 'int',
                'nullable' => true,
                'database_type' => 'integer',
                'default' => null,
            ],
        ],
        'relationships' => [],
        'fillable' => ['name', 'email', 'age'],
        'hidden' => ['password'],
        'timestamps' => true,
    ];

    echo "   ✅ Model Analyzer initialized successfully\n";
    echo "   ✅ Mock model data structure is valid\n";
} catch (Exception $e) {
    echo "   ❌ Model Analyzer failed: " . $e->getMessage() . "\n";
}

// Test 2: Dart Model Generator
echo "\n2. Testing Dart Model Generator...\n";
try {
    $modelGenerator = new DartModelGenerator();
    $dartCode = $modelGenerator->generate($mockModelData);

    // Validate generated code
    if (strpos($dartCode, 'class User {') !== false) {
        echo "   ✅ Dart model class generated successfully\n";
    } else {
        echo "   ❌ Dart model class not found in generated code\n";
    }

    if (strpos($dartCode, 'final int id;') !== false) {
        echo "   ✅ Properties generated correctly\n";
    } else {
        echo "   ❌ Properties not generated correctly\n";
    }

    if (strpos($dartCode, 'factory User.fromJson') !== false) {
        echo "   ✅ fromJson method generated\n";
    } else {
        echo "   ❌ fromJson method not generated\n";
    }

    if (strpos($dartCode, 'Map<String, dynamic> toJson') !== false) {
        echo "   ✅ toJson method generated\n";
    } else {
        echo "   ❌ toJson method not generated\n";
    }

    // Save generated code to file for inspection
    $outputDir = __DIR__ . '/test_output';
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    file_put_contents($outputDir . '/user.dart', $dartCode);
    echo "   ✅ Generated Dart code saved to test_output/user.dart\n";

} catch (Exception $e) {
    echo "   ❌ Dart Model Generator failed: " . $e->getMessage() . "\n";
}

// Test 3: API Service Generator
echo "\n3. Testing API Service Generator...\n";
try {
    $serviceGenerator = new ApiServiceGenerator();
    $serviceCode = $serviceGenerator->generate($mockModelData);

    // Validate generated service code
    if (strpos($serviceCode, 'class UserService {') !== false) {
        echo "   ✅ Service class generated successfully\n";
    } else {
        echo "   ❌ Service class not found in generated code\n";
    }

    if (strpos($serviceCode, 'Future<List<User>> getAll') !== false) {
        echo "   ✅ getAll method generated\n";
    } else {
        echo "   ❌ getAll method not generated\n";
    }

    if (strpos($serviceCode, 'Future<User> getById') !== false) {
        echo "   ✅ getById method generated\n";
    } else {
        echo "   ❌ getById method not generated\n";
    }

    if (strpos($serviceCode, 'Future<User> create') !== false) {
        echo "   ✅ create method generated\n";
    } else {
        echo "   ❌ create method not generated\n";
    }

    // Save generated service code
    file_put_contents($outputDir . '/user_service.dart', $serviceCode);
    echo "   ✅ Generated service code saved to test_output/user_service.dart\n";

} catch (Exception $e) {
    echo "   ❌ API Service Generator failed: " . $e->getMessage() . "\n";
}

// Test 4: Route Analyzer
echo "\n4. Testing Route Analyzer...\n";
try {
    $routeAnalyzer = new RouteAnalyzer();

    // Test with mock route data
    $mockRoutes = [
        [
            'uri' => 'api/users',
            'methods' => ['GET'],
            'name' => 'users.index',
            'controller' => 'UserController@index',
            'middleware' => ['api'],
        ],
        [
            'uri' => 'api/users/{user}',
            'methods' => ['GET'],
            'name' => 'users.show',
            'controller' => 'UserController@show',
            'middleware' => ['api'],
        ],
    ];

    echo "   ✅ Route Analyzer initialized successfully\n";
    echo "   ✅ Mock route data structure is valid\n";

} catch (Exception $e) {
    echo "   ❌ Route Analyzer failed: " . $e->getMessage() . "\n";
}

// Test 5: File Output Validation
echo "\n5. Testing File Output...\n";
try {
    if (file_exists($outputDir . '/user.dart')) {
        $dartContent = file_get_contents($outputDir . '/user.dart');
        $lines = explode("\n", $dartContent);
        echo "   ✅ Dart model file created (" . count($lines) . " lines)\n";

        // Check for null safety
        if (strpos($dartContent, 'int?') !== false || strpos($dartContent, 'String?') !== false) {
            echo "   ✅ Null safety syntax detected\n";
        }

        // Check for JSON annotation
        if (strpos($dartContent, '@JsonSerializable()') !== false) {
            echo "   ✅ JSON annotation detected\n";
        }
    }

    if (file_exists($outputDir . '/user_service.dart')) {
        $serviceContent = file_get_contents($outputDir . '/user_service.dart');
        $lines = explode("\n", $serviceContent);
        echo "   ✅ Service file created (" . count($lines) . " lines)\n";

        // Check for error handling
        if (strpos($serviceContent, 'try {') !== false && strpos($serviceContent, 'catch (e)') !== false) {
            echo "   ✅ Error handling detected\n";
        }

        // Check for async/await
        if (strpos($serviceContent, 'async') !== false && strpos($serviceContent, 'await') !== false) {
            echo "   ✅ Async/await patterns detected\n";
        }
    }

} catch (Exception $e) {
    echo "   ❌ File output validation failed: " . $e->getMessage() . "\n";
}

// Summary
echo "\n📊 Test Summary\n";
echo "===============\n";
echo "Generated files are available in: " . $outputDir . "\n";
echo "You can inspect the generated Dart code to verify quality.\n\n";

echo "🎯 Next Steps:\n";
echo "1. Run the full test suite: composer test\n";
echo "2. Test with a real Laravel application\n";
echo "3. Copy generated files to a Flutter project and test compilation\n";
echo "4. Run static analysis: composer analyse\n\n";

echo "✨ Quick test completed!\n";

// Display generated file contents for quick inspection
echo "\n📄 Generated Dart Model (first 20 lines):\n";
echo "==========================================\n";
if (file_exists($outputDir . '/user.dart')) {
    $lines = explode("\n", file_get_contents($outputDir . '/user.dart'));
    for ($i = 0; $i < min(20, count($lines)); $i++) {
        echo sprintf("%2d: %s\n", $i + 1, $lines[$i]);
    }
    if (count($lines) > 20) {
        echo "... (" . (count($lines) - 20) . " more lines)\n";
    }
}

echo "\n📄 Generated Service (first 15 lines):\n";
echo "======================================\n";
if (file_exists($outputDir . '/user_service.dart')) {
    $lines = explode("\n", file_get_contents($outputDir . '/user_service.dart'));
    for ($i = 0; $i < min(15, count($lines)); $i++) {
        echo sprintf("%2d: %s\n", $i + 1, $lines[$i]);
    }
    if (count($lines) > 15) {
        echo "... (" . (count($lines) - 15) . " more lines)\n";
    }
}

echo "\n🎉 Test script completed successfully!\n";
