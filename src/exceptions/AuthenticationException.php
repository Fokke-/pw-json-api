<?php

namespace PwJsonApi;

/**
 * Authentication exception (401)
 *
 * Thrown when a request fails authentication.
 *
 * @see https://pwjsonapi.fokke.fi/authentication.html
 */
class AuthenticationException extends ApiException
{
  /**
   * Constructor
   *
   * @param \Throwable|null $previous
   */
  public function __construct(?\Throwable $previous = null)
  {
    parent::__construct(null, $previous);
    $this->code(401);
  }
}
