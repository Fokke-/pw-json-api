<?php

use PwJsonApi\Endpoint;
use PwJsonApi\RequestMethod;

test('path', function () {
  $endpoint = new Endpoint('/foo');
  expect($endpoint->getPath())->toBe('foo');

  $endpoint = new Endpoint('///foO/BaR//Baz//');
  expect($endpoint->getPath())->toBe('foo/bar/baz');
});

test('get handler', function () {
  $endpoint = new Endpoint('/foo');
  $endpoint->get(function () {});
  expect($endpoint->getHandler(RequestMethod::Get))->toBeCallable();
});

test('post handler', function () {
  $endpoint = new Endpoint('/foo');
  $endpoint->post(function () {});
  expect($endpoint->getHandler(RequestMethod::Post))->toBeCallable();
});

test('head handler', function () {
  $endpoint = new Endpoint('/foo');
  $endpoint->head(function () {});
  expect($endpoint->getHandler(RequestMethod::Head))->toBeCallable();
});

test('put handler', function () {
  $endpoint = new Endpoint('/foo');
  $endpoint->put(function () {});
  expect($endpoint->getHandler(RequestMethod::Put))->toBeCallable();
});

test('delete handler', function () {
  $endpoint = new Endpoint('/foo');
  $endpoint->delete(function () {});
  expect($endpoint->getHandler(RequestMethod::Delete))->toBeCallable();
});

test('options handler', function () {
  $endpoint = new Endpoint('/foo');
  $endpoint->options(function () {});
  expect($endpoint->getHandler(RequestMethod::Options))->toBeCallable();
});
