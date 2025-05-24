<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Output Configuration
    |--------------------------------------------------------------------------
    |
    | Configure where the generated Flutter files should be saved.
    |
    */
    'output' => [
        'base_path' => sys_get_temp_dir() . '/flutter_test_output',
        'models_path' => 'models',
        'services_path' => 'services',
        'widgets_path' => 'widgets',
        'screens_path' => 'screens',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the API settings for generated services.
    |
    */
    'api' => [
        'base_url' => env('FLUTTER_API_BASE_URL', 'http://localhost:8000/api'),
        'timeout' => 30,
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
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
        'use_json_annotation' => true,
        'use_equatable' => false,
        'use_freezed' => false,
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
        'use_snake_case' => true,
        'file_suffix' => '',
        'class_prefix' => '',
        'class_suffix' => '',
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
        'include_scopes' => false,
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
        'theme' => 'material',
        'responsive' => true,
        'generate_forms' => true,
        'generate_lists' => true,
        'generate_cards' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    | Configure template settings.
    |
    */
    'templates' => [
        'path' => __DIR__ . '/../../src/Templates',
        'extension' => '.dart.stub',
        'custom_templates' => [],
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
        'Illuminate\Notifications\DatabaseNotification',
    ],
];
