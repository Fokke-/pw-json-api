<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field, PageImage};

class HookReturnBeforeImageParse
{
  /** Source image */
  public PageImage $image;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page $page;

  /** Parser for custom fields */
  public PageParser $parser;
}
