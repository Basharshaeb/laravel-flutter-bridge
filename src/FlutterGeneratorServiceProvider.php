<?php

/**
 * Laravel Flutter Generator Package
 *
 * @author BasharShaeb
 * @package BasharShaeb\LaravelFlutterGenerator
 * @version 1.0.0
 */

namespace BasharShaeb\LaravelFlutterGenerator;

use Illuminate\Support\ServiceProvider;
use BasharShaeb\LaravelFlutterGenerator\Commands\FlutterGenerateAllCommand;
use BasharShaeb\LaravelFlutterGenerator\Commands\FlutterGenerateFeatureCommand;
use BasharShaeb\LaravelFlutterGenerator\Commands\FlutterGenerateModelCommand;
use BasharShaeb\LaravelFlutterGenerator\Commands\FlutterGenerateServiceCommand;
use BasharShaeb\LaravelFlutterGenerator\Contracts\AnalyzerInterface;
use BasharShaeb\LaravelFlutterGenerator\Contracts\GeneratorInterface;
use BasharShaeb\LaravelFlutterGenerator\Analyzers\ModelAnalyzer;
use BasharShaeb\LaravelFlutterGenerator\Analyzers\RouteAnalyzer;
use BasharShaeb\LaravelFlutterGenerator\Generators\DartModelGenerator;
use BasharShaeb\LaravelFlutterGenerator\Generators\ApiServiceGenerator;
use BasharShaeb\LaravelFlutterGenerator\Generators\WidgetGenerator;
use BasharShaeb\LaravelFlutterGenerator\Generators\ScreenGenerator;

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
