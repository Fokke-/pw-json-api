<?php

namespace PwJsonApi;

/**
 * Provides authorization support.
 */
trait HasAuthorization
{
  /**
   * Authorizer
   *
   * @var callable(AuthorizeArgs): bool|null
   */
  private $authorizer = null;

  /**
   * Set authorizer
   *
   * @param callable(AuthorizeArgs): bool $authorizer
   */
  public function authorize(callable $authorizer): static
  {
    $this->_assertNotLocked('set authorizer');
    $this->authorizer = $authorizer;
    return $this;
  }

  /**
   * Get authorizer
   *
   * @internal
   * @return callable(AuthorizeArgs): bool|null
   */
  public function _getAuthorizer(): callable|null
  {
    return $this->authorizer;
  }
}
