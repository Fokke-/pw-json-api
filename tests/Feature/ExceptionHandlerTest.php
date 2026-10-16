<?php

const EXCEPTION_PHASES = [
  'on-request',
  'authenticator',
  'authorizer',
  'before-hook',
  'handler',
  'after-hook',
];

test('exception handler returns Response for exception in any phase', function (
  string $phase,
) {
  $client = getHttp('exception-phase-api');
  $res = $client->get("phases/{$phase}");
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(500);
  expect($json['data']['handled'])->toBeTrue();
  expect($json['data']['message'])->toBe($phase);
})->with(EXCEPTION_PHASES);

test('exception handler Response includes on-request headers', function () {
  $client = getHttp('exception-phase-api');
  $res = $client->get('phases/before-hook');

  expect($res->getHeaderLine('X-On-Request'))->toBe('api');
});

test('exception handler ApiException runs error hooks in any phase', function (
  string $phase,
) {
  $client = getHttp('exception-phase-api-exception');
  $res = $client->get("phases/{$phase}");
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(418);
  expect($json['error'])->toBe($phase);
  expect($json['error_hook'])->toBeTrue();
})->with(EXCEPTION_PHASES);

test('unhandled exception is not passed to exception handler twice', function (
  string $phase,
) {
  $client = getHttp('exception-phase-api-unhandled');
  $res = $client->get("phases/{$phase}");

  expect($res->getStatusCode())->toBe(500);
  expect((string) $res->getBody())->not->toContain('called_twice');
})->with(['handler', 'before-hook']);

test(
  'exception without exception handler is passed to ProcessWire',
  function () {
    $client = getHttp('exception-phase-api-none');
    $res = $client->get('phases/before-hook');

    expect($res->getStatusCode())->toBe(500);
    expect($res->getHeaderLine('Content-Type'))->not->toBe('application/json');
  },
);
