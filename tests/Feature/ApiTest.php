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

test('method not allowed', function () {
  $client = getHttp();
  $res = $client->post('food');

  expect($res->getStatusCode())->toBe(405);
});

test('options method is always accepted', function () {
  $client = getHttp();
  $res = $client->request('options', 'food');
  expect($res->getStatusCode())->toBe(200);

  $res = $client->request('options', 'non-existant-endpoint');
  expect($res->getStatusCode())->toBe(200);
});

test('request method handlers', function () {
  $res = getResponse('methods/', 'get');
  expect($res)->toHaveKey('method');
  expect($res['method'])->toBe('GET');

  $res = getResponse('methods/', 'put');
  expect($res)->toHaveKey('method');
  expect($res['method'])->toBe('PUT');

  $res = getResponse('methods/', 'delete');
  expect($res)->toHaveKey('method');
  expect($res['method'])->toBe('DELETE');

  $res = getResponse('methods/', 'post');
  expect($res)->toHaveKey('method');
  expect($res['method'])->toBe('POST');

  $res = getResponse('methods/', 'patch');
  expect($res)->toHaveKey('method');
  expect($res['method'])->toBe('PATCH');
});

test('before hooks are executed in right order', function () {
  $res = getResponse('/hooks/hello-world');

  expect($res['_before_hook_execution_order'])->toBe([
    'api',
    'service',
    'endpoint',
  ]);
});

test('after hooks are executed in right order', function () {
  $res = getResponse('/hooks/hello-world');

  expect($res['_after_hook_execution_order'])->toBe([
    'endpoint',
    'service',
    'api',
  ]);
});

test('after hook can manipulate response', function () {
  $res = getResponse('food/fruits/apple');
  expect($res)->toHaveKey('food_type');
  expect($res['food_type'])->toBe('fruit');
  expect($res)->toHaveKey('fruit');
  expect($res['fruit'])->toBe('apple');
});
