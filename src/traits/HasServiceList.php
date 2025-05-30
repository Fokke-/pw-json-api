<?php

namespace PwJsonApi;

/**
 * Provides $services property and shorthand methods for ServiceList instance.
 */
trait HasServiceList
{
  /**
   * Service list
   *
   * @var ServiceList
   */
  protected ServiceList|null $services = null;

  /**
   * Get services
   *
   * @return Service[]
   */
  public function getServices(): array
  {
    return $this->services->getItems();
  }

  /**
   * Recursively get service by name
   */
  public function getService(string $name): Service|null
  {
    foreach ($this->services->getItems() as $service) {
      if ($service->name === $name) {
        return $service;
      }

      // Recursively search in the current service
      $foundService = $service->getService($name);
      if ($foundService !== null) {
        return $foundService;
      }
    }

    return null;
  }

  /**
   * Add service
   *
   * In optional setup function you can access the added service to
   * modify it's behavior by adding hooks.
   *
   * @param Service $service
   * @param callable(Service): void $setup
   */
  public function addService(
    Service $service,
    callable|null $setup = null
  ): static {
    $this->services->add($service, $setup);
    return $this;
  }
}
