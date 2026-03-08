<?php

test('custom header is included in response', function () {
  $client = getHttp('response-headers');
  $res = $client->get('');

  expect($res->getStatusCode())->toBe(200);
  expect($res->getHeaderLine('X-Custom-Header'))->toBe('custom-value');
});

test('multiple headers can be set on a single response', function () {
  $client = getHttp('response-headers');
  $res = $client->get('multiple');

  expect($res->getStatusCode())->toBe(200);
  expect($res->getHeaderLine('X-First'))->toBe('one');
  expect($res->getHeaderLine('X-Second'))->toBe('two');
});

test(
  'custom headers on ApiException are included in error response',
  function () {
    $client = getHttp('response-headers');
    $res = $client->get('error');

    expect($res->getStatusCode())->toBe(400);
    expect($res->getHeaderLine('X-Error-Header'))->toBe('error-value');
  },
);

test('headers set in after hooks are included in the response', function () {
  $client = getHttp('response-headers');
  $res = $client->get('');

  expect($res->getStatusCode())->toBe(200);
  expect($res->getHeaderLine('X-After-Hook-Header'))->toBe('after-hook-value');
});
