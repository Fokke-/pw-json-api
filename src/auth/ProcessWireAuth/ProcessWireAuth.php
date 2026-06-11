<?php

namespace PwJsonApi\Auth;

use PwJsonApi\{AuthenticateArgs, AuthenticationException, Authenticator};

/**
 * ProcessWire session authenticator
 *
 * Verifies that the current ProcessWire user is logged in.
 *
 * @see https://pwjsonapi.fokke.fi/processwire-auth.html
 */
class ProcessWireAuth extends Authenticator
{
  public function authenticate(AuthenticateArgs $args): void
  {
    if ($this->wire->user->isLoggedin() === false) {
      throw new AuthenticationException();
    }
  }
}
