---
description: 'Throw ApiException to return structured error responses with custom status codes and messages.'
---

# Error handling

The library catches any exception derived from the `ApiException` class and converts it to a response. You can throw an `ApiException` at any time to halt the process.

::: tip
To handle other exceptions, such as `WireException`, you need to specify custom handling function by calling `handleException()` method of the API instance. [Read more about exception handling](api-instance.html#exception-handling).
:::

## Example exception

```php
throw new ApiException('Snap, crackle and pop!');
```

The resulting JSON:

```json
{
  "error": "Snap, crackle and pop!"
}
```

## Exception response code

The default response code is `400`. Use `code()` to specify a custom code.

```php
throw (new ApiException('Snap, crackle and pop!'))->code(401);
```

## Additional top-level keys

Like responses, exceptions can also contain additional key-value pairs.

```php
throw (new ApiException())->code(401)->with([
  'login_url' => 'https://example.com/login',
]);
```

The resulting JSON:

```json
{
  "login_url": "https://example.com/login"
}
```

## HTTP headers <Badge type="tip" text="^2.0" />

Use `header()` to set custom HTTP headers on the error response.

```php
throw (new ApiException('Forbidden'))
  ->code(403)
  ->header('X-Reason', 'insufficient-permissions');
```

## Api404Exception

This can be used as a shorthand for an exception with a `404` response code.

```php
throw new Api404Exception();
```
