<?php

namespace PwJsonApi;

use \ProcessWire\{WireException, HookEvent};
use function ProcessWire\wire;

/**
 * ProcessWire JSON API
 *
 * @see https://pwjsonapi.fokke.fi/api-instance.html
 */
class Api
{
  use Utils;
  use HasBasePath;
  use HasServiceList;
  use HasRequestHooks;
  use HasApiSearch;
  use HasPluginList;
  use HasAuthentication;
  use HasAuthorization;
  use HasWire;
  use HasLock;

  /** Configuration */
  private ApiConfig $config;

  /**
   * Exception handler
   *
   * Should return a Response or an ApiException, but as user code it may
   * return anything, so the result is checked at runtime.
   *
   * @var callable(ExceptionHandlerArgs): mixed|null $exceptionHandler
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
    $request = new Request($event);

    /** @var array<string, string> */
    $headers = [];

    // Exception already passed to the exception handler without a result
    $unhandledException = null;

    // Get response from endpoint
    try {
      // On-request hooks
      $onRequestHooks = [
        // API
        ...$this->getRequestHooks(RequestHookKey::OnRequest),

        // Services and endpoint
        ...$result->resolveOnRequestHooks(),
      ];

      if (!empty($onRequestHooks)) {
        $hookReturnOnRequest = new OnRequestHookReturn();
        $hookReturnOnRequest->request = $request;
        $hookReturnOnRequest->user = $this->wire->user;
        $hookReturnOnRequest->event = $event;
        $hookReturnOnRequest->endpoint = $result->endpoint;
        $hookReturnOnRequest->service = $result->service;
        $hookReturnOnRequest->services = $result->endpoint->services;
        $hookReturnOnRequest->api = $this;

        try {
          foreach ($onRequestHooks as $hookFn) {
            call_user_func($hookFn, $hookReturnOnRequest);
          }
        } finally {
          $headers = $hookReturnOnRequest->headers;
        }
      }

      // Handle OPTIONS requests with Allow header
      if ($request->methodEnum === RequestMethod::Options) {
        return (new Response(null))
          ->header(
            'Allow',
            implode(', ', [
              'OPTIONS',
              ...$result->endpoint->getAllowedMethods(),
            ]),
          )
          ->_addHeaders($headers);
      }

      // Reject malformed request body. Deferred until after on-request
      // hooks and OPTIONS handling, so that their headers (e.g. CORS)
      // are included in the error response.
      $request->_assertValidBody();

      // Try to find handler matching the request method
      $handler = $request->methodEnum
        ? $result->endpoint->getHandler($request->methodEnum)
        : null;

      if (empty($handler)) {
        throw (new ApiException())
          ->code(405)
          ->header(
            'Allow',
            implode(', ', [
              'OPTIONS',
              ...$result->endpoint->getAllowedMethods(),
            ]),
          );
      }

      // Authenticate
      $authenticator = $result->resolveAuthenticator($this);

      if ($authenticator !== null) {
        $authenticateArgs = new AuthenticateArgs();
        $authenticateArgs->request = $request;
        $authenticateArgs->user = $this->wire->user;
        $authenticateArgs->event = $event;
        $authenticateArgs->endpoint = $result->endpoint;
        $authenticateArgs->service = $result->service;
        $authenticateArgs->services = $result->endpoint->services;
        $authenticateArgs->api = $this;
        $authenticator->authenticate($authenticateArgs);
      }

      // Authorize
      $authorizers = $result->resolveAuthorizers($this);

      if (!empty($authorizers)) {
        $authorizeArgs = new AuthorizeArgs();
        $authorizeArgs->request = $request;
        $authorizeArgs->user = $this->wire->user;
        $authorizeArgs->event = $event;
        $authorizeArgs->endpoint = $result->endpoint;
        $authorizeArgs->service = $result->service;
        $authorizeArgs->services = $result->endpoint->services;
        $authorizeArgs->api = $this;

        foreach ($authorizers as $authorizeFn) {
          if (call_user_func($authorizeFn, $authorizeArgs) !== true) {
            throw new AuthorizationException();
          }
        }
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
        $hookReturnBefore->user = $this->wire->user;
        $hookReturnBefore->event = $event;
        $hookReturnBefore->handler = $handler;
        $hookReturnBefore->endpoint = $result->endpoint;
        $hookReturnBefore->service = $result->service;
        $hookReturnBefore->services = $result->endpoint->services;
        $hookReturnBefore->api = $this;

        foreach ($beforeHooks as $hookFn) {
          call_user_func($hookFn, $hookReturnBefore);
        }

        $handler = $hookReturnBefore->handler;
      }

      // Get response from handler
      try {
        $handlerArgs = new EndpointHandlerArgs();
        $handlerArgs->request = $request;
        $handlerArgs->user = $this->wire->user;
        $handlerArgs->event = $event;
        $handlerArgs->endpoint = $result->endpoint;
        $handlerArgs->service = $result->service;
        $handlerArgs->services = $result->endpoint->services;
        $handlerArgs->api = $this;

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
        // A Response from the handler continues to after hooks.
        $handled = $this->callExceptionHandler($e, $request, $event, $result);

        if ($handled instanceof Response) {
          $response = $handled;
        } elseif ($handled instanceof ApiException) {
          throw $handled;
        } else {
          $unhandledException = $e;
          throw $e;
        }
      }

      $response->_addHeaders($headers);

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
        $hookReturnAfter->user = $this->wire->user;
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
      $this->runErrorHooks($e, $headers, $request, $event, $result);
      throw $e;
    } catch (\Throwable $e) {
      // Exceptions thrown outside the endpoint handler (hooks,
      // authenticators, authorizers and plugins). A Response from the
      // exception handler is returned as is, without after hooks.
      if ($e === $unhandledException) {
        throw $e;
      }

      $handled = $this->callExceptionHandler($e, $request, $event, $result);

      if ($handled instanceof Response) {
        return $handled->_addHeaders($headers);
      }

      if ($handled instanceof ApiException) {
        $this->runErrorHooks($handled, $headers, $request, $event, $result);
        throw $handled;
      }

      throw $e;
    }

    return $response;
  }

  /**
   * Pass an exception to the custom exception handler, if defined
   *
   * Returns null if there is no handler, or it did not return
   * a Response or an ApiException.
   */
  private function callExceptionHandler(
    \Throwable $exception,
    Request $request,
    HookEvent $event,
    ApiSearchEndpointResult $result,
  ): Response|ApiException|null {
    if (!is_callable($this->exceptionHandler)) {
      return null;
    }

    $exceptionHandlerArgs = new ExceptionHandlerArgs();
    $exceptionHandlerArgs->exception = $exception;
    $exceptionHandlerArgs->request = $request;
    $exceptionHandlerArgs->user = $this->wire->user;
    $exceptionHandlerArgs->event = $event;
    $exceptionHandlerArgs->endpoint = $result->endpoint;
    $exceptionHandlerArgs->service = $result->service;
    $exceptionHandlerArgs->services = $result->endpoint->services;
    $exceptionHandlerArgs->api = $this;

    $handled = call_user_func($this->exceptionHandler, $exceptionHandlerArgs);

    return $handled instanceof Response || $handled instanceof ApiException
      ? $handled
      : null;
  }

  /**
   * Add on-request headers to the error response and run error hooks
   *
   * @param array<string, string> $headers
   */
  private function runErrorHooks(
    ApiException $exception,
    array $headers,
    Request $request,
    HookEvent $event,
    ApiSearchEndpointResult $result,
  ): void {
    $exception->response->_addHeaders($headers);

    $errorHooks = [
      // Endpoint with services
      ...$result->resolveErrorHooks(),

      // API
      ...$this->getRequestHooks(RequestHookKey::OnError),
    ];

    if (empty($errorHooks)) {
      return;
    }

    $hookReturnError = new ErrorHookReturn();
    $hookReturnError->request = $request;
    $hookReturnError->user = $this->wire->user;
    $hookReturnError->event = $event;
    $hookReturnError->endpoint = $result->endpoint;
    $hookReturnError->service = $result->service;
    $hookReturnError->services = $result->endpoint->services;
    $hookReturnError->api = $this;
    $hookReturnError->exception = $exception;
    $hookReturnError->response = $exception->response;

    foreach ($errorHooks as $hookFn) {
      call_user_func($hookFn, $hookReturnError);
    }
  }

  /**
   * Run API
   *
   * - Resolves all services and endpoints and creates listeners for them
   * - Catches ApiExceptions and renders errors as JSON
   */
  final public function run(): void
  {
    $this->_initPlugins();
    $this->_lock();

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
        $result->service->_lock();
        continue;
      }

      $path = (function () use ($result) {
        $preserve = $this->config->preservePathCase;
        $out = $result->resolvePath($this->_getBasePath($preserve), $preserve);

        // Lowercasing changes the path
        if (
          !$preserve &&
          $out !== $result->resolvePath($this->_getBasePath(true), true)
        ) {
          // Suppressed with @ so the notice never ends up in the response
          // output. Error handlers and deprecation tooling still receive it.
          @trigger_error(
            "Endpoint path '{$out}' contains uppercase characters, which are lowercased. Set the preservePathCase option to true to use paths as defined. This will be the default in 3.0.",
            E_USER_DEPRECATED,
          );
        }

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
      $this->wire->addHook($path, function (HookEvent $event) use ($result) {
        try {
          $this->send($this->handleRequest($result, $event), true);
        } catch (ApiException $e) {
          $this->send($e->response, false);
        }
      });
    }
  }

  /**
   * Output response as JSON and end the request
   *
   * If the response cannot be encoded as JSON, a 500 error is sent instead.
   */
  private function send(Response $response, bool $withData): never
  {
    $code = $response->code;

    try {
      $body = (string) $response->toJson(
        $this->config->jsonFlags | JSON_THROW_ON_ERROR,
        $withData,
      );
    } catch (\JsonException) {
      $code = 500;
      $body = (string) json_encode(
        ['error' => 'Failed to encode response'],
        $this->config->jsonFlags,
      );
    }

    $response->_addHeaders(['Content-Type' => 'application/json']);

    foreach ($response->getHeaders() as $name => $value) {
      header("{$name}: {$value}");
    }
    http_response_code($code);
    echo $body;
    die();
  }
}
