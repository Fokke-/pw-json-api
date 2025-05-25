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
    parent::__construct('', 404, $previous);
  }

  /**
   * Transform exception to a new Response
   */
  public function toResponse(): Response
  {
    return new Response([], $this->code);
  }
}
