# API Instance

An API instance holds all the services. You can also define system-wide request hooks, which will apply to every endpoint. [Read more about hooks](/request-hooks).

```php
$api = new Api();
```

## Configuration

Use `configure()` method to configure the main instance.

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
