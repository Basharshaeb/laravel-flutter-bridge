<?php

namespace BasharShaeb\LaravelFlutterGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use BasharShaeb\LaravelFlutterGenerator\Contracts\GeneratorInterface;

abstract class BaseGenerator implements GeneratorInterface
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected array $config;

    /**
     * Create a new generator instance.
     *
     * @param array $config The configuration array
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * Generate code based on the provided data.
     *
     * @param array $data The data to generate code from
     * @param array $options Additional options for generation
     * @return string The generated code
     */
    abstract public function generate(array $data, array $options = []): string;

    /**
     * Get the file extension for the generated code.
     *
     * @return string The file extension
     */
    public function getFileExtension(): string
    {
        return '.dart';
    }

    /**
     * Get the output path for the generated file.
     *
     * @param string $name The name of the file
     * @return string The output path
     */
    abstract public function getOutputPath(string $name): string;

    /**
     * Get the default configuration.
     *
     * @return array The default configuration
     */
    protected function getDefaultConfig(): array
    {
        return config('flutter-generator', []);
    }

    /**
     * Render a template with the given data.
     *
     * @param string $template The template name
     * @param array $data The data to pass to the template
     * @return string The rendered template
     */
    protected function renderTemplate(string $template, array $data = []): string
    {
        $templatePath = $this->getTemplatePath($template);

        if (!File::exists($templatePath)) {
            throw new \InvalidArgumentException("Template not found: {$templatePath}");
        }

        // Simple template rendering - in a real implementation, you might use Blade
        $content = File::get($templatePath);

        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $content = str_replace($placeholder, $value, $content);
        }

        return $content;
    }

    /**
     * Get the full path to a template.
     *
     * @param string $template The template name
     * @return string The template path
     */
    protected function getTemplatePath(string $template): string
    {
        $basePath = $this->config['templates']['path'] ?? __DIR__ . '/../Templates';
        $extension = $this->config['templates']['extension'] ?? '.dart.stub';

        return $basePath . '/' . $template . $extension;
    }

    /**
     * Convert a string to camelCase.
     *
     * @param string $string The string to convert
     * @return string The camelCase string
     */
    protected function toCamelCase(string $string): string
    {
        return Str::camel($string);
    }

    /**
     * Convert a string to PascalCase.
     *
     * @param string $string The string to convert
     * @return string The PascalCase string
     */
    protected function toPascalCase(string $string): string
    {
        return Str::studly($string);
    }

    /**
     * Convert a string to snake_case.
     *
     * @param string $string The string to convert
     * @return string The snake_case string
     */
    protected function toSnakeCase(string $string): string
    {
        return Str::snake($string);
    }

    /**
     * Convert a string to kebab-case.
     *
     * @param string $string The string to convert
     * @return string The kebab-case string
     */
    protected function toKebabCase(string $string): string
    {
        return Str::kebab($string);
    }

    /**
     * Get the base output path.
     *
     * @return string The base output path
     */
    protected function getBaseOutputPath(): string
    {
        return $this->config['output']['base_path'] ?? base_path('flutter_output');
    }

    /**
     * Ensure the output directory exists.
     *
     * @param string $path The directory path
     * @return void
     */
    protected function ensureDirectoryExists(string $path): void
    {
        $directory = dirname($path);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    /**
     * Write content to a file.
     *
     * @param string $path The file path
     * @param string $content The content to write
     * @return bool True if successful
     */
    protected function writeFile(string $path, string $content): bool
    {
        $this->ensureDirectoryExists($path);

        return File::put($path, $content) !== false;
    }

    /**
     * Get the imports for the generated file.
     *
     * @param array $data The data array
     * @return array The imports
     */
    protected function getImports(array $data): array
    {
        return [];
    }

    /**
     * Format the generated code.
     *
     * @param string $code The code to format
     * @return string The formatted code
     */
    protected function formatCode(string $code): string
    {
        // Basic formatting - remove extra blank lines
        $lines = explode("\n", $code);
        $formatted = [];
        $previousLineEmpty = false;

        foreach ($lines as $line) {
            $isEmpty = trim($line) === '';

            if ($isEmpty && $previousLineEmpty) {
                continue; // Skip consecutive empty lines
            }

            $formatted[] = $line;
            $previousLineEmpty = $isEmpty;
        }

        return implode("\n", $formatted);
    }

    /**
     * Generate documentation comment.
     *
     * @param string $description The description
     * @param array $params The parameters
     * @return string The documentation comment
     */
    protected function generateDocComment(string $description, array $params = []): string
    {
        $doc = "/// {$description}\n";

        if (!empty($params)) {
            $doc .= "///\n";
            foreach ($params as $param => $desc) {
                $doc .= "/// [{$param}] {$desc}\n";
            }
        }

        return $doc;
    }
}
