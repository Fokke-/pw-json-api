<?php

namespace PwJsonApi;

/**
 * Config
 */
// TODO: create singleton class?
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
