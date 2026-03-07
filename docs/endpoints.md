# Endpoints

Under the hood, the endpoint path will be used to create a [ProcessWire URL hook](https://processwire.com/blog/posts/pw-3.0.173/#introducing-url-path-hooks). All URL hook features are supported.

Each endpoint listener must either return a [`Response`](/responses) object or throw an [`ApiException`](/error-handling) to halt the process and respond with an error.

::: info
By default, all the request methods are denied, except for `OPTIONS`. You must add listeners for each request method you want to allow. Requests with disallowed methods will be denied with a 405 response that includes an `Allow` header listing the permitted methods.

The `OPTIONS` method is always allowed for existing endpoints and will result in an empty response with a status code of `200`. The response includes an `Allow` header listing the permitted methods.
:::

The supported listeners are:

- `get()`
- `post()`
- `head()`
- `put()`
- `patch()`
- `delete()`

Like an API instance or service, an endpoint can also contain request hooks. [Read more about request hooks](/request-hooks).

## Example endpoint

Call `addEndpoint()` in the service `init()` method and pass the `path` you want to listen to.

```php
use PwJsonApi\ApiException;
```

```php
$this->addEndpoint('/user')
  // Handle GET request
  ->get(function () {
    return new Response([
      'first_name' => 'Jerry',
      'last_name' => 'Cotton',
    ]);
  })

  // Handle POST request
  ->post(function ($args) {
    // Validate post data etc.
    // $data = $args->request->body;

    // If something goes wrong...
    // throw new ApiException('Snap, crackle and pop!');

    // Save user details
    // ...

    // Respond with updated data
    return new Response([
      'first_name' => 'Jerry',
      'last_name' => 'Cotton',
    ]);
  });
```

## Endpoint handler arguments

You can access the following properties via the `$args` parameter of the handler function.

| Property  | Type                     | Description                 |
| --------- | ------------------------ | --------------------------- |
| `request` | `Request`                | [Request object](/requests) |
| `event`   | `\ProcessWire\HookEvent` | ProcessWire URL hook event  |

```php{1}
$this->addEndpoint('/test-request')->get(function ($args) {
  return new Response([
    'request_path' => $args->request->path,
    'request_method' => $args->request->method,
  ]);
});
```

## Dynamic paths

You can use named arguments to allow dynamic paths. Use `$args->request->routeParam()` to access named arguments.

```php{3}
$this->addEndpoint('/products/{product}')->get(function ($args) {
  return new Response([
    'product_name' => $args->request->routeParam('product'),
  ]);
});
```

Querying `/products/bunny-rabbit` results in a following JSON:

```json
{
  "data": {
    "product_name": "bunny-rabbit"
  }
}
```
