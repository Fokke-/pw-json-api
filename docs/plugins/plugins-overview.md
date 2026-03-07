# Plugins <Badge type="tip" text="^2.0" />

Plugins are reusable packages of hooks, services, and configuration that can be installed on an API instance, a service, or an endpoint. They provide a way to encapsulate cross-cutting concerns and share them across projects.

## The `ApiPlugin` class

All plugins extend the abstract `ApiPlugin` class. Override the `init()` method to add hooks, services, or any other setup logic to the context where the plugin is installed.

```php
use PwJsonApi\Plugins\ApiPlugin;
use PwJsonApi\{Api, Service, Endpoint};
use ProcessWire\WireException;

class MyPlugin extends ApiPlugin
{
  public function init(Api|Service|Endpoint $context): static
  {
    // Optional: restrict plugin to API-level only
    // if (!($context instanceof Api)) {
    //   throw new WireException('MyPlugin must be installed on Api');
    // }

    parent::init($context);

    $context->hookAfter(function ($args) {
      $args->response->with([
        'plugin_active' => true,
      ]);
    });

    return $this;
  }
}
```

## Installing a plugin

Use the `addPlugin()` method available on `Api`, `Service`, and `Endpoint` instances.

```php
$api->addPlugin(new MyPlugin());
```

An optional setup callback gives you access to the plugin instance for configuration before it is initialized:

```php
$api->addPlugin(new MyPlugin(), function ($plugin) {
  $plugin->someSetting = 'value';
});
```

## Scopes

Plugins can be installed at three different levels:

### API-level

API-level plugins affect the entire API.

```php
$api->addPlugin(new MyPlugin());
```

### Service-level

Service-level plugins affect only the endpoints within that service and its child services.

```php
$api->addService(new MyService(), function ($service) {
  $service->addPlugin(new MyPlugin());
});
```

### Endpoint-level

Endpoint-level plugins affect a single endpoint.

```php
$service->findEndpoint('/')?->addPlugin(new MyPlugin());
```
