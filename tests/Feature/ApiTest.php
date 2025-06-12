<?php

test('query endpoint of service without base path', function () {
  $client = getHttp();

  $res = $client->get('api/food/carrot');
  expect($res->getStatusCode())->toBe(200);

  $res = $client->get('api/food/vegetables/carrot');
  expect($res->getStatusCode())->toBe(404);
});

test('query endpoint of service with base path', function () {
  $client = getHttp();

  $res = $client->get('api/food/fruits/apple');
  expect($res->getStatusCode())->toBe(200);

  $res = $client->get('api/food/apple');
  expect($res->getStatusCode())->toBe(404);
});

test('method not allowed', function () {
  $client = getHttp();
  $res = $client->post('api/food');

  expect($res->getStatusCode())->toBe(405);
});

test('options method is always accepted', function () {
  $client = getHttp();
  $res = $client->request('options', 'api/food');
  expect($res->getStatusCode())->toBe(200);

  $res = $client->request('options', 'api/non-existant-endpoint');
  expect($res->getStatusCode())->toBe(200);
});

test('request method handlers', function () {
  $res = getResponse('api/methods/', 'get');
  expect($res)->toHaveKey('method');
  expect($res['method'])->toBe('GET');

  $res = getResponse('api/methods/', 'put');
  expect($res)->toHaveKey('method');
  expect($res['method'])->toBe('PUT');

  $res = getResponse('api/methods/', 'delete');
  expect($res)->toHaveKey('method');
  expect($res['method'])->toBe('DELETE');

  $res = getResponse('api/methods/', 'post');
  expect($res)->toHaveKey('method');
  expect($res['method'])->toBe('POST');

  $res = getResponse('api/methods/', 'patch');
  expect($res)->toHaveKey('method');
  expect($res['method'])->toBe('PATCH');
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

test('error hooks are executed in right order', function () {
  $res = getResponse('exception-hooks/');

  expect($res['_error_hook_execution_order'])->toBe([
    'endpoint',
    'service',
    'api',
  ]);
});
