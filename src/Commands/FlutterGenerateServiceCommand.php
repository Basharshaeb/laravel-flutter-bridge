<?php

namespace BasharShaeb\LaravelFlutterGenerator\Commands;

use BasharShaeb\LaravelFlutterGenerator\Generators\ApiServiceGenerator;

class FlutterGenerateServiceCommand extends BaseFlutterCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flutter:generate-service
                            {model? : The model name to generate service for}
                            {--all : Generate services for all available models}
                            {--force : Overwrite existing files}
                            {--with-routes : Include route analysis for custom methods}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Flutter API service classes from Laravel models and routes';

    /**
     * The API service generator instance.
     *
     * @var ApiServiceGenerator
     */
    protected ApiServiceGenerator $generator;

    /**
     * Create a new command instance.
     */
    public function __construct(
        \BasharShaeb\LaravelFlutterGenerator\Analyzers\ModelAnalyzer $modelAnalyzer,
        \BasharShaeb\LaravelFlutterGenerator\Analyzers\RouteAnalyzer $routeAnalyzer,
        ApiServiceGenerator $generator
    ) {
        parent::__construct($modelAnalyzer, $routeAnalyzer);
        $this->generator = $generator;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Flutter Service Generator');
        $this->info('============================');

        // Ensure base API service exists
        $this->ensureBaseApiServiceExists();

        try {
            if ($this->option('all')) {
                return $this->generateAllServices();
            }

            $modelName = $this->argument('model');

            if (!$modelName) {
                $modelName = $this->askForModel();
            }

            return $this->generateSingleService($modelName);

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Generate services for all available models.
     *
     * @return int
     */
    protected function generateAllServices(): int
    {
        $models = $this->getAvailableModels();
        $models = $this->filterExcludedModels($models);

        if (empty($models)) {
            $this->warn('No models found to generate services for.');
            return self::SUCCESS;
        }

        $this->info("Found " . count($models) . " models to generate services for:");
        foreach ($models as $model) {
            $this->line("  - " . class_basename($model));
        }

        if (!$this->confirm('Do you want to continue?')) {
            $this->info('Generation cancelled.');
            return self::SUCCESS;
        }

        $results = [];
        $progressBar = $this->output->createProgressBar(count($models));
        $progressBar->start();

        foreach ($models as $modelClass) {
            try {
                $result = $this->generateServiceFile($modelClass);
                $results[] = [
                    'model' => class_basename($modelClass),
                    'file' => $result['file'],
                    'success' => $result['success'],
                    'error' => $result['error'] ?? null,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'model' => class_basename($modelClass),
                    'file' => 'N/A',
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->displaySummary($results);

        return self::SUCCESS;
    }

    /**
     * Generate a single service.
     *
     * @param string $modelName
     * @return int
     */
    protected function generateSingleService(string $modelName): int
    {
        try {
            $modelClass = $this->validateAndGetModelClass($modelName);

            if ($this->isModelExcluded($modelClass)) {
                $this->warn("Model '{$modelName}' is excluded from generation.");
                return self::SUCCESS;
            }

            $this->info("Generating API service for: " . class_basename($modelClass));

            $result = $this->generateServiceFile($modelClass);

            if ($result['success']) {
                $this->info("✅ Successfully generated: {$result['file']}");
                return self::SUCCESS;
            } else {
                $this->error("❌ Failed to generate service: {$result['error']}");
                return self::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error("Error generating service for '{$modelName}': " . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Ask user to select a model.
     *
     * @return string
     */
    protected function askForModel(): string
    {
        $models = $this->getAvailableModels();
        $models = $this->filterExcludedModels($models);

        if (empty($models)) {
            throw new \RuntimeException('No models found in the application.');
        }

        $modelNames = array_map('class_basename', $models);

        $selectedModel = $this->choice(
            'Which model would you like to generate a service for?',
            $modelNames
        );

        return $selectedModel;
    }

    /**
     * Generate the service file.
     *
     * @param string $modelClass
     * @return array
     */
    protected function generateServiceFile(string $modelClass): array
    {
        try {
            // Analyze the model
            $modelData = $this->modelAnalyzer->analyzeModel($modelClass);

            // Analyze routes if requested
            if ($this->option('with-routes')) {
                $routeData = $this->analyzeModelRoutes($modelClass);
                $modelData = array_merge($modelData, $routeData);
            }

            // Generate the service code
            $serviceCode = $this->generator->generate($modelData);

            // Get the output path
            $outputPath = $this->generator->getOutputPath($modelData['class_name']);

            // Write the file
            $overwrite = $this->option('force');
            $success = $this->writeFile($outputPath, $serviceCode, $overwrite);

            return [
                'success' => $success,
                'file' => $outputPath,
                'error' => $success ? null : 'Failed to write file',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'file' => 'N/A',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Analyze routes related to the model.
     *
     * @param string $modelClass
     * @return array
     */
    protected function analyzeModelRoutes(string $modelClass): array
    {
        $modelName = class_basename($modelClass);
        $resourceName = strtolower($modelName);

        // Get all API routes
        $routeData = $this->routeAnalyzer->analyzeApiRoutes();

        // Filter routes related to this model
        $modelRoutes = [];

        if (isset($routeData['grouped_routes'][$resourceName])) {
            $modelRoutes = $routeData['grouped_routes'][$resourceName];
        }

        // Also check for plural form
        $pluralResourceName = str_plural($resourceName);
        if (isset($routeData['grouped_routes'][$pluralResourceName])) {
            $modelRoutes = array_merge($modelRoutes, $routeData['grouped_routes'][$pluralResourceName]);
        }

        return [
            'routes' => $modelRoutes,
            'resource_name' => $resourceName,
        ];
    }
}
