<?php

namespace BasharShaeb\LaravelFlutterGenerator\Tests\Feature;

use BasharShaeb\LaravelFlutterGenerator\Tests\TestCase;
use BasharShaeb\LaravelFlutterGenerator\Tests\Fixtures\TestUser;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CommandsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestTable();
    }

    protected function createTestTable(): void
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->integer('age')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->text('bio')->nullable();
            $table->json('preferences')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function test_flutter_generate_model_command_works(): void
    {
        $this->artisan('flutter:generate-model', ['model' => 'TestUser', '--force' => true])
             ->expectsOutput('🚀 Flutter Model Generator')
             ->assertExitCode(0);

        $outputPath = config('flutter-generator.output.base_path') . '/models/test_user.dart';
        $this->assertTrue(File::exists($outputPath));

        $content = File::get($outputPath);
        $this->assertStringContainsString('class TestUser {', $content);
        $this->assertStringContainsString('final int id;', $content);
        $this->assertStringContainsString('final String name;', $content);
    }

    public function test_flutter_generate_service_command_works(): void
    {
        $this->artisan('flutter:generate-service', ['model' => 'TestUser', '--force' => true])
             ->expectsOutput('🚀 Flutter Service Generator')
             ->assertExitCode(0);

        $outputPath = config('flutter-generator.output.base_path') . '/services/test_user_service.dart';
        $this->assertTrue(File::exists($outputPath));

        $content = File::get($outputPath);
        $this->assertStringContainsString('class TestUserService {', $content);
        $this->assertStringContainsString('Future<List<TestUser>> getAll', $content);
        $this->assertStringContainsString('Future<TestUser> getById', $content);
    }

    public function test_flutter_generate_feature_command_works(): void
    {
        $this->artisan('flutter:generate-feature', ['model' => 'TestUser', '--force' => true])
             ->expectsOutput('🚀 Flutter Feature Generator')
             ->assertExitCode(0);

        $basePath = config('flutter-generator.output.base_path');

        // Check model file
        $this->assertTrue(File::exists($basePath . '/models/test_user.dart'));

        // Check service file
        $this->assertTrue(File::exists($basePath . '/services/test_user_service.dart'));

        // Check widget files
        $this->assertTrue(File::exists($basePath . '/widgets/test_user_form.dart'));
        $this->assertTrue(File::exists($basePath . '/widgets/test_user_list.dart'));
        $this->assertTrue(File::exists($basePath . '/widgets/test_user_card.dart'));

        // Check screen files
        $this->assertTrue(File::exists($basePath . '/screens/test_user_list_screen.dart'));
        $this->assertTrue(File::exists($basePath . '/screens/test_user_detail_screen.dart'));
        $this->assertTrue(File::exists($basePath . '/screens/test_user_create_screen.dart'));
        $this->assertTrue(File::exists($basePath . '/screens/test_user_edit_screen.dart'));
    }

    public function test_flutter_generate_all_command_works(): void
    {
        $this->artisan('flutter:generate-all', ['--force' => true, '--models' => ['TestUser']])
             ->expectsOutput('🚀 Flutter Complete Application Generator')
             ->assertExitCode(0);

        $basePath = config('flutter-generator.output.base_path');

        // Check base API service
        $this->assertTrue(File::exists($basePath . '/services/api_service.dart'));

        // Check all TestUser files are generated
        $this->assertTrue(File::exists($basePath . '/models/test_user.dart'));
        $this->assertTrue(File::exists($basePath . '/services/test_user_service.dart'));
    }

    public function test_commands_handle_invalid_model_gracefully(): void
    {
        $this->artisan('flutter:generate-model', ['model' => 'NonExistentModel'])
             ->expectsOutput('Error: Model \'NonExistentModel\' not found or is not an Eloquent model.')
             ->assertExitCode(1);
    }

    public function test_commands_respect_force_flag(): void
    {
        // Generate file first time
        $this->artisan('flutter:generate-model', ['model' => 'TestUser', '--force' => true])
             ->assertExitCode(0);

        $outputPath = config('flutter-generator.output.base_path') . '/models/test_user.dart';
        $this->assertTrue(File::exists($outputPath));

        // Modify the file
        File::put($outputPath, '// Modified content');
        $this->assertEquals('// Modified content', File::get($outputPath));

        // Generate again with force flag
        $this->artisan('flutter:generate-model', ['model' => 'TestUser', '--force' => true])
             ->assertExitCode(0);

        // File should be overwritten
        $content = File::get($outputPath);
        $this->assertStringContainsString('class TestUser {', $content);
        $this->assertStringNotContainsString('// Modified content', $content);
    }

    public function test_skip_options_work_in_feature_command(): void
    {
        $this->artisan('flutter:generate-feature', [
            'model' => 'TestUser',
            '--force' => true,
            '--skip-widgets' => true,
            '--skip-screens' => true
        ])->assertExitCode(0);

        $basePath = config('flutter-generator.output.base_path');

        // Model and service should exist
        $this->assertTrue(File::exists($basePath . '/models/test_user.dart'));
        $this->assertTrue(File::exists($basePath . '/services/test_user_service.dart'));

        // Widgets and screens should not exist
        $this->assertFalse(File::exists($basePath . '/widgets/test_user_form.dart'));
        $this->assertFalse(File::exists($basePath . '/screens/test_user_list_screen.dart'));
    }

    public function test_configuration_affects_output_paths(): void
    {
        // Change configuration
        config(['flutter-generator.output.models_path' => 'custom_models']);

        $this->artisan('flutter:generate-model', ['model' => 'TestUser', '--force' => true])
             ->assertExitCode(0);

        $customPath = config('flutter-generator.output.base_path') . '/custom_models/test_user.dart';
        $this->assertTrue(File::exists($customPath));
    }
}
