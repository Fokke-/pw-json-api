<?php

namespace PwJsonApi;

/**
 * This class can be used to crawl entire tree of services.
 *
 * The main purpose is to return all endpoints and their service
 * hierarchy.
 */
class EndpointCrawler
{
  /**
   * @var Service[] $servicesCarry
   */
  private array $servicesCarry = [];

  /**
   * Crawl all services and yield endpoints
   *
   * This function recursively traverses a hierarchy of services and their subservices,
   * yielding information about each endpoint, including the services it belongs to,
   * and the endpoint itself.
   *
   * @param Service[] $services
   * @return Generator<EndpointCrawlerResult>
   */
  public function crawl(array $services): \Generator
  {
    foreach ($services as $service) {
      // Add this service to the carry
      $this->servicesCarry[] = $service;

      // Loop service endpoints and yield results
      foreach ($service->getEndpoints() as $endpoint) {
        yield new EndpointCrawlerResult($endpoint, $service, $this->servicesCarry);
      }

      // Crawl endpoints recursively from subservices
      yield from $this->crawl($service->getServices());

      // Remove the current service from the carry after recursion
      array_pop($this->servicesCarry);
    }
  }
}
