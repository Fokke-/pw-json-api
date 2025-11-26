# CHANGELOG

## 1.2.0

### New features

- `Service::$name` is now mutable property. This can be useful when you want to extend an existing service, but keep the original name.

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
