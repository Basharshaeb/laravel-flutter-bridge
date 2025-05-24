<?php

namespace BasharShaeb\LaravelFlutterGenerator\Tests\Feature;

use BasharShaeb\LaravelFlutterGenerator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class SimpleCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure output directory exists
        $outputPath = sys_get_temp_dir() . '/flutter_test_output';
        if (!File::isDirectory($outputPath)) {
            File::makeDirectory($outputPath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test output
        $outputPath = sys_get_temp_dir() . '/flutter_test_output';
        if (File::isDirectory($outputPath)) {
            File::deleteDirectory($outputPath);
        }
        
        parent::tearDown();
    }

    public function test_flutter_commands_are_registered(): void
    {
        $commands = $this->app['artisan']->all();
        
        $flutterCommands = array_filter(array_keys($commands), function ($command) {
            return str_starts_with($command, 'flutter:');
        });
        
        $this->assertNotEmpty($flutterCommands);
        $this->assertContains('flutter:generate-model', array_keys($commands));
        $this->assertContains('flutter:generate-service', array_keys($commands));
        $this->assertContains('flutter:generate-feature', array_keys($commands));
        $this->assertContains('flutter:generate-all', array_keys($commands));
    }

    public function test_flutter_generate_model_command_exists(): void
    {
        $exitCode = $this->artisan('flutter:generate-model', ['--help' => true])
                         ->run();
        
        // Command should exist (exit code 0 for help)
        $this->assertEquals(0, $exitCode);
    }

    public function test_flutter_generate_service_command_exists(): void
    {
        $exitCode = $this->artisan('flutter:generate-service', ['--help' => true])
                         ->run();
        
        // Command should exist (exit code 0 for help)
        $this->assertEquals(0, $exitCode);
    }

    public function test_flutter_generate_feature_command_exists(): void
    {
        $exitCode = $this->artisan('flutter:generate-feature', ['--help' => true])
                         ->run();
        
        // Command should exist (exit code 0 for help)
        $this->assertEquals(0, $exitCode);
    }

    public function test_flutter_generate_all_command_exists(): void
    {
        $exitCode = $this->artisan('flutter:generate-all', ['--help' => true])
                         ->run();
        
        // Command should exist (exit code 0 for help)
        $this->assertEquals(0, $exitCode);
    }

    public function test_commands_handle_missing_arguments_gracefully(): void
    {
        // Test that commands don't crash when required arguments are missing
        $this->artisan('flutter:generate-model')
             ->assertExitCode(1); // Should exit with error code but not crash
    }

    public function test_service_provider_is_loaded(): void
    {
        $providers = $this->app->getLoadedProviders();
        
        $this->assertArrayHasKey(
            'BasharShaeb\LaravelFlutterGenerator\FlutterGeneratorServiceProvider',
            $providers
        );
    }

    public function test_configuration_is_available(): void
    {
        $config = config('flutter-generator');
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('output', $config);
        $this->assertArrayHasKey('api', $config);
        $this->assertArrayHasKey('generation', $config);
    }
}
