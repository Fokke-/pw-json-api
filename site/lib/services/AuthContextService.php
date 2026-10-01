<?php namespace ProcessWire;

use PwJsonApi\Service;

class AuthContextService extends Service
{
  protected function init()
  {
    $this->setBasePath('/auth-context');

    $this->addService(new AuthContextChildService());
  }
}
