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

test('CSRF token endpoint can skip Api authorization', function () {
  $client = getHttp('authz-skip-api');
  $res = $client->get('csrf-token');

  expect($res->getStatusCode())->toBe(200);
});

// --- Opt-out chains (/foo → /foo/bar → /foo/bar/baz) ---

test('opt-out chain', function (string $api, string $path, int $status) {
  $client = getHttp($api);
  $res = $client->get($path);

  expect($res->getStatusCode())->toBe($status);
})->with([
  // Authentication
  'authn scenario 1: /foo' => ['authn-s1-api', 'foo', 403],
  'authn scenario 1: /foo/bar' => ['authn-s1-api', 'foo/bar', 200],
  'authn scenario 2: /foo' => ['authn-s2-api', 'foo', 200],
  'authn scenario 2: /foo/bar' => ['authn-s2-api', 'foo/bar', 403],
  'authn scenario 3: /foo' => ['authn-s3-api', 'foo', 200],
  'authn scenario 3: /foo/bar' => ['authn-s3-api', 'foo/bar', 403],
  'authn scenario 3: /foo/bar/baz' => ['authn-s3-api', 'foo/bar/baz', 200],
  'authn endpoint: /foo' => ['authn-endpoint-api', 'foo', 200],
  'authn endpoint: /foo/sibling' => ['authn-endpoint-api', 'foo/sibling', 403],

  // Authorization
  'authz scenario 1: /foo' => ['authz-s1-api', 'foo', 403],
  'authz scenario 1: /foo/bar' => ['authz-s1-api', 'foo/bar', 200],
  'authz scenario 2: /foo' => ['authz-s2-api', 'foo', 200],
  'authz scenario 2: /foo/bar' => ['authz-s2-api', 'foo/bar', 403],
  'authz scenario 3: /foo' => ['authz-s3-api', 'foo', 200],
  'authz scenario 3: /foo/bar' => ['authz-s3-api', 'foo/bar', 403],
  'authz scenario 3: /foo/bar/baz' => ['authz-s3-api', 'foo/bar/baz', 200],
  'authz endpoint: /foo' => ['authz-endpoint-api', 'foo', 200],
  'authz endpoint: /foo/sibling' => ['authz-endpoint-api', 'foo/sibling', 403],
]);
