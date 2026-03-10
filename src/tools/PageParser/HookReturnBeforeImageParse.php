<?php

namespace PwJsonApi;

use \ProcessWire\{Page, Field, Pageimage};

/**
 * Hook arguments for before image parse.
 *
 * @see https://pwjsonapi.fokke.fi/processwire-page-parser.html#hookbeforeimageparse
 */
class HookReturnBeforeImageParse
{
  /** Source image */
  public Pageimage $image;

  /** Source field */
  public Field|null $field;

  /** Source page */
  public Page|null $page;

  /** Parser for custom fields */
  public PageParser|null $parser;

  /** Current depth */
  public int $depth;
}
