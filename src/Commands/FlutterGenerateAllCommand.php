<?php

/**
 * Flutter Generate All Command for Laravel Flutter Bridge
 *
 * @author BasharShaeb
 * @package LaravelFlutter\Generator\Commands
 */

namespace LaravelFlutter\Generator\Commands;

use LaravelFlutter\Generator\Generators\DartModelGenerator;
use LaravelFlutter\Generator\Generators\ApiServiceGenerator;
use LaravelFlutter\Generator\Generators\WidgetGenerator;
use LaravelFlutter\Generator\Generators\ScreenGenerator;

class FlutterGenerateAllCommand extends BaseFlutterCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flutter:generate-all
                            {--force : Overwrite existing files}
                            {--skip-model : Skip model generation}
                            {--skip-service : Skip service generation}
                            {--skip-widgets : Skip widget generation}
                            {--skip-screens : Skip screen generation}
                            {--models=* : Specific models to generate (comma-separated)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate complete Flutter application code from all Laravel models';

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
        $this->info('🚀 Flutter Complete Application Generator');
        $this->info('==========================================');

        // Ensure base API service exists
        $this->ensureBaseApiServiceExists();

        try {
            $models = $this->getModelsToGenerate();

            if (empty($models)) {
                $this->warn('No models found to generate.');
                return self::SUCCESS;
            }

            $this->displayGenerationPlan($models);

            if (!$this->confirm('Do you want to continue with the generation?')) {
                $this->info('Generation cancelled.');
                return self::SUCCESS;
            }

            $allResults = $this->generateAllFeatures($models);

            $this->displayFinalSummary($allResults);

            return $this->determineExitCode($allResults);

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Get models to generate.
     *
     * @return array
     */
    protected function getModelsToGenerate(): array
    {
        $specificModels = $this->option('models');

        if (!empty($specificModels)) {
            return $this->validateSpecificModels($specificModels);
        }

        $models = $this->getAvailableModels();
        return $this->filterExcludedModels($models);
    }

    /**
     * Validate specific models provided via option.
     *
     * @param array $modelNames
     * @return array
     */
    protected function validateSpecificModels(array $modelNames): array
    {
        $validModels = [];

        foreach ($modelNames as $modelName) {
            try {
                $modelClass = $this->validateAndGetModelClass($modelName);

                if (!$this->isModelExcluded($modelClass)) {
                    $validModels[] = $modelClass;
                } else {
                    $this->warn("Model '{$modelName}' is excluded from generation.");
                }
            } catch (\Exception $e) {
                $this->error("Invalid model '{$modelName}': " . $e->getMessage());
            }
        }

        return $validModels;
    }

    /**
     * Display generation plan.
     *
     * @param array $models
     * @return void
     */
    protected function displayGenerationPlan(array $models): void
    {
        $this->info("Generation Plan:");
        $this->info("================");
        $this->info("Models to process: " . count($models));

        foreach ($models as $model) {
            $this->line("  - " . class_basename($model));
        }

        $this->newLine();
        $this->info("Components to generate:");

        if (!$this->option('skip-model')) {
            $this->line("  ✓ Dart Models");
        }

        if (!$this->option('skip-service')) {
            $this->line("  ✓ API Services");
        }

        if (!$this->option('skip-widgets')) {
            $this->line("  ✓ UI Widgets (Form, List, Card)");
        }

        if (!$this->option('skip-screens')) {
            $this->line("  ✓ Screens (List, Detail, Create, Edit)");
        }

        $estimatedFiles = $this->calculateEstimatedFiles($models);
        $this->info("Estimated files to generate: {$estimatedFiles}");
        $this->newLine();
    }

    /**
     * Calculate estimated number of files.
     *
     * @param array $models
     * @return int
     */
    protected function calculateEstimatedFiles(array $models): int
    {
        $filesPerModel = 0;

        if (!$this->option('skip-model')) $filesPerModel += 1;      // Model
        if (!$this->option('skip-service')) $filesPerModel += 1;   // Service
        if (!$this->option('skip-widgets')) $filesPerModel += 3;   // 3 widgets
        if (!$this->option('skip-screens')) $filesPerModel += 4;   // 4 screens

        return count($models) * $filesPerModel + 1; // +1 for base API service
    }

    /**
     * Generate all features for all models.
     *
     * @param array $models
     * @return array
     */
    protected function generateAllFeatures(array $models): array
    {
        $allResults = [];
        $totalFiles = $this->calculateEstimatedFiles($models);

        $progressBar = $this->output->createProgressBar($totalFiles);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        foreach ($models as $modelClass) {
            $modelName = class_basename($modelClass);
            $this->line("\nProcessing: {$modelName}");

            $modelResults = $this->generateModelFeatures($modelClass, $progressBar);
            $allResults = array_merge($allResults, $modelResults);
        }

        $progressBar->finish();
        $this->newLine(2);

        return $allResults;
    }

    /**
     * Generate features for a single model.
     *
     * @param string $modelClass
     * @param \Symfony\Component\Console\Helper\ProgressBar $progressBar
     * @return array
     */
    protected function generateModelFeatures(string $modelClass, $progressBar): array
    {
        $results = [];
        $modelData = $this->modelAnalyzer->analyzeModel($modelClass);
        $overwrite = $this->option('force');

        // Generate model
        if (!$this->option('skip-model')) {
            $results[] = $this->generateModel($modelData, $overwrite);
            $progressBar->advance();
        }

        // Generate service
        if (!$this->option('skip-service')) {
            $results[] = $this->generateService($modelData, $overwrite);
            $progressBar->advance();
        }

        // Generate widgets
        if (!$this->option('skip-widgets')) {
            $widgetResults = $this->generateWidgets($modelData, $overwrite, $progressBar);
            $results = array_merge($results, $widgetResults);
        }

        // Generate screens
        if (!$this->option('skip-screens')) {
            $screenResults = $this->generateScreens($modelData, $overwrite, $progressBar);
            $results = array_merge($results, $screenResults);
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
                'model' => $modelData['class_name'],
                'type' => 'Model',
                'file' => $outputPath,
                'success' => $success,
                'error' => $success ? null : 'Failed to write file',
            ];
        } catch (\Exception $e) {
            return [
                'model' => $modelData['class_name'],
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
                'model' => $modelData['class_name'],
                'type' => 'Service',
                'file' => $outputPath,
                'success' => $success,
                'error' => $success ? null : 'Failed to write file',
            ];
        } catch (\Exception $e) {
            return [
                'model' => $modelData['class_name'],
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
     * @param \Symfony\Component\Console\Helper\ProgressBar $progressBar
     * @return array
     */
    protected function generateWidgets(array $modelData, bool $overwrite, $progressBar): array
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
                    'model' => $modelData['class_name'],
                    'type' => "Widget ({$widgetType})",
                    'file' => $outputPath,
                    'success' => $success,
                    'error' => $success ? null : 'Failed to write file',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'model' => $modelData['class_name'],
                    'type' => "Widget ({$widgetType})",
                    'file' => 'N/A',
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }

            $progressBar->advance();
        }

        return $results;
    }

    /**
     * Generate screen files.
     *
     * @param array $modelData
     * @param bool $overwrite
     * @param \Symfony\Component\Console\Helper\ProgressBar $progressBar
     * @return array
     */
    protected function generateScreens(array $modelData, bool $overwrite, $progressBar): array
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
                    'model' => $modelData['class_name'],
                    'type' => "Screen ({$screenType})",
                    'file' => $outputPath,
                    'success' => $success,
                    'error' => $success ? null : 'Failed to write file',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'model' => $modelData['class_name'],
                    'type' => "Screen ({$screenType})",
                    'file' => 'N/A',
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }

            $progressBar->advance();
        }

        return $results;
    }

    /**
     * Display final summary.
     *
     * @param array $allResults
     * @return void
     */
    protected function displayFinalSummary(array $allResults): void
    {
        $successful = array_filter($allResults, fn($result) => $result['success']);
        $failed = array_filter($allResults, fn($result) => !$result['success']);

        $this->info('🎉 Generation Complete!');
        $this->info('========================');
        $this->info('Total files processed: ' . count($allResults));
        $this->info('Successful: ' . count($successful));

        if (!empty($failed)) {
            $this->error('Failed: ' . count($failed));
            $this->newLine();
            $this->error('Failed files:');

            foreach ($failed as $failure) {
                $this->error("  - {$failure['model']} ({$failure['type']}): {$failure['error']}");
            }
        }

        // Group by model for summary
        $byModel = [];
        foreach ($allResults as $result) {
            $byModel[$result['model']][] = $result;
        }

        $this->newLine();
        $this->info('Generated files by model:');
        foreach ($byModel as $model => $results) {
            $modelSuccessful = array_filter($results, fn($r) => $r['success']);
            $this->line("  {$model}: " . count($modelSuccessful) . '/' . count($results) . ' files');
        }

        $outputPath = $this->getBaseOutputPath();
        $this->newLine();
        $this->info("All files generated in: {$outputPath}");
        $this->info('You can now integrate these files into your Flutter project!');
    }

    /**
     * Determine exit code based on results.
     *
     * @param array $allResults
     * @return int
     */
    protected function determineExitCode(array $allResults): int
    {
        $failed = array_filter($allResults, fn($result) => !$result['success']);

        return empty($failed) ? self::SUCCESS : self::FAILURE;
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
