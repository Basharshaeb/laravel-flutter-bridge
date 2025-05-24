# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

**Package**: Laravel Flutter Bridge
**Author**: BasharShaeb

## [Unreleased]

### Added
- Initial release of Laravel Flutter Bridge package
- Complete code generation for Flutter applications from Laravel models
- Support for generating Dart models with null safety
- API service generation with full CRUD operations
- UI widget generation (forms, lists, cards)
- Screen generation (list, detail, create, edit)
- Comprehensive configuration system
- Route analysis for custom API methods
- SOLID principles implementation
- Extensive test coverage
- Template customization support
- Multiple architectural patterns support (Provider, Bloc, Riverpod)

### Features
- **Model Analysis**: Deep analysis of Laravel Eloquent models including attributes, relationships, validation rules
- **Code Generation**: Automatic generation of type-safe Dart code
- **API Integration**: Complete HTTP client with authentication support
- **UI Components**: Ready-to-use Flutter widgets and screens
- **Customization**: Configurable templates and generation options
- **Testing**: Comprehensive unit and feature tests
- **Documentation**: Extensive documentation and examples

### Commands
- `flutter:generate-model` - Generate Dart models from Laravel models
- `flutter:generate-service` - Generate API service classes
- `flutter:generate-feature` - Generate complete features (model + service + UI)
- `flutter:generate-all` - Generate everything for all models

### Configuration Options
- Output path customization
- API configuration (base URL, authentication, timeout)
- Code generation settings (architecture, null safety, JSON annotation)
- Model analysis options (relationships, excluded attributes)
- UI generation preferences (theme, responsive design)
- Template customization

### Supported Features
- Laravel 10.0+ and 11.0+ compatibility
- PHP 8.1+ support
- Flutter 3.0+ compatibility
- Null safety support
- JSON serialization with json_annotation
- Relationship handling
- Validation rule extraction
- Custom route method generation
- Error handling and logging
- Progress tracking for bulk operations

## [1.0.0] - 2025-05-23

### Added
- Initial stable release
- Production-ready code generation
- Complete documentation
- Full test suite
- Example projects

### Security
- Input validation for all generators
- Safe file writing with permission checks
- Sanitized template rendering

## [0.1.0] - 2025-05-24

### Added
- Initial development version
- Basic model and service generation
- Core architecture implementation
