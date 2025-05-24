<?php

namespace PwJsonApi;

use function ProcessWire\wire;

class Api
{
  use Utils;
  use HasBasePath;
  use HasServiceList;
  use HasHooks;

  /** JSON options */
  public const JSON_OPTIONS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

  /** JSON options when debug mode is on */
  public const JSON_OPTIONS_DEBUG =
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;

  /** Is debug mode on? */
  private bool $debug = false;

  /**
   * Create a new API instance
   *
   * @param Config ...$config Configuration arguments
   */
  public function __construct(Config|null ...$config)
  {
    if (in_array(Config::Debug, $config)) {
      $this->debug = true;
    }
  }

  public function run(): void
  {
    /** @var string[] */
    $paths = [];
    foreach ($this->getServices() as $service) {
      // Add listener to each endpoint
      foreach ($service->getEndpoints() as $endpoint) {
        // Build full path for the endpoint
        $path = implode(
          '/',
          array_filter(
            [
              '', // for leading slash
              $this->basePath,
              $service->getBasePath(),
              $endpoint->getPath(),
              '?', // allow access with or without trailing slash
            ],
            fn($segment) => !is_null($segment)
          )
        );

        // Check for duplicated path
        if (in_array($path, $paths)) {
          // TODO: throw critical exception
          throw new \Exception(
            "Duplicated endpoint path: '{$path}' (defined in service {$service->name})."
          );
        }

        $paths[] = $path;

        wire()->addHook($path, function (\ProcessWire\HookEvent $event) use ($service, $endpoint) {
          header('Content-Type: application/json');

          // Try to find handler matching the request method.
          // If found, get response from handler.
          try {
            $requestMethod = RequestMethod::tryFrom(wire()->input->requestMethod());
            $handler = $endpoint->getHandler($requestMethod);

            if (empty($requestMethod) || empty($handler)) {
              throw new ApiException('Method not allowed', 405);
            }

            // Payload for before hooks
            $hookReturnBefore = new HookReturnBefore();
            $hookReturnBefore->event = $event;
            $hookReturnBefore->handler = $handler;
            $hookReturnBefore->method = wire()->input->requestMethod();
            $hookReturnBefore->endpoint = $endpoint;
            $hookReturnBefore->service = $service;

            // Run before hooks
            foreach (
              [
                // Service before by request type
                ...$service->findHooks(HookTiming::Before, $requestMethod),

                // Service before
                ...$service->findHooks(HookTiming::Before),

                // Global before by request type
                ...$this->findHooks(HookTiming::Before, $requestMethod),

                // Global before
                ...$this->findHooks(HookTiming::Before),
              ]
              as $hookFn
            ) {
              call_user_func($hookFn, $hookReturnBefore);
            }

            $response = call_user_func($handler, $event);
            if (!($response instanceof Response)) {
              // TODO: throw critical exception
              throw new ApiException('Malformed result', 500);
            }

            // Payload for after hooks
            $hookReturnAfter = new HookReturnAfter();
            $hookReturnAfter->event = $event;
            $hookReturnAfter->response = $response;
            $hookReturnAfter->method = wire()->input->requestMethod();
            $hookReturnAfter->endpoint = $endpoint;
            $hookReturnAfter->service = $service;

            foreach (
              [
                // Service after by request type
                ...$service->findHooks(HookTiming::After, $requestMethod),

                // Service after
                ...$service->findHooks(HookTiming::After),

                // Global after by request type
                ...$this->findHooks(HookTiming::After, $requestMethod),

                // Global after
                ...$this->findHooks(HookTiming::After),
              ]
              as $hookFn
            ) {
              call_user_func($hookFn, $hookReturnAfter);
            }

            // Output response
            http_response_code($response->code);
            echo $response->toJson(
              $this->debug === true ? self::JSON_OPTIONS_DEBUG : self::JSON_OPTIONS
            );
          } catch (ApiException $e) {
            // Output error
            http_response_code($e->getCode());
            echo $e
              ->toResponse()
              ->toJson(
                $this->debug === true ? self::JSON_OPTIONS_DEBUG : self::JSON_OPTIONS,
                false
              );
          }

          die();
        });
      }
    }
  }
}
