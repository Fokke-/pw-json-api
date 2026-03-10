<?php

namespace PwJsonApi;

/**
 * Event to return when an error occurs
 *
 * @see https://pwjsonapi.fokke.fi/error-hooks.html#error-hook-arguments
 */
class ErrorHookReturn extends RequestHookReturn
{
  /** Exception that was thrown */
  public ApiException $exception;

  /** Error response (reference to $exception->response) */
  public Response $response;
}
