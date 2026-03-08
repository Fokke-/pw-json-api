<?php

// --- Installation ---

test('installed on API', function () {
  $client = getHttp('plugins');
  $res = $client->get('');
  $json = resToJson($res);

  expect($json['api_plugin'])->toBe(true);
});

test('installed on service', function () {
  $client = getHttp('plugins');
  $res = $client->get('');
  $json = resToJson($res);

  expect($json['service_plugin'])->toBe(true);
});

test('installed on endpoint', function () {
  $client = getHttp('plugins');
  $res = $client->get('');
  $json = resToJson($res);

  expect($json['endpoint_plugin'])->toBe(true);
});

// --- Tree discovery ---

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

// --- Hook attachment ---

test('plugin attaches hookAfter to direct child service', function () {
  $client = getHttp('plugin-hook-attach');
  $res = $client->get('food');
  $json = resToJson($res);

  expect($json['plugin_service_hook'])->toBe(true);
});

test('plugin attaches hookAfter to nested child service', function () {
  $client = getHttp('plugin-hook-attach');
  $res = $client->get('food/carrot');
  $json = resToJson($res);

  expect($json['plugin_child_service_hook'])->toBe(true);
});

test(
  'plugin attaches hookAfter to endpoint on direct child service',
  function () {
    $client = getHttp('plugin-hook-attach');
    $res = $client->get('food');
    $json = resToJson($res);

    expect($json['plugin_endpoint_hook'])->toBe(true);
  },
);

test(
  'plugin attaches hookAfter to endpoint on nested child service',
  function () {
    $client = getHttp('plugin-hook-attach');
    $res = $client->get('food/carrot');
    $json = resToJson($res);

    expect($json['plugin_child_endpoint_hook'])->toBe(true);
  },
);
