<?php

test('valid response is encoded', function () {
  $client = getHttp('encoding-api');
  $res = $client->get('encoding/valid');
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(200);
  expect($json['data']['value'])->toBe('äö');
});

test('invalid UTF-8 in response returns 500', function () {
  $client = getHttp('encoding-api');
  $res = $client->get('encoding/invalid-utf8');
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(500);
  expect($json['error'])->toBe('Failed to encode response');
});

test('NaN in response returns 500', function () {
  $client = getHttp('encoding-api');
  $res = $client->get('encoding/nan');
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(500);
  expect($json['error'])->toBe('Failed to encode response');
});

test('encoding error response keeps response headers', function () {
  $client = getHttp('encoding-api');
  $res = $client->get('encoding/invalid-utf8');

  expect($res->getHeaderLine('X-Encoding-Test'))->toBe('kept');
});
