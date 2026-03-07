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
  use HasPluginSupport;
  use HasWire;

  /** Service name */
  public string $name;

  /**
   * Is this service initialized?
   *
   * @internal
   */
  private bool $_isInitialized = false;

  /** Constructor */
  public function __construct()
  {
    $this->name = (new \ReflectionClass($this))->getShortName();

    /** @var \ProcessWire\ProcessWire */
    $wire = wire();
    $this->wire = $wire;
  }

  /**
   * Prepare service
   *
   * @internal
   */
  public function _prepare(): static
  {
    if ($this->_isInitialized === true) {
      return $this;
    }

    $this->_isInitialized = true;
    $this->init();
    return $this;
  }

  /**
   * Configure service endpoints and hooks.
   *
   * @phpstan-ignore missingType.return
   */
  protected function init() {}
}
