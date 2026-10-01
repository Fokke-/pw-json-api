<?php namespace ProcessWire;

use PwJsonApi\{Response, Service};
use PwJsonApi\Auth\PublicAuth;

class AuthOverrideService extends Service
{
  protected function init()
  {
    $this->setBasePath('/auth-override');

    // Inherits Api authenticator
    $this->addEndpoint('/protected')->get(function ($args) {
      return new Response([
        'public' => false,
      ]);
    });

    // Overrides Api authenticator
    $this->addEndpoint('/public')
      ->get(function ($args) {
        return new Response([
          'public' => true,
        ]);
      })
      ->authenticate(new PublicAuth());
  }
}
