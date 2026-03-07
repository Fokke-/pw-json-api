<?php

test('plugin finds direct child service', function () {
  $client = getHttp('plugin-tree');
  $res = $client->get('food');
  $json = resToJson($res);

  expect($json['plugin_found_service'])->toBe(true);
});

test('plugin finds nested child service', function () {
  $client = getHttp('plugin-tree');
  $res = $client->get('food/carrot');
  $json = resToJson($res);

  expect($json['plugin_found_child_service'])->toBe(true);
});

test('plugin finds endpoint on direct child service', function () {
  $client = getHttp('plugin-tree');
  $res = $client->get('food');
  $json = resToJson($res);

  expect($json['plugin_found_service_endpoint'])->toBe(true);
});

test('plugin finds endpoint on nested child service', function () {
  $client = getHttp('plugin-tree');
  $res = $client->get('food/carrot');
  $json = resToJson($res);

  expect($json['plugin_found_child_service_endpoint'])->toBe(true);
});
