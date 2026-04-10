<?php

// --- Authentication ---

test('unauthenticated request returns 401', function () {
  $client = getHttp('auth-api');
  $res = $client->get('auth');

  expect($res->getStatusCode())->toBe(401);
});

test('unauthenticated request to child service returns 401', function () {
  $client = getHttp('auth-api');
  $res = $client->get('auth/child');

  // Authenticate runs before authorize, so 401 not 403
  expect($res->getStatusCode())->toBe(401);
});
