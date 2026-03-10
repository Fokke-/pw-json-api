<?php

namespace PwJsonApi;

/**
 * Single endpoint
 *
 * @see https://pwjsonapi.fokke.fi/endpoints.html
 */
class Endpoint
{
  use Utils;
  use HasRequestHooks;
  use HasPluginList;
  use HasLock;

  /**
   * Endpoint path
   */
  private string|null $path;

  /**
   * Endpoint handlers
   *
   * @var array<string, callable(EndpointHandlerArgs): Response>
   */
  protected array $handlers = [];

  /**
   * Endpoint services. Resolved before endpoint request is being handled.
   */
  public ServiceList $services;

  /**
   * Constructor
   */
  public function __construct(string $path)
  {
    $this->setPath($path);
    $this->handlers = array_reduce(
      RequestMethod::cases(),
      static function (array $acc, \UnitEnum $method) {
        $acc[$method->name] = [];
        return $acc;
      },
      [],
    );
    $this->services = new ServiceList();
  }

  /**
   * Get endpoint path
   */
  public function getPath(): string|null
  {
    return $this->path;
  }

  /**
   * Set endpoint path
   */
  public function setPath(string $path): static
  {
    $this->path = $this->formatPath($path);
    return $this;
  }

  /**
   * Get handler by request method
   */
  public function getHandler(RequestMethod $method): callable|null
  {
    return !empty($this->handlers[$method->name])
      ? $this->handlers[$method->name]
      : null;
  }

  /**
   * Get HTTP methods for which handlers are registered
   *
   * @return string[]
   */
  public function getAllowedMethods(): array
  {
    $methods = [];
    foreach (RequestMethod::cases() as $method) {
      if ($method === RequestMethod::Options) {
        continue;
      }
      if (!empty($this->handlers[$method->name])) {
        $methods[] = $method->value;
      }
    }
    return $methods;
  }

  /**
   * Handle GET requests
   *
   * @param callable(EndpointHandlerArgs): Response $handler
   */
  public function get(callable $handler): static
  {
    $this->_assertNotLocked('set GET handler');
    $this->handlers[RequestMethod::Get->name] = $handler;
    return $this;
  }

  /**
   * Handle POST requests
   *
   * @param callable(EndpointHandlerArgs): Response $handler
   */
  public function post(callable $handler): static
  {
    $this->_assertNotLocked('set POST handler');
    $this->handlers[RequestMethod::Post->name] = $handler;
    return $this;
  }

  /**
   * Handle HEAD requests
   *
   * @param callable(EndpointHandlerArgs): Response $handler
   */
  public function head(callable $handler): static
  {
    $this->_assertNotLocked('set HEAD handler');
    $this->handlers[RequestMethod::Head->name] = $handler;
    return $this;
  }

  /**
   * Handle PUT requests
   *
   * @param callable(EndpointHandlerArgs): Response $handler
   */
  public function put(callable $handler): static
  {
    $this->_assertNotLocked('set PUT handler');
    $this->handlers[RequestMethod::Put->name] = $handler;
    return $this;
  }

  /**
   * Handle PATCH requests
   *
   * @param callable(EndpointHandlerArgs): Response $handler
   */
  public function patch(callable $handler): static
  {
    $this->_assertNotLocked('set PATCH handler');
    $this->handlers[RequestMethod::Patch->name] = $handler;
    return $this;
  }

  /**
   * Handle DELETE requests
   *
   * @param callable(EndpointHandlerArgs): Response $handler
   */
  public function delete(callable $handler): static
  {
    $this->_assertNotLocked('set DELETE handler');
    $this->handlers[RequestMethod::Delete->name] = $handler;
    return $this;
  }
}
