# Rate limiting <Badge type="tip" text="^2.0" />

This plugin adds rate limiting for your API endpoints using a fixed-window algorithm. It uses ProcessWire's WireCache for storage, which means request counts are persisted in the database and survive across requests.

The plugin can be installed at any level: `Api`, `Service`, or `Endpoint`.

## Installation

Install the plugin by calling the `addPlugin()` method.

```php
use PwJsonApi\Plugins\RateLimitPlugin;

// API level — limits all endpoints
$api->addPlugin(new RateLimitPlugin());

// Service level — limits only this service's endpoints
$api->addService(new MyService(), function ($service) {
  $service->addPlugin(new RateLimitPlugin());
});

// Endpoint level — limits only this endpoint
$api->addService(new MyService(), function ($service) {
  $service->findEndpoint('/')?->addPlugin(new RateLimitPlugin());
});
```

## Configuration

You can configure the plugin via the setup callback:

```php
$api->addPlugin(new RateLimitPlugin(), function ($plugin) {
  // Maximum requests per window (default: 60)
  $plugin->limit = 100;

  // Window duration in seconds (default: 60)
  $plugin->window = 120;

  // Error message when limit is exceeded (default: 'Rate limit exceeded')
  $plugin->errorMessage = 'Too many requests, please try again later';
});
```

## Response headers

The plugin sets the following headers on every response:

| Header                  | Description                                        |
| ----------------------- | -------------------------------------------------- |
| `X-RateLimit-Limit`     | Maximum number of requests allowed per window      |
| `X-RateLimit-Remaining` | Number of requests remaining in the current window |
| `X-RateLimit-Reset`     | Unix timestamp when the current window resets      |

When the rate limit is exceeded, additional headers are included:

| Header        | Description                                       |
| ------------- | ------------------------------------------------- |
| `Retry-After` | Number of seconds until the current window resets |

The response body also includes `retry_after` with the same value.

## Custom client identifier

By default, the plugin identifies clients by their IP address (`$request->ip`). If no IP address is available (e.g. in CLI contexts), rate limiting is skipped. You can provide a custom identifier using the `$clientId` callback — returning `null` will skip rate limiting for that request:

```php
use function ProcessWire\wire;

$api->addPlugin(new RateLimitPlugin(), function ($plugin) {
  $plugin->clientId = function ($request) {
    // Rate limit by user ID for authenticated users, IP for guests
    $user = wire()->user;
    return $user->isGuest() ? $request->ip : (string) $user->id;
  };
});
```

## Algorithm

The plugin uses a **fixed-window** algorithm:

1. Time is divided into fixed windows (e.g. 60-second intervals).
2. Each request increments a counter stored in WireCache.
3. If the counter exceeds the limit, a `429` response is returned.
4. When the window expires, the counter resets automatically via WireCache TTL.
