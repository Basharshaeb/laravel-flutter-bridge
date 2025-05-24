<?php

/**
 * Model Analyzer for Laravel Flutter Generator
 *
 * @author BasharShaeb
 * @package LaravelFlutter\Generator\Analyzers
 */

namespace LaravelFlutter\Generator\Analyzers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use LaravelFlutter\Generator\Contracts\ModelAnalyzerInterface;
use ReflectionClass;
use ReflectionMethod;

class ModelAnalyzer implements ModelAnalyzerInterface
{
    /**
     * Analyze the given model.
     *
     * @param mixed $subject The model to analyze
     * @return array The analyzed data
     */
    public function analyze($subject): array
    {
        return $this->analyzeModel($subject);
    }

    /**
     * Check if the analyzer can handle the given subject.
     *
     * @param mixed $subject The subject to check
     * @return bool True if the analyzer can handle the subject
     */
    public function canAnalyze($subject): bool
    {
        if (is_string($subject)) {
            return class_exists($subject) && is_subclass_of($subject, Model::class);
        }

        return $subject instanceof Model;
    }

    /**
     * Analyze a Laravel Eloquent model.
     *
     * @param Model|string $model The model instance or class name
     * @return array The analyzed model data
     */
    public function analyzeModel($model): array
    {
        $modelClass = is_string($model) ? $model : get_class($model);
        $modelInstance = is_string($model) ? new $model : $model;

        return [
            'class_name' => class_basename($modelClass),
            'full_class_name' => $modelClass,
            'table_name' => $modelInstance->getTable(),
            'primary_key' => $modelInstance->getKeyName(),
            'attributes' => $this->getModelAttributes($model),
            'relationships' => $this->getModelRelationships($model),
            'validation_rules' => $this->getValidationRules($model),
            'fillable' => $modelInstance->getFillable(),
            'guarded' => $modelInstance->getGuarded(),
            'hidden' => $modelInstance->getHidden(),
            'casts' => $modelInstance->getCasts(),
            'dates' => $this->getDateAttributes($modelInstance),
            'timestamps' => $modelInstance->timestamps,
            'soft_deletes' => $this->hasSoftDeletes($modelInstance),
        ];
    }

    /**
     * Get model attributes with their types.
     *
     * @param Model|string $model The model instance or class name
     * @return array The model attributes
     */
    public function getModelAttributes($model): array
    {
        $modelInstance = is_string($model) ? new $model : $model;
        $tableName = $modelInstance->getTable();

        if (!Schema::hasTable($tableName)) {
            return [];
        }

        $columns = Schema::getColumnListing($tableName);
        $attributes = [];

        foreach ($columns as $column) {
            $columnType = Schema::getColumnType($tableName, $column);
            $attributes[$column] = [
                'name' => $column,
                'type' => $this->mapDatabaseTypeToDartType($columnType),
                'database_type' => $columnType,
                'nullable' => $this->isColumnNullable($tableName, $column),
                'default' => $this->getColumnDefault($tableName, $column),
            ];
        }

        return $attributes;
    }

    /**
     * Get model relationships.
     *
     * @param Model|string $model The model instance or class name
     * @return array The model relationships
     */
    public function getModelRelationships($model): array
    {
        $modelClass = is_string($model) ? $model : get_class($model);
        $reflection = new ReflectionClass($modelClass);
        $relationships = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== $modelClass ||
                $method->getNumberOfParameters() > 0 ||
                $method->isStatic()) {
                continue;
            }

            try {
                $modelInstance = is_string($model) ? new $model : $model;
                $return = $method->invoke($modelInstance);

                if ($return instanceof Relation) {
                    $relationships[$method->getName()] = [
                        'name' => $method->getName(),
                        'type' => class_basename(get_class($return)),
                        'related_model' => get_class($return->getRelated()),
                        'foreign_key' => $this->getForeignKey($return),
                        'local_key' => $this->getLocalKey($return),
                    ];
                }
            } catch (\Throwable $e) {
                // Skip methods that throw exceptions
                continue;
            }
        }

        return $relationships;
    }

    /**
     * Get model validation rules if available.
     *
     * @param Model|string $model The model instance or class name
     * @return array The validation rules
     */
    public function getValidationRules($model): array
    {
        $modelClass = is_string($model) ? $model : get_class($model);

        // Check if model has rules property or method
        if (property_exists($modelClass, 'rules')) {
            return $modelClass::$rules ?? [];
        }

        if (method_exists($modelClass, 'rules')) {
            $modelInstance = is_string($model) ? new $model : $model;
            return $modelInstance->rules() ?? [];
        }

        return [];
    }

    /**
     * Map database type to Dart type.
     *
     * @param string $databaseType The database type
     * @return string The corresponding Dart type
     */
    private function mapDatabaseTypeToDartType(string $databaseType): string
    {
        return match (strtolower($databaseType)) {
            'integer', 'bigint', 'smallint', 'tinyint' => 'int',
            'decimal', 'float', 'double', 'real' => 'double',
            'boolean' => 'bool',
            'date', 'datetime', 'timestamp' => 'DateTime',
            'json', 'jsonb' => 'Map<String, dynamic>',
            default => 'String',
        };
    }

    /**
     * Check if column is nullable.
     *
     * @param string $tableName The table name
     * @param string $columnName The column name
     * @return bool True if nullable
     */
    private function isColumnNullable(string $tableName, string $columnName): bool
    {
        $columns = Schema::getConnection()->getDoctrineSchemaManager()
            ->listTableColumns($tableName);

        return !($columns[$columnName]->getNotnull() ?? true);
    }

    /**
     * Get column default value.
     *
     * @param string $tableName The table name
     * @param string $columnName The column name
     * @return mixed The default value
     */
    private function getColumnDefault(string $tableName, string $columnName)
    {
        $columns = Schema::getConnection()->getDoctrineSchemaManager()
            ->listTableColumns($tableName);

        return $columns[$columnName]->getDefault();
    }

    /**
     * Get date attributes from model.
     *
     * @param Model $model The model instance
     * @return array The date attributes
     */
    private function getDateAttributes(Model $model): array
    {
        return $model->getDates();
    }

    /**
     * Check if model uses soft deletes.
     *
     * @param Model $model The model instance
     * @return bool True if uses soft deletes
     */
    private function hasSoftDeletes(Model $model): bool
    {
        return in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model));
    }

    /**
     * Get foreign key from relationship.
     *
     * @param Relation $relation The relationship
     * @return string|null The foreign key
     */
    private function getForeignKey(Relation $relation): ?string
    {
        if (method_exists($relation, 'getForeignKeyName')) {
            return $relation->getForeignKeyName();
        }

        if (method_exists($relation, 'getForeignKey')) {
            return $relation->getForeignKey();
        }

        return null;
    }

    /**
     * Get local key from relationship.
     *
     * @param Relation $relation The relationship
     * @return string|null The local key
     */
    private function getLocalKey(Relation $relation): ?string
    {
        if (method_exists($relation, 'getLocalKeyName')) {
            return $relation->getLocalKeyName();
        }

        if (method_exists($relation, 'getOwnerKeyName')) {
            return $relation->getOwnerKeyName();
        }

        return null;
    }
}
