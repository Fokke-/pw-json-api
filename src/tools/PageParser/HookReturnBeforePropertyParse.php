<?php

namespace PwJsonApi;

use ProcessWire\{Page};

class HookReturnBeforePropertyParse
{
  /** Property value */
  public mixed $value;

  /** Property name */
  public string $propertyName;

  /** Source page */
  public Page $page;

  /** Current depth */
  public int $depth;
}
