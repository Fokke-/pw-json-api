# Exceptions

The library catches any exception derived from `ApiException` class, and converts it to a response. You can throw `ApiException` at any time to halt process.

::: info
Only `ApiException` will be caught. For other exceptions, such as `WireException`, you need add custom handling.
:::

## Example exception

```php
use PwJsonApi\ApiException;
```

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

The default response code is `400`. Use `code()` to specify custom code.

```php
throw (new ApiException('Snap, crackle and pop!'))->code(401);
```

## Additional top level keys

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

## Api404Exception

This can be used as a shorthand for exception with `404` response code. Errors with 404 code should not have any visible message.

```php
use PwJsonApi\Api404Exception;
```

```php
throw new Api404Exception();
```
