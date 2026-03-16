<?php

namespace PwJsonApi;

use ProcessWire\Page;

/**
 * Hook arguments for before page parse.
 *
 * @see https://pwjsonapi.fokke.fi/processwire-page-parser.html#hookbeforepageparse
 */
class HookReturnBeforePageParse
{
  use HasSkip;

  /** Source page */
  public Page $page;

  /** Parser for child pages */
  public PageParser $parser;

  /** Current depth */
  public int $depth;
}
