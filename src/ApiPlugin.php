<?php

namespace PwJsonApi\Plugins;

use PwJsonApi\{HasWire, Api, Service, Endpoint};
use function ProcessWire\wire;

abstract class ApiPlugin
{
  use HasWire;

  /** Plugin context */
  protected Api|Service|Endpoint|null $context = null;

  public function __construct()
  {
    /** @var \ProcessWire\ProcessWire */
    $wire = wire();
    $this->wire = $wire;
  }

  /** Initialize plugin */
  public function init(Api|Service|Endpoint $context): static
  {
    $this->context = $context;
    return $this;
  }
}
