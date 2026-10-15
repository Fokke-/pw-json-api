<?php

use PwJsonApi\Api;
use PwJsonApi\Plugins\RateLimitPlugin;

test('init() triggers a deprecation notice', function () {
  $notices = [];

  // Error handlers also receive errors suppressed with @
  set_error_handler(function (int $level, string $message) use (&$notices) {
    if ($level === E_USER_DEPRECATED) {
      $notices[] = $message;
    }
    return true;
  });

  try {
    (new RateLimitPlugin())->init(new Api());
  } finally {
    restore_error_handler();
  }

  expect($notices)->toHaveCount(1);
  expect($notices[0])->toContain('RateLimitPlugin is deprecated');
});
