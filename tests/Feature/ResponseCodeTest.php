<?php

test('existing endpoint', function () {
  $client = getHttp();
  $res = $client->get('food');

  expect($res->getStatusCode())->toBe(200);
});

test('non-existent endpoint', function () {
  $client = getHttp();
  $res = $client->get('non-existent-endpoint');

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
});
