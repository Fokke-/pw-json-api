<?php

namespace PwJsonApi;

/**
 * Event to return when an error occurs
 */
class ErrorHookReturn extends RequestHookReturn
{
  /** Exception that was thrown */
  public ApiException $exception;

  /** Error response (reference to $exception->response) */
  public Response $response;
}
