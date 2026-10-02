<?php

namespace PwJsonApi;

/**
 * Result of a successful endpoint search
 */
class ApiSearchEndpointResult
{
  /**
   * Endpoint
   */
  public Endpoint $endpoint;

  /**
   * Service
   */
  public Service $service;

  /**
   * Service sequence of the result
   *
   * @var Service[]
   */
  public array $serviceSequence;

  /**
   * Constructor
   *
   * @param Service[] $serviceSequence
   */
  public function __construct(
    Endpoint $endpoint,
    Service $service,
    array $serviceSequence,
  ) {
    $this->endpoint = $endpoint;
    $this->service = $service;
    $this->serviceSequence = $serviceSequence;
  }

  /**
   * Resolve path for the endpoint
   */
  public function resolvePath(?string $basePath = null): string
  {
    return implode(
      '/',
      array_filter(
        [
          // For leading slash
          '',

          // Base path
          $basePath,

          // Service tree base paths
          ...array_map(
            static fn(Service $service) => $service->getBasePath(),
            $this->serviceSequence,
          ),

          // Endpoint path
          $this->endpoint->getPath(),
        ],
        static fn($segment) => !is_null($segment),
      ),
    );
  }

  /**
   * Resolve hooks from result endpoint and all of its services
   *
   * For timing "Before" the order is Service -> Endpoint
   * For timing "After" the order is Endpoint -> Service
   *
   * @return callable[]
   */
  public function resolveHooks(
    HookTiming $timing,
    RequestMethod|null $requestMethod = null,
  ): array {
    $serviceHooks = array_merge(
      ...array_map(
        static fn($service) => [
          ...$service->findRequestHooks($timing),
          ...$service->findRequestHooks($timing, $requestMethod),
        ],
        $timing === HookTiming::Before
          ? $this->serviceSequence
          : array_reverse($this->serviceSequence),
      ),
    );

    $endpointHooks = [
      ...$this->endpoint->findRequestHooks($timing),
      ...$this->endpoint->findRequestHooks($timing, $requestMethod),
    ];

    if ($timing === HookTiming::Before) {
      return [...$serviceHooks, ...$endpointHooks];
    }

    return [...$endpointHooks, ...$serviceHooks];
  }

  /**
   * Get endpoint and services from the nearest level to the farthest
   *
   * @return array<Endpoint|Service>
   */
  private function getLevelsLeafToRoot(): array
  {
    return [$this->endpoint, ...array_reverse($this->serviceSequence)];
  }

  /**
   * Resolve authenticator from the nearest level
   *
   * Order: Endpoint → Services (leaf → root) → Api. A level that skips
   * authentication stops the search, unless it has its own authenticator.
   */
  public function resolveAuthenticator(Api $api): Authenticator|null
  {
    foreach ($this->getLevelsLeafToRoot() as $level) {
      $authenticator = $level->_getAuthenticator();

      if ($authenticator !== null) {
        return $authenticator;
      }

      if ($level->_skipsAuthentication()) {
        return null;
      }
    }

    return $api->_getAuthenticator();
  }

  /**
   * Resolve authorizers from all levels
   *
   * Collected from Endpoint → Services (leaf → root) → Api. A level that
   * skips authorization stops the collection after its own authorizer.
   *
   * Returned in execution order: Api → Services (root → leaf) → Endpoint
   *
   * @return array<callable(AuthorizeArgs): bool>
   */
  public function resolveAuthorizers(Api $api): array
  {
    $authorizers = [];

    foreach ($this->getLevelsLeafToRoot() as $level) {
      $authorizer = $level->_getAuthorizer();

      if ($authorizer !== null) {
        $authorizers[] = $authorizer;
      }

      if ($level->_skipsAuthorization()) {
        return array_reverse($authorizers);
      }
    }

    $apiAuthorizer = $api->_getAuthorizer();

    if ($apiAuthorizer !== null) {
      $authorizers[] = $apiAuthorizer;
    }

    return array_reverse($authorizers);
  }

  /**
   * Resolve onRequest hooks from result endpoint and all of its services
   *
   * Order: Services (root → leaf) → Endpoint
   *
   * @return callable[]
   */
  public function resolveOnRequestHooks(): array
  {
    $serviceHooks = array_merge(
      ...array_map(
        static fn($service) => $service->getRequestHooks(
          RequestHookKey::OnRequest,
        ),
        $this->serviceSequence,
      ),
    );

    $endpointHooks = [
      ...$this->endpoint->getRequestHooks(RequestHookKey::OnRequest),
    ];

    return [...$serviceHooks, ...$endpointHooks];
  }

  /**
   * Resolve onError hooks from result endpoint and all of its services
   *
   * @return callable[]
   */
  public function resolveErrorHooks(): array
  {
    $serviceHooks = array_merge(
      ...array_map(
        static fn($service) => $service->getRequestHooks(
          RequestHookKey::OnError,
        ),
        array_reverse($this->serviceSequence),
      ),
    );

    $endpointHooks = [
      ...$this->endpoint->getRequestHooks(RequestHookKey::OnError),
    ];

    return [...$endpointHooks, ...$serviceHooks];
  }
}
