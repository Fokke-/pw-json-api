<?php namespace ProcessWire;

use PwJsonApi\{ApiException, Response, Service};

class OnRequestService extends Service
{
  protected function init()
  {
    $this->setBasePath('/on-request');

    $this->hookOnRequest(function ($args) {
      $args->headers['X-Order'] .= ',service';
    });

    $this->addEndpoint('/')
      ->get(function ($args) {
        return new Response([
          'handled' => true,
        ]);
      })
      ->hookOnRequest(function ($args) {
        $args->headers['X-Order'] .= ',endpoint';
      });

    $this->addEndpoint('/post')->post(function ($args) {
      return new Response([
        'handled' => true,
      ]);
    });

    $this->addEndpoint('/handler-header')->get(function ($args) {
      return (new Response([
        'handled' => true,
      ]))->header('X-On-Request', 'handler');
    });

    $this->addEndpoint('/throw')
      ->get(function ($args) {
        return new Response([
          'handled' => true,
        ]);
      })
      ->hookOnRequest(function ($args) {
        throw (new ApiException('Rejected on request'))->code(418);
      });
  }
}
