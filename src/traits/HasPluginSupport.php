<?php

namespace PwJsonApi;

use PwJsonApi\Plugins\ApiPlugin;

/**
 * Provides methods for adding plugins
 * @todo Prevent cross-service injection
 */
trait HasPluginSupport
{
  /**
   * Add plugin
   *
   * In optional setup function you can access the added plugin to
   * configure it.
   *
   * @template TPlugin of ApiPlugin
   * @param TPlugin $plugin
   * @param (callable(TPlugin): void)|null $setup
   */
  public function addPlugin(
    ApiPlugin $plugin,
    callable|null $setup = null,
  ): static {
    if (is_callable($setup)) {
      call_user_func($setup, $plugin);
    }

    $plugin->init($this);
    return $this;
  }
}
