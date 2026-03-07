<?php

test('before hook arguments', function () {
  $client = getHttp();
  $res = $client->get('hooks');
  $json = resToJson($res);

  expect($json['before_hook_args'])->toBe([
    'type' => 'PwJsonApi\\RequestHookReturnBefore',
    'request' => 'PwJsonApi\\Request',
    'endpoint' => 'PwJsonApi\\Endpoint',
    'service' => 'ProcessWire\\HooksService',
    'services' => 'PwJsonApi\\ServiceList',
    'api' => 'PwJsonApi\\Api',
    'handler' => 'object',
  ]);
});

test('after hook arguments', function () {
  $client = getHttp();
  $res = $client->get('hooks');
  $json = resToJson($res);

  expect($json['after_hook_args'])->toBe([
    'type' => 'PwJsonApi\\RequestHookReturnAfter',
    'request' => 'PwJsonApi\\Request',
    'endpoint' => 'PwJsonApi\\Endpoint',
    'service' => 'ProcessWire\\HooksService',
    'services' => 'PwJsonApi\\ServiceList',
    'api' => 'PwJsonApi\\Api',
    'response' => 'PwJsonApi\\Response',
  ]);
});

test('before hooks execution order', function () {
  $client = getHttp();
  $res = $client->get('hooks');
  $json = resToJson($res);

  expect($json['before_hook_execution_order'])->toBe([
    'api',
    'service',
    'endpoint',
  ]);
});

test('after hooks execution order', function () {
  $client = getHttp();
  $res = $client->get('hooks');
  $json = resToJson($res);

  expect($json['after_hook_execution_order'])->toBe([
    'endpoint',
    'service',
    'api',
  ]);
});

test('after hook can manipulate response', function () {
  $client = getHttp();
  $res = $client->get('hooks/manipulate-response');
  $json = resToJson($res);

  expect($json['data']['foo'])->toBe('bar');
  expect($json['data']['fruits'])->toContain('banana');
});

test('nested service before hooks execution order', function () {
  $client = getHttp();
  $res = $client->get('hooks/nested');
  $json = resToJson($res);

  expect($json['before_hook_execution_order'])->toBe([
    'api',
    'service',
    'child-service',
    'endpoint',
  ]);
});

test('nested service after hooks execution order', function () {
  $client = getHttp();
  $res = $client->get('hooks/nested');
  $json = resToJson($res);

  expect($json['after_hook_execution_order'])->toBe([
    'endpoint',
    'child-service',
    'service',
    'api',
  ]);
});

test('nested service error hooks execution order', function () {
  $client = getHttp();
  $res = $client->get('hooks/nested/error');
  $json = resToJson($res);

  expect($json['error_hook_execution_order'])->toBe([
    'endpoint',
    'child-service',
    'service',
    'api',
  ]);
});

test('nested service base path', function () {
  $client = getHttp();
  $nested = $client->get('hooks/nested');
  $root = $client->get('hooks');

  expect($nested->getStatusCode())->toBe(200);
  expect($root->getStatusCode())->toBe(200);
});

test('error hook arguments', function () {
  $client = getHttp();
  $res = $client->get('exceptions');
  $json = resToJson($res);

  expect($json['error_hook_args'])->toBe([
    'type' => 'PwJsonApi\\ApiException',
    'request' => 'PwJsonApi\\Request',
    'response' => 'PwJsonApi\\Response',
    'endpoint' => 'PwJsonApi\\Endpoint',
    'service' => 'ProcessWire\\ExceptionService',
    'services' => 'PwJsonApi\\ServiceList',
    'api' => 'PwJsonApi\\Api',
  ]);
});

test('error hooks are executed in right order', function () {
  $client = getHttp();
  $res = $client->get('exceptions');
  $json = resToJson($res);

  expect($json['error_hook_execution_order'])->toBe([
    'endpoint',
    'service',
    'api',
  ]);
});

test('error hook can manipulate response', function () {
  $client = getHttp();
  $res = $client->get('exceptions/manipulate-response');
  $json = resToJson($res);

  expect($json['error'])->toBe('updated');
});

test('hookBeforeGet fires on GET request', function () {
  $client = getHttp();
  $res = $client->get('hooks/method-specific');
  $json = resToJson($res);

  expect($json['hook_before_get_fired'])->toBeTrue();
});

test('hookBeforeGet does not fire on POST request', function () {
  $client = getHttp();
  $res = $client->post('hooks/method-specific');
  $json = resToJson($res);

  expect($json)->not->toHaveKey('hook_before_get_fired');
});
