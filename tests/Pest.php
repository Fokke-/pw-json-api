<?php

use ProcessWire\{FoodService, FruitService, Page, PageArray, VegetableService};
use PwJsonApi\{
  ApiSearchEndpointResult,
  AuthenticateArgs,
  Authenticator,
  Endpoint,
  Service,
};
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

/**
 * Send a POST request with the given CSRF token
 *
 * @param array{name: string, value: string} $token
 */
function postWithCsrfToken(
  GuzzleHttp\Client $client,
  string $path,
  array $token,
): \Psr\Http\Message\ResponseInterface {
  return $client->post($path, [
    'headers' => [
      'X-' . $token['name'] => $token['value'],
    ],
  ]);
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

/**
 * Page whose files and images have been deleted from the disk
 */
function getPageWithMissingFiles(): Page
{
  $page = clone wire()->pages->get(1079);
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

/**
 * Service chain /foo → /foo/bar → /foo/bar/baz
 *
 * @return array{foo: Service, bar: Service, baz: Service}
 */
function authChain(): array
{
  $chain = [
    'foo' => new FoodService(),
    'bar' => new FruitService(),
    'baz' => new VegetableService(),
  ];

  foreach ($chain as $service) {
    $service->_prepare();
  }

  return $chain;
}

/**
 * Result for a request to an endpoint of the last service in the sequence
 *
 * @param Service[] $services
 */
function authChainRequest(array $services): ApiSearchEndpointResult
{
  return new ApiSearchEndpointResult(
    new Endpoint('/'),
    $services[array_key_last($services)],
    $services,
  );
}

function authChainAuthenticator(): Authenticator
{
  return new class extends Authenticator {
    public function authenticate(AuthenticateArgs $args): void {}
  };
}

/**
 * Exposes the private Utils::formatPath() method
 */
function formatPathForTest(string $path, bool $preservePatternCase): ?string
{
  $formatter = new class {
    use \PwJsonApi\Utils;

    public function format(string $path, bool $preserve): ?string
    {
      return $this->formatPath($path, $preserve);
    }
  };

  return $formatter->format($path, $preservePatternCase);
}

/**
 * Run an API with a single endpoint and collect deprecation notices
 *
 * @return string[]
 */
function collectPathDeprecations(string $path, bool $preserve): array
{
  $notices = [];

  set_error_handler(function (int $level, string $message) use (&$notices) {
    if ($level === E_USER_DEPRECATED) {
      $notices[] = $message;
    }
    return true;
  });

  try {
    $service = new class extends Service {
      protected function init()
      {
        $this->setBasePath('/path-case-unit');
      }
    };

    (new \PwJsonApi\Api())
      ->configure(function ($config) use ($preserve) {
        $config->preservePathCase = $preserve;
      })
      ->setBasePath('/unit')
      ->addService($service, function ($service) use ($path) {
        $service->addEndpoint($path)->get(function () {
          return new \PwJsonApi\Response();
        });
      })
      ->run();
  } finally {
    restore_error_handler();
  }

  return $notices;
}

/**
 * Run a function without reporting expected errors of the given levels,
 * e.g. warnings suppressed by ProcessWire
 */
function withoutErrors(int $levels, callable $fn): mixed
{
  set_error_handler(static fn() => true, $levels);

  try {
    return $fn();
  } finally {
    restore_error_handler();
  }
}

/**
 * Run a function without reporting expected deprecation notices,
 * e.g. when testing a deprecated class
 */
function withoutDeprecations(callable $fn): mixed
{
  return withoutErrors(E_USER_DEPRECATED, $fn);
}
