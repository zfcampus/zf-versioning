# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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
