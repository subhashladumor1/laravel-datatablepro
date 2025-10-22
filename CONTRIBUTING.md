# Contributing to Laravel DataTablePro

Thank you for considering contributing to Laravel DataTablePro! This document outlines the process for contributing to this project.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with:

1. **Clear title and description**
2. **Steps to reproduce** the issue
3. **Expected behavior** vs **actual behavior**
4. **Environment details** (PHP version, Laravel version, OS)
5. **Code samples** if applicable

### Suggesting Features

Feature suggestions are welcome! Please:

1. Check if the feature has already been requested
2. Provide a clear use case
3. Explain why this feature would benefit users
4. Include code examples if possible

### Pull Requests

1. **Fork the repository** and create your branch from `develop`
2. **Write tests** for your changes
3. **Ensure tests pass**: `composer test`
4. **Follow code style**: `composer cs-fix`
5. **Run static analysis**: `composer static`
6. **Update documentation** if needed
7. **Submit a pull request** with a clear description

### Development Setup

```bash
# Clone your fork
git clone https://github.com/YOUR-USERNAME/laravel-datatablepro.git
cd laravel-datatablepro

# Install dependencies
composer install
npm install

# Build assets
npm run build

# Run tests
composer test

# Check code style
composer cs-check

# Fix code style
composer cs-fix

# Run static analysis
composer static
```

## Coding Standards

### PHP

- Follow **PSR-12** coding standard
- Use **strict types**: `declare(strict_types=1);`
- Add **PHPDoc blocks** for all methods
- Use **type hints** for all parameters and return types
- Write **meaningful variable names**

### JavaScript

- Use **ES6+** syntax
- Follow **consistent formatting**
- Add **JSDoc comments** for complex functions
- Avoid **jQuery** or other dependencies (keep it vanilla)

### Testing

- Write **unit tests** for new functionality
- Write **feature tests** for end-to-end scenarios
- Aim for **high code coverage**
- Use **descriptive test names**: `it_can_do_something()`

### Documentation

- Update **README.md** for new features
- Add examples to **docs/examples.md**
- Update **docs/api.md** for API changes
- Keep **CHANGELOG.md** updated

## Commit Messages

Use clear, descriptive commit messages:

```
feat: add virtual scrolling support
fix: resolve relationship ordering issue
docs: update export examples
test: add tests for filter callbacks
refactor: extract response transformer logic
```

Prefixes:
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation
- `test:` - Tests
- `refactor:` - Code refactoring
- `perf:` - Performance improvement
- `chore:` - Maintenance

## Branch Naming

- `feature/description` - New features
- `fix/description` - Bug fixes
- `docs/description` - Documentation updates
- `refactor/description` - Code refactoring

## Release Process

1. Update version in `composer.json`
2. Update `CHANGELOG.md`
3. Create release tag
4. Publish to Packagist

## Questions?

If you have questions, feel free to:

- Open a GitHub issue
- Start a discussion
- Contact maintainers

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
