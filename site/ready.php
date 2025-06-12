<?php namespace ProcessWire;

// JSON API
use PwJsonApi\{Api};

if (!defined('PROCESSWIRE')) {
  die();
}

if ($page->template->name !== 'admin') {
  (new Api())
    ->configure(function ($config) {
      $config->trailingSlashes = null;
      $config->jsonFlags =
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
    })
    ->setBasePath('/api')
    ->addService(new FoodService(), function ($service) {
      $service->addService(new FruitService());
      $service->addService(new VegetableService());
    })
    ->addService(new PageService())
    ->addService(new HelloWorldService())
    ->addService(new MethodService())
    ->run();

  // Exception tests
  (new Api())
    ->setBasePath('/exceptions')
    ->addService(new ExceptionService())
    ->run();

  // Exception hook tests
  (new Api())
    ->setBasePath('/exception-hooks')
    ->hookOnError(function ($e) {
      $e->response->with([
        '_error_hook_execution_order' => [
          ...$e->response->additionalData['_error_hook_execution_order'] ?? [],
          'api',
        ],
      ]);
    })
    ->addService(new ExceptionService(), function ($service) {
      $service->hookOnError(function ($e) {
        $e->response->with([
          '_error_hook_execution_order' => [
            ...$e->response->additionalData['_error_hook_execution_order'] ??
            [],
            'service',
          ],
        ]);
      });

      $service->getEndpoint('/')->hookOnError(function ($e) {
        $e->response->with([
          '_error_hook_execution_order' => [
            ...$e->response->additionalData['_error_hook_execution_order'] ??
            [],
            'endpoint',
          ],
        ]);
      });
    })
    ->run();

  // Hook tests
  (new Api())
    ->setBasePath('/hooks')
    ->hookBefore(function ($args) {
      $args->endpoint->hookAfter(function ($args) {
        $args->response->with([
          '_before_hook_execution_order' => [
            ...$args->response->additionalData[
              '_before_hook_execution_order'
            ] ?? [],
            'api',
          ],
        ]);
      });
    })
    ->hookAfter(function ($args) {
      $args->response->with([
        '_after_hook_execution_order' => [
          ...$args->response->additionalData['_after_hook_execution_order'] ??
          [],
          'api',
        ],
      ]);
    })
    ->addService(new HelloWorldService(), function ($service) {
      $service->hookBefore(function ($args) {
        $args->endpoint->hookAfter(function ($args) {
          $args->response->with([
            '_before_hook_execution_order' => [
              ...$args->response->additionalData[
                '_before_hook_execution_order'
              ] ?? [],
              'service',
            ],
          ]);
        });
      });

      $service->hookAfter(function ($args) {
        $args->response->with([
          '_after_hook_execution_order' => [
            ...$args->response->additionalData['_after_hook_execution_order'] ??
            [],
            'service',
          ],
        ]);
      });

      $service
        ->getEndpoint('/hello-world')
        ?->hookBefore(function ($args) {
          $args->endpoint->hookAfter(function ($args) {
            $args->response->with([
              '_before_hook_execution_order' => [
                ...$args->response->additionalData[
                  '_before_hook_execution_order'
                ] ?? [],
                'endpoint',
              ],
            ]);
          });
        })
        ->hookafter(function ($args) {
          $args->response->with([
            '_after_hook_execution_order' => [
              ...$args->response->additionalData[
                '_after_hook_execution_order'
              ] ?? [],
              'endpoint',
            ],
          ]);
        });
    })
    ->run();
}
