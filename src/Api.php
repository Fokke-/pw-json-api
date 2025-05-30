<?php

// TODO: getservice needs to be recursive!
// TODO: move getEndpoint to HasService list and search recursively
// TODO: getendpoints should return endpoints from subservices
// TODO: getendpoint should return result object
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

  /** Configuration */
  private ApiConfig $config;

  /**
   * Create a new API instance
   *
   * @param callable(ApiConfig): void $configure Configuration function
   */
  public function __construct(callable|null $configure = null)
  {
    $this->config = new ApiConfig();

    if (is_callable($configure)) {
      call_user_func($configure, $this->config);
    }
  }

  protected function handleRequest(
    EndpointCrawlerResult $result,
    \ProcessWire\HookEvent $event
  ): Response {
    // Try to find handler matching the request method.
    // If found, get response from handler.
    $requestMethod = RequestMethod::tryFrom(wire()->input->requestMethod());
    $handler = $result->endpoint->getHandler($requestMethod);

    if (empty($requestMethod) || empty($handler)) {
      throw new ApiException('Method not allowed', 405);
    }

    // Inject service list to the endpoint
    foreach ($result->serviceSequence as $service) {
      $result->endpoint->services->add($service);
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
    $response = (function () use ($handler, $event) {
      $out = call_user_func($handler, $event);
      if (empty($out)) {
        return new Response();
      }

      if (!($out instanceof Response)) {
        throw new WireException('Malformed result', 500);
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
    /** Previous installed service. Used in duplicate checking. */
    $prevServiceName = null;

    /** @var string[] */
    $serviceNames = [];

    /** @var string[] */
    $paths = [];

    $crawler = new EndpointCrawler();

    foreach ($crawler->crawl($this->getServices(), $this->hooks) as $result) {
      /** @var EndpointCrawlerResult $result */

      // Check for duplicated service name
      if ($prevServiceName !== $result->service->name) {
        if (in_array($result->service->name, $serviceNames)) {
          throw new \ProcessWire\WireException(
            "Duplicated service '{$result->service->name}'"
          );
        }

        $serviceNames[] = $result->service->name;
        $prevServiceName = $result->service->name;

        // Inject API instance to the service
        $result->service->_setApi($this);
      }

      // Resolve endpoint path
      $path = $result->resolvePath($this->getBasePath()) . '/?';

      // Check for duplicated path
      if (in_array($path, $paths)) {
        throw new WireException(
          "Duplicated endpoint path '{$path}' (defined in service '{$result->service->name}')."
        );
      }

      $paths[] = $path;

      // Listen to path
      wire()->addHook($path, function (\ProcessWire\HookEvent $event) use (
        $result
      ) {
        header('Content-Type: application/json');

        try {
          $response = $this->handleRequest($result, $event);

          http_response_code($response->code);
          echo $response->toJson($this->config->jsonFlags);
        } catch (ApiException $e) {
          // Output error
          http_response_code($e->getCode());
          echo $e->toResponse()->toJson($this->config->jsonFlags, false);
        }

        die();
      });
    }
  }
}
