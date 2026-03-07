<?php

namespace PwJsonApi;

use PwJsonApi\Plugins\ApiPlugin;

/**
 * Provides methods for adding and managing plugins
 */
trait HasPluginList
{
  /**
   * Plugin list
   */
  protected PluginList $plugins;

  /**
   * Whether plugins have been bulk-initialized
   *
   * @internal
   */
  private bool $_pluginsInitialized = false;

  /**
   * Initialise Plugin list
   */
  private function initPluginList(): PluginList
  {
    if (empty($this->plugins)) {
      $this->plugins = new PluginList();
    }

    return $this->plugins;
  }

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
    $this->_assertNotLocked('add plugin');
    $this->initPluginList()->add($plugin);

    if (is_callable($setup)) {
      call_user_func($setup, $plugin);
    }

    if ($this->_pluginsInitialized) {
      $plugin->init($this);
    }

    return $this;
  }

  /**
   * Get all plugins
   *
   * @return ApiPlugin[]
   */
  public function getPlugins(): array
  {
    return $this->initPluginList()->getAll();
  }

  /**
   * Get plugin by class name
   *
   * @template TPlugin of ApiPlugin
   * @param class-string<TPlugin> $className
   * @return TPlugin|null
   */
  public function getPlugin(string $className): ApiPlugin|null
  {
    return $this->initPluginList()->get($className);
  }

  /**
   * Check if plugin is installed
   *
   * @param class-string<ApiPlugin> $className
   */
  public function hasPlugin(string $className): bool
  {
    return $this->initPluginList()->has($className);
  }

  /**
   * Initialize all plugins
   *
   * @internal
   */
  public function _initPlugins(): void
  {
    $this->_pluginsInitialized = true;
    $this->initPluginList()->_initAll($this);
  }
}
