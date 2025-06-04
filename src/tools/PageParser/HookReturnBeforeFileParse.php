<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field, Pagefile};

class HookReturnBeforeFileParse
{
  /** Source file */
  public Pagefile $file;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page $page;

  /** Parser for custom fields */
  public PageParser $parser;
}
