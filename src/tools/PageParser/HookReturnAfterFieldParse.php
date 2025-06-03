<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field};

class HookReturnAfterFieldParse
{
  /** Parsed value value */
  public mixed $parsedValue;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page $page;
}
