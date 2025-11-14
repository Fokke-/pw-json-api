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
