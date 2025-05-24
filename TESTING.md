# Testing Guide for Laravel Flutter Generator

This guide explains how to test the Laravel Flutter Generator package thoroughly.

## 🧪 Testing Overview

The package includes multiple testing approaches:
- **Unit Tests**: Test individual components in isolation
- **Feature Tests**: Test complete workflows and integrations
- **Manual Testing**: Test with real Laravel applications
- **Generated Code Testing**: Verify the quality of generated Flutter code

## 🚀 Quick Start Testing

### 1. Run All Tests
```bash
# Install dependencies
composer install

# Run the complete test suite
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer analyse
```

### 2. Run Specific Test Types
```bash
# Unit tests only
vendor/bin/phpunit tests/Unit

# Feature tests only
vendor/bin/phpunit tests/Feature

# Specific test file
vendor/bin/phpunit tests/Unit/ModelAnalyzerTest.php

# Specific test method
vendor/bin/phpunit --filter test_can_analyze_returns_true_for_eloquent_model
```

## 🔧 Setting Up Test Environment

### 1. Create a Test Laravel Application

```bash
# Create a new Laravel app for testing
composer create-project laravel/laravel test-app
cd test-app

# Add the package locally for testing
composer config repositories.local path ../laravel-flutter-generator-package
composer require laravel-flutter/generator @dev
```

### 2. Create Test Models

Create sample models in your test Laravel app:

```php
// app/Models/User.php (extend the default)
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'age',
        'is_active',
        'bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'age' => 'integer',
    ];

    // Relationships for testing
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}
```

```php
// app/Models/Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'published_at',
        'user_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### 3. Create Migrations

```bash
# Create migrations for test models
php artisan make:migration create_posts_table
php artisan make:migration create_profiles_table
```

```php
// database/migrations/xxxx_create_posts_table.php
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->timestamp('published_at')->nullable();
        $table->foreignId('user_id')->constrained();
        $table->timestamps();
    });
}
```

## 📋 Manual Testing Checklist

### 1. Test Model Generation

```bash
# Test single model generation
php artisan flutter:generate-model User

# Test with --force flag
php artisan flutter:generate-model User --force

# Test all models
php artisan flutter:generate-model --all

# Verify generated files
ls flutter_output/models/
cat flutter_output/models/user.dart
```

**Expected Results:**
- ✅ Dart file created in correct location
- ✅ Proper class name and properties
- ✅ Null safety syntax
- ✅ JSON serialization methods
- ✅ copyWith, toString, equality methods

### 2. Test Service Generation

```bash
# Test service generation
php artisan flutter:generate-service User

# Test with route analysis
php artisan flutter:generate-service User --with-routes

# Test all services
php artisan flutter:generate-service --all

# Verify generated files
cat flutter_output/services/user_service.dart
cat flutter_output/services/api_service.dart
```

**Expected Results:**
- ✅ Service class with CRUD methods
- ✅ Base API service created
- ✅ Proper error handling
- ✅ Type-safe method signatures

### 3. Test Feature Generation

```bash
# Test complete feature generation
php artisan flutter:generate-feature User

# Test with skip options
php artisan flutter:generate-feature Post --skip-widgets

# Verify all generated files
find flutter_output -name "*user*" -type f
```

**Expected Results:**
- ✅ Model, service, widgets, and screens generated
- ✅ Proper imports between files
- ✅ Consistent naming conventions

### 4. Test Complete Generation

```bash
# Test generating everything
php artisan flutter:generate-all

# Test with specific models
php artisan flutter:generate-all --models=User,Post

# Test with force flag
php artisan flutter:generate-all --force

# Verify output structure
tree flutter_output/
```

**Expected Results:**
- ✅ Complete Flutter app structure
- ✅ All models processed
- ✅ Progress tracking works
- ✅ Summary report displayed

## 🔍 Testing Generated Flutter Code

### 1. Create a Test Flutter Project

```bash
# Create Flutter project for testing generated code
flutter create test_flutter_app
cd test_flutter_app

# Copy generated files
cp -r ../test-app/flutter_output/* lib/
```

### 2. Add Required Dependencies

```yaml
# pubspec.yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.1.0
  json_annotation: ^4.8.1
  provider: ^6.1.1

dev_dependencies:
  flutter_test:
    sdk: flutter
  build_runner: ^2.4.7
  json_serializable: ^6.7.1
```

### 3. Test Generated Code

```bash
# Install dependencies
flutter pub get

# Generate JSON serialization code
flutter packages pub run build_runner build

# Run Flutter analyzer
flutter analyze

# Run Flutter tests
flutter test

# Try to build the app
flutter build apk --debug
```

**Expected Results:**
- ✅ No compilation errors
- ✅ JSON serialization works
- ✅ API services compile correctly
- ✅ Widgets render properly

## 🧪 Unit Testing Examples

### Test Model Analyzer

```php
// tests/Unit/ModelAnalyzerTest.php
public function test_analyzes_model_attributes_correctly()
{
    $analyzer = new ModelAnalyzer();
    $result = $analyzer->analyzeModel(TestUser::class);
    
    $this->assertArrayHasKey('attributes', $result);
    $this->assertArrayHasKey('name', $result['attributes']);
    $this->assertEquals('String', $result['attributes']['name']['type']);
    $this->assertFalse($result['attributes']['name']['nullable']);
}

public function test_handles_relationships()
{
    $analyzer = new ModelAnalyzer();
    $result = $analyzer->analyzeModel(TestUser::class);
    
    $this->assertArrayHasKey('relationships', $result);
    // Add assertions for specific relationships
}
```

### Test Code Generation

```php
// tests/Unit/DartModelGeneratorTest.php
public function test_generates_valid_dart_syntax()
{
    $generator = new DartModelGenerator();
    $modelData = [
        'class_name' => 'User',
        'attributes' => [
            'id' => ['name' => 'id', 'type' => 'int', 'nullable' => false],
            'name' => ['name' => 'name', 'type' => 'String', 'nullable' => false],
        ],
        'relationships' => [],
    ];
    
    $result = $generator->generate($modelData);
    
    $this->assertStringContainsString('class User {', $result);
    $this->assertStringContainsString('final int id;', $result);
    $this->assertStringContainsString('factory User.fromJson', $result);
}
```

## 🔧 Debugging and Troubleshooting

### Common Issues and Solutions

1. **Database Connection Issues**
   ```bash
   # Ensure test database is configured
   php artisan migrate --env=testing
   ```

2. **Permission Issues**
   ```bash
   # Fix output directory permissions
   chmod -R 755 flutter_output/
   ```

3. **Memory Issues**
   ```bash
   # Increase PHP memory limit
   php -d memory_limit=512M artisan flutter:generate-all
   ```

### Debug Mode

Add debug output to commands:

```php
// In command classes, add:
$this->info('Debug: Processing model ' . $modelClass);
$this->line('Generated code length: ' . strlen($generatedCode));
```

### Verbose Testing

```bash
# Run tests with verbose output
vendor/bin/phpunit --verbose

# Run with debug information
vendor/bin/phpunit --debug
```

## 📊 Performance Testing

### Test Large Models

```bash
# Create models with many attributes
php artisan make:model LargeModel

# Test generation performance
time php artisan flutter:generate-model LargeModel
```

### Memory Usage Testing

```bash
# Monitor memory usage during generation
php -d memory_limit=128M artisan flutter:generate-all
```

## ✅ Test Checklist

Before releasing, ensure all these tests pass:

### Package Tests
- [ ] All unit tests pass
- [ ] All feature tests pass
- [ ] Static analysis passes (PHPStan level 8)
- [ ] Code coverage > 80%

### Integration Tests
- [ ] Package installs correctly in fresh Laravel app
- [ ] All commands work without errors
- [ ] Configuration publishing works
- [ ] Template publishing works

### Generated Code Tests
- [ ] Generated Dart code compiles
- [ ] JSON serialization works
- [ ] API services make valid HTTP requests
- [ ] UI widgets render correctly
- [ ] No Flutter analyzer warnings

### Edge Cases
- [ ] Empty models handled gracefully
- [ ] Models with no relationships work
- [ ] Models with complex relationships work
- [ ] Large models (50+ attributes) work
- [ ] Models with unusual attribute types work

## 🚀 Continuous Integration

For automated testing, create a GitHub Actions workflow:

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.1
        
    - name: Install dependencies
      run: composer install
      
    - name: Run tests
      run: composer test
      
    - name: Run static analysis
      run: composer analyse
```

This comprehensive testing approach ensures the package works reliably across different scenarios and environments! 🎯
