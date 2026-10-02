<?php namespace ProcessWire;

use PwJsonApi\{Response, Service};

class AuthSkipService extends Service
{
  protected function init()
  {
    $this->setBasePath('/auth-skip');

    $this->addEndpoint('/')->get(function ($args) {
      return new Response([
        'skipped' => true,
      ]);
    });
  }
}
