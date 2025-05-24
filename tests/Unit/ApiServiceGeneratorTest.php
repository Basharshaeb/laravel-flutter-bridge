<?php

namespace LaravelFlutter\Generator\Tests\Unit;

use LaravelFlutter\Generator\Tests\TestCase;
use LaravelFlutter\Generator\Generators\ApiServiceGenerator;

class ApiServiceGeneratorTest extends TestCase
{
    protected ApiServiceGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->generator = new ApiServiceGenerator();
    }

    public function test_generate_creates_valid_service_class(): void
    {
        $modelData = [
            'class_name' => 'User',
            'resource_name' => 'users',
            'routes' => [
                [
                    'uri' => 'api/users',
                    'methods' => ['GET'],
                    'name' => 'users.index',
                    'endpoint_type' => 'index',
                    'http_method' => 'GET',
                    'parameters' => [],
                ],
                [
                    'uri' => 'api/users/{user}',
                    'methods' => ['GET'],
                    'name' => 'users.show',
                    'endpoint_type' => 'show',
                    'http_method' => 'GET',
                    'parameters' => [
                        ['name' => 'user', 'type' => 'int', 'required' => true]
                    ],
                ],
            ],
        ];

        $result = $this->generator->generate($modelData);

        $this->assertIsString($result);
        $this->assertStringContainsString('class UserService {', $result);
        $this->assertStringContainsString('final ApiService _apiService;', $result);
        $this->assertStringContainsString('final String _endpoint;', $result);
    }

    public function test_generate_includes_crud_methods(): void
    {
        $modelData = [
            'class_name' => 'User',
            'resource_name' => 'users',
        ];

        $result = $this->generator->generate($modelData);

        // Check for CRUD methods
        $this->assertStringContainsString('Future<List<User>> getAll(', $result);
        $this->assertStringContainsString('Future<User> getById(int id)', $result);
        $this->assertStringContainsString('Future<User> create(Map<String, dynamic> data)', $result);
        $this->assertStringContainsString('Future<User> update(int id, Map<String, dynamic> data)', $result);
        $this->assertStringContainsString('Future<bool> delete(int id)', $result);
    }

    public function test_generate_includes_proper_imports(): void
    {
        $modelData = [
            'class_name' => 'User',
            'resource_name' => 'users',
        ];

        $result = $this->generator->generate($modelData);

        $this->assertStringContainsString("import 'dart:convert';", $result);
        $this->assertStringContainsString("import 'package:http/http.dart' as http;", $result);
        $this->assertStringContainsString("import '../models/user.dart';", $result);
        $this->assertStringContainsString("import 'api_service.dart';", $result);
    }

    public function test_generate_includes_error_handling(): void
    {
        $modelData = [
            'class_name' => 'User',
            'resource_name' => 'users',
        ];

        $result = $this->generator->generate($modelData);

        $this->assertStringContainsString('try {', $result);
        $this->assertStringContainsString('} catch (e) {', $result);
        $this->assertStringContainsString('throw Exception(', $result);
    }

    public function test_generate_includes_pagination_support(): void
    {
        $modelData = [
            'class_name' => 'User',
            'resource_name' => 'users',
        ];

        $result = $this->generator->generate($modelData);

        $this->assertStringContainsString('{int? page, Map<String, dynamic>? filters}', $result);
        $this->assertStringContainsString('if (page != null) queryParams[\'page\'] = page.toString();', $result);
    }

    public function test_get_file_extension_returns_dart(): void
    {
        $this->assertEquals('.dart', $this->generator->getFileExtension());
    }

    public function test_get_output_path_returns_correct_path(): void
    {
        $path = $this->generator->getOutputPath('User');
        
        $this->assertStringContainsString('services', $path);
        $this->assertStringContainsString('user_service.dart', $path);
    }

    public function test_generate_with_custom_routes(): void
    {
        $modelData = [
            'class_name' => 'User',
            'resource_name' => 'users',
            'routes' => [
                [
                    'uri' => 'api/users/{user}/activate',
                    'methods' => ['POST'],
                    'name' => 'users.activate',
                    'endpoint_type' => 'activate',
                    'http_method' => 'POST',
                    'parameters' => [
                        ['name' => 'user', 'type' => 'int', 'required' => true]
                    ],
                ],
                [
                    'uri' => 'api/users/{user}/posts',
                    'methods' => ['GET'],
                    'name' => 'users.posts',
                    'endpoint_type' => 'posts',
                    'http_method' => 'GET',
                    'parameters' => [
                        ['name' => 'user', 'type' => 'int', 'required' => true]
                    ],
                ],
            ],
        ];

        $result = $this->generator->generate($modelData);

        // Should include custom methods
        $this->assertStringContainsString('Future<', $result);
        $this->assertStringContainsString('activate(', $result);
        $this->assertStringContainsString('posts(', $result);
    }

    public function test_generate_handles_different_return_types(): void
    {
        $modelData = [
            'class_name' => 'User',
            'resource_name' => 'users',
            'routes' => [
                [
                    'uri' => 'api/users/{user}',
                    'methods' => ['DELETE'],
                    'name' => 'users.destroy',
                    'endpoint_type' => 'destroy',
                    'http_method' => 'DELETE',
                    'parameters' => [
                        ['name' => 'user', 'type' => 'int', 'required' => true]
                    ],
                ],
            ],
        ];

        $result = $this->generator->generate($modelData);

        // Delete method should return bool
        $this->assertStringContainsString('Future<bool> delete(int id)', $result);
        $this->assertStringContainsString('return true;', $result);
    }

    public function test_generate_includes_documentation(): void
    {
        $modelData = [
            'class_name' => 'User',
            'resource_name' => 'users',
        ];

        $result = $this->generator->generate($modelData);

        $this->assertStringContainsString('/// Get all User items', $result);
        $this->assertStringContainsString('/// Get a User by ID', $result);
        $this->assertStringContainsString('/// Create a new User', $result);
        $this->assertStringContainsString('/// Update a User', $result);
        $this->assertStringContainsString('/// Delete a resource by ID', $result);
    }
}
