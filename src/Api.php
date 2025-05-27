<?php

namespace PwJsonApi;

use \ProcessWire\{WireException};
use function ProcessWire\wire;

class Api
{
  use Utils;
  use HasBasePath;
  use HasServiceList;
  use HasHooks;

  /** Is debug mode on? */
  private bool $debug = false;

  /** Flags to pass to json_encode() */
  public int $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

  /** Flags to pass to json_encode() when debug mode is on */
  public int $jsonFlagsDebug = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;

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

    // Before hooks
    $beforeHooks = [
      // API before
      ...$this->findHooks(HookTiming::Before),

      // API before by request method
      ...$this->findHooks(HookTiming::Before, $requestMethod),

      // Endpoint with services
      ...$result->resolveHooks(HookTiming::Before, $requestMethod),
    ];

    if (!empty($beforeHooks)) {
      $hookReturnBefore = new HookReturnBefore();
      $hookReturnBefore->event = $event;
      $hookReturnBefore->handler = $handler;
      $hookReturnBefore->method = $requestMethod->value;
      $hookReturnBefore->endpoint = $result->endpoint;
      $hookReturnBefore->service = $result->service;

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
      ...$this->findHooks(HookTiming::After),

      // API before by request method
      ...$this->findHooks(HookTiming::After, $requestMethod),
    ];

    if (!empty($afterHooks)) {
      $hookReturnAfter = new HookReturnAfter();
      $hookReturnAfter->event = $event;
      $hookReturnAfter->response = $response;
      $hookReturnAfter->method = $requestMethod->value;
      $hookReturnAfter->endpoint = $result->endpoint;
      $hookReturnAfter->service = $result->service;

      foreach ($afterHooks as $hookFn) {
        call_user_func($hookFn, $hookReturnAfter);
      }
    }

    return $response;
  }

  /**
   * Run API
   *
   * Resolves all services and endpoints and creates listeners for them
   * - Catches ApiExceptions and renders errors as JSON
   *
   * Note that this method will NOT catch any other exceptions, such as WireExceptions.
   */
  public function run(): void
  {
    /** @var string[] */
    $paths = [];

    $crawler = new EndpointCrawler();

    foreach ($crawler->crawl($this->getServices(), $this->hooks) as $result) {
      /** @var EndpointCrawlerResult $result */
      $path = $result->resolvePath($this->getBasePath());

      // Allow access with or without trailing slash
      $path .= '/?';

      // Check for duplicated path
      if (in_array($path, $paths)) {
        throw new WireException(
          "Duplicated endpoint path '{$path}' (defined in service '{$result->service->name}')."
        );
      }

      $paths[] = $path;

      // Listen to path
      wire()->addHook($path, function (\ProcessWire\HookEvent $event) use ($result) {
        $jsonFlags = $this->debug === true ? $this->jsonFlagsDebug : $this->jsonFlags;

        header('Content-Type: application/json');

        try {
          $response = $this->handleRequest($result, $event);

          http_response_code($response->code);
          echo $response->toJson($jsonFlags);
        } catch (ApiException $e) {
          // Output error
          http_response_code($e->getCode());
          echo $e->toResponse()->toJson($jsonFlags, false);
        }

        die();
      });
    }
  }
}
