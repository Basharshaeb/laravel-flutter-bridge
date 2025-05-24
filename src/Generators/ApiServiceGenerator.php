<?php

namespace BasharShaeb\LaravelFlutterGenerator\Generators;

use Illuminate\Support\Str;

class ApiServiceGenerator extends BaseGenerator
{
    /**
     * Generate API service code based on the provided data.
     *
     * @param array $data The data to generate code from
     * @param array $options Additional options for generation
     * @return string The generated API service code
     */
    public function generate(array $data, array $options = []): string
    {
        $className = $this->toPascalCase($data['class_name'] ?? $data['resource_name']) . 'Service';
        $modelName = $this->toPascalCase($data['class_name'] ?? $data['resource_name']);
        $resourceName = $this->toSnakeCase($data['class_name'] ?? $data['resource_name']);

        $imports = $this->getImports($data);
        $properties = $this->generateProperties();
        $constructor = $this->generateConstructor($className);
        $methods = $this->generateMethods($modelName, $resourceName, $data);

        $code = $this->buildServiceClass(
            $className,
            $imports,
            $properties,
            $constructor,
            $methods
        );

        return $this->formatCode($code);
    }

    /**
     * Get the output path for the generated file.
     *
     * @param string $name The name of the file
     * @return string The output path
     */
    public function getOutputPath(string $name): string
    {
        $basePath = $this->getBaseOutputPath();
        $servicesPath = $this->config['output']['services_path'] ?? 'services';
        $fileName = $this->toSnakeCase($name) . '_service' . $this->getFileExtension();

        return $basePath . '/' . $servicesPath . '/' . $fileName;
    }

    /**
     * Get the imports for the generated file.
     *
     * @param array $data The data array
     * @return array The imports
     */
    protected function getImports(array $data): array
    {
        $modelName = $this->toPascalCase($data['class_name'] ?? $data['resource_name']);
        $modelFileName = $this->toSnakeCase($modelName);

        return [
            "import 'dart:convert';",
            "import 'package:http/http.dart' as http;",
            "import '../models/{$modelFileName}.dart';",
            "import 'api_service.dart';",
        ];
    }

    /**
     * Generate properties for the service.
     *
     * @return string The properties code
     */
    private function generateProperties(): string
    {
        return "  final ApiService _apiService;\n" .
               "  final String _endpoint;";
    }

    /**
     * Generate constructor for the service.
     *
     * @param string $className The class name
     * @return string The constructor code
     */
    private function generateConstructor(string $className): string
    {
        return "  {$className}(this._apiService, this._endpoint);";
    }

    /**
     * Generate service methods.
     *
     * @param string $modelName The model name
     * @param string $resourceName The resource name
     * @param array $data The data array
     * @return string The methods code
     */
    private function generateMethods(string $modelName, string $resourceName, array $data): string
    {
        $methods = [];

        // Generate CRUD methods
        $methods[] = $this->generateGetAllMethod($modelName);
        $methods[] = $this->generateGetByIdMethod($modelName);
        $methods[] = $this->generateCreateMethod($modelName);
        $methods[] = $this->generateUpdateMethod($modelName);
        $methods[] = $this->generateDeleteMethod();

        // Generate custom methods based on routes
        if (isset($data['routes'])) {
            foreach ($data['routes'] as $route) {
                if (!in_array($route['endpoint_type'], ['index', 'show', 'store', 'update', 'destroy'])) {
                    $methods[] = $this->generateCustomMethod($route, $modelName);
                }
            }
        }

        return implode("\n\n", $methods);
    }

    /**
     * Generate getAll method.
     *
     * @param string $modelName The model name
     * @return string The getAll method code
     */
    private function generateGetAllMethod(string $modelName): string
    {
        return $this->generateDocComment(
            "Get all {$modelName} items",
            ['page' => 'Optional page number for pagination']
        ) .
        "  Future<List<{$modelName}>> getAll({int? page, Map<String, dynamic>? filters}) async {\n" .
        "    try {\n" .
        "      final queryParams = <String, String>{};\n" .
        "      if (page != null) queryParams['page'] = page.toString();\n" .
        "      if (filters != null) {\n" .
        "        filters.forEach((key, value) {\n" .
        "          queryParams[key] = value.toString();\n" .
        "        });\n" .
        "      }\n\n" .
        "      final response = await _apiService.get(_endpoint, queryParams: queryParams);\n" .
        "      final List<dynamic> data = response['data'] ?? response;\n" .
        "      return data.map((json) => {$modelName}.fromJson(json)).toList();\n" .
        "    } catch (e) {\n" .
        "      throw Exception('Failed to fetch {$modelName} list: \$e');\n" .
        "    }\n" .
        "  }";
    }

    /**
     * Generate getById method.
     *
     * @param string $modelName The model name
     * @return string The getById method code
     */
    private function generateGetByIdMethod(string $modelName): string
    {
        return $this->generateDocComment(
            "Get a {$modelName} by ID",
            ['id' => 'The ID of the {$modelName}']
        ) .
        "  Future<{$modelName}> getById(int id) async {\n" .
        "    try {\n" .
        "      final response = await _apiService.get('\$_endpoint/\$id');\n" .
        "      final data = response['data'] ?? response;\n" .
        "      return {$modelName}.fromJson(data);\n" .
        "    } catch (e) {\n" .
        "      throw Exception('Failed to fetch {$modelName} with ID \$id: \$e');\n" .
        "    }\n" .
        "  }";
    }

    /**
     * Generate create method.
     *
     * @param string $modelName The model name
     * @return string The create method code
     */
    private function generateCreateMethod(string $modelName): string
    {
        return $this->generateDocComment(
            "Create a new {$modelName}",
            ['data' => 'The {$modelName} data to create']
        ) .
        "  Future<{$modelName}> create(Map<String, dynamic> data) async {\n" .
        "    try {\n" .
        "      final response = await _apiService.post(_endpoint, data);\n" .
        "      final responseData = response['data'] ?? response;\n" .
        "      return {$modelName}.fromJson(responseData);\n" .
        "    } catch (e) {\n" .
        "      throw Exception('Failed to create {$modelName}: \$e');\n" .
        "    }\n" .
        "  }";
    }

    /**
     * Generate update method.
     *
     * @param string $modelName The model name
     * @return string The update method code
     */
    private function generateUpdateMethod(string $modelName): string
    {
        return $this->generateDocComment(
            "Update a {$modelName}",
            [
                'id' => 'The ID of the {$modelName} to update',
                'data' => 'The updated {$modelName} data'
            ]
        ) .
        "  Future<{$modelName}> update(int id, Map<String, dynamic> data) async {\n" .
        "    try {\n" .
        "      final response = await _apiService.put('\$_endpoint/\$id', data);\n" .
        "      final responseData = response['data'] ?? response;\n" .
        "      return {$modelName}.fromJson(responseData);\n" .
        "    } catch (e) {\n" .
        "      throw Exception('Failed to update {$modelName} with ID \$id: \$e');\n" .
        "    }\n" .
        "  }";
    }

    /**
     * Generate delete method.
     *
     * @return string The delete method code
     */
    private function generateDeleteMethod(): string
    {
        return $this->generateDocComment(
            "Delete a resource by ID",
            ['id' => 'The ID of the resource to delete']
        ) .
        "  Future<bool> delete(int id) async {\n" .
        "    try {\n" .
        "      await _apiService.delete('\$_endpoint/\$id');\n" .
        "      return true;\n" .
        "    } catch (e) {\n" .
        "      throw Exception('Failed to delete resource with ID \$id: \$e');\n" .
        "    }\n" .
        "  }";
    }

    /**
     * Generate custom method based on route.
     *
     * @param array $route The route data
     * @param string $modelName The model name
     * @return string The custom method code
     */
    private function generateCustomMethod(array $route, string $modelName): string
    {
        $methodName = $this->toCamelCase($route['action'] ?? $route['endpoint_type']);
        $httpMethod = strtolower($route['http_method']);
        $hasBody = in_array($httpMethod, ['post', 'put', 'patch']);

        $parameters = [];
        $pathParams = [];

        // Add path parameters
        foreach ($route['parameters'] ?? [] as $param) {
            $paramType = $param['type'] === 'int' ? 'int' : 'String';
            $parameters[] = "{$paramType} {$param['name']}";
            $pathParams[] = $param['name'];
        }

        // Add body parameter for methods that support it
        if ($hasBody) {
            $parameters[] = "Map<String, dynamic> data";
        }

        $paramString = implode(', ', $parameters);
        $pathString = $route['uri'];

        // Replace path parameters
        foreach ($pathParams as $param) {
            $pathString = str_replace("{{$param}}", "\${$param}", $pathString);
        }

        $bodyParam = $hasBody ? ', data' : '';
        $returnType = $this->determineReturnType($route, $modelName);

        return $this->generateDocComment("Custom method: {$methodName}") .
               "  Future<{$returnType}> {$methodName}({$paramString}) async {\n" .
               "    try {\n" .
               "      final response = await _apiService.{$httpMethod}('{$pathString}'{$bodyParam});\n" .
               "      return {$this->generateReturnStatement($route, $modelName)};\n" .
               "    } catch (e) {\n" .
               "      throw Exception('Failed to execute {$methodName}: \$e');\n" .
               "    }\n" .
               "  }";
    }

    /**
     * Determine return type for custom method.
     *
     * @param array $route The route data
     * @param string $modelName The model name
     * @return string The return type
     */
    private function determineReturnType(array $route, string $modelName): string
    {
        $method = strtolower($route['http_method']);

        if ($method === 'delete') {
            return 'bool';
        }

        if (Str::contains($route['uri'], '{')) {
            return $modelName; // Single item
        }

        return "List<{$modelName}>"; // List of items
    }

    /**
     * Generate return statement for custom method.
     *
     * @param array $route The route data
     * @param string $modelName The model name
     * @return string The return statement
     */
    private function generateReturnStatement(array $route, string $modelName): string
    {
        $method = strtolower($route['http_method']);

        if ($method === 'delete') {
            return 'true';
        }

        if (Str::contains($route['uri'], '{')) {
            return "{$modelName}.fromJson(response['data'] ?? response)";
        }

        return "(response['data'] ?? response).map((json) => {$modelName}.fromJson(json)).toList()";
    }

    /**
     * Build the complete service class.
     *
     * @param string $className The class name
     * @param array $imports The imports
     * @param string $properties The properties
     * @param string $constructor The constructor
     * @param string $methods The methods
     * @return string The complete class code
     */
    private function buildServiceClass(
        string $className,
        array $imports,
        string $properties,
        string $constructor,
        string $methods
    ): string {
        $importsString = implode("\n", $imports);

        return "{$importsString}\n\n" .
               $this->generateDocComment("Service class for {$className}") .
               "class {$className} {\n" .
               "{$properties}\n\n" .
               "{$constructor}\n\n" .
               "{$methods}\n" .
               "}";
    }
}
