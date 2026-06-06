# Contributing to ZippyExt

Thank you for considering contributing to ZippyExt!

## Development Setup

1. Clone the repository:
```bash
git clone https://github.com/victor78/ZippyExt.git
cd ZippyExt
```

2. Install dependencies:
```bash
composer install
```

3. Install 7zip (required for 7zip adapter tests):
```bash
# Ubuntu/Debian
sudo apt-get install p7zip-full

# macOS
brew install sevenzip
```

4. Run tests:
```bash
vendor/bin/phpunit
```

## Requirements

- PHP 8.1+
- Composer
- 7-Zip (`7za` in PATH) for running 7zip adapter tests

## Branching

- `master` — stable releases
- `release/1.x` — current development branch

## Submitting Changes

1. Fork the repository
2. Create a feature branch from `master`
3. Make your changes with tests
4. Ensure all tests pass: `vendor/bin/phpunit`
5. Submit a Pull Request to `master`

## Coding Standards

- PHP 8.1+ syntax
- `declare(strict_types=1)` in all source files
- PSR-4 autoloading
- PHPDoc for public methods

