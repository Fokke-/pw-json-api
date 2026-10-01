<?php namespace ProcessWire;

use PwJsonApi\{Response, Service};

class AuthOverridePublicService extends Service
{
  protected function init()
  {
    $this->setBasePath('/auth-override-public');

    $this->addEndpoint('/')->get(function ($args) {
      return new Response([
        'public' => true,
      ]);
    });
  }
}
