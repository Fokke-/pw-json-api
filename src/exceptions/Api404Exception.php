<?php

namespace PwJsonApi;

/**
 * Api 404 exception
 *
 * @see https://pwjsonapi.fokke.fi/error-handling.html#api404exception
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
    $this->code(404);
  }
}
