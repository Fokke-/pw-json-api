# Services

Services are used to group endpoints. Since services are classes, they can have properties and methods.

Service can define any number of [endpoints](/endpoints), and services may also contain child services, which will inherit hooks from the parent service.

You can also define service-wide request hooks, which will apply to every endpoint of the service and it's child services. [Read more about hooks](/hooks).

## Example service

Create a new service by extending `Service` class. In the constructor you can define one or more endpoints with handlers for different request methods. [Read more about endpoints](/endpoints).

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

Like the main instance, the service can also define it's base path in constructor.

```php
$this->setBasePath('/greet');
```

## Child services

In the most cases a flat service tree is enough, but for bigger API's you can define child services to further categorise the endpoints. Child services will inherit all hooks from parent service(s), and base paths of the parent services will apply.

Child services can be defined in service constructor by calling `addService`.

```php
// In service constructor
$this->addService(new AnotherService());
```

This relationship can be also defined in fly while adding the service to the main instance, allowing to nest services which normally would not be related to each other. [See example of adding child service in fly](api-instance.html#adding-a-service).

## Accessing main instance from the service

Reference to the main instance can be accessed in `api` property of the service. Methods and properties of other services can be accessed through this property.

::: warning
Main instance will be injected to the service after `run()` has been called. Therefore you cannot access `api` in the service constructor.
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
        ...$anotherService->gimmeGimme(),
      ]);
    });
  }
}
```
