<?php namespace ProcessWire;

use PwJsonApi\{Service, Response};

class HelloWorldService extends Service
{
  public function __construct()
  {
    parent::__construct();

    // Listen to path /hello-world with GET handler
    $this->addEndpoint('/hello-world')->get(function ($request) {
      return new Response([
        'hello' => 'world',
        'request_method' => $request->method,
        'path' => $request->path,
      ]);
    });
  }
}
