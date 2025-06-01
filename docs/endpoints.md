# Endpoints

Under the hood endpoint path will be used to create [ProcessWire URL hook](https://processwire.com/blog/posts/pw-3.0.173/#introducing-url-path-hooks). All URL hook features are supported.

Endpoint listener must either return a [`Response`](/responses) object, or throw [`ApiException`](/exceptions) to halt the process and respond with an error.

::: info
By default all request methods are denied, and you must add listeners for all request methods you want to allow. Requests with disallowed methods will be denied with 405 response.
:::

The supported listeners are:

- `get()`
- `post()`
- `head()`
- `put()`
- `delete()`
- `options()`

Like API instance or service, endpoint can also contain request hooks. [Read more about hooks](/hooks).

## Example endpoint

Call `addEndpoint()` in the service constructor and pass `path` you want to listen to.

```php
use PwJsonApi\ApiException;
use function ProcessWire\wire;
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
  ->post(function () {
    // Validate post data etc.
    // $data = wire('input')->post;

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

## Dynamic paths

You can use named arguments or regular expressions to allow dynamic paths. Use `$event` argument of response handler to access named arguments.

```php{3}
$this->addEndpoint('/products/{product}')
  // Use $event->arguments to access named arguments
  ->get(function ($event) {
    return new Response([
      'product_name' => $event->arguments('product'),
    ]);
  });
```
