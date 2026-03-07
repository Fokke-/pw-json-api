<?php

namespace PwJsonApi;

use ProcessWire\WireException;

/**
 * Provides structure locking after initialization.
 */
trait HasLock
{
  /**
   * Whether the structure is locked
   *
   * @internal
   */
  private bool $_isLocked = false;

  /**
   * Lock the structure
   *
   * @internal
   */
  public function _lock(): void
  {
    $this->_isLocked = true;
  }

  /**
   * Check if the structure is locked
   *
   * @internal
   */
  public function _isLocked(): bool
  {
    return $this->_isLocked;
  }

  /**
   * Assert that the structure is not locked
   *
   * @internal
   */
  public function _assertNotLocked(string $action): void
  {
    if (!$this->_isLocked) {
      return;
    }

    $context = 'unknown';
    if ($this instanceof Api) {
      $context = 'Api';
    } elseif ($this instanceof Service) {
      $context = "service '{$this->name}'";
    } elseif ($this instanceof Endpoint) {
      $context = "endpoint '{$this->getPath()}'";
    }

    throw new WireException("Cannot {$action}: {$context} is locked.");
  }
}
