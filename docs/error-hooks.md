---
description: 'Customize error responses with API-level and service-level error hooks when exceptions are thrown.'
---

# Error hooks

Like request hooks, error hooks can be used to modify the response when an `ApiException` is thrown. The behavior of error hooks is similar, except that there are no 'before' or 'after' timings.

An `ErrorHookReturn` object will be passed to the hook handler function. [The hook execution order](request-hooks.html#hook-execution-order) remains the same.

## Error hook scopes

### API error hooks

Defined for the whole API instance. These hooks will apply to all errors.

```php
// For 401 errors, provide login URL
$api->hookOnError(function ($args) {
  if ($args->response->code === 401) {
    $args->response->with([
      'login_url' => 'https://example.com/login',
    ]);
  }
});
```

### Service error hooks

Defined for a single service branch. These hooks will apply to all errors thrown in endpoints within the given service (including child services). Service hooks can be defined directly in the service `init()` method or injected into the service object.

#### Define in init()

```php
$this->hookOnError(function ($args) {
  if ($args->response->code === 401) {
    $args->response->with([
      'login_url' => 'https://example.com/login',
    ]);
  }
});
```

#### Inject in addService() callback

```php
$api->addService(new HelloWorldService(), function ($service) {
  $service->hookOnError(function ($args) {
    if ($args->response->code === 401) {
      $args->response->with([
        'login_url' => 'https://example.com/login',
      ]);
    }
  });
});
```

#### Find installed service and inject

```php
$api->findService('HelloWorldService')?->hookOnError(function ($args) {
  if ($args->response->code === 401) {
    $args->response->with([
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
  ->hookOnError(function ($args) {
    $args->response->with(['_foo' => 'foo']);
  });
```

#### Find existing endpoint and inject

```php
$api->findEndpoint('/api/hello-world')?->hookOnError(function ($args) {
  $args->response->with(['_foo' => 'foo']);
});
```

## Error hook arguments

You can access the following properties via the `$args` parameter of the handler function.

| Property    | Type                     | Description                 |
| ----------- | ------------------------ | --------------------------- |
| `exception` | `ApiException`           | The thrown exception        |
| `response`  | `Response`               | Error Response object       |
| `request`   | `Request`                | [Request object](/requests) |
| `event`     | `\ProcessWire\HookEvent` | ProcessWire URL hook event  |
| `endpoint`  | `Endpoint`               | Requested endpoint          |
| `service`   | `Service`                | Requested service           |
| `services`  | `ServiceList`            | List of all parent services |
| `api`       | `Api`                    | API instance                |
