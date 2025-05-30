<?php

use PwJsonApi\ServiceList;
use ProcessWire\FoodService;
use ProcessWire\FruitService;

test('getItems()', function () {
  $list = (new ServiceList())->add(new FoodService())->add(new FruitService());

  expect(count($list->getItems()))->toBe(2);
});

test('get()', function () {
  $list = (new ServiceList())->add(new FoodService());

  expect($list->get('FoodService') instanceof FoodService)->toBe(true);
  expect($list->get('Foo'))->toBe(null);
});

test('add() with setup function', function () {
  $list = new ServiceList();
  $list->add(new FoodService(), function ($service) {
    $service->setBasePath('/foo');
  });

  expect($list->get('FoodService')->getBasePath())->toBe('foo');
});
