<?php

test('default response code', function () {
  $client = getHttp();
  $res = $client->get('exceptions/');

  expect($res->getStatusCode())->toBe(400);
});

test('custom response code', function () {
  $client = getHttp();
  $res = $client->get('exceptions/custom-code');

  expect($res->getStatusCode())->toBe(401);
});

test('if message is passed, response includes it', function () {
  $client = getHttp();
  $res = $client->get('exceptions/');
  $data = resToJson($res);

  expect($data)->toHaveKey('error');
  expect($data['error'])->toBe('This was doomed to fail!');
});

test('if message is not passed, response is empty', function () {
  $client = getHttp();
  $res = $client->get('exceptions/without-message');

  expect((string) $res->getBody())->toBeEmpty();
});

test('Api404Exception is a shorthand', function () {
  $client = getHttp();
  $res = $client->get('exceptions/404');

  expect($res->getStatusCode())->toBe(404);
});
