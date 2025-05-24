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
   *
   * Thanks to the goofy PHP restriction to only allow primitive as a default value for a property,
   * this method will initialize a new service list.
   */
  private function initServiceList(): ServiceList
  {
    if (!$this->services) {
      $this->services = new ServiceList();
    }

    return $this->services;
  }

  /**
   * Get service by name
   */
  public function getService(string $name): Service|null
  {
    return $this->initServiceList()->get($name);
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
  public function addService(Service $service, callable|null $setup = null): static
  {
    $this->initServiceList()->add($service, $setup);
    return $this;
  }
}
