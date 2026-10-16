<?php namespace ProcessWire;

use PwJsonApi\{ApiException, AuthenticateArgs, Authenticator};

/**
 * Test authenticator that rejects all requests, like an API key gate
 */
class GateAuth extends Authenticator
{
  public function authenticate(AuthenticateArgs $args): void
  {
    throw (new ApiException('Gate closed'))->code(403);
  }
}
