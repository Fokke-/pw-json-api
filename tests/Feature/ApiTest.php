<?php

test('query endpoint of service without base path', function () {
  $client = getHttp();

  $res = $client->get('food/carrot');
  expect($res->getStatusCode())->toBe(200);

  $res = $client->get('food/vegetables/carrot');
  expect($res->getStatusCode())->toBe(404);
});

test('query endpoint of service with base path', function () {
  $client = getHttp();

  $res = $client->get('food/fruits/apple');
  expect($res->getStatusCode())->toBe(200);

  $res = $client->get('food/apple');
  expect($res->getStatusCode())->toBe(404);
});

test('after hook can manipulate response', function () {
  $res = getResponse('food/fruits/apple');
  expect($res)->toHaveKey('food_type');
  expect($res['food_type'])->toBe('fruit');
  expect($res)->toHaveKey('fruit');
  expect($res['fruit'])->toBe('apple');
});

test('after hooks are executed in right order', function () {
  $res = getResponse('food');

  expect($res['_after_hook_execution_order'])->toBe([
    'endpoint',
    'service',
    'api',
  ]);
});
