# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.0.5] — 2026-06-07

### Fixed
- Exception namespace correctness in `Zippy::create()` and `Zippy::open()`:
  `ExceptionInterface` and `RuntimeException` now correctly reference `Alchemy\Zippy\Exception\*`
- Password state leak between operations: adapter password is now always explicitly
  set (including reset to `null`) before each `create()` / `open()` call
- `DomainException` in `Zip7zipAdapter::doExtractMembers()` now uses FQCN `\DomainException`
- Undefined variable `$ok` in `ZippyTesting::testExtractWithPassword()` — replaced with `$ok1`
- `Zip7zipAdapter::parseVersion()` now uses regex extraction instead of fixed string offset,
  making it locale-agnostic (fixes failures on non-English system locales)
- `FileHelper` constructor now auto-creates `files_arena` and `archives_arena` directories
  if they do not exist, eliminating test setup errors on fresh checkouts

### Added
- `phpunit/phpunit ^9.6` added to `require-dev` for reproducible test runs
- `Requirements` and `Known Limitations` sections added to `README.md`

### Changed
- Test classes migrated to PHPUnit 9 compatible signatures:
  `setUp(): void` and `tearDown(): void` (removes legacy constructor pattern)
- Legacy `__construct()` removed from `ZippyZipTest`, `ZippyTarTest`, `Zippy7zipTest`
- Password-related tests for zip and tar adapters now use `markTestSkipped` with
  explicit reason instead of silent `assertTrue(true)` stubs
- Removed deprecated `<filter><blacklist>` block from `phpunit.xml` (not valid in PHPUnit 9)
- Removed hardcoded `"version"` field from `composer.json` — Packagist derives version
  from git tags (official recommendation)
- Updated `.gitignore`: added `.idea/`, `.phpunit.result.cache`, `tests/files_arena/`,
  `tests/archives_arena/`
- Fixed variable name typo in README `$archiveZip` → `$archive` in 7zip extract example
- Fixed typo `libruary` → `library` in README description

### Security
- `symfony/process@3.4.47` contains CVE-2024-51736 (HIGH) and CVE-2026-24739 (MEDIUM),
  both **Windows-only**. Not exploitable on Linux. Fix scheduled for v1.0.0
  (requires PHP 8.1+ and `symfony/process ^5.4.51`).

## [0.0.4] — (previous release)

- Fix issue with extracting by 7zip with password

## [0.0.3] — (previous release)

- Initial stable release

[Unreleased]: https://github.com/victor78/ZippyExt/compare/v0.0.5...HEAD
[0.0.5]: https://github.com/victor78/ZippyExt/compare/0.0.4...v0.0.5
[0.0.4]: https://github.com/victor78/ZippyExt/compare/0.0.3...0.0.4
[0.0.3]: https://github.com/victor78/ZippyExt/releases/tag/0.0.3

