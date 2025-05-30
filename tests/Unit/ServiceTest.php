<?php

use ProcessWire\FoodService;

test('name equals to class name', function () {
  $service = new FoodService();
  expect($service->name)->toBe('FoodService');
});
