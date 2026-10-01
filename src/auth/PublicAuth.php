<?php

namespace PwJsonApi\Auth;

use PwJsonApi\{AuthenticateArgs, Authenticator};

/**
 * Public authenticator
 *
 * Accepts all requests. Use it to opt a service or endpoint out of
 * an authenticator inherited from a parent level.
 *
 * @see https://pwjsonapi.fokke.fi/authentication-overview.html#opting-out
 */
class PublicAuth extends Authenticator
{
  public function authenticate(AuthenticateArgs $args): void
  {
    // Accept all requests
  }
}
