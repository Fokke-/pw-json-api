<?php

use ProcessWire\FoodService;

test('name equals to class name', function () {
  $service = new FoodService();
  expect($service->name)->toBe('FoodService');
});

test('_prepare() calls init()', function () {
  $service = new FoodService();
  $service->_prepare();
  expect($service->getEndpoints())->not->toBeEmpty();
});

test('_prepare() only calls init() once', function () {
  $service = new FoodService();
  $service->_prepare();
  $service->_prepare();
  expect($service->getEndpoints())->toHaveCount(1);
});

test('_prepare() returns the service instance', function () {
  $service = new FoodService();
  expect($service->_prepare())->toBe($service);
});
