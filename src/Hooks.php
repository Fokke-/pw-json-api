<?php

namespace PwJsonApi;

/**
 * Represents an object of hooks, categorised by a key
 */
abstract class Hooks
{
  /**
   * Hook map
   *
   * @var array<string, callable[]>
   */
  protected array $items = [];

  /**
   * Constructor
   */
  public function __construct(array $keys)
  {
    $this->items = array_reduce(
      $keys,
      function ($acc, $item) {
        $acc[$item->name] = [];
        return $acc;
      },
      []
    );
  }

  /**
   * Get all hook keys and handlers
   */
  public function getItems(): array
  {
    return $this->items;
  }

  /**
   * Get hooks by key
   */
  public function get(\UnitEnum $key): array
  {
    return $this->items[$key->name];
  }

  /**
   * Add a new hook
   */
  public function add(\UnitEnum $key, callable $handler)
  {
    $this->items[$key->name][] = $handler;
  }

  /**
   * Find hooks by timing and method
   */
  public function find(\UnitEnum $timing, \UnitEnum|null $method = null): array
  {
    $key = $timing->name . ($method ? $method->name : '');
    return $this->items[$key];
  }
}
