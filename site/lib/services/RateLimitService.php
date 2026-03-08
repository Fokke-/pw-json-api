<?php namespace ProcessWire;

use PwJsonApi\Response;
use PwJsonApi\Service;

class RateLimitService extends Service
{
  public function init()
  {
    $this->addEndpoint('/')->get(function () {
      return new Response([
        'message' => 'Success!',
      ]);
    });
  }
}
