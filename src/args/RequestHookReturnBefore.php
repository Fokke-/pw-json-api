<?php

namespace PwJsonApi;

/**
 * Event to return before request is processed
 *
 * @see https://pwjsonapi.fokke.fi/request-hooks.html#hookbefore-arguments
 */
class RequestHookReturnBefore extends RequestHookReturn
{
  /**
   * Endpoint request handler
   *
   * @var callable(EndpointHandlerArgs): Response
   */
  public $handler;
}
