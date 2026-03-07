<?php namespace ProcessWire;

// JSON API
use PwJsonApi\{Api, ApiException, Response};
use PwJsonApi\Plugins\{CSRFPlugin};

if (!defined('PROCESSWIRE')) {
  die();
}

if ($page->template->name !== 'admin') {
  (new Api())
    ->configure(function ($config) {
      $config->jsonFlags =
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
    })
    ->setBasePath('/api')
    ->hookBefore(function ($args) {
      if ($args->service instanceof HooksService) {
        $args->endpoint->hookAfter(function ($args) {
          $args->response->with([
            'before_hook_execution_order' => [
              ...$args->response->additionalData[
                'before_hook_execution_order'
              ] ?? [],
              'api',
            ],
          ]);
        });
      }
    })
    ->hookAfter(function ($args) {
      $args->response->with([
        'request' => $args->request->toArray(),
      ]);

      if ($args->service instanceof HooksService) {
        $args->response->with([
          'after_hook_execution_order' => [
            ...$args->response->additionalData['after_hook_execution_order'] ??
            [],
            'api',
          ],
        ]);
      }
    })
    ->hookOnError(function ($args) {
      $args->response->with([
        'request' => $args->request->toArray(),
      ]);
    })
    ->handleException(function ($args) {
      throw (new ApiException())->code(400)->with([
        'message' => $args->exception->getMessage(),
        'request' => $args->request->toArray(),
      ]);
    })
    ->addService(new FoodService(), function ($service) {
      $service->addService(new FruitService());
    })
    ->addService(new PageService())
    ->addService(new HelloWorldService())
    ->addService(new RequestService())
    ->addService(new HooksService())
    ->addService(new ExceptionService())
    ->run();

  (new Api())
    ->setBasePath('exception-response')
    ->handleException(function ($args) {
      return (new Response([
        'handled' => true,
        'message' => $args->exception->getMessage(),
      ]))->code(500);
    })
    ->addService(new ExceptionService())
    ->run();

  (new Api())
    ->setBasePath('plugins')
    ->addPlugin(new TestPlugin())
    ->addService(new RequestService(), function ($service) {
      $service->setBasePath(null);
      $service->addPlugin(new TestPlugin());
      $service->findEndpoint('/')?->addPlugin(new TestPlugin());
    })
    ->run();

  (new Api())
    ->setBasePath('csrf')
    ->addPlugin(new CSRFPlugin(), function ($plugin) {
      // Token name
      $plugin->tokenName = 'pw_json_api_csrf_token';

      // Token key name in responses
      $plugin->tokenKey = 'csrf_token';

      // Endpoint path for retrieving the current token
      $plugin->endpointPath = '/csrf-token';
    })
    ->hookAfter(function ($args) {
      $args->response->with([
        'request' => $args->request->toArray(),
      ]);
    })
    ->hookOnError(function ($args) {
      $args->response->with([
        'request' => $args->request->toArray(),
      ]);
    })
    ->addService(new CSRFService())
    ->run();
}
