<?php

use PwJsonApi\Endpoint;
use PwJsonApi\EndpointList;
use ProcessWire\WireException;

test('add() and getItems()', function () {
  $list = (new EndpointList())
    ->add(new Endpoint('foo'))
    ->add(new Endpoint('bar'));

  expect(count($list->getItems()))->toBe(2);
});

test('get()', function () {
  $list = (new EndpointList())
    ->add(new Endpoint('foo'))
    ->add(new Endpoint('bar'));

  expect($list->get('/foo') instanceof Endpoint)->toBe(true);
  expect($list->get('//fOo//') instanceof Endpoint)->toBe(true);
  expect($list->get('/non-existent'))->toBe(null);
});

test('get() with base path', function () {
  $list = (new EndpointList())
    ->setBasePath('foo')
    ->add(new Endpoint('bar'))
    ->add(new Endpoint('baz'));

  expect($list->get('/foo/bar') instanceof Endpoint)->toBe(true);
  expect($list->get('//fOo//BaR') instanceof Endpoint)->toBe(true);
  expect($list->get('/bar') instanceof Endpoint)->toBe(true);
  expect($list->get('//bAr//') instanceof Endpoint)->toBe(true);
});

test('getPaths()', function () {
  $list = (new EndpointList())
    ->add(new Endpoint('bar'))
    ->add(new Endpoint('baz'));
  expect($list->getPaths())->toBe(['bar', 'baz']);

  $list->setBasePath('foo');
  expect($list->getPaths())->toBe(['bar', 'baz']);
});

test('remove()', function () {
  $list = (new EndpointList())
    ->add(new Endpoint('bar'))
    ->add(new Endpoint('baz'))
    ->remove('///bar/');
  expect($list->getPaths())->toBe(['baz']);

  expect(fn() => $list->remove('/non-existent'))->toThrow(WireException::class);
});
