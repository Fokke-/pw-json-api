<?php

test('before hook arguments', function () {
  $client = getHttp();
  $res = $client->get('hooks');
  $json = resToJson($res);

  expect($json['before_hook_args'])->toBe([
    'type' => 'PwJsonApi\\RequestHookReturnBefore',
    'request' => 'PwJsonApi\\Request',
    'user' => 'ProcessWire\\User',
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
    'user' => 'ProcessWire\\User',
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
    'type' => 'PwJsonApi\\ErrorHookReturn',
    'request' => 'PwJsonApi\\Request',
    'user' => 'ProcessWire\\User',
    'response' => 'PwJsonApi\\Response',
    'endpoint' => 'PwJsonApi\\Endpoint',
    'service' => 'ProcessWire\\ExceptionService',
    'services' => 'PwJsonApi\\ServiceList',
    'api' => 'PwJsonApi\\Api',
    'exception' => 'PwJsonApi\\ApiException',
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

test('error hook can manipulate response code', function () {
  $client = getHttp();
  $res = $client->get('exceptions/manipulate-response-code');

  expect($res->getStatusCode())->toBe(503);
});

test('before hook can replace handler', function () {
  $client = getHttp();
  $res = $client->get('hooks/replace-handler');
  $json = resToJson($res);

  expect($json['data']['handler'])->toBe('replaced');
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

// --- hookOnRequest ---

test('on-request hook headers are added to a successful response', function () {
  $client = getHttp('on-request-api');
  $res = $client->get('on-request');

  expect($res->getStatusCode())->toBe(200);
  expect($res->getHeaderLine('X-On-Request'))->toBe('api');
});

test('on-request hooks run in order: api, service, endpoint', function () {
  $client = getHttp('on-request-api');
  $res = $client->get('on-request');

  expect($res->getHeaderLine('X-Order'))->toBe('api,service,endpoint');
});

test('on-request hook headers are added to OPTIONS response', function () {
  $client = getHttp('on-request-api');
  $res = $client->request('OPTIONS', 'on-request');

  expect($res->getStatusCode())->toBe(200);
  expect($res->getHeaderLine('Allow'))->toBe('OPTIONS, GET');
  expect($res->getHeaderLine('X-On-Request'))->toBe('api');
});

test('on-request hook headers are added to 405 response', function () {
  $client = getHttp('on-request-api');
  $res = $client->post('on-request');

  expect($res->getStatusCode())->toBe(405);
  expect($res->getHeaderLine('X-On-Request'))->toBe('api');
});

test('on-request hooks run before authentication', function () {
  $client = getHttp('on-request-api');
  $res = $client->get('gated');

  expect($res->getStatusCode())->toBe(403);
  expect($res->getHeaderLine('X-On-Request'))->toBe('api');
});

test('exception thrown in on-request hook runs error hooks', function () {
  $client = getHttp('on-request-api');
  $res = $client->get('on-request/throw');
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(418);
  expect($json['error_hook'])->toBeTrue();
  expect($res->getHeaderLine('X-On-Request'))->toBe('api');
});

test('response header overrides on-request hook header', function () {
  $client = getHttp('on-request-api');
  $res = $client->get('on-request/handler-header');

  expect($res->getHeaderLine('X-On-Request'))->toBe('handler');
});

test('malformed JSON is rejected after on-request hooks', function () {
  $client = getHttp('on-request-api');
  $res = $client->post('on-request/post', [
    'headers' => [
      'Content-Type' => 'application/json',
    ],
    'body' => '{invalid json',
  ]);
  $json = resToJson($res);

  expect($res->getStatusCode())->toBe(400);
  expect($json['error'])->toBe('Malformed request payload');
  expect($json['error_hook'])->toBeTrue();
  expect($res->getHeaderLine('X-On-Request'))->toBe('api');
});

test('OPTIONS request ignores malformed JSON', function () {
  $client = getHttp('on-request-api');
  $res = $client->request('OPTIONS', 'on-request/post', [
    'headers' => [
      'Content-Type' => 'application/json',
    ],
    'body' => '{invalid json',
  ]);

  expect($res->getStatusCode())->toBe(200);
  expect($res->getHeaderLine('Allow'))->toBe('OPTIONS, POST');
  expect($res->getHeaderLine('X-On-Request'))->toBe('api');
});
