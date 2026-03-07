<?php

test('default response code', function () {
  $client = getHttp();
  $res = $client->get('exceptions');

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

test('if message is not passed, response does not include it', function () {
  $client = getHttp();
  $res = $client->get('exceptions/without-message');
  $data = resToJson($res);

  expect($data)->not()->toHaveKey('error');
});

test('Api404Exception is a shorthand', function () {
  $client = getHttp();
  $res = $client->get('exceptions/404');
  $data = resToJson($res);

  expect($res->getStatusCode())->toBe(404);
  expect($data)->not()->toHaveKey('error');
});

test('with() adds top-level keys to error response', function () {
  $client = getHttp();
  $res = $client->get('exceptions/with');
  $data = resToJson($res);

  expect($res->getStatusCode())->toBe(400);
  expect($data['key'])->toBe('value');
});

test('custom error handler function handles Exception', function () {
  $client = getHttp();
  $res = $client->get('exceptions/base-exception');
  $data = resToJson($res);

  expect($res->getStatusCode())->toBe(400);
  expect($data['message'])->toBe('base-exception');
});

test('custom error handler function handles WireException', function () {
  $client = getHttp();
  $res = $client->get('exceptions/wire-exception');
  $data = resToJson($res);

  expect($res->getStatusCode())->toBe(400);
  expect($data['message'])->toBe('wire-exception');
});

test('exception handler can return Response', function () {
  $client = getHttp('exception-response');
  $res = $client->get('exceptions/response-from-handler');
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(500);
  expect($json['data']['handled'])->toBe(true);
  expect($json['data']['message'])->toBe('handled-with-response');
});
