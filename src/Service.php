<?php

namespace PwJsonApi;

use function ProcessWire\wire;

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
  use HasApiSearch;
  use HasWire;

  /** Service name */
  public string $name;

  /** Constructor */
  public function __construct()
  {
    $this->name = (new \ReflectionClass($this))->getShortName();

    /** @var \ProcessWire\ProcessWire */
    $wire = wire();
    $this->wire = $wire;
  }
}
