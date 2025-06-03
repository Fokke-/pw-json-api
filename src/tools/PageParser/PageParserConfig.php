<?php

namespace PwJsonApi;

/**
 * Page parser configuration
 */
class PageParserConfig
{
  /** Recursively parse child pages? */
  public bool $parseChildren = false;

  /** Recursively parse children of page references? */
  public bool $parsePageReferenceChildren = false;

  /** Maximum depth for recursive parsing */
  public int $maxDepth = 3;

  /** Selector for child pages */
  public string $childrenSelector = '';

  /** Key name for child pages */
  public string $childrenKey = '_children';

  /** Output full file URLs */
  public bool $fullFileUrls = true;
}
