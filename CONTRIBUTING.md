# Contributing to Laravel Flutter Generator

Thank you for considering contributing to the Laravel Flutter Generator package!

## Author

**BasharShaeb** - Original author and maintainer

## How to Contribute

### Reporting Issues

If you find a bug or have a feature request, please create an issue on GitHub with:

1. A clear description of the problem or feature
2. Steps to reproduce (for bugs)
3. Expected vs actual behavior
4. Your environment details (PHP version, Laravel version, etc.)

### Development Setup

1. Fork the repository
2. Clone your fork locally
3. Install dependencies:
   ```bash
   composer install
   ```
4. Run tests to ensure everything works:
   ```bash
   composer test
   ```

### Making Changes

1. Create a new branch for your feature/fix:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes following these guidelines:
   - Follow PSR-12 coding standards
   - Add tests for new functionality
   - Update documentation as needed
   - Ensure all tests pass

3. Run code quality checks:
   ```bash
   composer analyse
   composer test
   ```

4. Commit your changes with a descriptive message:
   ```bash
   git commit -m "Add feature: description of what you added"
   ```

5. Push to your fork and create a Pull Request

### Code Style

- Follow PSR-12 coding standards
- Use meaningful variable and method names
- Add PHPDoc comments for public methods
- Keep methods small and focused
- Follow SOLID principles

### Testing

- Write unit tests for new functionality
- Ensure all existing tests pass
- Aim for good test coverage
- Use descriptive test method names

### Documentation

- Update README.md if you add new features
- Add examples for new functionality
- Update configuration documentation
- Keep CHANGELOG.md updated

## Pull Request Process

1. Ensure your code follows the style guidelines
2. Add tests for new functionality
3. Update documentation
4. Ensure all tests pass
5. Create a clear PR description explaining your changes

## Code of Conduct

- Be respectful and inclusive
- Focus on constructive feedback
- Help others learn and grow
- Maintain a positive environment

## Questions?

If you have questions about contributing, feel free to:
- Open an issue for discussion
- Contact the maintainer: BasharShaeb

Thank you for contributing! 🚀
