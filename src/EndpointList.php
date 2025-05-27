<?php

namespace PwJsonApi;

use \ProcessWire\{WireException};

class EndpointList
{
  use HasBasePath;
  use Utils;

  /**
   * Endpoint list items
   *
   * @var Endpoint[]
   */
  private $items = [];

  /**
   * Get endpoints
   *
   * @return Endpoint[]
   */
  public function getItems(): array
  {
    return $this->items;
  }

  /**
   * Get endpoint by path
   */
  public function get(string $path): Endpoint|null
  {
    $path = $this->formatPath($path);

    // Endpoint does not contain basePath of EndpointList
    // If search contains basePath, strip it.
    if (!empty($this->basePath) && !empty($path) && str_starts_with($path, $this->basePath)) {
      $path = $this->formatPath(substr($path, strlen($this->basePath)));
    }

    $idx = array_search($path, $this->getPaths());
    if (!is_int($idx)) {
      return null;
    }

    return $this->items[$idx];
  }

  /**
   * Return paths of endpoints
   *
   * @return string[]
   */
  public function getPaths(): array
  {
    return array_map(function ($item) {
      return $item->getPath();
    }, $this->items);
  }

  /**
   * Add endpoint
   */
  public function add(Endpoint $endpoint)
  {
    $this->items[] = $endpoint;
  }

  /**
   * Remove endpoint
   */
  public function remove(Endpoint|string $endpointOrPath): static
  {
    $endpoint = $endpointOrPath instanceof Endpoint ? $endpointOrPath : $this->get($endpointOrPath);

    if (empty($endpoint)) {
      throw new WireException(
        "Unable to remove endpoint. Endpoint with path '{$endpointOrPath}' was not found."
      );
    }

    $idx = array_search($endpoint, $this->items);
    array_splice($this->items, $idx, 1);

    return $this;
  }
}
