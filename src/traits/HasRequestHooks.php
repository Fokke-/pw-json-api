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
  protected RequestHooks $hooks;

  /**
   * Initialise request hooks
   */
  private function initRequestHooks(): RequestHooks
  {
    if (empty($this->hooks)) {
      $this->hooks = new RequestHooks();
    }

    return $this->hooks;
  }

  /**
   * Get hooks by key
   *
   * @return callable[]
   */
  public function getRequestHooks(RequestHookKey $key): array
  {
    return $this->initRequestHooks()->get($key);
  }

  /**
   * Find hooks by timing and request method
   *
   * @return callable[]
   */
  public function findRequestHooks(
    HookTiming $timing,
    RequestMethod|null $requestMethod = null,
  ): array {
    return $this->initRequestHooks()->find($timing, $requestMethod);
  }

  /**
   * Hook before any request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBefore(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::Before, $handler);
    return $this;
  }

  /**
   * Hook before GET request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBeforeGet(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::BeforeGet, $handler);
    return $this;
  }

  /**
   * Hook before POST request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBeforePost(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::BeforePost, $handler);
    return $this;
  }

  /**
   * Hook before HEAD request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBeforeHead(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::BeforeHead, $handler);
    return $this;
  }

  /**
   * Hook before PUT request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBeforePut(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::BeforePut, $handler);
    return $this;
  }

  /**
   * Hook before PATCH request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBeforePatch(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::BeforePatch, $handler);
    return $this;
  }

  /**
   * Hook before DELETE request
   *
   * @param callable(RequestHookReturnBefore): void $handler
   */
  public function hookBeforeDelete(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::BeforeDelete, $handler);
    return $this;
  }

  /**
   * Hook after any request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfter(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::After, $handler);
    return $this;
  }

  /**
   * Hook after GET request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfterGet(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::AfterGet, $handler);
    return $this;
  }

  /**
   * Hook after POST request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfterPost(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::AfterPost, $handler);
    return $this;
  }

  /**
   * Hook after HEAD request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfterHead(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::AfterHead, $handler);
    return $this;
  }

  /**
   * Hook after PUT request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfterPut(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::AfterPut, $handler);
    return $this;
  }

  /**
   * Hook after PATCH request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfterPatch(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::AfterPatch, $handler);
    return $this;
  }

  /**
   * Hook after DELETE request
   *
   * @param callable(RequestHookReturnAfter): void $handler
   */
  public function hookAfterDelete(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::AfterDelete, $handler);
    return $this;
  }

  /**
   * Hook on error
   *
   * @param callable(ApiException): void $handler
   */
  public function hookOnError(callable $handler): static
  {
    $this->_assertNotLocked('add hook');
    $this->initRequestHooks()->add(RequestHookKey::OnError, $handler);
    return $this;
  }
}
