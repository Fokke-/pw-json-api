---
description: 'Access request properties like method, path, query parameters, headers, cookies, and body data in endpoint handlers.'
---

# Requests

Request object can be accessed in callback functions of:

- [Endpoint request handler](/endpoints.html#endpoint-handler-arguments)
- [Request hook handler](/request-hooks.html#hook-arguments)
- [Error hook handler](/error-hooks.html#error-hook-arguments)
- [API instance exception handler](/api-instance.html#exception-handling)

## Request properties

| Property      | Type                  | Description                                                        |
| ------------- | --------------------- | ------------------------------------------------------------------ |
| `method`      | `string`              | Request method                                                     |
| `methodEnum`  | `RequestMethod\|null` | Method as enum                                                     |
| `path`        | `string\|null`        | Requested path                                                     |
| `routeParams` | `array`               | Route parameters of [dynamic paths](/endpoints.html#dynamic-paths) |
| `queryParams` | `array`               | Query parameters                                                   |
| `headers`     | `array`               | Request headers                                                    |
| `contentType` | `string\|null`        | `Content-Type` header                                              |
| `accept`      | `string\|null`        | `Accept` header                                                    |
| `cookies`     | `array`               | Shorthand for `$_COOKIE`                                           |
| `ip`          | `string\|null`        | Shorthand for `$_SERVER['REMOTE_ADDR']`                            |
| `userAgent`   | `string\|null`        | Shorthand for `$_SERVER['HTTP_USER_AGENT']`                        |
| `protocol`    | `string\|null`        | Shorthand for `$_SERVER['SERVER_PROTOCOL']`                        |
| `body`        | `mixed`               | Request body                                                       |
| `files`       | `array`               | Normalized value of `$_FILES`                                      |

::: warning
Properties like `body`, `queryParams`, `routeParams`, `headers`, `cookies`, and `files` contain raw, unsanitized input from the client. Always sanitize values before using them — for example with ProcessWire's [$sanitizer API](https://processwire.com/api/ref/sanitizer/).
:::

### Body

If the request `Content-Type` header is `application/json`, the request body will be parsed from `php://input`. In such cases, the body must be a valid JSON string. If the JSON is malformed, an `ApiException` will be thrown.

For all other cases, the body will contain the raw value of `$_POST` superglobal.

### Files

By default, `$_FILES` superglobal has a non-intuitive structure when it contains multiple files for the same field. `files` property contains a normalized value. The structure is always an array, regardless of the number of files.

#### Single file

```json
{
  "my_file_field": [
    {
      "name": "foo.txt",
      "full_path": "foo.txt",
      "type": "text/plain",
      "tmp_name": "/tmp/phprawUMP",
      "error": 0,
      "size": 4
    }
  ]
}
```

#### Multiple files

```json
{
  "my_file_field": [
    {
      "name": "foo.txt",
      "full_path": "foo.txt",
      "type": "text/plain",
      "tmp_name": "/tmp/phprawUMP",
      "error": 0,
      "size": 4
    },
    {
      "name": "bar.txt",
      "full_path": "bar.txt",
      "type": "text/plain",
      "tmp_name": "/tmp/php8P7qcq",
      "error": 0,
      "size": 4
    }
  ]
}
```

## Request methods

### routeParam()

Get route parameter. If parameter is not found, `null` is returned.

```php
$request->routeParam('product');
```

### queryParam()

Get query parameter. If parameter is not found, `null` is returned.

```php
$request->queryParam('product');
```

### toArray()

Return all request properties as an associative array.

```php
$request->toArray();
```
