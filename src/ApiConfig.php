<?php

namespace PwJsonApi;

/**
 * API configuration
 */
class ApiConfig
{
  /**
   * Should endpoint path and with a trailing slash?
   *
   * null (default) = does not matter
   * true = enforce path with trailing slash
   * false = enforce path without trailing slash
   */
  public bool|null $trailingSlashes = null;

  /** Flags to pass to json_encode function */
  public int $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
}
