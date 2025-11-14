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
