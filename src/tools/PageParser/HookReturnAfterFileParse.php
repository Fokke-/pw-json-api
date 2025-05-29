<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field, PageFile};

class HookReturnAfterFileParse
{
  /** Parsed file */
  public array $parsedFile;

  /** Source file */
  public PageFile $file;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page $page;
}
