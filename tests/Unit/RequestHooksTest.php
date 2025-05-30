<?php

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
    []
  );

  $hooks = new RequestHooks(RequestHookKey::cases());
  expect(array_keys($hooks->getItems()))->toBe($keys);
});

test('get()', function () {
  $hooks = new RequestHooks(RequestHookKey::cases());

  expect($hooks->get(RequestHookKey::After))->toBe([]);
});

test('add()', function () {
  $hooks = new RequestHooks(RequestHookKey::cases());
  $hooks->add(RequestHookKey::After, function () {});
  $hooks->add(RequestHookKey::After, function () {});

  expect($hooks->get(RequestHookKey::After))->toHaveCount(2);
});

test('find()', function () {
  $hooks = new RequestHooks(RequestHookKey::cases());
  $hooks->add(RequestHookKey::Before, function () {});
  $hooks->add(RequestHookKey::After, function () {});
  $hooks->add(RequestHookKey::BeforePost, function () {});
  $hooks->add(RequestHookKey::AfterGet, function () {});

  expect($hooks->find(HookTiming::Before))->toBeArray();
  expect($hooks->find(HookTiming::After))->toBeArray();
  expect($hooks->find(HookTiming::Before, RequestMethod::Post))->toBeArray();
  expect($hooks->find(HookTiming::After, RequestMethod::Get))->toBeArray();
});
