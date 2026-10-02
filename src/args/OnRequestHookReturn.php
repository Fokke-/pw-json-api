<?php

namespace PwJsonApi;

/**
 * Event to return when a request is received, before any other processing
 *
 * @see https://pwjsonapi.fokke.fi/request-hooks.html#hookonrequest-arguments
 */
class OnRequestHookReturn extends RequestHookReturn
{
  /**
   * HTTP headers to add to the response
   *
   * @var array<string, string>
   */
  public array $headers = [];
}
