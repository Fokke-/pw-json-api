<?php

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
