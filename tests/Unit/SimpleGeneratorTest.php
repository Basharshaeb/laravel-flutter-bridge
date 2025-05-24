<?php

namespace BasharShaeb\LaravelFlutterGenerator\Tests\Unit;

use BasharShaeb\LaravelFlutterGenerator\Tests\TestCase;
use BasharShaeb\LaravelFlutterGenerator\Tests\TestHelper;
use BasharShaeb\LaravelFlutterGenerator\Generators\DartModelGenerator;
use BasharShaeb\LaravelFlutterGenerator\Generators\ApiServiceGenerator;

class SimpleGeneratorTest extends TestCase
{
    public function test_dart_model_generator_can_be_instantiated(): void
    {
        $config = TestHelper::getTestConfig();
        $generator = new DartModelGenerator($config);
        
        $this->assertInstanceOf(DartModelGenerator::class, $generator);
    }

    public function test_api_service_generator_can_be_instantiated(): void
    {
        $config = TestHelper::getTestConfig();
        $generator = new ApiServiceGenerator($config);
        
        $this->assertInstanceOf(ApiServiceGenerator::class, $generator);
    }

    public function test_dart_model_generator_produces_output(): void
    {
        $config = TestHelper::getTestConfig();
        $generator = new DartModelGenerator($config);
        
        $testData = TestHelper::createMockModelData();
        $result = $generator->generate($testData);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('class User', $result);
    }

    public function test_api_service_generator_produces_output(): void
    {
        $config = TestHelper::getTestConfig();
        $generator = new ApiServiceGenerator($config);
        
        $testData = TestHelper::createMockModelData();
        $result = $generator->generate($testData);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('class UserService', $result);
    }

    public function test_generators_handle_empty_data(): void
    {
        $config = TestHelper::getTestConfig();
        $generator = new DartModelGenerator($config);
        
        $result = $generator->generate([]);
        
        // Should not crash, might return empty string or default template
        $this->assertIsString($result);
    }

    public function test_generator_file_extension(): void
    {
        $config = TestHelper::getTestConfig();
        $generator = new DartModelGenerator($config);
        
        $this->assertEquals('.dart', $generator->getFileExtension());
    }

    public function test_generator_output_path(): void
    {
        $config = TestHelper::getTestConfig();
        $generator = new DartModelGenerator($config);
        
        $path = $generator->getOutputPath('User');
        
        $this->assertIsString($path);
        $this->assertStringContainsString('models', $path);
        $this->assertStringContainsString('user.dart', $path);
    }
}
