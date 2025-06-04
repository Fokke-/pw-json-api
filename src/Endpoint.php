<?php

namespace PwJsonApi;

/**
 * Single endpoint
 */
class Endpoint
{
  use Utils;
  use HasRequestHooks;

  /**
   * Endpoint path
   */
  private string|null $path;

  /**
   * Endpoint handlers
   *
   * @var array<string, callable(\ProcessWire\HookEvent): Response>
   */
  protected array $handlers = [];

  /**
   * Endpoint services. Resolved before endpoint request is being handled.
   */
  public ServiceList $services;

  /**
   * Constructor
   */
  public function __construct(string|null $path = null)
  {
    $this->setPath($path);
    $this->handlers = array_reduce(
      RequestMethod::cases(),
      function ($acc, $method) {
        $acc[$method->name] = [];
        return $acc;
      },
      [],
    );
    $this->services = new ServiceList();
    $this->hooks = new RequestHooks();
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
   * Handle GET requests
   *
   * @param callable(\ProcessWire\HookEvent): Response $handler
   */
  public function get(callable $handler): static
  {
    $this->handlers[RequestMethod::Get->name] = $handler;
    return $this;
  }

  /**
   * Handle POST requests
   *
   * @param callable(\ProcessWire\HookEvent): Response $handler
   */
  public function post(callable $handler): static
  {
    $this->handlers[RequestMethod::Post->name] = $handler;
    return $this;
  }

  /**
   * Handle HEAD requests
   *
   * @param callable(\ProcessWire\HookEvent): Response $handler
   */
  public function head(callable $handler): static
  {
    $this->handlers[RequestMethod::Head->name] = $handler;
    return $this;
  }

  /**
   * Handle PUT requests
   *
   * @param callable(\ProcessWire\HookEvent): Response $handler
   */
  public function put(callable $handler): static
  {
    $this->handlers[RequestMethod::Put->name] = $handler;
    return $this;
  }

  /**
   * Handle DELETE requests
   *
   * @param callable(\ProcessWire\HookEvent): Response $handler
   */
  public function delete(callable $handler): static
  {
    $this->handlers[RequestMethod::Delete->name] = $handler;
    return $this;
  }
}
