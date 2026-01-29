<?php

namespace PwJsonApi;

use ProcessWire\WireException;

/**
 * Represents a list of services
 */
class ServiceList
{
  /**
   * Service list items
   *
   * @var Service[]
   */
  private $items = [];

  /**
   * Get services
   *
   * @return Service[]
   */
  public function getItems(): array
  {
    return $this->items;
  }

  /**
   * Get service by name
   */
  public function get(string $name): Service|null
  {
    $idx = array_search($name, array_column($this->items, 'name'));
    if (!is_int($idx)) {
      return null;
    }

    return $this->items[$idx];
  }

  /**
   * Add service
   *
   * In optional setup function you can access the added service to
   * configure it or modify it's behavior by adding hooks.
   *
   * @template TService of Service
   * @param TService $service
   * @param (callable(TService): void)|null $setup
   * @todo Prevent cross-service injection when API is running
   */
  public function add(
    Service $service,
    callable|null $setup = null,
    Api|Service|null $context = null,
  ): static {
    // Prevent cross-service injection
    // if ($context instanceof Service && $context->_isInitialized === true) {
    //   throw new WireException(
    //     "Cannot add service '{$service->name}' as a child service for '{$context->name}'. Service '{$context->name}' is already initialized.",
    //   );
    // }

    $service->_prepare();

    if (is_callable($setup)) {
      call_user_func($setup, $service);
    }

    $this->items[] = $service;
    return $this;
  }
}
