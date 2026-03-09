<?php

namespace PwJsonApi\Plugins;

use PwJsonApi\{Api, Service, Endpoint, ApiException, Request};

/**
 * Rate limiting plugin using a fixed-window algorithm with
 * ProcessWire's WireCache for storage.
 *
 * @see https://fokke-.github.io/pw-json-api/plugins/rate-limit.html
 */
class RateLimitPlugin extends ApiPlugin
{
  protected const CACHE_NS = 'PwJsonApi_RateLimit';

  /** Maximum requests per window */
  public int $limit = 60;

  /** Window duration in seconds */
  public int $window = 60;

  /** Error message for rate limit exceeded */
  public string $errorMessage = 'Rate limit exceeded';

  /**
   * Custom client identifier resolver
   *
   * @var (callable(Request): ?string)|null
   */
  public $clientId = null;

  /** Initialize plugin */
  public function init(Api|Service|Endpoint $context): static
  {
    parent::init($context);

    $context->hookBefore(function ($args) {
      $request = $args->request;
      $identifier = $this->resolveClientId($request);

      if ($identifier === null) {
        return;
      }

      $count = $this->increment($identifier);
      $reset = $this->getWindowStart() + $this->window;

      if ($count > $this->limit) {
        $retryAfter = $reset - time();

        throw (new ApiException($this->errorMessage))
          ->code(429)
          ->with(['retry_after' => $retryAfter])
          ->header('X-RateLimit-Limit', (string) $this->limit)
          ->header('X-RateLimit-Remaining', '0')
          ->header('X-RateLimit-Reset', (string) $reset)
          ->header('Retry-After', (string) $retryAfter);
      }
    });

    $context->hookAfter(function ($args) {
      $request = $args->request;
      $identifier = $this->resolveClientId($request);

      if ($identifier === null) {
        return;
      }

      $cacheKey = $this->getCacheKey($identifier);

      $cached = $this->wire->cache->getFor(static::CACHE_NS, $cacheKey);
      $count = is_numeric($cached) ? (int) $cached : 0;
      $remaining = max(0, $this->limit - $count);
      $reset = $this->getWindowStart() + $this->window;

      $args->response
        ->header('X-RateLimit-Limit', (string) $this->limit)
        ->header('X-RateLimit-Remaining', (string) $remaining)
        ->header('X-RateLimit-Reset', (string) $reset);
    });

    return $this;
  }

  /**
   * Resolve the client identifier from the request
   */
  protected function resolveClientId(Request $request): ?string
  {
    if (is_callable($this->clientId)) {
      return call_user_func($this->clientId, $request);
    }

    return $request->ip;
  }

  /**
   * Build cache key for the given identifier
   */
  protected function getCacheKey(string $identifier): string
  {
    $windowStart = $this->getWindowStart();
    return "{$identifier}_{$windowStart}";
  }

  /**
   * Get the start timestamp of the current window
   */
  protected function getWindowStart(): int
  {
    return (int) (floor(time() / $this->window) * $this->window);
  }

  /**
   * Increment the request counter and return the new count
   */
  protected function increment(string $identifier): int
  {
    $cacheKey = $this->getCacheKey($identifier);

    $cached = $this->wire->cache->getFor(static::CACHE_NS, $cacheKey);
    $count = is_numeric($cached) ? (int) $cached : 0;
    $count++;

    $this->wire->cache->saveFor(
      static::CACHE_NS,
      $cacheKey,
      (string) $count,
      $this->window,
    );

    return $count;
  }
}
