<?php

namespace PwJsonApi;

/**
 * Event to return before request is processed
 */
class RequestHookReturnBefore extends RequestHookReturn
{
  /*
   * Request handler
   *
   * @var callable
   */
  public $handler;
}
