<?php namespace ProcessWire;

use PwJsonApi\{Service, Response};

class HelloWorldService extends Service
{
  protected function init()
  {
    // Listen to path /hello-world with GET handler
    $this->addEndpoint('/hello-world')->get(function ($args) {
      return new Response([
        'hello' => 'world',
        'request_method' => $args->request->method,
        'path' => $args->request->path,
      ]);
    });
  }
}
