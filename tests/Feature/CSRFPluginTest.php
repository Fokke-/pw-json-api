<?php

test('token endpoint', function () {
  $client = getHttp('csrf');

  $res = $client->get('csrf-token');
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(200);
  expect($json['csrf_token']['name'])->toBeString();
  expect($json['csrf_token']['value'])->toBeString();
  expect($json['csrf_token']['time'])->toBeInt();
});

test('token endpoint does not rotate the token', function () {
  $client = getHttp('csrf');

  $firstRes = $client->get('csrf-token');
  $firstToken = resToJson($firstRes)['csrf_token'];

  $secondRes = $client->get('csrf-token');
  $secondToken = resToJson($secondRes)['csrf_token'];

  expect($firstToken)->toBe($secondToken);
});

test('unsuccessful post', function () {
  $client = getHttp('csrf');
  $res = $client->post('');
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(400);
  expect($json['csrf_token']['name'])->toBeString();
  expect($json['csrf_token']['value'])->toBeString();
  expect($json['csrf_token']['time'])->toBeInt();
});

test('unsuccessful post does not rotate the token', function () {
  $client = getHttp('csrf');

  $firstRes = $client->post('');
  $firstToken = resToJson($firstRes)['csrf_token'];

  $secondRes = $client->post('');
  $secondToken = resToJson($secondRes)['csrf_token'];

  expect($firstRes->getStatusCode())->toBe(400);
  expect($secondRes->getStatusCode())->toBe(400);
  expect($firstToken)->toBe($secondToken);
});

test('successful post rotates the token', function () {
  $client = getHttp('csrf');
  $tokenRes = $client->get('csrf-token');
  $token = resToJson($tokenRes)['csrf_token'];

  $res = $client->post('', [
    'headers' => [
      'X-' . $token['name'] => $token['value'],
    ],
  ]);
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(200);
  expect($json['csrf_token']['name'])->toBeString();
  expect($json['csrf_token']['value'])->toBeString();
  expect($json['csrf_token']['time'])->toBeInt();
  expect($json['csrf_token'])->not()->toBe($token);
});
