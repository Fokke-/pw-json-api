<?php namespace ProcessWire;

use PwJsonApi\{Response, Service};

class AuthService extends Service
{
  protected function init()
  {
    $this->setBasePath('/auth');

    $this->addEndpoint('/')->get(function ($args) {
      return new Response([
        'authenticated' => true,
      ]);
    });

    $this->addService(new AuthChildService());
  }
}
