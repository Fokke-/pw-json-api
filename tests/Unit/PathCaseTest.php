<?php

use PwJsonApi\Request;

// --- formatPath() ---

test('formatPath() lowercases the whole path by default', function () {
  expect(formatPathForTest('/Products/{productId}', false))->toBe(
    'products/{productid}',
  );
});

test('formatPath() keeps the case when preserving', function (
  string $path,
  string $expected,
) {
  expect(formatPathForTest($path, true))->toBe($expected);
})->with([
  'literal' => ['/Products/Latest', 'Products/Latest'],
  'named argument' => ['/Products/{productId}', 'Products/{productId}'],
  'regex group' => ['/Codes/(?<code>\D+)', 'Codes/(?<code>\D+)'],
  'character class' => ['/Items/[A-Z]+', 'Items/[A-Z]+'],
]);

test('formatPath() normalizes slashes when preserving', function () {
  expect(formatPathForTest('//Api//Users/', true))->toBe('Api/Users');
});

// --- routeParam() ---

test('routeParam() is case-insensitive', function () {
  $ref = new ReflectionClass(Request::class);
  $request = $ref->newInstanceWithoutConstructor();
  $ref->getProperty('routeParams')->setValue($request, [
    'productid' => '123',
  ]);

  expect($request->routeParam('productid'))->toBe('123');
  expect($request->routeParam('productId'))->toBe('123');
  expect($request->routeParam('missing'))->toBeNull();
});

test('routeParam() prefers an exact match', function () {
  $ref = new ReflectionClass(Request::class);
  $request = $ref->newInstanceWithoutConstructor();
  $ref->getProperty('routeParams')->setValue($request, [
    'productid' => 'lower',
    'productId' => 'exact',
  ]);

  expect($request->routeParam('productId'))->toBe('exact');
});

// --- Deprecation ---

test('run() warns about lowercased paths', function (string $path) {
  $notices = collectPathDeprecations($path, false);

  expect($notices)->toHaveCount(1);
  expect($notices[0])->toContain('preservePathCase');
})->with(['/Products/Latest', '/codes/(?<code>\D+)']);

test('run() does not warn about lowercase paths', function () {
  expect(collectPathDeprecations('/products/{id}', false))->toBe([]);
});

test('run() does not warn when preservePathCase is enabled', function () {
  expect(collectPathDeprecations('/Products/Latest', true))->toBe([]);
});
