<?php namespace ProcessWire;

use PwJsonApi\{Service, Response};

class HelloWorldService extends Service
{
  public function __construct()
  {
    parent::__construct();

    $this->setBasePath('/hello-world');

    $this->addEndpoint('/')->get(function () {
      return new Response([
        'hello' => 'world',
      ]);
    });
  }
}
