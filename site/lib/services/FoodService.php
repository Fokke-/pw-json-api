<?php namespace ProcessWire;

use PwJsonApi\Response;
use PwJsonApi\Service;

class FoodService extends Service
{
  public function __construct()
  {
    parent::__construct();

    $this->setBasePath('/food');
    $this->addEndpoint('/')->get(function () {
      return new Response([
        'food' => null,
      ]);
    });
  }
}
