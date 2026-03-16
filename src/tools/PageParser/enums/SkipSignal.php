<?php

namespace PwJsonApi;

/**
 * Signal returned by parse methods when a before-hook called skip()
 *
 * @internal
 */
enum SkipSignal
{
  case Skip;
}
