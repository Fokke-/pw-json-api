<?php

use ProcessWire\{FoodService, FruitService, WireException};
use PwJsonApi\{
  Api,
  AuthenticateArgs,
  Authenticator,
  AuthenticationException,
  AuthorizationException,
  AuthorizeArgs,
  Endpoint,
};

// --- AuthenticationException ---

test('AuthenticationException has code 401', function () {
  $e = new AuthenticationException();
  expect($e->response->code)->toBe(401);
});

test('AuthenticationException extends ApiException', function () {
  $e = new AuthenticationException();
  expect($e)->toBeInstanceOf(\PwJsonApi\ApiException::class);
});

// --- AuthorizationException ---

test('AuthorizationException has code 403', function () {
  $e = new AuthorizationException();
  expect($e->response->code)->toBe(403);
});

test('AuthorizationException extends ApiException', function () {
  $e = new AuthorizationException();
  expect($e)->toBeInstanceOf(\PwJsonApi\ApiException::class);
});

// --- HasAuthentication ---

test('authenticate() sets authenticator on Api', function () {
  $api = new Api();
  $auth = new class extends Authenticator {
    public function authenticate(AuthenticateArgs $args): void {}
  };

  $api->authenticate($auth);
  expect($api->_getAuthenticator())->toBe($auth);
});

test('authenticate() sets authenticator on Service', function () {
  $service = new FoodService();
  $service->_prepare();
  $auth = new class extends Authenticator {
    public function authenticate(AuthenticateArgs $args): void {}
  };

  $service->authenticate($auth);
  expect($service->_getAuthenticator())->toBe($auth);
});

test('authenticate() sets authenticator on Endpoint', function () {
  $endpoint = new Endpoint('/test');
  $auth = new class extends Authenticator {
    public function authenticate(AuthenticateArgs $args): void {}
  };

  $endpoint->authenticate($auth);
  expect($endpoint->_getAuthenticator())->toBe($auth);
});

test('authenticate() returns static for fluent interface', function () {
  $api = new Api();
  $auth = new class extends Authenticator {
    public function authenticate(AuthenticateArgs $args): void {}
  };

  $result = $api->authenticate($auth);
  expect($result)->toBe($api);
});

test('_getAuthenticator() returns null by default', function () {
  $api = new Api();
  expect($api->_getAuthenticator())->toBeNull();
});

test('locked api rejects authenticate()', function () {
  $api = new Api();
  $api->run();

  $auth = new class extends Authenticator {
    public function authenticate(AuthenticateArgs $args): void {}
  };

  $api->authenticate($auth);
})->throws(WireException::class, 'Cannot set authenticator');

test('locked service rejects authenticate()', function () {
  $api = new Api();
  $api->addService(new FoodService());
  $api->run();

  $service = $api->getService('FoodService');
  $auth = new class extends Authenticator {
    public function authenticate(AuthenticateArgs $args): void {}
  };

  $service->authenticate($auth);
})->throws(WireException::class, 'Cannot set authenticator');

test('locked endpoint rejects authenticate()', function () {
  $api = new Api();
  $api->addService(new FruitService());
  $api->run();

  $endpoint = $api->findEndpoint('/fruits');
  $auth = new class extends Authenticator {
    public function authenticate(AuthenticateArgs $args): void {}
  };

  $endpoint->authenticate($auth);
})->throws(WireException::class, 'Cannot set authenticator');

// --- HasAuthorization ---

test('authorize() sets authorizer on Api', function () {
  $api = new Api();
  $fn = static fn(AuthorizeArgs $args) => true;

  $api->authorize($fn);
  expect($api->_getAuthorizer())->toBe($fn);
});

test('authorize() sets authorizer on Service', function () {
  $service = new FoodService();
  $service->_prepare();
  $fn = static fn(AuthorizeArgs $args) => true;

  $service->authorize($fn);
  expect($service->_getAuthorizer())->toBe($fn);
});

test('authorize() sets authorizer on Endpoint', function () {
  $endpoint = new Endpoint('/test');
  $fn = static fn(AuthorizeArgs $args) => true;

  $endpoint->authorize($fn);
  expect($endpoint->_getAuthorizer())->toBe($fn);
});

test('authorize() returns static for fluent interface', function () {
  $api = new Api();
  $fn = static fn(AuthorizeArgs $args) => true;

  $result = $api->authorize($fn);
  expect($result)->toBe($api);
});

test('_getAuthorizer() returns null by default', function () {
  $api = new Api();
  expect($api->_getAuthorizer())->toBeNull();
});

test('locked api rejects authorize()', function () {
  $api = new Api();
  $api->run();

  $api->authorize(static fn(AuthorizeArgs $args) => true);
})->throws(WireException::class, 'Cannot set authorizer');

test('locked service rejects authorize()', function () {
  $api = new Api();
  $api->addService(new FoodService());
  $api->run();

  $service = $api->getService('FoodService');
  $service->authorize(static fn(AuthorizeArgs $args) => true);
})->throws(WireException::class, 'Cannot set authorizer');

test('locked endpoint rejects authorize()', function () {
  $api = new Api();
  $api->addService(new FruitService());
  $api->run();

  $endpoint = $api->findEndpoint('/fruits');
  $endpoint->authorize(static fn(AuthorizeArgs $args) => true);
})->throws(WireException::class, 'Cannot set authorizer');
