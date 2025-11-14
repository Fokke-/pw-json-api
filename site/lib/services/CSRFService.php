<?php namespace ProcessWire;

use PwJsonApi\Response;
use PwJsonApi\Service;

class CSRFService extends Service
{
  public function __construct()
  {
    parent::__construct();

    $this->addEndpoint('/')
      ->get(function () {
        return new Response([
          'message' => 'Success!',
        ]);
      })
      ->head(function () {
        return new Response([
          'message' => 'Success!',
        ]);
      })
      ->put(function () {
        return new Response([
          'message' => 'Success!',
        ]);
      })
      ->delete(function () {
        return new Response([
          'message' => 'Success!',
        ]);
      })
      ->post(function () {
        return new Response([
          'message' => 'Success!',
        ]);
      })
      ->patch(function () {
        return new Response([
          'message' => 'Success!',
        ]);
      });
  }
}
