<?php namespace ProcessWire;

use PwJsonApi\{Response, Service};

class HandlerContextChildService extends Service
{
  protected function init()
  {
    $this->setBasePath('/child');

    $this->addEndpoint('/')->get(function ($args) {
      return new Response([
        'handler_args' => [
          'type' => get_class($args),
          'request' => get_class($args->request),
          'user' => get_class($args->user),
          'event' => get_class($args->event),
          'endpoint' => get_class($args->endpoint),
          'service' => get_class($args->service),
          'services' => get_class($args->services),
          'api' => get_class($args->api),
        ],
      ]);
    });
  }
}
