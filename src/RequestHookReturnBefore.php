<?php

namespace PwJsonApi;

/**
 * Event to return before request is processed
 */
class RequestHookReturnBefore extends RequestHookReturn
{
  /*
   * Endpoint request handler
   *
   * @var callable
   */
  public $handler;
}
