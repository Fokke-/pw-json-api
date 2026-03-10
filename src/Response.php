<?php

namespace PwJsonApi;

/**
 * API response
 *
 * @see https://pwjsonapi.fokke.fi/responses.html
 */
class Response
{
  /**
   * Response data
   *
   * @var array<string, mixed>|array<int, array<string, mixed>>|null
   */
  public array|null $data;

  /** Response code */
  public int $code = 200;

  /**
   * HTTP headers
   *
   * @var array<string, string>
   */
  public array $headers = [];

  /**
   * Additional top-level key-value pairs
   *
   * @var array<string, mixed>
   */
  public array $additionalData = [];

  /**
   * Constructor
   *
   * @param array<string, mixed>|array<int, array<string, mixed>>|null $data
   */
  public function __construct(?array $data = [])
  {
    $this->data = $data;
  }

  /**
   * Specify response code
   */
  public function code(int $code): static
  {
    $this->code = $code;
    return $this;
  }

  /**
   * Set an HTTP header
   */
  public function header(string $name, string $value): static
  {
    $this->headers[$name] = $value;
    return $this;
  }

  /**
   * Get all HTTP headers
   *
   * @return array<string, string>
   */
  public function getHeaders(): array
  {
    return $this->headers;
  }

  /**
   * Add top-level keys and values to the response
   *
   * @param array<string, mixed> $data
   */
  public function with(array $data): static
  {
    $this->additionalData = [...$this->additionalData, ...$data];
    return $this;
  }

  /**
   * Return response as an array
   *
   * @return array<string, mixed>|null
   */
  public function toArray(bool $withData = true): ?array
  {
    $out = [
      ...(function () use ($withData) {
        if ($withData === false || $this->data === null) {
          return [];
        }

        return [
          'data' => $this->data,
        ];
      })(),
      ...array_filter(
        $this->additionalData,
        static fn($value) => !is_null($value),
      ),
    ];

    return !empty($out) ? $out : null;
  }

  /**
   * Return response as JSON
   */
  public function toJson(int $jsonOptions = 0, bool $withData = true): ?string
  {
    $data = $this->toArray($withData);
    if (empty($data)) {
      return null;
    }

    $json = json_encode($data, $jsonOptions);
    return is_string($json) ? $json : null;
  }
}
