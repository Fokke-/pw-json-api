<?php

namespace PwJsonApi;

/**
 * Services are used to group endpoints.
 *
 * Service inherits hooks from the API instance and from all the parent services.
 */
abstract class Service
{
  use Utils;
  use HasEndpointList;
  use HasServiceList;
  use HasRequestHooks;

  /** Service name */
  public readonly string $name;

  /** Constructor */
  public function __construct()
  {
    $this->name = (new \ReflectionClass($this))->getShortName();
  }
}
