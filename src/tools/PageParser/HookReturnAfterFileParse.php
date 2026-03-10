<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field, Pagefile};

/**
 * Hook arguments for after file parse.
 *
 * @see https://pwjsonapi.fokke.fi/processwire-page-parser.html#hookafterfileparse
 */
class HookReturnAfterFileParse
{
  /**
   * Parsed file
   *
   * @var array<string, mixed>
   */
  public array $parsedFile;

  /** Source file */
  public Pagefile $file;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page|null $page;

  /** Current depth */
  public int $depth;
}
