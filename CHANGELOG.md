# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.1] — 2026-06-07

### Fixed
- `Zip7zipVersionProbe` now validates **both** inflator and deflator binaries before reporting support.
- Probe status cache is reset when inflator/deflator factories are changed.
- `Zip7zipAdapter::sanitizeCommandLine()` now masks password in both forms:
  `-psecret` and `-p "secret with spaces"`.

### Changed
- `Zip7zipAdapter` listing parser now treats `7z l -slt` as primary format and only falls back to legacy table parsing if needed.
- `Zip7zipAdapter` `-slt` record filtering is more robust (`Path` + `Folder` required for item records).
- `Zippy::guessAdapterExtension()` now uses `str_ends_with()` for readability.
- `Zippy::create()` / `Zippy::open()` now throw clearer runtime error when archive type cannot be inferred from path.
- `AdapterContainer` was modernized (strict types, typed `load()` return, static closures) without behavior changes.

### Added
- New parser tests in `tests/unit/Zip7zipAdapterParserTest.php`:
  - `-slt` parsing,
  - legacy fallback parsing,
  - password masking coverage for both CLI password styles.

## [1.0.0] — 2026-06-07

### Security
- Upgraded `symfony/process` to `^5.4.51` (addresses CVE-2024-51736 and CVE-2026-24739).

### Changed
- Minimum PHP version raised to `8.1`.
- Upgraded `alchemy/zippy` to `^1.0`.
- Upgraded test stack to `phpunit/phpunit ^10.5`.
- Added strict typing and PHP 8.1 modernization in core code.
- Added CI workflow (`.github/workflows/ci.yml`) for PHP 8.1/8.2/8.3.
- Added `CONTRIBUTING.md` and `SECURITY.md`.

## [0.0.5] — 2026-06-07

### Fixed
- Exception namespace correctness in `Zippy::create()` and `Zippy::open()`:
  `ExceptionInterface` and `RuntimeException` now correctly reference `Alchemy\Zippy\Exception\*`.
- Password state leak between operations: adapter password is now always explicitly
  set (including reset to `null`) before each `create()` / `open()` call.
- `DomainException` in `Zip7zipAdapter::doExtractMembers()` now uses FQCN `\DomainException`.
- Undefined variable `$ok` in `ZippyTesting::testExtractWithPassword()` was replaced with `$ok1`.
- `Zip7zipAdapter::parseVersion()` now uses regex extraction instead of fixed string offset.

### Added
- `phpunit/phpunit ^9.6` added to `require-dev` for reproducible test runs.
- `Requirements` and `Known Limitations` sections added to `README.md`.

### Changed
- Test classes migrated to PHPUnit-compatible signatures:
  `setUp(): void` and `tearDown(): void`.
- Password-related tests for zip/tar adapters now use explicit `markTestSkipped()`.
- Removed hardcoded `"version"` field from `composer.json` (Packagist reads git tags).

## [0.0.4] — (previous release)

- Fix issue with extracting by 7zip with password.

## [0.0.3] — (previous release)

- Initial stable release.

[Unreleased]: https://github.com/victor78/ZippyExt/compare/v1.0.1...HEAD
[1.0.1]: https://github.com/victor78/ZippyExt/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/victor78/ZippyExt/compare/v0.0.5...v1.0.0
[0.0.5]: https://github.com/victor78/ZippyExt/compare/0.0.4...v0.0.5
[0.0.4]: https://github.com/victor78/ZippyExt/compare/0.0.3...0.0.4
[0.0.3]: https://github.com/victor78/ZippyExt/releases/tag/0.0.3

