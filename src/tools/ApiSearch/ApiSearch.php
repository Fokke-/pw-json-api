<?php

namespace PwJsonApi;

/**
 * API search
 */
class ApiSearch
{
  /**
   * @var Service[] $servicesCarry
   */
  private array $servicesCarry = [];

  /**
   * Iterate all services and yield services and endpoints
   *
   * This function recursively traverses a hierarchy of services,
   * yielding information about each service and endpoint.
   *
   * @param Service[] $services
   * @return Generator<ApiSearchServiceResult|ApiSearchEndpointResult>
   */
  public function iterate(array $services): \Generator
  {
    foreach ($services as $service) {
      // Yield the service
      yield new ApiSearchServiceResult($service, $this->servicesCarry);

      // Add this service to the carry
      $this->servicesCarry[] = $service;

      // Loop service endpoints and yield results
      foreach ($service->getEndpoints() as $endpoint) {
        yield new ApiSearchEndpointResult(
          $endpoint,
          $service,
          $this->servicesCarry
        );
      }

      // Search recursively from child services
      yield from $this->iterate($service->getServices());

      // Remove the current service from the carry after recursion
      array_pop($this->servicesCarry);
    }
  }
}
