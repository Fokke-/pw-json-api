<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field, Pagefile};

class HookReturnAfterFileParse
{
  /** Parsed file */
  public array $parsedFile;

  /** Source file */
  public Pagefile $file;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page $page;
}
