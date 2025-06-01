<?php

namespace PwJsonApi;

/**
 * API response
 */
class Response
{
  /** Response data */
  public array|null $data;

  /** Response code */
  public int $code = 200;

  /** Additional top level key-value pairs */
  public array $additionalData = [];

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
   * Add top level keys and values to the response
   */
  public function with(array $data): static
  {
    $this->additionalData = [...$this->additionalData, ...$data];
    return $this;
  }

  /**
   * Return response as an array
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
      ...array_reduce(
        array_keys($this->additionalData),
        function ($acc, $key) {
          if (!is_null($this->additionalData[$key])) {
            $acc[$key] = $this->additionalData[$key];
          }

          return $acc;
        },
        []
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

    return json_encode($this->toArray($withData), $jsonOptions);
  }
}
