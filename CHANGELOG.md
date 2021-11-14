# Changelog

## [v1.0.0-alpha5](https://github.com/aphiria/aphiria/compare/v1.0.0-alpha4...v1.0.0-alpha5) (2021-11-14)

### Fixed

- Fixed typos in PHPDoc ([#159](https://github.com/aphiria/aphiria/pull/159))

### Changed

- Now requires PHP 8.1 ([#159](https://github.com/aphiria/aphiria/pull/159))
- Many methods now converted to readonly properties ([#159](https://github.com/aphiria/aphiria/pull/159))
- `Aphiria\Net\Http\Response::__construct()` now takes in either a `StatusCode` or an int status code ([#159](https://github.com/aphiria/aphiria/pull/159))
- `Aphiria\Net\Http\IResponse::getStatusCode()` now returns `StatusCode` and `setStatusCode()` takes in a `StatusCode` ([#159](https://github.com/aphiria/aphiria/pull/159))
- `Aphiria\Net\Http\Headers\Cookie::sameSite` now returns `SameSiteMode` ([#159](https://github.com/aphiria/aphiria/pull/159))
- Converted the following classes to enums ([#159](https://github.com/aphiria/aphiria/pull/159)):
  - `Aphiria\Console\Input\ArgumentTypes` => `Aphiria\Console\Input\ArgumentType`
  - `Aphiria\Console\Input\OptionTypes` => `Aphiria\Console\Input\OptionType`
  - `Aphiria\Console\Output\Compilers\Elements\Colors` => `Aphiria\Console\Output\Compilers\Elements\Color`
  - `Aphiria\Console\Output\Compilers\Elements\TextStyles` => `Aphiria\Console\Output\Compilers\Elements\TextStyle`
  - `Aphiria\Console\Output\Lexers\OutputTokenTypes` => `Aphiria\Console\Output\Lexers\OutputTokenType`
  - `Aphiria\Net\Http\StatusCodes` => `Aphiria\Net\Http\StatusCode`
  - `Aphiria\Net\Http\Headers\SameSiteMode` (new)
  - `Aphiria\Routing\UriTemplates\Lexers\TokenTypes` => `Aphiria\Routing\UriTemplates\Lexers\TokenType`
  - `Aphiria\Routing\UriTemplates\Parsers\AstNodeTypes` => `Aphiria\Routing\UriTemplates\Parsers\AstNodeType`
- `Aphiria\Console\Input\Argument` and `Aphiria\Console\Commands\Attributes\Argument` now take in an `ArgumentType` or list of `ArgumentType`s instead of a bitwise-OR'd integer ([#159](https://github.com/aphiria/aphiria/pull/159))
- `Aphiria\Console\Input\Option` and `Aphiria\Console\Commands\Attributes\Option` now take in an `OptionType` or list of `OptionType`s instead of a bitwise-OR'd integer ([#159](https://github.com/aphiria/aphiria/pull/159))
- `Aphiria\Api\Controllers\IRouteActionInvoker::invokeRouteAction()` now requires a `Closure` `$routeActionDelegate` instead of just a `callable` ([#159](https://github.com/aphiria/aphiria/pull/159))
- `Aphiria\Collections\IList::sort()` now requires a `Closure` `$comparer` instead of just a `callable` ([#159](https://github.com/aphiria/aphiria/pull/159))
- `Aphiria\Collections\ISet::sort()` now requires a `Closure` `$comparer` instead of just a `callable` ([#159](https://github.com/aphiria/aphiria/pull/159))
- `Aphiria\Console\Output\Formatters\PaddingFormatter::format()` now requires a `Closure` `$callback` instead of just a `callable` ([#159](https://github.com/aphiria/aphiria/pull/159))
- `Aphiria\DependencyInjection\IContainer::bindFactory()` now requires a `Closure` `$factory` instead of just a `callable` ([#159](https://github.com/aphiria/aphiria/pull/159))
- `Aphiria\DependencyInjection\IServiceResolver::for()` now requires a `Closure` `$callback` instead of just a `callable` ([#159](https://github.com/aphiria/aphiria/pull/159))
- `Aphiria\Validation\Constraints\CallbackConstraint()` now requires a `Closure` `$callback` instead of just a `callable` ([#159](https://github.com/aphiria/aphiria/pull/159))
- `Aphiria\Application\IModule::build()` renamed to `configure()` ([#165](https://github.com/aphiria/aphiria/pull/165))
- All Symfony dependencies were bumped to ^6.0 ([#159](https://github.com/aphiria/aphiria/pull/159))

### Added

- Added support for automatically resolving unknown encoders/decoders and normalizers/denormalizers in `Aphiria\Framework\Serialization\Binders\SymfonySerializerBinder` ([#159](https://github.com/aphiria/aphiria/pull/159))
- Added support for `Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer` so that enums can be properly (de)serialized ([#159](https://github.com/aphiria/aphiria/pull/159))

## [v1.0.0-alpha4](https://github.com/aphiria/aphiria/compare/v1.0.0-alpha3...v1.0.0-alpha4) (2021-08-08)

### Fixed

- Fixed bug that prevented error messages from being displayed when the application runs out of memory ([#142](https://github.com/aphiria/aphiria/pull/142))
- Fixed a lot of PHPDoc to use `list<{type}>` instead of `{type}[]` where applicable ([#128](https://github.com/aphiria/aphiria/pull/128))
- Switched to using stable version of Xdebug during CI ([#138](https://github.com/aphiria/aphiria/pull/138))
- Re-enabled linter in libraries' CI ([#139](https://github.com/aphiria/aphiria/pull/139))

### Changed

- Changed `IList::intersect()`, `IList::reverse()`, `IList::sort()`, `IList::union()`, `ISet::intersect()`, `ISet::sort()`, and `ISet::union()` to return a new instance rather than change the original value ([#131](https://github.com/aphiria/aphiria/pull/131))
- Changed all collection constructors to be final ([#131](https://github.com/aphiria/aphiria/pull/131))
- Updated to use PHP-CS-Fixer 3.0 ([#140](https://github.com/aphiria/aphiria/pull/140))

### Added

- Added support for generics to all collections to provide better typing ([#147](https://github.com/aphiria/aphiria/pull/147))

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
