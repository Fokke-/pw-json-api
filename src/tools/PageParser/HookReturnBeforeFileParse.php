<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field, Pagefile};

/**
 * Hook arguments for before file parse.
 *
 * @see https://pwjsonapi.fokke.fi/processwire-page-parser.html#hookbeforefileparse
 */
class HookReturnBeforeFileParse
{
  /** Source file */
  public Pagefile $file;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page|null $page;

  /** Parser for custom fields */
  public PageParser|null $parser;

  /** Current depth */
  public int $depth;
}
