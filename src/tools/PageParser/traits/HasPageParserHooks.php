<?php

namespace PwJsonApi;

/**
 * Provides $hooks property and shorthand methods for adding hooks.
 */
trait HasPageParserHooks
{
  /**
   * Hooks
   */
  private PageParserHooks|null $hooks = null;

  /**
   * Initialize hooks
   *
   * Thanks to the goofy PHP restriction to only allow primitive as a default value for a property,
   * this method will initialize a new hook list.
   */
  private function initPageParserHooks(): PageParserHooks
  {
    if (!$this->hooks) {
      $this->hooks = new PageParserHooks();
    }

    return $this->hooks;
  }

  /**
   * Get hooks by key
   */
  public function getPageParserHook(PageParserHookKey $key): array
  {
    return $this->initPageParserHooks()->getItems()[$key->name];
  }

  /**
   * Hook before page parse
   *
   * @param callable(HookReturnBeforePageParse): void $handler
   */
  public function hookBeforePageParse(callable $handler): static
  {
    $this->initPageParserHooks()->add(
      PageParserHookKey::BeforePageParse,
      $handler
    );
    return $this;
  }

  /**
   * Hook after page parse
   *
   * @param callable(HookReturnAfterPageParse): void $handler
   */
  public function hookAfterPageParse(callable $handler): static
  {
    $this->initPageParserHooks()->add(
      PageParserHookKey::AfterPageParse,
      $handler
    );
    return $this;
  }

  /**
   * Hook before field parse
   *
   * @param callable(HookReturnBeforeFieldParse): void $handler
   */
  public function hookBeforeFieldParse(callable $handler): static
  {
    $this->initPageParserHooks()->add(
      PageParserHookKey::BeforeFieldParse,
      $handler
    );
    return $this;
  }

  /**
   * Hook after field parse
   *
   * @param callable(HookReturnAfterFieldParse): void $handler
   */
  public function hookAfterFieldParse(callable $handler): static
  {
    $this->initPageParserHooks()->add(
      PageParserHookKey::AfterFieldParse,
      $handler
    );
    return $this;
  }

  /**
   * Hook before image parse
   *
   * @param callable(HookReturnBeforeImageParse): void $handler
   */
  public function hookBeforeImageParse(callable $handler): static
  {
    $this->initPageParserHooks()->add(
      PageParserHookKey::BeforeImageParse,
      $handler
    );
    return $this;
  }

  /**
   * Hook after image parse
   *
   * @param callable(HookReturnAfterImageParse): void $handler
   */
  public function hookAfterImageParse(callable $handler): static
  {
    $this->initPageParserHooks()->add(
      PageParserHookKey::AfterImageParse,
      $handler
    );
    return $this;
  }

  /**
   * Hook before file parse
   *
   * @param callable(HookReturnBeforeFileParse): void $handler
   */
  public function hookBeforeFileParse(callable $handler): static
  {
    $this->initPageParserHooks()->add(
      PageParserHookKey::BeforeFileParse,
      $handler
    );
    return $this;
  }

  /**
   * Hook after file parse
   *
   * @param callable(HookReturnAfterFileParse): void $handler
   */
  public function hookAfterFileParse(callable $handler): static
  {
    $this->initPageParserHooks()->add(
      PageParserHookKey::AfterFileParse,
      $handler
    );
    return $this;
  }
}
