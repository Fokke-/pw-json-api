<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field, Pageimage};

class HookReturnBeforeImageParse
{
  /** Source image */
  public Pageimage $image;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page $page;

  /** Parser for custom fields */
  public PageParser $parser;
}
