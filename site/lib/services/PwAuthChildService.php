<?php namespace ProcessWire;

use PwJsonApi\{Response, Service};

class PwAuthChildService extends Service
{
  protected function init()
  {
    $this->setBasePath('/orders');

    $this->addEndpoint('/')->get(function ($args) {
      return new Response([
        'user' => $args->user->name,
      ]);
    });
  }
}
