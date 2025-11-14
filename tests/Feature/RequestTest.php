<?php

const UPLOADED_FILE_KEYS = [
  'name',
  'full_path',
  'type',
  'tmp_name',
  'error',
  'size',
];

test('query endpoint of service without base path', function () {
  $client = getHttp();

  $res = $client->get('food/carrot');
  expect($res->getStatusCode())->toBe(200);

  $res = $client->get('food/vegetables/carrot');
  expect($res->getStatusCode())->toBe(404);
});

test('query endpoint of service with base path', function () {
  $client = getHttp();

  $res = $client->get('food/fruits/apple');
  expect($res->getStatusCode())->toBe(200);

  $res = $client->get('food/apple');
  expect($res->getStatusCode())->toBe(404);
});

test('method not allowed', function () {
  $client = getHttp();
  $res = $client->post('food');

  expect($res->getStatusCode())->toBe(405);
});

test('options method is always accepted', function () {
  $client = getHttp();
  $res = $client->request('options', 'food');
  expect($res->getStatusCode())->toBe(200);

  $res = $client->request('options', 'non-existant-endpoint');
  expect($res->getStatusCode())->toBe(200);
});

test('request method handlers', function () {
  $client = getHttp();

  $res = $client->get('request');
  $json = resToJson($res);
  expect($json['request']['method'])->toBe('GET');

  $res = $client->put('request');
  $json = resToJson($res);
  expect($json['request']['method'])->toBe('PUT');

  $res = $client->delete('request');
  $json = resToJson($res);
  expect($json['request']['method'])->toBe('DELETE');

  $res = $client->post('request');
  $json = resToJson($res);
  expect($json['request']['method'])->toBe('POST');

  $res = $client->patch('request');
  $json = resToJson($res);
  expect($json['request']['method'])->toBe('PATCH');
});

test('payload as json', function () {
  $payload = [
    'foo' => 'foo',
    'bar' => 'bar',
    'baz' => [
      'foo' => 'foo',
    ],
  ];

  $client = getHttp();
  $res = $client->post('request', [
    'json' => $payload,
  ]);

  $json = resToJson($res);
  expect($json['request']['body'])->toBe($payload);
});

test('payload as form params', function () {
  $payload = [
    'foo' => 'foo',
    'bar' => 'bar',
    'baz' => [
      'foo' => 'foo',
    ],
  ];

  $client = getHttp();
  $res = $client->post('request', [
    'form_params' => $payload,
  ]);

  $json = resToJson($res);
  expect($json['request']['body'])->toBe($payload);
});

test('payload as multipart', function () {
  $payload = [
    [
      'name' => 'foo',
      'contents' => 'foo',
    ],
    [
      'name' => 'bar',
      'contents' => 'bar',
    ],
    [
      'name' => 'baz',
      'contents' => 'baz',
    ],
  ];

  $client = getHttp();
  $res = $client->post('request', [
    'multipart' => $payload,
  ]);

  $json = resToJson($res);
  expect($json['request']['body'])->toBe([
    'foo' => 'foo',
    'bar' => 'bar',
    'baz' => 'baz',
  ]);
});

test('single file upload', function () {
  $client = getHttp();
  $res = $client->post('request', [
    'multipart' => [
      [
        'name' => 'upload',
        'contents' => getFile('foo.txt'),
        'filename' => 'foo.txt',
        'Mime-Type' => 'text/plain',
      ],
      [
        'name' => 'foo',
        'contents' => 'foo',
      ],
    ],
  ]);

  $json = resToJson($res);
  expect($json['request']['files']['upload'])
    ->toBeArray()
    ->toHaveCount(1);

  expect(array_keys($json['request']['files']['upload'][0]))->toBe(
    UPLOADED_FILE_KEYS,
  );

  expect($json['request']['body'])->toBe([
    'foo' => 'foo',
  ]);
});

test('multiple file uploads', function () {
  $client = getHttp();
  $res = $client->post('request', [
    'multipart' => [
      [
        'name' => 'upload[]',
        'contents' => getFile('foo.txt'),
        'filename' => 'foo.txt',
        'Mime-Type' => 'text/plain',
      ],
      [
        'name' => 'upload[]',
        'contents' => getFile('bar.txt'),
        'filename' => 'bar.txt',
        'Mime-Type' => 'text/plain',
      ],
      [
        'name' => 'foo',
        'contents' => 'foo',
      ],
    ],
  ]);

  $json = resToJson($res);
  expect($json['request']['files']['upload'])
    ->toBeArray()
    ->toHaveCount(2);

  expect(array_keys($json['request']['files']['upload'][0]))->toBe(
    UPLOADED_FILE_KEYS,
  );

  expect(array_keys($json['request']['files']['upload'][1]))->toBe(
    UPLOADED_FILE_KEYS,
  );

  expect($json['request']['body'])->toBe([
    'foo' => 'foo',
  ]);
});
