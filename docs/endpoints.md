# Endpoints

Under the hood, the endpoint path will be used to create a [ProcessWire URL hook](https://processwire.com/blog/posts/pw-3.0.173/#introducing-url-path-hooks). All URL hook features are supported.

Each endpoint listener must either return a [`Response`](/responses) object or throw an [`ApiException`](/error-handling) to halt the process and respond with an error.

::: info
By default, all the request methods are denied, except for `OPTIONS`. You must add listeners for each request method you want to allow. Requests with disallowed methods will be denied with a 405 response.

The `OPTIONS` method is always allowed and will result in an empty response with a status code of `200`, even if the actual endpoint does not exist. This is important to prevent false-positive CORS errors when performing cross-origin requests to non-existent endpoints.
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

Call `addEndpoint()` in the service constructor and pass the `path` you want to listen to.

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
  ->post(function ($request) {
    // Validate post data etc.
    // $data = $this->wire->input->post;

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

## Access request data in handler

Use the `$request` object to access request data. [Read more about requests](/requests).

```php{1}
$this->addEndpoint('/test-request')->get(function ($request) {
  return new Response([
    'request_path' => $request->path,
    'request_method' => $request->method,
  ]);
});
```

## Dynamic paths

You can use named arguments to allow dynamic paths. Use `$request->params()` to access named arguments.

```php{3}
$this->addEndpoint('/products/{product}')->get(function ($request) {
  return new Response([
    'product_name' => $request->routeParam('product'),
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
