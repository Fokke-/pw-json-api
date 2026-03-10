<?php

namespace PwJsonApi;

use ProcessWire\{Page};

/**
 * Hook arguments for after property parse.
 *
 * @see https://pwjsonapi.fokke.fi/processwire-page-parser.html#hookafterpropertyparse
 */
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
