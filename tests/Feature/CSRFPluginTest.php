<?php

test('token endpoint', function () {
  $client = getHttp('csrf');

  $res = $client->get('csrf-token');
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(200);
  expect($json)->not()->toHaveKey('data');
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

test('successful post returns the current token by default', function () {
  $client = getHttp('csrf');
  $token = resToJson($client->get('csrf-token'))['csrf_token'];

  $res = postWithCsrfToken($client, '', $token);
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(200);
  expect($json['csrf_token'])->toBe($token);
});

test('same token can be used for multiple requests by default', function () {
  $client = getHttp('csrf');
  $token = resToJson($client->get('csrf-token'))['csrf_token'];

  $first = postWithCsrfToken($client, '', $token);
  $second = postWithCsrfToken($client, '', $token);

  expect($first->getStatusCode())->toBe(200);
  expect($second->getStatusCode())->toBe(200);
});

test(
  'successful post rotates the token when rotateToken is enabled',
  function () {
    $client = getHttp('csrf-rotate');
    $token = resToJson($client->get('csrf-token'))['csrf_token'];

    $res = postWithCsrfToken($client, '', $token);
    $json = resToJson($res);

    expect($res->getStatusCode())->toBe(200);
    expect($json['csrf_token']['name'])->toBeString();
    expect($json['csrf_token']['value'])->toBeString();
    expect($json['csrf_token']['time'])->toBeInt();
    expect($json['csrf_token'])->not()->toBe($token);

    // The previous token is no longer valid
    expect(postWithCsrfToken($client, '', $token)->getStatusCode())->toBe(400);
  },
);

// Login also replaces the token: ProcessWire's Session::login() resets
// all CSRF tokens. Not tested here to avoid login throttling.
test('logout replaces the token', function () {
  $client = getHttp('csrf-auth');
  $token = resToJson($client->get('csrf-token'))['csrf_token'];

  $res = postWithCsrfToken($client, 'auth/logout', $token);
  $newToken = resToJson($res)['csrf_token'];

  expect($res->getStatusCode())->toBe(200);
  expect($newToken)->not()->toBe($token);

  // The previous token is no longer valid, the new one is
  expect(postWithCsrfToken($client, '', $token)->getStatusCode())->toBe(400);
  expect(postWithCsrfToken($client, '', $newToken)->getStatusCode())->toBe(200);
});
