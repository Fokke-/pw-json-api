<?php namespace ProcessWire;

use PwJsonApi\{ApiException, Service, Response};

class HooksService extends Service
{
  public function init()
  {
    $this->setBasePath('/hooks');

    $this->hookBefore(function ($beforeArgs) {
      $beforeArgs->endpoint->hookAfter(function ($afterArgs) use ($beforeArgs) {
        $afterArgs->response->with([
          'before_hook_execution_order' => [
            ...$afterArgs->response->additionalData[
              'before_hook_execution_order'
            ] ?? [],
            'service',
          ],
          'before_hook_args' => [
            'type' => get_class($beforeArgs),
            'request' => get_class($beforeArgs->request),
            'endpoint' => get_class($beforeArgs->endpoint),
            'service' => get_class($beforeArgs->service),
            'services' => get_class($beforeArgs->services),
            'api' => get_class($beforeArgs->api),
            'handler' => gettype($beforeArgs->handler),
          ],
        ]);
      });

      $beforeArgs->api->hookAfter(function ($args) {
        $args->response->with([
          'after_hook_execution_order' => [
            ...$args->response->additionalData['after_hook_execution_order'] ??
            [],
            'api',
          ],
        ]);
      });

      $beforeArgs->api->hookOnError(function ($args) {
        $args->response->with([
          'error_hook_execution_order' => [
            ...$args->response->additionalData['error_hook_execution_order'] ??
            [],
            'api',
          ],
        ]);
      });
    });

    $this->hookOnError(function ($args) {
      $args->response->with([
        'error_hook_execution_order' => [
          ...$args->response->additionalData['error_hook_execution_order'] ??
          [],
          'service',
        ],
      ]);
    });

    $this->hookAfter(function ($args) {
      $args->response->with([
        'after_hook_execution_order' => [
          ...$args->response->additionalData['after_hook_execution_order'] ??
          [],
          'service',
        ],
        'after_hook_args' => [
          'type' => get_class($args),
          'request' => get_class($args->request),
          'endpoint' => get_class($args->endpoint),
          'service' => get_class($args->service),
          'services' => get_class($args->services),
          'api' => get_class($args->api),
          'response' => get_class($args->response),
        ],
      ]);
    });

    $this->addEndpoint('/')
      ->get(function () {
        return new Response([
          'hello' => 'world',
        ]);
      })
      ->hookBefore(function ($args) {
        $args->endpoint->hookAfter(function ($args) {
          $args->response->with([
            'before_hook_execution_order' => [
              ...$args->response->additionalData[
                'before_hook_execution_order'
              ] ?? [],
              'endpoint',
            ],
          ]);
        });
      })
      ->hookAfter(function ($args) {
        $args->response->with([
          'after_hook_execution_order' => [
            ...$args->response->additionalData['after_hook_execution_order'] ??
            [],
            'endpoint',
          ],
        ]);
      });

    $this->addService(new HooksChildService());

    $this->addEndpoint('method-specific')
      ->get(function () {
        return new Response([
          'method' => 'GET',
        ]);
      })
      ->post(function () {
        return new Response([
          'method' => 'POST',
        ]);
      })
      ->hookBeforeGet(function ($args) {
        $args->endpoint->hookAfter(function ($args) {
          $args->response->with([
            'hook_before_get_fired' => true,
          ]);
        });
      });

    $this->addEndpoint('manipulate-response')
      ->get(function ($args) {
        return new Response([
          'foo' => 'foo',
          'fruits' => ['apple', 'orange'],
        ]);
      })
      ->hookAfter(function ($args) {
        $args->response->data['foo'] = 'bar';
        $args->response->data['fruits'][] = 'banana';
      });
  }
}
