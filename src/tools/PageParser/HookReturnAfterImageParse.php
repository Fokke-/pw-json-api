<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field, PageImage};

class HookReturnAfterImageParse
{
  /** Parsed image */
  public array $parsedImage;

  /** Source image */
  public PageImage $image;

  /** Original image */
  public PageImage|null $originalImage;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page $page;

  /** Parser for custom fields */
  public PageParser $parser;
}
