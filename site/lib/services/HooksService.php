<?php namespace ProcessWire;

use PwJsonApi\{ApiException, Service, Response};

class HooksService extends Service
{
  /**
   * Tracks before hook execution order within a single request.
   * Reset per-process (each HTTP request is a new PHP process).
   *
   * @var string[]
   */
  public static array $beforeOrder = [];

  /**
   * Captures before hook arguments for assertion.
   *
   * @var array<string, string>|null
   */
  public static ?array $beforeArgs = null;

  /**
   * Whether hookBeforeGet fired in this request.
   */
  public static bool $hookBeforeGetFired = false;

  public function init()
  {
    $this->setBasePath('/hooks');

    $this->hookBefore(function ($beforeArgs) {
      self::$beforeOrder[] = 'service';
      self::$beforeArgs = [
        'type' => get_class($beforeArgs),
        'request' => get_class($beforeArgs->request),
        'endpoint' => get_class($beforeArgs->endpoint),
        'service' => get_class($beforeArgs->service),
        'services' => get_class($beforeArgs->services),
        'api' => get_class($beforeArgs->api),
        'handler' => gettype($beforeArgs->handler),
      ];
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
        'before_hook_execution_order' => self::$beforeOrder,
        'before_hook_args' => self::$beforeArgs,
      ]);
    });

    $this->addEndpoint('/')
      ->get(function () {
        return new Response([
          'hello' => 'world',
        ]);
      })
      ->hookBefore(function ($args) {
        self::$beforeOrder[] = 'endpoint';
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
        self::$hookBeforeGetFired = true;
      })
      ->hookAfter(function ($args) {
        if (self::$hookBeforeGetFired) {
          $args->response->with([
            'hook_before_get_fired' => true,
          ]);
        }
      });

    $this->addEndpoint('replace-handler')
      ->get(function () {
        return new Response([
          'handler' => 'original',
        ]);
      })
      ->hookBefore(function ($args) {
        $args->handler = function () {
          return new Response([
            'handler' => 'replaced',
          ]);
        };
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
