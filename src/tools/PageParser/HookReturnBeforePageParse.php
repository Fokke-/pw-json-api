<?php

namespace PwJsonApi;

use ProcessWire\Page;

class HookReturnBeforePageParse
{
  /** Source page */
  public Page $page;

  /** Parser for child pages */
  public PageParser $parser;
}
