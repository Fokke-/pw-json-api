<?php namespace ProcessWire;

use PwJsonApi\{AuthenticateArgs, AuthenticationException, AuthInterface};

/**
 * Test authenticator that requires a logged-in ProcessWire user
 */
class TestAuth implements AuthInterface
{
  public function authenticate(AuthenticateArgs $args): void
  {
    /** @var \ProcessWire\ProcessWire */
    $wire = wire();

    if ($wire->user->isLoggedin() === false) {
      throw new AuthenticationException();
    }
  }
}
