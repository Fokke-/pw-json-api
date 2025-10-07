<?php

namespace PwJsonApi;

use ProcessWire\Page;

class HookReturnAfterPageParse
{
  /** Source page */
  public Page $page;

  /** Parsed page */
  public array $parsedPage;

  /** Current depth */
  public int $depth;
}
