<?php

namespace PwJsonApi;

use ProcessWire\{Page};

/**
 * Hook arguments for before property parse.
 *
 * @see https://pwjsonapi.fokke.fi/processwire-page-parser.html#hookbeforepropertyparse
 */
class HookReturnBeforePropertyParse
{
  use HasSkip;

  /** Property value */
  public mixed $value;

  /** Property name */
  public string $propertyName;

  /** Source page */
  public Page $page;

  /** Current depth */
  public int $depth;
}
