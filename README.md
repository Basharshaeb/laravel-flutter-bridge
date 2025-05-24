# Laravel Flutter Bridge

A comprehensive Laravel package that bridges your Laravel backend with Flutter frontend by automatically generating complete Flutter features (models, services, UI components) from your existing Laravel Eloquent models and routes.

## Features

- 🚀 **Automatic Code Generation**: Generate complete Flutter features from Laravel models
- 📱 **UI Components**: Create forms, lists, and detail views automatically
- 🔗 **API Integration**: Generate Flutter services that integrate with Laravel API endpoints
- 🏗️ **SOLID Principles**: Clean, maintainable code following best practices
- 🎨 **Customizable Templates**: Modify generation templates to fit your needs
- 🧪 **Comprehensive Testing**: Full test coverage for reliable code generation
- 🔄 **CRUD Operations**: Complete Create, Read, Update, Delete functionality
- 🎯 **Type Safety**: Full Dart null safety support
- 📊 **Relationship Support**: Handle Eloquent model relationships
- ⚡ **Performance Optimized**: Efficient code generation with minimal dependencies

## Installation

Install the package via Composer:

```bash
composer require bashar-shaeb/laravel-flutter-bridge
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="LaravelFlutter\Generator\FlutterGeneratorServiceProvider" --tag="flutter-generator-config"
```

Optionally, publish the templates for customization:

```bash
php artisan vendor:publish --provider="LaravelFlutter\Generator\FlutterGeneratorServiceProvider" --tag="flutter-generator-templates"
```

## Quick Start

### 1. Generate a Dart model from Laravel model:
```bash
php artisan flutter:generate-model User
```

### 2. Generate API service for a model:
```bash
php artisan flutter:generate-service User --with-routes
```

### 3. Generate complete feature (model + service + UI):
```bash
php artisan flutter:generate-feature User
```

### 4. Generate everything for all models:
```bash
php artisan flutter:generate-all
```

### 5. Generate for specific models only:
```bash
php artisan flutter:generate-all --models=User,Post,Category
```

## Command Options

### Global Options
- `--force`: Overwrite existing files without confirmation
- `--all`: Process all available models

### Feature-specific Options
- `--skip-model`: Skip model generation
- `--skip-service`: Skip service generation
- `--skip-widgets`: Skip widget generation
- `--skip-screens`: Skip screen generation
- `--with-routes`: Include route analysis for custom API methods

## Configuration

The package configuration file is published to `config/flutter-generator.php`. Key configuration options:

### Output Paths
```php
'output' => [
    'base_path' => base_path('flutter_output'),
    'models_path' => 'models',
    'services_path' => 'services',
    'widgets_path' => 'widgets',
    'screens_path' => 'screens',
],
```

### API Configuration
```php
'api' => [
    'base_url' => env('FLUTTER_API_BASE_URL', 'http://localhost:8000/api'),
    'timeout' => 30,
    'authentication' => [
        'type' => 'bearer', // bearer, basic, none
        'header_name' => 'Authorization',
    ],
],
```

### Code Generation Settings
```php
'generation' => [
    'architecture' => 'provider', // provider, bloc, riverpod
    'null_safety' => true,
    'use_freezed' => false,
    'use_json_annotation' => true,
    'generate_tests' => true,
    'generate_documentation' => true,
],
```

### Model Analysis
```php
'model_analysis' => [
    'include_relationships' => true,
    'include_accessors' => true,
    'include_timestamps' => true,
    'excluded_attributes' => [
        'password',
        'remember_token',
        'email_verified_at',
    ],
],
```

## Generated Code Structure

```
flutter_output/
├── models/
│   ├── user.dart
│   ├── user.g.dart (if using json_annotation)
│   └── post.dart
├── services/
│   ├── api_service.dart (base HTTP client)
│   ├── user_service.dart
│   └── post_service.dart
├── widgets/
│   ├── user_form.dart
│   ├── user_list.dart
│   ├── user_card.dart
│   └── post_form.dart
└── screens/
    ├── user_list_screen.dart
    ├── user_detail_screen.dart
    ├── user_create_screen.dart
    └── user_edit_screen.dart
```

## Example Generated Code

### Dart Model
```dart
import 'package:json_annotation/json_annotation.dart';

part 'user.g.dart';

@JsonSerializable()
class User {
  final int id;
  final String name;
  final String email;
  final DateTime? emailVerifiedAt;
  final bool isActive;

  const User({
    required this.id,
    required this.name,
    required this.email,
    this.emailVerifiedAt,
    required this.isActive,
  });

  factory User.fromJson(Map<String, dynamic> json) => _$UserFromJson(json);
  Map<String, dynamic> toJson() => _$UserToJson(this);

  User copyWith({
    int? id,
    String? name,
    String? email,
    DateTime? emailVerifiedAt,
    bool? isActive,
  }) {
    return User(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      emailVerifiedAt: emailVerifiedAt ?? this.emailVerifiedAt,
      isActive: isActive ?? this.isActive,
    );
  }
}
```

### API Service
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/user.dart';
import 'api_service.dart';

class UserService {
  final ApiService _apiService;
  final String _endpoint;

  UserService(this._apiService, this._endpoint);

  Future<List<User>> getAll({int? page, Map<String, dynamic>? filters}) async {
    try {
      final queryParams = <String, String>{};
      if (page != null) queryParams['page'] = page.toString();

      final response = await _apiService.get(_endpoint, queryParams: queryParams);
      final List<dynamic> data = response['data'] ?? response;
      return data.map((json) => User.fromJson(json)).toList();
    } catch (e) {
      throw Exception('Failed to fetch User list: $e');
    }
  }

  Future<User> getById(int id) async {
    try {
      final response = await _apiService.get('$_endpoint/$id');
      final data = response['data'] ?? response;
      return User.fromJson(data);
    } catch (e) {
      throw Exception('Failed to fetch User with ID $id: $e');
    }
  }

  Future<User> create(Map<String, dynamic> data) async {
    try {
      final response = await _apiService.post(_endpoint, data);
      final responseData = response['data'] ?? response;
      return User.fromJson(responseData);
    } catch (e) {
      throw Exception('Failed to create User: $e');
    }
  }

  Future<User> update(int id, Map<String, dynamic> data) async {
    try {
      final response = await _apiService.put('$_endpoint/$id', data);
      final responseData = response['data'] ?? response;
      return User.fromJson(responseData);
    } catch (e) {
      throw Exception('Failed to update User with ID $id: $e');
    }
  }

  Future<bool> delete(int id) async {
    try {
      await _apiService.delete('$_endpoint/$id');
      return true;
    } catch (e) {
      throw Exception('Failed to delete User with ID $id: $e');
    }
  }
}
```

## Integration with Flutter Project

1. **Copy generated files** to your Flutter project:
```bash
cp -r flutter_output/* your_flutter_project/lib/
```

2. **Add dependencies** to your `pubspec.yaml`:
```yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.1.0
  json_annotation: ^4.8.1
  provider: ^6.1.1  # if using Provider architecture

dev_dependencies:
  build_runner: ^2.4.7
  json_serializable: ^6.7.1
```

3. **Run code generation** (if using json_annotation):
```bash
flutter packages pub run build_runner build
```

4. **Initialize API service** in your app:
```dart
void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      home: UserListScreen(),
    );
  }
}
```

## Advanced Usage

### Custom Templates

You can customize the generated code by modifying the published templates in `resources/views/flutter-generator/`.

### Excluding Models

Add models to exclude in your configuration:

```php
'excluded_models' => [
    'App\\Models\\InternalModel',
    'Spatie\\Permission\\Models\\Role',
],
```

### Route Integration

Use the `--with-routes` flag to analyze your API routes and generate additional service methods:

```bash
php artisan flutter:generate-service User --with-routes
```

This will analyze routes like:
- `GET /api/users/{user}/posts` → `getUserPosts(int userId)`
- `POST /api/users/{user}/activate` → `activateUser(int userId)`

## Testing

Run the package tests:

```bash
composer test
```

Run with coverage:

```bash
composer test-coverage
```

## Requirements

- PHP 8.1+
- Laravel 10.0+ or 11.0+
- Flutter 3.0+
- Doctrine DBAL 3.0+ (for database schema analysis)

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Author

**BasharShaeb** - [GitHub Profile](https://github.com/BasharShaeb)

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
