<?php

namespace PwJsonApi;

/**
 * Provides $hooks property and shorthand methods for adding hooks.
 */
trait HasHooks
{
  /**
   * Hooks
   */
  private Hooks|null $hooks = null;

  /**
   * Initialize hooks
   *
   * Thanks to the goofy PHP restriction to only allow primitive as a default value for a property,
   * this method will initialize a new hook list.
   */
  private function initHooks(): Hooks
  {
    if (!$this->hooks) {
      $this->hooks = new Hooks();
    }

    return $this->hooks;
  }

  /**
   * Find hooks by timing and request method
   */
  public function findHooks(HookTiming $order, RequestMethod|null $requestMethod = null): array
  {
    return $this->initHooks()->find($order, $requestMethod);
  }

  /**
   * Hook before any request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function before(callable $handler): static
  {
    $this->initHooks()->add(HookKey::Before, $handler);
    return $this;
  }

  /**
   * Hook before GET request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function beforeGet(callable $handler): static
  {
    $this->initHooks()->add(HookKey::BeforeGet, $handler);
    return $this;
  }

  /**
   * Hook before POST request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function beforePost(callable $handler): static
  {
    $this->initHooks()->add(HookKey::BeforePost, $handler);
    return $this;
  }

  /**
   * Hook before HEAD request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function beforeHead(callable $handler): static
  {
    $this->initHooks()->add(HookKey::BeforeHead, $handler);
    return $this;
  }

  /**
   * Hook before PUT request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function beforePut(callable $handler): static
  {
    $this->initHooks()->add(HookKey::BeforePut, $handler);
    return $this;
  }

  /**
   * Hook before DELETE request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function beforeDelete(callable $handler): static
  {
    $this->initHooks()->add(HookKey::BeforeDelete, $handler);
    return $this;
  }

  /**
   * Hook before OPTIONS request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function beforeOptions(callable $handler): static
  {
    $this->initHooks()->add(HookKey::BeforeOptions, $handler);
    return $this;
  }

  /**
   * Hook after any request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function after(callable $handler): static
  {
    $this->initHooks()->add(HookKey::After, $handler);
    return $this;
  }

  /**
   * Hook after GET request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function afterGet(callable $handler): static
  {
    $this->initHooks()->add(HookKey::AfterGet, $handler);
    return $this;
  }

  /**
   * Hook after POST request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function afterPost(callable $handler): static
  {
    $this->initHooks()->add(HookKey::AfterPost, $handler);
    return $this;
  }

  /**
   * Hook after HEAD request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function afterHead(callable $handler): static
  {
    $this->initHooks()->add(HookKey::AfterHead, $handler);
    return $this;
  }

  /**
   * Hook after PUT request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function afterPut(callable $handler): static
  {
    $this->initHooks()->add(HookKey::AfterPut, $handler);
    return $this;
  }

  /**
   * Hook after DELETE request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function afterDelete(callable $handler): static
  {
    $this->initHooks()->add(HookKey::AfterDelete, $handler);
    return $this;
  }

  /**
   * Hook after OPTIONS request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function afterOptions(callable $handler): static
  {
    $this->initHooks()->add(HookKey::AfterOptions, $handler);
    return $this;
  }
}
