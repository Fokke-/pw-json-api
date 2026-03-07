<?php

namespace PwJsonApi;

/**
 * API response
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
      ...array_reduce(
        array_keys($this->additionalData),
        function (array $acc, string $key) {
          if (!is_null($this->additionalData[$key])) {
            $acc[$key] = $this->additionalData[$key];
          }

          return $acc;
        },
        [],
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
