<?php

namespace PwJsonApi;

/**
 * Request hooks
 * @todo Prevent cross-service injection
 */
class RequestHooks extends Hooks
{
  /**
   * Constructor
   */
  public function __construct()
  {
    parent::__construct(RequestHookKey::cases());
  }
}
