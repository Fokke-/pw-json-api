<?php namespace ProcessWire;

use PwJsonApi\{AuthorizeArgs, Response, Service};

class AuthChildService extends Service
{
  protected function init()
  {
    $this->setBasePath('/child');

    $this->authorize(
      static fn(AuthorizeArgs $args) => $args->user->hasRole('superuser'),
    );

    $this->addEndpoint('/')->get(function ($args) {
      return new Response([
        'authorized' => true,
      ]);
    });
  }
}
