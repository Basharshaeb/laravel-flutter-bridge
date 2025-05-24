<?php

namespace BasharShaeb\LaravelFlutterGenerator\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use BasharShaeb\LaravelFlutterGenerator\FlutterGeneratorServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Additional setup if needed
    }

    protected function getPackageProviders($app): array
    {
        return [
            FlutterGeneratorServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup the application environment for testing
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up flutter-generator config for testing
        $app['config']->set('flutter-generator', [
            'output' => [
                'base_path' => sys_get_temp_dir() . '/flutter_test_output',
                'models_path' => 'models',
                'services_path' => 'services',
                'widgets_path' => 'widgets',
                'screens_path' => 'screens',
            ],
            'api' => [
                'base_url' => 'http://localhost:8000/api',
                'timeout' => 30,
            ],
            'generation' => [
                'architecture' => 'provider',
                'null_safety' => true,
                'use_json_annotation' => true,
            ],
            'naming' => [
                'use_snake_case' => true,
            ],
            'model_analysis' => [
                'include_relationships' => true,
                'excluded_attributes' => ['password', 'remember_token'],
            ],
            'excluded_models' => [],
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up test output directory
        $outputPath = config('flutter-generator.output.base_path');
        if (is_dir($outputPath)) {
            $this->deleteDirectory($outputPath);
        }

        parent::tearDown();
    }

    /**
     * Recursively delete a directory.
     *
     * @param string $dir
     * @return void
     */
    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
