<?php

namespace PwJsonApi;

/**
 * Event to return when request has been finished and response has been returned.
 */
class RequestHookReturnAfter extends RequestHookReturn
{
  /** Request response, which can be modified */
  public Response $response;
}
