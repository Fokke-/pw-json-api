<?php

use ProcessWire\{Page, PageArray};
use function ProcessWire\wire;

// Bootstrap PW
require __DIR__ . '/../index.php';

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

// pest()->extend(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

// expect()->extend('toBeOne', function () {
//     return $this->toBe(1);
// });

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function getHttp(string $prefix = 'api')
{
  $client = new GuzzleHttp\Client([
    'base_uri' => 'https://pw-json-api.ddev.site/' . $prefix . '/',
    'http_errors' => false,
    'cookies' => true,
  ]);

  return $client;
}

function resToJson(\Psr\Http\Message\ResponseInterface $res): array
{
  $body = (string) $res->getbody();
  return json_decode(!empty($body) ? $body : '[]', true);
}

function getMultiplePages(): PageArray
{
  $pages = new PageArray();
  $pages->add(getPage());
  $pages->add(getEmptyPage());

  return $pages;
}

function getPage(): Page
{
  $page = clone wire()->pages->get(1018);
  $page->of(true);

  return $page;
}

function getEmptyPage(): Page
{
  $page = clone wire()->pages->get(1072);
  $page->of(true);

  return $page;
}

function getPageWithChildren(): Page
{
  $page = clone wire()->pages->get(1029);
  $page->of(true);

  return $page;
}

function getPageStackKeys(array $pages, string $key): \Generator
{
  foreach ($pages as $page) {
    yield $page[$key];

    if (!empty($page['_children'])) {
      yield from getPageStackKeys($page['_children'], $key);
    }
  }
}

function getFile(string $filename)
{
  return fopen("/var/www/html/tests/fixtures/files/{$filename}", 'r');
}
