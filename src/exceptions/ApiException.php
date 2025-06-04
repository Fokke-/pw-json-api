<?php

namespace PwJsonApi;

/**
 * Api exception
 */
class ApiException extends \Exception
{
  /** Response object */
  public readonly Response $response;

  /**
   * Constructor
   *
   * @param string $message
   * @param integer $code
   * @param \Throwable|null $previous
   */
  public function __construct(
    string|null $message = null,
    ?\Throwable $previous = null,
  ) {
    parent::__construct(!empty($message) ? $message : '', 400, $previous);

    $this->response = (new Response())->code(400)->with([
      'error' => $message,
    ]);
  }

  /**
   * Specify response code
   */
  public function code(int $code): static
  {
    $this->code = $code;
    $this->response->code($code);
    return $this;
  }

  /**
   * Add top-level keys and values to the response
   */
  public function with(array $data): static
  {
    $this->response->with($data);
    return $this;
  }
}
