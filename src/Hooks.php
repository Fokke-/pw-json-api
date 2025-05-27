<?php

namespace PwJsonApi;

class Hooks
{
  /**
   * Hooks
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
      HookKey::cases(),
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
  public function get(HookKey $key): array
  {
    return $this->items[$key->name];
  }

  /**
   * Add a new hook
   */
  public function add(HookKey $key, callable $handler)
  {
    $this->items[$key->name][] = $handler;
  }

  /**
   * Find hooks by timing and request method
   */
  public function find(HookTiming $timing, RequestMethod|null $requestMethod = null): array
  {
    $key = $timing->name . ($requestMethod ? $requestMethod->name : '');
    return $this->items[$key];
  }
}
