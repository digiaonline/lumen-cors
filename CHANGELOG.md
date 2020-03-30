# Change Log

The format is based on [Keep a Changelog](https://keepachangelog.com/) and this project adheres to [Semantic Versioning](https://semver.org/).

## [3.3.0] - 2020-03-30
### Changed
- Lumen 7.x is now supported

## [3.2.0] - 2019-12-18
### Changed
- CorsService setters are now fluent
- Lumen 6.x is now supported
- Run Travis CI on PHP 7.3 and 7.4 too 

## [3.1.1] - 2019-01-05
### Changed
- Added basic pattern support for allowed origins
- Document how to override the library for advanced customization

## [3.1.0] - 2018-11-14
### Changed
- Exceptions are no longer thrown if the pre-flight request is considered "invalid". All requests will now go through 
and this library only adds headers to responses where appropriate.

## [3.0.0] - 2018-10-15
### Changed
- PHP 7.1 is now required
- Exceptions are no longer thrown if a non pre-flight request comes from a non-allowed origin
### Removed
- Support for custom callbacks for non-allowed pre-flight requests have been removed

## [2.1.0] - 2017-02-27
### Changed
- Do not call closure in CorsMiddleware::handle() before CORS validation in CorsService::handleRequest().
- Update tests.
- Update CHANGELOG.

## [2.0.1] - 2017-02-20
### Changed
- Composer lock-file.
- Update CHANGELOG.

## [2.0.0] - 2017-02-20
### Added
- CHANGELOG.md

### Changed
- Update composer to use lumen-framework 5.4.

### Fixed
- Namespace in CorsService.

## [1.7.0] - 2017-02-20
### Added
- Scrutinizer.
- StyleCI.
- Tests.

### Changed
- Use Lumen 5.3.

### Removed
- Travis PHP 5.5 build.
- Nightly builds.

### Fixed
- Issue #13.
- Issue #14.

## [1.6.0] - 2016-04-14
### Added
- Code coverage.

### Removed
- HHVM from travis CI.

### Fixed
- Travis CI configuration.

## [1.5.2] - 2016-03-30
### Fixed
- File name for CorsException.

## [1.5.1] - 2016-03-26
### Added
- Proper handling for errors.

### Changed
- Update README.
- Ditch support for PHP 5.4.

## [1.5.0] - 2016-03-22
### Added
- Exceptions.

### Changed
- Update README to reflect configuration changes.
- Improved handling of not allowed requests.
- Re-factor CorsService.
- Update tests.

## [1.4.0] - 2016-03-11
### Added
- Contributing information.
- Code climate badges.
- Tests for more PHP versions.

### Changed
- Tests against all branches.
- Use container-based infrastructure.
- Move CorsMiddleware to source root.
- Configuration parameters to use snake_case.
- Re-factor CorsServiceProvider.

### Fixed
- Check if CORS Class already exists when adding class alias.

## [1.3.4] - 2015-05-15
### Changed
- Update README.

## [1.3.3] - 2015-05-15
### Changed
- Update README.

## [1.3.2] - 2015-05-15
### Changed
- Update README.
- Update composer.

## [1.3.1] - 2015-05-15
### Added
- Missing comments.

## [1.3.0] - 2015-05-15
### Added
- Missing comments.

### Removed
- HHVM from Travis CI.

## [1.2.6] - 2015-05-15
### Added
- Custom Xdebug configuration.

### Changed
- Update tests.

## [1.2.5] - 2015-05-15
### Changed
- Update tests.

## [1.2.4] - 2015-05-15
### Changed
- Update tests.

## [1.2.3] - 2015-05-15
### Changed
- Update tests.

## [1.2.2] - 2015-05-15
### Changed
- Use shallow clones in tests.

## [1.2.1] - 2015-05-15
### Changed
- Update README.

## [1.2.0] - 2015-05-15
### Added 
- Keywords to composer.

### Changed
- Cleanup code in CorsService.
- Re-factor unit tests.
- Update README.

## [1.1.0] - 2015-05-14
### Added
- Keywords to composer.

## [1.0.0] - 2015-05-14
### Added
- Project files.

[Unreleased]: https://github.com/nordsoftware/lumen-cors/compare/2.1.0...HEAD
[2.1.0]: https://github.com/nordsoftware/lumen-cors/compare/2.0.1...2.1.0
[2.0.1]: https://github.com/nordsoftware/lumen-cors/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/nordsoftware/lumen-cors/compare/1.7.0...2.0.0
[1.7.0]: https://github.com/nordsoftware/lumen-cors/compare/1.6.0...1.7.0
[1.6.0]: https://github.com/nordsoftware/lumen-cors/compare/1.5.2...1.6.0
[1.5.2]: https://github.com/nordsoftware/lumen-cors/compare/1.5.1...1.5.2
[1.5.1]: https://github.com/nordsoftware/lumen-cors/compare/1.5.0...1.5.1
[1.5.0]: https://github.com/nordsoftware/lumen-cors/compare/1.4.0...1.5.0
[1.4.0]: https://github.com/nordsoftware/lumen-cors/compare/1.3.4...1.4.0
[1.3.4]: https://github.com/nordsoftware/lumen-cors/compare/1.3.3...1.3.4
[1.3.3]: https://github.com/nordsoftware/lumen-cors/compare/1.3.2...1.3.3
[1.3.2]: https://github.com/nordsoftware/lumen-cors/compare/1.3.1...1.3.2
[1.3.1]: https://github.com/nordsoftware/lumen-cors/compare/1.3.0...1.3.1
[1.3.0]: https://github.com/nordsoftware/lumen-cors/compare/1.2.6...1.3.0
[1.2.6]: https://github.com/nordsoftware/lumen-cors/compare/1.2.5...1.2.6
[1.2.5]: https://github.com/nordsoftware/lumen-cors/compare/1.2.4...1.2.5
[1.2.4]: https://github.com/nordsoftware/lumen-cors/compare/1.2.3...1.2.4
[1.2.3]: https://github.com/nordsoftware/lumen-cors/compare/1.2.2...1.2.3
[1.2.2]: https://github.com/nordsoftware/lumen-cors/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/nordsoftware/lumen-cors/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/nordsoftware/lumen-cors/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/nordsoftware/lumen-cors/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/nordsoftware/lumen-cors/tree/1.0.0
