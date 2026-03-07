<?php

namespace PwJsonApi;

/**
 * Request hooks
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
