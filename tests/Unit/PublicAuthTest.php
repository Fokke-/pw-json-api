<?php

use PwJsonApi\Auth\PublicAuth;
use PwJsonApi\{AuthenticateArgs, Authenticator};

test('PublicAuth extends Authenticator', function () {
  $auth = withoutDeprecations(static fn() => new PublicAuth());
  expect($auth)->toBeInstanceOf(Authenticator::class);
});

test('PublicAuth accepts all requests', function () {
  $auth = withoutDeprecations(static fn() => new PublicAuth());
  $auth->authenticate(new AuthenticateArgs());
})->throwsNoExceptions();

test('PublicAuth triggers a deprecation notice', function () {
  $notices = [];

  // Error handlers also receive errors suppressed with @
  set_error_handler(function (int $level, string $message) use (&$notices) {
    if ($level === E_USER_DEPRECATED) {
      $notices[] = $message;
    }
    return true;
  });

  try {
    new PublicAuth();
  } finally {
    restore_error_handler();
  }

  expect($notices)->toHaveCount(1);
  expect($notices[0])->toContain('PublicAuth is deprecated');
});
