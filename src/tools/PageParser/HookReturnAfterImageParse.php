<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field, Pageimage};

class HookReturnAfterImageParse
{
  /** Parsed image */
  public array $parsedImage;

  /** Source image */
  public Pageimage $image;

  /** Original image */
  public Pageimage|null $originalImage;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page $page;

  /** Parser for custom fields */
  public PageParser $parser;
}
