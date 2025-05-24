<?php

namespace LaravelFlutter\Generator\Tests\Unit;

use LaravelFlutter\Generator\Tests\TestCase;
use LaravelFlutter\Generator\Analyzers\RouteAnalyzer;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

class RouteAnalyzerTest extends TestCase
{
    protected RouteAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->analyzer = new RouteAnalyzer();
        $this->setupTestRoutes();
    }

    protected function setupTestRoutes(): void
    {
        // Clear existing routes
        RouteFacade::getRoutes()->clear();
        
        // Add test API routes
        RouteFacade::group(['prefix' => 'api'], function () {
            RouteFacade::get('users', function () {})->name('users.index');
            RouteFacade::get('users/{user}', function () {})->name('users.show');
            RouteFacade::post('users', function () {})->name('users.store');
            RouteFacade::put('users/{user}', function () {})->name('users.update');
            RouteFacade::delete('users/{user}', function () {})->name('users.destroy');
            
            RouteFacade::get('posts', function () {})->name('posts.index');
            RouteFacade::post('posts', function () {})->name('posts.store');
            
            // Custom routes
            RouteFacade::post('users/{user}/activate', function () {})->name('users.activate');
            RouteFacade::get('users/{user}/posts', function () {})->name('users.posts');
        });
        
        // Add non-API routes (should be ignored)
        RouteFacade::get('home', function () {})->name('home');
        RouteFacade::get('about', function () {})->name('about');
    }

    public function test_can_analyze_returns_true_for_valid_inputs(): void
    {
        $this->assertTrue($this->analyzer->canAnalyze(null));
        $this->assertTrue($this->analyzer->canAnalyze([]));
        $this->assertTrue($this->analyzer->canAnalyze(app('router')));
    }

    public function test_analyze_api_routes_returns_correct_structure(): void
    {
        $result = $this->analyzer->analyzeApiRoutes();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('routes', $result);
        $this->assertArrayHasKey('grouped_routes', $result);
        $this->assertArrayHasKey('resources', $result);

        // Should have routes for users and posts
        $this->assertContains('users', $result['resources']);
        $this->assertContains('posts', $result['resources']);
    }

    public function test_analyze_route_extracts_correct_information(): void
    {
        $routes = RouteFacade::getRoutes()->getRoutes();
        $userIndexRoute = null;
        
        foreach ($routes as $route) {
            if ($route->getName() === 'users.index') {
                $userIndexRoute = $route;
                break;
            }
        }
        
        $this->assertNotNull($userIndexRoute);
        
        $result = $this->analyzer->analyzeRoute($userIndexRoute);

        $this->assertIsArray($result);
        $this->assertEquals('api/users', $result['uri']);
        $this->assertContains('GET', $result['methods']);
        $this->assertEquals('users.index', $result['name']);
        $this->assertTrue($result['is_api_route']);
        $this->assertEquals('GET', $result['http_method']);
        $this->assertEquals('index', $result['endpoint_type']);
    }

    public function test_extract_resource_name_works_correctly(): void
    {
        $routes = RouteFacade::getRoutes()->getRoutes();
        
        foreach ($routes as $route) {
            if ($route->getName() === 'users.show') {
                $result = $this->analyzer->analyzeRoute($route);
                $this->assertEquals('users', $result['resource_name']);
                break;
            }
        }
    }

    public function test_extract_parameters_works(): void
    {
        $routes = RouteFacade::getRoutes()->getRoutes();
        
        foreach ($routes as $route) {
            if ($route->getName() === 'users.show') {
                $result = $this->analyzer->analyzeRoute($route);
                
                $this->assertArrayHasKey('parameters', $result);
                $this->assertCount(1, $result['parameters']);
                $this->assertEquals('user', $result['parameters'][0]['name']);
                $this->assertTrue($result['parameters'][0]['required']);
                break;
            }
        }
    }

    public function test_determine_endpoint_type_works(): void
    {
        $routes = RouteFacade::getRoutes()->getRoutes();
        $expectedTypes = [
            'users.index' => 'index',
            'users.show' => 'show',
            'users.store' => 'store',
            'users.update' => 'update',
            'users.destroy' => 'destroy',
        ];
        
        foreach ($routes as $route) {
            $routeName = $route->getName();
            if (isset($expectedTypes[$routeName])) {
                $result = $this->analyzer->analyzeRoute($route);
                $this->assertEquals($expectedTypes[$routeName], $result['endpoint_type']);
            }
        }
    }

    public function test_grouped_routes_structure(): void
    {
        $result = $this->analyzer->analyzeApiRoutes();
        
        $this->assertArrayHasKey('users', $result['grouped_routes']);
        $this->assertArrayHasKey('posts', $result['grouped_routes']);
        
        // Users should have multiple routes
        $userRoutes = $result['grouped_routes']['users'];
        $this->assertGreaterThan(1, count($userRoutes));
        
        // Check that routes are properly grouped
        foreach ($userRoutes as $route) {
            $this->assertEquals('users', $route['resource_name']);
        }
    }

    public function test_custom_routes_are_detected(): void
    {
        $result = $this->analyzer->analyzeApiRoutes();
        
        $customRoutes = array_filter($result['routes'], function ($route) {
            return !in_array($route['endpoint_type'], ['index', 'show', 'store', 'update', 'destroy']);
        });
        
        $this->assertGreaterThan(0, count($customRoutes));
        
        // Find the activate route
        $activateRoute = array_filter($customRoutes, function ($route) {
            return str_contains($route['uri'], 'activate');
        });
        
        $this->assertCount(1, $activateRoute);
    }

    public function test_only_api_routes_are_analyzed(): void
    {
        $result = $this->analyzer->analyzeApiRoutes();
        
        foreach ($result['routes'] as $route) {
            $this->assertTrue($route['is_api_route']);
            $this->assertStringStartsWith('api/', $route['uri']);
        }
    }

    public function test_http_method_extraction(): void
    {
        $result = $this->analyzer->analyzeApiRoutes();
        
        $methodMap = [];
        foreach ($result['routes'] as $route) {
            $methodMap[$route['name']] = $route['http_method'];
        }
        
        $this->assertEquals('GET', $methodMap['users.index']);
        $this->assertEquals('GET', $methodMap['users.show']);
        $this->assertEquals('POST', $methodMap['users.store']);
        $this->assertEquals('PUT', $methodMap['users.update']);
        $this->assertEquals('DELETE', $methodMap['users.destroy']);
    }
}
