<?php

namespace PwJsonApi;

/**
 * Page parser configuration
 */
class PageParserConfig
{
  /** Recursively parse child pages? (default: false) */
  public bool $parseChildren = false;

  /** Selector for child pages (default: '') */
  public string $childrenSelector = '';

  /** Key name for child pages (default: '_children') */
  public string $childrenKey = '_children';

  /** Recursively parse children of page field references? (default: false) */
  public bool $parsePageReferenceChildren = false;

  /** Maximum depth for recursive parsing (default: 3) */
  public int $maxDepth = 3;

  /** Output full file URLs (default: true) */
  public bool $fullFileUrls = true;

  /** Parse custom fields of files? (default: false) */
  public bool $parseFileCustomFields = false;

  /** Key name for custom fields of files (default: '_custom_fields') */
  public string $fileCustomFieldsKey = '_custom_fields';
}
