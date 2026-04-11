<?php

namespace PwJsonApi;

/**
 * Authorization exception (403)
 *
 * Thrown when a request fails authorization.
 *
 * @see https://pwjsonapi.fokke.fi/authentication-overview.html#authorization
 */
class AuthorizationException extends ApiException
{
  /**
   * Constructor
   *
   * @param \Throwable|null $previous
   */
  public function __construct(?\Throwable $previous = null)
  {
    parent::__construct(null, $previous);
    $this->code(403);
  }
}
