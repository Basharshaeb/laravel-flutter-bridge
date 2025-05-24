<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Output Configuration
    |--------------------------------------------------------------------------
    |
    | Configure where the generated Flutter code should be saved.
    |
    */
    'output' => [
        'base_path' => base_path('flutter_output'),
        'models_path' => 'models',
        'services_path' => 'services',
        'widgets_path' => 'widgets',
        'screens_path' => 'screens',
        'utils_path' => 'utils',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API settings for generated Flutter services.
    |
    */
    'api' => [
        'base_url' => env('FLUTTER_API_BASE_URL', 'http://localhost:8000/api'),
        'timeout' => 30,
        'authentication' => [
            'type' => 'bearer', // bearer, basic, none
            'header_name' => 'Authorization',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Code Generation Settings
    |--------------------------------------------------------------------------
    |
    | Configure how the code should be generated.
    |
    */
    'generation' => [
        'architecture' => 'provider', // provider, bloc, riverpod
        'null_safety' => true,
        'use_freezed' => false,
        'use_json_annotation' => true,
        'generate_tests' => true,
        'generate_documentation' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Naming Conventions
    |--------------------------------------------------------------------------
    |
    | Configure naming conventions for generated code.
    |
    */
    'naming' => [
        'model_suffix' => '',
        'service_suffix' => 'Service',
        'widget_suffix' => 'Widget',
        'screen_suffix' => 'Screen',
        'use_snake_case' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Analysis
    |--------------------------------------------------------------------------
    |
    | Configure how Laravel models should be analyzed.
    |
    */
    'model_analysis' => [
        'include_relationships' => true,
        'include_accessors' => true,
        'include_mutators' => false,
        'include_timestamps' => true,
        'include_soft_deletes' => true,
        'excluded_attributes' => [
            'password',
            'remember_token',
            'email_verified_at',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Generation
    |--------------------------------------------------------------------------
    |
    | Configure UI component generation.
    |
    */
    'ui' => [
        'theme' => 'material', // material, cupertino
        'generate_forms' => true,
        'generate_lists' => true,
        'generate_detail_views' => true,
        'include_validation' => true,
        'responsive_design' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    | Configure template paths and settings.
    |
    */
    'templates' => [
        'path' => resource_path('views/flutter-generator'),
        'extension' => '.blade.php',
        'cache' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Models
    |--------------------------------------------------------------------------
    |
    | Models that should be excluded from generation.
    |
    */
    'excluded_models' => [
        'Illuminate\Foundation\Auth\User',
        'Laravel\Sanctum\PersonalAccessToken',
        'Spatie\Permission\Models\Role',
        'Spatie\Permission\Models\Permission',
    ],
];
