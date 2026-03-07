<?php

use ProcessWire\FoodService;
use ProcessWire\FruitService;
use ProcessWire\HooksChildService;
use ProcessWire\HooksService;
use PwJsonApi\ApiSearchEndpointResult;
use PwJsonApi\Endpoint;

test('resolvePath() with single service', function () {
  $service = new FoodService();
  $service->_prepare();
  $endpoint = $service->findEndpoint('/');

  $result = new ApiSearchEndpointResult($endpoint, $service, [$service]);

  expect($result->resolvePath())->toBe('/food');
  expect($result->resolvePath('api'))->toBe('/api/food');
});

test('resolvePath() with nested services', function () {
  $parent = new FoodService();
  $parent->_prepare();
  $child = new FruitService();
  $child->_prepare();

  $endpoint = $child->findEndpoint('/orange');

  $result = new ApiSearchEndpointResult($endpoint, $child, [$parent, $child]);

  expect($result->resolvePath())->toBe('/food/fruits/orange');
  expect($result->resolvePath('api'))->toBe('/api/food/fruits/orange');
});

test('resolvePath() with deeply nested services', function () {
  $hooks = new HooksService();
  $hooks->_prepare();
  $child = new HooksChildService();
  $child->_prepare();

  $endpoint = $child->findEndpoint('/');

  $result = new ApiSearchEndpointResult($endpoint, $child, [$hooks, $child]);

  expect($result->resolvePath())->toBe('/hooks/nested');
  expect($result->resolvePath('api'))->toBe('/api/hooks/nested');
});

test('resolvePath() with root endpoint', function () {
  $service = new FoodService();
  $service->_prepare();
  $endpoint = $service->findEndpoint('/');

  $result = new ApiSearchEndpointResult($endpoint, $service, [$service]);

  expect($result->resolvePath())->toBe('/food');
});

test('resolvePath() without base path', function () {
  $endpoint = new Endpoint('/hello');

  $service = new FruitService();
  $service->_prepare();
  $service->setBasePath(null);

  $result = new ApiSearchEndpointResult($endpoint, $service, [$service]);

  expect($result->resolvePath())->toBe('/hello');
  expect($result->resolvePath('api'))->toBe('/api/hello');
});
