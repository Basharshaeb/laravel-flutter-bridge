<?php

namespace LaravelFlutter\Generator\Commands;

use LaravelFlutter\Generator\Generators\DartModelGenerator;
use LaravelFlutter\Generator\Generators\ApiServiceGenerator;
use LaravelFlutter\Generator\Generators\WidgetGenerator;
use LaravelFlutter\Generator\Generators\ScreenGenerator;

class FlutterGenerateFeatureCommand extends BaseFlutterCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flutter:generate-feature 
                            {model? : The model name to generate feature for}
                            {--all : Generate features for all available models}
                            {--force : Overwrite existing files}
                            {--skip-model : Skip model generation}
                            {--skip-service : Skip service generation}
                            {--skip-widgets : Skip widget generation}
                            {--skip-screens : Skip screen generation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate complete Flutter features (models, services, widgets, screens) from Laravel models';

    /**
     * The generators.
     */
    protected DartModelGenerator $modelGenerator;
    protected ApiServiceGenerator $serviceGenerator;
    protected WidgetGenerator $widgetGenerator;
    protected ScreenGenerator $screenGenerator;

    /**
     * Create a new command instance.
     */
    public function __construct(
        \LaravelFlutter\Generator\Analyzers\ModelAnalyzer $modelAnalyzer,
        \LaravelFlutter\Generator\Analyzers\RouteAnalyzer $routeAnalyzer,
        DartModelGenerator $modelGenerator,
        ApiServiceGenerator $serviceGenerator,
        WidgetGenerator $widgetGenerator,
        ScreenGenerator $screenGenerator
    ) {
        parent::__construct($modelAnalyzer, $routeAnalyzer);
        $this->modelGenerator = $modelGenerator;
        $this->serviceGenerator = $serviceGenerator;
        $this->widgetGenerator = $widgetGenerator;
        $this->screenGenerator = $screenGenerator;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Flutter Feature Generator');
        $this->info('============================');

        // Ensure base API service exists
        $this->ensureBaseApiServiceExists();

        try {
            if ($this->option('all')) {
                return $this->generateAllFeatures();
            }

            $modelName = $this->argument('model');
            
            if (!$modelName) {
                $modelName = $this->askForModel();
            }

            return $this->generateSingleFeature($modelName);
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Generate features for all available models.
     *
     * @return int
     */
    protected function generateAllFeatures(): int
    {
        $models = $this->getAvailableModels();
        $models = $this->filterExcludedModels($models);

        if (empty($models)) {
            $this->warn('No models found to generate features for.');
            return self::SUCCESS;
        }

        $this->info("Found " . count($models) . " models to generate features for:");
        foreach ($models as $model) {
            $this->line("  - " . class_basename($model));
        }

        if (!$this->confirm('Do you want to continue?')) {
            $this->info('Generation cancelled.');
            return self::SUCCESS;
        }

        $allResults = [];
        $progressBar = $this->output->createProgressBar(count($models));
        $progressBar->start();

        foreach ($models as $modelClass) {
            $results = $this->generateFeatureFiles($modelClass);
            $allResults = array_merge($allResults, $results);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->displaySummary($allResults);

        return self::SUCCESS;
    }

    /**
     * Generate a single feature.
     *
     * @param string $modelName
     * @return int
     */
    protected function generateSingleFeature(string $modelName): int
    {
        try {
            $modelClass = $this->validateAndGetModelClass($modelName);
            
            if ($this->isModelExcluded($modelClass)) {
                $this->warn("Model '{$modelName}' is excluded from generation.");
                return self::SUCCESS;
            }

            $this->info("Generating complete Flutter feature for: " . class_basename($modelClass));

            $results = $this->generateFeatureFiles($modelClass);

            $this->displaySummary($results);

            $successful = array_filter($results, fn($result) => $result['success']);
            
            if (count($successful) === count($results)) {
                $this->info("✅ Successfully generated complete feature for {$modelName}");
                return self::SUCCESS;
            } else {
                $this->warn("⚠️  Feature generation completed with some errors for {$modelName}");
                return self::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error("Error generating feature for '{$modelName}': " . $e->getMessage());
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
            'Which model would you like to generate a complete feature for?',
            $modelNames
        );

        return $selectedModel;
    }

    /**
     * Generate all feature files for a model.
     *
     * @param string $modelClass
     * @return array
     */
    protected function generateFeatureFiles(string $modelClass): array
    {
        $results = [];
        $modelData = $this->modelAnalyzer->analyzeModel($modelClass);
        $overwrite = $this->option('force');

        // Generate model
        if (!$this->option('skip-model')) {
            $results[] = $this->generateModel($modelData, $overwrite);
        }

        // Generate service
        if (!$this->option('skip-service')) {
            $results[] = $this->generateService($modelData, $overwrite);
        }

        // Generate widgets
        if (!$this->option('skip-widgets')) {
            $results = array_merge($results, $this->generateWidgets($modelData, $overwrite));
        }

        // Generate screens
        if (!$this->option('skip-screens')) {
            $results = array_merge($results, $this->generateScreens($modelData, $overwrite));
        }

        return $results;
    }

    /**
     * Generate model file.
     *
     * @param array $modelData
     * @param bool $overwrite
     * @return array
     */
    protected function generateModel(array $modelData, bool $overwrite): array
    {
        try {
            $dartCode = $this->modelGenerator->generate($modelData);
            $outputPath = $this->modelGenerator->getOutputPath($modelData['class_name']);
            $success = $this->writeFile($outputPath, $dartCode, $overwrite);

            return [
                'type' => 'Model',
                'file' => $outputPath,
                'success' => $success,
                'error' => $success ? null : 'Failed to write file',
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'Model',
                'file' => 'N/A',
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate service file.
     *
     * @param array $modelData
     * @param bool $overwrite
     * @return array
     */
    protected function generateService(array $modelData, bool $overwrite): array
    {
        try {
            $serviceCode = $this->serviceGenerator->generate($modelData);
            $outputPath = $this->serviceGenerator->getOutputPath($modelData['class_name']);
            $success = $this->writeFile($outputPath, $serviceCode, $overwrite);

            return [
                'type' => 'Service',
                'file' => $outputPath,
                'success' => $success,
                'error' => $success ? null : 'Failed to write file',
            ];
        } catch (\Exception $e) {
            return [
                'type' => 'Service',
                'file' => 'N/A',
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate widget files.
     *
     * @param array $modelData
     * @param bool $overwrite
     * @return array
     */
    protected function generateWidgets(array $modelData, bool $overwrite): array
    {
        $results = [];
        $widgetTypes = ['form', 'list', 'card'];

        foreach ($widgetTypes as $widgetType) {
            try {
                $widgetCode = $this->widgetGenerator->generate($modelData, ['widget_type' => $widgetType]);
                $fileName = $this->getWidgetFileName($modelData['class_name'], $widgetType);
                $outputPath = $this->widgetGenerator->getOutputPath($fileName);
                $success = $this->writeFile($outputPath, $widgetCode, $overwrite);

                $results[] = [
                    'type' => "Widget ({$widgetType})",
                    'file' => $outputPath,
                    'success' => $success,
                    'error' => $success ? null : 'Failed to write file',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'type' => "Widget ({$widgetType})",
                    'file' => 'N/A',
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Generate screen files.
     *
     * @param array $modelData
     * @param bool $overwrite
     * @return array
     */
    protected function generateScreens(array $modelData, bool $overwrite): array
    {
        $results = [];
        $screenTypes = ['list', 'detail', 'create', 'edit'];

        foreach ($screenTypes as $screenType) {
            try {
                $screenCode = $this->screenGenerator->generate($modelData, ['screen_type' => $screenType]);
                $fileName = $this->getScreenFileName($modelData['class_name'], $screenType);
                $outputPath = $this->screenGenerator->getOutputPath($fileName);
                $success = $this->writeFile($outputPath, $screenCode, $overwrite);

                $results[] = [
                    'type' => "Screen ({$screenType})",
                    'file' => $outputPath,
                    'success' => $success,
                    'error' => $success ? null : 'Failed to write file',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'type' => "Screen ({$screenType})",
                    'file' => 'N/A',
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get widget file name.
     *
     * @param string $className
     * @param string $widgetType
     * @return string
     */
    protected function getWidgetFileName(string $className, string $widgetType): string
    {
        $baseName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        return "{$baseName}_{$widgetType}";
    }

    /**
     * Get screen file name.
     *
     * @param string $className
     * @param string $screenType
     * @return string
     */
    protected function getScreenFileName(string $className, string $screenType): string
    {
        $baseName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        return "{$baseName}_{$screenType}_screen";
    }
}
