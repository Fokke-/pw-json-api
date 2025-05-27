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

  public function getHooks(): Hooks
  {
    return $this->initHooks();
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
  public function hookBefore(callable $handler): static
  {
    $this->initHooks()->add(HookKey::Before, $handler);
    return $this;
  }

  /**
   * Hook before GET request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function hookGeforeGet(callable $handler): static
  {
    $this->initHooks()->add(HookKey::BeforeGet, $handler);
    return $this;
  }

  /**
   * Hook before POST request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function hookBeforePost(callable $handler): static
  {
    $this->initHooks()->add(HookKey::BeforePost, $handler);
    return $this;
  }

  /**
   * Hook before HEAD request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function hookBeforeHead(callable $handler): static
  {
    $this->initHooks()->add(HookKey::BeforeHead, $handler);
    return $this;
  }

  /**
   * Hook before PUT request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function hookBeforePut(callable $handler): static
  {
    $this->initHooks()->add(HookKey::BeforePut, $handler);
    return $this;
  }

  /**
   * Hook before DELETE request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function hookBeforeDelete(callable $handler): static
  {
    $this->initHooks()->add(HookKey::BeforeDelete, $handler);
    return $this;
  }

  /**
   * Hook before OPTIONS request
   *
   * @param callable(HookReturnBefore): void $handler
   */
  public function hookBeforeOptions(callable $handler): static
  {
    $this->initHooks()->add(HookKey::BeforeOptions, $handler);
    return $this;
  }

  /**
   * Hook after any request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function hookAfter(callable $handler): static
  {
    $this->initHooks()->add(HookKey::After, $handler);
    return $this;
  }

  /**
   * Hook after GET request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function hookAfterGet(callable $handler): static
  {
    $this->initHooks()->add(HookKey::AfterGet, $handler);
    return $this;
  }

  /**
   * Hook after POST request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function hookAfterPost(callable $handler): static
  {
    $this->initHooks()->add(HookKey::AfterPost, $handler);
    return $this;
  }

  /**
   * Hook after HEAD request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function hookAfterHead(callable $handler): static
  {
    $this->initHooks()->add(HookKey::AfterHead, $handler);
    return $this;
  }

  /**
   * Hook after PUT request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function hookAfterPut(callable $handler): static
  {
    $this->initHooks()->add(HookKey::AfterPut, $handler);
    return $this;
  }

  /**
   * Hook after DELETE request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function hookAfterDelete(callable $handler): static
  {
    $this->initHooks()->add(HookKey::AfterDelete, $handler);
    return $this;
  }

  /**
   * Hook after OPTIONS request
   *
   * @param callable(HookReturnAfter): void $handler
   */
  public function hookAfterOptions(callable $handler): static
  {
    $this->initHooks()->add(HookKey::AfterOptions, $handler);
    return $this;
  }
}
