<?php

namespace PwJsonApi;

/**
 * Page parser hooks
 *
 * @see https://pwjsonapi.fokke.fi/processwire-page-parser.html#hooks
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
