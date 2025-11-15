# CHANGELOG

## 1.2.0

### New features

- New `Request` object is exposed in request handlers and hooks
- New plugin interface
- CSRF protection plugin
- Better type hinting for `addService()` method
- **Page Parser:** Added new methods:
  - `properties()`
  - `excludeProperties()`
  - `hookBeforePropertyParse()`
  - `hookAfterPropertyParse()`
- **Page Parser:** Include base file name in parsed file data
- **Page Parser:** Include file extension in parsed file data
- `Api::$wire` is now public property
- `Service::$wire` is now public property

### Bug fixes

- Error hooks will be applied to `ApiException` thrown from `handleException()` method
- Fixed incorrect `Content-Type` header when an unhandled exception was thrown

### Deprecations

The following deprecations will be removed in the next major version.

- `RequestHookReturn::$event` property - Use `RequestHookReturn::$request->event` instead.
- `RequestHookReturn::$method` property - Use `RequestHookReturn::$request->method` instead.
- `ApiException::$event` property - Use `ApiException::$request->event` instead
- `ApiException::$method` property- Use `ApiException::$request->method` instead

## 1.1.1

### Bug fixes

- Fixed an issue where error hooks would not be executed

## 1.1.0

### New features

- $api is now exposed in request hooks
- $api is now exposed in error hooks

### Bug fixes

- API instance no longer eats exceptions when exception handler is not defined
- Fixed `PageParser::toArray()` behaviour when parser has no input defined

## 1.0.0

Initial release
