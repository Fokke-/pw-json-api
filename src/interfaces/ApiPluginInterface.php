<?php

namespace PwJsonApi\Plugins;

use PwJsonApi\{Api, Service, Endpoint};

interface ApiPluginInterface
{
  public function init(Api|Service|Endpoint $context): static;
}
