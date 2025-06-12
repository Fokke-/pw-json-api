<?php

namespace PwJsonApi;

/**
 * Provides API search methods
 */
trait HasApiSearch
{
  /**
   * Recursively find service by name
   */
  public function findService(string $name): Service|null
  {
    $search = new ApiSearch();

    foreach ($search->iterate($this->services->getItems()) as $result) {
      if (
        $result instanceof ApiSearchServiceResult &&
        $result->service->name === $name
      ) {
        return $result->service;
      }
    }

    return null;
  }

  /**
   * Recursively find endpoint by path
   */
  public function findEndpoint(string $path): Endpoint|null
  {
    // Try searching from the current service
    if ($this instanceof Service) {
      /** @var Service $this */
      $endpoint = $this->getEndpoint($path);
      if (!empty($endpoint)) {
        return $endpoint;
      }
    }

    // Continue searching recursively
    $search = new ApiSearch();
    $path = $this->formatPath($path);

    foreach ($search->iterate($this->services->getItems()) as $result) {
      if (
        $result instanceof ApiSearchEndpointResult &&
        $this->formatPath($result->resolvePath($this->getBasePath())) === $path
      ) {
        return $result->endpoint;
      }
    }

    return null;
  }
}
