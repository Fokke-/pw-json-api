<?php

namespace PwJsonApi;

/**
 * Event to return when request has been finished and response has been returned.
 */
class RequestHookReturnAfter extends RequestHookReturn
{
  /** Response from endpoint request handler */
  public Response $response;
}
