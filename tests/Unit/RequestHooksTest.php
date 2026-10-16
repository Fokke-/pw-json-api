<?php

use ProcessWire\{FoodService, FruitService};
use PwJsonApi\{ApiSearchEndpointResult, Endpoint};
use PwJsonApi\RequestHooks;
use PwJsonApi\RequestHookKey;
use PwJsonApi\HookTiming;
use PwJsonApi\RequestMethod;

test('all keys in enum are defined', function () {
  $keys = array_reduce(
    RequestHookKey::cases(),
    function ($acc, $item) {
      $acc[] = $item->name;
      return $acc;
    },
    [],
  );

  $hooks = new RequestHooks();
  expect(array_keys($hooks->getItems()))->toBe($keys);
});

test('get()', function () {
  $hooks = new RequestHooks();

  expect($hooks->get(RequestHookKey::After))->toBe([]);
});

test('add()', function () {
  $hooks = new RequestHooks();
  $hooks->add(RequestHookKey::After, function () {});
  $hooks->add(RequestHookKey::After, function () {});

  expect($hooks->get(RequestHookKey::After))->toHaveCount(2);
});

test('find()', function () {
  $hooks = new RequestHooks();
  $hooks->add(RequestHookKey::Before, function () {});
  $hooks->add(RequestHookKey::After, function () {});
  $hooks->add(RequestHookKey::BeforePost, function () {});
  $hooks->add(RequestHookKey::AfterGet, function () {});

  expect($hooks->find(HookTiming::Before))->toBeArray();
  expect($hooks->find(HookTiming::After))->toBeArray();
  expect($hooks->find(HookTiming::Before, RequestMethod::Post))->toBeArray();
  expect($hooks->find(HookTiming::After, RequestMethod::Get))->toBeArray();
});

test('hookOnRequest() adds hook with OnRequest key', function () {
  $endpoint = new Endpoint('/test');
  $fn = function () {};

  expect($endpoint->hookOnRequest($fn))->toBe($endpoint);
  expect($endpoint->getRequestHooks(RequestHookKey::OnRequest))->toBe([$fn]);
});

test(
  'resolveOnRequestHooks() orders services root to leaf, then endpoint',
  function () {
    $parent = new FoodService();
    $parent->_prepare();
    $parentFn = function () {};
    $parent->hookOnRequest($parentFn);

    $child = new FruitService();
    $child->_prepare();
    $childFn = function () {};
    $child->hookOnRequest($childFn);

    $endpoint = $child->findEndpoint('/');
    $endpointFn = function () {};
    $endpoint->hookOnRequest($endpointFn);

    $result = new ApiSearchEndpointResult($endpoint, $child, [$parent, $child]);

    expect($result->resolveOnRequestHooks())->toBe([
      $parentFn,
      $childFn,
      $endpointFn,
    ]);
  },
);
