<?php namespace ProcessWire;

use PwJsonApi\Response;
use PwJsonApi\Service;

class RateLimitService extends Service
{
  protected function init()
  {
    $this->addEndpoint('/')->get(function () {
      return new Response([
        'message' => 'Success!',
      ]);
    });
  }
}
