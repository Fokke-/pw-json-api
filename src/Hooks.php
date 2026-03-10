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
   *
   * @param \UnitEnum[] $keys
   */
  public function __construct(array $keys)
  {
    $this->items = array_reduce(
      $keys,
      static function (array $acc, \UnitEnum $item) {
        $acc[$item->name] = [];
        return $acc;
      },
      [],
    );
  }

  /**
   * Get all hook keys and handlers
   *
   * @return array<string, callable[]>
   */
  public function getItems(): array
  {
    return $this->items;
  }

  /**
   * Get hooks by key
   *
   * @return callable[]
   */
  public function get(\UnitEnum $key): array
  {
    return $this->items[$key->name];
  }

  /**
   * Add a new hook
   */
  public function add(\UnitEnum $key, callable $handler): static
  {
    $this->items[$key->name][] = $handler;
    return $this;
  }

  /**
   * Find hooks by timing and method
   *
   * @return callable[]
   */
  public function find(\UnitEnum $timing, \UnitEnum|null $method = null): array
  {
    $key = $timing->name . ($method ? $method->name : '');
    return $this->items[$key];
  }
}
