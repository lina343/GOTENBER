# Contributing to Gotenberg PHP PDF Converter

Thank you for your interest in contributing! This document provides guidelines for contributing to this project.

## 🚀 Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/YOUR_USERNAME/gotenberg-php-pdf-converter.git
   cd gotenberg-php-pdf-converter
   ```
3. **Set up the development environment**:
   ```bash
   cp .env.example .env
   docker-compose up -d
   docker-compose exec php composer install
   ```

## 🧪 Development Workflow

### Running Tests
```bash
# Run all tests
docker-compose exec php composer test

# Run with coverage
docker-compose exec php composer test-coverage

# Run static analysis
docker-compose exec php composer phpstan

# Check code style
docker-compose exec php composer cs-check
```

### Code Quality
- Follow **PSR-12** coding standards
- Write **PHPDoc** comments for all public methods
- Add **unit tests** for new features
- Ensure **type declarations** are used
- Run **PHPStan** at level 8

### Making Changes

1. **Create a feature branch**:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes** following the coding standards

3. **Add tests** for new functionality

4. **Run the quality checks**:
   ```bash
   docker-compose exec php composer quality
   ```

5. **Commit your changes**:
   ```bash
   git add .
   git commit -m "Add: your feature description"
   ```

6. **Push to your fork**:
   ```bash
   git push origin feature/your-feature-name
   ```

7. **Create a Pull Request** on GitHub

## 📝 Commit Message Format

Use clear, descriptive commit messages:
- `Add: new feature description`
- `Fix: bug description`
- `Update: improvement description`
- `Docs: documentation changes`
- `Test: test-related changes`

## 🐛 Reporting Issues

When reporting issues, please include:
- **Environment details** (PHP version, Docker version, OS)
- **Steps to reproduce** the issue
- **Expected vs actual behavior**
- **Error messages** or logs
- **Screenshots** if applicable

## 💡 Feature Requests

We welcome feature requests! Please:
- **Check existing issues** first
- **Describe the use case** clearly
- **Explain the expected behavior**
- **Consider implementation complexity**

## 📚 Documentation

- Update **README.md** for new features
- Add **inline comments** for complex logic
- Update **API documentation** if applicable
- Include **usage examples**

## 🤝 Code of Conduct

- Be respectful and inclusive
- Focus on constructive feedback
- Help others learn and grow
- Maintain a positive environment

Thank you for contributing! 🎉
