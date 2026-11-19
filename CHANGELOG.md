# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.2] - 2026-11-19

### Fixed
- `conditional_options.container` replaced any class the form already declared through
  `row_attr`, silently dropping it. The container class is now added to the existing classes
  instead, and is not repeated when it is already present.

## [1.2.1] - 2026-11-19

### Fixed
- The initialization script was repeated once per nested compound form that contained
  conditional fields, so a three level deep form emitted it three times and initialized the
  same form repeatedly. It is now added to the root form view only.
- The initialization script now targets the root form's actual rendered id instead of the
  form name, so forms with a custom `attr.id` are initialized correctly.

## [1.2.0] - 2026-11-19

### Fixed
- Conditional fields were no longer detected once a compound child without conditional
  fields was rendered before them, which meant the initialization script was omitted and
  the fields never reacted to their rules.
- `symfony/options-resolver` is now required explicitly instead of being relied upon
  through `symfony/form`.

### Added
- A PHPUnit test suite covering rule building, nested form hierarchies, option validation
  and the service registration.
- Static analysis with PHPStan and a coding standard enforced by PHP-CS-Fixer.
- A GitHub Actions workflow running the quality tooling and the test suite against
  PHP 8.2 up to 8.5 on both Symfony 7 and Symfony 8, including a lowest-dependency run.
- Dependabot updates for Composer and GitHub Actions.
- Composer metadata (authors, keywords, support links) and `composer qa` shortcuts.

### Changed
- All source files declare strict types.
- `conditional_options` values are validated more defensively, so malformed input raises a
  `ConditionalFieldException` instead of a PHP error.

## [1.1.4] - 2026-09-01

### Added
- Symfony 8 support.

## [1.1.3] - 2024-10-15

### Fixed
- A syntax error in `ConditionalFieldExtension`.

## [1.1.2] - 2024-10-08

### Fixed
- Conditional fields inside child form types are now detected.
- Rule names are built from the full form type hierarchy.

## [1.1.1] - 2024-10-04

### Fixed
- The `isempty` and `isnotempty` operators no longer require a `value`.

## [1.1.0] - 2024-07-30

### Changed
- The form theme supports both classic script loading and module loading.

## [1.0.0] - 2024-07-23

### Added
- Initial release.

[1.2.2]: https://github.com/Martin1982/mfconditionalfieldsbundle/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/Martin1982/mfconditionalfieldsbundle/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/Martin1982/mfconditionalfieldsbundle/compare/1.1.4...1.2.0
[1.1.4]: https://github.com/Martin1982/mfconditionalfieldsbundle/compare/1.1.3...1.1.4
[1.1.3]: https://github.com/Martin1982/mfconditionalfieldsbundle/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/Martin1982/mfconditionalfieldsbundle/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/Martin1982/mfconditionalfieldsbundle/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/Martin1982/mfconditionalfieldsbundle/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/Martin1982/mfconditionalfieldsbundle/releases/tag/1.0.0
