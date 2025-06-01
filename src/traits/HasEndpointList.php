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
  protected EndpointList|null $endpoints = null;

  /**
   * Get base path
   */
  public function getBasePath(): string|null
  {
    return $this->endpoints->getBasePath();
  }

  /**
   * Set base path
   */
  public function setBasePath(string|null $path): static
  {
    $this->endpoints->setBasePath($path);
    return $this;
  }

  /**
   * Get endpoints
   *
   * @return Endpoint[]
   */
  public function getEndpoints(): array
  {
    return $this->endpoints->getItems();
  }

  /**
   * Get endpoint by path
   *
   * Note that this method is not recursive.
   * To search recursively, use findEndpoint()
   */
  public function getEndpoint(string $path): Endpoint|null
  {
    return $this->endpoints->get($path);
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
    $endpoint = new Endpoint($path);
    $this->endpoints->add($endpoint);

    return $endpoint;
  }

  /**
   * Remove endpoint
   */
  public function removeEndpoint(string $path): static
  {
    $this->endpoints->remove($path);
    return $this;
  }
}
