<?php namespace ProcessWire;

use PwJsonApi\{ApiException, Service, Response};

class HooksChildService extends Service
{
  public function init()
  {
    $this->setBasePath('/nested');

    $this->hookBefore(function ($beforeArgs) {
      $beforeArgs->endpoint->hookAfter(function ($afterArgs) {
        $afterArgs->response->with([
          'before_hook_execution_order' => [
            ...$afterArgs->response->additionalData[
              'before_hook_execution_order'
            ] ?? [],
            'child-service',
          ],
        ]);
      });
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
