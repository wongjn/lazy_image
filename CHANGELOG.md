# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Add helper class tests

### Changed
- Expose supported theme hooks as public constant

### Security
- Implement `TrustedCallbackInterface` for `#pre_render`

### Removed
- Remove `#lazy_placeholder` render array property

## [1.3.8] - 2020-02-28
### Added
- Add `core_version_requirement` key

### Changed
- Update module dependency notation

### Fixed
- Fix invalid config schema
- Improve preview for CKEditor preview media images

## [1.3.7] - 2019-05-05
### Added
- Add lazy_image style config as dependency

## [1.3.6] - 2018-12-06
### Security
- Secure external JavaScript

## [1.3.5] - 2018-12-03
### Changed
- Encode and embed lazy image placeholders

## [1.3.4] - 2018-10-25
### Fixed
- Fix empty `<noscript>` tags

## [1.3.3] - 2018-09-28
### Changed
- Leverage core theme engine to prevent recursion

## [1.3.2] - 2018-09-21
### Added
- Add configuration schema

### Removed
- Remove version number from info

## [1.3.1] - 2018-09-10
### Fixed
- Fix placeholder image generation from image_style

## [1.3.0] - 2018-08-31
### Added
- Introduce new API to add placeholder image `#lazy_placeholder_style`

### Changed
- Deprecate old API to add placeholder image `#lazy_placeholder`

### Fixed
- Fix coding standards violation
- Fix typo in comment

[Unreleased]: https://github.com/wongjn/lazy_image/compare/1.3.8...HEAD
[1.3.8]: https://github.com/wongjn/lazy_image/compare/1.3.7...1.3.8
[1.3.7]: https://github.com/wongjn/lazy_image/compare/1.3.6...1.3.7
[1.3.6]: https://github.com/wongjn/lazy_image/compare/1.3.5...1.3.6
[1.3.5]: https://github.com/wongjn/lazy_image/compare/1.3.4...1.3.5
[1.3.4]: https://github.com/wongjn/lazy_image/compare/1.3.3...1.3.4
[1.3.3]: https://github.com/wongjn/lazy_image/compare/1.3.2...1.3.3
[1.3.2]: https://github.com/wongjn/lazy_image/compare/1.3.1...1.3.2
[1.3.1]: https://github.com/wongjn/lazy_image/compare/1.3.0...1.3.1
[1.3.0]: https://github.com/wongjn/lazy_image/compare/1.3.0...1.2.1
