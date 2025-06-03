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
  protected PageParserHooks|null $hooks = null;

  /**
   * Get hooks by key
   */
  public function getPageParserHooks(PageParserHookKey $key): array
  {
    return $this->hooks->getItems()[$key->name];
  }

  /**
   * Hook before page parse
   *
   * @param callable(HookReturnBeforePageParse): void $handler
   */
  public function hookBeforePageParse(callable $handler): static
  {
    $this->hooks->add(PageParserHookKey::BeforePageParse, $handler);
    return $this;
  }

  /**
   * Hook after page parse
   *
   * @param callable(HookReturnAfterPageParse): void $handler
   */
  public function hookAfterPageParse(callable $handler): static
  {
    $this->hooks->add(PageParserHookKey::AfterPageParse, $handler);
    return $this;
  }

  /**
   * Hook before field parse
   *
   * @param callable(HookReturnBeforeFieldParse): void $handler
   */
  public function hookBeforeFieldParse(callable $handler): static
  {
    $this->hooks->add(PageParserHookKey::BeforeFieldParse, $handler);
    return $this;
  }

  /**
   * Hook after field parse
   *
   * @param callable(HookReturnAfterFieldParse): void $handler
   */
  public function hookAfterFieldParse(callable $handler): static
  {
    $this->hooks->add(PageParserHookKey::AfterFieldParse, $handler);
    return $this;
  }

  /**
   * Hook before image parse
   *
   * @param callable(HookReturnBeforeImageParse): void $handler
   */
  public function hookBeforeImageParse(callable $handler): static
  {
    $this->hooks->add(PageParserHookKey::BeforeImageParse, $handler);
    return $this;
  }

  /**
   * Hook after image parse
   *
   * @param callable(HookReturnAfterImageParse): void $handler
   */
  public function hookAfterImageParse(callable $handler): static
  {
    $this->hooks->add(PageParserHookKey::AfterImageParse, $handler);
    return $this;
  }

  /**
   * Hook before file parse
   *
   * @param callable(HookReturnBeforeFileParse): void $handler
   */
  public function hookBeforeFileParse(callable $handler): static
  {
    $this->hooks->add(PageParserHookKey::BeforeFileParse, $handler);
    return $this;
  }

  /**
   * Hook after file parse
   *
   * @param callable(HookReturnAfterFileParse): void $handler
   */
  public function hookAfterFileParse(callable $handler): static
  {
    $this->hooks->add(PageParserHookKey::AfterFileParse, $handler);
    return $this;
  }
}
