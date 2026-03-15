---
description: 'Return structured JSON responses with custom status codes, headers, and data payloads.'
---

# Responses

The data you return with the response will be rendered under the `data` key in the resulting JSON.

## Example response

```php
return new Response([
  'first_name' => 'Jerry',
  'last_name' => 'Cotton',
]);
```

The resulting JSON:

```json
{
  "data": {
    "first_name": "Jerry",
    "last_name": "Cotton"
  }
}
```

## Response code

The default response code is `200`. Use `code()` to specify a custom response code.

```php
return (new Response([
  'first_name' => 'Jerry',
  'last_name' => 'Cotton',
]))->code(201);
```

## Additional top-level keys

Sometimes you need to return additional key-value pairs, such as a message, alongside the data. In this example, the response data contains user details, and the message would not belong there. Use `with()` to define additional keys.

```php{4-6}
return (new Response([
  'first_name' => 'Jerry',
  'last_name' => 'Cotton',
]))->with([
  'message' => 'Your details were saved successfully!',
]);
```

The resulting JSON:

```json
{
  "data": {
    "first_name": "Jerry",
    "last_name": "Cotton"
  },
  "message": "Your details were saved successfully!"
}
```

## Response without data

If you pass `null` to the constructor, the `data` key will be omitted from the response. This is useful when the response only contains additional keys.

```php
return (new Response(null))->with([
  'message' => 'Action completed successfully!',
]);
```

The resulting JSON:

```json
{
  "message": "Action completed successfully!"
}
```

## HTTP headers <Badge type="tip" text="^2.0" />

Use `header()` to set custom HTTP headers on the response. Headers set this way are emitted alongside `Content-Type` when the response is sent.

```php
return (new Response([
  'first_name' => 'Jerry',
  'last_name' => 'Cotton',
]))->header('X-Custom-Header', 'custom-value');
```

Multiple headers can be chained:

```php
return (new Response())->header('X-First', 'one')->header('X-Second', 'two');
```

## Paginated responses <Badge type="tip" text="^2.1" />

`PaginatedResponse` extends `Response` and adds a `pagination` key to the output. It provides `start()`, `limit()`, and `total()` setters, plus computed `page` and `pages` values.

::: warning
This class does **not** perform pagination. It only attaches pagination metadata to the response. Actual pagination must be handled elsewhere, for example with ProcessWire selectors using `start` and `limit`.
:::

```php
use PwJsonApi\PaginatedResponse;

return (new PaginatedResponse(['foo', 'bar', 'baz']))
  ->start(10)
  ->limit(10)
  ->total(85);
```

The resulting JSON:

```json
{
  "data": ["foo", "bar", "baz"],
  "pagination": {
    "start": 10,
    "limit": 10,
    "total": 85,
    "page": 2,
    "pages": 9
  }
}
```

All three setters — `start()`, `limit()`, and `total()` — must be called before `toArray()` or `toJson()`. A `LogicException` is thrown if any value is missing.

The computed values are derived as follows:

- **`page`** — current page number (1-based): `floor(start / limit) + 1`
- **`pages`** — total number of pages: `ceil(total / limit)`

## Methods

### toArray()

Return the response as an associative array.

```php
$response->toArray();
```
