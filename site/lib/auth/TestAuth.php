<?php namespace ProcessWire;

use PwJsonApi\{AuthenticateArgs, AuthenticationException, Authenticator};

/**
 * Test authenticator that requires a logged-in ProcessWire user
 */
class TestAuth extends Authenticator
{
  public function authenticate(AuthenticateArgs $args): void
  {
    if ($this->wire->user->isLoggedin() === false) {
      throw new AuthenticationException();
    }
  }
}
