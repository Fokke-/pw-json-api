<?php namespace ProcessWire;

// JSON API
use PwJsonApi\{Api};

if (!defined('PROCESSWIRE')) {
  die();
}

if ($page->template->name !== 'admin') {
  $api = new Api();
  $api->configure(function ($config) {
    $config->trailingSlashes = null;
    $config->jsonFlags =
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
  });
  $api->setBasePath('/api');

  $api->hookAfter(function ($args) {
    $args->response->with([
      '_after_hook_execution_order' => [
        ...$args->response->additionalData['_after_hook_execution_order'] ?? [],
        'api',
      ],
    ]);
  });

  $api->addService(new FoodService(), function ($service) {
    $service->hookAfter(function ($args) {
      $args->response->with([
        '_after_hook_execution_order' => [
          ...$args->response->additionalData['_after_hook_execution_order'] ??
          [],
          'service',
        ],
      ]);
    });

    $service->getEndpoint('/')->hookafter(function ($args) {
      $args->response->with([
        '_after_hook_execution_order' => [
          ...$args->response->additionalData['_after_hook_execution_order'] ??
          [],
          'endpoint',
        ],
      ]);
    });

    $service->addService(new FruitService());
    $service->addService(new VegetableService());
  });

  $api->addService(new PageService());
  $api->addService(new ExceptionService());
  $api->addService(new HelloWorldService());
  $api->addService(new MethodService());
  $api->run();

  // Add second API instance
  $parallelApi = new Api(function ($config) {
    $config->jsonFlags =
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
  });
  $parallelApi->setBasePath('/parallel-api');
  $parallelApi->addService(new HelloWorldService());
  $parallelApi->run();
}
