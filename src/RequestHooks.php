<?php

namespace PwJsonApi;

/**
 * Represents an object of request hooks, categorised by request hook key
 */
class RequestHooks
{
  /**
   * Request hooks
   *
   * @var array<string, callable[]>
   */
  private array $items = [];

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->items = array_reduce(
      RequestHookKey::cases(),
      function ($acc, $item) {
        $acc[$item->name] = [];
        return $acc;
      },
      []
    );
  }

  /**
   * Get all hooks
   */
  public function getItems(): array
  {
    return $this->items;
  }

  /**
   * Get hooks by key
   */
  public function get(RequestHookKey $key): array
  {
    return $this->items[$key->name];
  }

  /**
   * Add a new hook
   */
  public function add(RequestHookKey $key, callable $handler)
  {
    $this->items[$key->name][] = $handler;
  }

  /**
   * Find hooks by timing and request method
   */
  public function find(RequestHookTiming $timing, RequestMethod|null $requestMethod = null): array
  {
    $key = $timing->name . ($requestMethod ? $requestMethod->name : '');
    return $this->items[$key];
  }
}
