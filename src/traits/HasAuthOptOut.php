<?php

namespace PwJsonApi;

/**
 * Provides methods for opting out of inherited authentication and
 * authorization.
 *
 * @see https://pwjsonapi.fokke.fi/authentication-overview.html#opting-out
 */
trait HasAuthOptOut
{
  /**
   * Whether inherited authentication is skipped
   */
  private bool $skipAuthentication = false;

  /**
   * Whether inherited authorization is skipped
   */
  private bool $skipAuthorization = false;

  /**
   * Skip authenticators inherited from parent levels
   */
  public function skipAuthentication(): static
  {
    $this->_assertNotLocked('skip authentication');
    $this->skipAuthentication = true;
    return $this;
  }

  /**
   * Skip authorizers inherited from parent levels
   */
  public function skipAuthorization(): static
  {
    $this->_assertNotLocked('skip authorization');
    $this->skipAuthorization = true;
    return $this;
  }

  /**
   * Whether inherited authentication is skipped
   *
   * @internal
   */
  public function _skipsAuthentication(): bool
  {
    return $this->skipAuthentication;
  }

  /**
   * Whether inherited authorization is skipped
   *
   * @internal
   */
  public function _skipsAuthorization(): bool
  {
    return $this->skipAuthorization;
  }
}
