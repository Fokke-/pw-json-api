---
description: 'Configure the top-level Api class with base paths, JSON encoding options, and system-wide request hooks.'
---

# API Instance

An API instance holds all the services. You can also define system-wide request hooks, which will apply to every endpoint. [Read more about hooks](/request-hooks).

```php
$api = new Api();
```

## Configuration

Use `configure()` to configure the main instance.

```php
$api->configure(function ($config) {
  // Should endpoint paths end with a trailing slash?
  // (null = no preference)
  $config->trailingSlashes = null;

  // Flags to pass to the json_encode function
  // (default: JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
  $config->jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

  // Optional: pretty-print JSON in debug mode
  // if (wire()->config->debug) {
  //   $config->jsonFlags |= JSON_PRETTY_PRINT;
  // }

  // Use paths exactly as defined, without lowercasing
  // (default: false, will be true in 3.0)
  $config->preservePathCase = false;
});
```

:::tip <Badge type="tip" text="^2.5" />
If a response cannot be encoded as JSON — for example, it contains invalid UTF-8 or a `NAN` value — a `500` response with the error `Failed to encode response` is returned instead. To replace invalid UTF-8 characters instead of failing, add `JSON_INVALID_UTF8_SUBSTITUTE` to `jsonFlags`.
:::

## Base path

All endpoint paths will be prefixed with the base path. You can set a custom base path with `setBasePath()`. The default value is `/`.

::: warning
Always set a base path. Without one, endpoint paths may collide with ProcessWire page URLs — in which case the page takes priority and the endpoint becomes unreachable.
:::

```php
$api->setBasePath('/api');
```

## Adding a service

Call `addService()` to attach a service to the API.

```php
$api->addService(new HelloWorldService());
```

The newly added service can be accessed in an optional setup function. This can be used to reconfigure the service, add child services, define hooks, etc.

```php
$api->addService(new HelloWorldService(), function ($service) {
  // Override the default base path
  $service->setBasePath('/greet');

  // Add a child service
  $service->addService(new AnotherService());
});
```

## Running the API

Call `run()` to register all endpoint listeners and start handling requests. This should be called after all services and configuration have been set up.

```php
$api->run();
```

Under the hood, the method:

1. Iterates all registered services and their endpoints
2. Validates that there are no duplicate service names or endpoint paths
3. Registers a [ProcessWire URL hook](https://processwire.com/docs/modules/hooks/#url-hooks-new-in-3-0-173) listener for each endpoint path

::: tip
For a complete overview of the initialization and request handling sequence, see [Application lifecycle](/lifecycle).
:::

### Locking

After `run()` is called, the Api instance, all services, and all endpoints are **locked**. Locked objects reject structural mutations — this includes adding services, endpoints, plugins, hooks, and setting endpoint handlers.

All configuration must happen either in a service's `init()` method or in the `addService()` setup callback.

A `WireException` is thrown if a mutation is attempted after locking.

## Exception handling

Due to the nature of ProcessWire URL hooks, exceptions thrown in hook code cannot be caught in the main program flow. Use `handleException()` to define your own exception handler for other exception types, such as `WireException`.

You can access the following properties via the `$args` parameter of the handler function.

| Property    | Type                     | Description                  |
| ----------- | ------------------------ | ---------------------------- |
| `exception` | `\Throwable`             | Exception                    |
| `request`   | `Request`                | [Request object](/requests)  |
| `user`      | `\ProcessWire\User`      | The current ProcessWire user |
| `event`     | `\ProcessWire\HookEvent` | ProcessWire URL hook event   |
| `endpoint`  | `Endpoint`               | Requested endpoint           |
| `service`   | `Service`                | Requested service            |
| `services`  | `ServiceList`            | List of all parent services  |
| `api`       | `Api`                    | API instance                 |

You need to return either a `Response` or an `ApiException` from the handler.

The handler receives exceptions thrown at any point of the request handling: in the endpoint handler, in request hooks, in authenticators and authorization callbacks, and in plugins. <Badge type="tip" text="^2.5" />

- **`ApiException`** — handled like any other `ApiException`: [error hooks](/error-hooks) are executed and an error response is returned.
- **`Response`** — returned to the client. If the exception was thrown in the endpoint handler, after hooks are executed as usual. Otherwise the response is returned as is.
- **Anything else** — the original exception is passed to ProcessWire. The same happens if no exception handler is defined.

```php
$api->handleException(function ($args) {
  // Handle WireExceptions
  if ($args->exception instanceof WireException) {
    return (new ApiException())->code(500)->with([
      'message' => $args->exception->getMessage(),
    ]);
  }

  return (new ApiException())->code(400)->with([
    'message' => $args->exception->getMessage(),
  ]);
});
```

## Multiple instances

You can create multiple API instances, each with its own configuration, services, and hooks. This can be useful for API versioning.

::: tip
To avoid path clashes, it's highly recommended to set unique base paths for all instances.
:::

```php
$v1 = new Api();
$v1->setBasePath('/v1');
$v1->run();

$v2 = new Api();
$v2->setBasePath('/v2');
$v2->run();
```
