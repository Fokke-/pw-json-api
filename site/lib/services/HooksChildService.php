<?php namespace ProcessWire;

use PwJsonApi\{ApiException, Service, Response};

class HooksChildService extends Service
{
  protected function init()
  {
    $this->setBasePath('/nested');

    $this->hookBefore(function ($beforeArgs) {
      HooksService::$beforeOrder[] = 'child-service';
    });

    $this->hookAfter(function ($args) {
      $args->response->with([
        'after_hook_execution_order' => [
          ...$args->response->additionalData['after_hook_execution_order'] ??
          [],
          'child-service',
        ],
      ]);
    });

    $this->hookOnError(function ($args) {
      $args->response->with([
        'error_hook_execution_order' => [
          ...$args->response->additionalData['error_hook_execution_order'] ??
          [],
          'child-service',
        ],
      ]);
    });

    $this->addEndpoint('/')
      ->get(function () {
        return new Response([
          'hello' => 'nested',
        ]);
      })
      ->hookBefore(function ($args) {
        HooksService::$beforeOrder[] = 'endpoint';
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

    $this->addEndpoint('/error')
      ->get(function () {
        throw new ApiException('Nested error!');
      })
      ->hookOnError(function ($args) {
        $args->response->with([
          'error_hook_execution_order' => [
            ...$args->response->additionalData['error_hook_execution_order'] ??
            [],
            'endpoint',
          ],
        ]);
      });
  }
}
