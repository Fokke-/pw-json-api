<?php

namespace PwJsonApi;

/**
 * Provides authentication support.
 */
trait HasAuthentication
{
  /**
   * Authenticator
   */
  private Authenticator|null $authenticator = null;

  /**
   * Set authenticator
   */
  public function authenticate(Authenticator $authenticator): static
  {
    $this->_assertNotLocked('set authenticator');
    $this->authenticator = $authenticator;
    return $this;
  }

  /**
   * Get authenticator
   *
   * @internal
   */
  public function _getAuthenticator(): Authenticator|null
  {
    return $this->authenticator;
  }
}
