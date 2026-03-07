<?php

namespace PwJsonApi;

use PwJsonApi\Plugins\ApiPlugin;
use ProcessWire\WireException;

/**
 * Represents a list of plugins
 */
class PluginList
{
  /**
   * Plugin list items
   *
   * @var ApiPlugin[]
   */
  private array $items = [];

  /**
   * Add plugin
   */
  public function add(ApiPlugin $plugin): static
  {
    $className = $plugin::class;

    if ($this->has($className)) {
      throw new WireException("Plugin '{$className}' is already installed");
    }

    $this->items[] = $plugin;
    return $this;
  }

  /**
   * Get all plugins
   *
   * @return ApiPlugin[]
   */
  public function getAll(): array
  {
    return $this->items;
  }

  /**
   * Get plugin by class name
   *
   * @template TPlugin of ApiPlugin
   * @param class-string<TPlugin> $className
   * @return TPlugin|null
   */
  public function get(string $className): ApiPlugin|null
  {
    foreach ($this->items as $plugin) {
      if ($plugin instanceof $className) {
        return $plugin;
      }
    }

    return null;
  }

  /**
   * Check if plugin is installed
   *
   * @param class-string<ApiPlugin> $className
   */
  public function has(string $className): bool
  {
    return $this->get($className) !== null;
  }

  /**
   * Initialize all plugins
   *
   * @internal
   */
  public function _initAll(Api|Service|Endpoint $context): void
  {
    foreach ($this->items as $plugin) {
      $plugin->init($context);
    }
  }
}
