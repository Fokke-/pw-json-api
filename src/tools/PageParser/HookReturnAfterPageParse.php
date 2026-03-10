<?php

namespace PwJsonApi;

use ProcessWire\Page;

/**
 * Hook arguments for after page parse.
 *
 * @see https://pwjsonapi.fokke.fi/processwire-page-parser.html#hookafterpageparse
 */
class HookReturnAfterPageParse
{
  /** Source page */
  public Page $page;

  /**
   * Parsed page
   *
   * @var array<string, mixed>
   */
  public array $parsedPage;

  /** Current depth */
  public int $depth;
}
