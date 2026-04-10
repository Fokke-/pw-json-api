# CHANGELOG

## 2.2.0

### New features

- Authentication support via `AuthInterface` — set an authenticator on API, service, or endpoint level with `authenticate()`
- Authorization support via `authorize()` callback — chain authorization checks across API → services → endpoint
- New `AuthenticationException` (401) and `AuthorizationException` (403) exception classes

## 2.1.2

### Bug fixes

- Fixed links in `llms-full.txt`

## 2.1.1

### Bug fixes

- `llms-full.txt` is now included in the Composer package for AI-assisted development

## 2.1.0

### New features

- Add `llms.txt` and `llms-full.txt` for AI-assisted development
- New `PaginatedResponse` class for attaching pagination metadata to responses
- **Page Parser:** New `toPaginatedResponse()` method for returning paginated results directly from the parser
- **Page Parser:** `skip()` method for `BeforePageParse`, `BeforePropertyParse`, and `BeforeFieldParse` hooks to exclude items from output

## 2.0.0

### Breaking changes

- `Api::run()` is now defined as final
- Services, endpoints, and the Api instance are now locked after `Api::run()` is called to prevent cross-service injection and runtime mutation. Adding services, endpoints, plugins, hooks, or setting handlers after locking will throw `WireException`. All configuration must happen in Service `init()`, `__construct()`, or the setup callback.
- `OPTIONS` requests to non-existent endpoints are no longer intercepted with an empty `200` response. These requests are now handled by ProcessWire's default routing.
- `$event` argument is no longer passed directly to the endpoint handler. Use [`$args->event` instead](https://pwjsonapi.fokke.fi/endpoints.html#endpoint-handler-arguments).
- `$e` and `$request` are no longer passed directly to the `handleException()` callback function. [Use `$args->exception` and `$args->request` instead](https://pwjsonapi.fokke.fi/api-instance.html#exception-handling).
- `RequestHookReturn::$method` property has been removed. Use [`RequestHookReturn::$request->method` instead](https://pwjsonapi.fokke.fi/request-hooks.html#hook-arguments).
- Error hook handler now receives an `ErrorHookReturn` object instead of `ApiException`. The exception is available via `$args->exception`. [See error hook arguments](https://pwjsonapi.fokke.fi/error-hooks.html#error-hook-arguments).
- `ApiException` context properties (`request`, `event`, `endpoint`, `service`, `services`, `api`) have been removed. These are available on the error hook `$args` object instead.
- **Page parser:** Page properties, such as `template` are no longer defined by using `fields()` method. [Use `properties()` instead](https://pwjsonapi.fokke.fi/processwire-page-parser.html#property-selection).

### New features

- New `init()` method for services
- New `header()` method for response
- New `Request` object is exposed in arguments of request handlers and hooks
- New plugin interface
- CSRF protection plugin
- Rate limit plugin
- `Api::$wire` is now public property
- `Service::$wire` is now public property
- `Allow:` header is included for requests if the request method is disallowed or `OPTIONS`
- **Page Parser:** Performance enhancements
- **Page Parser:** Added new methods:
  - `properties()`
  - `excludeProperties()`
  - `hookBeforePropertyParse()`
  - `hookAfterPropertyParse()`
- **Page Parser:** Include base file name in parsed file data
- **Page Parser:** Include file extension in parsed file data

### Bug fixes

- Before-hooks can now replace the endpoint handler via `$args->handler`
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
