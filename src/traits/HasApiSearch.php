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
    $search = new ApiSearch($this->services);

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
    $path = $this->formatPath($path);
    $search = new ApiSearch($this->services);

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
