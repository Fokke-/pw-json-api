<?php

namespace PwJsonApi;

/**
 * Page parser hooks
 */
class PageParserHooks extends Hooks
{
  /**
   * Constructor
   */
  public function __construct()
  {
    parent::__construct(PageParserHookKey::cases());
  }
}
