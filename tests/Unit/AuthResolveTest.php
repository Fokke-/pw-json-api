<?php

use ProcessWire\{FoodService, FruitService};
use PwJsonApi\{
  Api,
  AuthenticateArgs,
  AuthInterface,
  AuthorizeArgs,
  ApiSearchEndpointResult,
  Endpoint,
};

// --- resolveAuthenticator ---

test('resolveAuthenticator() returns null when no auth is set', function () {
  $api = new Api();
  $service = new FoodService();
  $service->_prepare();
  $endpoint = $service->findEndpoint('/');

  $result = new ApiSearchEndpointResult($endpoint, $service, [$service]);

  expect($result->resolveAuthenticator($api))->toBeNull();
});

test('resolveAuthenticator() returns Api authenticator', function () {
  $api = new Api();
  $auth = new class implements AuthInterface {
    public function authenticate(AuthenticateArgs $args): void {}
  };
  $api->authenticate($auth);

  $service = new FoodService();
  $service->_prepare();
  $endpoint = $service->findEndpoint('/');

  $result = new ApiSearchEndpointResult($endpoint, $service, [$service]);

  expect($result->resolveAuthenticator($api))->toBe($auth);
});

test(
  'resolveAuthenticator() returns Service authenticator over Api',
  function () {
    $api = new Api();
    $apiAuth = new class implements AuthInterface {
      public function authenticate(AuthenticateArgs $args): void {}
    };
    $api->authenticate($apiAuth);

    $serviceAuth = new class implements AuthInterface {
      public function authenticate(AuthenticateArgs $args): void {}
    };

    $service = new FoodService();
    $service->_prepare();
    $service->authenticate($serviceAuth);
    $endpoint = $service->findEndpoint('/');

    $result = new ApiSearchEndpointResult($endpoint, $service, [$service]);

    expect($result->resolveAuthenticator($api))->toBe($serviceAuth);
  },
);

test(
  'resolveAuthenticator() returns Endpoint authenticator over Service and Api',
  function () {
    $api = new Api();
    $apiAuth = new class implements AuthInterface {
      public function authenticate(AuthenticateArgs $args): void {}
    };
    $api->authenticate($apiAuth);

    $serviceAuth = new class implements AuthInterface {
      public function authenticate(AuthenticateArgs $args): void {}
    };
    $endpointAuth = new class implements AuthInterface {
      public function authenticate(AuthenticateArgs $args): void {}
    };

    $service = new FoodService();
    $service->_prepare();
    $service->authenticate($serviceAuth);
    $endpoint = $service->findEndpoint('/');
    $endpoint->authenticate($endpointAuth);

    $result = new ApiSearchEndpointResult($endpoint, $service, [$service]);

    expect($result->resolveAuthenticator($api))->toBe($endpointAuth);
  },
);

test(
  'resolveAuthenticator() returns leaf service authenticator in nested services',
  function () {
    $api = new Api();

    $parentAuth = new class implements AuthInterface {
      public function authenticate(AuthenticateArgs $args): void {}
    };
    $childAuth = new class implements AuthInterface {
      public function authenticate(AuthenticateArgs $args): void {}
    };

    $parent = new FoodService();
    $parent->_prepare();
    $parent->authenticate($parentAuth);

    $child = new FruitService();
    $child->_prepare();
    $child->authenticate($childAuth);

    $endpoint = $child->findEndpoint('/');

    $result = new ApiSearchEndpointResult($endpoint, $child, [$parent, $child]);

    expect($result->resolveAuthenticator($api))->toBe($childAuth);
  },
);

// --- resolveAuthorizers ---

test(
  'resolveAuthorizers() returns empty array when no authorizer is set',
  function () {
    $api = new Api();
    $service = new FoodService();
    $service->_prepare();
    $endpoint = $service->findEndpoint('/');

    $result = new ApiSearchEndpointResult($endpoint, $service, [$service]);

    expect($result->resolveAuthorizers($api))->toBe([]);
  },
);

test('resolveAuthorizers() collects from Api', function () {
  $api = new Api();
  $fn = static fn(AuthorizeArgs $args) => true;
  $api->authorize($fn);

  $service = new FoodService();
  $service->_prepare();
  $endpoint = $service->findEndpoint('/');

  $result = new ApiSearchEndpointResult($endpoint, $service, [$service]);

  expect($result->resolveAuthorizers($api))->toBe([$fn]);
});

test('resolveAuthorizers() chains Api → Service → Endpoint', function () {
  $api = new Api();
  $apiFn = static fn(AuthorizeArgs $args) => true;
  $api->authorize($apiFn);

  $service = new FoodService();
  $service->_prepare();
  $serviceFn = static fn(AuthorizeArgs $args) => true;
  $service->authorize($serviceFn);

  $endpoint = $service->findEndpoint('/');
  $endpointFn = static fn(AuthorizeArgs $args) => true;
  $endpoint->authorize($endpointFn);

  $result = new ApiSearchEndpointResult($endpoint, $service, [$service]);

  expect($result->resolveAuthorizers($api))->toBe([
    $apiFn,
    $serviceFn,
    $endpointFn,
  ]);
});

test(
  'resolveAuthorizers() chains Api → Parent → Child → Endpoint in nested services',
  function () {
    $api = new Api();
    $apiFn = static fn(AuthorizeArgs $args) => true;
    $api->authorize($apiFn);

    $parent = new FoodService();
    $parent->_prepare();
    $parentFn = static fn(AuthorizeArgs $args) => true;
    $parent->authorize($parentFn);

    $child = new FruitService();
    $child->_prepare();
    $childFn = static fn(AuthorizeArgs $args) => true;
    $child->authorize($childFn);

    $endpoint = $child->findEndpoint('/');
    $endpointFn = static fn(AuthorizeArgs $args) => true;
    $endpoint->authorize($endpointFn);

    $result = new ApiSearchEndpointResult($endpoint, $child, [$parent, $child]);

    expect($result->resolveAuthorizers($api))->toBe([
      $apiFn,
      $parentFn,
      $childFn,
      $endpointFn,
    ]);
  },
);

test('resolveAuthorizers() skips levels without authorizer', function () {
  $api = new Api();

  $service = new FoodService();
  $service->_prepare();
  $serviceFn = static fn(AuthorizeArgs $args) => true;
  $service->authorize($serviceFn);

  $endpoint = $service->findEndpoint('/');

  $result = new ApiSearchEndpointResult($endpoint, $service, [$service]);

  expect($result->resolveAuthorizers($api))->toBe([$serviceFn]);
});
