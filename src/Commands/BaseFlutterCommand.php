<?php

namespace BasharShaeb\LaravelFlutterGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use BasharShaeb\LaravelFlutterGenerator\Analyzers\ModelAnalyzer;
use BasharShaeb\LaravelFlutterGenerator\Analyzers\RouteAnalyzer;

abstract class BaseFlutterCommand extends Command
{
    /**
     * The model analyzer instance.
     *
     * @var ModelAnalyzer
     */
    protected ModelAnalyzer $modelAnalyzer;

    /**
     * The route analyzer instance.
     *
     * @var RouteAnalyzer
     */
    protected RouteAnalyzer $routeAnalyzer;

    /**
     * Create a new command instance.
     */
    public function __construct(ModelAnalyzer $modelAnalyzer = null, RouteAnalyzer $routeAnalyzer = null)
    {
        parent::__construct();

        $this->modelAnalyzer = $modelAnalyzer ?? new ModelAnalyzer();
        $this->routeAnalyzer = $routeAnalyzer ?? new RouteAnalyzer();
    }

    /**
     * Get all available models.
     *
     * @return array
     */
    protected function getAvailableModels(): array
    {
        $models = [];
        $modelPaths = [
            app_path('Models'),
            app_path(), // For Laravel apps that don't use Models directory
        ];

        foreach ($modelPaths as $path) {
            if (!File::isDirectory($path)) {
                continue;
            }

            $files = File::allFiles($path);

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $className = $this->getClassNameFromFile($file->getPathname());

                if ($className && $this->isEloquentModel($className)) {
                    $models[] = $className;
                }
            }
        }

        return array_unique($models);
    }

    /**
     * Get class name from file path.
     *
     * @param string $filePath
     * @return string|null
     */
    protected function getClassNameFromFile(string $filePath): ?string
    {
        $content = File::get($filePath);

        // Extract namespace
        if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches)) {
            $namespace = $namespaceMatches[1];
        } else {
            $namespace = '';
        }

        // Extract class name
        if (preg_match('/class\s+(\w+)/', $content, $classMatches)) {
            $className = $classMatches[1];
            return $namespace ? $namespace . '\\' . $className : $className;
        }

        return null;
    }

    /**
     * Check if a class is an Eloquent model.
     *
     * @param string $className
     * @return bool
     */
    protected function isEloquentModel(string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        try {
            $reflection = new \ReflectionClass($className);
            return $reflection->isSubclassOf(\Illuminate\Database\Eloquent\Model::class) &&
                   !$reflection->isAbstract();
        } catch (\ReflectionException $e) {
            return false;
        }
    }

    /**
     * Validate model name and return the full class name.
     *
     * @param string $modelName
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function validateAndGetModelClass(string $modelName): string
    {
        // Try different possible class names
        $possibleClasses = [
            $modelName,
            'App\\Models\\' . $modelName,
            'App\\' . $modelName,
        ];

        foreach ($possibleClasses as $className) {
            if (class_exists($className) && $this->isEloquentModel($className)) {
                return $className;
            }
        }

        throw new \InvalidArgumentException("Model '{$modelName}' not found or is not an Eloquent model.");
    }

    /**
     * Ensure output directory exists.
     *
     * @param string $path
     * @return void
     */
    protected function ensureDirectoryExists(string $path): void
    {
        $directory = dirname($path);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
            $this->info("Created directory: {$directory}");
        }
    }

    /**
     * Write content to file.
     *
     * @param string $path
     * @param string $content
     * @param bool $overwrite
     * @return bool
     */
    protected function writeFile(string $path, string $content, bool $overwrite = false): bool
    {
        if (File::exists($path) && !$overwrite) {
            if (!$this->confirm("File {$path} already exists. Overwrite?")) {
                $this->warn("Skipped: {$path}");
                return false;
            }
        }

        $this->ensureDirectoryExists($path);

        if (File::put($path, $content) !== false) {
            $this->info("Generated: {$path}");
            return true;
        }

        $this->error("Failed to write: {$path}");
        return false;
    }

    /**
     * Get excluded models from configuration.
     *
     * @return array
     */
    protected function getExcludedModels(): array
    {
        return config('flutter-generator.excluded_models', []);
    }

    /**
     * Check if model should be excluded.
     *
     * @param string $modelClass
     * @return bool
     */
    protected function isModelExcluded(string $modelClass): bool
    {
        $excludedModels = $this->getExcludedModels();

        return in_array($modelClass, $excludedModels) ||
               in_array(class_basename($modelClass), $excludedModels);
    }

    /**
     * Filter models by exclusion list.
     *
     * @param array $models
     * @return array
     */
    protected function filterExcludedModels(array $models): array
    {
        return array_filter($models, function ($model) {
            return !$this->isModelExcluded($model);
        });
    }

    /**
     * Display generation summary.
     *
     * @param array $results
     * @return void
     */
    protected function displaySummary(array $results): void
    {
        $successful = array_filter($results, fn($result) => $result['success']);
        $failed = array_filter($results, fn($result) => !$result['success']);

        $this->newLine();
        $this->info('Generation Summary:');
        $this->info('==================');
        $this->info('Successful: ' . count($successful));

        if (!empty($failed)) {
            $this->error('Failed: ' . count($failed));

            foreach ($failed as $failure) {
                $this->error("  - {$failure['file']}: {$failure['error']}");
            }
        }

        $this->newLine();
    }

    /**
     * Get the base output path.
     *
     * @return string
     */
    protected function getBaseOutputPath(): string
    {
        return config('flutter-generator.output.base_path', base_path('flutter_output'));
    }

    /**
     * Create base API service if it doesn't exist.
     *
     * @return void
     */
    protected function ensureBaseApiServiceExists(): void
    {
        $basePath = $this->getBaseOutputPath();
        $servicesPath = config('flutter-generator.output.services_path', 'services');
        $apiServicePath = $basePath . '/' . $servicesPath . '/api_service.dart';

        if (!File::exists($apiServicePath)) {
            $this->createBaseApiService($apiServicePath);
        }
    }

    /**
     * Create the base API service file.
     *
     * @param string $path
     * @return void
     */
    private function createBaseApiService(string $path): void
    {
        $baseUrl = config('flutter-generator.api.base_url', 'http://localhost:8000/api');
        $timeout = config('flutter-generator.api.timeout', 30);

        $content = "import 'dart:convert';\nimport 'package:http/http.dart' as http;\n\n" .
                   "class ApiService {\n" .
                   "  static const String baseUrl = '{$baseUrl}';\n" .
                   "  static const Duration timeout = Duration(seconds: {$timeout});\n\n" .
                   "  final http.Client _client = http.Client();\n" .
                   "  String? _authToken;\n\n" .
                   "  void setAuthToken(String token) {\n" .
                   "    _authToken = token;\n" .
                   "  }\n\n" .
                   "  Map<String, String> get _headers {\n" .
                   "    final headers = {\n" .
                   "      'Content-Type': 'application/json',\n" .
                   "      'Accept': 'application/json',\n" .
                   "    };\n\n" .
                   "    if (_authToken != null) {\n" .
                   "      headers['Authorization'] = 'Bearer \$_authToken';\n" .
                   "    }\n\n" .
                   "    return headers;\n" .
                   "  }\n\n" .
                   "  Future<Map<String, dynamic>> get(String endpoint, {Map<String, String>? queryParams}) async {\n" .
                   "    final uri = Uri.parse('\$baseUrl/\$endpoint');\n" .
                   "    final uriWithQuery = queryParams != null ? uri.replace(queryParameters: queryParams) : uri;\n\n" .
                   "    final response = await _client.get(uriWithQuery, headers: _headers).timeout(timeout);\n" .
                   "    return _handleResponse(response);\n" .
                   "  }\n\n" .
                   "  Future<Map<String, dynamic>> post(String endpoint, Map<String, dynamic> data) async {\n" .
                   "    final uri = Uri.parse('\$baseUrl/\$endpoint');\n" .
                   "    final response = await _client.post(\n" .
                   "      uri,\n" .
                   "      headers: _headers,\n" .
                   "      body: jsonEncode(data),\n" .
                   "    ).timeout(timeout);\n" .
                   "    return _handleResponse(response);\n" .
                   "  }\n\n" .
                   "  Future<Map<String, dynamic>> put(String endpoint, Map<String, dynamic> data) async {\n" .
                   "    final uri = Uri.parse('\$baseUrl/\$endpoint');\n" .
                   "    final response = await _client.put(\n" .
                   "      uri,\n" .
                   "      headers: _headers,\n" .
                   "      body: jsonEncode(data),\n" .
                   "    ).timeout(timeout);\n" .
                   "    return _handleResponse(response);\n" .
                   "  }\n\n" .
                   "  Future<Map<String, dynamic>> delete(String endpoint) async {\n" .
                   "    final uri = Uri.parse('\$baseUrl/\$endpoint');\n" .
                   "    final response = await _client.delete(uri, headers: _headers).timeout(timeout);\n" .
                   "    return _handleResponse(response);\n" .
                   "  }\n\n" .
                   "  Map<String, dynamic> _handleResponse(http.Response response) {\n" .
                   "    if (response.statusCode >= 200 && response.statusCode < 300) {\n" .
                   "      return jsonDecode(response.body);\n" .
                   "    } else {\n" .
                   "      throw Exception('HTTP \${response.statusCode}: \${response.body}');\n" .
                   "    }\n" .
                   "  }\n\n" .
                   "  void dispose() {\n" .
                   "    _client.close();\n" .
                   "  }\n" .
                   "}";

        $this->writeFile($path, $content, true);
    }
}
