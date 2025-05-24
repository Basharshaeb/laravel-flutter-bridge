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

        // Ensure config service is properly bound
        if (!$app->bound('config')) {
            $app->singleton('config', function ($app) {
                return new \Illuminate\Config\Repository();
            });
        }

        // Load test configuration
        $testConfig = require __DIR__ . '/config/flutter-generator.php';
        $app['config']->set('flutter-generator', $testConfig);
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
