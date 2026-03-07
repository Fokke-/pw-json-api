<?php namespace ProcessWire;

use PwJsonApi\Api404Exception;
use PwJsonApi\ApiException;
use PwJsonApi\Service;

class ExceptionService extends Service
{
  public function init()
  {
    $this->setBasePath('/exceptions');

    $this->hookOnError(function ($args) {
      $args->response->with([
        'error_hook_execution_order' => [
          ...$args->response->additionalData['error_hook_execution_order'] ??
          [],
          'service',
        ],
        'error_hook_args' => [
          'type' => get_class($args),
          'request' => get_class($args->request),
          'response' => get_class($args->response),
          'endpoint' => get_class($args->endpoint),
          'service' => get_class($args->service),
          'services' => get_class($args->services),
          'api' => get_class($args->api),
        ],
      ]);
    });

    $this->hookBefore(function ($args) {
      $args->api->hookOnError(function ($args) {
        $args->response->with([
          'error_hook_execution_order' => [
            ...$args->response->additionalData['error_hook_execution_order'] ??
            [],
            'api',
          ],
        ]);
      });
    });

    $this->addEndpoint('/')
      ->get(function () {
        throw new ApiException('This was doomed to fail!');
      })
      ->hookOnError(function ($args) {
        $this->addEndpointExecutionOrder($args);
      });

    $this->addEndpoint('/custom-code')
      ->get(function () {
        throw (new ApiException('This was doomed to fail!'))->code(401);
      })
      ->hookOnError(function ($args) {
        $this->addEndpointExecutionOrder($args);
      });

    $this->addEndpoint('/without-message')
      ->get(function () {
        throw new ApiException();
      })
      ->hookOnError(function ($args) {
        $this->addEndpointExecutionOrder($args);
      });

    $this->addEndpoint('/404')
      ->get(function () {
        throw new Api404Exception();
      })
      ->hookOnError(function ($args) {
        $this->addEndpointExecutionOrder($args);
      });

    $this->addEndpoint('/base-exception')
      ->get(function () {
        throw new \Exception('base-exception');
      })
      ->hookOnError(function ($args) {
        $this->addEndpointExecutionOrder($args);
      });

    $this->addEndpoint('/wire-exception')
      ->get(function () {
        throw new WireException('wire-exception');
      })
      ->hookOnError(function ($args) {
        $this->addEndpointExecutionOrder($args);
      });

    $this->addEndpoint('/response-from-handler')->get(function () {
      throw new \Exception('handled-with-response');
    });

    $this->addEndpoint('/manipulate-response')
      ->get(function () {
        throw new ApiException('wire-exception');
      })
      ->hookOnError(function ($args) {
        $this->addEndpointExecutionOrder($args);
        $args->response->with([
          'error' => 'updated',
        ]);
      });
  }

  protected function addEndpointExecutionOrder(ApiException $args)
  {
    $args->response->with([
      'error_hook_execution_order' => [
        ...$args->response->additionalData['error_hook_execution_order'] ?? [],
        'endpoint',
      ],
    ]);
  }
}
