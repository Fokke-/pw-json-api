<?php

use PwJsonApi\Request;

/**
 * Creates a Request instance with custom headers using reflection
 * to bypass the HookEvent constructor dependency.
 *
 * @param array<string, string> $headers
 */
function createRequestWithHeaders(array $headers): Request
{
  $ref = new ReflectionClass(Request::class);
  $request = $ref->newInstanceWithoutConstructor();

  $headersProp = $ref->getProperty('headers');
  $headersProp->setValue($request, $headers);

  // Simulate what the constructor does for contentType and accept
  $contentType = null;
  $accept = null;
  $lowerMap = array_change_key_case($headers, CASE_LOWER);
  $contentType = $lowerMap['content-type'] ?? null;
  $accept = $lowerMap['accept'] ?? null;

  $ref->getProperty('contentType')->setValue($request, $contentType);
  $ref->getProperty('accept')->setValue($request, $accept);

  return $request;
}

test('header() returns value case-insensitively', function () {
  $ref = new ReflectionClass(Request::class);
  $request = $ref->newInstanceWithoutConstructor();
  $ref->getProperty('headers')->setValue($request, [
    'content-type' => 'application/json',
    'Accept' => 'text/html',
    'X-Custom-Header' => 'custom',
  ]);

  expect($request->header('Content-Type'))->toBe('application/json');
  expect($request->header('content-type'))->toBe('application/json');
  expect($request->header('CONTENT-TYPE'))->toBe('application/json');
  expect($request->header('Accept'))->toBe('text/html');
  expect($request->header('accept'))->toBe('text/html');
  expect($request->header('x-custom-header'))->toBe('custom');
});

test('header() returns null for missing header', function () {
  $ref = new ReflectionClass(Request::class);
  $request = $ref->newInstanceWithoutConstructor();
  $ref->getProperty('headers')->setValue($request, []);

  expect($request->header('Content-Type'))->toBeNull();
  expect($request->header('Accept'))->toBeNull();
});
