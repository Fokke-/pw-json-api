# CHANGELOG

## 2.0.0

### Breaking changes

- `OPTIONS` requests to non-existent endpoints are no longer intercepted with an empty `200` response. These requests are now handled by ProcessWire's default routing.
- `$event` argument is no longer passed directly to the endpoint handler. Use [`$args->event` instead](https://fokke-.github.io/pw-json-api/endpoints.html#endpoint-handler-arguments).
- `$e` and `$request` are no longer passed directly to the `handleException()` callback function. [Use `$args->exception` and `$args->request` instead](https://fokke-.github.io/pw-json-api/api-instance.html#exception-handling).
- `RequestHookReturn::$method` property has been removed. Use [`RequestHookReturn::$request->method` instead](https://fokke-.github.io/pw-json-api/request-hooks.html#hook-arguments).
- `ApiException::$method` property has been removed. [Use `ApiException::$request->method` instead](https://fokke-.github.io/pw-json-api/error-hooks.html#error-hook-arguments).
- **Page parser:** Page properties, such as `template` are no longer defined by using `fields()` method. [Use `properties()` instead](https://fokke-.github.io/pw-json-api/processwire-page-parser.html#property-selection).

### New features

- New `init()` method for Services
- New `Request` object is exposed in arguments of request handlers and hooks
- New plugin interface
- CSRF protection plugin
- `Api::$wire` is now public property
- `Service::$wire` is now public property
- **Page Parser:** Added new methods:
  - `properties()`
  - `excludeProperties()`
  - `hookBeforePropertyParse()`
  - `hookAfterPropertyParse()`
- **Page Parser:** Include base file name in parsed file data
- **Page Parser:** Include file extension in parsed file data
- `Allow:` header is included for requests if the request method is disallowed or `OPTIONS`

### Bug fixes

- Error hooks will be applied to `ApiException` thrown from `handleException()` method
- Fixed incorrect `Content-Type: application/json` header when an unhandled exception was thrown
- Fixed exception handler not returning `Response` when handler result was valid

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
