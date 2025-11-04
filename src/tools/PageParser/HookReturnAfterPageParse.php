<?php

namespace PwJsonApi;

use ProcessWire\Page;

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
