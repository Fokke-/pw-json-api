<?php

use PwJsonApi\Auth\PublicAuth;
use PwJsonApi\{AuthenticateArgs, Authenticator};

test('PublicAuth extends Authenticator', function () {
  $auth = new PublicAuth();
  expect($auth)->toBeInstanceOf(Authenticator::class);
});

test('PublicAuth accepts all requests', function () {
  $auth = new PublicAuth();
  $auth->authenticate(new AuthenticateArgs());
})->throwsNoExceptions();
