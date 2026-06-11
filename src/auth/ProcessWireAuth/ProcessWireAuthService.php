<?php

namespace PwJsonApi\Auth;

use PwJsonApi\{
  ApiException,
  AuthenticationException,
  EndpointHandlerArgs,
  Response,
  Service,
};

/**
 * Login and logout service for ProcessWire session authentication
 *
 * @see https://pwjsonapi.fokke.fi/processwire-auth.html
 */
class ProcessWireAuthService extends Service
{
  protected function init(): void
  {
    $this->setBasePath('/auth');

    $this->addEndpoint('/login')->post(function (
      EndpointHandlerArgs $args,
    ): Response {
      /** @var array{username?: string, password?: string} $body */
      $body = $args->request->body;
      $username = $body['username'] ?? '';
      $password = $body['password'] ?? '';

      // Ensure SessionLoginThrottle is loaded so its hooks are
      // registered regardless of $_POST content.
      $this->wire->modules->get('SessionLoginThrottle');

      try {
        /** @var \ProcessWire\User|null $user */
        $user = $this->wire->session->login($username, $password); // @phpstan-ignore arguments.count
      } catch (\ProcessWire\SessionLoginThrottleException $e) {
        throw (new ApiException(null, $e))->code(429);
      }

      if ($user === null) {
        throw new AuthenticationException();
      }

      return new Response();
    });

    $this->addEndpoint('/logout')->post(function (): Response {
      $this->wire->session->logout();

      return new Response();
    });
  }
}
