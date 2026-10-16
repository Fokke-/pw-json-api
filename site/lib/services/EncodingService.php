<?php namespace ProcessWire;

use PwJsonApi\{Response, Service};

class EncodingService extends Service
{
  protected function init()
  {
    $this->setBasePath('/encoding');

    $this->addEndpoint('/invalid-utf8')->get(function ($args) {
      return (new Response([
        'value' => "\xB1\x31",
      ]))->header('X-Encoding-Test', 'kept');
    });

    $this->addEndpoint('/nan')->get(function ($args) {
      return new Response([
        'value' => NAN,
      ]);
    });

    $this->addEndpoint('/valid')->get(function ($args) {
      return new Response([
        'value' => 'äö',
      ]);
    });
  }
}
