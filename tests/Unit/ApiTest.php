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

  expect($api->findEndpoint('/food') instanceof Endpoint)->toBe(true);
  expect($api->findEndpoint('/food/fruits/orange') instanceof Endpoint)->toBe(
    true,
  );

  expect(
    $api->findService('FoodService')->findEndpoint('/') instanceof Endpoint,
  )->toBe(true);
});
