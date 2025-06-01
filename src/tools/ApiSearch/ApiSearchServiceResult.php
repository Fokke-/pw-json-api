<?php

namespace PwJsonApi;

/**
 * Result of a successful service search
 */
class ApiSearchServiceResult
{
  /**
   * Service
   */
  public Service $service;

  /**
   * Service sequence of the result
   *
   * @var Service[]
   */
  public array $serviceSequence;

  /**
   * Constructor
   */
  public function __construct(Service $service, array $serviceSequence)
  {
    $this->service = $service;
    $this->serviceSequence = $serviceSequence;
  }
}
