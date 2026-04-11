<?php

namespace PwJsonApi;

/**
 * Request hooks
 *
 * @see https://pwjsonapi.fokke.fi/request-hooks.html
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
