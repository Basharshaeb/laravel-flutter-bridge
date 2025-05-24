<?php

/**
 * Dart Model Generator for Laravel Flutter Generator
 *
 * @author BasharShaeb
 * @package LaravelFlutter\Generator\Generators
 */

namespace LaravelFlutter\Generator\Generators;

use Illuminate\Support\Str;

class DartModelGenerator extends BaseGenerator
{
    /**
     * Generate Dart model code based on the provided data.
     *
     * @param array $data The model data to generate code from
     * @param array $options Additional options for generation
     * @return string The generated Dart model code
     */
    public function generate(array $data, array $options = []): string
    {
        $className = $this->toPascalCase($data['class_name']);
        $imports = $this->getImports($data);
        $properties = $this->generateProperties($data['attributes']);
        $constructor = $this->generateConstructor($className, $data['attributes']);
        $fromJson = $this->generateFromJson($className, $data['attributes']);
        $toJson = $this->generateToJson($data['attributes']);
        $copyWith = $this->generateCopyWith($className, $data['attributes']);
        $toString = $this->generateToString($className, $data['attributes']);
        $equality = $this->generateEquality($data['attributes']);

        $code = $this->buildModelClass(
            $className,
            $imports,
            $properties,
            $constructor,
            $fromJson,
            $toJson,
            $copyWith,
            $toString,
            $equality
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
        $modelsPath = $this->config['output']['models_path'] ?? 'models';
        $fileName = $this->toSnakeCase($name) . $this->getFileExtension();

        return $basePath . '/' . $modelsPath . '/' . $fileName;
    }

    /**
     * Get the imports for the generated file.
     *
     * @param array $data The data array
     * @return array The imports
     */
    protected function getImports(array $data): array
    {
        $imports = [];

        if ($this->config['generation']['use_json_annotation'] ?? true) {
            $imports[] = "import 'package:json_annotation/json_annotation.dart';";
        }

        // Add imports for relationships
        if (!empty($data['relationships'])) {
            foreach ($data['relationships'] as $relationship) {
                $relatedModel = class_basename($relationship['related_model']);
                $fileName = $this->toSnakeCase($relatedModel);
                $imports[] = "import '{$fileName}.dart';";
            }
        }

        return array_unique($imports);
    }

    /**
     * Generate properties for the model.
     *
     * @param array $attributes The model attributes
     * @return string The properties code
     */
    private function generateProperties(array $attributes): string
    {
        $properties = [];
        $excludedAttributes = $this->config['model_analysis']['excluded_attributes'] ?? [];

        foreach ($attributes as $attribute) {
            if (in_array($attribute['name'], $excludedAttributes)) {
                continue;
            }

            $type = $attribute['type'];
            $name = $this->toCamelCase($attribute['name']);
            $nullable = $attribute['nullable'] ? '?' : '';

            if ($this->config['generation']['use_json_annotation'] ?? true) {
                $properties[] = "  @JsonKey(name: '{$attribute['name']}')";
            }

            $properties[] = "  final {$type}{$nullable} {$name};";
            $properties[] = "";
        }

        return implode("\n", $properties);
    }

    /**
     * Generate constructor for the model.
     *
     * @param string $className The class name
     * @param array $attributes The model attributes
     * @return string The constructor code
     */
    private function generateConstructor(string $className, array $attributes): string
    {
        $parameters = [];
        $excludedAttributes = $this->config['model_analysis']['excluded_attributes'] ?? [];

        foreach ($attributes as $attribute) {
            if (in_array($attribute['name'], $excludedAttributes)) {
                continue;
            }

            $name = $this->toCamelCase($attribute['name']);
            $required = !$attribute['nullable'] ? 'required ' : '';
            $parameters[] = "    {$required}this.{$name},";
        }

        $parameterString = implode("\n", $parameters);

        return "  const {$className}({\n{$parameterString}\n  });";
    }

    /**
     * Generate fromJson factory method.
     *
     * @param string $className The class name
     * @param array $attributes The model attributes
     * @return string The fromJson code
     */
    private function generateFromJson(string $className, array $attributes): string
    {
        if ($this->config['generation']['use_json_annotation'] ?? true) {
            return "  factory {$className}.fromJson(Map<String, dynamic> json) => _\${$className}FromJson(json);";
        }

        $assignments = [];
        $excludedAttributes = $this->config['model_analysis']['excluded_attributes'] ?? [];

        foreach ($attributes as $attribute) {
            if (in_array($attribute['name'], $excludedAttributes)) {
                continue;
            }

            $name = $this->toCamelCase($attribute['name']);
            $jsonKey = $attribute['name'];
            $type = $attribute['type'];

            $assignment = $this->generateJsonAssignment($name, $jsonKey, $type, $attribute['nullable']);
            $assignments[] = "      {$name}: {$assignment},";
        }

        $assignmentString = implode("\n", $assignments);

        return "  factory {$className}.fromJson(Map<String, dynamic> json) {\n" .
               "    return {$className}(\n{$assignmentString}\n    );\n  }";
    }

    /**
     * Generate toJson method.
     *
     * @param array $attributes The model attributes
     * @return string The toJson code
     */
    private function generateToJson(array $attributes): string
    {
        if ($this->config['generation']['use_json_annotation'] ?? true) {
            return "  Map<String, dynamic> toJson() => _\$this.toJson();";
        }

        $assignments = [];
        $excludedAttributes = $this->config['model_analysis']['excluded_attributes'] ?? [];

        foreach ($attributes as $attribute) {
            if (in_array($attribute['name'], $excludedAttributes)) {
                continue;
            }

            $name = $this->toCamelCase($attribute['name']);
            $jsonKey = $attribute['name'];
            $assignments[] = "      '{$jsonKey}': {$name},";
        }

        $assignmentString = implode("\n", $assignments);

        return "  Map<String, dynamic> toJson() {\n" .
               "    return {\n{$assignmentString}\n    };\n  }";
    }

    /**
     * Generate copyWith method.
     *
     * @param string $className The class name
     * @param array $attributes The model attributes
     * @return string The copyWith code
     */
    private function generateCopyWith(string $className, array $attributes): string
    {
        $parameters = [];
        $assignments = [];
        $excludedAttributes = $this->config['model_analysis']['excluded_attributes'] ?? [];

        foreach ($attributes as $attribute) {
            if (in_array($attribute['name'], $excludedAttributes)) {
                continue;
            }

            $name = $this->toCamelCase($attribute['name']);
            $type = $attribute['type'];
            $nullable = $attribute['nullable'] ? '?' : '';

            $parameters[] = "    {$type}{$nullable} {$name},";
            $assignments[] = "      {$name}: {$name} ?? this.{$name},";
        }

        $parameterString = implode("\n", $parameters);
        $assignmentString = implode("\n", $assignments);

        return "  {$className} copyWith({\n{$parameterString}\n  }) {\n" .
               "    return {$className}(\n{$assignmentString}\n    );\n  }";
    }

    /**
     * Generate toString method.
     *
     * @param string $className The class name
     * @param array $attributes The model attributes
     * @return string The toString code
     */
    private function generateToString(string $className, array $attributes): string
    {
        $properties = [];
        $excludedAttributes = $this->config['model_analysis']['excluded_attributes'] ?? [];

        foreach ($attributes as $attribute) {
            if (in_array($attribute['name'], $excludedAttributes)) {
                continue;
            }

            $name = $this->toCamelCase($attribute['name']);
            $properties[] = "{$name}: \${$name}";
        }

        $propertiesString = implode(', ', $properties);

        return "  @override\n" .
               "  String toString() {\n" .
               "    return '{$className}({$propertiesString})';\n" .
               "  }";
    }

    /**
     * Generate equality operators.
     *
     * @param array $attributes The model attributes
     * @return string The equality code
     */
    private function generateEquality(array $attributes): string
    {
        $comparisons = [];
        $hashCodes = [];
        $excludedAttributes = $this->config['model_analysis']['excluded_attributes'] ?? [];

        foreach ($attributes as $attribute) {
            if (in_array($attribute['name'], $excludedAttributes)) {
                continue;
            }

            $name = $this->toCamelCase($attribute['name']);
            $comparisons[] = "{$name} == other.{$name}";
            $hashCodes[] = "{$name}.hashCode";
        }

        $comparisonString = implode(' &&\n        ', $comparisons);
        $hashCodeString = implode(' ^\n        ', $hashCodes);

        return "  @override\n" .
               "  bool operator ==(Object other) {\n" .
               "    if (identical(this, other)) return true;\n" .
               "    return other is {$this->toPascalCase('className')} &&\n" .
               "        {$comparisonString};\n" .
               "  }\n\n" .
               "  @override\n" .
               "  int get hashCode {\n" .
               "    return {$hashCodeString};\n" .
               "  }";
    }

    /**
     * Generate JSON assignment for a property.
     *
     * @param string $name The property name
     * @param string $jsonKey The JSON key
     * @param string $type The property type
     * @param bool $nullable Whether the property is nullable
     * @return string The assignment code
     */
    private function generateJsonAssignment(string $name, string $jsonKey, string $type, bool $nullable): string
    {
        $baseAssignment = "json['{$jsonKey}']";

        if ($type === 'DateTime') {
            if ($nullable) {
                return "json['{$jsonKey}'] != null ? DateTime.parse(json['{$jsonKey}']) : null";
            }
            return "DateTime.parse({$baseAssignment})";
        }

        if ($type === 'int' || $type === 'double') {
            if ($nullable) {
                return "json['{$jsonKey}']?.to{$type}()";
            }
            return "{$baseAssignment}";
        }

        return $baseAssignment;
    }

    /**
     * Build the complete model class.
     *
     * @param string $className The class name
     * @param array $imports The imports
     * @param string $properties The properties
     * @param string $constructor The constructor
     * @param string $fromJson The fromJson method
     * @param string $toJson The toJson method
     * @param string $copyWith The copyWith method
     * @param string $toString The toString method
     * @param string $equality The equality methods
     * @return string The complete class code
     */
    private function buildModelClass(
        string $className,
        array $imports,
        string $properties,
        string $constructor,
        string $fromJson,
        string $toJson,
        string $copyWith,
        string $toString,
        string $equality
    ): string {
        $importsString = implode("\n", $imports);
        $partDirective = $this->config['generation']['use_json_annotation'] ?? true
            ? "\n\npart '{$this->toSnakeCase($className)}.g.dart';"
            : '';

        return "{$importsString}{$partDirective}\n\n" .
               "@JsonSerializable()\n" .
               "class {$className} {\n" .
               "{$properties}\n" .
               "{$constructor}\n\n" .
               "{$fromJson}\n\n" .
               "{$toJson}\n\n" .
               "{$copyWith}\n\n" .
               "{$toString}\n\n" .
               "{$equality}\n" .
               "}";
    }
}
