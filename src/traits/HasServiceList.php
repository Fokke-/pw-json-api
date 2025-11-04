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
  protected ServiceList $services;

  /**
   * Initialise Service list
   */
  private function initServiceList(): static
  {
    if (empty($this->services)) {
      $this->services = new ServiceList();
    }

    return $this;
  }

  /**
   * Get services
   *
   * @return Service[]
   */
  public function getServices(): array
  {
    $this->initServiceList();
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
    $this->initServiceList();
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
    $this->initServiceList();
    $this->services->add($service, $setup);
    return $this;
  }
}
