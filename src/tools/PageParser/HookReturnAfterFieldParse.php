<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field};

class HookReturnAfterFieldParse
{
  /** Parsed value value */
  public mixed $value;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page $page;
}
