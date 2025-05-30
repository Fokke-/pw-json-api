<?php namespace ProcessWire;

use PwJsonApi\Response;
use PwJsonApi\Service;

class FruitService extends Service
{
  public function __construct()
  {
    parent::__construct();

    $this->hookAfter(function ($args) {
      $args->response->with([
        'food_type' => 'fruit',
      ]);
    });

    $this->setBasePath('/fruits');

    $this->addEndpoint('/')->get(function () {
      return new Response([
        'fruit' => null,
      ]);
    });

    $this->addEndpoint('/apple')->get(function () {
      return (new Response([
        'fruit' => 'apple',
      ]))->with(['fruit' => 'apple']);
    });

    $this->addEndpoint('/orange')->get(function () {
      return (new Response([
        'fruit' => 'orange',
      ]))->with(['fruit' => 'orange']);
    });
  }
}
