<?php

namespace PwJsonApi;

/**
 * Api 404 exception
 */
class Api404Exception extends ApiException
{
  /**
   * Constructor
   *
   * @param \Throwable|null $previous
   */
  public function __construct(?\Throwable $previous = null)
  {
    parent::__construct(null, $previous);
    $this->response->code(404);
  }
}
