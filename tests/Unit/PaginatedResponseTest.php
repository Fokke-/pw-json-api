<?php

use PwJsonApi\PaginatedResponse;

test('toArray() includes pagination keys', function () {
  $response = (new PaginatedResponse(['foo' => 'bar']))
    ->start(0)
    ->limit(10)
    ->total(25);

  $result = $response->toArray();

  expect($result)->toHaveKey('data');
  expect($result)->toHaveKey('pagination');
  expect($result['pagination'])->toBe([
    'start' => 0,
    'limit' => 10,
    'total' => 25,
    'page' => 1,
    'pages' => 3,
  ]);
});

test('computed page and pages values', function () {
  $response = (new PaginatedResponse([]))->start(10)->limit(10)->total(85);

  $result = $response->toArray();

  expect($result['pagination']['page'])->toBe(2);
  expect($result['pagination']['pages'])->toBe(9);
});

test('start=0, total=0', function () {
  $response = (new PaginatedResponse([]))->start(0)->limit(10)->total(0);

  $result = $response->toArray();

  expect($result['pagination']['page'])->toBe(1);
  expect($result['pagination']['pages'])->toBe(0);
});

test('limit=0 with results returns pages=1', function () {
  $response = (new PaginatedResponse([]))->start(0)->limit(0)->total(8);

  $result = $response->toArray();

  expect($result['pagination']['page'])->toBe(1);
  expect($result['pagination']['pages'])->toBe(1);
});

test('limit=0, total=0 returns pages=0', function () {
  $response = (new PaginatedResponse([]))->start(0)->limit(0)->total(0);

  $result = $response->toArray();

  expect($result['pagination']['page'])->toBe(1);
  expect($result['pagination']['pages'])->toBe(0);
});

test('throws LogicException when start is missing', function () {
  $response = (new PaginatedResponse([]))->limit(10)->total(25);

  $response->toArray();
})->throws(
  LogicException::class,
  'PaginatedResponse requires start, limit, and total to be set',
);

test('throws LogicException when limit is missing', function () {
  $response = (new PaginatedResponse([]))->start(0)->total(25);

  $response->toArray();
})->throws(
  LogicException::class,
  'PaginatedResponse requires start, limit, and total to be set',
);

test('throws LogicException when total is missing', function () {
  $response = (new PaginatedResponse([]))->start(0)->limit(10);

  $response->toArray();
})->throws(
  LogicException::class,
  'PaginatedResponse requires start, limit, and total to be set',
);

test('fluent interface returns static', function () {
  $response = new PaginatedResponse([]);
  expect($response->start(0))->toBe($response);
  expect($response->limit(10))->toBe($response);
  expect($response->total(25))->toBe($response);
});

test('inherits Response functionality', function () {
  $response = (new PaginatedResponse(['foo' => 'bar']))
    ->start(0)
    ->limit(10)
    ->total(5)
    ->with(['message' => 'ok'])
    ->code(200);

  $result = $response->toArray();

  expect($result['data'])->toBe(['foo' => 'bar']);
  expect($result['message'])->toBe('ok');
  expect($result)->toHaveKey('pagination');
});
