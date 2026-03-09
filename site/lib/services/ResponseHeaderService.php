<?php namespace ProcessWire;

use PwJsonApi\{Response, ApiException};
use PwJsonApi\Service;

class ResponseHeaderService extends Service
{
  protected function init()
  {
    $this->addEndpoint('/')->get(function () {
      return (new Response([
        'message' => 'Success!',
      ]))->header('X-Custom-Header', 'custom-value');
    });

    $this->addEndpoint('/multiple')->get(function () {
      return (new Response([
        'message' => 'Success!',
      ]))
        ->header('X-First', 'one')
        ->header('X-Second', 'two');
    });

    $this->addEndpoint('/error')->get(function () {
      throw (new ApiException('Test error'))->header(
        'X-Error-Header',
        'error-value',
      );
    });
  }
}
