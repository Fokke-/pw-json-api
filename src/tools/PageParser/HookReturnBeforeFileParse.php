<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field, PageFile};

class HookReturnBeforeFileParse
{
  /** Source file */
  public PageFile $file;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page $page;

  /** Parser for custom fields */
  public PageParser $parser;
}
