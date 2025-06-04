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
   * Get service by name
   *
   * Note that this method is not recursive.
   * To search recursively, use findService()
   */
  public function getService(string $name): Service|null
  {
    return $this->services->get($name);
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
    callable|null $setup = null,
  ): static {
    $this->services->add($service, $setup);
    return $this;
  }
}
