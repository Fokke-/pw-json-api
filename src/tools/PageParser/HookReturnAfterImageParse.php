<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field, PageImage};

class HookReturnAfterImageParse
{
  /** Parsed image */
  public array $parsedImage;

  /** Source image */
  public PageImage $image;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page $page;
}
