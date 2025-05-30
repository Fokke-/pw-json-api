<?php

namespace PwJsonApi;

/**
 * Provides $api property
 */
trait HasApiInstance
{
  /**
   * Reference to the API instance
   *
   * Note that this is available only after the API has started to run.
   * Therefore you cannot access this in constructor.
   */
  protected Api|null $api = null;

  /**
   * Set API instance
   *
   * @internal
   */
  public function _setApi(Api $api): void
  {
    $this->api = $api;
  }
}
