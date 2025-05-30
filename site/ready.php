<?php namespace ProcessWire;

if (!defined('PROCESSWIRE')) {
  die();
}

// JSON API
use PwJsonApi\{Api, Config};

if ($page->template->name !== 'admin') {
  $api = new Api(Config::Debug);
  $api->setBasePath('/api');

  $api->hookAfter(function ($args) {
    $test[] = 'api';

    $args->response->with([
      '_after_hook_execution_order' => [
        ...$args->response->withData['_after_hook_execution_order'] ?? [],
        'api',
      ],
    ]);
  });

  $api->addService(new FoodService(), function ($service) {
    $service->hookAfter(function ($args) {
      $args->response->with([
        '_after_hook_execution_order' => [
          ...$args->response->withData['_after_hook_execution_order'] ?? [],
          'service',
        ],
      ]);
    });

    $service->getEndpoint('/')->hookafter(function ($args) {
      $args->response->with([
        '_after_hook_execution_order' => [
          ...$args->response->withData['_after_hook_execution_order'] ?? [],
          'endpoint',
        ],
      ]);
    });

    $service->addService(new FruitService());
    $service->addService(new VegetableService());
  });

  $api->addService(new ExceptionService());

  $api->run();
}
