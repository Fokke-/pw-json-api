<?php namespace ProcessWire;

use PwJsonApi\{AuthenticateArgs, Authenticator};

/**
 * Test authenticator that captures its arguments
 */
class ContextAuth extends Authenticator
{
  public function authenticate(AuthenticateArgs $args): void
  {
    AuthContextChildService::$authenticateArgs = AuthContextChildService::describeArgs(
      $args,
    );
  }
}
