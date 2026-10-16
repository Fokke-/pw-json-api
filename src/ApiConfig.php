<?php

namespace PwJsonApi;

/**
 * API configuration
 *
 * @see https://pwjsonapi.fokke.fi/api-instance.html#configuration
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

  /**
   * Use paths exactly as defined, without lowercasing?
   *
   * false (default) = paths are lowercased
   * true = paths keep their case
   *
   * ProcessWire matches URLs case-sensitively. The default will change
   * to true in 3.0.
   */
  public bool $preservePathCase = false;
}
