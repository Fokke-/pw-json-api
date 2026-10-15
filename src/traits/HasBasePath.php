<?php

namespace PwJsonApi;

/**
 * Provides $basePath property with getter and setter functions.
 */
trait HasBasePath
{
  use Utils;

  /**
   * Base path of the instance
   */
  private string|null $basePath = null;

  /**
   * Base path as given, before formatting
   */
  private string|null $rawBasePath = null;

  /**
   * Get base path of the instance
   */
  public function getBasePath(): string|null
  {
    return $this->basePath;
  }

  /**
   * Get base path, optionally without lowercasing
   *
   * @internal
   */
  public function _getBasePath(bool $preserveCase): string|null
  {
    return $preserveCase
      ? $this->formatPath($this->rawBasePath, true)
      : $this->basePath;
  }

  /**
   * Set base path for instance
   */
  public function setBasePath(string|null $path): static
  {
    $this->rawBasePath = $path;

    if (is_null($path)) {
      $this->basePath = null;
      return $this;
    }

    $path = $this->formatPath($path);
    if (empty($path)) {
      $path = null;
    }

    $this->basePath = $path;
    return $this;
  }
}
