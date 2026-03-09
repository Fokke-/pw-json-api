<?php namespace ProcessWire;

use PwJsonApi\Response;
use PwJsonApi\Service;

class FoodService extends Service
{
  protected function init()
  {
    $this->setBasePath('/food');

    $this->addService(new VegetableService());

    $this->addEndpoint('/')->get(function () {
      return new Response([
        'food' => null,
      ]);
    });
  }
}
