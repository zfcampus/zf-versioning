# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.3.0 - 2018-05-03

### Added

- [#21](https://github.com/zfcampus/zf-versioning/pull/21) adds support for PHP 7.1 and 7.2.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#21](https://github.com/zfcampus/zf-versioning/pull/21) removes support for HHVM.

### Fixed

- Nothing.

## 1.2.1 - 2018-02-05

### Added

- [#19](https://github.com/zfcampus/zf-versioning/pull/19) adds the ability to
  override the `default_version` setting to specify default versions by route
  name. As such, the `default_version` value may be one of the following:

  - An integer value, indicating the default version for all APIs.
  - An associative array of route name keys pointing to their specific default version.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#19](https://github.com/zfcampus/zf-versioning/pull/19) fixes a problem with
  how the `PrototypeRouteListener` handles the `default_version` setting; the
  value was overriding route-specific defaults.

## 1.2.0 - 2016-07-13

### Added

- [#14](https://github.com/zfcampus/zf-versioning/pull/14) adds support for v3
  releases of Zend Framework components, while retaining compatibility with v2
  releases.
- [#14](https://github.com/zfcampus/zf-versioning/pull/14) adds
  `ZF\Versioning\Factory\AcceptListenerFactory` and
  `ZF\Versioning\Factory\ContentTypeListenerFactory`, instead of creating
  the factories inline in the `Module` class.

### Deprecated

- Nothing.

### Removed

- [#14](https://github.com/zfcampus/zf-versioning/pull/14) removes support for PHP 5.5.

### Fixed

- [#15](https://github.com/zfcampus/zf-versioning/pull/15) fixes the
  `VersionListener` to no longer ignore OPTIONS requests when determining
  versioning information provided by the client. Previously, such requests were
  ignored, effectively locking OPTIONS requests to v1 of an API.
