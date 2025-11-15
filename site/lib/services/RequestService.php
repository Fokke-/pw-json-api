<?php namespace ProcessWire;

use PwJsonApi\Response;
use PwJsonApi\Service;

class RequestService extends Service
{
  public function __construct()
  {
    parent::__construct();

    $this->setBasePath('/request');
    $this->addEndpoint('/')
      ->get(function ($request) {
        return new Response([
          'method' => $request->method,
        ]);
      })
      ->put(function ($request) {
        return new Response([
          'method' => $request->method,
        ]);
      })
      ->delete(function ($request) {
        return new Response([
          'method' => $request->method,
        ]);
      })
      ->post(function ($request) {
        return new Response([
          'method' => $request->method,
        ]);
      })
      ->patch(function ($request) {
        return new Response([
          'method' => $request->method,
        ]);
      });
  }
}
