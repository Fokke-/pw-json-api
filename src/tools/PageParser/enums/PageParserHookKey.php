<?php

namespace PwJsonApi;

/**
 * Page parser hook keys
 */
enum PageParserHookKey
{
  /** Before page parse */
  case BeforePageParse;

  /** After page parse */
  case AfterPageParse;

  /** Before field parse */
  case BeforeFieldParse;

  /** After field parse */
  case AfterFieldParse;

  /** Before image parse */
  case BeforeImageParse;

  /** After image parse */
  case AfterImageParse;

  /** Before file parse */
  case BeforeFileParse;

  /** After file parse */
  case AfterFileParse;
}
