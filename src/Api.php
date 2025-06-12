<?php

namespace PwJsonApi;

use \ProcessWire\{WireException};
use function ProcessWire\wire;

/**
 * ProcessWire JSON API
 */
class Api
{
  use Utils;
  use HasBasePath;
  use HasServiceList;
  use HasRequestHooks;
  use HasApiSearch;
  use HasWire;

  /** Configuration */
  private ApiConfig $config;

  /**
   * Create a new API instance
   */
  public function __construct()
  {
    $this->config = new ApiConfig();
    $this->services = new ServiceList();
    $this->hooks = new RequestHooks();
    $this->wire = wire();
  }

  /**
   * Configure API instance
   *
   * @param callable(ApiConfig): void $configure Configuration function
   */
  public function configure(callable|null $configure = null): static
  {
    if (is_callable($configure)) {
      call_user_func($configure, $this->config);
    }

    return $this;
  }

  /**
   * Get the current request method
   */
  protected function getRequestMethod(): ?RequestMethod
  {
    return RequestMethod::tryFrom($_SERVER['REQUEST_METHOD'] ?? '');
  }

  /**
   * Handle request
   */
  protected function handleRequest(
    ApiSearchEndpointResult $result,
    \ProcessWire\HookEvent $event,
  ): Response {
    // Resolve request method
    $requestMethod = $this->getRequestMethod();
    if (empty($requestMethod)) {
      throw (new ApiException())->code(405);
    }

    // Try to find handler matching the request method.
    // If found, get response from handler.
    $handler = $result->endpoint->getHandler($requestMethod);
    if (empty($handler)) {
      throw (new ApiException())->code(405);
    }

    // Before hooks
    $beforeHooks = [
      // API before
      ...$this->findRequestHooks(HookTiming::Before),

      // API before by request method
      ...$this->findRequestHooks(HookTiming::Before, $requestMethod),

      // Endpoint with services
      ...$result->resolveHooks(HookTiming::Before, $requestMethod),
    ];

    if (!empty($beforeHooks)) {
      $hookReturnBefore = new RequestHookReturnBefore();
      $hookReturnBefore->event = $event;
      $hookReturnBefore->handler = $handler;
      $hookReturnBefore->method = $requestMethod->value;
      $hookReturnBefore->endpoint = $result->endpoint;
      $hookReturnBefore->service = $result->service;
      $hookReturnBefore->services = $result->endpoint->services;

      foreach ($beforeHooks as $hookFn) {
        call_user_func($hookFn, $hookReturnBefore);
      }
    }

    // Get response from endpoint
    $response = (function () use ($handler, $event, $requestMethod, $result) {
      try {
        $out = call_user_func($handler, $event);
        if (empty($out)) {
          return new Response();
        }

        if (!($out instanceof Response)) {
          throw new WireException('Malformed result', 500);
        }
      } catch (ApiException $e) {
        // Inject request data to the exception
        $e->event = $event;
        $e->method = $requestMethod->value;
        $e->endpoint = $result->endpoint;
        $e->service = $result->service;
        $e->services = $result->endpoint->services;

        // Error hooks
        $errorHooks = [
          // Endpoint with services
          ...$result->resolveErrorHooks(),

          // API
          ...$this->getRequestHooks(RequestHookKey::OnError),
        ];

        if (!empty($errorHooks)) {
          foreach ($errorHooks as $hookFn) {
            call_user_func($hookFn, $e);
          }
        }

        throw $e;
      }

      return $out;
    })();

    // After hooks
    $afterHooks = [
      // Endpoint with services
      ...$result->resolveHooks(HookTiming::After, $requestMethod),

      // API before
      ...$this->findRequestHooks(HookTiming::After),

      // API before by request method
      ...$this->findRequestHooks(HookTiming::After, $requestMethod),
    ];

    if (!empty($afterHooks)) {
      $hookReturnAfter = new RequestHookReturnAfter();
      $hookReturnAfter->event = $event;
      $hookReturnAfter->response = $response;
      $hookReturnAfter->method = $requestMethod->value;
      $hookReturnAfter->endpoint = $result->endpoint;
      $hookReturnAfter->service = $result->service;
      $hookReturnAfter->services = $result->endpoint->services;

      foreach ($afterHooks as $hookFn) {
        call_user_func($hookFn, $hookReturnAfter);
      }
    }

    return $response;
  }

  /**
   * Run API
   *
   * - Resolves all services and endpoints and creates listeners for them
   * - Catches ApiExceptions and renders errors as JSON
   *
   * Note that this method will NOT catch any other exceptions, such as WireExceptions.
   */
  public function run(): void
  {
    // Special handling for OPTIONS requests.
    // To avoid false positives with CORS errors, always return 200,
    // regardless of the path.
    if ($this->getRequestMethod() === RequestMethod::Options) {
      $response = new Response();

      header('Content-Type: application/json');
      http_response_code($response->code);
      die($response->toJson($this->config->jsonFlags, false));
    }

    /** @var string[] */
    $serviceNames = [];

    /** @var string[] */
    $paths = [];

    $search = new ApiSearch();

    foreach ($search->iterate($this->getServices(), $this->hooks) as $result) {
      /** @var ApiSearchServiceResult|ApiSearchEndpointResult $result */

      // Prepare service
      if ($result instanceof ApiSearchServiceResult) {
        // Check for duplicated service
        if (in_array($result->service->name, $serviceNames)) {
          throw new \ProcessWire\WireException(
            "Duplicated service '{$result->service->name}'",
          );
        }

        $serviceNames[] = $result->service->name;

        // Inject API instance to the service
        $result->service->_setApi($this);
      }

      // Prepare endpoint
      if ($result instanceof ApiSearchEndpointResult) {
        // Resolve endpoint path
        $path = (function () use ($result) {
          $out = $result->resolvePath($this->getBasePath());

          if ($this->config->trailingSlashes === true) {
            return $out . '/';
          } elseif ($this->config->trailingSlashes === null) {
            return $out . '/?';
          }

          return $out;
        })();

        // Check for duplicated path
        if (in_array($path, $paths)) {
          throw new WireException(
            "Duplicated endpoint path '{$path}' (defined in service '{$result->service->name}').",
          );
        }

        $paths[] = $path;

        // Inject service list to the endpoint
        foreach ($result->serviceSequence as $service) {
          $result->endpoint->services->add($service);
        }

        // Listen to path
        $this->wire->addHook($path, function (
          \ProcessWire\HookEvent $event,
        ) use ($result) {
          header('Content-Type: application/json');

          try {
            $response = $this->handleRequest($result, $event);

            http_response_code($response->code);
            echo $response->toJson($this->config->jsonFlags);
          } catch (ApiException $e) {
            // Output error
            http_response_code($e->response->code);
            echo $e->response->toJson($this->config->jsonFlags, false);
          }

          die();
        });
      }
    }
  }
}
