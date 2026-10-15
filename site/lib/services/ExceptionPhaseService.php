<?php namespace ProcessWire;

use PwJsonApi\{AuthenticateArgs, Authenticator, Response, Service};

/**
 * Throws non-ApiExceptions in different phases of request handling
 */
class ExceptionPhaseService extends Service
{
  protected function init()
  {
    $this->setBasePath('/phases');

    $this->addEndpoint('/on-request')
      ->get(function ($args) {
        return new Response(['handled' => false]);
      })
      ->hookOnRequest(function ($args) {
        throw new \Exception('on-request');
      });

    $this->addEndpoint('/authenticator')
      ->get(function ($args) {
        return new Response(['handled' => false]);
      })
      ->authenticate(
        new class extends Authenticator {
          public function authenticate(AuthenticateArgs $args): void
          {
            throw new WireException('authenticator');
          }
        },
      );

    $this->addEndpoint('/authorizer')
      ->get(function ($args) {
        return new Response(['handled' => false]);
      })
      ->authorize(function ($args) {
        throw new \Exception('authorizer');
      });

    $this->addEndpoint('/before-hook')
      ->get(function ($args) {
        return new Response(['handled' => false]);
      })
      ->hookBefore(function ($args) {
        throw new \Exception('before-hook');
      });

    $this->addEndpoint('/handler')->get(function ($args) {
      throw new \Exception('handler');
    });

    $this->addEndpoint('/after-hook')
      ->get(function ($args) {
        return new Response(['handled' => false]);
      })
      ->hookAfter(function ($args) {
        throw new WireException('after-hook');
      });
  }
}
