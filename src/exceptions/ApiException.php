<?php

namespace PwJsonApi;

/**
 * Api exception
 */
class ApiException extends \Exception
{
  /**
   * Constructor
   *
   * @param string $message
   * @param integer $code
   * @param \Throwable|null $previous
   */
  public function __construct(string $message, int $code = 400, ?\Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }

  /**
   * Transform exception to a new Response
   */
  public function toResponse(): Response
  {
    return (new Response([], $this->code))->with([
      'error' => $this->message,
    ]);
  }
}
