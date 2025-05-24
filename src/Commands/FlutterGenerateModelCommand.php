<?php

namespace BasharShaeb\LaravelFlutterGenerator\Commands;

use BasharShaeb\LaravelFlutterGenerator\Generators\DartModelGenerator;

class FlutterGenerateModelCommand extends BaseFlutterCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flutter:generate-model
                            {model? : The model name to generate}
                            {--all : Generate models for all available models}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Flutter Dart models from Laravel Eloquent models';

    /**
     * The Dart model generator instance.
     *
     * @var DartModelGenerator
     */
    protected DartModelGenerator $generator;

    /**
     * Create a new command instance.
     */
    public function __construct(
        \BasharShaeb\LaravelFlutterGenerator\Analyzers\ModelAnalyzer $modelAnalyzer = null,
        \BasharShaeb\LaravelFlutterGenerator\Analyzers\RouteAnalyzer $routeAnalyzer = null,
        DartModelGenerator $generator = null
    ) {
        $this->modelAnalyzer = $modelAnalyzer ?? new \BasharShaeb\LaravelFlutterGenerator\Analyzers\ModelAnalyzer();
        $this->routeAnalyzer = $routeAnalyzer ?? new \BasharShaeb\LaravelFlutterGenerator\Analyzers\RouteAnalyzer();
        $this->generator = $generator ?? new DartModelGenerator();

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Flutter Model Generator');
        $this->info('==========================');

        try {
            if ($this->option('all')) {
                return $this->generateAllModels();
            }

            $modelName = $this->argument('model');

            if (!$modelName) {
                $modelName = $this->askForModel();
            }

            return $this->generateSingleModel($modelName);

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Generate models for all available models.
     *
     * @return int
     */
    protected function generateAllModels(): int
    {
        $models = $this->getAvailableModels();
        $models = $this->filterExcludedModels($models);

        if (empty($models)) {
            $this->warn('No models found to generate.');
            return self::SUCCESS;
        }

        $this->info("Found " . count($models) . " models to generate:");
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
                $result = $this->generateModelFile($modelClass);
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
     * Generate a single model.
     *
     * @param string $modelName
     * @return int
     */
    protected function generateSingleModel(string $modelName): int
    {
        try {
            $modelClass = $this->validateAndGetModelClass($modelName);

            if ($this->isModelExcluded($modelClass)) {
                $this->warn("Model '{$modelName}' is excluded from generation.");
                return self::SUCCESS;
            }

            $this->info("Generating Dart model for: " . class_basename($modelClass));

            $result = $this->generateModelFile($modelClass);

            if ($result['success']) {
                $this->info("✅ Successfully generated: {$result['file']}");
                return self::SUCCESS;
            } else {
                $this->error("❌ Failed to generate model: {$result['error']}");
                return self::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error("Error generating model '{$modelName}': " . $e->getMessage());
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
            'Which model would you like to generate?',
            $modelNames
        );

        // Find the full class name
        foreach ($models as $modelClass) {
            if (class_basename($modelClass) === $selectedModel) {
                return $selectedModel;
            }
        }

        return $selectedModel;
    }

    /**
     * Generate the model file.
     *
     * @param string $modelClass
     * @return array
     */
    protected function generateModelFile(string $modelClass): array
    {
        try {
            // Analyze the model
            $modelData = $this->modelAnalyzer->analyzeModel($modelClass);

            // Generate the Dart code
            $dartCode = $this->generator->generate($modelData);

            // Get the output path
            $outputPath = $this->generator->getOutputPath($modelData['class_name']);

            // Write the file
            $overwrite = $this->option('force');
            $success = $this->writeFile($outputPath, $dartCode, $overwrite);

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
}
