<?php

namespace PwJsonApi;

use ProcessWire\{Page, Field, Pageimage};

class HookReturnAfterImageParse
{
  /**
   * Parsed image
   *
   * @var array<string, mixed>
   */
  public array $parsedImage;

  /** Source image */
  public Pageimage $image;

  /** Original image */
  public Pageimage|null $originalImage;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page|null $page;

  /** Parser for custom fields */
  public PageParser|null $parser;

  /** Current depth */
  public int $depth;
}
