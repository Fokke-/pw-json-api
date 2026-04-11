<?php

use PwJsonApi\Auth\{ProcessWireAuth, ProcessWireAuthService};
use PwJsonApi\{AuthenticationException, Authenticator, Service};

test('ProcessWireAuth extends Authenticator', function () {
  $auth = new ProcessWireAuth();
  expect($auth)->toBeInstanceOf(Authenticator::class);
});

test('ProcessWireAuthService extends Service', function () {
  $service = new ProcessWireAuthService();
  $service->_prepare();
  expect($service)->toBeInstanceOf(Service::class);
});

test('ProcessWireAuthService has /auth base path', function () {
  $service = new ProcessWireAuthService();
  $service->_prepare();
  expect($service->getBasePath())->toBe('auth');
});

test('ProcessWireAuthService has login endpoint', function () {
  $service = new ProcessWireAuthService();
  $service->_prepare();
  $endpoint = $service->findEndpoint('/login');
  expect($endpoint)->not->toBeNull();
  expect($endpoint->getAllowedMethods())->toBe(['POST']);
});

test('ProcessWireAuthService has logout endpoint', function () {
  $service = new ProcessWireAuthService();
  $service->_prepare();
  $endpoint = $service->findEndpoint('/logout');
  expect($endpoint)->not->toBeNull();
  expect($endpoint->getAllowedMethods())->toBe(['POST']);
});
