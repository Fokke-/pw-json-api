<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field};

class HookReturnBeforeFieldParse
{
  /** Field value */
  public mixed $value;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page $page;

  /** Parser (used for Page and Pagearray) */
  public PageParser $parser;
}
