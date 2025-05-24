<?php

namespace BasharShaeb\LaravelFlutterGenerator\Tests;

use Illuminate\Support\Facades\File;

class TestHelper
{
    /**
     * Create a test output directory.
     */
    public static function createTestOutputDirectory(): string
    {
        $path = sys_get_temp_dir() . '/flutter_test_output_' . uniqid();
        
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
        
        return $path;
    }

    /**
     * Clean up test output directory.
     */
    public static function cleanupTestOutputDirectory(string $path): void
    {
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }

    /**
     * Get test configuration.
     */
    public static function getTestConfig(array $overrides = []): array
    {
        $defaultConfig = [
            'output' => [
                'base_path' => self::createTestOutputDirectory(),
                'models_path' => 'models',
                'services_path' => 'services',
                'widgets_path' => 'widgets',
                'screens_path' => 'screens',
            ],
            'api' => [
                'base_url' => 'http://localhost:8000/api',
                'timeout' => 30,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ],
            'generation' => [
                'architecture' => 'provider',
                'null_safety' => true,
                'use_json_annotation' => true,
                'use_equatable' => false,
                'use_freezed' => false,
            ],
            'naming' => [
                'use_snake_case' => true,
                'file_suffix' => '',
                'class_prefix' => '',
                'class_suffix' => '',
            ],
            'model_analysis' => [
                'include_relationships' => true,
                'include_accessors' => true,
                'include_mutators' => false,
                'include_scopes' => false,
                'excluded_attributes' => [
                    'password',
                    'remember_token',
                    'email_verified_at',
                ],
            ],
            'ui' => [
                'theme' => 'material',
                'responsive' => true,
                'generate_forms' => true,
                'generate_lists' => true,
                'generate_cards' => true,
            ],
            'templates' => [
                'path' => __DIR__ . '/../src/Templates',
                'extension' => '.dart.stub',
                'custom_templates' => [],
            ],
            'excluded_models' => [
                'Illuminate\Foundation\Auth\User',
                'Illuminate\Notifications\DatabaseNotification',
            ],
        ];

        return array_merge_recursive($defaultConfig, $overrides);
    }

    /**
     * Create a mock model data array.
     */
    public static function createMockModelData(array $overrides = []): array
    {
        $defaultData = [
            'class_name' => 'User',
            'table_name' => 'users',
            'full_class_name' => 'App\\Models\\User',
            'attributes' => [
                'id' => [
                    'type' => 'int',
                    'nullable' => false,
                    'default' => null,
                ],
                'name' => [
                    'type' => 'string',
                    'nullable' => false,
                    'default' => null,
                ],
                'email' => [
                    'type' => 'string',
                    'nullable' => false,
                    'default' => null,
                ],
                'created_at' => [
                    'type' => 'datetime',
                    'nullable' => true,
                    'default' => null,
                ],
                'updated_at' => [
                    'type' => 'datetime',
                    'nullable' => true,
                    'default' => null,
                ],
            ],
            'relationships' => [],
            'fillable' => ['name', 'email'],
            'hidden' => ['password', 'remember_token'],
            'casts' => [],
        ];

        return array_merge_recursive($defaultData, $overrides);
    }

    /**
     * Assert that a file contains specific content.
     */
    public static function assertFileContains(string $filePath, string $content): bool
    {
        if (!File::exists($filePath)) {
            return false;
        }

        $fileContent = File::get($filePath);
        return str_contains($fileContent, $content);
    }

    /**
     * Assert that a file is valid Dart code (basic check).
     */
    public static function assertValidDartFile(string $filePath): bool
    {
        if (!File::exists($filePath)) {
            return false;
        }

        $content = File::get($filePath);
        
        // Basic Dart syntax checks
        $hasClassDeclaration = preg_match('/class\s+\w+/', $content);
        $hasProperBraces = substr_count($content, '{') === substr_count($content, '}');
        $hasProperSemicolons = !preg_match('/\w+\s*$/', trim($content)); // Should end with } or ;
        
        return $hasClassDeclaration && $hasProperBraces;
    }

    /**
     * Get a temporary file path.
     */
    public static function getTempFilePath(string $filename): string
    {
        return sys_get_temp_dir() . '/' . $filename;
    }

    /**
     * Create a test database table.
     */
    public static function createTestTable(string $tableName, array $columns): void
    {
        \Illuminate\Support\Facades\Schema::create($tableName, function ($table) use ($columns) {
            $table->id();
            
            foreach ($columns as $column => $type) {
                switch ($type) {
                    case 'string':
                        $table->string($column);
                        break;
                    case 'text':
                        $table->text($column);
                        break;
                    case 'integer':
                        $table->integer($column);
                        break;
                    case 'boolean':
                        $table->boolean($column);
                        break;
                    case 'datetime':
                        $table->dateTime($column);
                        break;
                    default:
                        $table->string($column);
                }
            }
            
            $table->timestamps();
        });
    }

    /**
     * Drop a test database table.
     */
    public static function dropTestTable(string $tableName): void
    {
        \Illuminate\Support\Facades\Schema::dropIfExists($tableName);
    }
}
