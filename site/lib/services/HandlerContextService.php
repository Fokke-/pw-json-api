<?php namespace ProcessWire;

use PwJsonApi\Service;

class HandlerContextService extends Service
{
  protected function init()
  {
    $this->setBasePath('/handler-context');

    $this->addService(new HandlerContextChildService());
  }
}
