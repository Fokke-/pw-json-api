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
  protected EndpointList $endpoints;

  /**
   * Initialise Endpoint list
   */
  private function initEndpointList(): EndpointList
  {
    if (empty($this->endpoints)) {
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
   * Note that this method is not recursive.
   * To search recursively, use findEndpoint()
   */
  public function getEndpoint(string $path): Endpoint|null
  {
    return $this->initEndpointList()->get($path);
  }

  /**
   * Add endpoint
   *
   * Specify path has ProcessWire URL hook path.
   *
   * @see https://processwire.com/blog/posts/pw-3.0.173/#introducing-url-path-hooks
   */
  public function addEndpoint(string $path): Endpoint
  {
    $this->_assertNotLocked('add endpoint');
    $endpoint = new Endpoint($path);
    $this->initEndpointList()->add($endpoint);

    return $endpoint;
  }

  /**
   * Remove endpoint
   */
  public function removeEndpoint(string $path): static
  {
    $this->_assertNotLocked('remove endpoint');
    $this->initEndpointList()->remove($path);
    return $this;
  }
}
