# Error hooks

Like request hooks, error hooks can be used to modify the response when an `ApiException` is thrown. The behavior of error hooks is similar, except that there are no 'before' or 'after' timings.

The entire `ApiException` object will be passed to the hook handler function, with [hook arguments](#error-hook-arguments) injected. [The hook execution order](request-hooks.html#hook-execution-order) remains the same.

## Error hook scopes

### API error hooks

Defined for the whole API instance. These hooks will apply to all errors.

```php
// For 401 errors, provide login URL
$api->hookOnError(function ($e) {
  if ($e->response->code === 401) {
    $e->response->with([
      'login_url' => 'https://example.com/login',
    ]);
  }
});
```

### Service error hooks

Defined for a single service branch. These hooks will apply to all errors thrown in endpoints within the given service (including child services). Service hooks can be defined directly in the service constructor or injected into the service object.

#### Define in service constructor

```php
$this->hookOnError(function ($e) {
  if ($e->response->code === 401) {
    $e->response->with([
      'login_url' => 'https://example.com/login',
    ]);
  }
});
```

#### Inject in addService() callback

```php
$api->addService(new HelloWorldService(), function ($service) {
  $service->hookOnError(function ($e) {
    if ($e->response->code === 401) {
      $e->response->with([
        'login_url' => 'https://example.com/login',
      ]);
    }
  });
});
```

#### Find installed service and inject

```php
$api->findService('HelloWorldService')?->hookOnError(function ($e) {
  if ($e->response->code === 401) {
    $e->response->with([
      'login_url' => 'https://example.com/login',
    ]);
  }
});
```

### Endpoint error hooks

Defined for a single endpoint. Endpoint hooks can be defined directly when creating an endpoint, or they can be injected into the endpoint object.

#### Define directly in endpoint

```php
$this->addEndpoint('/hello-world')
  ->get(function () {
    return new Response([
      'hello' => 'world',
    ]);
  })
  ->hookOnError(function ($e) {
    $e->response->data['_foo'] = 'foo';
  });
```

#### Find existing endpoint and inject

```php
$api->findEndpoint('/api/hello-world')?->hookOnError(function ($e) {
  $e->response->data['_foo'] = 'foo';
});
```

## Error hook arguments

You can access hook arguments via the `$e` parameter of the hook handler function.

| Property       | Type                     | Description                 |
| -------------- | ------------------------ | --------------------------- |
| `$e->response` | `Response`               | Error Response object       |
| `$e->event`    | `\ProcessWire\HookEvent` | ProcessWire URL hook event  |
| `$e->method`   | `string`                 | Request method              |
| `$e->endpoint` | `Endpoint`               | Requested endpoint          |
| `$e->service`  | `Service`                | Requested service           |
| `$e->services` | `ServiceList`            | List of all parent services |
