<?php

namespace PwJsonApi;

/**
 * Provides $endpoints property and shorthand methods for EndpointList instance.
 */
trait HasEndpointList
{
  /**
   * Endpoint list
   *
   * @var EndpointList
   */
  private EndpointList|null $endpoints = null;

  /**
   * Initialize endpoint list
   *
   * Thanks to the goofy PHP restriction to only allow primitive as a default value for a property,
   * this method will initialize a new endpoint list.
   */
  private function initEndpointList(): EndpointList
  {
    if (!$this->endpoints) {
      $this->endpoints = new EndpointList();
    }

    return $this->endpoints;
  }

  /**
   * Get base path
   */
  public function getBasePath(): string|null
  {
    return $this->initEndpointList()->getBasePath();
  }

  /**
   * Set base path
   */
  public function setBasePath(string|null $path): static
  {
    $this->initEndpointList()->setBasePath($path);
    return $this;
  }

  /**
   * Get endpoints
   *
   * @return Endpoint[]
   */
  public function getEndpoints(): array
  {
    return $this->initEndpointList()->getItems();
  }

  /**
   * Get endpoint by path
   *
   * @return Endpoint[]
   */
  public function getEndpoint(string $path): Endpoint|null
  {
    return $this->initEndpointList()->get($path);
  }

  /**
   * Listen to path
   */
  public function listen(string $path): Endpoint
  {
    $endpoint = new Endpoint($path);
    $this->initEndpointList()->add($endpoint);

    return $endpoint;
  }

  /**
   * Unlisten path
   */
  public function unlisten(string $path): static
  {
    $this->initEndpointList()->remove($path);
    return $this;
  }
}
