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
  use HasApiInstance;
  use HasServiceList;
  use HasEndpointList;
  use HasRequestHooks;

  /** Service name */
  public readonly string $name;

  /** Constructor */
  public function __construct()
  {
    $this->name = (new \ReflectionClass($this))->getShortName();
    $this->services = new ServiceList();
    $this->endpoints = new EndpointList();
    $this->hooks = new RequestHooks();
  }
}
