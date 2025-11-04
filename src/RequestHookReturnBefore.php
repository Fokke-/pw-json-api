<?php

namespace PwJsonApi;

/**
 * Event to return before request is processed
 */
class RequestHookReturnBefore extends RequestHookReturn
{
  /**
   * Endpoint request handler
   *
   * @var callable(): void
   */
  public $handler;
}
