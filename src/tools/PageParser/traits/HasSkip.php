<?php

namespace PwJsonApi;

/**
 * Provides skip() for before-hook argument classes
 *
 * @internal
 */
trait HasSkip
{
  /** @internal */
  private bool $_skipped = false;

  /**
   * Skip parsing of this item — the key will be omitted from output
   */
  public function skip(): void
  {
    $this->_skipped = true;
  }

  /** @internal */
  public function _isSkipped(): bool
  {
    return $this->_skipped;
  }
}
