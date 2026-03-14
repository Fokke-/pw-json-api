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
  $config->jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
});
```

## Base path

All endpoint paths will be prefixed with the base path. You can set a custom base path with `setBasePath()`. The default value is `/`.

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

### Locking

After `run()` is called, the Api instance, all services, and all endpoints are **locked**. Locked objects reject structural mutations — this includes adding services, endpoints, plugins, hooks, and setting endpoint handlers.

All configuration must happen either in a service's `init()` method or in the `addService()` setup callback.

A `WireException` is thrown if a mutation is attempted after locking.

## Exception handling

Due to the nature of ProcessWire URL hooks, exceptions thrown in hook code cannot be caught in the main program flow. Use `handleException()` to define your own exception handler for other exception types, such as `WireException`.

You can access the following properties via the `$args` parameter of the handler function.

| Property    | Type                     | Description                 |
| ----------- | ------------------------ | --------------------------- |
| `exception` | `\Throwable`             | Exception                   |
| `request`   | `Request`                | [Request object](/requests) |
| `event`     | `\ProcessWire\HookEvent` | ProcessWire URL hook event  |
| `endpoint`  | `Endpoint`               | Requested endpoint          |
| `service`   | `Service`                | Requested service           |
| `services`  | `ServiceList`            | List of all parent services |
| `api`       | `Api`                    | API instance                |

You need to return either a `Response` or an `ApiException` from the handler.

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
