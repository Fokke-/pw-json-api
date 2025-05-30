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
  private ServiceList|null $services = null;

  /**
   * Initialize service list
   * TODO: remove this. Just init in constructor...
   *
   * Thanks to the goofy PHP restriction to only allow primitive as a default value for a property,
   * this method will initialize a new service list.
   */
  public function initServiceList(): ServiceList
  {
    if (!$this->services) {
      $this->services = new ServiceList();
    }

    return $this->services;
  }

  /**
   * Recursively get service by name
   */
  public function getService(string $name): Service|null
  {
    foreach ($this->initServiceList()->getItems() as $service) {
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
   * Get services
   *
   * @return Service[]
   */
  public function getServices(): array
  {
    return $this->initServiceList()->getItems();
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
    $this->initServiceList()->add($service, $setup);
    return $this;
  }

  /**
   * Get endpoint recursively by path
   *
   * @return Endpoint
   */
  public function getEndpoint(string $path): Endpoint|null
  {
    return $this->initEndpointList()->get($path);
  }
}
