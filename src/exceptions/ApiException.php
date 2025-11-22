<?php

namespace PwJsonApi;

/**
 * Api exception
 */
class ApiException extends \Exception
{
  /** Response */
  public readonly Response $response;

  /** Request */
  public Request $request;

  /** ProcessWire URL hook event */
  public \ProcessWire\HookEvent $event;

  /** Request endpoint */
  public Endpoint $endpoint;

  /** Request service */
  public Service $service;

  /** List of all parent services */
  public ServiceList $services;

  /** API instance */
  public Api $api;

  /**
   * Constructor
   *
   * @param string $message
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
   *
   * @param array<string, mixed> $data
   */
  public function with(array $data): static
  {
    $this->response->with($data);
    return $this;
  }
}
