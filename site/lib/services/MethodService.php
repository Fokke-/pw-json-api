<?php namespace ProcessWire;

use PwJsonApi\Response;
use PwJsonApi\Service;

class MethodService extends Service
{
  public function __construct()
  {
    parent::__construct();

    $this->setBasePath('/methods');
    $this->addEndpoint('/')
      ->get(function () {
        return (new Response())->with([
          'method' => 'GET',
        ]);
      })
      ->put(function () {
        return (new Response())->with([
          'method' => 'PUT',
        ]);
      })
      ->delete(function () {
        return (new Response())->with([
          'method' => 'DELETE',
        ]);
      })
      ->post(function () {
        return (new Response())->with([
          'method' => 'POST',
        ]);
      })
      ->patch(function () {
        return (new Response())->with([
          'method' => 'PATCH',
        ]);
      });
  }
}
