<?php

namespace PwJsonApi;

use function ProcessWire\wire;

/**
 * Base class for authenticators
 *
 * @see https://pwjsonapi.fokke.fi/authentication.html
 */
abstract class Authenticator
{
  use HasWire;

  public function __construct()
  {
    /** @var \ProcessWire\ProcessWire */
    $wire = wire();
    $this->wire = $wire;
  }

  /**
   * Authenticate the request
   *
   * Should throw AuthenticationException on failure.
   */
  abstract public function authenticate(AuthenticateArgs $args): void;
}
