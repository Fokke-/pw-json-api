<?php

// --- Login ---

test('login with valid credentials returns 200', function () {
  $client = getHttp('pw-auth-api');
  $res = $client->post('auth/login', [
    'json' => [
      'username' => 'auth-test-editor',
      'password' => 'auth-test-editor123',
    ],
  ]);

  expect($res->getStatusCode())->toBe(200);
});

test('login with invalid credentials returns 401', function () {
  $client = getHttp('pw-auth-api');
  $res = $client->post('auth/login', [
    'json' => ['username' => 'admin', 'password' => 'wrong'],
  ]);

  expect($res->getStatusCode())->toBe(401);
});

test('login with empty body returns 401', function () {
  $client = getHttp('pw-auth-api');
  $res = $client->post('auth/login', [
    'json' => [],
  ]);

  expect($res->getStatusCode())->toBe(401);
});

// --- Login throttle ---

test('login throttle returns 429 instead of 500', function () {
  $client = getHttp('pw-auth-api');

  // Use a unique username to avoid affecting other tests.
  // Form-encoded body triggers SessionLoginThrottle autoload
  // (it checks count($_POST) > 0).
  $username = 'throttle-test-' . time();

  $lastStatus = null;
  for ($i = 0; $i < 10; $i++) {
    $res = $client->post('auth/login', [
      'form_params' => [
        'username' => $username,
        'password' => 'wrong',
      ],
    ]);
    $lastStatus = $res->getStatusCode();
    if ($lastStatus === 429) {
      break;
    }
  }

  // Should eventually be throttled — expect 429, not 500
  expect($lastStatus)->toBe(429);
});

// --- Logout ---

test('logout returns 200', function () {
  $client = getHttp('pw-auth-api');
  $res = $client->post('auth/logout');

  expect($res->getStatusCode())->toBe(200);
});

// --- Session persistence ---

test('authenticated session can access protected endpoint', function () {
  $client = getHttp('pw-auth-api');

  // Login
  $loginRes = $client->post('auth/login', [
    'json' => [
      'username' => 'auth-test-editor',
      'password' => 'auth-test-editor123',
    ],
  ]);
  expect($loginRes->getStatusCode())->toBe(200);

  // Access protected endpoint (cookies persist via Guzzle cookie jar)
  $protectedRes = $client->get('request');
  expect($protectedRes->getStatusCode())->toBe(200);
});

test('unauthenticated request to protected endpoint returns 401', function () {
  $client = getHttp('pw-auth-api');
  $res = $client->get('request');

  expect($res->getStatusCode())->toBe(401);
});

test('logout invalidates session', function () {
  $client = getHttp('pw-auth-api');

  // Login
  $client->post('auth/login', [
    'json' => [
      'username' => 'auth-test-editor',
      'password' => 'auth-test-editor123',
    ],
  ]);

  // Logout
  $client->post('auth/logout');

  // Access protected endpoint should fail
  $res = $client->get('request');
  expect($res->getStatusCode())->toBe(401);
});
