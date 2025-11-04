<?php

test('request hook arguments', function () {
  $res = getResponse('hooks/hello-world');

  expect($res['hook_args']['event'])->toBe('ProcessWire\\HookEvent');
  expect($res['hook_args']['method'])->toBe('GET');
  expect($res['hook_args']['endpoint'])->toBe('PwJsonApi\\Endpoint');
  expect($res['hook_args']['service'])->toBe('ProcessWire\\HelloWorldService');
  expect($res['hook_args']['services'])->toBe('PwJsonApi\\ServiceList');
  expect($res['hook_args']['api'])->toBe('PwJsonApi\\Api');
});

test('before hooks are executed in right order', function () {
  $res = getResponse('hooks/hello-world');

  expect($res['_before_hook_execution_order'])->toBe([
    'api',
    'service',
    'endpoint',
  ]);
});

test('after hooks are executed in right order', function () {
  $res = getResponse('hooks/hello-world');

  expect($res['_after_hook_execution_order'])->toBe([
    'endpoint',
    'service',
    'api',
  ]);
});

test('after hook can manipulate response', function () {
  $res = getResponse('api/food/fruits/apple');
  expect($res)->toHaveKey('food_type');
  expect($res['food_type'])->toBe('fruit');
  expect($res)->toHaveKey('fruit');
  expect($res['fruit'])->toBe('apple');
});

test('error hook arguments', function () {
  $res = getResponse('exception-hooks/');

  expect($res['hook_args']['event'])->toBe('ProcessWire\\HookEvent');
  expect($res['hook_args']['response'])->toBe('PwJsonApi\\Response');
  expect($res['hook_args']['method'])->toBe('GET');
  expect($res['hook_args']['endpoint'])->toBe('PwJsonApi\\Endpoint');
  expect($res['hook_args']['service'])->toBe('ProcessWire\\ExceptionService');
  expect($res['hook_args']['services'])->toBe('PwJsonApi\\ServiceList');
  expect($res['hook_args']['api'])->toBe('PwJsonApi\\Api');
});

test('error hooks are executed in right order', function () {
  $res = getResponse('exception-hooks/');

  expect($res['_error_hook_execution_order'])->toBe([
    'endpoint',
    'service',
    'api',
  ]);
});
