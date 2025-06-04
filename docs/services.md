# Services

Services are used to group endpoints. Since services are classes, they can have properties and methods.

A service can define any number of [endpoints](/endpoints), and services may also contain child services, which will inherit hooks from the parent service.

You can also define service-wide request hooks, which will apply to every endpoint of the service and its child services. [Read more about hooks](/request-hooks).

## Example service

Create a new service by extending the `Service` class. In the constructor, you can define one or more endpoints with handlers for different request methods. [Read more about endpoints](/endpoints).

```php
<?php namespace ProcessWire;

use PwJsonApi\{Service, Response};

class HelloWorldService extends Service
{
  protected string $word = 'world';

  public function __construct()
  {
    parent::__construct();

    $this->setBasePath('/greet')

    // Listen to the base path (/greet)
    $this->addEndpoint('/')->get(function () {
      return new Response()->with([
        'message' => 'Nothing to see here!',
      ]);
    });

    // Listen to path /greet/hello-world with GET handler
    $this->addEndpoint('/hello-world')->get(function () {
      return new Response($this->getData());
    });
  }

  public function getData(): array
  {
    return [
      'hello' => $this->word,
    ];
  }
}
```

## Base path

Like the main instance, the service can also define its base path in the constructor.

```php
$this->setBasePath('/greet');
```

## Child services

In most cases, a flat service tree is enough, but for larger APIs you can define child services to further categorize the endpoints. Child services will inherit all hooks from parent service(s), and the base paths of the parent services will apply.

Child services can be defined in the service constructor by calling `addService()`.

```php
// In service constructor
$this->addService(new AnotherService());
```

This relationship can also be defined on the fly while adding the service to the main instance, allowing you to nest services that normally would not be related to each other. [See example of adding a child service on the fly](api-instance.html#adding-a-service).

## Accessing the ProcessWire API in the service

Use `wire` property to access ProcessWire API

```php
$page = $this->wire->pages->findOne('template=basic-page');
```

## Accessing the main instance in the service

A reference to the main instance will be injected into the `api` property of the service. You can use this to access methods and properties of other services.

::: warning
The main instance will be injected into the service after `run()` has been called. Therefore, you cannot access `api` directly in the service constructor.
:::

```php
class HelloWorldService extends Service
{
  public function __construct()
  {
    parent::__construct();

    // ❌ Trying to access API too early
    $anotherService = $this->api->findService('AnotherService')

    $this->addEndpoint('/use-another-service')->get(function () {
      // ✅ This will work
      $anotherService = $this->api->findService('AnotherService')

      return new Response([
        'hello' => 'world',
        ...$anotherService?->gimmeGimme(),
      ]);
    });
  }
}
```
