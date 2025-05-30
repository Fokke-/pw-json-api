<?php

namespace PwJsonApi;

/**
 * API configuration
 */
class ApiConfig
{
  /** Flags to pass to json_encode function */
  public int $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
}
