<?php

use function ProcessWire\wire;

beforeEach(function () {
  wire()->cache->deleteFor('PwJsonApi_RateLimit');
});

test('requests within the limit succeed with rate limit headers', function () {
  $client = getHttp('rate-limit-api');
  $res = $client->get('');

  expect($res->getStatusCode())->toBe(200);
  expect($res->getHeaderLine('X-RateLimit-Limit'))->toBe('3');
  expect($res->getHeaderLine('X-RateLimit-Remaining'))->not()->toBe('');
  expect($res->getHeaderLine('X-RateLimit-Reset'))->not()->toBe('');
});

test('requests over the limit return 429 with retry_after', function () {
  $client = getHttp('rate-limit-api');

  // Make requests up to the limit
  for ($i = 0; $i < 3; $i++) {
    $res = $client->get('');
    expect($res->getStatusCode())->toBe(200);
  }

  // This request should be rate limited
  $res = $client->get('');
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(429);
  expect($json['error'])->toBe('Rate limit exceeded');
  expect($json['retry_after'])->toBeInt();
  expect($res->getHeaderLine('Retry-After'))->not()->toBe('');
  expect($res->getHeaderLine('X-RateLimit-Limit'))->toBe('3');
  expect($res->getHeaderLine('X-RateLimit-Remaining'))->toBe('0');
  expect($res->getHeaderLine('X-RateLimit-Reset'))->not()->toBe('');
});

test('plugin works at API level', function () {
  $client = getHttp('rate-limit-api');

  for ($i = 0; $i < 3; $i++) {
    $res = $client->get('');
    expect($res->getStatusCode())->toBe(200);
  }

  $res = $client->get('');
  expect($res->getStatusCode())->toBe(429);
});

test('plugin works at service level', function () {
  $client = getHttp('rate-limit-service');

  for ($i = 0; $i < 3; $i++) {
    $res = $client->get('');
    expect($res->getStatusCode())->toBe(200);
  }

  $res = $client->get('');
  expect($res->getStatusCode())->toBe(429);
});

test('plugin works at endpoint level', function () {
  $client = getHttp('rate-limit-endpoint');

  for ($i = 0; $i < 3; $i++) {
    $res = $client->get('');
    expect($res->getStatusCode())->toBe(200);
  }

  $res = $client->get('');
  expect($res->getStatusCode())->toBe(429);
});
