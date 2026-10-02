<?php

use PwJsonApi\RequestMethod;

test('isSafe() returns whether the method is safe', function (
  RequestMethod $method,
  bool $expected,
) {
  expect($method->isSafe())->toBe($expected);
})->with([
  'GET' => [RequestMethod::Get, true],
  'HEAD' => [RequestMethod::Head, true],
  'OPTIONS' => [RequestMethod::Options, true],
  'PUT' => [RequestMethod::Put, false],
  'DELETE' => [RequestMethod::Delete, false],
  'POST' => [RequestMethod::Post, false],
  'PATCH' => [RequestMethod::Patch, false],
]);
