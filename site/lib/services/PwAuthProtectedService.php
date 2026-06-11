<?php namespace ProcessWire;

use PwJsonApi\Auth\ProcessWireAuth;
use PwJsonApi\{Response, Service};

class PwAuthProtectedService extends Service
{
  protected function init()
  {
    // Require authentication for this service
    $this->authenticate(new ProcessWireAuth());

    $this->addEndpoint('/me')->get(function ($args) {
      return new Response([
        'name' => $args->user->name,
      ]);
    });

    // Child services inherit authentication
    $this->addService(new PwAuthChildService());
  }
}
