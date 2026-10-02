<?php

use PwJsonApi\{Api, AuthorizeArgs};

// --- Authentication ---

test('authentication scenario 1: skip on child service', function () {
  ['foo' => $foo, 'bar' => $bar] = authChain();
  $fooAuth = authChainAuthenticator();
  $foo->authenticate($fooAuth);
  $bar->skipAuthentication();

  $api = new Api();

  // /foo
  expect(authChainRequest([$foo])->resolveAuthenticator($api))->toBe($fooAuth);

  // /foo/bar
  expect(
    authChainRequest([$foo, $bar])->resolveAuthenticator($api),
  )->toBeNull();
});

test('authentication scenario 2: authenticator on child service', function () {
  ['foo' => $foo, 'bar' => $bar] = authChain();
  $barAuth = authChainAuthenticator();
  $bar->authenticate($barAuth);

  $api = new Api();

  // /foo
  expect(authChainRequest([$foo])->resolveAuthenticator($api))->toBeNull();

  // /foo/bar
  expect(authChainRequest([$foo, $bar])->resolveAuthenticator($api))->toBe(
    $barAuth,
  );
});

test('authentication scenario 3: skip on grandchild service', function () {
  ['foo' => $foo, 'bar' => $bar, 'baz' => $baz] = authChain();
  $barAuth = authChainAuthenticator();
  $bar->authenticate($barAuth);
  $baz->skipAuthentication();

  $api = new Api();

  // /foo
  expect(authChainRequest([$foo])->resolveAuthenticator($api))->toBeNull();

  // /foo/bar
  expect(authChainRequest([$foo, $bar])->resolveAuthenticator($api))->toBe(
    $barAuth,
  );

  // /foo/bar/baz
  expect(
    authChainRequest([$foo, $bar, $baz])->resolveAuthenticator($api),
  )->toBeNull();
});

test(
  'authentication: skip on child service bypasses Api authenticator',
  function () {
    ['foo' => $foo, 'bar' => $bar] = authChain();
    $apiAuth = authChainAuthenticator();
    $bar->skipAuthentication();

    $api = new Api();
    $api->authenticate($apiAuth);

    // /foo
    expect(authChainRequest([$foo])->resolveAuthenticator($api))->toBe(
      $apiAuth,
    );

    // /foo/bar
    expect(
      authChainRequest([$foo, $bar])->resolveAuthenticator($api),
    )->toBeNull();
  },
);

// --- Authorization ---

test('authorization scenario 1: skip on child service', function () {
  ['foo' => $foo, 'bar' => $bar] = authChain();
  $fooFn = static fn(AuthorizeArgs $args) => true;
  $foo->authorize($fooFn);
  $bar->skipAuthorization();

  $api = new Api();

  // /foo
  expect(authChainRequest([$foo])->resolveAuthorizers($api))->toBe([$fooFn]);

  // /foo/bar
  expect(authChainRequest([$foo, $bar])->resolveAuthorizers($api))->toBe([]);
});

test('authorization scenario 2: authorizer on child service', function () {
  ['foo' => $foo, 'bar' => $bar] = authChain();
  $barFn = static fn(AuthorizeArgs $args) => true;
  $bar->authorize($barFn);

  $api = new Api();

  // /foo
  expect(authChainRequest([$foo])->resolveAuthorizers($api))->toBe([]);

  // /foo/bar
  expect(authChainRequest([$foo, $bar])->resolveAuthorizers($api))->toBe([
    $barFn,
  ]);
});

test('authorization scenario 3: skip on grandchild service', function () {
  ['foo' => $foo, 'bar' => $bar, 'baz' => $baz] = authChain();
  $barFn = static fn(AuthorizeArgs $args) => true;
  $bar->authorize($barFn);
  $baz->skipAuthorization();

  $api = new Api();

  // /foo
  expect(authChainRequest([$foo])->resolveAuthorizers($api))->toBe([]);

  // /foo/bar
  expect(authChainRequest([$foo, $bar])->resolveAuthorizers($api))->toBe([
    $barFn,
  ]);

  // /foo/bar/baz
  expect(authChainRequest([$foo, $bar, $baz])->resolveAuthorizers($api))->toBe(
    [],
  );
});

test(
  'authorization: skip on child service bypasses Api authorizer',
  function () {
    ['foo' => $foo, 'bar' => $bar] = authChain();
    $apiFn = static fn(AuthorizeArgs $args) => true;
    $bar->skipAuthorization();

    $api = new Api();
    $api->authorize($apiFn);

    // /foo
    expect(authChainRequest([$foo])->resolveAuthorizers($api))->toBe([$apiFn]);

    // /foo/bar
    expect(authChainRequest([$foo, $bar])->resolveAuthorizers($api))->toBe([]);
  },
);
