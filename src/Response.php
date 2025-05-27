<?php

namespace PwJsonApi;

/**
 * API response
 */
class Response
{
  /** Response data */
  public array $data;

  /** Response code */
  public int $code;

  /** Response data */
  public array $withData = [];

  public function __construct(array $data = [], $code = 200)
  {
    $this->data = $data;
    $this->code = $code;
  }

  /**
   * Add top level keys and values to the response
   */
  public function with(array $data): static
  {
    $this->withData = [...$this->withData, ...$data];

    return $this;
  }

  /**
   * Return response as an array
   */
  public function toArray(bool $withData = true): ?array
  {
    $out = [
      ...(function () use ($withData) {
        if ($withData === false) {
          return [];
        }

        return [
          'data' => $this->data,
        ];
      })(),
      ...array_reduce(
        array_keys($this->withData),
        function ($acc, $key) {
          if (!is_null($this->withData[$key])) {
            $acc[$key] = $this->withData[$key];
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
