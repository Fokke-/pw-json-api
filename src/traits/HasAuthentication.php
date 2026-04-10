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
  private AuthInterface|null $authenticator = null;

  /**
   * Set authenticator
   */
  public function authenticate(AuthInterface $authenticator): static
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
  public function _getAuthenticator(): AuthInterface|null
  {
    return $this->authenticator;
  }
}
