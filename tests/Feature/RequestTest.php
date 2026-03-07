<?php

const UPLOADED_FILE_KEYS = [
  'name',
  'full_path',
  'type',
  'tmp_name',
  'error',
  'size',
];

test('endpoint of service without base path', function () {
  $client = getHttp();

  $res = $client->get('food/carrot');
  expect($res->getStatusCode())->toBe(200);

  $res = $client->get('food/vegetables/carrot');
  expect($res->getStatusCode())->toBe(404);
});

test('endpoint of service with base path', function () {
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
  expect($res->getHeaderLine('Allow'))->toBe('OPTIONS, GET');
});

test('options method is always accepted', function () {
  $client = getHttp();
  $res = $client->request('options', 'food');
  expect($res->getStatusCode())->toBe(200);
  expect($res->getHeaderLine('Allow'))->toBe('OPTIONS, GET');
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

test('dynamic path with one named argument', function () {
  $client = getHttp();

  $res = $client->get('request/dynamic-path/name/foo');
  $json = resToJson($res);

  expect($json['request']['routeParams']['name'])->toBe('foo');
});

test('dynamic path with two named arguments', function () {
  $client = getHttp();

  $res = $client->get('request/dynamic-path/name/foo/bar');
  $json = resToJson($res);
  expect($json['request']['routeParams']['name'])->toBe('foo');
  expect($json['request']['routeParams']['another'])->toBe('bar');
});

test('dynamic path with predefined argument', function () {
  $client = getHttp();

  $res = $client->get('request/dynamic-path/predefined-name/bar');
  $json = resToJson($res);
  expect($json['request']['routeParams']['name'])->toBe('bar');
});

test('dynamic path with regex', function () {
  $client = getHttp();

  $res = $client->get('request/dynamic-path/regex/foo');
  $json = resToJson($res);
  expect($json['request']['routeParams'][1])->toBe('foo');

  $res = $client->get('request/dynamic-path/regex/foo/bar');
  $json = resToJson($res);
  expect($json['request']['routeParams'][1])->toBe('foo/bar');

  $res = $client->get('request/dynamic-path/regex/foo/bar/baz');
  $json = resToJson($res);
  expect($json['request']['routeParams'][1])->toBe('foo/bar/baz');
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

test('head method', function () {
  $client = getHttp();
  $res = $client->request('HEAD', 'request');

  expect($res->getStatusCode())->toBe(200);
  expect((string) $res->getBody())->toBe('');
});

test('malformed json payload', function () {
  $client = getHttp();
  $res = $client->post('request', [
    'headers' => [
      'Content-Type' => 'application/json',
    ],
    'body' => '{invalid json',
  ]);

  expect($res->getStatusCode())->toBe(400);
  $json = resToJson($res);
  expect($json['error'])->toBe('Malformed request payload');
});

test('query parameters', function () {
  $client = getHttp();
  $res = $client->get('request', [
    'query' => [
      'foo' => 'bar',
      'baz' => 'qux',
    ],
  ]);

  $json = resToJson($res);
  expect($json['request']['queryParams'])->toBe([
    'foo' => 'bar',
    'baz' => 'qux',
  ]);
  expect($json['request']['queryParams']['foo'])->toBe('bar');
  expect($json['request']['queryParams']['baz'])->toBe('qux');
});

test('toArray returns all properties', function () {
  $client = getHttp();
  $res = $client->get('request');

  $json = resToJson($res);
  $request = $json['request'];

  expect($request)->toBeArray();
  expect($request)->toHaveKeys([
    'method',
    'methodEnum',
    'path',
    'routeParams',
    'queryParams',
    'headers',
    'contentType',
    'accept',
    'cookies',
    'ip',
    'userAgent',
    'protocol',
    'body',
    'files',
  ]);
});
