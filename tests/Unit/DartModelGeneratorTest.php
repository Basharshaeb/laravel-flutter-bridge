<?php

namespace LaravelFlutter\Generator\Tests\Unit;

use LaravelFlutter\Generator\Tests\TestCase;
use LaravelFlutter\Generator\Generators\DartModelGenerator;

class DartModelGeneratorTest extends TestCase
{
    protected DartModelGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->generator = new DartModelGenerator();
    }

    public function test_generate_creates_valid_dart_model(): void
    {
        $modelData = [
            'class_name' => 'User',
            'attributes' => [
                'id' => [
                    'name' => 'id',
                    'type' => 'int',
                    'nullable' => false,
                ],
                'name' => [
                    'name' => 'name',
                    'type' => 'String',
                    'nullable' => false,
                ],
                'email' => [
                    'name' => 'email',
                    'type' => 'String',
                    'nullable' => false,
                ],
                'age' => [
                    'name' => 'age',
                    'type' => 'int',
                    'nullable' => true,
                ],
            ],
            'relationships' => [],
        ];

        $result = $this->generator->generate($modelData);

        $this->assertIsString($result);
        $this->assertStringContainsString('class User {', $result);
        $this->assertStringContainsString('final int id;', $result);
        $this->assertStringContainsString('final String name;', $result);
        $this->assertStringContainsString('final String email;', $result);
        $this->assertStringContainsString('final int? age;', $result);
        $this->assertStringContainsString('factory User.fromJson', $result);
        $this->assertStringContainsString('Map<String, dynamic> toJson', $result);
        $this->assertStringContainsString('User copyWith', $result);
    }

    public function test_get_file_extension_returns_dart(): void
    {
        $this->assertEquals('.dart', $this->generator->getFileExtension());
    }

    public function test_get_output_path_returns_correct_path(): void
    {
        $path = $this->generator->getOutputPath('User');
        
        $this->assertStringContainsString('models', $path);
        $this->assertStringContainsString('user.dart', $path);
    }

    public function test_generate_handles_nullable_types(): void
    {
        $modelData = [
            'class_name' => 'Post',
            'attributes' => [
                'id' => [
                    'name' => 'id',
                    'type' => 'int',
                    'nullable' => false,
                ],
                'title' => [
                    'name' => 'title',
                    'type' => 'String',
                    'nullable' => true,
                ],
                'published_at' => [
                    'name' => 'published_at',
                    'type' => 'DateTime',
                    'nullable' => true,
                ],
            ],
            'relationships' => [],
        ];

        $result = $this->generator->generate($modelData);

        $this->assertStringContainsString('final int id;', $result);
        $this->assertStringContainsString('final String? title;', $result);
        $this->assertStringContainsString('final DateTime? publishedAt;', $result);
    }

    public function test_generate_includes_json_annotation_imports(): void
    {
        $modelData = [
            'class_name' => 'User',
            'attributes' => [
                'id' => [
                    'name' => 'id',
                    'type' => 'int',
                    'nullable' => false,
                ],
            ],
            'relationships' => [],
        ];

        $result = $this->generator->generate($modelData);

        $this->assertStringContainsString("import 'package:json_annotation/json_annotation.dart';", $result);
        $this->assertStringContainsString("part 'user.g.dart';", $result);
        $this->assertStringContainsString('@JsonSerializable()', $result);
    }
}
