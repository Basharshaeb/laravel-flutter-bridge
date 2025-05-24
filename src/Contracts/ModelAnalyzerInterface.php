<?php

namespace LaravelFlutter\Generator\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ModelAnalyzerInterface extends AnalyzerInterface
{
    /**
     * Analyze a Laravel Eloquent model.
     *
     * @param Model|string $model The model instance or class name
     * @return array The analyzed model data
     */
    public function analyzeModel($model): array;

    /**
     * Get model attributes with their types.
     *
     * @param Model|string $model The model instance or class name
     * @return array The model attributes
     */
    public function getModelAttributes($model): array;

    /**
     * Get model relationships.
     *
     * @param Model|string $model The model instance or class name
     * @return array The model relationships
     */
    public function getModelRelationships($model): array;

    /**
     * Get model validation rules if available.
     *
     * @param Model|string $model The model instance or class name
     * @return array The validation rules
     */
    public function getValidationRules($model): array;
}
