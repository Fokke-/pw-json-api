<?php

namespace PwJsonApi;

use \ProcessWire\{WireException, HookEvent};
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
  use HasPluginList;
  use HasWire;
  use HasLock;

  /** Configuration */
  private ApiConfig $config;

  /**
   * Exception handler
   *
   * @var callable(ExceptionHandlerArgs): (Response|ApiException)|null $exceptionHandler
   */
  private $exceptionHandler = null;

  /**
   * Create a new API instance
   */
  public function __construct()
  {
    $this->config = new ApiConfig();

    /** @var \ProcessWire\ProcessWire */
    $wire = wire();
    $this->wire = $wire;
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
   * Set exception handler
   *
   * Due to the nature of ProcessWire URL hooks, exceptions thrown in hook code
   * cannot be caught in the main program flow. Use this method to define your own
   * exception handler for other exception types, such as WireException.
   *
   * The API instance will handle all exceptions of type ApiException automatically,
   * so there is no need to implement custom handling for them.
   *
   * @param callable(ExceptionHandlerArgs): (Response|ApiException) $handler Handler function
   */
  public function handleException(callable $handler): static
  {
    $this->exceptionHandler = $handler;
    return $this;
  }

  /**
   * Handle request
   */
  protected function handleRequest(
    ApiSearchEndpointResult $result,
    HookEvent $event,
  ): Response {
    $request = new Request();

    // Get response from endpoint
    try {
      $request->_init($event);

      // Try to find handler matching the request method
      $handler = $request->methodEnum
        ? $result->endpoint->getHandler($request->methodEnum)
        : null;

      if (empty($handler)) {
        header(
          'Allow: ' .
            implode(', ', [
              'OPTIONS',
              ...$result->endpoint->getAllowedMethods(),
            ]),
        );
        throw (new ApiException())->code(405);
      }

      // Before hooks
      $beforeHooks = [
        // API
        ...$this->findRequestHooks(HookTiming::Before),

        // API by request method
        ...$this->findRequestHooks(HookTiming::Before, $request->methodEnum),

        // Endpoint with services
        ...$result->resolveHooks(HookTiming::Before, $request->methodEnum),
      ];

      if (!empty($beforeHooks)) {
        $hookReturnBefore = new RequestHookReturnBefore();
        $hookReturnBefore->request = $request;
        $hookReturnBefore->event = $event;
        $hookReturnBefore->handler = $handler;
        $hookReturnBefore->endpoint = $result->endpoint;
        $hookReturnBefore->service = $result->service;
        $hookReturnBefore->services = $result->endpoint->services;
        $hookReturnBefore->api = $this;

        foreach ($beforeHooks as $hookFn) {
          call_user_func($hookFn, $hookReturnBefore);
        }
      }

      // Get response from handler
      try {
        $handlerArgs = new EndpointHandlerArgs();
        $handlerArgs->request = $request;
        $handlerArgs->event = $event;

        $response = call_user_func($handler, $handlerArgs);
        if (!($response instanceof Response)) {
          throw new WireException(
            'Malformed result. You must return a Response object from the handler.',
            500,
          );
        }
      } catch (\Throwable $e) {
        // Pass ApiExceptions through
        if ($e instanceof ApiException) {
          throw $e;
        }

        // For other exception types, try to get response
        // from custom exception handler function.
        if (is_callable($this->exceptionHandler)) {
          $exceptionHandlerArgs = new ExceptionHandlerArgs();
          $exceptionHandlerArgs->exception = $e;
          $exceptionHandlerArgs->request = $request;
          $exceptionHandlerArgs->event = $event;
          $exceptionHandlerArgs->endpoint = $result->endpoint;
          $exceptionHandlerArgs->service = $result->service;
          $exceptionHandlerArgs->services = $result->endpoint->services;
          $exceptionHandlerArgs->api = $this;

          $exceptionHandlerResult = call_user_func(
            $this->exceptionHandler,
            $exceptionHandlerArgs,
          );

          if ($exceptionHandlerResult instanceof Response) {
            $response = $exceptionHandlerResult;
          } elseif ($exceptionHandlerResult instanceof ApiException) {
            throw $exceptionHandlerResult;
          } else {
            throw $e;
          }
        } else {
          throw $e;
        }
      }

      // After hooks
      $afterHooks = [
        // Endpoint with services
        ...$result->resolveHooks(HookTiming::After, $request->methodEnum),

        // API by request method
        ...$this->findRequestHooks(HookTiming::After, $request->methodEnum),

        // API
        ...$this->findRequestHooks(HookTiming::After),
      ];

      if (!empty($afterHooks)) {
        $hookReturnAfter = new RequestHookReturnAfter();
        $hookReturnAfter->request = $request;
        $hookReturnAfter->event = $event;
        $hookReturnAfter->response = $response;
        $hookReturnAfter->endpoint = $result->endpoint;
        $hookReturnAfter->service = $result->service;
        $hookReturnAfter->services = $result->endpoint->services;
        $hookReturnAfter->api = $this;

        foreach ($afterHooks as $hookFn) {
          call_user_func($hookFn, $hookReturnAfter);
        }
      }
    } catch (ApiException $e) {
      // Inject request data to the exception
      $e->request = $request;
      $e->event = $event;
      $e->endpoint = $result->endpoint;
      $e->service = $result->service;
      $e->services = $result->endpoint->services;
      $e->api = $this;

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

    return $response;
  }

  /**
   * Run API
   *
   * - Resolves all services and endpoints and creates listeners for them
   * - Catches ApiExceptions and renders errors as JSON
   */
  public function run(): void
  {
    $this->_initPlugins();
    $this->_lock();

    $isOptions = ($_SERVER['REQUEST_METHOD'] ?? null) == 'OPTIONS';

    /** @var string[] */
    $serviceNames = [];

    /** @var string[] */
    $paths = [];

    // Loop all services and endpoints
    foreach ((new ApiSearch())->iterate($this->getServices()) as $result) {
      if ($result instanceof ApiSearchServiceResult) {
        if (in_array($result->service->name, $serviceNames)) {
          throw new WireException(
            "Duplicated service '{$result->service->name}'",
          );
        }
        $serviceNames[] = $result->service->name;
        $result->service->_setApi($this);
        continue;
      }

      $path = (function () use ($result) {
        $out = $result->resolvePath($this->getBasePath());

        if ($this->config->trailingSlashes === null) {
          return $out . '/?';
        }

        if ($this->config->trailingSlashes === true) {
          return $out . '/';
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

      // Inject service sequence to the endpoint
      foreach ($result->serviceSequence as $service) {
        $result->endpoint->services->add($service);
      }

      // Initialize endpoint plugins
      $result->endpoint->_initPlugins();
      $result->endpoint->_lock();

      // Add listener for the endpoint path
      $this->wire->addHook($path, function (HookEvent $event) use (
        $result,
        $isOptions,
      ) {
        // Handle OPTIONS requests with Allow header
        if ($isOptions) {
          $methods = $result->endpoint->getAllowedMethods();
          header('Allow: ' . implode(', ', ['OPTIONS', ...$methods]));

          $response = new Response();

          header('Content-Type: application/json');
          http_response_code($response->code);
          die($response->toJson($this->config->jsonFlags, false));
        }

        try {
          $response = $this->handleRequest($result, $event);

          header('Content-Type: application/json');
          http_response_code($response->code);
          echo $response->toJson($this->config->jsonFlags);
          die();
        } catch (ApiException $e) {
          // Output error
          header('Content-Type: application/json');
          http_response_code($e->response->code);
          echo $e->response->toJson($this->config->jsonFlags, false);
          die();
        }
      });
    }
  }
}
