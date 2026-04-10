<?php

namespace PwJsonApi;

/**
 * Contract for authentication implementations
 *
 * @see https://pwjsonapi.fokke.fi/authentication.html
 */
interface AuthInterface
{
  /**
   * Authenticate the request
   *
   * Should throw AuthenticationException on failure.
   */
  public function authenticate(AuthenticateArgs $args): void;
}
