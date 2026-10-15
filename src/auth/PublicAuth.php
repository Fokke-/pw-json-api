<?php

namespace PwJsonApi\Auth;

use PwJsonApi\{AuthenticateArgs, Authenticator};

/**
 * Public authenticator
 *
 * Accepts all requests. Use it to opt a service or endpoint out of
 * an authenticator inherited from a parent level.
 *
 * @deprecated 2.5.0 Use skipAuthentication() instead. Will be removed in 3.0.
 * @see https://pwjsonapi.fokke.fi/authentication-overview.html#publicauth
 */
class PublicAuth extends Authenticator
{
  public function __construct()
  {
    parent::__construct();

    // Suppressed with @ so the notice never ends up in the response output.
    // Error handlers and deprecation tooling still receive it.
    @trigger_error(
      'PublicAuth is deprecated since 2.5.0 and will be removed in 3.0. Use skipAuthentication() instead.',
      E_USER_DEPRECATED,
    );
  }

  public function authenticate(AuthenticateArgs $args): void
  {
    // Accept all requests
  }
}
