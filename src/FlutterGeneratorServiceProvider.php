<?php

/**
 * Laravel Flutter Bridge Package
 *
 * @author BasharShaeb
 * @package LaravelFlutter\Generator
 * @version 1.0.0
 */

namespace LaravelFlutter\Generator;

use Illuminate\Support\ServiceProvider;
use LaravelFlutter\Generator\Commands\FlutterGenerateAllCommand;
use LaravelFlutter\Generator\Commands\FlutterGenerateFeatureCommand;
use LaravelFlutter\Generator\Commands\FlutterGenerateModelCommand;
use LaravelFlutter\Generator\Commands\FlutterGenerateServiceCommand;
use LaravelFlutter\Generator\Contracts\AnalyzerInterface;
use LaravelFlutter\Generator\Contracts\GeneratorInterface;
use LaravelFlutter\Generator\Analyzers\ModelAnalyzer;
use LaravelFlutter\Generator\Analyzers\RouteAnalyzer;
use LaravelFlutter\Generator\Generators\DartModelGenerator;
use LaravelFlutter\Generator\Generators\ApiServiceGenerator;
use LaravelFlutter\Generator\Generators\WidgetGenerator;
use LaravelFlutter\Generator\Generators\ScreenGenerator;

class FlutterGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/flutter-generator.php',
            'flutter-generator'
        );

        // Register analyzers
        $this->app->bind('flutter.model.analyzer', ModelAnalyzer::class);
        $this->app->bind('flutter.route.analyzer', RouteAnalyzer::class);

        // Register generators
        $this->app->bind('flutter.dart.model.generator', DartModelGenerator::class);
        $this->app->bind('flutter.api.service.generator', ApiServiceGenerator::class);
        $this->app->bind('flutter.widget.generator', WidgetGenerator::class);
        $this->app->bind('flutter.screen.generator', ScreenGenerator::class);

        // Register commands
        $this->commands([
            FlutterGenerateModelCommand::class,
            FlutterGenerateServiceCommand::class,
            FlutterGenerateFeatureCommand::class,
            FlutterGenerateAllCommand::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/flutter-generator.php' => config_path('flutter-generator.php'),
        ], 'flutter-generator-config');

        // Publish templates
        $this->publishes([
            __DIR__ . '/Templates' => resource_path('views/flutter-generator'),
        ], 'flutter-generator-templates');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'flutter.model.analyzer',
            'flutter.route.analyzer',
            'flutter.dart.model.generator',
            'flutter.api.service.generator',
            'flutter.widget.generator',
            'flutter.screen.generator',
            FlutterGenerateModelCommand::class,
            FlutterGenerateServiceCommand::class,
            FlutterGenerateFeatureCommand::class,
            FlutterGenerateAllCommand::class,
        ];
    }
}
