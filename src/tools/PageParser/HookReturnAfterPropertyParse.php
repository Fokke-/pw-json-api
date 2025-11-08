<?php

namespace PwJsonApi;

use ProcessWire\{Page};

class HookReturnAfterPropertyParse
{
  /** Parsed property value */
  public mixed $parsedValue;

  /** Property name */
  public string $propertyName;

  /** Source page */
  public Page $page;

  /** Current depth */
  public int $depth;
}
