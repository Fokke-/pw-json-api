<?php

use PwJsonApi\{AuthenticationException, AuthorizationException};

// --- Authentication (401) ---

test('unauthenticated request returns 401', function () {
  $client = getHttp('auth-api');
  $res = $client->get('auth');

  expect($res->getStatusCode())->toBe(401);
});

test('unauthenticated request to child service returns 401', function () {
  $client = getHttp('auth-api');
  $res = $client->get('auth/child');

  // Authenticate runs before authorize, so 401 not 403
  expect($res->getStatusCode())->toBe(401);
});

// --- Authorization (403) ---

test('authenticated user without required role returns 403', function () {
  $client = getHttp('auth-api');

  // Login as editor (not superuser)
  $client->post('auth/login', [
    'json' => [
      'username' => 'auth-test-editor',
      'password' => 'auth-test-editor123',
    ],
  ]);

  // AuthChildService requires superuser role
  $res = $client->get('auth/child');

  expect($res->getStatusCode())->toBe(403);
});

// --- Exception catchability in error hooks ---

test('AuthenticationException is catchable in error hook', function () {
  $client = getHttp('auth-api');
  $res = $client->get('auth');
  $body = resToJson($res);

  expect($res->getStatusCode())->toBe(401);
  expect($body['exception_class'])->toBe(AuthenticationException::class);
});

test('AuthorizationException is catchable in error hook', function () {
  $client = getHttp('auth-api');

  // Login as editor (not superuser)
  $client->post('auth/login', [
    'json' => [
      'username' => 'auth-test-editor',
      'password' => 'auth-test-editor123',
    ],
  ]);

  // AuthChildService requires superuser role
  $res = $client->get('auth/child');
  $body = resToJson($res);

  expect($res->getStatusCode())->toBe(403);
  expect($body['exception_class'])->toBe(AuthorizationException::class);
});
