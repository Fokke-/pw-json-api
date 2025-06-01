<?php

use PwJsonApi\Response;

test('empty response', function () {
  $response = new Response();
  expect($response->data)->toBe([]);
  expect($response->code)->toBe(200);
});

test('response with data', function () {
  $response = new Response(['foo' => 'bar']);
  expect($response->data)->toBe(['foo' => 'bar']);
});

test('response with custom code', function () {
  $response = (new Response(null))->code(201);
  expect($response->code)->toBe(201);
});

test('response with extra keys', function () {
  $response = (new Response())->with(['foo' => 'bar']);
  expect($response->additionalData)->toBe(['foo' => 'bar']);
});

test('toArray()', function () {
  $response = new Response();
  expect($response->toArray())->toBe(['data' => []]);

  $response = new Response(['foo' => 'bar']);
  expect($response->toArray())->toBe(['data' => ['foo' => 'bar']]);
  expect($response->toArray(false))->toBe(null);

  $response = (new Response(['foo' => 'bar']))->with(['foo' => 'bar']);
  expect($response->toArray())->toBe([
    'data' => [
      'foo' => 'bar',
    ],
    'foo' => 'bar',
  ]);
  expect($response->toArray(false))->toBe([
    'foo' => 'bar',
  ]);
});

test('toJson()', function () {
  $response = new Response();
  expect($response->toJson())->toBe('{"data":[]}');

  $response = new Response(['foo' => 'bar']);
  expect($response->toJson())->toBe('{"data":{"foo":"bar"}}');
  expect($response->toJson(0, false))->toBe(null);

  $response = (new Response(['foo' => 'bar']))->with(['foo' => 'bar']);
  expect($response->toJson())->toBe('{"data":{"foo":"bar"},"foo":"bar"}');
  expect($response->toJson(0, false))->toBe('{"foo":"bar"}');
});
