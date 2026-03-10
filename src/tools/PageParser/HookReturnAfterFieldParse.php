<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field};

/**
 * Hook arguments for after field parse.
 *
 * @see https://pwjsonapi.fokke.fi/processwire-page-parser.html#hookafterfieldparse
 */
class HookReturnAfterFieldParse
{
  /** Parsed value */
  public mixed $parsedValue;

  /** Source field */
  public Field $field;

  /** Source page */
  public Page $page;

  /** Current depth */
  public int $depth;
}
