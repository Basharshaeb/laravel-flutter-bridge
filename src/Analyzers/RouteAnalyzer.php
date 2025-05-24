<?php

namespace LaravelFlutter\Generator\Analyzers;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use LaravelFlutter\Generator\Contracts\AnalyzerInterface;

class RouteAnalyzer implements AnalyzerInterface
{
    /**
     * Analyze the given routes.
     *
     * @param mixed $subject The routes to analyze
     * @return array The analyzed data
     */
    public function analyze($subject): array
    {
        if ($subject instanceof Router) {
            return $this->analyzeRouter($subject);
        }

        if (is_array($subject)) {
            return $this->analyzeRoutes($subject);
        }

        if ($subject instanceof Route) {
            return $this->analyzeRoute($subject);
        }

        // Default: analyze all API routes
        return $this->analyzeApiRoutes();
    }

    /**
     * Check if the analyzer can handle the given subject.
     *
     * @param mixed $subject The subject to check
     * @return bool True if the analyzer can handle the subject
     */
    public function canAnalyze($subject): bool
    {
        return $subject instanceof Router ||
               $subject instanceof Route ||
               is_array($subject) ||
               is_null($subject);
    }

    /**
     * Analyze all API routes.
     *
     * @return array The analyzed routes
     */
    public function analyzeApiRoutes(): array
    {
        $routes = collect(RouteFacade::getRoutes())
            ->filter(function (Route $route) {
                return Str::startsWith($route->uri(), 'api/');
            });

        return $this->analyzeRoutes($routes->all());
    }

    /**
     * Analyze a router instance.
     *
     * @param Router $router The router to analyze
     * @return array The analyzed routes
     */
    public function analyzeRouter(Router $router): array
    {
        return $this->analyzeRoutes($router->getRoutes()->getRoutes());
    }

    /**
     * Analyze an array of routes.
     *
     * @param array $routes The routes to analyze
     * @return array The analyzed routes
     */
    public function analyzeRoutes(array $routes): array
    {
        $analyzedRoutes = [];
        $groupedRoutes = [];

        foreach ($routes as $route) {
            $routeData = $this->analyzeRoute($route);
            $analyzedRoutes[] = $routeData;

            // Group routes by resource
            $resource = $this->extractResourceName($routeData);
            if ($resource) {
                $groupedRoutes[$resource][] = $routeData;
            }
        }

        return [
            'routes' => $analyzedRoutes,
            'grouped_routes' => $groupedRoutes,
            'resources' => array_keys($groupedRoutes),
        ];
    }

    /**
     * Analyze a single route.
     *
     * @param Route $route The route to analyze
     * @return array The analyzed route data
     */
    public function analyzeRoute(Route $route): array
    {
        $action = $route->getAction();
        $controller = $action['controller'] ?? null;
        $middleware = $route->middleware();

        return [
            'uri' => $route->uri(),
            'methods' => $route->methods(),
            'name' => $route->getName(),
            'controller' => $controller,
            'action' => $this->extractActionName($controller),
            'parameters' => $this->extractParameters($route),
            'middleware' => $middleware,
            'resource_name' => $this->extractResourceName($route),
            'is_api_route' => Str::startsWith($route->uri(), 'api/'),
            'requires_auth' => $this->requiresAuthentication($middleware),
            'http_method' => $this->getPrimaryHttpMethod($route->methods()),
            'endpoint_type' => $this->determineEndpointType($route),
        ];
    }

    /**
     * Extract resource name from route.
     *
     * @param Route|array $route The route or route data
     * @return string|null The resource name
     */
    private function extractResourceName($route): ?string
    {
        if (is_array($route)) {
            $uri = $route['uri'] ?? '';
            $name = $route['name'] ?? '';
        } else {
            $uri = $route->uri();
            $name = $route->getName();
        }

        // Try to extract from route name first
        if ($name && Str::contains($name, '.')) {
            $parts = explode('.', $name);
            return $parts[0];
        }

        // Extract from URI
        $uriParts = explode('/', trim($uri, '/'));
        
        // Remove 'api' prefix if present
        if ($uriParts[0] === 'api') {
            array_shift($uriParts);
        }

        // Return the first segment as resource name
        return $uriParts[0] ?? null;
    }

    /**
     * Extract action name from controller.
     *
     * @param string|null $controller The controller string
     * @return string|null The action name
     */
    private function extractActionName(?string $controller): ?string
    {
        if (!$controller) {
            return null;
        }

        if (Str::contains($controller, '@')) {
            return Str::after($controller, '@');
        }

        return null;
    }

    /**
     * Extract parameters from route.
     *
     * @param Route $route The route
     * @return array The parameters
     */
    private function extractParameters(Route $route): array
    {
        $parameters = [];
        
        foreach ($route->parameterNames() as $parameter) {
            $parameters[] = [
                'name' => $parameter,
                'required' => true,
                'type' => $this->guessParameterType($parameter),
            ];
        }

        return $parameters;
    }

    /**
     * Guess parameter type based on name.
     *
     * @param string $parameter The parameter name
     * @return string The guessed type
     */
    private function guessParameterType(string $parameter): string
    {
        if (Str::endsWith($parameter, '_id') || $parameter === 'id') {
            return 'int';
        }

        if (Str::contains($parameter, ['uuid', 'guid'])) {
            return 'String';
        }

        return 'String';
    }

    /**
     * Check if route requires authentication.
     *
     * @param array $middleware The middleware array
     * @return bool True if requires authentication
     */
    private function requiresAuthentication(array $middleware): bool
    {
        $authMiddleware = ['auth', 'auth:api', 'auth:sanctum', 'jwt.auth'];
        
        return !empty(array_intersect($middleware, $authMiddleware));
    }

    /**
     * Get primary HTTP method.
     *
     * @param array $methods The HTTP methods
     * @return string The primary method
     */
    private function getPrimaryHttpMethod(array $methods): string
    {
        // Remove HEAD and OPTIONS if other methods exist
        $filtered = array_diff($methods, ['HEAD', 'OPTIONS']);
        
        return !empty($filtered) ? $filtered[0] : $methods[0];
    }

    /**
     * Determine endpoint type.
     *
     * @param Route $route The route
     * @return string The endpoint type
     */
    private function determineEndpointType(Route $route): string
    {
        $method = $this->getPrimaryHttpMethod($route->methods());
        $uri = $route->uri();
        $action = $this->extractActionName($route->getAction()['controller'] ?? '');

        // Check for common CRUD patterns
        if ($method === 'GET' && !Str::contains($uri, '{')) {
            return 'index'; // List all
        }

        if ($method === 'GET' && Str::contains($uri, '{')) {
            return 'show'; // Show single
        }

        if ($method === 'POST') {
            return 'store'; // Create
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            return 'update'; // Update
        }

        if ($method === 'DELETE') {
            return 'destroy'; // Delete
        }

        // Fallback to action name or method
        return $action ?: strtolower($method);
    }
}
