<?php

namespace PwJsonApi;

use ProcessWire\Page;

class HookReturnAfterPageParse
{
  /** Parsed page */
  public array $parsedPage;

  /** Source page */
  public Page $page;
}
