<?php

namespace PwJsonApi;

/**
 * Available hook keys
 */
enum Config
{
  /**
   * Enable debug mode?
   *
   * In debug mode:
   * - Resulting JSON is pretty printed
   */
  case Debug;
}
