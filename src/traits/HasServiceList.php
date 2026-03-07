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
  private function initServiceList(): ServiceList
  {
    if (empty($this->services)) {
      $this->services = new ServiceList();
    }

    return $this->services;
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
   * Get service by name
   *
   * Note that this method is not recursive.
   * To search recursively, use findService()
   */
  public function getService(string $name): Service|null
  {
    return $this->initServiceList()->get($name);
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
   */
  public function addService(
    Service $service,
    callable|null $setup = null,
  ): static {
    $this->_assertNotLocked('add service');
    $this->initServiceList()->add($service, $setup);
    return $this;
  }
}
