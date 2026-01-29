<?php namespace ProcessWire;

use PwJsonApi\Response;
use PwJsonApi\Service;

class VegetableService extends Service
{
  public function init()
  {
    $this->addEndpoint('/carrot')->get(function () {
      return new Response([
        'vegetable' => 'carrot',
      ]);
    });

    $this->addEndpoint('/broccoli')->get(function () {
      return new Response([
        'vegetable' => 'broccoli',
      ]);
    });
  }
}
