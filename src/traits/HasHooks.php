<?php

namespace PwJsonApi;

/**
 * Provides $hooks property and shorthand methods for adding hooks.
 */
trait HasRequestHooks
{
  /**
   * Hooks
   */
  private RequestHooks|null $hooks = null;

  /**
   * Initialize hooks
   *
   * Thanks to the goofy PHP restriction to only allow primitive as a default value for a property,
   * this method will initialize a new hook list.
   */
  private function initHooks(): RequestHooks
  {
    if (!$this->hooks) {
      $this->hooks = new RequestHooks();
    }

    return $this->hooks;
  }

  public function getHooks(): RequestHooks
  {
    return $this->initHooks();
  }

  /**
   * Find hooks by timing and request method
   */
  public function findHooks(
    RequestHookTiming $order,
    RequestMethod|null $requestMethod = null
  ): array {
    return $this->initHooks()->find($order, $requestMethod);
  }

  /**
   * Hook before any request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBefore(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::Before, $handler);
    return $this;
  }

  /**
   * Hook before GET request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBeforeGet(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::BeforeGet, $handler);
    return $this;
  }

  /**
   * Hook before POST request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBeforePost(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::BeforePost, $handler);
    return $this;
  }

  /**
   * Hook before HEAD request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBeforeHead(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::BeforeHead, $handler);
    return $this;
  }

  /**
   * Hook before PUT request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBeforePut(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::BeforePut, $handler);
    return $this;
  }

  /**
   * Hook before DELETE request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBeforeDelete(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::BeforeDelete, $handler);
    return $this;
  }

  /**
   * Hook before OPTIONS request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBeforeOptions(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::BeforeOptions, $handler);
    return $this;
  }

  /**
   * Hook after any request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfter(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::After, $handler);
    return $this;
  }

  /**
   * Hook after GET request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfterGet(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::AfterGet, $handler);
    return $this;
  }

  /**
   * Hook after POST request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfterPost(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::AfterPost, $handler);
    return $this;
  }

  /**
   * Hook after HEAD request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfterHead(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::AfterHead, $handler);
    return $this;
  }

  /**
   * Hook after PUT request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfterPut(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::AfterPut, $handler);
    return $this;
  }

  /**
   * Hook after DELETE request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfterDelete(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::AfterDelete, $handler);
    return $this;
  }

  /**
   * Hook after OPTIONS request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfterOptions(callable $handler): static
  {
    $this->initHooks()->add(RequestHookKey::AfterOptions, $handler);
    return $this;
  }
}
