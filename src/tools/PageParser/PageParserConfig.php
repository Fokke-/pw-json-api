<?php

namespace PwJsonApi;

/**
 * Page parser configuration
 */
class PageParserConfig
{
  /** Recursively parse child pages? */
  public bool $parseChildren = false;

  /** Maximum depth for recursive parsing */
  public int $maxDepth = 1;

  /** Recursively parse children of page field references? */
  public bool $parsePageFieldChildren = false;

  /** Selector for child pages */
  public string $childrenSelector = '';

  /** Key name for child pages */
  public string $childrenKey = '_children';
}
