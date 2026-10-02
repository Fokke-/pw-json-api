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

// --- Argument objects ---

test('authenticator arguments', function () {
  $client = getHttp('auth-context-api');
  $res = $client->get('auth-context/child');
  $json = resToJson($res);

  expect($json['data']['authenticate_args'])->toBe([
    'type' => 'PwJsonApi\\AuthenticateArgs',
    'request' => 'PwJsonApi\\Request',
    'user' => 'ProcessWire\\User',
    'endpoint' => 'PwJsonApi\\Endpoint',
    'service' => 'ProcessWire\\AuthContextChildService',
    'services' => 'PwJsonApi\\ServiceList',
    'api' => 'PwJsonApi\\Api',
  ]);
});

test('authorizer arguments', function () {
  $client = getHttp('auth-context-api');
  $res = $client->get('auth-context/child');
  $json = resToJson($res);

  expect($json['data']['authorize_args'])->toBe([
    'type' => 'PwJsonApi\\AuthorizeArgs',
    'request' => 'PwJsonApi\\Request',
    'user' => 'ProcessWire\\User',
    'endpoint' => 'PwJsonApi\\Endpoint',
    'service' => 'ProcessWire\\AuthContextChildService',
    'services' => 'PwJsonApi\\ServiceList',
    'api' => 'PwJsonApi\\Api',
  ]);
});

// --- Authenticator override ---

test(
  'endpoint without own authenticator inherits Api authenticator',
  function () {
    $client = getHttp('auth-override-api');
    $res = $client->get('auth-override/protected');

    expect($res->getStatusCode())->toBe(401);
  },
);

test('endpoint authenticator overrides Api authenticator', function () {
  $client = getHttp('auth-override-api');
  $res = $client->get('auth-override/public');

  expect($res->getStatusCode())->toBe(200);
});

test('service authenticator overrides Api authenticator', function () {
  $client = getHttp('auth-override-api');
  $res = $client->get('auth-override-public');

  expect($res->getStatusCode())->toBe(200);
});

// --- Opt-out ---

test('CSRF token endpoint follows Api authenticator by default', function () {
  $client = getHttp('auth-gate-api');
  $res = $client->get('csrf-token');

  expect($res->getStatusCode())->toBe(403);
});

test('CSRF token endpoint can skip authentication', function () {
  $client = getHttp('auth-skip-api');
  $res = $client->get('csrf-token');
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(200);
  expect($json['csrf_token']['value'])->toBeString();
});

test('ProcessWireAuthService can skip authentication', function () {
  $client = getHttp('auth-skip-api');
  $token = resToJson($client->get('csrf-token'))['csrf_token'];

  $res = $client->post('auth/logout', [
    'headers' => [
      'X-' . $token['name'] => $token['value'],
    ],
  ]);

  expect($res->getStatusCode())->toBe(200);
});

test('service can skip Api authorization', function () {
  $client = getHttp('authz-skip-api');
  $res = $client->get('auth-skip');

  expect($res->getStatusCode())->toBe(200);
});

test('sibling service still follows Api authorization', function () {
  $client = getHttp('authz-skip-api');
  $res = $client->get('hello-world');

  expect($res->getStatusCode())->toBe(403);
});
