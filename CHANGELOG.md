# CHANGELOG

## 2.0.0

### Breaking changes

- RequestHookReturn::$method` property has been removed. Use `RequestHookReturn::$request->method` instead.
- ApiException::$method` property has been removed. Use `ApiException::$request->method` instead.
- **Page parser:** Page properties, such as `template` are no longer defined by using `fields()` method. Use `properties()` instead.

### New features

- New `Request` object is exposed in request handlers and hooks
- New plugin interface
- CSRF protection plugin
- Better type hinting for `addService()` method
- `Api::$wire` is now public property
- `Service::$wire` is now public property
- **Page Parser:** Added new methods:
  - `properties()`
  - `excludeProperties()`
  - `hookBeforePropertyParse()`
  - `hookAfterPropertyParse()`
- **Page Parser:** Include base file name in parsed file data
- **Page Parser:** Include file extension in parsed file data

### Bug fixes

- Error hooks will be applied to `ApiException` thrown from `handleException()` method
- Fixed incorrect `Content-Type: application/json` header when an unhandled exception was thrown

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
