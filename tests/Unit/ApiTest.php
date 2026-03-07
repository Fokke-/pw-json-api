<?php

use ProcessWire\FoodService;
use ProcessWire\FruitService;
use PwJsonApi\Api;
use PwJsonApi\Service;
use PwJsonApi\Endpoint;

test('base path is formatted', function () {
  $api = new Api();
  expect($api->getBasePath())->toBe(null);

  $api->setBasePath('/');
  expect($api->getBasePath())->toBe(null);

  $api->setBasePath('/FOOBAR/');
  expect($api->getBasePath())->toBe('foobar');

  $api->setBasePath('/foo//bar////baz');
  expect($api->getBasePath())->toBe('foo/bar/baz');
});

test('add service', function () {
  $api = new Api();
  $api->addService(new FoodService());

  expect($api->getService('FoodService') instanceof Service)->toBe(true);
});

test('add service with subservice', function () {
  $api = new Api();
  $api->addService(new FoodService(), function ($service) {
    $service->addService(new FruitService());
  });

  expect($api->getService('FoodService') instanceof Service)->toBe(true);
  expect($api->findService('FruitService') instanceof Service)->toBe(true);
});

test('endpoint can be found', function () {
  $api = new Api();
  $api->addService(new FoodService(), function ($service) {
    $service->addService(new FruitService());
  });

  $api->run();

  expect($api->findEndpoint('/food') instanceof Endpoint)->toBe(true);
  expect($api->findEndpoint('/food/fruits/orange') instanceof Endpoint)->toBe(
    true,
  );

  expect(
    $api->findService('FoodService')->findEndpoint('/') instanceof Endpoint,
  )->toBe(true);
});

test('duplicate services are not allowed', function () {
  $api = new Api();
  $api->addService(new FoodService());
  $api->addService(new FoodService());

  $api->run();
})->throws(
  \ProcessWire\WireException::class,
  "Duplicated service 'FoodService'",
);

test('api is set on service after run', function () {
  $api = new Api();
  $api->addService(new FoodService());

  $api->run();

  $service = $api->getService('FoodService');
  $ref = new ReflectionProperty($service, 'api');
  expect($ref->getValue($service))->toBe($api);
});

test('configure sets config values', function () {
  $api = new Api();
  $api->configure(function ($config) {
    $config->trailingSlashes = true;
    $config->jsonFlags = JSON_PRETTY_PRINT;
  });

  $ref = new ReflectionProperty($api, 'config');
  $config = $ref->getValue($api);
  expect($config->trailingSlashes)->toBe(true);
  expect($config->jsonFlags)->toBe(JSON_PRETTY_PRINT);
});

test('duplicate endpoint paths are not allowed', function () {
  $api = new Api();
  $api->addService(new FruitService());

  $service = new FruitService();
  $service->name = 'FruitService2';
  $api->addService($service);

  $api->run();
})->throws(
  \ProcessWire\WireException::class,
  "Duplicated endpoint path '/fruits/?' (defined in service 'FruitService2').",
);
