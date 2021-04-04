# Changelog

## [v1.0.0-alpha4](https://github.com/aphiria/aphiria/compare/v1.0.0-alpha3...v1.0.0-alpha4) (?)

### Fixed

- Fixed a lot of PHPDoc to use `list<{type}>` instead of `{type}[]` where applicable ([#128](https://github.com/aphiria/aphiria/pull/128))

### Changed

- Changed `IList::intersect()`, `IList::reverse()`, `IList::sort()`, `IList::union()`, `ISet::intersect()`, `ISet::sort()`, and `ISet::union()` to return a new instance rather than change the original value ([#131](https://github.com/aphiria/aphiria/pull/131))
- Changed all collection constructors to be final ([#131](https://github.com/aphiria/aphiria/pull/131))

## [v1.0.0-alpha3](https://github.com/aphiria/aphiria/compare/v1.0.0-alpha2...v1.0.0-alpha3) (2021-03-13)

### Fixed

- Fixed a bug that caused reading console input to throw an exception on certain setups ([#122](https://github.com/aphiria/aphiria/pull/122))

### Added

- Added the ability to read config values as objects ([#124](https://github.com/aphiria/aphiria/pull/124))

## [v1.0.0-alpha2](https://github.com/aphiria/aphiria/compare/v1.0.0-alpha1...v1.0.0-alpha2) (2021-2-15)

### Fixed

- Fixed a bug that caused non-scalar values in `$_SERVER` to throw an exception when creating a request via `RequestFactory::createRequestFromSuperglobals()` ([#116](https://github.com/aphiria/aphiria/pull/116))

### Changed

- Changed default port number to 8080 when running `php aphiria app:serve` ([#114](https://github.com/aphiria/aphiria/pull/114))
- Reintroduced PHP-CS-Fixer and ran it ([#106](https://github.com/aphiria/aphiria/pull/106), [#109](https://github.com/aphiria/aphiria/pull/109), [#111](https://github.com/aphiria/aphiria/pull/111))
- Updated Psalm and removed unused suppressions ([#112](https://github.com/aphiria/aphiria/pull/112))
- Updated copyright year ([#103](https://github.com/aphiria/aphiria/pull/103))

## v1.0.0-alpha1 (2020-12-20)

### Added

- Literally everything
