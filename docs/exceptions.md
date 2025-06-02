# Exceptions

The library catches any exception derived from the `ApiException` class and converts it to a response. You can throw an `ApiException` at any time to halt the process.

::: tip
For other exceptions, such as `WireException`, you need to add custom handling.
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

## Api404Exception

This can be used as a shorthand for an exception with a `404` response code.

```php
throw new Api404Exception();
```
