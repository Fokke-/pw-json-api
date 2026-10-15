<?php namespace ProcessWire;

use PwJsonApi\{Response, Service};

class PathCaseService extends Service
{
  protected function init()
  {
    $this->setBasePath('/path-case');

    $this->addEndpoint('/products/{productId}')->get(function ($args) {
      return new Response([
        'camel' => $args->request->routeParam('productId'),
        'lower' => $args->request->routeParam('productid'),
      ]);
    });

    $this->addEndpoint('/Latest')->get(function ($args) {
      return new Response([
        'latest' => true,
      ]);
    });

    $this->addEndpoint('/codes/(?<code>\D+)')->get(function ($args) {
      return new Response([
        'code' => $args->request->routeParam('code'),
      ]);
    });
  }
}
