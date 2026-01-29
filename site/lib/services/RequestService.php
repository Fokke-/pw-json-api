<?php namespace ProcessWire;

use PwJsonApi\Response;
use PwJsonApi\Service;

class RequestService extends Service
{
  public function init()
  {
    $this->setBasePath('/request');
    $this->addEndpoint('/')
      ->get(function ($args) {
        return new Response([
          'method' => $args->request->method,
        ]);
      })
      ->put(function ($args) {
        return new Response([
          'method' => $args->request->method,
        ]);
      })
      ->delete(function ($args) {
        return new Response([
          'method' => $args->request->method,
        ]);
      })
      ->post(function ($args) {
        return new Response([
          'method' => $args->request->method,
        ]);
      })
      ->patch(function ($args) {
        return new Response([
          'method' => $args->request->method,
        ]);
      });

    $this->addEndpoint('/dynamic-path/name/{name}')->get(function () {
      return new Response();
    });

    $this->addEndpoint('/dynamic-path/name/{name}/{another}')->get(function () {
      return new Response();
    });

    $this->addEndpoint('/dynamic-path/predefined-name/(name:foo|bar|baz)')->get(
      function () {
        return new Response();
      },
    );

    $this->addEndpoint('/dynamic-path/regex/(.*)')->get(function () {
      return new Response();
    });
  }
}
